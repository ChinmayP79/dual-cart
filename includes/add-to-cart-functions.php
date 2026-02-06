<?php
defined( 'ABSPATH' ) || exit; // Exit if accessed directly

// Add Prebooking Add to Cart button in single product page
add_action( 'woocommerce_after_add_to_cart_button', 'dc_prebooking_add_to_cart_button' );
function dc_prebooking_add_to_cart_button() {
    global $product;

	// Initialize variables to track prebooking status and stock
	$is_prebooking = $has_prebooking_stock = null;

	// Get product type
	$product_type = $product->get_type();

	// Check prebooking status and stock based on product type
	if ( $product_type === Automattic\WooCommerce\Enums\ProductType::SIMPLE )
	{
		// For simple products, get prebooking status and stock directly
		$is_prebooking = $product->get_meta( '_dc_prod_prebooking', true );
		$has_prebooking_stock = $product->get_meta( '_dc_prod_prebooking_stock', true );
	}
	else if ( $product_type === Automattic\WooCommerce\Enums\ProductType::VARIABLE )
	{
		$variations = $product->get_children(); // Get all variation IDs

		// Loop through variations to check if any have prebooking enabled with stock
		foreach ( $variations as $variation_id ) {
			// Get variation product object and then get prebooking status and stock
			$variation = wc_get_product( $variation_id );
			$variation_prebooking = $variation->get_meta( '_dc_prod_prebooking', true );
			$variation_stock = $variation->get_meta( '_dc_prod_prebooking_stock', true );

			// If any variation is available for prebooking, then consider the product as prebooking enabled
			if ( $variation_prebooking === 'yes' && intval( $variation_stock ) > 0 ) {
				$is_prebooking = 'yes';
				$has_prebooking_stock += intval( $variation_stock );
			}
		}
	}

	// Output Prebooking Add to Cart button if applicable
    if ( $is_prebooking === 'yes' && $has_prebooking_stock > 0 ) {
        ?>
        <button type="submit" name="dc_cart_prebooking" value="yes" class="single_add_to_cart_button button alt wp-element-button"><?php esc_html_e( 'Add to Prebooking Cart', 'dual-cart' ); ?></button>
        <?php
    }
}

// Add Prebooking Add to Cart button in shop loop
function dc_prebooking_content_product_add_to_cart_button(WC_Product $product) {
    $product_id = $product->get_id();
    $is_prebooking = get_post_meta( $product_id, '_dc_prod_prebooking', true );
    $has_prebooking_stock = get_post_meta( $product_id, '_dc_prod_prebooking_stock', true );
	?>
	<form class="shop-cart">
		<input type="hidden" name="product-id" value="<?php echo esc_attr( $product_id ); ?>" />
		<input type="hidden" name="quantity" value="1" />

		<?php
		if ( $product->is_purchasable() && $product->is_in_stock() ) {
			?>
			<button type="submit" name="add-to-cart" value="yes" class="single_add_to_cart_button button wp-element-button"><?php esc_html_e( 'Add to Cart', 'dual-cart' ); ?></button>
			<?php
		}
		?>
		<?php
		if ( $is_prebooking === 'yes' && $has_prebooking_stock > 0 ) {
			?>
			<button type="submit" name="dc_cart_prebooking" value="yes" class="single_add_to_cart_button button wp-element-button"><?php esc_html_e( 'Add to Prebooking Cart', 'dual-cart' ); ?></button>
			<?php
		}
		?>
	</form>
	<?php
}

// Modify add to cart link in shop loop to include Prebooking button
add_filter( 'woocommerce_loop_add_to_cart_link', 'dc_modify_loop_add_to_cart_link', 9999, 3 );
function dc_modify_loop_add_to_cart_link( $link, $product, $args ) {
	if ( $product->add_to_cart_text() == 'Add to cart' ) {
		ob_start();
		dc_prebooking_content_product_add_to_cart_button($product);
		$link = ob_get_clean();
	}
	return $link;
}

// Custom AJAX add to cart
add_action( 'wp_ajax_custom_add_to_cart', 'custom_add_to_cart' );
add_action( 'wp_ajax_nopriv_custom_add_to_cart', 'custom_add_to_cart' );
function custom_add_to_cart() {
	check_ajax_referer('dc_dual_cart_nonce', 'nonce');

	// check if product ID is provided
	if ( empty( $_POST['product_id'] ) ) {
		wp_send_json_error( __( 'Product ID missing.', 'dual-cart' ) );
		wp_die();
	}

	$product_id   = intval( $_POST['product_id'] );
	$quantity     = intval( $_POST['quantity'] ?? 1 );
	$variation_id = intval( $_POST['variation_id'] ?? 0 );
	$variation    = isset( $_POST['variation'] ) ? array_map( 'wc_clean', (array) $_POST['variation'] ) : array();
	$prebooking   = isset( $_POST['dc_cart_prebooking'] ) ? $_POST['dc_cart_prebooking'] : '';

	// Determine cart type based on prebooking flag
	$is_prebooking   = $prebooking === 'yes';
	$cart_key   = $is_prebooking ? DC_PLUGIN_PRE_KEY : DC_PLUGIN_STD_KEY;

    dc_switch_cart_session( $cart_key ); // Switch to the appropriate cart session

	ksort( $variation ); // Normalize variation attributes

	// Get product correctly
	$final_product_id = $variation_id ? $variation_id : $product_id;
    $product = wc_get_product( $final_product_id );

	// Prepare cart item data
	$cart_item_data = array();
	$cart_item_data['cart_key'] = $cart_key;
	$cart_item_data['is_prebooking'] = $is_prebooking;

	error_log(print_r(['custom_add_to_cart', $product->is_in_stock(), $product->get_stock_quantity() ], true), 3, DC_PLUGIN_DIR . 'debug.log');

	$added = false; // Initialize variable to track if product was added successfully or if there was an error

	// Check stock availability
	if ($product->is_in_stock() && $product->get_stock_quantity() >= $quantity)
	{
		// Add to cart
		$added = WC()->cart->add_to_cart(
			$product_id,
			$quantity,
			$variation_id,
			$variation,
			$cart_item_data
		);

		dc_save_cart_session( $cart_key ); // Save current cart session
	}
	else
	{
		// If product is out of stock, set $added to WP_Error with appropriate message
		$added = new WP_Error( 'out_of_stock', __( 'Product is out of stock.', 'dual-cart' ) );
	}

	if ( $added && ! is_wp_error( $added ) ) {
		wp_send_json_success( __( 'Product added to cart.', 'dual-cart' ) );
	} else {
		wp_send_json_error( __( 'Unable to add to cart, Due to: ', 'dual-cart' ) . ( is_wp_error( $added ) ? $added->get_error_message() : __( 'Unknown error', 'dual-cart' ) ) );
	}

	wp_die();
}
