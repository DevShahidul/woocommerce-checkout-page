<?php
/**
 * WooCommerce compatibility file.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Overide cueckout fields
 *
 * @return array List of fields
 */



add_filter( 'woocommerce_checkout_fields', 'custom_override_checkout_fields' );

function custom_override_checkout_fields( $fields ) {
    // Remove WooCommerce default fields
    unset($fields['billing']['billing_first_name']);
    unset($fields['billing']['billing_last_name']);
    unset($fields['billing']['billing_email']);
    unset($fields['billing']['billing_phone']);
    unset($fields['billing']['billing_address_1']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_city']);
    unset($fields['billing']['billing_state']);
    unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_country']);

    // Keep only billing country and email, remove all other billing fields
    $fields['billing'] = array(
        'billing_country' => array(
            'type' => 'hidden',
            'default' => 'US',
            'required' => true
        ),
        'billing_email' => array(
            'type' => 'hidden',
            'required' => true
        )
    );

    // Add required shipping fields
    $fields['shipping']['shipping_country'] = array(
        'type' => 'hidden',
        'default' => 'US',
        'required' => true
    );

    return $fields;
}

// Force shipping fields to be required
add_filter('woocommerce_shipping_fields', 'make_shipping_fields_required');
function make_shipping_fields_required($fields) {
    $required_fields = array(
        'shipping_first_name',
        'shipping_last_name',
        'shipping_address_1',
        'shipping_city',
        'shipping_state',
        'shipping_postcode',
        'shipping_phone'
    );

    foreach ($required_fields as $field) {
        if (isset($fields[$field])) {
            $fields[$field]['required'] = true;
        }
    }

    return $fields;
}

// Add this function to set default country
add_filter('default_checkout_billing_country', 'set_default_billing_country');
function set_default_billing_country() {
    return 'US';
}

// Also add this filter for shipping country
add_filter('default_checkout_shipping_country', 'set_default_shipping_country');
function set_default_shipping_country() {
    return 'US';
}

// Add this to ensure billing email is set
add_action('woocommerce_checkout_create_order', 'ensure_billing_email', 10, 2);
function ensure_billing_email($order, $data) {
    if (!empty($data['applicant_email'])) {
        $order->set_billing_email($data['applicant_email']);
    }
}


add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );



// Applicant Info Fields
function custom_checkout_fields( $checkout = null ) {
    if (!$checkout) {
        $checkout = WC()->checkout;
    }
    
    echo '<h3>Applicant Info</h3>';

    // First Name
    woocommerce_form_field( 'applicant_first_name', array(
        'type' => 'text',
        'class' => array('form-row-first'),
        'label' => __('First Name'),
        'required' => true
    ), $checkout->get_value( 'applicant_first_name' ));

    // Last Name
    woocommerce_form_field( 'applicant_last_name', array(
        'type' => 'text',
        'class' => array('form-row-last'),
        'label' => __('Last Name'),
        'required' => true
    ), $checkout->get_value( 'applicant_last_name' ));

    // Date of Birth with standardized date attributes
    woocommerce_form_field( 'applicant_dob', array(
        'type' => 'date',
        'class' => array('form-row-wide'),
        'label' => __('Date of Birth'),
        'required' => true,
        'custom_attributes' => array(
            'pattern' => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
            'min' => '1920-01-01',
            'max' => date('Y-m-d')
        )
    ), $checkout->get_value( 'applicant_dob' ));

    // Email Address
    woocommerce_form_field( 'applicant_email', array(
        'type' => 'email',
        'class' => array('form-row-wide'),
        'label' => __('Email Address'),
        'required' => true
    ), $checkout->get_value( 'applicant_email' ));
}

// Passport type
function passport_types($checkout = null) {
    if (!$checkout) {
        $checkout = WC()->checkout;
    }
    // $products = get_all_woo_products();
?>

    <div class="passport-type-section">
        <h3><?php _e('Select your passport type?', 'woocommerce'); ?></h3>
        <?php echo do_shortcode('[product_selector]'); ?>
    </div>
    <?php

}

