<?php

/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\Form\AdvancedVarnishCacheDeflateForm.
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
use Drupal\Core\Datetime\DateFormatter;

/**
 * Configure varnish settings for this site.
 */
class AdvancedVarnishCacheDeflateForm extends ConfigFormBase {

  /**
   * Stores the state storage service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;
  protected $varnish_handler;

  /**
   * Constructs a AdvancedVarnishCacheDeflateForm.php object.
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
    return 'advanced_varnish_cache_deflate';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['advanced_varnish_cache.deflate'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('advanced_varnish_cache.deflate');

    $form['advanced_varnish_cache'] = [
      '#tree' => TRUE,
    ];

    // Display module status.
    $backend_status = $this->varnish_handler->varnishGetStatus();

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

    // Step size.
    $options = array(
      '1' => '1%',
      '2' => '2%',
      '5' => '5%',
      '10' => '10%',
      '20' => '20%',
      '50' => '50%',
      '100' => '100',
    );
    $form['deflate'] = array(
      '#title' => t('Deflate cache'),
      '#type' => 'details',
      '#description' => t('Deflation is a process that will slowly invalidate all Varnish cache on cron runs.'),
      '#tree' => TRUE,
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );
    $form['deflate']['step'] = array(
      '#title' => t('Step size'),
      '#description' => t('Amount of cache that will be invalidated on each deflation step.'),
      '#type' => 'select',
      '#default_value' => '10',
      '#options' => $options,
    );
    $form['deflate']['start'] = array(
      '#type' => 'submit',
      '#value' => $this->t('Start defaltion'),
      '#button_type' => 'primary',
    );

    // By default, render the form using theme_system_config_form().
    $form['#theme'] = 'system_config_form';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $values = $form_state->getValue('advanced_varnish_cache');
    $this->config('advanced_varnish_cache.deflate')
      ->set('connection', $values['connection'])
      ->save();

    parent::submitForm($form, $form_state);
  }
}
