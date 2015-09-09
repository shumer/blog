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

  public static $needsReload;

  /**
   * {@inheritdoc}
   *
   * @var AdvancedVarnishCacheInterface.
   */
  public $varnishHandler;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = array('handlePageRequest');
    return $events;
  }

  /**
   * Handle page request.
   *
   * @param FilterResponseEvent $event
   *   Event object.
   */
  public function handlePageRequest(FilterResponseEvent $event) {

    // Check if we on MasterRequest, also we never should cache POST requests.
    if (!$event->isMasterRequest() || !empty($_POST)) {
      return;
    }

    // Checking Varnish settings and define if we should work further.
    if (!$this->varnishHandler->cachingEnabled()) {
      return;
    }

    $params = \Drupal::routeMatch()->getParameters()->all();
    $entities = array_filter($params, function($param) {
      return ($param instanceof EntityInterface);
    });

    $config = \Drupal::config('advanced_varnish_cache.settings');

    $cache_settings = $this->getEntityCacheSettings($entities, $config);

    $response = $event->getResponse();

    $debug_mode = $config->get('general.debug');

    if ($debug_mode) {
      $response->headers->set($this->varnishHandler->getHeaderCacheDebug(), '1');
    }

    // Set headers.
    $response->headers->set($this->varnishHandler->getHeaderRndpage(), $this->uniqueId());

    // Validate existing cookies and update them if needed.
    $this->cookieUpdate();
    $needs_update = isset(self::$needsReload) ? self::$needsReload : FALSE;
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
$response->addCacheableDependency();
      $cacheable = $response->getCacheableMetadata();

      kpr($cacheable);
      $tags = $cacheable->getCacheTags();
      $tags = array_merge($tags, $cache_settings['tags']);
      $response->headers->set($this->varnishHandler->getHeaderCacheTag(), implode(';', $tags) . ';');

      // Set header with cache TTL based on site Performance settings.
      if (!isset($response->_esi)) {
        $response->headers->set($this->varnishHandler->getXTTL(), $cache_settings['ttl']);
      }
      $response->setPublic();

      // Set Etag to allow varnish deflate process.
      $response->setEtag(time());
    }
  }

  /**
   * Generated unique id based on time.
   *
   * @return string
   *   Unique id.
   */
  protected static function uniqueId() {
    $id = uniqid(time(), TRUE);
    return substr(md5($id), 5, 10);
  }

  /**
   * Updates cookie if required.
   */
  protected function cookieUpdate($account = '') {

    // Cookies may be disabled for resource files,
    // so no need to redirect in such a case.
    if ($this->redirectForbidden()) {
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
    if (empty($_COOKIE[$this->varnishHandler->getCookieBin()]) || ($_COOKIE[$this->varnishHandler->getCookieBin()] != $cookie_bin)) {

      // Update cookies.
      $params = session_get_cookie_params();
      $expire = $params['lifetime'] ? (REQUEST_TIME + $params['lifetime']) : 0;
      setcookie($this->varnishHandler->getCookieBin(), $cookie_bin, $expire, $params['path'], $params['domain'], FALSE, $params['httponly']);
      setcookie($this->varnishHandler->getCookieInf(), $cookie_inf, $expire, $params['path'], $params['domain'], FALSE, $params['httponly']);

      // Mark this page as required reload as ESI request
      // from this page will be sent with old cookie info.
      self::$needsReload = TRUE;
    }
    elseif (!empty($_GET['reload'])) {
      // Front asks us to do reload.
      self::$needsReload = TRUE;
    }

  }

  /**
   * Check if redirect enabled.
   *
   * Check if this page is allowed to redirect,
   * be default resource files should not be redirected.
   */
  public static function redirectForbidden($path = '') {

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

  /**
   * Specific entity cache settings getter.
   */
  public function getEntityCacheSettings($entities, $config) {
    $cache_settings = [
      'ttl' => '',
      'tags' => [],
    ];
    foreach ($entities as $entity) {
      $cache_key_generator = $this->varnishHandler->getCacheKeyGenerator($entity);
      $key = $cache_key_generator->generateSettingsKey();
      $cache_settings['ttl'] = empty($cache_settings['ttl']) ? $config->get($key)['cache_settings']['ttl'] : $cache_settings['ttl'];
      if ($config->get($key)['cache_settings']['purge_id']) {
        $cache_settings['tags'][] = $config->get($key)['cache_settings']['purge_id'];
      }
    }

    // If no ttl set check for custom rules settings.
    if (empty($cache_settings['ttl'])) {
      $config = \Drupal::config('advanced_varnish_cache.settings');

      // Get current path as default.
      $current_path = \Drupal::service('path.current')->getPath();
      $rules = explode(PHP_EOL, trim($config->get('custom.rules')));
      foreach ($rules as $line) {
        $conf = explode('|', trim($line));
        if (count($conf) == 3) {

          // Check for match.
          $path_matcher = \Drupal::service('path.matcher');
          $match = $path_matcher->matchPath($current_path, $conf[0]);
          if ($match) {
            $cache_settings['ttl'] = $conf[1];
            $cache_settings['tags'][] = $conf[2];
          }
        }
      }
    }

    // Use general TTL as fallback option.
    $cache_settings['ttl'] = $cache_settings['ttl'] ?: $config->get('general.page_cache_maximum_age');

    return $cache_settings;
  }

}
