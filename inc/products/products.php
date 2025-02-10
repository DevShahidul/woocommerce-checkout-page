<?php
/**
 * Get all products
 *
 * @param array $args Additional arguments for the query
 * @return array List of products
 */
function get_all_woo_products($args = array()) {
    $default_args = array(
        'status' => 'publish',
        'limit' => -1,
        'orderby' => 'date',
        'order' => 'DESC',
        'return' => 'objects'
    );
    
    $args = wp_parse_args($args, $default_args);
    
    return wc_get_products($args);
}

/**
 * Get products by category
 *
 * @param string|array $category Category slug(s)
 * @param int $limit Number of products to return
 * @return array List of products
 */
function get_products_by_category($category, $limit = -1) {
    $args = array(
        'category' => array($category),
        'limit' => $limit
    );
    
    return wc_get_products($args);
}

/**
 * Get featured products
 *
 * @param int $limit Number of products to return
 * @return array List of products
 */
function get_featured_products($limit = -1) {
    $args = array(
        'featured' => true,
        'limit' => $limit
    );
    
    return wc_get_products($args);
}

/**
 * Get products on sale
 *
 * @param int $limit Number of products to return
 * @return array List of products
 */
function get_sale_products($limit = -1) {
    $args = array(
        'status' => 'publish',
        'limit' => $limit,
        'include' => array_merge(array(0), wc_get_product_ids_on_sale())
    );
    
    return wc_get_products($args);
}

/**
 * Get recent products
 *
 * @param int $limit Number of products to return
 * @return array List of products
 */
function get_recent_products($limit = 10) {
    $args = array(
        'limit' => $limit,
        'orderby' => 'date',
        'order' => 'DESC'
    );
    
    return wc_get_products($args);
}

/**
 * Get best selling products
 *
 * @param int $limit Number of products to return
 * @return array List of products
 */
function get_bestseller_products($limit = 10) {
    $args = array(
        'limit' => $limit,
        'orderby' => 'meta_value_num',
        'meta_key' => 'total_sales',
        'order' => 'DESC'
    );
    
    return wc_get_products($args);
}

/**
 * Get products by price range
 *
 * @param float $min_price Minimum price
 * @param float $max_price Maximum price
 * @return array List of products
 */
function get_products_by_price_range($min_price, $max_price) {
    $args = array(
        'price_range' => array(
            'min_price' => $min_price,
            'max_price' => $max_price
        )
    );
    
    return wc_get_products($args);
}

/**
 * Get products by custom query
 *
 * @param array $query_args WP_Query arguments
 * @return array List of products
 */
function get_products_by_custom_query($query_args = array()) {
    $default_args = array(
        'post_type' => 'product',
        'post_status' => 'publish',
        'posts_per_page' => -1
    );
    
    $args = wp_parse_args($query_args, $default_args);
    $query = new WP_Query($args);
    
    return array_map('wc_get_product', $query->posts);
}

/**
 * Get related products
 *
 * @param int $product_id Product ID
 * @param int $limit Number of products to return
 * @return array List of products
 */
function get_related_woo_products($product_id, $limit = 5) {
    $product = wc_get_product($product_id);
    if (!$product) return array();
    
    $related_products = array_filter(array_map('wc_get_product', 
        wc_get_related_products($product_id, $limit)));
        
    return $related_products;
}

/**
 * Get cross-sell products
 *
 * @param int $product_id Product ID
 * @return array List of products
 */
function get_crosssell_products($product_id) {
    $product = wc_get_product($product_id);
    if (!$product) return array();
    
    return array_filter(array_map('wc_get_product', $product->get_cross_sell_ids()));
}

/**
 * Example usage of the above functions
 */
function example_product_listing() {
    // Get all products
    $all_products = get_all_woo_products();
    
    // Get products from specific category
    $category_products = get_products_by_category('t-shirts', 10);
    
    // Get featured products
    $featured_products = get_featured_products(5);
    
    // Get products on sale
    $sale_products = get_sale_products(5);
    
    // Get recent products
    $recent_products = get_recent_products(5);
    
    // Get best selling products
    $bestsellers = get_bestseller_products(5);
    
    // Get products in price range
    $price_range_products = get_products_by_price_range(10, 100);
    
    // Get products with custom query
    $custom_query_products = get_products_by_custom_query(array(
        'meta_query' => array(
            array(
                'key' => '_stock_status',
                'value' => 'instock'
            )
        )
    ));
    
    // Get related products
    $related_products = get_related_woo_products(123, 5);
    
    // Get cross-sell products
    $crosssell_products = get_crosssell_products(123);
    
    // Example of displaying products
    foreach ($all_products as $product) {
        echo $product->get_name() . ' - ' . $product->get_price_html() . '<br>';
    }
}