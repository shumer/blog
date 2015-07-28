<?php

/**
 * @file
 * Contains \Drupal\config_pages\ConfigPagesContextInterface.
 */

namespace Drupal\config_pages;

use Drupal\Component\Plugin\PluginInspectionInterface;

interface ConfigPagesContextInterface extends PluginInspectionInterface {

  /**
   * Return the label of the context.
   *
   * @return string
   */
  public function getLabel();

  /**
   * Return the value of the context.
   *
   * @return mixed
   */
  public static function getValue();

}
