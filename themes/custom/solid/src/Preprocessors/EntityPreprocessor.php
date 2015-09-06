<?php
namespace Drupal\solid\Preprocessors;


use Drupal\solid\Dispatcher\Dispatcher;

abstract class EntityPreprocessor implements EntityPreprocessorInterface
{

  public $variables;
  public $content;
  public $entity;
  public $viewMode;
  protected $dispatcher;

  public function init(&$variables) {
    $this->variables = &$variables;
    $this->content = &$variables['content'];
    $this->viewMode = $variables['view_mode'];
    $this->entity = $this->getEntity($variables);
    $this->dispatcher = Dispatcher::getInstance();
  }

  public function preprocess(&$variables) {
    $this->init($variables);
    $method = $this->dispatcher->init($this->entity, $this->viewMode)->getPreprocessMethod();

    if (method_exists($this, $method)) {
      call_user_func([$this, $method]);
    }
  }

  public function setVariables(&$variables) {
    $this->variables = &$variables;
  }

  abstract public function getEntity(&$variables);

}