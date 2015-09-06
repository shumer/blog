<?php
/**
 * @file
 * Contains  Drupal\bsb_core\Plugin\Block\TwitterBlock\TwitterBlock
 */

namespace Drupal\bsb_core\Plugin\Block\TwitterBlock;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a Twitter Block
 *
 * @Block(
 *   id = "twitter_block",
 *   admin_label = @Translation("Twitter block"),
 * )
 */
class TwitterBlock extends BlockBase
{

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    if (!empty($config['channel'])) {
      $channel = explode(';', $config['channel']);
      $key = array_rand($channel);
      $channel = trim($channel[$key]);
    }
    else {
      $channel = $this->t('Drupal');
    }

    return array(
      '#theme' => 'bsb_core_twitter_block',
      '#channel' => $channel,
    );

    return ;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $form['channel'] = array (
        '#type' => 'textfield',
        '#title' => $this->t('Channel name'),
        '#description' => $this->t('Enter a twitter channel name here'),
        '#default_value' => isset($config['channel']) ? $config['channel'] : ''
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('channel', $form_state->getValue('channel'));
  }
}