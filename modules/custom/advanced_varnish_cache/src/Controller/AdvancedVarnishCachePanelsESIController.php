<?php

/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\Controller\AdvancedVarnishCachePanelsESIController.
 */

namespace Drupal\advanced_varnish_cache\Controller;

use Drupal\Core\Cache\CacheableResponse;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Drupal\advanced_varnish_cache\AdvancedVarnishCache;
use Symfony\Component\Validator\Constraints\DateTime;

class AdvancedVarnishCachePanelsESIController extends ControllerBase{

  /**
   * Return rendered block html to replace esi tag.
   */
  public function content($page, $block_id){
    $content = '';
    $response = new CacheableResponse();

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

      //$response->addCacheableDependency($block);

      // Mark this block and response as rendered through ESI request.
      $block->_esi = 1;
      $response->_esi = 1;

      $response->headers->set(AdvancedVarnishCache::ADVANCED_VARNISH_CACHE_X_TTL, $ttl);
    }
    $response->setContent($content);
    return $response;
    /*
    $response = new CacheableResponse();

    // Block load.
    $block = \Drupal\block\Entity\Block::load($block_id);
    if ($block) {
      $settings = $block->get('settings');
      $ttl = $settings['cache']['max_age'];

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
    */
  }

}