// Delivery Options
function acf_delivery_options($checkout = null) {
    if (!$checkout) {
        $checkout = WC()->checkout;
    }

    $delivery_options = get_field('delivery_options', 'option');

    ?>
    <div class="passport-delivery-section">
        <h3><?php _e('How quickly do you need your passport?', 'woocommerce'); ?></h3>
        <div class="delivery-options">
            <?php

            foreach ($delivery_options as $key => $option) {
                $label = esc_html($option['label']);
                $desc = esc_html($option['description']);
                $original_price = floatval($option['original_price']);
                $discounted_price = floatval($option['discounted_price']);

                $radio_value = $label . ' - ' . number_format($discounted_price, 2);
                ?>
                <div class="delivery-option">
                    <label for="delivery_<?php echo esc_attr($key); ?>">
						<input type="radio" 
							   name="acf_delivery_speed" 
							   id="delivery_<?php echo esc_attr($key); ?>" 
							   value="<?php echo esc_attr($radio_value) ?>"
							   data-price="<?php echo $discounted_price; ?>"
                               data-title="<?php echo $label; ?>"
                               data-desc="<?php echo $desc; ?>"
                               >
						<span class="title-col">
							<span class="title">
                        		<?php echo $label; ?>
							</span>
							<span class="duration">
								<?php echo $desc; ?>
							</span>
						</span>
                        <span class="price">
                            <del>$<?php echo $original_price; ?></del>
                            <ins>$<?php echo $discounted_price; ?></ins>
                        </span>
                    </label>
                </div>
                <?php
            }
            ?>
        </div>
        <p class="government-fee-notice">Government fee of $211.36 is not included</p>
    </div>
    <?php
}


add_action( 'woocommerce_after_order_notes', 'acf_travel_information_fields' );

function acf_travel_information_fields( $checkout = null ) {
    if (!$checkout) {
        $checkout = WC()->checkout;
    }

    // Get the field object instead of just the value
    $field = get_field_object('where_traveling', 'option');
    
    echo '<h3>Travel Information</h3>';
    
    // Debug information
    // echo '<!-- Debug Field: ';
    // var_dump($field);
    // echo ' -->';

    // Initialize the options array
    $locations_array = array('' => 'Select Destination');
    
    // Check if we have choices in the field object
    if (!empty($field['choices'])) {
        $locations_array = array('' => 'Select Destination') + $field['choices'];
    }

    // Use WooCommerce form field function
    woocommerce_form_field('where_traveling', array(
        'type'          => 'select',
        'class'         => array('form-row-first'),
        'label'         => __('Where Traveling'),
        'required'      => true,
        'options'       => $locations_array
    ), $checkout->get_value('where_traveling'));

    // Travel Date with standardized date attributes
    woocommerce_form_field('travel_date', array(
        'type'          => 'date',
        'class'         => array('form-row-last'),
        'label'         => __('Travel Date'),
        'required'      => true,
        'custom_attributes' => array(
            'pattern' => '[0-9]{4}-[0-9]{2}-[0-9]{2}',
            'min' => date('Y-m-d'),
            'max' => date('Y-m-d', strtotime('+2 years'))
        )
    ), $checkout->get_value('travel_date'));
}



function acf_optional_delivery_addons($checkout = null) {
    if (!$checkout) {
        $checkout = WC()->checkout;
    }

    ?>
	<?php if(have_rows('optional_delivery_addons', 'option')): ?>
    <div class="passport-addons-section">
        <h3 class="heading"><?php _e('Optional Add-ons', 'woocommerce'); ?></h3>
		<div class="card-row">
			<?php while(have_rows('optional_delivery_addons', 'option')) : the_row();
                $title = acf_esc_html( get_sub_field('title') );
                $description = acf_esc_html( get_sub_field('description') );
                $price = number_format( get_sub_field('price'), 2);
                $addon_id = sanitize_title($title);
            
            ?>
			<div class="addon-card">
				<h4 class="title"><?php echo $title; ?></h4>
                <input type="checkbox" data-addon-title="<?php echo $title; ?>" data-description="<?php echo $description; ?>" 
                id="<?php echo $title; ?>"
                data-addon-price="<?php echo $price; ?>" class="addon-checkbox" />
				<p class="description"><?php echo $description; ?></p>
				<hr class="separator">
				<div class="action-row">
					<span class="price" data-addon-price="" >$<?php echo $price; ?></span>
					<label for="<?php echo $title; ?>" data-addon-id="<?php esc_attr($addon_id); ?>" data-addon-price="<?php esc_attr($price); ?>" data-description="<?php echo $description; ?>" type="button" class="button">Add</label>
				</div>
			</div>
			<?php endwhile; ?>
		</div>
    </div>
    <?php endif; ?>

    <?php
}

