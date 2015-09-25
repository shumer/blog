<?php
/**
 * @file
 * Contains \Drupal\draft_moderation\Routing\RouteSubscriber.
 */

namespace Drupal\draft_moderation\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {

    // Alter node edit page to check for edit access on production node.
    if ($route = $collection->get('entity.node.edit_form')) {
      $route->setDefault('_controller', '\Drupal\draft_moderation\Controller\NodeController::editPage');
    }
  }

}
