/**
 * @file
 * Defines Javascript behaviors for the background_changer module.
 */
$=jQuery;

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

      // Options for SuperBGImage
      $.fn.superbgimage.options = {
        randomtransition: 2, // 0-none, 1-use random transition (0-7)
        z_index: -1, // z-index for the container
        slideshow: 1, // 0-none, 1-autostart slideshow
        slide_interval: settings.slide_interval || 8000, // interval for the slideshow
        randomimage: 1, // 0-none, 1-random image
        speed: 'slow' // animation speed
      };

      // initialize SuperBGImage
      $('#' + element_id).superbgimage().hide();

    });
  }
};

