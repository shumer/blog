<?php

/**
 * @file
 * Contains background_set.page.inc.
 *
 * Page callback for Background set entities.
 */

use Drupal\Core\Render\Element;

/**
 * Prepares variables for Background set templates.
 *
 * Default template: background_set.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_background_set(array &$variables) {
  // Fetch BackgroundSet Entity Object.
  $background_set = $variables['elements']['#background_set'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }
}
