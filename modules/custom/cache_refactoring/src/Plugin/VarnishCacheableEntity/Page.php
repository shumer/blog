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
 *   per_bundle_settings = 0
 * )
 */
class Page extends VarnishCacheableEntityBase {

  protected $displayVariant;

  /**
   * Class constructor.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    if ($this->entity instanceof \Drupal\page_manager\Entity\Page) {
      if (empty($display_variant = $configuration['displayVariant'])) {
        $executable = $this->entity->getExecutable();
        if ($executable) {
          $display_variant = $executable->selectDisplayVariant()->id();
        }
      }
      $this->displayVariant = $display_variant;
    }
  }

  /**
   * Generate a entity cache key.
   */
  public function generateSettingsKey() {
    $display_variant = $this->displayVariant ?: '';
    $page = $this->entity;
    $type = $page->getEntityTypeId();
    $id = $page->id();

    return $display_variant ? 'entities_settings.' . $type . '.' . $id . '.' . $display_variant : '';
  }

}
