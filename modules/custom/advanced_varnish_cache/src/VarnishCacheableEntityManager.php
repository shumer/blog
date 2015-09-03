<?php

/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\VarnishCacheableEntityManager.
 */

namespace Drupal\advanced_varnish_cache;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

class VarnishCacheableEntityManager extends DefaultPluginManager {

  /**
   * Constructs an ConfigPagesContextManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations,
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/VarnishCacheableEntity', $namespaces, $module_handler, '\Drupal\advanced_varnish_cache\VarnishCacheableEntityInterface', 'Drupal\advanced_varnish_cache\Annotation\VarnishCacheableEntity');

    $this->alterInfo('advanced_varnish_cache_varnish_cacheable_entity');
    $this->setCacheBackend($cache_backend, 'varnish_cacheable_entity');
  }
}
