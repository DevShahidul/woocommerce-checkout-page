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
                <div class="elementor-element elementor-element-7c7338f2 elementor-icon-list--layout-inline elementor-align-center checkout-steps elementor-list-item-link-full_width elementor-widget elementor-widget-icon-list" data-id="7c7338f2" data-element_type="widget" data-widget_type="icon-list.default">
                    <div class="elementor-widget-container">
                        <ul class="elementor-icon-list-items elementor-inline-items">
                            <li class="elementor-icon-list-item elementor-inline-item step-1 active" data-step="application" id="step-indicator-1">
                                <span class="elementor-icon-list-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M6.66667 10.1665H12.5" stroke="#222222" stroke-width="1.4" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6.66667 13.5H10.3167" stroke="#222222" stroke-width="1.4" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path><path d="M5.83333 6.6665H14.1667C14.1667 6.6665 15 6.6665 15 5.4165C15 4.1665 14.1667 4.1665 14.1667 4.1665H5.83333C5.83333 4.1665 5 4.1665 5 5.4165C5 6.6665 5.83333 6.6665 5.83333 6.6665Z" stroke="#222222" stroke-width="1.4" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path><path d="M13.3333 1.6665C16.1083 1.83336 17.5 2.97351 17.5 7.2097V12.7714C17.5 16.4793 16.6667 18.3332 12.5 18.3332H7.5C3.33333 18.3332 2.5 16.4793 2.5 12.7714V7.2097C2.5 2.98278 3.89167 1.83336 6.66667 1.6665" stroke="#222222" stroke-width="1.4" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round"></path></svg>						</span>
                                        <span class="elementor-icon-list-text">1.Application</span>
                            </li>
                            <li class="elementor-icon-list-item elementor-inline-item step-2" data-step="delivery" id="step-indicator-2">
                                <span class="elementor-icon-list-icon">
                                   <svg xmlns="http://www.w3.org/2000/svg" width="21" height="20" viewBox="0 0 21 20" fill="none"><path d="M3.14166 6.19971L10.5 10.458L17.8083 6.22468" stroke="#222222" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M10.5 18.008V10.4497" stroke="#222222" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M8.77498 2.0665L4.32499 4.54153C3.31665 5.09986 2.49167 6.49985 2.49167 7.64985V12.3582C2.49167 13.5082 3.31665 14.9082 4.32499 15.4665L8.77498 17.9415C9.72498 18.4665 11.2833 18.4665 12.2333 17.9415L16.6833 15.4665C17.6917 14.9082 18.5167 13.5082 18.5167 12.3582V7.64985C18.5167 6.49985 17.6917 5.09986 16.6833 4.54153L12.2333 2.0665C11.275 1.53317 9.72498 1.53317 8.77498 2.0665Z" stroke="#222222" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M14.6667 11.0332V7.98321L6.75833 3.4165" stroke="#222222" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>						</span>
                                <span class="elementor-icon-list-text">2.Delivery Address</span>
                            </li>
                            <li class="elementor-icon-list-item elementor-inline-item step-3" data-step="payment" id="step-indicator-3">
                               <span class="elementor-icon-list-icon">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none"><path d="M7.00002 5.4165H13C15.8334 5.4165 16.1167 6.7415 16.3084 8.35817L17.0584 14.6082C17.3 16.6582 16.6667 18.3332 13.75 18.3332H6.25835C3.33335 18.3332 2.70002 16.6582 2.95002 14.6082L3.70003 8.35817C3.88336 6.7415 4.16669 5.4165 7.00002 5.4165Z" stroke="#222222" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M6.66669 6.6665V3.74984C6.66669 2.49984 7.50002 1.6665 8.75002 1.6665H11.25C12.5 1.6665 13.3334 2.49984 13.3334 3.74984V6.6665" stroke="#222222" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path><path d="M17.0084 14.1919H6.66669" stroke="#222222" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"></path></svg>						</span>
                                        <span class="elementor-icon-list-text">3.Submit Order</span>
                                    </li>
                        </ul>
					</div>
				</div>
                <!-- <div class="checkout-progress">
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
                </div> -->

                <!-- Step 1: Application -->
                <div id="checkout-step-1" class="checkout-step">
                    <?php do_action('woocommerce_checkout_before_customer_details'); ?>
                    <div class="d-flex flex-column group-wrap">
                        <h2>Applicant Info</h2>
                        <div class="applicant-information d-grid items-group">
                            <?php custom_checkout_fields($checkout); ?>
                        </div>
                        <div class="passport-type-selector-wrapper items-group clearfix">
                            <?php passport_types($checkout); ?>
                        </div>
                        <div class="delivery-options-wrap items-group clearfix">
                            <?php acf_delivery_options($checkout); ?>
                        </div>
                        <div class="travel-information items-group d-grid">
                            <?php acf_travel_information_fields($checkout); ?>
                        </div>
                        <div class="optional-addons items-group clearfix">
                            <?php acf_optional_delivery_addons($checkout); ?>
                        </div>
                    </div>
                    <div class="step-buttons">
                        <button class="checkout-next-step step-button button alt">Continue to Delivery Address</button>
                    </div>
                </div>

                <!-- Step 2: Delivery Address -->
                <div id="checkout-step-2" class="checkout-step" style="display: none;">
                    <div class="group-wrap">
                        <h2>Delivery Address</h2>
                        <div class="items-group d-flex">
                            <?php do_action('woocommerce_checkout_shipping'); ?>
                        </div>
                        <div class="delivery-address items-group">
                            <?php 
                                $fields = $checkout->get_checkout_fields('shipping');
                                foreach ($fields as $key => $field) {
                                    woocommerce_form_field($key, $field, $checkout->get_value($key));
                                }
                            ?>
                        </div>
                        <div class="shipping-speed items-group">
                            <?php acf_shipping_speed_options($checkout); ?>
                        </div>
                    </div>
                    <div class="step-buttons">
                        <button class="checkout-prev-step step-button button">Back</button>
                        <button class="checkout-next-step step-button button alt">Continue to Payment</button>
                    </div>
                </div>

                <!-- Step 3: Submit Order -->
                <div id="checkout-step-3" class="checkout-step" style="display: none;">
                    <h2>Payment</h2>
                    <?php do_action('woocommerce_checkout_before_order_review'); ?>
                    <div id="order_review" class="woocommerce-checkout-review-order">
                        <?php do_action('woocommerce_checkout_order_review'); ?>
                    </div>
                    <div class="step-buttons">
                        <button class="checkout-prev-step step-button button">Back</button>
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
                <div class="order-summary-content d-flex flex-column">
                    <div class="order-summary-item recipient-info">
                        <span><?php esc_html_e('Recipient:', 'woocommerce'); ?></span>
                        <span class="applicant-name">Not specified</span>
                    </div>
                    
                    <?php
                    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                        $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                        echo '<div class="order-summary-item product-info d-flex">';
                        echo '<span class="item-name flex-grow">' . $_product->get_name() . '</span>';
                        echo '<span class="item-price">' . WC()->cart->get_product_price($_product) . '</span>';
                        echo '</div>';
                    }
                    ?>
                    
                    <div class="order-summary-item delivery-option d-flex"></div>
                    
                    <div class="order-summary-item subtotal d-flex">
                        <span class="flex-grow"><?php esc_html_e('Subtotal', 'woocommerce'); ?></span>
                        <span class="subtotal-amount"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
                    </div>
                    
                    <div class="order-summary-item total d-flex">
                        <span class="flex-grow"><?php esc_html_e('Total', 'woocommerce'); ?></span>
                        <span class="total-amount"><?php echo WC()->cart->get_total(); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>



