<?php

/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\EventSubscriber\AdvancedVarnishCacheSubscriber.
 */

namespace Drupal\advanced_varnish_cache\EventSubscriber;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use \Symfony\Component\HttpFoundation\Response;

class AdvancedVarnishCacheSubscriber implements EventSubscriberInterface {

  // Set header name.
  const ADVANCED_VARNISH_CACHE_HEADER_RNDPAGE = 'X-RNDPAGE';

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = array('handlePageRequest');
    return $events;
  }

  public function handlePageRequest(GetResponseEvent $event) {

    $response = new Response();

    // Skip all if environment is not ready.
    if (!$this->ready()) {
      return;
    }

    // Set headers.
    $response->headers->set(self::ADVANCED_VARNISH_CACHE_HEADER_RNDPAGE, $this->unique_id());

    // Validate existing cookies and update them if needed.

    $response->send();
  }

  /**
   * Check if everything is ready for Varnish caching.
   *
   * @return bool
   */
  protected static function ready() {
    return (basename($_SERVER['PHP_SELF']) == 'index.php' && php_sapi_name() != 'cli');
  }

  /**
   * Generated unique id based on time.
   *
   * @return string
   */
  protected static function unique_id() {
    $id = uniqid(time(), TRUE);
    return substr(md5($id), 5, 10);
  }

  protected static function cookie_update() {
    
  }
}
