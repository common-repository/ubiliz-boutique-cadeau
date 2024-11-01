jQuery(document).ready(function($) {

  $(document).on('click', 'button.ubiliz-sc-copy', function(e) {
    e.preventDefault();
    var ubilizShortCode = $('#ubiliz-sc-value').text();
    navigator.clipboard.writeText(ubilizShortCode);
    $(this).text('Copié !');
  });

});
