<?php

/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\Cache\AdvancedVarnishCacheTagsInvalidator.
 */

namespace Drupal\advanced_varnish_cache\Cache;

use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\Logger\RfcLogLevel;


class AdvancedVarnishCacheTagsInvalidator implements CacheTagsInvalidatorInterface {
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
    $header = ADVANCED_VARNISH_CACHE_HEADER_CACHE_TAG;

    // Build pattern.
    $pattern = (count($tag) > 1)
      ? implode(';|', $tag) . ';'
      : reset($tag) . ';';

    // Remove quotes from pattern.
    $pattern = strtr($pattern, array('"' => '', "'" => ''));

    // Clean all or only current host.
    if (_advanced_varnish_cache_settings('purge', 'all_hosts', TRUE)) {
      $command_line = "ban obj.http.$header ~ \"$pattern\"";
    }
    else {
      $host = advanced_varnish_cache__varnish_get_host();
      $command_line = "ban req.http.host ~ $host && obj.http.$header ~ \"$pattern\"";
    }

    // Log action.
    if (_advanced_varnish_cache_settings('general', 'logging', FALSE)) {
      \Drupal::logger('advanced_varnish_cache')->log(RfcLogLevel::DEBUG, 'u=@uid purge !command_line', array(
          '@uid' => $account->id(),
          '!command_line' => $command_line,
        )
      );
    }

    // Query Varnish.
    $res = advanced_varnish_cache__varnish_terminal_run(array($command_line));
    return $res;
  }
}
