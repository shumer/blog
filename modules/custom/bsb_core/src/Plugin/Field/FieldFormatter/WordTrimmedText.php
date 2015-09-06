<?php

/**
 * @file
 * Contains \Drupal\bsb_core\Plugin\Field\FieldFormatter\WordTrimmedText.
 */

namespace Drupal\bsb_core\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
/**
 * Plugin implementation of the 'youtube_embed' formatter.
 *
 * @FieldFormatter(
 *   id = "word_trimmed_text",
 *   label = @Translation("Text trimmed to words"),
 *   field_types = {
 *     "string",
 *     "text",
 *     "text_with_summary",
 *     "text_long"
 *   }
 * )
 */
class WordTrimmedText extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
        'count' => '100',
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $elements['count'] = array(
      '#type' => 'textfield',
      '#title' => t('Words count'),
      '#default_value' => $this->getSetting('count') ?: '100',
    );

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();
    $settings = $this->getSettings();

    if (!empty($settings['count'])) {
      $summary[] = t('Words count: @count', array('@count' => $settings['count']));
    }
    else {
      $summary[] = t('Default count (100)');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items) {
    $element = array();
    $count = $this->getSetting('count');

    foreach ($items as $delta => $item) {
      dpm($item->value);
      $text = $this->trimWords($item->value, $count);
      $element[$delta] = array(
        '#type' => 'markup',
        '#markup' => $text,
      );

    }

    return $element;
  }

  public function trimWords($string, $count = 100, $end = '...') {
    preg_match('/^\s*+(?:\S++\s*+){1,'.$count.'}/u', $string, $matches);
    if (!isset($matches[0]) || strlen($string) === strlen($matches[0])) {
      return $string;
    }

    return rtrim($matches[0]) . $end;
  }
}
