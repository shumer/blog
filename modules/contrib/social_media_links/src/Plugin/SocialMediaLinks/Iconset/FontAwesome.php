<?php
/**
 * @file
 * Contains \Drupal\social_media_links\Plugin\SocialMediaLinks\Iconset\FontAwesome.
 */

namespace Drupal\social_media_links\Plugin\SocialMediaLinks\Iconset;

use Drupal\social_media_links\IconsetBase;
use Drupal\social_media_links\IconsetInterface;
use Drupal\social_media_links\IconsetFinder;

/**
 * Provides 'elegantthemes' iconset.
 *
 * @Iconset(
 *   id = "fontawesome",
 *   publisher = "Font Awesome",
 *   publisherUrl = "http://fontawesome.github.io/",
 *   downloadUrl = "http://fortawesome.github.io/Font-Awesome/",
 *   name = "Font Awesome",
 * )
 */
class FontAwesome extends IconsetBase implements IconsetInterface {

  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    // FontAwesome is always available, because we can integrate it as a external library.
    $this->path = IconsetFinder::getPath($plugin_id) ? IconsetFinder::getPath($plugin_id) : 'library';
  }

  public function getStyle() {
    return array(
      '2x' => 'fa-2x',
      '3x' => 'fa-3x',
    );
  }

  public function getIconElement($platform, $style) {
    $iconName = $platform->getIconName();

    switch ($iconName) {
      case 'vimeo':
        $iconName = $iconName . '-square';
        break;

      case 'googleplus':
        $iconName = 'google-plus';
        break;

      case 'email':
        $iconName = 'envelope';
        break;
    }

    $icon = array(
      '#type' => 'markup',
      '#markup' => "<span class='fa fa-$iconName fa-$style'></span>",
    );

    return $icon;
  }

  public function getLibrary() {
    return array(
      'social_media_links/fontawesome.component',
    );
  }

  public function getIconPath($iconName, $style) {
    return NULL;
  }

}
