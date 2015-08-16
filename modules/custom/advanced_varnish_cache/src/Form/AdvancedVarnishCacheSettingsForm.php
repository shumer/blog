<?php

/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\Form\AdvancedVarnishCacheSettingsForm.
 */

namespace Drupal\advanced_varnish_cache\Form;

use Drupal\advanced_varnish_cache\AdvancedVarnishCacheInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Render\Element\StatusMessages;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Configure varnish settings for this site.
 */
class AdvancedVarnishCacheSettingsForm extends ConfigFormBase {

  /**
   * Stores the state storage service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;
  protected $varnish_handler;

  /**
   * Constructs a AdvancedVarnishCacheSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state key value store.
   */
  public function __construct(ConfigFactoryInterface $config_factory, StateInterface $state, AdvancedVarnishCacheInterface $varnish_handler) {
    parent::__construct($config_factory);
    $this->state = $state;
    $this->varnish_handler = $varnish_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('state'),
      $container->get('advanced_varnish_cache_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'advanced_varnish_cache_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['advanced_varnish_cache.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('advanced_varnish_cache.settings');

    $form['advanced_varnish_cache'] = [
      '#tree' => TRUE,
    ];

    // Display module status.
    $backend_status = $this->varnish_handler->varnish_get_status();

    $_SESSION['messages'] = [];
    if (empty($backend_status)) {
      drupal_set_message(t('Varnish backend is not set.'), 'warning');
    }
    else {
      foreach ($backend_status as $backend => $status) {
        if (empty($status)) {
          drupal_set_message(t('Varnish at !backend not responding.', ['!backend' => $backend]), 'error');
        }
        else {
          drupal_set_message(t('Varnish at !backend connected.', ['!backend' => $backend]));
        }
      }
    }

    $form['advanced_varnish_cache']['general'] = array(
      '#title' => t('General settings'),
      '#type' => 'details',
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );

    $form['advanced_varnish_cache']['general']['logging'] = array(
      '#type' => 'checkbox',
      '#title' => t('Logging'),
      '#default_value' => $config->get('general.logging'),
      '#description' => t('Check, if you want to log vital actions to watchdog.'),
    );

    $form['advanced_varnish_cache']['general']['debug'] = array(
      '#type' => 'checkbox',
      '#title' => t('Debug ESI'),
      '#default_value' => $config->get('general.debug'),
      '#description' => t('Check if you want to add debug info to ESI tags.'),
    );


    $form['advanced_varnish_cache']['general']['noise'] = array(
        '#type' => 'textfield',
        '#title' => t('Hashing Noise'),
        '#default_value' => $config->get('general.noise'),
        '#description' => t('This works as private key, you can change it at any time.'),
    );

    $form['advanced_varnish_cache']['connection'] = array(
      '#title' => t('Varnish Connection settings'),
      '#type' => 'details',
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );

    // This is replica from Varnish module.
    $form['advanced_varnish_cache']['connection']['control_terminal'] = array(
      '#type' => 'textfield',
      '#title' => t('Control Terminal'),
      '#default_value' => $config->get('connection.control_terminal'),
      '#required' => TRUE,
      '#description' => t('Set this to the server IP or hostname that varnish runs on (e.g. 127.0.0.1:6082). This must be configured for Drupal to talk to Varnish. Separate multiple servers with spaces.'),
    );

    // This is replica from Varnish module.
    $form['advanced_varnish_cache']['connection']['control_key'] = array(
      '#type' => 'textfield',
      '#title' => t('Control Key'),
      '#default_value' => $config->get('connection.control_key'),
      '#description' => t('Optional: if you have established a secret key for control terminal access, please put it here.'),
    );

    // This is replica from Varnish module.
    $form['advanced_varnish_cache']['connection']['socket_timeout'] = array(
      '#type' => 'textfield',
      '#title' => t('Connection timeout (milliseconds)'),
      '#default_value' => $config->get('connection.socket_timeout'),
      '#description' => t('If Varnish is running on a different server, you may need to increase this value.'),
      '#required' => TRUE,
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValue('advanced_varnish_cache');
    $this->config('advanced_varnish_cache.settings')
      ->set('connection.control_terminal', $values['connection']['control_terminal'])
      ->set('connection.control_key', $values['connection']['control_key'])
      ->set('connection.socket_timeout', $values['connection']['socket_timeout'])
      ->set('general.logging', $values['general']['logging'])
      ->set('general.debug', $values['general']['debug'])
      ->set('general.noise', $values['general']['noise'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
