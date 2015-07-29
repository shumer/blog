/**
 * @file
 * Defines Javascript behaviors for the node module.
 */

(function ($, Drupal, drupalSettings) {

  "use strict";

  /**
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.highligjter = {
    attach: function (context) {
      var $context = $(context);
      hljs.initHighlightingOnLoad();
    }
  };
})(jQuery, Drupal, drupalSettings);

