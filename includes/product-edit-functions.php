<?php
defined( 'ABSPATH' ) || exit; // Exit if accessed directly

// Product edit metabox to set '_dc_prod_prebooking' meta
add_action( 'add_meta_boxes', 'dc_add_product_prebooking_metabox' );
function dc_add_product_prebooking_metabox() {
    add_meta_box(
        'dc_prod_prebooking',
        'Dual Cart: Prebooking',
        'dc_product_prebooking_metabox_callback',
        'product',
        'normal',
        'high'
    );
}

// Metabox callback to display '_dc_prod_prebooking' checkbox and '_dc_prod_prebooking_stock' integer field
function dc_product_prebooking_metabox_callback( $post ) {
    $product = wc_get_product( $post->ID );
    $value = $product->get_meta( '_dc_prod_prebooking', true );
    $checked = ( $value === 'yes' ) ? 'checked' : '';

    // Get stock meta (ensure integer)
    $stock_value = $product->get_meta( '_dc_prod_prebooking_stock', true );
    $stock_value = '' === $stock_value ? '' : intval( $stock_value );
    ?>
    <p>
        <label for="dc_prod_prebooking_field">
            <?php esc_html_e( 'Prebooking:', 'dual-cart' ); ?>
            <input type="checkbox" id="dc_prod_prebooking_field" name="dc_prod_prebooking" value="yes" <?php echo $checked; ?> />
            <?php esc_html_e( 'Enable for prebooking', 'dual-cart' ); ?>
        </label>
    </p>

    <p>
        <label for="dc_prod_prebooking_stock_field">
            <?php esc_html_e( 'Prebooking stock:', 'dual-cart' ); ?>
            <input type="number" id="dc_prod_prebooking_stock_field" name="dc_prod_prebooking_stock" value="<?php echo esc_attr( $stock_value ); ?>" min="0" step="1" />
        </label>
    </p>
    <?php
}

// Save metabox data for '_dc_prod_prebooking' and '_dc_prod_prebooking_stock' meta
add_action( 'save_post', 'dc_save_product_prebooking_meta', 10, 2 );
function dc_save_product_prebooking_meta( $post_id, $post ) {
    if ( $post->post_type !== 'product' ) return;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
    if ( ! current_user_can( 'edit_post', $post_id ) ) return;

    $value = ( isset( $_POST['dc_prod_prebooking'] ) && 'yes' === $_POST['dc_prod_prebooking'] ) ? 'yes' : 'no';

    // Sanitize and ensure integer (non-negative)
    $stock = isset( $_POST['dc_prod_prebooking_stock'] ) ? intval( $_POST['dc_prod_prebooking_stock'] ) : 0;
    if ( $stock < 0 ) {
        $stock = 0;
    }

    $product = wc_get_product( $post->ID );
    $product->update_meta_data( '_dc_prod_prebooking', $value );
    $product->update_meta_data( '_dc_prod_prebooking_stock', $stock );
    $product->save(); // Ensure product data is saved
}
