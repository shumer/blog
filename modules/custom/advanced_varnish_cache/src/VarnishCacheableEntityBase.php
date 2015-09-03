<?php

/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\VarnishCacheableEntityBase.
 */

namespace Drupal\advanced_varnish_cache;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\Entity;

class VarnishCacheableEntityBase extends PluginBase implements VarnishCacheableEntityInterface {

  protected $entity;

  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configuration = $configuration;
    $this->entity = $configuration['entity'];
  }

  public function generateSettingsKey() {
    $entity = $this->entity;
    $type = $entity->getEntityTypeId();
    $bundle = $entity->bundle();
    return 'entities_settings.' . $type . '.' . $bundle ;
  }
  
}