// Remove the shipping toggle checkbox
add_filter('woocommerce_cart_needs_shipping_address', '__return_true');

// Modify the shipping fields section title
add_filter('woocommerce_shipping_fields_heading', '__return_false');

// Remove duplicate action for travel information
remove_action('woocommerce_after_order_notes', 'acf_travel_information_fields');

// Remove the shipping toggle functionality
function remove_shipping_toggle() {
    // Remove the "Ship to a different address?" checkbox
    remove_action('woocommerce_checkout_shipping', 'woocommerce_checkout_shipping', 10);
    
    // Remove the shipping toggle section completely
    remove_action('woocommerce_checkout_shipping', 'woocommerce_checkout_form_shipping');
    remove_action('woocommerce_before_checkout_shipping_form', 'woocommerce_checkout_shipping_form');
}
add_action('init', 'remove_shipping_toggle');

add_filter( 'woocommerce_checkout_fields', 'custom_delivery_fields' );

function custom_delivery_fields( $fields ) {
    // Remove shipping toggle field
    unset($fields['shipping']['ship_to_different_address']);
    
    // Enable Shipping Address Fields
    $fields['shipping']['shipping_first_name'] = array(
        'label'     => __('First Name'),
        'required'  => true,
        'class'     => array('form-row-first'),
        'priority'  => 30,
    );

    $fields['shipping']['shipping_last_name'] = array(
        'label'     => __('Last Name'),
        'required'  => true,
        'class'     => array('form-row-last'),
        'priority'  => 40,
    );

    $fields['shipping']['shipping_address_1'] = array(
        'label'     => __('Address'),
        'required'  => true,
        'class'     => array('form-row-wide'),
        'priority'  => 50,
    );

    $fields['shipping']['shipping_address_2'] = array(
        'label'     => __('Apartment, Suite, or Room (Optional)'),
        'required'  => false,
        'class'     => array('form-row-wide'),
        'priority'  => 60,
    );

    $fields['shipping']['shipping_city'] = array(
        'label'     => __('City'),
        'required'  => true,
        'class'     => array('form-row-first'),
        'priority'  => 70,
    );

    $fields['shipping']['shipping_state'] = array(
        'label'     => __('State'),
        'required'  => true,
        'class'     => array('form-row-last'),
        'type'      => 'select',
        'priority'  => 80,
        'options'   => WC()->countries->get_states( WC()->countries->get_base_country() ), // Auto-load states
    );

    $fields['shipping']['shipping_postcode'] = array(
        'label'     => __('ZIP Code'),
        'required'  => true,
        'class'     => array('form-row-first'),
        'priority'  => 90,
    );

    $fields['shipping']['shipping_phone'] = array(
        'label'     => __('Mobile Phone'),
        'required'  => true,
        'class'     => array('form-row-last'),
        'priority'  => 100,
    );

    // Update the country field to be more user-friendly
    $fields['shipping']['shipping_country'] = array(
        'label'     => __('Country'),
        'required'  => true,
        'class'     => array('form-row-wide', 'us-only-field'),
        'priority'  => 20,
        'type'      => 'select',
        'options'   => array('US' => 'United States (US-only service)'),
        'default'   => 'US',
        'custom_attributes' => array(
            'disabled' => 'disabled'
        )
    );

    // Add notice about US-only service
    add_action('woocommerce_before_checkout_shipping_form', function() {
        echo '<div class="us-only-notice">';
        echo '<p><i class="fas fa-info-circle"></i> Our service is currently available for U.S. addresses only.</p>';
        echo '</div>';
    });

    // Force show shipping section
    add_filter('woocommerce_ship_to_different_address_checked', '__return_true');
    
    return $fields;
}

