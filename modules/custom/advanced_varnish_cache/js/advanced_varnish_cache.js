/**
 * Created by max on 11.09.2015.
 */
var elements = document.querySelectorAll("#advanced_varnish_cache_userblocks .advanced_varnish_cache_userblock-item");
Array.prototype.forEach.call(elements, function(el, i){
  var selector = el.getAttribute("data-target");
  var dst_el = document.querySelector(selector);
  if (dst_el !== null) {
    dst_el.outerHTML = el.innerHTML;
  }
});;


var deepExtend = function(out) {
  out = out || {};

  for (var i = 1; i < arguments.length; i++) {
    var obj = arguments[i];

    if (!obj)
      continue;

    for (var key in obj) {
      if (obj.hasOwnProperty(key)) {
        if (typeof obj[key] === 'object')
          deepExtend(out[key], obj[key]);
        else
          out[key] = obj[key];
      }
    }
  }

  return out;
};

deepExtend({}, drupalSettings, avcUserBlocksSettings);

