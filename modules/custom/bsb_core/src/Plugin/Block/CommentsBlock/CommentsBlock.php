<?php
/**
 * @file
 * Contains  Drupal\bsb_core\Plugin\Block\TwitterBlock\TwitterBlock
 */

namespace Drupal\bsb_core\Plugin\Block\CommentsBlock;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a comments Block
 *
 * @Block(
 *   id = "comments_block",
 *   admin_label = @Translation("Comments block"),
 * )
 *
 */
class CommentsBlock extends BlockBase
{

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();


    return array(
      '#theme' => 'bsb_core_twitter_block',
    );

    return ;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    return $form;
  }

}