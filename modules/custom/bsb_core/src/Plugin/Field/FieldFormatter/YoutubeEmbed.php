<?php

/**
 * @file
 * Contains \Drupal\bsb_core\Plugin\Field\FieldFormatter\YoutubeEmbed.
 */

namespace Drupal\bsb_core\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
/**
 * Plugin implementation of the 'youtube_embed' formatter.
 *
 * @FieldFormatter(
 *   id = "youtube_embed",
 *   label = @Translation("Youtube embedded frame"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class YoutubeEmbed extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
        'ratio' => '16by9',
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $options = [
      '16by9' => '16x9',
      '4by3' => '4x3',
    ];
    $elements['ratio'] = array(
      '#type' => 'select',
      '#title' => t('Aspect ratio'),
      '#options' => $options,
      '#default_value' => $this->getSetting('ratio') ?: '16by9',
    );

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();
    $settings = $this->getSettings();

    if (!empty($settings['ratio'])) {
      $summary[] = t('Ratio used: @ratio', array('@ratio' => $settings['ratio']));
    }
    else {
      $summary[] = t('Default ratio (16x9)');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items) {
    $element = array();
    $ratio = $this->getSetting('ratio');

    foreach ($items as $delta => $item) {

      $element[$delta] = array(
        '#theme' => 'bsb_core_youtube_embed',
        '#ratio' => $ratio ?: '16by9',
        '#item' => $item->value,
      );

    }

    return $element;
  }
}
