jQuery(document).ready(function($) {

  $(document).on('click', '.ubiliz-product-box', function() {
    $(this).closest('.ubiliz-product-item').find('a.ubiliz-product-link')[0].click();
  });

});
