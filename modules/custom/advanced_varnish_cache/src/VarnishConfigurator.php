<?php
/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\Varnish.
 */

namespace Drupal\advanced_varnish_cache;


use Drupal\Core\Config\ConfigFactoryInterface;

class VarnishConfigurator implements VarnishConfiguratorInterface {

  protected $config_factory;
  protected $config_name;

  public function __construct(ConfigFactoryInterface $config_factory, $config_name) {
    $this->config_factory = $config_factory;
    $this->config_name = $config_name;
  }

  public function get($setting_key) {
    return $this->config_factory->get($this->config_name)->get($setting_key);
  }

}