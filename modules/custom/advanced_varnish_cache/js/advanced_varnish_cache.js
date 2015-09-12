/**
 * Created by max on 11.09.2015.
 */
$=jQuery;
(function() {
  $('#avc-user-block .avc-user-bloc-item').each(function () {
    var id = $(this).attr('id');
    var content = $(this).html();
    $('*[data-content-id='+ id +']').html(this);
  });
})();
