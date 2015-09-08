<?php

/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\EventSubscriber\AdvancedVarnishCacheSubscriber.
 */

namespace Drupal\advanced_varnish_cache\EventSubscriber;

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
class AdvancedVarnishCacheSubscriber implements EventSubscriberInterface {

}