// Add hidden field for country value
add_action('woocommerce_before_checkout_shipping_form', 'add_hidden_country_field');
function add_hidden_country_field() {
    echo '<input type="hidden" name="shipping_country" value="US">';
}

// Force shipping fields to always show
add_filter('woocommerce_ship_to_different_address_checked', '__return_true');
add_filter('woocommerce_cart_needs_shipping', '__return_true');
add_filter('woocommerce_cart_needs_shipping_address', '__return_true');

// Remove the shipping toggle functionality
add_action('init', function() {
    remove_action('woocommerce_checkout_shipping', 'woocommerce_checkout_shipping', 10);
    remove_action('woocommerce_checkout_shipping', 'woocommerce_checkout_form_shipping');
    remove_action('woocommerce_before_checkout_shipping_form', 'woocommerce_checkout_shipping_form');
    
    // Remove the shipping toggle checkbox completely
    remove_action('woocommerce_before_checkout_shipping_form', array('WC_Checkout', 'checkout_form_shipping'));
    remove_action('woocommerce_checkout_shipping', array('WC_Checkout', 'checkout_form_shipping'));
});

// Remove the optional addons duplicate action
remove_action('woocommerce_after_order_notes', 'acf_optional_delivery_addons');


add_action( 'woocommerce_after_order_notes', 'acf_shipping_speed_options' );

function acf_shipping_speed_options($checkout) {
    $shipping_options = get_field('shipping_speed_options', 'option');

    if ($shipping_options) {
        echo '<h3>Shipping Speed</h3>';
        echo '<div class="shipping-speed-options">';

        foreach ($shipping_options as $option) {
            $label = esc_html($option['label']);
            $price = floatval($option['price']);
            $description = esc_html($option['description']);
            
            // Clean value for option
            $option_value = $label . ' - ' . number_format($price, 2);

            echo '<div class="shipping-speed-option">';
            echo '<input type="radio" name="shipping_speed" id="' . sanitize_title($label) . '" ';
            echo 'value="' . esc_attr($option_value) . '" required>';
            echo '<label for="' . sanitize_title($label) . '">';
            echo '<strong>' . $label . '</strong>';
            if ($description) {
                echo ' <span class="description">(' . $description . ')</span>';
            }
            echo '<br><span class="price">' . wc_price($price) . '</span>';
            echo '</label>';
            echo '</div>';
        }

        echo '</div>';
    }
}

// Remove coupon form from checkout
add_filter('woocommerce_coupons_enabled', 'disable_coupon_field_on_checkout');
function disable_coupon_field_on_checkout($enabled) {
    if (is_checkout()) {
        return false;
    }
    return $enabled;
}

// Save custom checkout fields
add_action('woocommerce_checkout_update_order_meta', 'save_custom_checkout_fields');
function save_custom_checkout_fields($order_id) {
    $fields_to_save = array(
        'applicant_first_name',
        'applicant_last_name',
        'applicant_dob',
        'applicant_email',
        'acf_delivery_speed',
        'where_traveling',
        'travel_date'
    );

    foreach ($fields_to_save as $field) {
        if (!empty($_POST[$field])) {
            update_post_meta($order_id, '_' . $field, sanitize_text_field($_POST[$field]));
        }
    }

    // Save optional addons if selected
    if (!empty($_POST['optional_addons'])) {
        update_post_meta($order_id, '_optional_addons', array_map('sanitize_text_field', $_POST['optional_addons']));
    }
}

