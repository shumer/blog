<?php

/**
 * @file
 * Contains \Drupal\config_pages\Controller\ConfigPagesController.
 */

namespace Drupal\config_pages\Controller;

use Drupal\Component\Plugin\PluginManagerInterface;
use Drupal\config_pages\Entity\ConfigPages;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\config_pages\ConfigPagesTypeInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\config_pages\Entity\ConfigPagesType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ConfigPagesController extends ControllerBase {

  /**
   * The config page storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $ConfigPagesStorage;

  /**
   * The config page type storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $ConfigPagesTypeStorage;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $entity_manager = $container->get('entity.manager');
    return new static(
      $entity_manager->getStorage('config_pages'),
      $entity_manager->getStorage('config_pages_type'),
      $container->get('theme_handler')
    );
  }

  /**
   * Constructs a ConfigPages object.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $config_pages_storage
   *   The config page storage.
   * @param \Drupal\Core\Entity\EntityStorageInterface $config_pages_type_storage
   *   The config page type storage.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   */
  public function __construct(EntityStorageInterface $config_pages_storage, EntityStorageInterface $config_pages_type_storage, ThemeHandlerInterface $theme_handler) {
    $this->ConfigPagesStorage = $config_pages_storage;
    $this->ConfigPagesTypeStorage = $config_pages_type_storage;
    $this->themeHandler = $theme_handler;
  }

  /**
   * Presents the config page creation form.
   *
   * @param \Drupal\config_pages\ConfigPagesTypeInterface $config_pages_type
   *   The config page type to add.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request object.
   *
   * @return array
   *   A form array as expected by drupal_render().
   */
  public function addForm(ConfigPagesTypeInterface $config_pages_type, Request $request) {
    $config_page = $this->ConfigPagesStorage->create(array(
      'type' => $config_pages_type->id()
    ));
    return $this->entityFormBuilder()->getForm($config_page);
  }

  /**
   * Provides the page title for this controller.
   *
   * @param \Drupal\config_pages\ConfigPagesTypeInterface $config_pages_type
   *   The config page type being added.
   *
   * @return string
   *   The page title.
   */
  public function getAddFormTitle($config_pages_type) {
    $config_pages_types = ConfigPagesType::loadMultiple();
    $config_pages_type = $config_pages_types[$config_pages_type];
    return $this->t('Add %type config page', array('%type' => $config_pages_type->label()));
  }

  /**
   * Presents the config page creation/edit form.
   *
   * @param \Drupal\config_pages\ConfigPagesTypeInterface $config_pages_type
   *   The config page type to add.
   *
   * @return array
   *   A form array as expected by drupal_render().
   */
  public function classInit($config_pages_type = '') {

    $typeEntity = ConfigPagesType::load($config_pages_type);

    if (empty($typeEntity)) {
      throw new NotFoundHttpException;
    }

    $contextData = $typeEntity->getContextData();

    $config_page_ids = \Drupal::entityQuery('config_pages')->condition('context', $contextData)->execute();

    if (!empty($config_page_ids)) {
      $config_page_id = array_shift($config_page_ids);
      $entityStorage = \Drupal::entityManager()->getStorage('config_pages');
      $config_page = $entityStorage->load($config_page_id);
    }
    else {
      $config_page = $this->ConfigPagesStorage->create(array(
        'type' => $config_pages_type
      ));
    }
    return $this->entityFormBuilder()->getForm($config_page);
  }

  /**
   * Presents the config page confiramtion form.
   *
   * @return array
   *   A form array as expected by drupal_render().
   */
  public function clearConfirmation($config_pages) {
    return \Drupal::formBuilder()->getForm('Drupal\config_pages\Form\ConfigPagesClearConfirmationForm', $config_pages);
  }

}
