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
 *   id = "page",
 *   label = @Translation("Page"),
 *   entity_type = "page",
 * )
 */
class Page extends VarnishCacheableEntityBase {

  protected $displayVariant;

  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    if ($this->entity instanceof \Drupal\page_manager\Entity\Page) {
      $executable = $this->entity->getExecutable();
      if ($executable) {
        $displayVariant = $executable->selectDisplayVariant()->id();
      }
      $this->displayVariant = $configuration['displayVariant'] ?: $displayVariant;
    }
  }

  public function generateSettingsKey() {
    $displayVariant =  $this->displayVariant ?: '';
    $page = $this->entity;
    $type = $page->getEntityTypeId();
    $id = $page->id();

    return $displayVariant ? 'entities_settings.' . $type . '.' . $id . '.' . $displayVariant : '';
  }

}