// Add fields to order details
add_action('woocommerce_admin_order_data_after_billing_address', 'display_custom_fields_in_admin');
function display_custom_fields_in_admin($order) {
    echo '<h3>Applicant Information</h3>';
    echo '<p><strong>First Name:</strong> ' . get_post_meta($order->get_id(), '_applicant_first_name', true) . '</p>';
    echo '<p><strong>Last Name:</strong> ' . get_post_meta($order->get_id(), '_applicant_last_name', true) . '</p>';
    echo '<p><strong>Date of Birth:</strong> ' . get_post_meta($order->get_id(), '_applicant_dob', true) . '</p>';
    echo '<p><strong>Email:</strong> ' . get_post_meta($order->get_id(), '_applicant_email', true) . '</p>';
    
    echo '<h3>Travel Information</h3>';
    echo '<p><strong>Destination:</strong> ' . get_post_meta($order->get_id(), '_where_traveling', true) . '</p>';
    echo '<p><strong>Travel Date:</strong> ' . get_post_meta($order->get_id(), '_travel_date', true) . '</p>';
    
    echo '<h3>Delivery Speed</h3>';
    echo '<p>' . get_post_meta($order->get_id(), '_acf_delivery_speed', true) . '</p>';
    
    $addons = get_post_meta($order->get_id(), '_optional_addons', true);
    if (!empty($addons)) {
        echo '<h3>Optional Add-ons</h3>';
        foreach ($addons as $addon) {
            echo '<p>' . esc_html($addon) . '</p>';
        }
    }
}

// Validate required fields
add_action('woocommerce_checkout_process', 'validate_custom_checkout_fields');
function validate_custom_checkout_fields() {
    $required_fields = array(
        'applicant_first_name' => 'First Name',
        'applicant_last_name' => 'Last Name',
        'applicant_dob' => 'Date of Birth',
        'applicant_email' => 'Email Address',
        'acf_delivery_speed' => 'Delivery Speed',
        'where_traveling' => 'Travel Destination',
        'travel_date' => 'Travel Date'
    );

    foreach ($required_fields as $field => $label) {
        if (empty($_POST[$field])) {
            wc_add_notice(sprintf(__('%s is a required field.', 'woocommerce'), $label), 'error');
        }
    }
}

// Update cart totals when delivery speed is selected
add_action('wp_ajax_update_delivery_price', 'update_cart_delivery_price');
add_action('wp_ajax_nopriv_update_delivery_price', 'update_cart_delivery_price');
function update_cart_delivery_price() {
    if (!isset($_POST['delivery_option']) || !wp_verify_nonce($_POST['nonce'], 'custom-checkout-nonce')) {
        wp_send_json_error('Invalid request');
    }

    $delivery_price = floatval($_POST['price']);
    WC()->session->set('selected_delivery_price', $delivery_price);
    
    // Update cart total
    WC()->cart->calculate_totals();
    
    wp_send_json_success(array(
        'new_total' => WC()->cart->get_total(),
        'subtotal' => WC()->cart->get_subtotal()
    ));
}

// Add delivery fee to cart
add_action('woocommerce_cart_calculate_fees', 'add_delivery_fee');
function add_delivery_fee() {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    $delivery_price = WC()->session->get('selected_delivery_price');
    if ($delivery_price > 0) {
        WC()->cart->add_fee('Delivery Speed', $delivery_price);
    }
}

// Add AJAX handler for cart totals update
add_action('wp_ajax_update_cart_totals', 'update_cart_totals_with_addons');
add_action('wp_ajax_nopriv_update_cart_totals', 'update_cart_totals_with_addons');

function update_cart_totals_with_addons() {
    if (!wp_verify_nonce($_POST['nonce'], 'custom-checkout-nonce')) {
        wp_send_json_error('Invalid nonce');
    }

    $addons_total = isset($_POST['addons_total']) ? floatval($_POST['addons_total']) : 0;
    $delivery_price = isset($_POST['delivery_price']) ? floatval($_POST['delivery_price']) : 0;

    // Store in session for cart calculations
    WC()->session->set('selected_addons_total', $addons_total);
    WC()->session->set('selected_delivery_price', $delivery_price);

    // Recalculate totals
    WC()->cart->calculate_totals();

    wp_send_json_success(array(
        'subtotal' => WC()->cart->get_cart_subtotal(),
        'total' => WC()->cart->get_total()
    ));
}

// Add combined fees to cart
add_action('woocommerce_cart_calculate_fees', 'add_combined_fees');
function add_combined_fees() {
    if (is_admin() && !defined('DOING_AJAX')) {
        return;
    }

    $addons_total = WC()->session->get('selected_addons_total');
    $delivery_price = WC()->session->get('selected_delivery_price');

    if ($addons_total > 0) {
        WC()->cart->add_fee('Optional Add-ons', $addons_total);
    }
    if ($delivery_price > 0) {
        WC()->cart->add_fee('Delivery Speed', $delivery_price);
    }
}

