<?php

/**
 * @file
 * Contains \Drupal\config_pages\Plugin\Block\ConfigPagesBlock.
 */

namespace Drupal\config_pages\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\config_pages\Entity\ConfigPages;
use Drupal\config_pages\Entity\ConfigPagesType;
use Drupal\Core\Config\Entity\Query\Query;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a generic ConfigPages block.
 *
 * @Block(
 *   id = "config_pages_block",
 *   admin_label = @Translation("ConfigPages Block"),
 * )
 */
class ConfigPagesBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $config = $this->getConfiguration();

    if (!empty($config['config_page_type'])) {
      $config_page = ConfigPages::config($config['config_page_type']);
      return $config_page->getCacheTags();
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    if (!empty($config['config_page_type'])) {
      $config_page = ConfigPages::config($config['config_page_type']);
      $view_mode = $config['config_page_view_mode'];
      $build = \Drupal::entityManager()->getViewBuilder('config_pages')
        ->view($config_page, $view_mode, NULL);
      return $build;
    }

    return array();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $settings = parent::defaultConfiguration();

    // Set custom cache settings.
    if (isset($this->pluginDefinition['cache'])) {
      $settings['cache'] = $this->pluginDefinition['cache'];
    }

    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public function getMachineNameSuggestion() {
    return 'config_pages';
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    // Get all available ConfigPages types and prepare options list.
    $config = $this->getConfiguration();
    $config_pages_types = ConfigPagesType::loadMultiple();
    $options = [];
    foreach ($config_pages_types as $cp_type) {
      $id = $cp_type->id();
      $label = $cp_type->label();
      $options[$id] = $label;
    }
    $form['config_page_type'] = [
      '#type' => 'select',
      '#title' => $this->t('Select ConfigPage type to show'),
      '#options' => $options,
      '#default_value' => isset($config['config_page_type']) ? $config['config_page_type'] : ''
    ];

    $view_modes = \Drupal::entityManager()->getViewModes('config_pages');
    $options = [];
    foreach ($view_modes as $id => $view_mode) {
      $options[$id] = $view_mode['label'];
    }
    // Get view modes.
    $form['config_page_view_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Select view mode for ConfigPage to show'),
      '#options' => $options,
      '#default_value' => isset($config['config_page_view_mode']) ? $config['config_page_view_mode'] : ''
    ];

    return $form;
  }


  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('config_page_type', $form_state->getValue('config_page_type'));
    $this->setConfigurationValue('config_page_view_mode', $form_state->getValue('config_page_view_mode'));
  }

}
