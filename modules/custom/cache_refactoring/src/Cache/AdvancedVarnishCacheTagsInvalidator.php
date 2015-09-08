<?php

/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\Cache\AdvancedVarnishCacheTagsInvalidator.
 */

namespace Drupal\advanced_varnish_cache\Cache;

use Drupal\advanced_varnish_cache\AdvancedVarnishCacheInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Logger\RfcLogLevel;


class AdvancedVarnishCacheTagsInvalidator implements CacheTagsInvalidatorInterface {

  public $varnishHandler;

  /**
   * Marks cache items with any of the specified tags as invalid.
   *
   * @param string[] $tags
   *   The list of tags for which to invalidate cache items.
   */
  public function invalidateTags(array $tags) {
    $this->varnishHandler->purgeTags($tags);
  }
}
