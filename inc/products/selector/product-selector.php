<?php

// Add shortcode for product selector
function passport_type_selector_shortcode() {
    // Get published products
    $products = wc_get_products(array(
        'status' => 'publish',
        'limit' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
    ));

    ob_start();
    ?>
    <div class="product-selector-wrapper form-row form-row-wide validate-required">
        <label for="product-selector"><?php _e('Passport type', 'woocommerce'); ?></label>
        <select name="passport_type_selector" id="product-selector" class="product-dropdown woocommerce-input-wrapper">
            
            <?php foreach ($products as $product) : ?>
                <option value="<?php echo esc_attr($product->get_id()); ?>" 
                        data-price="<?php echo esc_attr($product->get_price()); ?>"
                        data-stock="<?php echo esc_attr($product->get_stock_quantity()); ?>">
                    <?php echo esc_html($product->get_name()); ?> 
                    <!-- - <?//php echo wp_strip_all_tags(wc_price($product->get_price())); ?> -->
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('passport_type_selector', 'passport_type_selector_shortcode');

// Empty cart via AJAX
add_action('wp_ajax_empty_cart', 'ajax_empty_cart');
add_action('wp_ajax_nopriv_empty_cart', 'ajax_empty_cart');
function ajax_empty_cart() {
    WC()->cart->empty_cart();
    wp_send_json_success();
}


// Add AJAX handler for adding to cart
function handle_ajax_add_to_cart() {
    check_ajax_referer('add-to-cart-nonce', 'nonce');

    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 1;

    if ($product_id > 0) {
        // Ensure only one item is added
        WC()->cart->empty_cart();
        $added = WC()->cart->add_to_cart($product_id, 1);
        if ($added) {
            $data = array(
                'success' => true,
                'message' => __('Passport Type Selected!', 'woocommerce'),
                'cart_count' => WC()->cart->get_cart_contents_count(),
                'cart_total' => WC()->cart->get_cart_total()
            );
        } else {
            $data = array(
                'success' => false,
                'message' => __('Failed to select Passport Type.', 'woocommerce')
            );
        }
    } else {
        $data = array(
            'success' => false,
            'message' => __('Invalid Passport Type.', 'woocommerce')
        );
    }

    wp_send_json($data);
}
add_action('wp_ajax_add_to_cart_ajax', 'handle_ajax_add_to_cart');
add_action('wp_ajax_nopriv_add_to_cart_ajax', 'handle_ajax_add_to_cart');

// Handle cart emptying
add_action('wp_ajax_empty_cart', 'empty_cart');
add_action('wp_ajax_nopriv_empty_cart', 'empty_cart');
function empty_cart() {
    WC()->cart->empty_cart();
    wp_send_json_success();
}

// Handle cart fragments
add_filter('woocommerce_update_order_review_fragments', 'custom_order_summary_fragments');
function custom_order_summary_fragments($fragments) {
    // Get subtotal
    $subtotal = WC()->cart->get_subtotal();
    
    // Get total
    $total = WC()->cart->get_total('edit');
    
    // Format prices
    $fragments['.subtotal-amount .amount'] = '<span class="amount">' . wc_price($subtotal) . '</span>';
    $fragments['.total-amount .amount'] = '<span class="amount">' . wc_price($total) . '</span>';
    
    return $fragments;
}



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
function enqueue_passport_type_selector_scripts() {
    wp_enqueue_style('product-selector-style', get_stylesheet_directory_uri() . '/inc/products/selector/assets/css/product-selector.css');
    wp_enqueue_script('product-selector-script', get_stylesheet_directory_uri() . '/inc/products/selector/assets/js/product-selector.js', array('jquery', 'wc-checkout'), '1.0', true);
    wp_localize_script('product-selector-script', 'ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('add-to-cart-nonce')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_passport_type_selector_scripts');