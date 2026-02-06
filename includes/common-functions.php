<?php
defined( 'ABSPATH' ) || exit; // Exit if accessed directly

// Enqueue frontend scripts, styles and localize AJAX parameters
add_action( 'wp_enqueue_scripts', 'dc_enqueue_dual_cart_script' );
function dc_enqueue_dual_cart_script() {
    if ( is_admin() ) return;

    // Enqueue script
    $handle = 'dc-dual-cart';
    $src = DC_PLUGIN_URL . 'assets/js/dc-dual-cart.js';
    wp_enqueue_script( $handle, $src, array( 'jquery' ), time() );

    // Enqueue styles
    $css_handle = 'dc-dual-cart-style';
    $css_src = DC_PLUGIN_URL . 'assets/css/dc-dual-cart.css';
    wp_enqueue_style( $css_handle, $css_src, array(), time() );

    // Localize script with AJAX parameters
    wp_localize_script( $handle, 'dc_dual_cart_params', array(
        'ajax_url' => admin_url( 'admin-ajax.php' ),
        'nonce'    => wp_create_nonce( 'dc_dual_cart_nonce' ),
    ) );
}

// Get current cart session key
function dc_get_current_cart_session_key() {
	if ( ! WC()->session ) return DC_PLUGIN_STD_KEY; // Default to standard cart if session not available

	$current_cart = ! empty( WC()->session->get( 'current_cart' ) ) ? WC()->session->get( 'current_cart' ) : DC_PLUGIN_STD_KEY;
	return $current_cart;
}

// Function to switch cart session based on selected cart type
function dc_switch_cart_session( $selected_cart ) {
    // Ensure WooCommerce session and cart are initialized
    if ( empty( WC()->session ) || empty( WC()->cart ) ) return;

    // Get the current cart type from session
    $current_cart = dc_get_current_cart_session_key();

    if ( $selected_cart === $current_cart ) return; // No need to switch

    // Save current cart to its respective session
    dc_save_cart_session( $current_cart );

    // Set the new current cart key
    WC()->session->set( 'current_cart', $selected_cart );

    // Load selected cart into WC()->cart
    dc_load_cart_session( $selected_cart );
}

// Function to save current cart to its respective session
function dc_save_cart_session( $cart_key ) {
    if ( empty( WC()->session ) || empty( WC()->cart ) ) return;

    // Save current WC()->cart contents to the named session key
    $contents = ! empty( WC()->cart->cart_contents ) ? WC()->cart->cart_contents : array();
    WC()->session->set( $cart_key, $contents );

    // Also keep the canonical 'cart' session in sync
    WC()->session->set( 'cart', $contents );
}

// Function to load cart session based on selected cart type
function dc_load_cart_session( $cart_key ) {
    if ( empty( WC()->session ) || empty( WC()->cart ) ) return;

    // Pull the stored cart array for the requested key
    $cart = WC()->session->get( $cart_key );
    $cart = ! empty( $cart ) ? $cart : array();

    // Set the raw session 'cart' value
    WC()->session->set( 'cart', $cart );

    // Sync the WC()->cart object
    WC()->cart->cart_contents = $cart;
    if ( method_exists( WC()->cart, 'set_session' ) ) WC()->cart->set_session(); // save cart to session storage
    if ( method_exists( WC()->cart, 'calculate_totals' ) ) WC()->cart->calculate_totals(); // recalculate totals
}
