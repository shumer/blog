<?php

/**
 * @file
 * Contains \Drupal\config_pages\ConfigPagesTypeForm.
 */

namespace Drupal\config_pages;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\language\Entity\ContentLanguageSettings;
use Drupal\config_pages\Entity\ConfigPages;

/**
 * Base form for category edit forms.
 */
class ConfigPagesTypeForm extends EntityForm {

  /**
   * Required routes rebuild.
   *
   * @var string
   */
  protected $routesRebuildRequired = FALSE;

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /* @var \Drupal\config_pages\ConfigPagesTypeInterface $config_pages_type */
    $config_pages_type = $this->entity;

    $form['label'] = [
      '#type' => 'textfield',
      '#title' => t('Label'),
      '#maxlength' => 255,
      '#default_value' => $config_pages_type->label(),
      '#description' => t("Provide a label for this config page type to help identify it in the administration pages."),
      '#required' => TRUE,
    ];
    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $config_pages_type->id(),
      '#machine_name' => [
        'exists' => '\Drupal\config_pages\Entity\ConfigPagesType::load',
      ],
      '#maxlength' => EntityTypeInterface::BUNDLE_MAX_LENGTH,
      '#disabled' => !$config_pages_type->isNew(),
    ];

    $form['actions'] = ['#type' => 'actions'];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Save'),
    ];

    $options = [];
    $items = \Drupal::service('plugin.manager.config_pages_context')->getDefinitions();

    foreach($items as $plugin_id => $item) {
      $options[$plugin_id] = $item['label'];
    }

    // Menu.
    $form['menu'] = [
      '#type' => 'details',
      '#title' => t('Menu'),
      '#tree' => TRUE,
      '#open' => TRUE,
    ];

    $form['menu']['path'] = [
      '#type' => 'textfield',
      '#description' => t('Menu path which will be used for form display.'),
      '#default_value' => !empty($config_pages_type->menu['path']) ? $config_pages_type->menu['path'] : [],
      '#required' => FALSE,
    ];

    // Context.
    $form['context'] = [
      '#type' => 'details',
      '#title' => t('Context'),
      '#tree' => TRUE,
      '#open' => FALSE,
    ];

    $form['context']['group'] = [
      '#type' => 'checkboxes',
      '#description' => t('Consider following context for this configuration'),
      '#options' => $options,
      '#default_value' => !empty($config_pages_type->context['group']) ? $config_pages_type->context['group'] : [],
      '#required' => FALSE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    $new_menu_path = $form_state->getValue('menu')['path'];

    if (!empty($new_menu_path) && !in_array($new_menu_path[0], ['/'], TRUE)) {
      $form_state->setErrorByName('menu', $this->t('Manually entered paths should start with /'));
      return;
    }

    $old_menu_path = NULL;

    // Load unchanged entity.
    $config_pages_type = $this->entity;
    $config_pages_type_unchanged = $config_pages_type->load($config_pages_type->id());
    if (is_object($config_pages_type_unchanged)) {
      $old_menu_path = $config_pages_type_unchanged->menu['path'];
    }

    // If menu path was changed check if it's a valid Drupal path.
    if (!empty($new_menu_path) && $new_menu_path != $old_menu_path) {
      $path_exists = \Drupal::service('path.validator')->isValid($new_menu_path);
      if ($path_exists) {
        $form_state->setErrorByName('menu', $this->t('This menu path is already exists, please provide another one.'));
      }
      $this->routesRebuildRequired = TRUE;
    }

  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {

    $config_pages_type = $this->entity;
    $status = $config_pages_type->save();

    $edit_link = $this->entity->link($this->t('Edit'));
    $logger = $this->logger('config_pages');
    if ($status == SAVED_UPDATED) {
      drupal_set_message(t('Custom config page type %label has been updated.', ['%label' => $config_pages_type->label()]));
      $logger->notice('Custom config page type %label has been updated.', ['%label' => $config_pages_type->label(), 'link' => $edit_link]);
    }
    else {
      drupal_set_message(t('Custom config page type %label has been added.', ['%label' => $config_pages_type->label()]));
      $logger->notice('Custom config page type %label has been added.', ['%label' => $config_pages_type->label(), 'link' => $edit_link]);
    }

    // Check if we need to rebuild routes.
    if ($this->routesRebuildRequired) {
      \Drupal::service('router.builder')->rebuild();
    }

    $form_state->setRedirectUrl($this->entity->urlInfo('collection'));
  }

}
