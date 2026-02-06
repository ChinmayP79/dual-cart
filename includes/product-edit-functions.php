<?php
defined( 'ABSPATH' ) || exit; // Exit if accessed directly

// Add custom tab to product data metabox for simple products
add_filter( 'woocommerce_product_data_tabs', 'dc_prod_simp_tab' );
function dc_prod_simp_tab( $tabs) {
	$tabs['dc_tab'] = array(
		'label'     => __( 'Dual Cart: Prebooking', 'dual-cart' ),
		'target'    => 'dc_prod_simp_tab_options',
		'class'     => array( 'show_if_simple' ),
	);
	return $tabs;
}

// Content for the custom tab for simple products
add_filter( 'woocommerce_product_data_panels', 'dc_prod_simp_tab_content' );
function dc_prod_simp_tab_content() {
	global $post;
	?>
    <div id='dc_prod_simp_tab_options' class='panel woocommerce_options_panel'>
        <div class='options_group'>
        <?php
        woocommerce_wp_checkbox( array(
            'id' => 'dc_prod_prebooking',
            'label' => __( 'Prebooking', 'dual-cart' ),
            'desc_tip' => true,
            'description' => __( 'Enable prebooking for this product.', 'dual-cart' ),
            'value' => get_post_meta( $post->ID, '_dc_prod_prebooking', true ),
        ));
        woocommerce_wp_text_input( array(
            'id' => 'dc_prod_prebooking_stock',
            'label' => __( 'Prebooking Stock', 'dual-cart' ),
            'desc_tip' => true,
            'description' => __( 'Enter Prebooking Stock Quantity.', 'dual-cart' ),
            'type' => 'number',
            'value' => get_post_meta( $post->ID, '_dc_prod_prebooking_stock', true ),
            'data_type' => 'stock',
        ));
        ?>
        </div>
    </div>
    <?php
}

// Save custom fields from the custom tab for simple products
add_action( 'woocommerce_process_product_meta_simple', 'dc_prod_simp_tab_save'  );
function dc_prod_simp_tab_save( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    // Save prebooking meta
    $checkbox = ( isset( $_POST['dc_prod_prebooking'] ) && 'yes' === $_POST['dc_prod_prebooking'] ) ? 'yes' : 'no';

    // Sanitize and ensure integer (non-negative)
    $stock = isset( $_POST['dc_prod_prebooking_stock'] ) ? intval( $_POST['dc_prod_prebooking_stock'] ) : 0;
    if ( $stock < 0 ) $stock = 0;

    // Update product meta
    update_post_meta( $post_id, '_dc_prod_prebooking', $checkbox );
    update_post_meta( $post_id, '_dc_prod_prebooking_stock', $stock );
}

// Add custom fields to variable product variations
add_action( 'woocommerce_variation_options_pricing', 'equinavia_add_custom_fields_to_variations_cart_switcher', 10, 3 );
function equinavia_add_custom_fields_to_variations_cart_switcher( $loop, $variation_data, $variation ) {
    woocommerce_wp_checkbox( array(
        'id' => "dc_prod_prebooking[{$loop}]",
        'label' => __( 'Prebooking', 'dual-cart' ),
        'desc_tip' => true,
        'description' => __( 'Enable prebooking for this variation.', 'dual-cart' ),
        'value' => get_post_meta( $variation->ID, '_dc_prod_prebooking', true ),
        'wrapper_class' => 'form-row form-row-first',
    ));
    woocommerce_wp_text_input( array(
        'id' => "dc_prod_prebooking_stock[{$loop}]",
        'label' => __( 'Prebooking Stock', 'dual-cart' ),
        'desc_tip' => true,
        'description' => __( 'Enter Prebooking Stock Quantity.', 'dual-cart' ),
        'type' => 'number',
        'value' => get_post_meta( $variation->ID, '_dc_prod_prebooking_stock', true ),
        'data_type' => 'stock',
        'wrapper_class' => 'form-row form-row-last',
    ));
}

// Save custom fields for variable product variations
add_action( 'woocommerce_save_product_variation', 'save_custom_checkbox_variation_field', 10, 2 );
function save_custom_checkbox_variation_field( $variation_id, $i ) {
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $variation_id ) ) return;

    // Save prebooking meta
    $checkbox = ( isset( $_POST['dc_prod_prebooking'][$i] ) && 'yes' === $_POST['dc_prod_prebooking'][$i] ) ? 'yes' : 'no';

    // Sanitize and ensure integer (non-negative)
    $stock = isset( $_POST['dc_prod_prebooking_stock'][$i] ) ? intval( $_POST['dc_prod_prebooking_stock'][$i] ) : 0;
    if ( $stock < 0 ) $stock = 0;

    // Update product meta
    update_post_meta( $variation_id, '_dc_prod_prebooking', $checkbox );
    update_post_meta( $variation_id, '_dc_prod_prebooking_stock', $stock );
}
