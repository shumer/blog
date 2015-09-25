<?php

/**
 * @file
 * Contains \Drupal\draft_moderation\Controller\NodeController.
 */

namespace Drupal\draft_moderation\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;
use Drupal\node\NodeTypeInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns responses for Node routes.
 */
class NodeController extends ControllerBase {

  /**
   * Displays add content links for available content types.
   *
   * Redirects to node/add/[type] if only one content type is available.
   *
   * @return array|\Symfony\Component\HttpFoundation\RedirectResponse
   *   A render array for a list of the node types that can be added; however,
   *   if there is only one node type defined for the site, the function
   *   will return a RedirectResponse to the node add page for that one node
   *   type.
   */
  public function editPage($node) {
    /*$content = array();
kpr([1]);
    // Only use node types the user has access to.
    foreach ($this->entityManager()->getStorage('node_type')->loadMultiple() as $type) {
      if ($this->entityManager()->getAccessControlHandler('node')->createAccess($type->id())) {
        $content[$type->id()] = $type;
      }
    }
print(55555);
    // Bypass the node/add listing if only one content type is available.
    if (count($content) == 1) {
      $type = array_shift($content);
      return $this->redirect('node.add', array('node_type' => $type->id()));
    }

    return array(
      '#theme' => 'node_add_list',
      '#content' => $content,
    );*/

    return \Drupal::service('entity.form_builder')->getForm($node, 'edit');
  }

}
