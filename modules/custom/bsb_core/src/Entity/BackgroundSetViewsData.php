<?php

/**
 * @file
 * Contains Drupal\bsb_core\Entity\BackgroundSet.
 */

namespace Drupal\bsb_core\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Background set entities.
 */
class BackgroundSetViewsData extends EntityViewsData implements EntityViewsDataInterface {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['background_set']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Background set'),
      'help' => $this->t('The Background set ID.'),
    );

    return $data;
  }

}
