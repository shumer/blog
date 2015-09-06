<?php
namespace Drupal\solid\Preprocessors;

use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\datetime\Plugin\Field\FieldFormatter\DateTimeDefaultFormatter;
use Drupal\datetime\Plugin\Field\FieldFormatter\DateTimeTimeAgoFormatter;
use Drupal\field\Tests\String;

class NodePreprocessor extends EntityPreprocessor
{

  public function getEntity(&$variables) {
    return $variables['elements']['#node'];
  }

  public function preprocess_node__article__default() {

  }

  public function preprocess_node__article__teaser() {

  }

  public function preprocess_node__article__listing() {
    $node = $this->entity;
    $created = $node->getCreatedTime();
    $formatter = \Drupal::service('date.formatter');
    $time_interval = $formatter->formatInterval(time() - $created, 1);
    $this->variables['date'] = t('Posted @time ago', ['@time' => $time_interval]);

  }

  public function preprocess_node__contact_form__default() {

    $field = $this->entity->get('field_for')->target_id;

    $entity_storage = \Drupal::entityManager()
        ->getStorage('contact_form');
    $form = $entity_storage->load($field);

    $message = \Drupal::entityManager()
      ->getStorage('contact_message')
      ->create(array(
        'contact_form' => $form->id(),
      ));

    $form = \Drupal::service('entity.form_builder')->getForm($message);
    $this->variables['form'] = $form;
  }

}
