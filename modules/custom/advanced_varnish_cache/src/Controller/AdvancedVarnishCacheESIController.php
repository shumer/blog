<?php

/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\Controller\AdvancedVarnishCacheESIController.
 */

namespace Drupal\advanced_varnish_cache\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

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
      $build = \Drupal::entityManager()->getViewBuilder('block')
          ->view($block);
      $content = \Drupal::service('renderer')->render($build);
    }

    // Set rendered block as response object content.
    $response->setContent($content);
    return $response;
  }
}
