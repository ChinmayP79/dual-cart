<?php
/*
Plugin Name: Dual Cart for WooCommerce
Description: Dual Cart for WooCommerce, a PET project, by Chinmay.
Version: 1.0
Author: Chinmay
*/

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

define('DC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('DC_PLUGIN_STD_KEY', 'dc_cart_standard');
define('DC_PLUGIN_PRE_KEY', 'dc_cart_prebooking');

// Register Custom Functions
require_once DC_PLUGIN_DIR . 'includes/common-functions.php';
require_once DC_PLUGIN_DIR . 'includes/woo-com-hook-functions.php';
require_once DC_PLUGIN_DIR . 'includes/product-edit-functions.php';
require_once DC_PLUGIN_DIR . 'includes/add-to-cart-functions.php';
require_once DC_PLUGIN_DIR . 'includes/cart-switch-widget-functions.php';
require_once DC_PLUGIN_DIR . 'includes/order-functions.php';
