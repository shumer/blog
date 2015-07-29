<?php

/**
 * @file
 * Contains \Drupal\config_pages\ConfigPagesAccessControlHandler.
 */

namespace Drupal\config_pages;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the access control handler for the config page entity type.
 *
 * @see \Drupal\config_pages\Entity\ConfigPages
 */
class ConfigPagesAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    if ($operation === 'view') {
      return AccessResult::allowed();
    }
    return parent::checkAccess($entity, $operation, $langcode, $account);
  }

}
