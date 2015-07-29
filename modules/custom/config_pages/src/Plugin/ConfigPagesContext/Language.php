<?php

/**
 * @file
 * Contains \Drupal\config_pages\Plugin\ConfigPagesContext\Language.
 */

namespace Drupal\config_pages\Plugin\ConfigPagesContext;

use Drupal\config_pages\ConfigPagesContextBase;

/**
 * Provides a language config pages context.
 *
 * @ConfigPagesContext(
 *   id = "language",
 *   label = @Translation("Language"),
 * )
 */
class Language extends ConfigPagesContextBase{

  /**
   * Return the value of the context.
   *
   * @return mixed
   */
  public static function getValue() {
    $lang = \Drupal::service('language_manager')->getCurrentLanguage();
    return $lang->getId();
  }

}
