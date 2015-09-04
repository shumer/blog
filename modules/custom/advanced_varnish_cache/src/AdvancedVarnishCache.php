<?php
/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\AdvancedVarnishCache.
 */

namespace Drupal\advanced_varnish_cache;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Url;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;

/**
 * Main class provide basic methods to work with Varnish.
 */
class AdvancedVarnishCache implements AdvancedVarnishCacheInterface {

  // Set header name.
  const ADVANCED_VARNISH_CACHE_HEADER_CACHE_TAG = 'X-TAG';
  const ADVANCED_VARNISH_CACHE_HEADER_RNDPAGE = 'X-RNDPAGE';
  const ADVANCED_VARNISH_CACHE_HEADER_CACHE_DEBUG = 'X-CACHE-DEBUG';
  const ADVANCED_VARNISH_CACHE_COOKIE_BIN = 'AVCEBIN';
  const ADVANCED_VARNISH_CACHE_COOKIE_INF = 'AVCEINF';
  const ADVANCED_VARNISH_CACHE_X_TTL = 'X-TTL';
  const ADVANCED_VARNISH_CACHE_HEADER_ETAG = 'ETag';
  const ADVANCED_VARNISH_CACHE_HEADER_DEFLATE_KEY = 'X-DEFLATE-KEY';

  public static $getStatusResults;

  /**
   * Parse the host from the global $base_url.
   *
   * @return string
   *   Varnish host.
   */
  public function varnishGetHost() {
    global $base_url;
    $parts = parse_url($base_url);
    return $parts['host'];
  }

  /**
   * Execute varnish command and get response.
   *
   * @param string $client
   *   Terminal settings.
   * @param string $command
   *   Command line to execute.
   *
   * @return mixed
   *   Result of executed command.
   */
  public function varnishExecuteCommand($client, $command) {

    // Send command and get response.
    socket_write($client, "$command\n");
    $status = $this->varnishReadSocket($client);
    if ($status['code'] != 200) {
      \Drupal::logger('advanced_varnish_cache')->log(RfcLogLevel::ERROR, 'Received status code @code running %command. Full response text: @error', array(
          '@code' => $status['code'],
          '%command' => $command,
          '@error' => $status['msg'],
        )
      );
      return FALSE;
    }
    else {

      // Successful connection.
      return $status;
    }
  }

  /**
   * Low-level socket read function.
   *
   * @params
   *   $client an initialized socket client
   *
   *   $retry how many times to retry on "temporarily unavailable" errors.
   *
   * @return array
   *   Response array.
   */
  public function varnishReadSocket($client, $retry = 2) {
    // Status and length info is always 13 characters.
    $header = socket_read($client, 13, PHP_BINARY_READ);
    if ($header == FALSE) {
      $error = socket_last_error();
      // 35 = socket-unavailable, so it might be blocked from our write.
      // This is an acceptable place to retry.
      if ($error == 35 && $retry > 0) {
        return $this->varnishReadSocket($client, $retry - 1);
      }
      else {
        \Drupal::logger('advanced_varnish_cache')->log(RfcLogLevel::ERROR, 'Socket error: @error', array('@error' => socket_strerror($error)));
        return array(
          'code' => $error,
          'msg' => socket_strerror($error),
        );
      }
    }
    $msg_len = (int) substr($header, 4, 6) + 1;
    $status = array(
      'code' => substr($header, 0, 3),
      'msg' => socket_read($client, $msg_len, PHP_BINARY_READ),
    );
    return $status;
  }

