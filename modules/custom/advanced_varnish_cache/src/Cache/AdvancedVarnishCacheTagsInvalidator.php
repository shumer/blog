<?php

/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\Cache\AdvancedVarnishCacheTagsInvalidator.
 */

namespace Drupal\advanced_varnish_cache\Cache;

use Drupal\advanced_varnish_cache\AdvancedVarnishCacheInterface;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Logger\RfcLogLevel;


class AdvancedVarnishCacheTagsInvalidator implements CacheTagsInvalidatorInterface {

  public $varnish_handler;

  /**
   * Marks cache items with any of the specified tags as invalid.
   *
   * @param string[] $tags
   *   The list of tags for which to invalidate cache items.
   */
  public function invalidateTags(array $tags) {
    $this->purgeTags($tags);
  }

  /**
   * Purge varnish cache for specific tag.
   *
  *
   * @param $tag
   *   (string/array) tag to search and purge.
   */
  protected function purgeTags($tag) {
    $account = \Drupal::currentUser();
    $header = $this->varnish_handler->getHeaderCacheTag();

    // Build pattern.
    $pattern = (count($tag) > 1)
      ? implode(';|', $tag) . ';'
      : reset($tag) . ';';

    // Remove quotes from pattern.
    $pattern = strtr($pattern, array('"' => '', "'" => ''));

    // Clean all or only current host.
    if ($this->varnish_handler->getSetting('purge', 'all_hosts', TRUE)) {
      $command_line = "ban obj.http.$header ~ \"$pattern\"";
    }
    else {
      $host = $this->varnish_handler->varnish_get_host();
      $command_line = "ban req.http.host ~ $host && obj.http.$header ~ \"$pattern\"";
    }

    // Log action.
    if ($this->varnish_handler->getSetting('general', 'logging', FALSE)) {
      \Drupal::logger('advanced_varnish_cache')->log(RfcLogLevel::DEBUG, 'u=@uid purge !command_line', array(
          '@uid' => $account->id(),
          '!command_line' => $command_line,
        )
      );
    }

    // Query Varnish.
    $res = $this->varnish_handler->varnish_terminal_run(array($command_line));
    return $res;
  }
}
