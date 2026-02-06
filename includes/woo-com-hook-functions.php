<?php
defined( 'ABSPATH' ) || exit; // Exit if accessed directly

///////////////////////////////////////////////
// Initialize cart session on WooCommerce init
add_action( 'init', function() {
    // Ensure WooCommerce session is initialized
    if ( empty(WC()->session) ) return;

    error_log(print_r(['init->-get_session_data', WC()->session->get_session_data() ], true), 3, DC_PLUGIN_DIR . 'debug.log');
    //error_log(print_r(['init', WC()->session->get_session_data()['cart'] ], true), 3, DC_PLUGIN_DIR . 'debug.log');
});
///////////////////////////////////////////////

// Filter stock status and quantity based on current cart session
add_filter('woocommerce_product_is_in_stock', 'dc_filter_product_is_in_stock', 10, 2);
function dc_filter_product_is_in_stock($in_stock, $product) {
	// Make sure session exists
    if ( empty(WC()->session) || !is_admin() ) return $in_stock;

	$current_cart = dc_get_current_cart_session_key();
	if ($current_cart == DC_PLUGIN_PRE_KEY)
	{
		if ( $product instanceof WC_Product_Simple || $product instanceof WC_Product_Variation ) {
			$prebook = $product->get_meta( '_dc_prod_prebooking', true );
			$quantity = $product->get_meta( '_dc_prod_prebooking_stock', true );
			$in_stock = ( $prebook === 'yes' && intval( $quantity ) > 0 ) ? true : false;
		}
	}

	return $in_stock;
}

// Filter stock quantity based on current cart session
add_filter('woocommerce_product_get_stock_quantity', 'dc_filter_product_get_stock_quantity', 10, 2);
add_filter('woocommerce_product_variation_get_stock_quantity', 'dc_filter_product_get_stock_quantity', 10, 2);
function dc_filter_product_get_stock_quantity($quantity, $product) {
	// Make sure session exists
    if ( empty(WC()->session) || !is_admin() ) return $quantity;

	$current_cart = dc_get_current_cart_session_key();
	if ($current_cart == DC_PLUGIN_PRE_KEY)
	{
		$quantity = $product->get_meta( '_dc_prod_prebooking_stock', true );
	}

    return $quantity;
}

// Clear current cart session when cart is emptied
add_action( 'woocommerce_cart_emptied', 'dc_clear_current_cart_session_key', 99 );
function dc_clear_current_cart_session_key() {
    $current_cart = dc_get_current_cart_session_key();
	WC()->session->set( $current_cart, null );
}




///////////////////////////////////////////////


