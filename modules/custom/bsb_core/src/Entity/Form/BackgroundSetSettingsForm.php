<?php

/**
 * @file
 * Contains Drupal\bsb_core\Entity\Form\BackgroundSetSettingsForm.
 */

namespace Drupal\bsb_core\Entity\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class BackgroundSetSettingsForm.
 *
 * @package Drupal\bsb_core\Form
 *
 * @ingroup bsb_core
 */
class BackgroundSetSettingsForm extends FormBase {
  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'BackgroundSet_settings';
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Empty implementation of the abstract submit class.
  }


  /**
   * Defines the settings form for Background set entities.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @return array
   *   Form definition array.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['BackgroundSet_settings']['#markup'] = 'Settings form for Background set entities. Manage field settings here.';
    return $form;
  }

}
