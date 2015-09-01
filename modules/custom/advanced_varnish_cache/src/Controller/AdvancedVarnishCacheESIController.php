<?php

/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\Controller\AdvancedVarnishCacheESIController.
 */

namespace Drupal\advanced_varnish_cache\Controller;

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
    $response = new Response();

    // Block load.
    $block = \Drupal\block\Entity\Block::load($block_id);
    if ($block) {
      $settings = $block->get('settings');
      $ttl = $settings['cache']['max_age'];

      $tags = $block->getCacheTags();
      $tags = implode(';', $tags);

      // Mark this block as rendered through ESI request.
      $block->_esi = 1;

      // Render block.
      $build = \Drupal::entityManager()->getViewBuilder('block')
        ->view($block);
      $content = \Drupal::service('renderer')->render($build);
      $content .= date('H:i:s', time());
      $response->headers->set(AdvancedVarnishCache::ADVANCED_VARNISH_CACHE_X_TTL, $ttl);
      $response->headers->set(AdvancedVarnishCache::ADVANCED_VARNISH_CACHE_HEADER_CACHE_TAG, $tags);
    }

    // Set rendered block as response object content.
    $response->setContent($content);

    return $response;
  }

}
