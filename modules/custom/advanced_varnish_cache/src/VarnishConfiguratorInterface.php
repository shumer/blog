<?php
/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\VarnishConfiguratorInterface.
 */

namespace Drupal\advanced_varnish_cache;


interface VarnishConfiguratorInterface {

  public function get($setting_key);

}
