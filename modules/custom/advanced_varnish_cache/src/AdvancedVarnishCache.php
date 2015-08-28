<?php
/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\AdvancedVarnishCache.
 */

namespace Drupal\advanced_varnish_cache;

use Drupal\Core\Logger\RfcLogLevel;

class AdvancedVarnishCache implements AdvancedVarnishCacheInterface
{
  // Set header name.
  const ADVANCED_VARNISH_CACHE_HEADER_CACHE_TAG = 'X-TAG';
  const ADVANCED_VARNISH_CACHE_HEADER_RNDPAGE = 'X-RNDPAGE';
  const ADVANCED_VARNISH_CACHE_HEADER_CACHE_DEBUG = 'X-CACHE-DEBUG';
  const ADVANCED_VARNISH_CACHE_COOKIE_BIN = 'AVCEBIN';
  const ADVANCED_VARNISH_CACHE_COOKIE_INF = 'AVCEINF';
  const ADVANCED_VARNISH_CACHE_X_TTL = 'X-TTL';

  public static $get_status_results;

  /**
   * Parse the host from the global $base_url.
   * @return string
   */
  public function varnish_get_host() {
    global $base_url;
    $parts = parse_url($base_url);
    return $parts['host'];
  }

  /**
   * Execute varnish command and get response.
   *
   * @param $client
   *
   * @param $command
   *
   * @return mixed
   */
  public function varnish_execute_command($client, $command) {
    // Send command and get response.
    $result = socket_write($client, "$command\n");
    $status = $this->varnish_read_socket($client);
    if ($status['code'] != 200) {
      \Drupal::logger('advanced_varnish_cache')->log(RfcLogLevel::ERROR, 'Recieved status code @code running %command. Full response text: @error', array(
              '@code' => $status['code'], '%command' => $command, '@error' => $status['msg'],
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
   */
  public function varnish_read_socket($client, $retry = 2) {
    // Status and length info is always 13 characters.
    $header = socket_read($client, 13, PHP_BINARY_READ);
    if ($header == FALSE) {
      $error = socket_last_error();
      // 35 = socket-unavailable, so it might be blocked from our write.
      // This is an acceptable place to retry.
      if ($error == 35 && $retry > 0) {
        return $this->varnish_read_socket($client, $retry-1);
      }
      else {
        \Drupal::logger('advanced_varnish_cache')->log(RfcLogLevel::ERROR, 'Socket error: @error', array('@error' => socket_strerror($error)));
        return array(
            'code' => $error,
            'msg' => socket_strerror($error),
        );
      }
    }
    $msg_len = (int)substr($header, 4, 6) + 1;
    $status = array(
        'code' => substr($header, 0, 3),
        'msg' => socket_read($client, $msg_len, PHP_BINARY_READ)
    );
    return $status;
  }

  /**
   * Sends commands to Varnish.
   * Utilizes sockets to talk to varnish terminal.
   *
   * @param mixed $commands
   *
   * @return array
   */
  public function varnish_terminal_run($commands) {
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
        $status = $this->varnish_read_socket($client);
        // Do we need to authenticate?
        if ($status['code'] == 107) { // Require authentication
          $secret = $this->getSetting('connection', 'control_key', '');
          $challenge = substr($status['msg'], 0, 32);
          $pack = $challenge . "\x0A" . $secret . "\x0A" . $challenge . "\x0A";
          $key = hash('sha256', $pack);
          socket_write($client, "auth $key\n");
          $status = $this->varnish_read_socket($client);
          if ($status['code'] != 200) {
            \Drupal::logger('advanced_varnish_cache')->error('Authentication to server failed!');
          }
        }
      }
      foreach ($commands as $command) {
        if ($status = $this->varnish_execute_command($client, $command)) {
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
  public function varnish_get_status() {
    // use a static-cache so this can be called repeatedly without incurring
    // socket-connects for each call.
    $results = (isset(self::$get_status_results)) ? self::$get_status_results : NULL;
    if (is_null($results)) {
      $results = array();
      $status = $this->varnish_terminal_run(array('status'));
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

    return !empty($settings->get($setting))
        ? $settings->get($setting)
        : $default;
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

}