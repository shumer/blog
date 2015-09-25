/**
 * @file
 * Defines Javascript behaviors for the background_changer module.
 */

(function ($, drupalSettings) {

  /**
   * Behaviors for tabs in the node edit form.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches summary behavior for tabs in the node edit form.
   */
  Drupal.behaviors.background_changer = {
    attach: function (context) {

      var $context = $(context);
      var settings = drupalSettings.background_changer.options;
      var element_id = settings.element_id || 'thumbs';

      $context.find('#' + element_id).once('bg-thumbs-scanned').each(function() {

        // Options for SuperBGImage.
        $.fn.superbgimage.options = {
          randomtransition: 2,
          z_index: -1,
          slideshow: 1,
          slide_interval: settings.slide_interval || 8000,
          randomimage: 1,
          speed: 'slow'
        };

        // Initialize SuperBGImage.
        $('#' + element_id).superbgimage().hide();

      });
    }
  };

})(jQuery, drupalSettings);
