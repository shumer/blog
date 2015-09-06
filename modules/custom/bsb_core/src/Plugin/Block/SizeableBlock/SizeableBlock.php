<?php
/**
 * @file
 * Contains  Drupal\bsb_core\Plugin\Block\SearchForm\SearchForm
 */

namespace Drupal\bsb_core\Plugin\Block\SizeableBlock;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a Logo Block
 *
 * @Block(
 *   id = "sizeable_block",
 *   admin_label = @Translation("Simple sizeable block"),
 * )
 */
class SizeableBlock extends BlockBase
{

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();


    return ;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['image'] = array (
        '#type' => 'textfield',
        '#title' => $this->t('Site name'),
        '#description' => $this->t('Enter a site name here'),
        '#default_value' => isset($config['site_logo_text']) ? $config['site_logo_text'] : ''
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('site_logo_text', $form_state->getValue('site_logo_text'));
  }
}