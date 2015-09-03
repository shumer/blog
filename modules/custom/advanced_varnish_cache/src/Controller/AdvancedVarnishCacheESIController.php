<?php

/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\Controller\AdvancedVarnishCacheESIController.
 */

namespace Drupal\advanced_varnish_cache\Controller;

use Drupal\Core\Cache\CacheableResponse;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Drupal\advanced_varnish_cache\AdvancedVarnishCache;
use Symfony\Component\Validator\Constraints\DateTime;

class AdvancedVarnishCacheESIController extends ControllerBase{

  /**
   * Return rendered block html to replace esi tag.
   */
  public function content($block_id){
    $content = '';
    $response = new CacheableResponse();

    // Block load.
    $block = \Drupal\block\Entity\Block::load($block_id);
    if ($block) {
      $settings = $block->get('settings');
      $ttl = $settings['cache']['max_age'];

      // Add block to dependency to respect block tags.
      $response->addCacheableDependency($block);

      // Check if block has special plugin and add it to dependency.
      $plugin = $block->getPlugin();
      if (is_object($plugin)) {
        $response->addCacheableDependency($plugin);
      }

      // Mark this block and response as rendered through ESI request.
      $block->_esi = 1;
      $response->_esi = 1;

      // Render block.
      $build = \Drupal::entityManager()->getViewBuilder('block')
        ->view($block);
      $content = \Drupal::service('renderer')->renderPlain($build);
      $response->headers->set(AdvancedVarnishCache::ADVANCED_VARNISH_CACHE_X_TTL, $ttl);
    }

    // Set rendered block as response object content.
    $response->setContent($content);

    return $response;
  }

}
