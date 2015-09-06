<?php
/**
 * @file
 * Contains \Drupal\social_media_links\Plugin\SocialMediaLinks\Platform\Contact.
 */

namespace Drupal\social_media_links\Plugin\SocialMediaLinks\Platform;

use Drupal\social_media_links\PlatformBase;

/**
 * Provides 'contact' platform.
 *
 * @Platform(
 *   id = "contact",
 *   name = @Translation("Contact"),
 *   iconName = "email",
 * )
 */
class Contact extends PlatformBase {

  public function getUrlPrefix() {
    // Get the url of the site as prefix for the url.
    return \Drupal::url('<none>', [], ['absolute' => TRUE]);
  }

  public function getUrl() {
    // Generate the internal url based on the user input.
    // See Url::fromUri() and Url::fromUserPathUri() for more information.
    return 'user-path:/' . $this->getValue() . $this->getUrlSuffix();
  }
  
}