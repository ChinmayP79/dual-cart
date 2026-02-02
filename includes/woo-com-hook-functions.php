<?php
defined( 'ABSPATH' ) || exit; // Exit if accessed directly

// Initialize cart session on WooCommerce init
/* add_action( 'init', function() {
    // Ensure WooCommerce session is initialized
    if ( empty(WC()->session) ) return;

    error_log(print_r(['init', WC()->session->get_session_data() ], true), 3, DC_PLUGIN_DIR . 'debug.log');
}); */

// Add Prebooking Add to Cart button and handle its functionality
add_action( 'woocommerce_after_add_to_cart_button', 'dc_prebooking_add_to_cart_button' );
function dc_prebooking_add_to_cart_button() {
    global $product;
    $product_id = $product->get_id();
    $is_prebooking = get_post_meta( $product_id, '_dc_prod_prebooking', true );
    $has_prebooking_stock = get_post_meta( $product_id, '_dc_prod_prebooking_stock', true );

    if ( $is_prebooking === 'yes' && $has_prebooking_stock > 0 ) {
        ?>
        <button type="submit" name="dc_cart_prebooking" value="yes" class="single_add_to_cart_button button alt wp-element-button">Add to Prebooking Cart</button>
        <?php
    }
}
// Custom AJAX add to cart
add_action( 'wp_ajax_custom_add_to_cart', 'custom_add_to_cart' );
add_action( 'wp_ajax_nopriv_custom_add_to_cart', 'custom_add_to_cart' );
function custom_add_to_cart() {
	check_ajax_referer('dc_dual_cart_nonce', 'nonce');

	if ( empty( $_POST['product_id'] ) ) {
		wp_send_json_error( 'Product ID missing.' );
		wp_die();
	}

	$product_id   = intval( $_POST['product_id'] );
	$quantity     = intval( $_POST['quantity'] ?? 1 );
	$variation_id = intval( $_POST['variation_id'] ?? 0 );
	$variation    = isset( $_POST['variation'] ) ? array_map( 'wc_clean', (array) $_POST['variation'] ) : array();
	$prebooking   = isset( $_POST['dc_cart_prebooking'] ) ? $_POST['dc_cart_prebooking'] : '';

    dc_switch_cart_session( $prebooking === 'yes' ? DC_PLUGIN_PRE_KEY : DC_PLUGIN_STD_KEY );
    error_log(print_r(['Before-custom_add_to_cart', WC()->session->get_session_data()], true), 3, DC_PLUGIN_DIR . 'debug.log');

	ksort( $variation ); // Normalize variation attributes

	$vendor_data = array();

	// Get vendor correctly
	//$final_product_id = $variation_id ? $variation_id : $product_id;
    //$product = wc_get_product( $final_product_id );

	// Prepare cart item data
	$cart_item_data = array();
	/* if ( ! empty( $some_data ) ) {
		$cart_item_data['some_data'] = $some_data;
	} */

    // Add to cart
    $added = WC()->cart->add_to_cart(
        $product_id,
        $quantity,
        $variation_id,
        $variation,
        $cart_item_data
    );

    dc_save_cart_session( $prebooking === 'yes' ? DC_PLUGIN_PRE_KEY : DC_PLUGIN_STD_KEY ); // Save current cart session

	if ( $added && ! is_wp_error( $added ) ) {
		wp_send_json_success( 'Product added to cart.' );
	} else {
		wp_send_json_error( 'Unable to add to cart, Due to: ' . ( is_wp_error( $added ) ? $added->get_error_message() : 'Unknown error' ) );
	}

	wp_die();
}
