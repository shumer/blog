<?php

/**
 * @file
 * Contains Drupal\background_changer\BackgroundSetInterface.
 */

namespace Drupal\background_changer;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Background set entities.
 *
 * @ingroup background_changer
 */
interface BackgroundSetInterface extends ContentEntityInterface, EntityOwnerInterface {

}
