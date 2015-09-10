<?php

/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\Plugin\Layout\CacheableLayout.
 */

namespace Drupal\advanced_varnish_cache\Plugin\Layout;

use Drupal\layout_plugin\Plugin\Layout\LayoutBase;
use \Drupal\Core\Render\Element;

/**
 * Cacheable layout plugin..
 */
class CacheableLayout extends LayoutBase {

  /**
   * Build regions.
   */
  public function build(array $regions) {

    $varnish = \Drupal::service('advanced_varnish_cache.handler');

    foreach (Element::children($regions) as $region_id) {
      $region = &$regions[$region_id];
      foreach (Element::children($region) as $block_id) {
        $block = &$region[$block_id];
        if (!empty($block['#configuration']['cache']['esi'])) {

          // If we need to replace block with ESI we
          // change #pre_render callback to handle this.
          $block['#theme'] = 'advanced_varnish_cache_esi_block';
          $block['#pre_render'] = '_advanced_varnish_cache_build_panels_esi_block';
        }
      }
    }
    return parent::build($regions);
  }

}
