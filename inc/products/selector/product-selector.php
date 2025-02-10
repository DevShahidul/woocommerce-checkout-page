<?php

// Add shortcode for product selector
function product_selector_shortcode() {
    // Get published products
    $products = wc_get_products(array(
        'status' => 'publish',
        'limit' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ));

    ob_start();
    ?>
    <div class="product-selector-wrapper">
        <select name="product_selector" id="product-selector" class="product-dropdown">
            <option value=""><?php _e('Select your passport type', 'woocommerce'); ?></option>
            <?php foreach ($products as $product) : ?>
                <option value="<?php echo esc_attr($product->get_id()); ?>" 
                        data-price="<?php echo esc_attr($product->get_price()); ?>"
                        data-stock="<?php echo esc_attr($product->get_stock_quantity()); ?>">
                    <?php echo esc_html($product->get_name()); ?> 
                    <!-- - <?//php echo wp_strip_all_tags(wc_price($product->get_price())); ?> -->
                </option>
            <?php endforeach; ?>
        </select>
        <!-- <div id="product-details" class="product-details" style="display: none;">
            <p class="selected-product"></p>
            <p class="stock-status"></p>
            <div class="quantity">
                <label for="product-quantity">Quantity:</label>
                <input type="number" id="product-quantity" min="1" value="1">
            </div>
            <button type="button" id="add-to-cart-btn" class="button add-to-cart">
                <?//php _e('Add to Cart', 'woocommerce'); ?>
            </button>
        </div> -->
        <!-- <div id="cart-message" class="cart-message"></div> -->
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('product_selector', 'product_selector_shortcode');

// Empty cart via AJAX
add_action('wp_ajax_empty_cart', 'ajax_empty_cart');
add_action('wp_ajax_nopriv_empty_cart', 'ajax_empty_cart');
function ajax_empty_cart() {
    WC()->cart->empty_cart();
    wp_send_json_success();
}

// Add single product via AJAX
add_action('wp_ajax_add_single_product', 'ajax_add_single_product');
add_action('wp_ajax_nopriv_add_single_product', 'ajax_add_single_product');
function ajax_add_single_product() {
    $product_id = intval($_POST['product_id']);
    
    // Ensure only one item is added
    WC()->cart->empty_cart();
    WC()->cart->add_to_cart($product_id, 1);
    
    wp_send_json_success();
}

// // Add AJAX handler for adding to cart
// function handle_ajax_add_to_cart() {
//     check_ajax_referer('add-to-cart-nonce', 'nonce');

//     $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
//     $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 1;

//     if ($product_id > 0) {
//         $added = WC()->cart->add_to_cart($product_id, $quantity);
//         if ($added) {
//             $data = array(
//                 'success' => true,
//                 'message' => __('Passport Type Selected!', 'woocommerce'),
//                 'cart_count' => WC()->cart->get_cart_contents_count(),
//                 'cart_total' => WC()->cart->get_cart_total()
//             );
//         } else {
//             $data = array(
//                 'success' => false,
//                 'message' => __('Failed to select Passport Type.', 'woocommerce')
//             );
//         }
//     } else {
//         $data = array(
//             'success' => false,
//             'message' => __('Invalid Passport Type.', 'woocommerce')
//         );
//     }

//     wp_send_json($data);
// }
// add_action('wp_ajax_add_to_cart_ajax', 'handle_ajax_add_to_cart');
// add_action('wp_ajax_nopriv_add_to_cart_ajax', 'handle_ajax_add_to_cart');

// // Add AJAX handler for update checkout order summary
// add_action('wp_ajax_update_checkout_product', 'ajax_update_checkout_product');
// add_action('wp_ajax_nopriv_update_checkout_product', 'ajax_update_checkout_product');
// function ajax_update_checkout_product() {
//     // Sanitize and validate product ID
//     $product_id = intval($_POST['product_id']);
    
//     // Empty existing cart
//     WC()->cart->empty_cart();
    
//     // Add new product
//     WC()->cart->add_to_cart($product_id, 1);
    
//     // Send success response
//     wp_send_json_success();
// }

add_action('wp_ajax_get_product_details', 'ajax_get_product_details');
add_action('wp_ajax_nopriv_get_product_details', 'ajax_get_product_details');
function ajax_get_product_details() {
    $product_id = intval($_POST['product_id']);
    $product = wc_get_product($product_id);

    if (!$product) {
        wp_send_json_error('Product not found');
    }

    // Empty cart and add new product
    WC()->cart->empty_cart();
    WC()->cart->add_to_cart($product_id, 1);

    // Prepare product data
    $response = [
        'name' => $product->get_name(),
        'price_html' => $product->get_price_html(), // Uses WooCommerce currency formatting
        'id' => $product_id
    ];

    wp_send_json_success($response);
}

// Enqueue necessary scripts and styles
function enqueue_product_selector_scripts() {
    wp_enqueue_style('product-selector-style', get_stylesheet_directory_uri() . '/inc/products/selector/assets/css/product-selector.css');
    wp_enqueue_script('product-selector-script', get_stylesheet_directory_uri() . '/inc/products/selector/assets/js/product-selector.js', array('jquery', 'wc-checkout'), '1.0', true);
    wp_localize_script('product-selector-script', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('add-to-cart-nonce')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_product_selector_scripts');