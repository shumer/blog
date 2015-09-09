<?php

/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\Controller\AdvancedVarnishCacheController.
 */

namespace Drupal\advanced_varnish_cache\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Main Varnish controller.
 *
 * Middleware stack controller to serve Cacheable response.
 */
class AdvancedVarnishCacheController {

  /**
   * @var AdvancedVarnishCacheInterface
   */
  public $varnishHandler;

  /**
   * @var string
   *   Unique id for current response.
   */
  protected $uniqueId;

  /**
   * @var bool
   */
  protected $needsReload;

  /**
   * @var CacheableResponseInterface
   */
  protected $response;

  /**
   * @var Request
   */
  protected $request;

  /**
   * Class constructor.
   *
   * @param AdvancedVarnishCacheInterface $varnishHandler
   *   Varnish handler object.
   *
   */
  public function __construct(VarnishInterface $varnishHandler, ConfigFactoryInterface $configFactory, RequestStack $request) {
    $this->varnishHandler = $varnishHandler;
    $this->configuration = $configFactory->get('advanced_varnish_cache.settings');
    $this->uniqueId = $this->uniqueId();
    $this->request = $request->getCurrentRequest();
  }

  /**
   * Response event handler.
   *
   * @param FilterResponseEvent $event
   *
   * Process CacheableResponse.
   */
  public function handleResponseEvent(FilterResponseEvent $event) {


    $this->response = $event->getResponse();

    if (!($this->response instanceof CacheableResponseInterface)) {
      return;
    }

    // Check if we on MasterRequest, also we never should cache POST requests.
    if (!$event->isMasterRequest() || !empty($_POST)) {
      return;
    }

    // Checking Varnish settings and define if we should work further.
    if (!$this->cachingEnabled()) {
      return;
    }

    $this->cookie_update();

    // Reload page with updated cookies if needed.
    $needs_update = isset($this->needsReload) ? $this->needsReload : FALSE;
    if ($needs_update) {
      $this->reload();
    }

    // Get affected entities.
    // Get entity specific settings
    // $cache_settings = $this->getCacheSettings($entities);
    //
    // Get Cacheable metadata
    // Merge metadata and settings tags
    // Choose ttl.

    // $this->setResponseHeaders();


  }

  /**
   * Set varnish specific response headers.
   */
  protected function setResponseHeaders() {

    $debug_mode = $this->configuration->get('general.debug');

    if ($debug_mode) {
      $this->response->headers->set(ADVANCED_VARNISH_CACHE_HEADER_CACHE_DEBUG, '1');
    }

    $grace = $this->configuration->get('general.grace');
    if ($grace) {
      $this->response->headers->set(ADVANCED_VARNISH_CACHE_HEADER_GRACE, $grace);
    }

    $this->response->headers->set(ADVANCED_VARNISH_CACHE_HEADER_RNDPAGE, $this->uniqueId());

    // $tags = ...
    $this->response->headers->set(ADVANCED_VARNISH_CACHE_HEADER_CACHE_TAG, implode(';', $tags) . ';');

    // $ttl = ...
    $this->response->headers->set(ADVANCED_VARNISH_CACHE_X_TTL, $cache_settings['ttl']);

    // Set this response to public as it cacheable so no private directive
    // should be present.
    $this->response->setPublic();

    // Set Etag to allow varnish deflate process.
    $this->response->setEtag(time());

  }

  /**
   * Reload page with updated cookies.
   */
  protected function reload() {

    // Setting cookie will prevent varnish from caching this.
    setcookie('time', time(), NULL, '/');

    $path = \Drupal::service('path.current')->getPath();
    $response = new RedirectResponse($path);
    $response->send();
    return;
  }


  /**
   * Generated unique id based on time.
   *
   * @return string
   *   Unique id.
   */
  protected function uniqueId() {
    $id = uniqid(time(), TRUE);
    return substr(md5($id), 5, 10);
  }

  /**
   * Updates cookie if required.
   */
  protected function cookieUpdate() {
    // Cookies may be disabled for resource files,
    // so no need to redirect in such a case.
    if ($this->redirectForbidden()) {
      return;
    }

    $account = \Drupal::currentUser();

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

    $noise = $this->configuration->get('general.noise') ?: '';

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
      $this->needsReload = TRUE;
    }
    elseif (!empty($_GET['reload'])) {
      // Front asks us to do reload.
      $this->needsReload = TRUE;
    }
  }

  /**
   * Check if redirect enabled.
   *
   * Check if this page is allowed to redirect,
   * be default resource files should not be redirected.
   */
  public function redirectForbidden($path = '') {

    if (!empty($_SESSION['advanced_varnish_cache__redirect_forbidden'])) {
      return TRUE;
    }
    elseif ($this->configuration->get('redirect_forbidden')) {
      return TRUE;
    }
    elseif (!$this->configuration->get('redirect_forbidden_no_cookie') && empty($_COOKIE)) {
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
      file_directory_temp(),
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
  public function getCacheSettings($entities) {

    $cacheable = $this->response->getCacheableMetadata();

    $cache_settings = [
      'ttl' => '',
      'tags' => [],
    ];
    foreach ($entities as $entity) {
      $cache_key_generator = $this->varnishHandler->getCacheKeyGenerator($entity);
      $key = $cache_key_generator->generateSettingsKey();
      $cache_settings['ttl'] = empty($cache_settings['ttl']) ? $this->configuration->get($key)['cache_settings']['ttl'] : $cache_settings['ttl'];
      if ($this->configuration->get($key)['cache_settings']['purge_id']) {
        $cache_settings['tags'][] = $this->configuration->get($key)['cache_settings']['purge_id'];
      }
    }

    // If no ttl set check for custom rules settings.
    if (empty($cache_settings['ttl'])) {

      // Get current path as default.
      $current_path = \Drupal::service('path.current')->getPath();
      $rules = explode(PHP_EOL, trim($this->configuration->get('custom.rules')));
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
    $cache_settings['ttl'] = $cache_settings['ttl'] ?: $this->configuration->get('general.page_cache_maximum_age');

    return $cache_settings;
  }

}