// Add this new function to copy shipping to billing
add_action('woocommerce_checkout_update_order_meta', 'copy_shipping_to_billing');
function copy_shipping_to_billing($order_id) {
    $shipping_fields = array(
        'shipping_first_name' => 'billing_first_name',
        'shipping_last_name'  => 'billing_last_name',
        'shipping_address_1'  => 'billing_address_1',
        'shipping_address_2'  => 'billing_address_2',
        'shipping_city'       => 'billing_city',
        'shipping_state'      => 'billing_state',
        'shipping_postcode'   => 'billing_postcode',
        'shipping_phone'      => 'billing_phone'
    );

    foreach ($shipping_fields as $shipping => $billing) {
        $value = get_post_meta($order_id, '_' . $shipping, true);
        update_post_meta($order_id, '_' . $billing, $value);
    }

    // Also copy email from applicant info to billing
    $applicant_email = get_post_meta($order_id, '_applicant_email', true);
    update_post_meta($order_id, '_billing_email', $applicant_email);
}

// Add this to automatically fill billing fields on checkout
add_filter('woocommerce_checkout_posted_data', 'auto_fill_billing_fields');
function auto_fill_billing_fields($data) {
    if (isset($data['shipping_first_name'])) {
        $data['billing_first_name'] = $data['shipping_first_name'];
        $data['billing_last_name'] = $data['shipping_last_name'];
        $data['billing_address_1'] = $data['shipping_address_1'];
        $data['billing_address_2'] = $data['shipping_address_2'];
        $data['billing_city'] = $data['shipping_city'];
        $data['billing_state'] = $data['shipping_state'];
        $data['billing_postcode'] = $data['shipping_postcode'];
        $data['billing_phone'] = $data['shipping_phone'];
        $data['billing_email'] = $data['applicant_email'];
        $data['billing_country'] = 'US'; // Set default country
    }
    return $data;
}

// Add this new filter to prevent WooCommerce's default address validation
add_filter('woocommerce_checkout_fields', 'modify_checkout_fields_validation', 9999);
function modify_checkout_fields_validation($fields) {
    // Set billing address as non-required since we're copying from shipping
    if (isset($fields['billing'])) {
        foreach ($fields['billing'] as $key => $field) {
            $fields['billing'][$key]['required'] = false;
        }
    }

    // Add hidden billing fields that will be auto-filled
    $fields['billing']['billing_first_name'] = array('type' => 'hidden');
    $fields['billing']['billing_last_name'] = array('type' => 'hidden');
    $fields['billing']['billing_address_1'] = array('type' => 'hidden');
    $fields['billing']['billing_address_2'] = array('type' => 'hidden');
    $fields['billing']['billing_city'] = array('type' => 'hidden');
    $fields['billing']['billing_state'] = array('type' => 'hidden');
    $fields['billing']['billing_postcode'] = array('type' => 'hidden');
    $fields['billing']['billing_country'] = array('type' => 'hidden');
    $fields['billing']['billing_phone'] = array('type' => 'hidden');
    $fields['billing']['billing_email'] = array('type' => 'hidden');

    return $fields;
}

// Add filter to automatically set billing address same as shipping
add_action('woocommerce_checkout_before_customer_details', 'add_billing_copy_script');
function add_billing_copy_script() {
    ?>
    <script type="text/javascript">
        jQuery(function($) {
            // Copy shipping to billing when form is submitted
            $('form.checkout').on('checkout_place_order', function() {
                $('input[name^="shipping_"]').each(function() {
                    var billing_field = $(this).attr('name').replace('shipping_', 'billing_');
                    $('input[name="' + billing_field + '"]').val($(this).val());
                });
                $('#billing_email').val($('#applicant_email').val());
                $('#billing_country').val('US');
                return true;
            });
        });
    </script>
    <?php
}

