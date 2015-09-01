<?php
/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\PageCache\DenyPageCache.
 */
namespace Drupal\advanced_varnish_cache\PageCache;

use Drupal\Core\PageCache\ResponsePolicyInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DenyPageCache implements ResponsePolicyInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Constructs a deny all page cache policy.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   */
  public function __construct(RouteMatchInterface $route_match) {
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public function check(Response $response, Request $request) {
      return static::DENY;
  }

}
