/**
 * Created by max on 11.09.2015.
 */
var elements = document.querySelectorAll("#advanced_varnish_cache_userblocks .advanced_varnish_cache_userblock-item");
Array.prototype.forEach.call(elements, function(el, i){
  var selector = el.getAttribute("data-target");
  if (selector !== null) {console.log(selector);
    var dst_el = document.querySelector(selector);
    if (dst_el !== null) {
      dst_el.outerHTML = el.innerHTML;
    }
  }
});


function extend(){
  for(var i=1; i<arguments.length; i++)
    for(var key in arguments[i])
      if(arguments[i].hasOwnProperty(key))
        arguments[0][key] = arguments[i][key];
  return arguments[0];
}
extend(drupalSettings, avcUserBlocksSettings);
