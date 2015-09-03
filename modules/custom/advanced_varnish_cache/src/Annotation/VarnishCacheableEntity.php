<?php

/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\Annotation\VarnishCacheableEntity.
 */

namespace Drupal\advanced_varnish_cache\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines cacheable entity item annotation object.
 *
 * Plugin Namespace: Plugin\advanced_varnish_cache\VarnishCacheableEntity
 *
 * @see \Drupal\config_pages\Plugin\IcecreamManager
 * @see plugin_api
 *
 * @Annotation
 */
class VarnishCacheableEntity extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * Entity id.
   *
   * @var string
   */
  public $entityId;

  /**
   * Has bundles.
   *
   * @var boolean
   */
  public $perBunleSettings;


}
