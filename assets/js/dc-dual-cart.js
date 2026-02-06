jQuery(document).ready(function($) {

    // Custom AJAX add to cart for dual cart functionality in single product page
    $('form.cart').on('submit', function (e) {
        e.preventDefault();
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

    // Custom AJAX add to cart for dual cart functionality in shop page
    $('form.shop-cart').on('submit', function (e) {
      console.log('Form Shop Cart triggered');
      
        e.preventDefault();
        var $form = $(this);

        $.ajax({
          url: dc_dual_cart_params.ajax_url,
          type: 'POST',
          dataType: 'json',
          data: {
            action: 'custom_add_to_cart',
            product_id: $form.find('[name=product-id]').val(),
            quantity: $form.find('[name=quantity]').val() || 1,
            variation_id: 0,
            variation: [],
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

// Handle cart switch tabs
jQuery(function($){
  $(document).on('click', '.dc-cart-tab', function(e){
    e.preventDefault();
    var $btn = $(this);
    var $inp = $('input[name="dc_cart_type"]');
    var val = $btn.data('value');

    if ($btn.data('value') == $inp.val()) return; // already active

    // optimistic UI: mark active
    $btn.addClass('active').attr('aria-pressed', 'true');
    $btn.siblings('.dc-cart-tab').removeClass('active').attr('aria-pressed', 'false');

    $.ajax({
      url: dc_dual_cart_params.ajax_url,
      method: 'POST',
      dataType: 'json',
      data: {
        action: 'dc_switch_cart',
        dc_cart_type: val,
        nonce: dc_dual_cart_params.nonce
      },
      success: function(response){
        if ( response && response.success ) {
          $inp.val( val );
          window.location.reload(); // reload to reflect cart change
        } else {
          alert( (response && response.data) ? response.data : 'Unable to switch cart.' );
        }
      },
      error: function(jqXHR, textStatus, errorThrown){
        alert('Request failed. Please try again.');
      }
    });

  });
});
