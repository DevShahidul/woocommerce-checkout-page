<?php
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_style' );
function hello_elementor_child_style() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
	wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style') );
}
add_action('wp_enqueue_scripts', 'hello_elementor_child_scripts');

function hello_elementor_child_scripts() {
    if (is_checkout()) {
        wp_enqueue_script(
            'custom-checkout',
            get_stylesheet_directory_uri() . '/assets/js/custom-checkout.js',
            array('jquery'),
            '1.0.0',
            true
        );

        // Child style
        wp_enqueue_style( 
            'custom-checkout', 
            get_stylesheet_directory_uri() . '/assets/css/checkout.css' 
        );

        // Add localized data if needed
        wp_localize_script('custom-checkout', 'checkoutData', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('custom-checkout-nonce')
        ));
    }
}

add_action('init', 'setup_woocommerce_support');
function setup_woocommerce_support() {
    add_theme_support('woocommerce');
    // Force shipping to be enabled
    if (class_exists('WC_Shipping')) {
        WC()->shipping()->load_shipping_methods();
    }
}


if ( class_exists( 'WooCommerce' ) ) {
    require get_stylesheet_directory() . '/inc/products/products.php';
    require get_stylesheet_directory() . '/inc/products/selector/product-selector.php';
	require get_stylesheet_directory() . '/inc/woocommerce.php';
}

