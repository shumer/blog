<?php

/**
 * @file
 * Contains \Drupal\search_autocomplete\AutocompletionConfigurationAccessControlHandler.
 *
 * Sponsored by: www.drupal-addict.com
 */

namespace Drupal\search_autocomplete;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines an access controller for the autocompletion_configuration entity.
 *
 * We set this class to be the access controller in Robot's entity annotation.
 *
 * @see \Drupal\search_autocomplete\Entity\Robot
 *
 * @ingroup search_autocomplete
 */
class AutocompletionConfigurationAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  public function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    // The $opereration parameter tells you what sort of operation access is
    // being checked for.
    if ($operation == 'view') {
      return TRUE;
    }
    // Other than the view operation, we're going to be insanely lax about
    // access. Don't try this at home!
    return parent::checkAccess($entity, $operation, $langcode, $account);
  }

}
