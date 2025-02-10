<?php
// Bypass Empty Cart Restriction
add_filter('woocommerce_checkout_redirect_empty_cart', '__return_false');

// Custom Checkout Access Function
add_action('template_redirect', 'allow_empty_cart_checkout');
function allow_empty_cart_checkout() {
    // Check if we're on the checkout page
    if (is_checkout() && WC()->cart->is_empty()) {
        // Option 1: Always allow
        add_filter('woocommerce_checkout_available', '__return_true');
        
        // Option 2: Conditional access (e.g., only for specific user roles)
        // $allowed_roles = ['administrator', 'shop_manager'];
        // if (!array_intersect($allowed_roles, wp_get_current_user()->roles)) {
        //     // Optional: Add a notice
        //     wc_add_notice('Please select a product before proceeding.', 'error');
        // }
    }
}

// Remove Cart Validation
add_filter('woocommerce_checkout_cart_empty', '__return_false');

// Custom Routing for Empty Cart Checkout
add_filter('woocommerce_is_checkout', 'custom_is_checkout_with_empty_cart');
function custom_is_checkout_with_empty_cart($is_checkout) {
    return $is_checkout || is_page('checkout');
}

// Prevent session expiration on checkout
add_action('wp_loaded', 'regenerate_woocommerce_session');
function regenerate_woocommerce_session() {
    if (is_checkout() || is_cart()) {
        if (!WC()->session->has_session()) {
            WC()->session->set_customer_session_cookie(true);
        }
    }
}

add_action('init', 'custom_woocommerce_session_management');
function custom_woocommerce_session_management() {
    // Extend session lifetime
    add_filter('woocommerce_login_session_length', function() {
        return 30 * DAY_IN_SECONDS;
    });

    // Prevent cart destruction
    add_filter('woocommerce_delete_cart_after_logout', '__return_false');
    
    // Enable persistent cart
    add_filter('woocommerce_persistent_cart_enabled', '__return_true');
}

// // Custom notices for empty cart scenarios
// add_action('woocommerce_before_checkout_form', 'display_empty_cart_product_selector');
// function display_empty_cart_product_selector() {
//     if (WC()->cart->is_empty()) {
//         // Display product selection dropdown
//         woocommerce_product_dropdown();
//     }
// }

// Selective Checkout Access
// add_filter('woocommerce_checkout_redirect_empty_cart', 'custom_empty_cart_checkout_access');
// function custom_empty_cart_checkout_access($redirect) {
//     // Customize checkout access logic
//     if (is_user_logged_in()) {
//         return false; // Allow logged-in users
//     }
//     return $redirect; // Default behavior for other users
// }