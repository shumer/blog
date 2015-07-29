<?php

/**
 * @file
 * Contains \Drupal\config_pages\ConfigPagesTypeListBuilder.
 */

namespace Drupal\config_pages;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines a class to build a listing of custom config page type entities.
 *
 * @see \Drupal\config_pages\Entity\ConfigPagesType
 */
class ConfigPagesTypeListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    // Place the edit operation after the operations added by field_ui.module
    // which have the weights 15, 20, 25.
    if (isset($operations['edit'])) {
      $operations['edit']['weight'] = 30;
    }
    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['type'] = t('Typeype');
    $header['context'] = t('Context');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['type'] = $entity->link();

    // Used context.
    if (!empty($entity->context['group'])) {
      foreach ($entity->context['group'] as $context_id => $context_enabled) {
        if ($context_enabled) {
          $item = \Drupal::service('plugin.manager.config_pages_context')->getDefinition($context_id);
          $context_value = $item['label'];
          $contextData[] = $context_value;
        }
      }
    }
    $row['context'] = implode(', ', $contextData);
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  protected function getTitle() {
    return $this->t('Config page types');
  }

}
