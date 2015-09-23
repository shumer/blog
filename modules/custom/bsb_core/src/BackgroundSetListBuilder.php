<?php

/**
 * @file
 * Contains Drupal\bsb_core\BackgroundSetListBuilder.
 */

namespace Drupal\bsb_core;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Background set entities.
 *
 * @ingroup bsb_core
 */
class BackgroundSetListBuilder extends EntityListBuilder {
  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['id'] = $this->t('Background set ID');
    $header['name'] = $this->t('Name');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    /* @var $entity \Drupal\bsb_core\Entity\BackgroundSet */
    $row['id'] = $entity->id();
    $row['name'] = \Drupal::l(
      $this->getLabel($entity),
      new Url(
        'entity.background_set.edit_form', array(
          'background_set' => $entity->id(),
        )
      )
    );
    return $row + parent::buildRow($entity);
  }

}