  /**
   * Sends commands to Varnish.
   * Utilizes sockets to talk to varnish terminal.
   *
   * @param mixed $commands
   *    Array of commands to execute.
   * @return array
   *   Result status.
   */
  public function varnishTerminalRun($commands) {
    if (!extension_loaded('sockets')) {
      // Prevent fatal errors if people don't have requirements.
      return FALSE;
    }
    // Convert single commands to an array so we can handle everything in the same way.
    if (!is_array($commands)) {
      $commands = array($commands);
    }
    $ret = array();
    $terminals = explode(' ', $this->getSetting('connection', 'control_terminal', '127.0.0.1:6082'));
    // The variable varnish_socket_timeout defines the timeout in milliseconds.
    $timeout = $this->getSetting('connection', 'socket_timeout', 100);
    $seconds = (int)($timeout / 1000);
    $microseconds = (int)($timeout % 1000 * 1000);
    foreach ($terminals as $terminal) {
      list($server, $port) = explode(':', $terminal);
      $client = socket_create(AF_INET, SOCK_STREAM, getprotobyname('tcp'));
      socket_set_option($client, SOL_SOCKET, SO_SNDTIMEO, array('sec' => $seconds, 'usec' => $microseconds));
      socket_set_option($client, SOL_SOCKET, SO_RCVTIMEO, array('sec' => $seconds, 'usec' => $microseconds));
      if (@!socket_connect($client, $server, $port)) {
        \Drupal::logger('advanced_varnish_cache')->log(RfcLogLevel::ERROR, 'Unable to connect to server socket @server:@port: %error', array(
                '@server' => $server,
                '@port' => $port,
                '%error' => socket_strerror(socket_last_error($client))
            )
        );
        $ret[$terminal] = FALSE;
        // If a varnish server is unavailable, move on to the next in the list.
        continue;
      }
      // If there is a CLI banner message (varnish >= 2.1.x), try to read it and move on.
      $varnish_version = \Drupal::config('advanced_varnish_cache.settings')->get('varnish_version');
      if (!$varnish_version) {
        $varnish_version = 2.1;
      }
      if(floatval($varnish_version) > 2.0) {
        $status = $this->varnishReadSocket($client);
        // Do we need to authenticate?
        if ($status['code'] == 107) { // Require authentication
          $secret = $this->getSetting('connection', 'control_key', '');
          $challenge = substr($status['msg'], 0, 32);
          $pack = $challenge . "\x0A" . $secret . "\x0A" . $challenge . "\x0A";
          $key = hash('sha256', $pack);
          socket_write($client, "auth $key\n");
          $status = $this->varnishReadSocket($client);
          if ($status['code'] != 200) {
            \Drupal::logger('advanced_varnish_cache')->error('Authentication to server failed!');
          }
        }
      }
      foreach ($commands as $command) {
        if ($status = $this->varnishExecuteCommand($client, $command)) {
          $ret[$terminal][$command] = $status;
        }
      }
    }
    return $ret;
  }

  /**
   * Get the status (up/down) of each of the varnish servers.
   *
   * @return array
   *    An array of server statuses, keyed by varnish terminal addresses.
   */
  public function varnishGetStatus() {
    // use a static-cache so this can be called repeatedly without incurring
    // socket-connects for each call.
    $results = (isset(self::$getStatusResults)) ? self::$getStatusResults : NULL;
    if (is_null($results)) {
      $results = array();
      $status = $this->varnishTerminalRun(array('status'));
      $terminals = explode(' ', $this->getSetting('connection', 'control_terminal', '127.0.0.1:6082'));
      foreach ($terminals as $terminal) {
        $stat = array_shift($status);
        $results[$terminal] = ($stat['status']['code'] == 200);
      }
    }
    return $results;
  }

  /**
   * Return module settings.
   *
   * @param string $block
   *    Setting block
   * @param string $setting
   *    Setting key
   * @param string $default
   *    Default setting value
   *
   * @return mixed
   *    Setting value by key
   */
  public function getSetting($block, $setting, $default = NULL) {
    $settings = \Drupal::config('advanced_varnish_cache.settings');
    $setting = $block . '.' . $setting;

    $config = $settings->get($setting);
    $result = !empty($config)
      ? $config
      : $default;

    return $result;
  }

  /**
   * Get varnish handler settings.
   *
   * @return mixed
   */
  public function getCookieBin() {
    return self::ADVANCED_VARNISH_CACHE_COOKIE_BIN;
  }

  /**
   * Get varnish handler settings.
   *
   * @return mixed
   */
  public function getCookieInf() {
    return self::ADVANCED_VARNISH_CACHE_COOKIE_INF;
  }

  /**
   * Get varnish handler settings.
   *
   * @return mixed
   */
  public function getHeaderCacheDebug() {
    return self::ADVANCED_VARNISH_CACHE_HEADER_CACHE_DEBUG;
  }

  /**
   * Get varnish handler settings.
   *
   * @return mixed
   */
  public function getHeaderCacheTag() {
    return self::ADVANCED_VARNISH_CACHE_HEADER_CACHE_TAG;
  }

