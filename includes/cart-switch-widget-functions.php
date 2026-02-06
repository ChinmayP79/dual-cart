<?php
defined( 'ABSPATH' ) || exit; // Exit if accessed directly

// Display cart switch widget
function dc_cart_switch_widget_display() {
	$selected = dc_get_current_cart_session_key();
	?>
	<div class="dc-cart-switch-widget">
		<input type="hidden" name="dc_cart_type" value="<?php echo esc_attr( $selected ); ?>" />
		<div id="dc-cart-switch" class="dc-cart-switch-tabs" role="tablist" aria-label="<?php esc_attr_e( 'Cart Type Switch', 'dual-cart' ); ?>">
			<button type="button" class="dc-cart-tab button wp-element-button <?php echo ( $selected === DC_PLUGIN_STD_KEY ? 'active' : '' ); ?>" data-value="<?php echo esc_attr( DC_PLUGIN_STD_KEY ); ?>" aria-pressed="<?php echo esc_attr( $selected === DC_PLUGIN_STD_KEY ? 'true' : 'false' ); ?>"><?php esc_html_e( 'Standard', 'dual-cart' ); ?></button>
			<button type="button" class="dc-cart-tab button wp-element-button <?php echo ( $selected === DC_PLUGIN_PRE_KEY ? 'active' : '' ); ?>" data-value="<?php echo esc_attr( DC_PLUGIN_PRE_KEY ); ?>" aria-pressed="<?php echo esc_attr( $selected === DC_PLUGIN_PRE_KEY ? 'true' : 'false' ); ?>"><?php esc_html_e( 'Prebooking', 'dual-cart' ); ?></button>
		</div>
	</div>
	<?php
}

// Shortcode wrapper: display cart switch widget
function dc_cart_switch_widget_shortcode() {
    ob_start();
    dc_cart_switch_widget_display();
    return ob_get_clean();
}

// Register the shortcode on init
add_action( 'init', 'dc_register_cart_switch_widget_shortcode' );
function dc_register_cart_switch_widget_shortcode() {
    add_shortcode( 'dc_cart_switch_widget', 'dc_cart_switch_widget_shortcode' );
}

// AJAX endpoint: switch the current cart session and return updated widget
add_action( 'wp_ajax_dc_switch_cart', 'dc_switch_cart_ajax' );
add_action( 'wp_ajax_nopriv_dc_switch_cart', 'dc_switch_cart_ajax' );
function dc_switch_cart_ajax() {
	// Use the generalized nonce used by frontend scripts
	check_ajax_referer( 'dc_dual_cart_nonce', 'nonce' );

	$selected = isset( $_POST['dc_cart_type'] ) ? sanitize_text_field( wp_unslash( $_POST['dc_cart_type'] ) ) : '';

	if ( ! in_array( $selected, array( DC_PLUGIN_STD_KEY, DC_PLUGIN_PRE_KEY ), true ) ) {
		wp_send_json_error( __( 'Invalid cart type.', 'dual-cart' ) );
	}

	// Switch sessions
	dc_switch_cart_session( $selected );

	wp_send_json_success( __( 'Cart switched successfully.', 'dual-cart' ) );
}
