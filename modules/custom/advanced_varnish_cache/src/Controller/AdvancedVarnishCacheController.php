<?php

/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\Controller\AdvancedVarnishCacheController.
 */

namespace Drupal\advanced_varnish_cache\Controller;


use Drupal\Core\Controller\ControllerBase;

class AdvancedVarnishCacheController {

  /**
   * @var
   *   Static var with class instance.
   */
  protected static $_instance;

  public $rnd_page;

  /**
   * Make __construct private to use singletone class instance.
   */
  private function __construct(){
    $this->rnd_page = $this->unique_id();
  }

  /**
   * Make __clone private to use singletone class instance.
   */
  private function __clone(){

  }

  /**
   * Return class instance.
   * @return AdvancedVarnishCacheController
   */
  public static function getInstance() {

    if (null === self::$_instance) {
      self::$_instance = new self();
    }

    return self::$_instance;
  }
}
