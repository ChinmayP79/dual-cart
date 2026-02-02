jQuery(document).ready(function($) {
    // Custom AJAX add to cart for variable products
    $('form.cart').on('submit', function (e) {
        e.preventDefault();
        console.log('Custom AJAX add to cart triggered');
        console.log($(e.originalEvent.submitter).attr('name')=='dc_cart_prebooking' ? 'yes' : 'no');
        var $form = $(this);

        $.ajax({
          url: dc_dual_cart_params.ajax_url,
          type: 'POST',
          dataType: 'json',
          data: {
            action: 'custom_add_to_cart',
            product_id: $form.find('[name=add-to-cart]').val(),
            quantity: $form.find('[name=quantity]').val() || 1,
            variation_id: $form.find('[name=variation_id]').val() || 0,
            variation: $form.find('select[name^="attribute_"]').serializeArray(),
            dc_cart_prebooking: $(e.originalEvent.submitter).attr('name')=='dc_cart_prebooking' ? 'yes' : 'no',
            nonce: dc_dual_cart_params.nonce
          },
          beforeSend: function() {
            $form.find('[name=add-to-cart], [name=dc_cart_prebooking]').prop('disabled', true);
          },
          success: function(response) {
            if (response.success) {
              let $wrapper = $('.woocommerce-notices-wrapper');
              if (!$wrapper.length) {
                $('body').prepend('<div class="woocommerce-notices-wrapper"></div>');
                $wrapper = $('.woocommerce-notices-wrapper');
              }

              $wrapper.empty(); // clear previous notices

              if (response.success) {
                $wrapper.append('<div class="woocommerce-message" role="alert" style="display:block;"> Product added to your cart.</div>');
                $(document.body).trigger('wc_fragment_refresh');
              } else {
                $wrapper.append('<div class="woocommerce-error" role="alert" style="display:block;">' + (response.data || 'Unable to add product.') + '</div>');
              }

              // Auto-hide after 2s
              let delay = 1000 * 2;
              setTimeout(() => $wrapper.fadeOut(300, () => $wrapper.empty().show()), delay);
              setTimeout(() => $form.find('[name=add-to-cart], [name=dc_cart_prebooking]').prop('disabled', false), delay);
            } else {
              alert(response.data || 'Unable to add product.');
              $form.find('[name=add-to-cart], [name=dc_cart_prebooking]').prop('disabled', false);
            }
          },
          error: function() {
            alert('Something went wrong. Please try again.');
            $form.find('[name=add-to-cart], [name=dc_cart_prebooking]').prop('disabled', false);
          }
        });
    });
});
