<?php

/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\Form\AdvancedVarnishCacheSettingsForm.
 */

namespace Drupal\advanced_varnish_cache\Form;

use Drupal\advanced_varnish_cache\AdvancedVarnishCacheInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\ConfigEntityStorage;
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
class AdvancedVarnishEntityCacheSettingsForm extends ConfigFormBase {

  /**
   * Stores the state storage service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;
  protected $varnishHandler;

  /**
   * Constructs a AdvancedVarnishCacheSettingsForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state key value store.
   */
  public function __construct(ConfigFactoryInterface $config_factory, StateInterface $state, AdvancedVarnishCacheInterface $varnish_handler, DateFormatter $date_formatter) {
    parent::__construct($config_factory);
    $this->state = $state;
    $this->varnishHandler = $varnish_handler;
    $this->dateFormatter = $date_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('state'),
      $container->get('advanced_varnish_cache_handler'),
      $container->get('date.formatter')
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

    $form['advanced_varnish_cache']['entities_settings'] = [
      '#tree' => TRUE,
    ];

    $entity_types = $this->varnishHandler->getVarnishCacheableEntities();

    foreach ($entity_types as $type) {

      $bundles = \Drupal::entityManager()->getBundleInfo($type);

      if (!empty($bundles)) {
        $form['advanced_varnish_cache']['entities_settings'][$type] = [
          '#tree' => TRUE,
          '#type' => 'details',
          '#title' => $type,
        ];

        foreach ($bundles as $machine_name => $info) {

          // Cache time for Varnish.
          $period = array(0, 60, 180, 300, 600, 900, 1800, 2700,
            3600, 10800, 21600, 32400, 43200, 86400,
          );
          $period = array_map(array($this->dateFormatter, 'formatInterval'), array_combine($period, $period));
          $period[0] = t('no caching');
          $form['advanced_varnish_cache']['entities_settings'][$type][$machine_name]['cache_settings']['ttl'] = array(
            '#type' => 'select',
            '#title' => t('@bundle', ['@bundle' => $info['label']]),
            '#default_value' => $config->get('entities_settings.' . $type . '.' . $machine_name)['cache_settings']['ttl'],
            '#options' => $period,
            '#description' => t('The maximum time a page can be cached by varnish.'),
          );
        }
      }
    }

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValue('entities_settings');

    $this->config('advanced_varnish_cache.settings')
      ->merge(['entities_settings' => $values])
      ->save();

    parent::submitForm($form, $form_state);
  }

}
