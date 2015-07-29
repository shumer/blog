<?php
/**
 * @file
 * Contains \Drupal\config_pages\Routing\ConfigPagesRoutes.
 */

namespace Drupal\config_pages\Routing;

use Drupal\config_pages\Entity\ConfigPagesType;
use Symfony\Component\Routing\Route;

/**
 * Defines dynamic routes for Config Pages.
 */
class ConfigPagesRoutes {

  /**
   * {@inheritdoc}
   */
  public function routes() {
    $routes = [];

    // Declare dinamic routes for config pages entities.
    $types = ConfigPagesType::loadMultiple();
    foreach ($types as $cp_type) {
      $bundle = $cp_type->id();
      $label = $cp_type->get('label');
      $menu = $cp_type->get('menu');
      $path = isset($menu['path']) ? $menu['path'] : '';

      if (!$path) {
        continue;
      }
      $routes['config_pages.' . $bundle] = new Route(
        $path,
        [
          '_controller' => '\Drupal\config_pages\Controller\ConfigPagesController::classInit',
          '_title' => "Edit config page $label",
          'config_pages_type' => $bundle,
        ],
        [
          '_permission'  => 'edit config_pages entity',
        ]
      );
    }
    return $routes;
  }
}
