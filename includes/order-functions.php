<?php
defined( 'ABSPATH' ) || exit; // Exit if accessed directly

// Save order meta data on new order creation
add_action( 'woocommerce_new_order', 'dc_create_new_order', 10, 2);
function dc_create_new_order( int $order_id, WC_Order $order ) {
	$cart_type = dc_get_current_cart_session_key(); // Get current cart type

    if ( !empty( $cart_type ) ) $order->update_meta_data( '_order_type', $cart_type ); // Save cart type as order meta data
}

// Display order meta data in admin order details
add_action('woocommerce_admin_order_data_after_order_details', 'dc_admin_order_display_meta');
function dc_admin_order_display_meta( WC_Order $order ) {
	$order_type = $order->get_meta('_order_type', true); // Get order type meta data

	// Display order type in admin order details
	woocommerce_wp_note(array(
		'id' => 'order_type',
		'label' => __('Order Type:', 'dual-cart'),
		'message' => __( ($order_type == DC_PLUGIN_PRE_KEY ? 'Prebooking' : 'Standard'), 'dual-cart' ),
		'wrapper_class' => 'form-field-wide',
	));
}

// Adjust order item quantity and stock management based on order type
/* add_filter( 'woocommerce_order_item_quantity', 'dc_filter_order_item_quantity', 10, 3 );
function dc_filter_order_item_quantity( int $quantity, WC_Order $order, WC_Order_Item_Product $item ) {
	$order_type = dc_get_current_cart_session_key();//$order->get_meta('_order_type', true); // Get order type meta data
	error_log(print_r(['dc_filter_order_item_quantity > START', $quantity, $order_type], true), 3, DC_PLUGIN_DIR . 'debug.log');

	// If order is prebooking, set standard quantity to null and decrease prebooking stock only
	if ( $order_type == DC_PLUGIN_PRE_KEY )
	{
		error_log(print_r(['dc_filter_order_item_quantity > WILL CHANGE', $quantity, $order_type], true), 3, DC_PLUGIN_DIR . 'debug.log');
		// Decrease prebooking stock
		$product = $item->get_product();
		if ( $product ) {
			$prebooking_stock = $product->get_meta( '_dc_prod_prebooking_stock', true );
			$new_stock = max( 0, intval( $prebooking_stock ) - intval( $item->get_quantity() ) );
			$product->update_meta_data( '_dc_prod_prebooking_stock', $new_stock );
			$product->save(); // Save the updated product meta

			$order->add_order_note( __( 'Stock levels reduced for ', 'dual-cart' ) . ' ' . $product->get_name() . ' (' . $prebooking_stock . '&rarr;' . $new_stock . ')', false, false, array( 'note_group' => Automattic\WooCommerce\Internal\Orders\OrderNoteGroup::PRODUCT_STOCK ) );
			error_log(print_r(['dc_filter_order_item_quantity > HAS CHANGED', $quantity, $order_type], true), 3, DC_PLUGIN_DIR . 'debug.log');
		}

		// Set quantity to null for standard orders
		$quantity = null;
	}
	error_log(print_r(['dc_filter_order_item_quantity > END', $quantity, $order_type], true), 3, DC_PLUGIN_DIR . 'debug.log');

	return $quantity;
} */

// Adjust order item quantity and stock management based on order type
add_filter( 'woocommerce_can_reduce_order_stock', 'dc_filter_can_reduce_order_stock', 10, 3 );
function dc_filter_can_reduce_order_stock( bool $can_reduce, WC_Order $order ) {
	$order_type = dc_get_current_cart_session_key();//$order->get_meta('_order_type', true); // Get order type meta data
	error_log(print_r(['dc_filter_can_reduce_order_stock > START', $can_reduce, $order_type], true), 3, DC_PLUGIN_DIR . 'debug.log');

	// If order is prebooking, do not reduce stock
	if ( $order_type == DC_PLUGIN_PRE_KEY )
	{
		error_log(print_r(['dc_filter_can_reduce_order_stock > WILL CHANGE', $can_reduce, $order_type], true), 3, DC_PLUGIN_DIR . 'debug.log');
		$can_reduce = false;
	}
	error_log(print_r(['dc_filter_can_reduce_order_stock > END', $can_reduce, $order_type], true), 3, DC_PLUGIN_DIR . 'debug.log');

	return $can_reduce;
}
