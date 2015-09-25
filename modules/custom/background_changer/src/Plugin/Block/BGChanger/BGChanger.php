<?php
/**
 * @file
 * Contains Drupal\background_changer\Plugin\Block\BGChanger\BGChanger
 */

namespace Drupal\background_changer\Plugin\Block\BGChanger;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a comments Block.
 *
 * @Block(
 *   id = "bg_changer",
 *   admin_label = @Translation("Background changer"),
 * )
 */
class BGChanger extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    $id = $config['bg_set'];
    $cid = 'background_changer:' . $id;

    // Check if we have this set in cache.
    if ($cache = \Drupal::cache()->get($cid)) {
      $links = $cache->data;
    }
    else {
      $node_storage = \Drupal::entityManager()->getStorage('background_set');
      $set = $node_storage->load($id);
      $files = $set->get('images');

      foreach ($files as $file) {
        $urls[] = $file->entity->url();
      }
      foreach ($urls as $url) {
        $url = \Drupal\Core\Url::fromUri($url);
        $links[] = \Drupal::l('', $url);
      }
      \Drupal::cache()->set($cid, $links);
    }

    $options = $config['options'];

    return array(
      '#options' => $options,
      '#images' => $links,
      '#theme' => 'background_changer_block',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {

    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    $options = ['' => 'None'];
    $query = \Drupal::entityQuery('background_set');
    $ids = $query->execute();
    $node_storage = \Drupal::entityManager()->getStorage('background_set');

    // Load a single node
    foreach ($ids as $id) {
      $set = $node_storage->load($id);
      $options[$id] = $set->get('name')->value;
    }

    $form['options'] = [
      '#type' => 'details',
      '#title' => t('Options'),
      '#tree' => TRUE,
    ];
    $form['options']['slide_interval'] = [
      '#type' => 'textfield',
      '#default_value' => $config['options']['slide_interval'] ?: '8000',
      '#title' => t('Slide interval'),
    ];
    $form['options']['element_id'] = [
      '#type' => 'textfield',
      '#default_value' => $config['options']['element_id'] ?: 'thumbs',
      '#title' => t('Element id'),
    ];

    $form['bg_set'] = [
      '#type' => 'select',
      '#default_value' => $config['bg_set'] ?: '',
      '#title' => t('Background set'),
      '#options' => $options,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    // Save our custom settings when the form is submitted.
    $this->setConfigurationValue('options', $form_state->getValue('options'));
    $this->setConfigurationValue('bg_set', $form_state->getValue('bg_set'));
  }

}