// Add action to ensure billing data is set before processing payment
add_action('woocommerce_checkout_process', 'ensure_billing_address_is_set');
function ensure_billing_address_is_set() {
    $_POST['billing_country'] = 'US';
    if (!empty($_POST['shipping_first_name'])) {
        $_POST['billing_first_name'] = $_POST['shipping_first_name'];
        $_POST['billing_last_name'] = $_POST['shipping_last_name'];
        $_POST['billing_address_1'] = $_POST['shipping_address_1'];
        $_POST['billing_address_2'] = $_POST['shipping_address_2'];
        $_POST['billing_city'] = $_POST['shipping_city'];
        $_POST['billing_state'] = $_POST['shipping_state'];
        $_POST['billing_postcode'] = $_POST['shipping_postcode'];
        $_POST['billing_phone'] = $_POST['shipping_phone'];
        $_POST['billing_email'] = $_POST['applicant_email'];
    }
}

// Add custom sections to order received page
add_action('woocommerce_order_details_after_order_table', 'add_custom_order_details', 10, 1);
function add_custom_order_details($order) {
    // Applicant Information Section
    echo '<section class="woocommerce-customer-details applicant-details">';
    echo '<h2>' . __('Applicant Information', 'woocommerce') . '</h2>';
    echo '<table class="woocommerce-table custom-details-table">';
    echo '<tr><th>' . __('Name', 'woocommerce') . ':</th><td>' . 
         get_post_meta($order->get_id(), '_applicant_first_name', true) . ' ' . 
         get_post_meta($order->get_id(), '_applicant_last_name', true) . '</td></tr>';
    echo '<tr><th>' . __('Date of Birth', 'woocommerce') . ':</th><td>' . 
         get_post_meta($order->get_id(), '_applicant_dob', true) . '</td></tr>';
    echo '<tr><th>' . __('Email', 'woocommerce') . ':</th><td>' . 
         get_post_meta($order->get_id(), '_applicant_email', true) . '</td></tr>';
    echo '</table>';
    echo '</section>';

    // Travel Information Section
    echo '<section class="woocommerce-customer-details travel-details">';
    echo '<h2>' . __('Travel Information', 'woocommerce') . '</h2>';
    echo '<table class="woocommerce-table custom-details-table">';
    echo '<tr><th>' . __('Destination', 'woocommerce') . ':</th><td>' . 
         get_post_meta($order->get_id(), '_where_traveling', true) . '</td></tr>';
    echo '<tr><th>' . __('Travel Date', 'woocommerce') . ':</th><td>' . 
         get_post_meta($order->get_id(), '_travel_date', true) . '</td></tr>';
    echo '</table>';
    echo '</section>';
}

// Add delivery speed and add-ons to order items table
add_action('woocommerce_order_item_meta_end', 'add_delivery_and_addon_details', 10, 3);
function add_delivery_and_addon_details($item_id, $item, $order) {
    // Display Delivery Speed
    $delivery_speed = get_post_meta($order->get_id(), '_acf_delivery_speed', true);
    if ($delivery_speed) {
        $speed_parts = explode(' - ', $delivery_speed);
        echo '<br><strong>' . __('Delivery Speed', 'woocommerce') . ':</strong> ' . $speed_parts[0];
    }

    // Display Optional Add-ons
    $addons = get_post_meta($order->get_id(), '_optional_addons', true);
    if (!empty($addons)) {
        echo '<br><strong>' . __('Optional Add-ons', 'woocommerce') . ':</strong><br>';
        foreach ($addons as $addon) {
            $addon_parts = explode(' - ', $addon);
            echo '- ' . $addon_parts[0] . '<br>';
        }
    }
}

// Add custom CSS for the order received page
add_action('wp_head', 'add_order_received_styles');
function add_order_received_styles() {
    if (is_wc_endpoint_url('order-received')) {
        ?>
        <style>
            .custom-details-table {
                width: 100%;
                margin-bottom: 30px;
            }
            .custom-details-table th {
                text-align: left;
                padding: 10px;
                width: 150px;
                font-weight: 600;
            }
            .custom-details-table td {
                padding: 10px;
            }
            .woocommerce-customer-details {
                margin-bottom: 40px;
            }
        </style>
        <?php
    }
}


