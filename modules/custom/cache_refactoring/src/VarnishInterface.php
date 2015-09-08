<?php
/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\VarnishInterface.
 */

namespace Drupal\advanced_varnish_cache;


interface VarnishInterface {

  /**
   * Execute varnish command and get response.
   *
   * @param $client
   *
   * @param $command
   *
   * @return mixed
   */
  public function varnishExecuteCommand($client, $command);

  /**
   * Parse the host from the global $base_url.
   * @return string
   */
  public function varnishGetHost();

  /**
   * Get the status (up/down) of each of the varnish servers.
   *
   * @return array
   *    An array of server statuses, keyed by varnish terminal addresses.
   */
  public function varnishGetStatus();

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
  public function varnishReadSocket($client, $retry);

  /**
   * Sends commands to Varnish.
   * Utilizes sockets to talk to varnish terminal.
   *
   * @param mixed $commands
   *
   * @return array
   */
  public function varnishTerminalRun($commands);

}
