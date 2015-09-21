<?php

/**
 * @file
 * Contains Drupal\bsb_core\BackgroundSetAccessControlHandler.
 */

namespace Drupal\bsb_core;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Background set entity.
 *
 * @see \Drupal\bsb_core\Entity\BackgroundSet.
 */
class BackgroundSetAccessControlHandler extends EntityAccessControlHandler {
  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {

    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view background set entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit background set entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete background set entities');
    }

    return AccessResult::allowed();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add background set entities');
  }

}
