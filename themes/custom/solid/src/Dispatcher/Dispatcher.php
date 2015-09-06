<?php
namespace Drupal\solid\Dispatcher;

use Drupal\Core\Entity\Entity;
use Drupal\Core\Entity\EntityInterface;

class Dispatcher
{
  protected static $instance;
  protected $entity;
  protected $viewMode;

  protected function __construct() {}
  protected function __clone() {}

  public static function getInstance() {
    if(!self::$instance) {
      self::$instance = new self;
    }
    return self::$instance;
  }

  public function getPreprocessMethod() {
    if ($this->entity) {
      $entity_type = $this->entity->getEntityTypeId();
      $bundle = $this->entity->bundle();
      $preprocessMethodName = 'preprocess_' . $entity_type . '__' . $bundle . '__' . $this->viewMode;
      return $preprocessMethodName;
    }
  }

  public function init(EntityInterface $entity, $viewMode) {
    $this->entity = $entity;
    $this->viewMode = $viewMode;
    return $this;
  }

}