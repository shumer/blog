<?php

/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\Plugin\UserBlock\Date.
 */

namespace Drupal\advanced_varnish_cache\Plugin\UserBlock;

use Drupal\advanced_varnish_cache\UserBlockBase;

/**
 * Provides a language config pages context.
 *
 * @UserBlock(
 *   id = "date",
 *   label = @Translation("Date"),
 * )
 */
class Date extends UserBlockBase {

  public static function content() {
    $user_data = (new \DateTime())->format('Y-m-d H:i:s');
    $selector = '.custom-div';
    return [$selector => $user_data];
  }

}
