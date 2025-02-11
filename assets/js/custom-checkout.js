jQuery(document).ready(function($) {
    var currentStep = 1;
    var totalSteps = 3;

    // Initial setup
    setupCheckoutSteps();
    updateStepVisibility();
    updateOrderSummary();

    // Next step button click
    $('.checkout-next-step').on('click', function(e) {
        e.preventDefault();
        if (validateCurrentStep()) {
            currentStep++;
            updateStepVisibility();
        }
    });

    // Previous step button click
    $('.checkout-prev-step').on('click', function(e) {
        e.preventDefault();
        currentStep--;
        updateStepVisibility();
    });

    // Showing Place holder for cart update
    function showPreloader() {
        $('.order-summary').addClass('loading');
        $('.preloader-overlay').addClass('active');
    }
    // hiding Place holder
    function hidePreloader() {
        $('.order-summary').removeClass('loading');
        $('.preloader-overlay').removeClass('active');
    }

    function setupCheckoutSteps() {
        // Add step navigation
        $('.checkout-step').hide();
        updateProgressBar();
    }

    function updateStepVisibility() {
        $('.checkout-step').hide();
        $('#checkout-step-' + currentStep).show();
        updateProgressBar();
        updateButtons();
        updateOrderSummary();
    }

    function updateButtons() {
        $('.checkout-prev-step').toggle(currentStep > 1);
        $('.checkout-next-step').toggle(currentStep < totalSteps);
        $('#place_order').toggle(currentStep === totalSteps);
    }

    function updateProgressBar() {
        var progress = ((currentStep - 1) / totalSteps) * 100;
        $('.checkout-progress-bar').css('width', progress + '%');
        $('.step-indicator').removeClass('active completed');
        
        for (var i = 1; i <= totalSteps; i++) {
            if (i < currentStep) {
                $('#step-indicator-' + i).addClass('completed');
            } else if (i === currentStep) {
                $('#step-indicator-' + i).addClass('active');
            }
        }
    }

    function validateCurrentStep() {
        var valid = true;
        if (currentStep === 1) {
            var requiredFields = $('#checkout-step-1').find('[required]');
            
            requiredFields.each(function() {
                if (!$(this).val()) {
                    valid = false;
                    $(this).addClass('validation-error');
                    showError($(this).attr('name'));
                } else {
                    $(this).removeClass('validation-error');
                }
            });

            // Check delivery speed selection
            if (!$('input[name="acf_delivery_speed"]:checked').length) {
                valid = false;
                showError('delivery_speed');
            }
        } else if (currentStep === 2) {
            // Validate shipping address fields
            var requiredFields = $('#checkout-step-2').find('[required]');
            
            requiredFields.each(function() {
                if (!$(this).val()) {
                    valid = false;
                    $(this).addClass('validation-error');
                    showError($(this).attr('name'));
                } else {
                    $(this).removeClass('validation-error');
                    removeError($(this).attr('name'));
                }
            });

            // Auto-fill billing fields with shipping data
            if (valid) {
                $('input[name^="shipping_"]').each(function() {
                    var billingField = $(this).attr('name').replace('shipping_', 'billing_');
                    $('input[name="' + billingField + '"]').val($(this).val());
                });
                
                // Also copy state select if exists
                if ($('#shipping_state').length) {
                    $('#billing_state').val($('#shipping_state').val());
                }
            }
        }
        return valid;
    }

    function removeError(fieldName) {
        $('.error-message-' + fieldName).remove();
    }

    function showError(fieldName) {
        var errorMessage = 'Please fill in this field.';
        switch(fieldName) {
            case 'applicant_dob':
                errorMessage = 'Please enter a valid date of birth.';
                break;
            case 'applicant_email':
                errorMessage = 'Please enter a valid email address.';
                break;
            case 'delivery_speed':
                errorMessage = 'Please select a delivery speed option.';
                break;
            case 'shipping_first_name':
                errorMessage = 'Please enter your first name.';
                break;
            case 'shipping_last_name':
                errorMessage = 'Please enter your last name.';
                break;
            case 'shipping_address_1':
                errorMessage = 'Please enter your street address.';
                break;
            case 'shipping_city':
                errorMessage = 'Please enter your city.';
                break;
            case 'shipping_state':
                errorMessage = 'Please select your state.';
                break;
            case 'shipping_postcode':
                errorMessage = 'Please enter your ZIP code.';
                break;
            case 'shipping_phone':
                errorMessage = 'Please enter your phone number.';
                break;
        }
        
        if (!$('.error-message-' + fieldName).length) {
            $('[name="' + fieldName + '"]').after('<span class="error-message error-message-' + fieldName + '">' + errorMessage + '</span>');
        }
    }

    function updateOrderSummary() {
        showPreloader(); // Show during updates

        // Update applicant name
        var firstName = $('#applicant_first_name').val() || '';
        var lastName = $('#applicant_last_name').val() || '';
        var fullName = (firstName + ' ' + lastName).trim();
        $('.applicant-name').text(fullName || ''); // Changed to empty string

        // Update delivery option
        var selectedDelivery = $('input[name="acf_delivery_speed"]:checked');
        if (selectedDelivery.length) {
            var deliveryLabel = selectedDelivery.data('title');
            var deliveryDesc = selectedDelivery.data('desc');
            var deliveryPrice = selectedDelivery.data('price');
            $('.order-summary-item.delivery-option').html(`
                <div class="d-flex">
                    <div class="info">
                        <dt>${deliveryLabel}</dt>
                        <span>${deliveryDesc}</span>
                    </div>
                    <span>$${deliveryPrice}</span>
                </span>
                `
            );
        }

        // Update optional addons
        var addonsTotal = 0;
        var addonsHtml = '';
        $('.addon-checkbox:checked').each(function() {
            var $checkbox = $(this);
            var title = $checkbox.data('addon-title');
            var price = parseFloat($checkbox.data('addon-price'));
            addonsTotal += price;
            addonsHtml += '<div class="order-summary-item addon-item">' +
                '<span>' + title + '</span>' +
                '<span>' + $('.woocommerce-Price-currencySymbol').first().text() + price.toFixed(2) + '</span>' +
                '</div>';
        });

        // Remove existing addon items and insert new ones
        $('.order-summary-item.addon-item').remove();
        if (addonsHtml) {
            $('.order-summary-item.subtotal').before(addonsHtml);
        }
        
        updateCartTotals(addonsTotal);

         // Wrap your existing AJAX call
        $.ajax({
            url: checkoutData.ajaxurl,
            type: 'POST',
            data: {
                action: 'update_cart_totals',
                addons_total: addonsTotal,
                delivery_price: deliveryPrice,
                nonce: checkoutData.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.subtotal-amount').html(response.data.subtotal);
                    $('.total-amount').html(response.data.total);
                }
            },
            complete: hidePreloader // Hide after totals update
        });
    }

    function formatPrice(price) {
        return woocommerce_params.currency_format_symbol + price.toFixed(2);
    }

    function updateCartTotals(addonsTotal) {
        var deliveryPrice = 0;
        var selectedDelivery = $('input[name="acf_delivery_speed"]:checked');
        if (selectedDelivery.length) {
            deliveryPrice = parseFloat(selectedDelivery.val().split(' - ')[1]) || 0;
        }

        $.ajax({
            url: checkoutData.ajaxurl,
            type: 'POST',
            data: {
                action: 'update_cart_totals',
                addons_total: addonsTotal,
                delivery_price: deliveryPrice,
                nonce: checkoutData.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.subtotal-amount').html(response.data.subtotal);
                    $('.total-amount').html(response.data.total);
                }
            }
        });
    }

    const debouncedUpdate = _.debounce(updateOrderSummary, 300);
    $('#applicant_first_name, #applicant_last_name').on('change', function() {
        showPreloader();
        debouncedUpdate();
    });

    // Add to existing event listeners
    // $('#applicant_first_name, #applicant_last_name').on('change', updateOrderSummary);


    $('input[name="acf_delivery_speed"]').on('change', updateOrderSummary);

    // Handle delivery speed selection
    $('input[name="acf_delivery_speed"]').on('change', function() {
        showPreloader(); // Activate preloader

        var selectedOption = $(this).val().split(' - ');
        var price = parseFloat(selectedOption[1]);
        
        $.ajax({
            url: checkoutData.ajaxurl,
            type: 'POST',
            data: {
                action: 'update_delivery_price',
                delivery_option: selectedOption[0],
                price: price,
                nonce: checkoutData.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('.subtotal-amount').html(response.data.subtotal);
                    $('.total-amount').html(response.data.new_total);
                    updateOrderSummary();
                }
            },
            complete: hidePreloader, // Hide on complete
            error: function() {
                hidePreloader();
                console.error('Delivery update failed');
            }
        });
    });

    // Example: Handle optional add-ons
    $('.addon-button').on('click', function() {
        var $button = $(this);
        var addonId = $button.data('addon-id');
        var addonTitle = $button.data('addon-title');
        var addonPrice = parseFloat($button.data('addon-price'));
        
        if ($button.hasClass('added')) {
            $button.removeClass('added').text('Add');
            $('#addon-' + addonId).remove();
        } else {
            $button.addClass('added').text('Remove');
            var input = $('<input>', {
                type: 'hidden',
                name: 'optional_addons[]',
                id: 'addon-' + addonId,
                value: addonTitle + ' - ' + addonPrice
            });
            $('.optional-addons').append(input);
        }
        
        updateOrderSummary();
    });

    // Handle optional add-ons
    $('.addon-checkbox').on('change', function() {
        showPreloader(); // Activate preloader

        var $checkbox = $(this);
        var addonId = $checkbox.data('addon-id');
        var addonTitle = $checkbox.data('addon-title');
        var addonPrice = parseFloat($checkbox.data('addon-price'));
        
        if ($checkbox.is(':checked')) {
            var input = $('<input>', {
                type: 'hidden',
                name: 'optional_addons[]',
                id: 'addon-input-' + addonId,
                value: addonTitle + ' - ' + addonPrice
            });
            $('.optional-addons').append(input);
        } else {
            $('#addon-input-' + addonId).remove();
        }
        
        // Debounce the update
        clearTimeout(window.addonDebounce);
        window.addonDebounce = setTimeout(function() {
            $.ajax({
                url: checkoutData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'update_addons',
                    addon_id: addonId,
                    is_checked: $checkbox.is(':checked'),
                    nonce: checkoutData.nonce
                },
                success: function() {
                    updateOrderSummary();
                },
                complete: hidePreloader, // Hide on complete
                error: function() {
                    hidePreloader();
                    console.error('Addon update failed');
                }
            });
        }, 300);
    });
});
