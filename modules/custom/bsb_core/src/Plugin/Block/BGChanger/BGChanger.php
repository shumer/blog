<?php
/**
 * @file
 * Contains  Drupal\bsb_core\Plugin\Block\BGChanger\BGChanger
 */
namespace Drupal\bsb_core\Plugin\Block\BGChanger;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a comments Block
 *
 * @Block(
 *   id = "bg_changer",
 *   admin_label = @Translation("Background changer"),
 * )
 *
 */
class BGChanger extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
kpr($config);
    $options = $config['options'];
    $bg_set = $config['bg_set'];

    return array(
      '#options' => $options,
      '#images' => $bg_set,
      '#theme' => 'bsb_core_bg_changer',
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