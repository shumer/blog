<?php

/**
 * @file
 * Contains \Drupal\bsb_core\Twig\WordsExtension.
 */

namespace Drupal\bsb_core\Twig;

use Twig_Extension;
/**
 * Provides the Words debugging function within Twig templates.
 */
class WordsExtension extends Twig_Extension {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'words';
  }

  /**
   * {@inheritdoc}
   */
  public function getFilters() {
    return array(
      new \Twig_SimpleFilter('words', array($this, 'words')),
    );
  }

  /**
   * Provides Words function to Twig templates.
   */
  public function words($string, $count = 100, $end = '...') {

    preg_match('/^\s*+(?:\S++\s*+){1,'.$count.'}/u', $string, $matches);
    if (!isset($matches[0]) || strlen($string) === strlen($matches[0])) {
      return $string;
    }

    return rtrim($matches[0]) . $end;
  }

}
