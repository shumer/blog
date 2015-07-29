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
      $('code').each(function(i, block) {
        hljs.highlightBlock(block);
      });
    }
  };
})(jQuery, Drupal, drupalSettings);

