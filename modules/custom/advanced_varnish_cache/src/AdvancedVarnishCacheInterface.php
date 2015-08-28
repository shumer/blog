<?php
/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\AdvancedVarnishCacheInterface.
 */

namespace Drupal\advanced_varnish_cache;


interface AdvancedVarnishCacheInterface
{

  /**
   * Get varnish handler settings.
   *
   * @return mixed
   */
  public function getCookieBin();

  /**
   * Get varnish handler settings.
   *
   * @return mixed
   */
  public function getCookieInf();

  /**
   * Get varnish handler settings.
   *
   * @return mixed
   */
  public function getHeaderCacheDebug();

  /**
   * Get varnish handler settings.
   *
   * @return mixed
   */
  public function getHeaderCacheTag();

  /**
   * Get varnish handler settings.
   *
   * @return mixed
   */
  public function getHeaderRndpage();

  /**
   * Get varnish handler settings.
   *
   * @return mixed
   */
  public function getXTTL();

  /**
   * Execute varnish command and get response.
   *
   * @param $client
   *
   * @param $command
   *
   * @return mixed
   */
  public function varnish_execute_command($client, $command);

  /**
   * Parse the host from the global $base_url.
   * @return string
   */
  public function varnish_get_host();

  /**
   * Get the status (up/down) of each of the varnish servers.
   *
   * @return array
   *    An array of server statuses, keyed by varnish terminal addresses.
   */
  public function varnish_get_status();

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
  public function varnish_read_socket($client, $retry);

  /**
   * Sends commands to Varnish.
   * Utilizes sockets to talk to varnish terminal.
   *
   * @param mixed $commands
   *
   * @return array
   */
  public function varnish_terminal_run($commands);

}
