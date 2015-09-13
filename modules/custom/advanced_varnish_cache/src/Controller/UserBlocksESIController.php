<?php

/**
 * @file
 * Contains \Drupal\advanced_varnish_cache\Controller\UserBlocksESIController.
 */

namespace Drupal\advanced_varnish_cache\Controller;

use Drupal\advanced_varnish_cache\Response\ESIResponse;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Component\Serialization\Json;

class UserBlocksESIController extends ControllerBase {

  /**
   * Return rendered block html to replace esi tag.
   */
  public function content($block_id){
    $content = '';
    $js_data = array();
    $response = new ESIResponse();
    $module_handler = \Drupal::moduleHandler();

    // Invoke hook to gather user data.
    $user_data = $module_handler->invokeAll('advanced_varnish_cache_userblocks');

    // PLUGIN Alternative
    $plugins = \Drupal::service('plugin.manager.user_block')->getDefinitions();
    foreach ($plugins as $plugin_id => $plugin) {
      $user_data[] = call_user_func([$plugin['class'], 'content']);
    }

    // Parse returned data.
    foreach ($user_data as $target => $data) {
      if (is_array($data)) {
        $js_data[$target] = $data;
      }
      elseif (is_string($data)) {
        $content[] = '<div class="advanced_varnish_cache_userblock-item" data-target="' . $target . '">' . $data . '</div>';
      }
    }

    $embed_prefix = "\n<!--//--><![CDATA[//><!--\n";
    $embed_suffix = "\n//--><!]]>\n";

    // Defaults for each SCRIPT element.
    $element_defaults = array(
      '#type' => 'html_tag',
      '#tag' => 'script',
      '#value' => '',
    );
    $element = $element_defaults;
    $element['#value_prefix'] = $embed_prefix;
    $element['#value'] = 'var avcUserBlocksSettings = ' . Json::encode(NestedArray::mergeDeepArray($js_data)) . ";";
    $element['#value_suffix'] = $embed_suffix;

    $content[] = '<script type="text/javascript">
      var elements = document.querySelectorAll("#advanced_varnish_cache_userblocks .advanced_varnish_cache_userblock-item");
      Array.prototype.forEach.call(elements, function(el, i){
        var selector = el.getAttribute("data-target");
        var dst_el = document.querySelector(selector);
        if (dst_el !== null) {
          dst_el.outerHTML = el.innerHTML;
        }
      });

      setTimeout(\'jQuery.extend(true, drupalSettings, ' . Json::encode(NestedArray::mergeDeepArray($js_data)) . ');\', 100);'
      . '</script>';

    $content = implode(PHP_EOL, $content);
    $content = '<div id="advanced_varnish_cache_userblocks" style="display:none;" time="' . time() . '">' . $content . '</div>';

    // Set rendered block as response object content.
    $response->setContent($content);

    return $response;
  }

}
