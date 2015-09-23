<?php

/**
 * @file
 * Contains Drupal\bsb_core\BackgroundSetInterface.
 */

namespace Drupal\bsb_core;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Background set entities.
 *
 * @ingroup bsb_core
 */
interface BackgroundSetInterface extends ContentEntityInterface, EntityOwnerInterface {
  // Add get/set methods for your configuration properties here.

}
