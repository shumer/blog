<?php

/**
 * @file
 * Contains Drupal\background_changer\BackgroundSetListBuilder.
 */

namespace Drupal\background_changer;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of Background set entities.
 *
 * @ingroup background_changer
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
    /* @var $entity \Drupal\background_changer\Entity\BackgroundSet */
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
