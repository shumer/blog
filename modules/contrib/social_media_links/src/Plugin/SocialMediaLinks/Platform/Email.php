<?php
/**
 * @file
 * Contains \Drupal\social_media_links\Plugin\SocialMediaLinks\Platform\Email.
 */

namespace Drupal\social_media_links\Plugin\SocialMediaLinks\Platform;

use Drupal\social_media_links\PlatformBase;

/**
 * Provides 'email' platform.
 *
 * @Platform(
 *   id = "email",
 *   name = @Translation("E-Mail"),
 * )
 */
class Email extends PlatformBase {

  public function getUrl() {
    return 'mailto:' . $this->getValue();
  }

}