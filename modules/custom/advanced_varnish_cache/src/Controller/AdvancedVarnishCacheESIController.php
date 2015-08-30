<?php

/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\Controller\AdvancedVarnishCacheESIController.
 */

namespace Drupal\advanced_varnish_cache\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Drupal\advanced_varnish_cache\AdvancedVarnishCache;

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

      $build = \Drupal::entityManager()->getViewBuilder('block')
        ->view($block);
      $content = \Drupal::service('renderer')->render($build);

      $response->headers->set(AdvancedVarnishCache::ADVANCED_VARNISH_CACHE_X_TTL, $ttl);
      $response->headers->set(AdvancedVarnishCache::ADVANCED_VARNISH_CACHE_HEADER_CACHE_TAG, $tags);
    }

    // Set rendered block as response object content.
    $response->setContent($content);
    $this->cookie_update();
    return $response;
  }

  /**
   * Updates cookie for ESI.
   */
  protected function cookie_update($account = '') {

    $varnish_handler = new  AdvancedVarnishCache();

    $config = \Drupal::config('advanced_varnish_cache.settings');
    $account = $account ?: \Drupal::currentUser();

    // If user should bypass varnish we must set per user bin.
    if ($account->hasPermission('bypass advanced varnish cache')) {
      $bin = 'u' . $account->id();
    }
    elseif ($account->id() > 0) {
      $roles = $account->getRoles();
      sort($roles);
      $bin = implode('__', $roles);
    }
    else {
      // Bin for anonym user.
      $bin = '0';
    }
    $cookie_inf = $bin;

    $noise = $config->get('general.noise') ?: '';

    // Allow other modules to interfere.
    \Drupal::moduleHandler()->alter('advanced_varnish_cache_user_cache_bin', $cookie_inf, $account);

    // Hash bin (PER_ROLE-PER_PAGE).
    $cookie_bin = hash('sha256', $cookie_inf . $noise) . '-' . hash('sha256', $noise);

    // Update cookies if did not match.
    if (empty($_COOKIE[$varnish_handler->getCookieBin()]) || ($_COOKIE[$varnish_handler->getCookieBin()] != $cookie_bin)) {

      // Update cookies.
      $params = session_get_cookie_params();
      $expire = $params['lifetime'] ? (REQUEST_TIME + $params['lifetime']) : 0;
      setcookie($varnish_handler->getCookieBin(), $cookie_bin, $expire, $params['path'], $params['domain'], FALSE, $params['httponly']);
      setcookie($varnish_handler->getCookieInf(), $cookie_inf, $expire, $params['path'], $params['domain'], FALSE, $params['httponly']);
    }
  }
}
