<?php

/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\Controller\UserBlocksESIController.
 */

namespace Drupal\advanced_varnish_cache\Controller;

use Drupal\advanced_varnish_cache\Response\ESIResponse;
use Drupal\Core\Controller\ControllerBase;

class UserBlocksESIController extends ControllerBase {

  /**
   * Return rendered block html to replace esi tag.
   */
  public function content($block_id){
    $content = '';
    $js_data = array();
    $response = new ESIResponse();
    $module_handler = \Drupal::moduleHandler();

    // Invoke hook to gather user data.
    $user_data = $module_handler->invokeAll('advanced_varnish_cache_page_ttl');
    kpr([$user_data]);die;
    // Set rendered block as response object content.
    $response->setContent($content);

    return $response;
  }

}
