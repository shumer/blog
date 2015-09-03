<?php

/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\Plugin\VarnishCacheableEntity\Page.
 */

namespace Drupal\advanced_varnish_cache\Plugin\VarnishCacheableEntity;

use Drupal\advanced_varnish_cache\VarnishCacheableEntityBase;

/**
 * Provides a language config pages context.
 *
 * @VarnishCacheableEntity(
 *   id = "node",
 *   label = @Translation("Node"),
 *   entity_type = "node",
 * )
 */
class Node extends VarnishCacheableEntityBase {

}
