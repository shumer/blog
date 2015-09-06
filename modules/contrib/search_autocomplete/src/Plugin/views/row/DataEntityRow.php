<?php

/**
 * @file
 * Contains \Drupal\search_autocomplete\Plugin\views\row\DataEntityRow.
 *
 * Inspired by rest core module.
 * Sponsored by: www.drupal-addict.com
 */

namespace Drupal\rest\Plugin\views\row;

use Drupal\views\ViewExecutable;
use Drupal\views\Plugin\views\row\RowPluginBase;

/**
 * Plugin which displays entities as raw data.
 *
 * @ingroup views_row_plugins
 *
 * @ViewsRow(
 *   id = "callback_entity",
 *   title = @Translation("Entity"),
 *   help = @Translation("Use entities as row data."),
 *   display_types = {"callback"}
 * )
 */
class DataEntityRow extends RowPluginBase {

  /**
   * Overrides \Drupal\views\Plugin\Plugin::$usesOptions.
   */
  protected $usesOptions = FALSE;

  /**
   * Overrides \Drupal\views\Plugin\views\row\RowPluginBase::render().
   */
  public function render($row) {
    return $row->_entity;
  }

}
