<?php
/**
 * @file
 * Contains  Drupal\bsb_core\Plugin\Block\SearchForm\SearchForm
 */
namespace Drupal\bsb_core\Plugin\Block\SearchForm;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a Logo Block
 *
 * @Block(
 *   id = "site_search",
 *   admin_label = @Translation("Site search"),
 * )
 */
class SearchForm extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $form['header'] = array(
      '#markup' => '<h4>Search</h4><div class="hline"></div>',
    );
    $form['search'] = array(
      '#type' => 'textfield',
      '#attributes' => [
        'class' => ['form-control', 'bsb-search-form'],
        'placeholder' => t('Search something'),
      ],
    );

    return $form;
  }

} 