<?php
/**
 * @file
 * Contains Drupal\bsb_core\Plugin\Block\SiteLogo\SiteLogo.
 */


namespace Drupal\bsb_core\Plugin\Block\SiteLogo;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Url;

/**
 * Provides a Logo Block
 *
 * @Block(
 *   id = "site_logo",
 *   admin_label = @Translation("Site logo"),
 * )
 */
class SiteLogo extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    if (!empty($config['site_logo_text'])) {
      $name = $config['site_logo_text'];
    }
    else {
      $name = $this->t('XXX');
    }

    $path = Url::fromRoute('<front>');

    return array(
      '#theme' => 'bsb_core_site_logo',
      '#name' => $name,
      '#path' => $path,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['site_logo_text'] = array (
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