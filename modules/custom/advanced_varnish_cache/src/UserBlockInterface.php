<?php

/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\UserBlockInterface.
 */

namespace Drupal\advanced_varnish_cache;

use Drupal\Component\Plugin\PluginInspectionInterface;

interface UserBlockInterface extends PluginInspectionInterface {

  public static function content();

}