  /**
   * Get varnish handler settings.
   *
   * @return mixed
   */
  public function getHeaderRndpage() {
    return self::ADVANCED_VARNISH_CACHE_HEADER_RNDPAGE;
  }

  /**
   * Get varnish handler settings.
   *
   * @return mixed
   */
  public function getXTTL() {
    return self::ADVANCED_VARNISH_CACHE_X_TTL;
  }

  /**
   * Define if caching enabled for this page and we can proceed with this request.
   *
   * @return bool.
   */
  public static function cachingEnabled() {
    $enabled = TRUE;
    $config = \Drupal::config('advanced_varnish_cache.settings');

    // Skip all if environment is not ready.
    if (!self::ready()) {
      $enabled = FALSE;
    }

    // Check if user is authenticated and we can use cache for such users.
    $account = \Drupal::currentUser();
    $authenticated = $account->isAuthenticated();
    $cache_authenticated = $config->get('available.authenticated_users');
    if ($authenticated && !$cache_authenticated) {
      $enabled = FALSE;
    }

    // Check if user has permission to bypass varnish.
    if ($account->hasPermission('bypass advanced varnish cache')) {
      $enabled = FALSE;
    }

    // Check if we in admin theme and if we allow to cache this page.
    $admin_theme_name = \Drupal::config('system.theme')->get('admin');
    $current_theme = \Drupal::theme()->getActiveTheme()->getName();
    $cache_admin_theme = $config->get('available.admin_theme');
    if ($admin_theme_name == $current_theme && !$cache_admin_theme) {
      $enabled = FALSE;
    }

    // Check if we on https and if we can to cache page.
    $https_cache_enabled = $config->get('available.https');
    $https = \Drupal::request()->isSecure();
    if ($https && !$https_cache_enabled) {
      $enabled = FALSE;
    }

    // Check if we acn be on disabled domain.
    $config = explode(PHP_EOL, $config->get('available.exclude'));
    foreach ($config as $line) {
      $rule = explode('|', trim($line));
      if (($rule[0] == '*') || ($_SERVER['SERVER_NAME'] == $rule[0])) {
        if (($rule[1] == '*') || strpos($_SERVER['REQUEST_URI'], $rule[1]) === 0) {
          $enabled = FALSE;
          break;
        }
      }
    }

    return $enabled;
  }

  /**
   * Check if everything is ready for Varnish caching.
   *
   * @return bool
   */
  public static function ready() {
    return (basename($_SERVER['PHP_SELF']) == 'index.php' && php_sapi_name() != 'cli');
  }

  /**
   * #pre_render callback for building a ESI block.
   *
   * Replace block content with ESI tag.
   */
  public function buildEsiBlock($build) {
    $id = $build['#block']->id();

    // Remove the block entity from the render array, to ensure that blocks
    // can be rendered without the block config entity.
    unset($build['#block']);

    $maxwait = 5000;
    $path = '/advanced_varnish_cache/esi/block/' . $id;
    $url = Url::fromUserInput($path);
    $content = "<!--esi\n" . '<esi:include src="' . $url->toString()  . '" maxwait="' . $maxwait . '"/>' . "\n-->";

    $build['#content'] = $content;

    // Set flag for varnish that we have ESI in the response.
    $build['#attached']['http_header'] = [
      ['X-DOESI', '1'],
    ];

    return $build;
  }

  /**
   * #pre_render callback for building a ESI block.
   *
   * Replace block content with ESI tag.
   */
  public function buildPanelsEsiBlock($build) {
    $route = \Drupal::request()->get(RouteObjectInterface::ROUTE_OBJECT);
    $defaults= $route->getDefaults();

    $page = $defaults['page_manager_page'];

    $conf = $build['#configuration'];
    $block_id = $conf['uuid'];

    $maxwait = 5000;
    $path = '/advanced_varnish_cache/esi/block/' . $page . '/' . $block_id;
    $url = Url::fromUserInput($path);
    $content = "<!--esi\n" . '<esi:include src="' . $url->toString()  . '" maxwait="' . $maxwait . '"/>' . "\n-->";

    $build['#content'] = $content;

    // Set flag for varnish that we have ESI in the response.
    $build['#attached']['http_header'] = [
        ['X-DOESI', '1'],
    ];

    return $build;
  }

