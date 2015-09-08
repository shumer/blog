<?php

/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\EventSubscriber\CacheableResponseSubscriber.
 */

namespace Drupal\advanced_varnish_cache\EventSubscriber;

use Drupal\advanced_varnish_cache\Controller\AdvancedVarnishCacheController;
use Drupal\Core\Annotation\Action;
use Drupal\Core\StreamWrapper\PrivateStream;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\Cache\CacheableResponseInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Core\Entity\EntityInterface;
use Drupal\advanced_varnish_cache\AdvancedVarnishCacheInterface;

/**
 * Event subscriber class.
 */
class CacheableResponseSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public function __construct(AdvancedVarnishCacheController $controller) {
    $this->controller = $controller;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = array('onResponse');
    return $events;
  }

  /**
   * Response event handler.
   *
   * @param FilterResponseEvent $event
   *
   * Process CacheableResponse.
   */
  public function onResponse(FilterResponseEvent $event) {
    $this->controller->handleResponseEvent($event);
  }

}
