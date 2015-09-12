<?php

/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\Plugin\VarnishCacheableEntity\Page.
 */

namespace Drupal\advanced_varnish_cache\Plugin\UserBlock;

use Drupal\advanced_varnish_cache\UserBlockBase;

/**
 * Provides a language config pages context.
 *
 * @UserBlock(
 *   id = "example",
 *   label = @Translation("Example"),
 * )
 */
class Example extends UserBlockBase {

  public static function content() {
    return 'Example';
  }

}
