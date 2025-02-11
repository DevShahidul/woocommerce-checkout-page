<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.4.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Before checkout form hook
do_action('woocommerce_before_checkout_form', $checkout);

// If checkout registration is disabled and not logged in, the user cannot checkout
if (!$checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) {
	echo esc_html(apply_filters('woocommerce_checkout_must_be_logged_in_message', __('You must be logged in to checkout.', 'woocommerce')));
	return;
}

?>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">
    
    <div class="checkout-layout">
        <div class="checkout-main">
            <div class="checkout-steps-wrapper">
                <!-- Progress Indicator -->
                <div class="checkout-progress">
                    <div class="step-indicator active" id="step-indicator-1">
                        <span class="step-number">1</span>
                        <span class="step-title">Application</span>
                    </div>
                    <div class="step-indicator" id="step-indicator-2">
                        <span class="step-number">2</span>
                        <span class="step-title">Delivery Address</span>
                    </div>
                    <div class="step-indicator" id="step-indicator-3">
                        <span class="step-number">3</span>
                        <span class="step-title">Submit Order</span>
                    </div>
                </div>

                <!-- Step 1: Application -->
                <div id="checkout-step-1" class="checkout-step">
                    <h2>Applicant Info</h2>
                    <?php do_action('woocommerce_checkout_before_customer_details'); ?>
                    <div class="d-flex flex-column">
                        <div class="applicant-information clearfix">
                            <?php custom_checkout_fields($checkout); ?>
                        </div>
                        <div class="passport-type-selector-wrapper clearfix">
                            <?php passport_types($checkout); ?>
                        </div>
                        <div class="delivery-options clearfix">
                            <?php acf_delivery_options($checkout); ?>
                        </div>
                        <div class="travel-information clearfix">
                            <?php acf_travel_information_fields($checkout); ?>
                        </div>
                        <div class="optional-addons clearfix">
                            <?php acf_optional_delivery_addons($checkout); ?>
                        </div>
                        <button class="checkout-next-step button alt">Continue to Delivery Address</button>
                    </div>
                </div>

                <!-- Step 2: Delivery Address -->
                <div id="checkout-step-2" class="checkout-step" style="display: none;">
                    <h2>Delivery Address</h2>
                    <?php do_action('woocommerce_checkout_shipping'); ?>
                    <div class="d-flex flex-column">
                        <div class="delivery-address">
                            <?php 
                                $fields = $checkout->get_checkout_fields('shipping');
                                foreach ($fields as $key => $field) {
                                    woocommerce_form_field($key, $field, $checkout->get_value($key));
                                }
                            ?>
                        </div>
                        <div class="shipping-speed">
                            <?php acf_shipping_speed_options($checkout); ?>
                        </div>
                        <div class="step-buttons">
                            <button class="checkout-prev-step button">Back</button>
                            <button class="checkout-next-step button alt">Continue to Payment</button>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Submit Order -->
                <div id="checkout-step-3" class="checkout-step" style="display: none;">
                    <h2>Submit Order</h2>
                    <?php do_action('woocommerce_checkout_before_order_review'); ?>
                    <div id="order_review" class="woocommerce-checkout-review-order">
                        <?php do_action('woocommerce_checkout_order_review'); ?>
                    </div>
                    <div class="step-buttons">
                        <button class="checkout-prev-step button">Back</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Summary Sidebar -->
        <div class="checkout-sidebar">
            <div class="order-summary">
                <div class="preloader-overlay" aria-hidden="true">
                    <div class="loading-spinner"></div>
                    <span class="screen-reader-text"><?php _e('Updating cart...', 'woocommerce'); ?></span>
                </div>
                <h3><?php esc_html_e('Order Summary', 'woocommerce'); ?></h3>
                <?php do_action('woocommerce_checkout_before_order_review'); ?>
                <div class="order-summary-content">
                    <div class="order-summary-item recipient-info">
                        <span><?php esc_html_e('Recipient:', 'woocommerce'); ?></span>
                        <span class="applicant-name">Not specified</span>
                    </div>
                    
                    <?php
                    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                        $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                        echo '<div class="order-summary-item product-info">';
                        echo '<span class="item-name">' . $_product->get_name() . '</span>';
                        echo '<span class="item-price">' . WC()->cart->get_product_price($_product) . '</span>';
                        echo '</div>';
                    }
                    ?>
                    
                    <div class="order-summary-item delivery-option"></div>
                    
                    <div class="order-summary-item subtotal">
                        <span><?php esc_html_e('Subtotal', 'woocommerce'); ?></span>
                        <span class="subtotal-amount"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
                    </div>
                    
                    <div class="order-summary-item total">
                        <span><?php esc_html_e('Total', 'woocommerce'); ?></span>
                        <span class="total-amount"><?php echo WC()->cart->get_total(); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>



