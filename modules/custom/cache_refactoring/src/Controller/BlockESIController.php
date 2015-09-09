<?php

/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\Controller\BlockESIController.
 */

namespace Drupal\advanced_varnish_cache\Controller;

use Drupal\advanced_varnish_cache\Response\ESIResponse;
use Drupal\Core\Cache\CacheableResponse;
use Drupal\Core\Controller\ControllerBase;

class BlockESIController extends ControllerBase {

  /**
   * Return rendered block html to replace esi tag.
   */
  public function content($block_id){
    $content = '';
    $response = new ESIResponse();

    // Block load.
    $block = \Drupal\block\Entity\Block::load($block_id);
    if ($block) {

      // Add block to dependency to respect block tags.
      $response->addCacheableDependency($block);

      // Check if block has special plugin and add it to dependency.
      $plugin = $block->getPlugin();
      if (is_object($plugin)) {
        $response->addCacheableDependency($plugin);
      }

      // Render block.
      $block->_esi = TRUE;
      $response->setEntity($block);

      $build = \Drupal::entityManager()->getViewBuilder('block')
        ->view($block);
      $content = \Drupal::service('renderer')->renderPlain($build);
    }

    // Set rendered block as response object content.
    $response->setContent($content);

    return $response;
  }

}
