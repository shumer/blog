<?php

/**
 * @file
 * Contains \Drupal\bsb_core\Plugin\CKEditorPlugin\Codesnippet.
 */
namespace Drupal\bsb_core\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "codesnippet" plugin.
 *
 * @CKEditorPlugin(
 *   id = "codesnippet",
 *   label = @Translation("Codesnippet"),
 * )
 */
class Codesnippet extends CKEditorPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getFile() {

    return drupal_get_path('module', 'bsb_core') . '/js/plugins/codesnippet/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return array(
      'codeSnippet_theme' => 'dark',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    $path = drupal_get_path('module', 'bsb_core') . '/js/plugins/codesnippet/icons/';
    return array(
      'CodeSnippet' => array(
        'label' => t('Code'),
        'image' => $path . '/codesnippet.png',
      ),
    );
  }

}
