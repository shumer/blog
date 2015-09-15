<?php

/**
 * @file
 * Contains \Drupal\config_pages\ConfigPagesForm.
 */

namespace Drupal\config_pages;

use Drupal\Component\Utility\Html;
use Drupal\config_pages\Entity\ConfigPagesType;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityConstraintViolationList;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Field\FieldConfigInterface;
use Drupal\Core\Url;

/**
 * Form controller for the custom config page edit forms.
 */
class ConfigPagesForm extends ContentEntityForm {

  /**
   * The custom config page storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $ConfigPagesStorage;

  /**
   * The custom config page type storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $ConfigPagesTypeStorage;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The config page content entity.
   *
   * @var \Drupal\config_pages\ConfigPagesInterface
   */
  protected $entity;

  /**
   * Constructs a ConfigPagesForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Entity\EntityStorageInterface $config_pages_storage
   *   The custom config page storage.
   * @param \Drupal\Core\Entity\EntityStorageInterface $config_pages_type_storage
   *   The custom config page type storage.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct(EntityManagerInterface $entity_manager, EntityStorageInterface $config_pages_storage, EntityStorageInterface $config_pages_type_storage, LanguageManagerInterface $language_manager) {
    parent::__construct($entity_manager);
    $this->ConfigPagesStorage = $config_pages_storage;
    $this->ConfigPagesTypeStorage = $config_pages_type_storage;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $entity_manager = $container->get('entity.manager');
    return new static(
      $entity_manager,
      $entity_manager->getStorage('config_pages'),
      $entity_manager->getStorage('config_pages_type'),
      $container->get('language_manager')
    );
  }

  /**
   * Overrides \Drupal\Core\Entity\EntityForm::prepareEntity().
   *
   * Prepares the custom config page object.
   *
   * Fills in a few default values, and then invokes
   * hook_config_pages_prepare() on all modules.
   */
  protected function prepareEntity() {
    $config_pages = $this->entity;

    // Set up default values, if required.
    $config_pages_type = $this->ConfigPagesTypeStorage->load($config_pages->bundle());
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    $config_pages = $this->entity;
    $account = $this->currentUser();
    $config_pages_type = $this->ConfigPagesTypeStorage->load($config_pages->bundle());

    $form = parent::form($form, $form_state, $config_pages);

    $conditions['type'] = $config_pages->bundle();

    $list = \Drupal::entityManager()
      ->getStorage('config_pages')
      ->loadByProperties($conditions);

    // Show context message.
    $label = $config_pages_type->getContextLabel();
    if (!empty($label)) {
      drupal_set_message($this->t('Please note that this Page is context sensitive, current context is %label', array(
        '%label' => $label,
      )), 'warning');
    }

    if ($this->operation == 'edit') {
      $form['#title'] = $this->t('Edit custom config page %label', array('%label' => $config_pages->label()));
    }
    // Override the default CSS class name, since the user-defined custom config page
    // type name in 'TYPE-config-page-form' potentially clashes with third-party class
    // names.
    $form['#attributes']['class'][0] = 'config-page-' . Html::getClass($config_pages->bundle()) . '-form';

    // Add context import fieldset if any CP exists at this moment.
    if (!$this->entity->get('context')->isEmpty()) {
      $options = [];
      foreach ($list as $id => $item) {

        // Build options list.
        if ($config_pages->id() != $id) {
          $value = $item->get('context')->first()->getValue();
          $params = unserialize($value['value']);
          $params = array_shift($params);
          $string = '';
          foreach ($params as $name => $val) {
            $string .= $name . ' - ' . $val . ';';
          }

          $options[$id] = $string;
        }
      }

      // Show form if any data available.
      if (!empty($options)) {
        $form['other_context'] = [
          '#type' => 'details',
          '#tree' => TRUE,
          '#title' => t('Import'),
        ];

        $form['other_context']['list'] = [
          '#type' => 'select',
          '#options' => $options,
        ];

        $form['other_context']['submit'] = [
          '#type' => 'submit',
          '#value' => t('Import'),
          '#prefix' => '<div class="imort-form-actions">',
          '#suffix' => '</div>',
          '#submit' => array('::configPagesImportValues'),
        ];
      }
    }

    return $form;
  }

  /**
   * Form submit.
   * Clear field values submit callback.
   */
  public function configPagesClearValues(array $form, FormStateInterface $form_state) {

    $entity = $this->entity;

    $form_state->setRedirectUrl(Url::fromRoute('entity.config_pages.clear_confirmation', array('config_pages' => $entity->id())));

  }

  /**
   * Form submit.
   * Import other context submit callback.
   */
  public function configPagesImportValues(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;

    if ($imported_entity_id = $form_state->getValue('other_context')['list']) {
      $entityStorage = \Drupal::entityManager()->getStorage('config_pages');
      $imported_entity = $entityStorage->load($imported_entity_id);

      foreach ($entity as $name => &$value) {

        // Process only fields added from BO.
        if ($value->getFieldDefinition() instanceof FieldConfigInterface) {
          $entity->set($name, $imported_entity->get($name)->getValue());
        }
      }

      $entity->save();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $config_pages = $this->entity;

    $type = ConfigPagesType::load($config_pages->bundle());

    if(!$config_pages->label()) {
      $config_pages->setLabel($type->label());
    }

    $config_pages->context = $type->getContextData();

    // Save as a new revision if requested to do so.
    if (!$form_state->isValueEmpty('revision')) {
      $config_pages->setNewRevision();
    }

    $insert = $config_pages->isNew();
    $config_pages->save();
    $context = ['@type' => $config_pages->bundle(), '%info' => $config_pages->label()];
    $logger = $this->logger('config_pages');
    $config_pages_type = $this->ConfigPagesTypeStorage->load($config_pages->bundle());
    $t_args = ['@type' => $config_pages_type->label(), '%info' => $config_pages->label()];

    if ($insert) {
      $logger->notice('@type: added %info.', $context);
      drupal_set_message($this->t('@type %info has been created.', $t_args));
    }
    else {
      $logger->notice('@type: updated %info.', $context);
      drupal_set_message($this->t('@type %info has been updated.', $t_args));
    }

    if ($config_pages->id()) {
      $form_state->setValue('id', $config_pages->id());
      $form_state->set('id', $config_pages->id());
    }
    else {
      // In the unlikely case something went wrong on save, the config page will be
      // rebuilt and config page form redisplayed.
      drupal_set_message($this->t('The config page could not be saved.'), 'error');
      $form_state->setRebuild();
    }
  }

  /**
   * Returns an array of supported actions for the current entity form.
   *
   * @todo Consider introducing a 'preview' action here, since it is used by
   *   many entity types.
   */
  protected function actions(array $form, FormStateInterface $form_state) {

    // Save ConfigPage entity.
    $actions['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save'),
      '#submit' => array('::submitForm', '::save'),
    ];

    // Add button to reset values.
    $actions['reset'] = [
      '#type' => 'submit',
      '#value' => t('Clear values'),
      '#submit' => array('::configPagesClearValues'),
      '#button_type' => "submit",
    ];

    return $actions;
  }

}
