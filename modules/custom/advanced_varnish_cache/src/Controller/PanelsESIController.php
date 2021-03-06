<?php

/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\Controller\PanelsESIController.
 */

namespace Drupal\advanced_varnish_cache\Controller;

use Drupal\advanced_varnish_cache\Response\ESIResponse;
use Drupal\Core\Controller\ControllerBase;


class PanelsESIController extends ControllerBase {

  /**
   * Return rendered block html to replace esi tag.
   */
  public function content($page, $block_id){

    $response = new ESIResponse();

    $conf = \Drupal::config("page_manager.page.$page")->get('display_variants');

    foreach ($conf as $display_variant) {
      if (isset($display_variant['blocks'][$block_id])) {
        $block_conf = $display_variant['blocks'][$block_id];
      }
    }
    $block = \Drupal::service('plugin.manager.block')->createInstance($block_conf['id'], $block_conf);
    $build = $block->build();
    $content = \Drupal::service('renderer')->renderPlain($build);
    if ($block) {

      $ttl = $block_conf['cache']['max_age'];

      // Mark this block and response as rendered through ESI request.
      $block->_esi = 1;

      // Add block to dependency to respect block tags and ttl.
      $response->addCacheableDependency($block);
      $response->getCacheableMetadata()->setCacheMaxAge((int) $ttl);
    }

    $response->setContent($content);

    return $response;
  }

}

