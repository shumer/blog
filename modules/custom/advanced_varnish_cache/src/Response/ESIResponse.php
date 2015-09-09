<?php
/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\Controller\BlockESIController.
 */
namespace Drupal\advanced_varnish_cache\Response;

use Drupal\Core\Cache\CacheableResponse;
use Drupal\Core\Entity\EntityInterface;

class ESIResponse extends CacheableResponse {

  protected $entity;

  public function getEntity() {
    return $this->entity;
  }

  public function setEntity(EntityInterface $entity) {
    $this->entity = $entity;
  }

}