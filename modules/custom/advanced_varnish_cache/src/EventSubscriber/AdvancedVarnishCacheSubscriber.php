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
use Drupal\advanced_varnish_cache\AdvancedVarnishCacheInterface;

class AdvancedVarnishCacheSubscriber implements EventSubscriberInterface {

  public static $needs_reload;
  public $varnish_handler;

  /**
   * {@inheritdoc}
   */
  static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = array('handlePageRequest');
    return $events;
  }

  public function handlePageRequest(FilterResponseEvent $event) {
    $account = \Drupal::currentUser();
    $authenticated = $account->isAuthenticated();
    if (!$event->isMasterRequest() || !empty($_POST) || $authenticated) {
      return;
    }

    $response = $event->getResponse();

    // Skip all if environment is not ready.
    if (!$this->ready()) {
      return;
    }
    $config = \Drupal::config('advanced_varnish_cache.settings');

    $debug_mode = $config->get('general.debug');

    if ($debug_mode) {
      $response->headers->set($this->varnish_handler->getHeaderCacheDebug(), '1');
    }

    // Set headers.
    $response->headers->set($this->varnish_handler->getHeaderRndpage(), $this->unique_id());

    // Validate existing cookies and update them if needed.
    $this->cookie_update();
    $needs_update = isset(self::$needs_reload) ? self::$needs_reload : FALSE;
    if ($needs_update) {

      // Setting cookie will prevent varnish from caching this.
      setcookie('time', time(), NULL, '/');

      $path = \Drupal::service('path.current')->getPath();
      $response = new RedirectResponse($path);
      $response->send();
      return;
    }

    // If there is no redirect set header with tags.
    if ($response instanceof CacheableResponseInterface) {
      $cacheable = $response->getCacheableMetadata();
      $tags = $cacheable->getCacheTags();
      $response->headers->set($this->varnish_handler->getHeaderCacheTag(), implode(';', $tags) . ';');

      // Add TTL header only for FE theme.
      $admin_theme_name = \Drupal::config('system.theme')->get('admin');
      $current_theme = \Drupal::theme()->getActiveTheme()->getName();
      if ($admin_theme_name == $current_theme) {
        return;
      }

      // Set headre with cache TTL based on site Perfomance settings.
      $site_ttl = \Drupal::config('system.performance')->get('cache.page.max_age');
      $response->headers->set($this->varnish_handler->getXTTL(), $site_ttl);
    }
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

  /**
   * Updates cookie if required.
   */
  protected function cookie_update($account = '') {

    // Cookies may be disabled for resource files,
    // so no need to redirect in such a case.
    if ($this->redirect_forbidden()) {
      return;
    }

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
    if (empty($_COOKIE[$this->varnish_handler->getCookieBin()]) || ($_COOKIE[$this->varnish_handler->getCookieBin()] != $cookie_bin)) {

      // Update cookies.
      $params = session_get_cookie_params();
      $expire = $params['lifetime'] ? (REQUEST_TIME + $params['lifetime']) : 0;
      setcookie($this->varnish_handler->getCookieBin(), $cookie_bin, $expire, $params['path'], $params['domain'], FALSE, $params['httponly']);
      setcookie($this->varnish_handler->getCookieInf(), $cookie_inf, $expire, $params['path'], $params['domain'], FALSE, $params['httponly']);

      // Mark this page as required reload as ESI request from this page will be sent with old cookie info.
      self::$needs_reload = TRUE;
    }
    elseif (!empty($_GET['reload'])) {
      // Front asks us to do reload.
      self::$needs_reload = TRUE;
    }

  }

  /**
   * Check if this page is allowed to redirect,
   * be default resource files should not be redirected.
   */
  public static function redirect_forbidden($path = '') {
    $forbidden = FALSE;

    $settings = \Drupal::config('advanced_varnish_cache.settings');

    if (!empty($_SESSION['advanced_varnish_cache__redirect_forbidden'])) {
      return TRUE;
    }
    elseif ($settings->get('redirect_forbidden')) {
      return TRUE;
    }
    elseif (!$settings->get('redirect_forbidden_no_cookie') && empty($_COOKIE)) {
      // This one is important as search engines don't have cookie support
      // and we don't want them to enter infinite loop.
      // Also images may have their cookies be stripped at Varnish level.
      return TRUE;
    }

    // Get current path as default.
    $current_path = \Drupal::service('path.current')->getPath();

    // By default ecxlude resource path.
    $path_to_exclude = [
      PublicStream::basePath(),
      PrivateStream::basePath(),
      \Drupal::config('system.file')->get('path.temporary'),
    ];
    $path_to_exclude = array_filter($path_to_exclude, 'trim');

    // Allow other modules to interfere.
    \Drupal::moduleHandler()->alter('advanced_varnish_cache_redirect_forbidden', $path_to_exclude, $path);

    // Check against excluded path.
    $forbidden = FALSE;
    foreach ($path_to_exclude as $exclude) {
      if (strpos($current_path, $exclude) === 0) {
        $forbidden = TRUE;
      }
    }

    return $forbidden;
  }

}
