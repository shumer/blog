<?php

/**
 * @file
 * Contains \Drupal\config_pages\ConfigPagesListBuilder.
 */

namespace Drupal\config_pages;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of custom config_pages entities.
 *
 * @see \Drupal\config_pages\Entity\ConfigPages
 */
class ConfigPagesListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = t('Name');
    $header['context'] = t('Context');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $this->getLabel($entity);

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
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    if (isset($operations['edit'])) {
      $operations['edit']['query']['destination'] = $entity->url('collection');
    }
    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function getOperations(EntityInterface $entity) {
    $operations = [];

    // Use user entry path if available for edit/add form page.
    $path = $entity->menu['path'];
    if (!empty($path)) {
      $operations['edit'] = [
        'title' => t('Edit'),
        'weight' => 10,
        'query' => [],
        'url' => Url::fromUserInput($path),
      ];
    }
    else {
      // Use default config page path in another case.
      $operations['edit'] = [
        'title' => t('Edit'),
        'weight' => 10,
        'query' => [],
        'url' => Url::fromRoute('config_pages.add_form', ['config_pages_type' => $entity->id()]),
      ];
    }
    uasort($operations, '\Drupal\Component\Utility\SortArray::sortByWeightElement');

    return $operations;
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    $entity_ids = $this->getEntityIds();
    return \Drupal::entityManager()
      ->getStorage('config_pages_type')->loadMultiple($entity_ids);
  }

  /**
   * Loads entity IDs using a pager sorted by the entity id.
   *
   * @return array
   *   An array of entity IDs.
   */
  protected function getEntityIds() {
    $query = \Drupal::entityManager()
      ->getStorage('config_pages_type')->getQuery();
    $keys = $this->entityType->getKeys();
    return $query
      ->sort($keys['id'])
      ->pager($this->limit)
      ->execute();
  }
}
