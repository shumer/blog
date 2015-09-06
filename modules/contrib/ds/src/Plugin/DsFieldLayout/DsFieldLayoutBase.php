<?php

/**
 * @file
 * Contains \Drupal\ds\Plugin\DsFieldLayout\DsFieldLayoutBase.
 */

namespace Drupal\ds\Plugin\DsFieldLayout;

use Drupal\Component\Plugin\PluginBase as ComponentPluginBase;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Entity\EntityInterface;
use Drupal\ds\Ds;

/**
 * Base class for all the ds plugins.
 */
abstract class DsFieldLayoutBase extends ComponentPluginBase implements DsFieldLayoutInterface {

  /**
   * Enables default sanitizing on the output of the fields.
   *
   * @var bool
   */
  protected $sanitize = TRUE;

  /**
   * The entity used for token replacement.
   *
   * @var EntityInterface
   */
  protected $entity = NULL;

  /**
   * Constructs a Display Suite field plugin.
   */
  public function __construct($configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configuration += $this->defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setEntity(EntityInterface $entity) {
    $this->entity = $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function alterForm(&$form) {
    $config = $this->getConfiguration();

    $form['lb'] = array(
      '#type' => 'textfield',
      '#title' => t('Label'),
      '#size' => '10',
      '#default_value' => SafeMarkup::checkPlain($config['lb']),
    );
    $form['lb-col'] = array(
      '#type' => 'checkbox',
      '#title' => t('Show label colon'),
      '#default_value' => $config['lb-col'],
      '#attributes' => array(
        'class' => array('colon-checkbox'),
      ),
    );
    $field_classes = Ds::getClasses('field');

    if (!empty($field_classes)) {
      $form['classes'] = array(
        '#type' => 'select',
        '#multiple' => TRUE,
        '#options' => $field_classes,
        '#title' => t('Choose additional CSS classes for the field'),
        '#default_value' => $config['classes'],
        '#prefix' => '<div class="field-classes">',
        '#suffix' => '</div>',
      );
    }
    else {
      $form['classes'] = array(
        '#type' => 'value',
        '#value' => array(''),
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function massageRenderValues(&$field_settings, $values) {
    if (!empty($values['lb'])) {
      $field_settings['lb'] = $values['lb'];
    }
    if (!(empty($values['lb-col']))) {
      $field_settings['lb-col'] = TRUE;
    }
    if (isset($values['classes'])) {
      $classes = is_array($values['classes']) ? implode(' ', $values['classes']) : $values['classes'];
      if (!empty($classes)) {
        $field_settings['classes'] = $classes;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function sanitizeRenderValues(&$field_settings) {
    if (isset($this->sanitize) && $this->sanitize) {
      foreach ($field_settings as &$setting) {
        if (is_string($setting)) {
          $setting = Xss::filter($setting);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getThemeFunction() {
    return $this->pluginDefinition['theme'];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = array();
    $config['lb'] = '';
    $config['lb-col'] = \Drupal::config('ds.settings')->get('ft-show-colon');
    $config['classes'] = array();

    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration + $this->configuration;
  }

}