  /**
   * Purge varnish cache for specific tag.
   *   *
   * @param $tag
   *   (string/array) tag to search and purge.
   *
   * @return array
   */
  public function purgeTags($tag) {
    $account = \Drupal::currentUser();
    $header = $this->getHeaderCacheTag();

    // Build pattern.
    $pattern = (count($tag) > 1)
        ? implode(';|', $tag) . ';'
        : reset($tag) . ';';

    // Remove quotes from pattern.
    $pattern = strtr($pattern, array('"' => '', "'" => ''));

    // Clean all or only current host.
    if ($this->getSetting('purge', 'all_hosts', TRUE)) {
      $command_line = "ban obj.http.$header ~ \"$pattern\"";
    }
    else {
      $host = $this->varnishGetHost();
      $command_line = "ban req.http.host ~ $host && obj.http.$header ~ \"$pattern\"";
    }

    // Log action.
    if ($this->getSetting('general', 'logging', FALSE)) {
      \Drupal::logger('advanced_varnish_cache')->log(RfcLogLevel::DEBUG, 'u=@uid purge !command_line', [
          '@uid' => $account->id(),
          '!command_line' => $command_line,
        ]
      );
    }

    // Query Varnish.
    $res = $this->varnishTerminalRun(array($command_line));
    return $res;
  }

  /**
   * Purge varnish cache for specific request, like '/sites/all/files/1.txt';
   *
   * @param $pattern
   *   (string/array) list of tags to search and purge.
   * @param $exact
   *   (bool) specify if pattern regex or exact match string.
   *
   * @return array
   */
  function purgeRequest($pattern, $exact = FALSE) {

    $account = \Drupal::currentUser();

    // Remove quotes from pattern.
    $pattern = strtr($pattern, array('"' => '', "'" => ''));
    $command = !empty($exact) ? '==' : '~';

    // Clean all or only current host.
    if ($this->getSetting('purge', 'all_hosts', TRUE)) {
      $command_line = "ban req.url $command \"$pattern\"";
    }
    else {
      $host = $this->varnishGetHost();
      $command_line = "ban req.http.host ~ $host && req.url $command \"$pattern\"";
    }

    // Log action.
    if ($this->getSetting('general', 'logging', FALSE)) {
      $message = t('u=@uid purge !command_line', [
        '@uid' => $account->id(),
        '!command_line' => $command_line,
      ]);
      \Drupal::logger('advanced_varnish_cache:purge')->notice($message);
    }

    // Query Varnish.
    $res = $this->varnishTerminalRun(array($command_line));
    return $res;
  }

  /**
   * Submit callback for panels page edit form
   */
  public function panelsSettingsSubmit($form, \Drupal\Core\Form\FormStateInterface $form_state) {
    $build = $form_state->getBuildInfo();
    $cache_settings = $form_state->getValue('cache_setting');

    $page = $build['callback_object']->getPage();
    $display_varaint = $build['callback_object']->getDisplayVariant();
    $page_id = $page->id();
    $type = $page->getEntityTypeId();
    $display_varaint_id = $display_varaint->id();

    $configFactory = \Drupal::service('config.factory');
    $config = $configFactory->getEditable('advanced_varnish_cache.settings');

    $key = implode('.', ['entities_settings', $type, $page_id, $display_varaint_id]);
    $config->set($key, ['cache_settings' => $cache_settings]);
    $config->save();
  }

  /**
   * Get registered entities for which ttl could be configured.
   *   per bundle basis.
   *
   * @return array
   */
  public function getVarnishCacheableEntities() {
    $plugins = \Drupal::service('plugin.manager.varnish_cacheable_entity')->getDefinitions();
    $return = [];
    foreach ($plugins as $plugin) {
      if ($plugin['per_bundle_settings']) {
        $return[] = $plugin['entity_type'];
      }
    }
    return $return;
  }

  /**
   * Purge varnish cache for specific request, like '/sites/all/files/1.txt';
   *
   * @param $entity
   *   EntityInterface
   * @param $options
   *   (array) options array
   *
   * @return \Drupal\advanced_varnish_cache\VarnishCacheableEntityInterface
   */
  public function getCacheKeyGenerator(EntityInterface $entity, array $options = []) {
    return \Drupal::service('plugin.manager.varnish_cacheable_entity')->createInstance($entity->getEntityTypeId(), ['entity' => $entity, 'displayVariant' => $options['displayVariant']]);
  }
}
