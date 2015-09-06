<?php
/**
 * @file
 * Contains  Drupal\bsb_core\Plugin\Block\SearchForm\SearchForm
 */

namespace Drupal\bsb_core\Plugin\Block\ContactWrapper;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a Contact wrapper Block
 *
 * @Block(
 *   id = "contact_wrapper",
 *   admin_label = @Translation("Contact wrapper"),
 * )
 */
class ContactWrapper extends BlockBase
{

  /**
   * {@inheritdoc}
   */
  public function build() {
    return [
      '#markup' => '<div id="contactwrap"></div>',
    ];
  }

}