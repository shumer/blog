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
  Drupal.behaviors.bsb_searchform = {
    attach: function (context) {
      var $context = $(context);
      if($context.find('.bsb-search-form')) {
        $('.bsb-search-form').on('keyup', function(e){
          if (e.keyCode == 13) {
            window.location = '/search/' + $(this).val();
          }
        });
      }
    }
  };

  /**
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.bsb_equalize = {
    attach: function (context) {
      var maxHeight = 0;

      $("div.equalize").each(function () {
        if ($(this).height() > maxHeight) {
          maxHeight = $(this).height();
        }
      });

      $("div.equalize").height(maxHeight);
    }
  };

  /**
   *
   * @type {Drupal~behavior}
   */
  Drupal.behaviors.bsb_ajax_user_popup = {
    attach: function (context) {

      // Bind Ajax behaviors to all items showing the class.
      $('.bsb-core-use-ajax').each(function () {
        var element_settings = {};
        // Clicked links look better with the throbber than the progress bar.
        element_settings.progress = {'type': 'throbber'};

        // For anchor tags, these will go to the target of the anchor rather
        // than the usual location.
        if ($(this).attr('href')) {
          element_settings.url = $(this).attr('href');
          element_settings.event = 'mouseover';
        }
        element_settings.dialogType = $(this).data('dialog-type');
        element_settings.dialog = $(this).data('dialog-options');
        element_settings.base = $(this).attr('id');
        element_settings.element = this;
        Drupal.ajax(element_settings);
      });

      $('.bsb-core-use-ajax').on("mousemove", function(e) {
        var uid = $(this).data('user-id');
        $('.bsb-ajax-info[data-user-id=' + uid +']').css({
          "display":"block",
          "top":e.clientY - 20,
          "left":e.clientX + 20
        });
      });

      $('.bsb-core-use-ajax').on("mouseout", function() {
        $('.bsb-ajax-info').html('');
        $('.bsb-ajax-info').css({
          "display":"none"
        });
      });

    }
  };

})(jQuery, Drupal, drupalSettings);

(function($) {

  // prettyPhoto
	jQuery(document).ready(function(){

    hljs.initHighlightingOnLoad();

		jQuery('a[data-gal]').each(function() {
			jQuery(this).attr('rel', jQuery(this).data('gal'));
		});  	
		jQuery("a[data-rel^='prettyPhoto']").prettyPhoto({animationSpeed:'slow',theme:'light_square',slideshow:false,overlay_gallery: false,social_tools:false,deeplinking:false});
	}); 

		
})(jQuery);

