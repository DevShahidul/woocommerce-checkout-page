document.addEventListener('DOMContentLoaded', function() {
    const $ = jQuery;
    const checkout = {
        currentStep: 1,
        totalSteps: 3,
        isProcessing: false,
        elements: {
            steps: $('.checkout-step'),
            progressBar: $('.checkout-progress-bar'),
            orderSummary: $('.order-summary'),
            preloader: $('.preloader-overlay')
        },
        
        init: function() {
            this.setupEventListeners();
            this.initializeCheckout();
        },

        initializeCheckout: function() {
            this.elements.steps.hide();
            this.updateProgressBar();
            this.updateStepVisibility();
            this.updateOrderSummary(false);
        },

        setupEventListeners: function() {
            $('.checkout-next-step').on('click', this.handleNextStep.bind(this));
            $('.checkout-prev-step').on('click', this.handlePrevStep.bind(this));
            $('.addon-button').on('click', this.handleAddonButton.bind(this));
            $('input[name="acf_delivery_speed"]').on('change', this.handleDeliveryChange.bind(this));
            $('.addon-checkbox').on('change', this.handleAddonChange.bind(this));
            $('#applicant_first_name, #applicant_last_name').on('change', _.debounce(() => this.debouncedUpdate(), 300));
        },

        handleNextStep: function(e) {
            e.preventDefault();
            if(this.validateCurrentStep()) {
                this.currentStep++;
                this.updateStepVisibility();
            }
        },

        handlePrevStep: function(e) {
            e.preventDefault();
            this.currentStep--;
            this.updateStepVisibility();
        },

        updateStepVisibility: function() {
            this.elements.steps.hide();
            $(`#checkout-step-${this.currentStep}`).show();
            this.updateProgressBar();
            this.updateNavigationButtons();
        },

        updateProgressBar: function() {
            const progress = ((this.currentStep - 1) / this.totalSteps) * 100;
            this.elements.progressBar.css('width', `${progress}%`);
            $('.step-indicator').removeClass('active completed')
                .slice(0, this.currentStep - 1).addClass('completed')
                .filter(`#step-indicator-${this.currentStep}`).addClass('active');
        },

        updateNavigationButtons: function() {
            $('.checkout-prev-step').toggle(this.currentStep > 1);
            $('.checkout-next-step').toggle(this.currentStep < this.totalSteps);
            $('#place_order').toggle(this.currentStep === this.totalSteps);
        },

        validateCurrentStep: function() {
            let isValid = true;
            const $currentStep = $(`#checkout-step-${this.currentStep}`);
            
            // Clear existing errors
            $('.validation-error').removeClass('validation-error');
            $('.error-message').remove();

            // Step-specific validation
            switch(this.currentStep) {
                case 1:
                    isValid = this.validateStep1($currentStep);
                    break;
                case 2:
                    isValid = this.validateStep2($currentStep);
                    break;
            }

            return isValid;
        },

        validateStep1: function($step) {
            let isValid = true;
            
            // Required fields
            $step.find('[required]').each(function() {
                if(!$(this).val().trim()) {
                    isValid = false;
                    checkout.showFieldError($(this).attr('name'), 'This field is required');
                }
            });

            // Delivery speed selection
            if(!$('input[name="acf_delivery_speed"]:checked').length) {
                isValid = false;
                checkout.showFieldError('delivery_speed', 'Please select a delivery option');
            }

            return isValid;
        },

        validateStep2: function($step) {
            let isValid = true;
            
            // Shipping address validation
            $step.find('[name^="shipping_"]').each(function() {
                if($(this).is('[required]') && !$(this).val().trim()) {
                    isValid = false;
                    checkout.showFieldError($(this).attr('name'), 'This field is required');
                }
            });

            // Copy shipping to billing if valid
            if(isValid) {
                $('[name^="shipping_"]').each(function() {
                    const billingName = $(this).attr('name').replace('shipping_', 'billing_');
                    $(`[name="${billingName}"]`).val($(this).val());
                });
            }

            return isValid;
        },

        showFieldError: function(fieldName, message) {
            $(`[name="${fieldName}"]`).addClass('validation-error')
                .after(`<span class="error-message error-${fieldName}">${message}</span>`);
        },

        // Preloader Management
        showPreloader: function() {
            this.elements.orderSummary.addClass('loading');
            this.elements.preloader.addClass('active');
        },

        hidePreloader: function() {
            this.elements.orderSummary.removeClass('loading');
            this.elements.preloader.removeClass('active');
        },

        // Order Summary Updates
        updateOrderSummary: function(showLoader = true) {
            if(showLoader) this.showPreloader();

            // Update applicant name
            const firstName = $('#applicant_first_name').val() || '';
            const lastName = $('#applicant_last_name').val() || '';
            $('.applicant-name').text(`${firstName} ${lastName}`.trim());

            // Update delivery option
            const deliveryOption = $('input[name="acf_delivery_speed"]:checked');
            if(deliveryOption.length) {
                $('.order-summary-item.delivery-option').html(`
                    <div class="d-flex">
                        <div class="info">
                            <dt>${deliveryOption.data('title')}</dt>
                            <span>${deliveryOption.data('desc')}</span>
                        </div>
                        <span>$${deliveryOption.data('price')}</span>
                    </div>
                `);
            }

            // Update addons
            this.updateAddonsDisplay();

            // Update totals via AJAX
            $.ajax({
                url: checkoutData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'update_cart_totals',
                    addons_total: this.calculateAddonsTotal(),
                    delivery_price: deliveryOption.data('price') || 0,
                    nonce: checkoutData.nonce,
                    _wp_http_referer: window.location.href
                },
                success: response => {
                    if(response.success) {
                        $('.subtotal-amount').html(response.data.subtotal);
                        $('.total-amount').html(response.data.total);
                    }
                },
                complete: () => {
                    if(showLoader) this.hidePreloader();
                }
            });
        },

        // calculateAddonsTotal: function() {
        //     return $('.addon-checkbox:checked').toArray().reduce((total, el) => 
        //         total + parseFloat($(el).data('addon-price')), 0);
        // },

        // updateAddonsDisplay: function() {
        //     let addonsHtml = '';
        //     $('.addon-checkbox:checked').each(function() {
        //         addonsHtml += `
        //             <div class="order-summary-item addon-item">
        //                 <span>${$(this).data('addon-title')}</span>
        //                 <span>${$('.woocommerce-Price-currencySymbol').first().text()}${parseFloat($(this).data('addon-price')).toFixed(2)}</span>
        //             </div>`;
        //     });
        //     $('.addon-item').remove();
        //     if(addonsHtml) $('.subtotal').before(addonsHtml);
        // },

        handleAddonButton: function(event) {
            event.preventDefault();
            const $button = $(event.currentTarget);
            const addonId = $button.data('addon-id');
            const addonPrice = parseFloat($button.data('addon-price'));
            const addonTitle = $button.data('addon-title');
            
            this.showPreloader();

            // Toggle addon state
            const isAdding = !$button.hasClass('added');
            
            $.ajax({
                url: checkoutData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'update_addons',
                    addon_id: addonId,
                    addon_price: addonPrice,
                    addon_title: addonTitle,
                    is_checked: isAdding,
                    nonce: checkoutData.nonce,
                    _wpnonce: checkoutData.nonce
                },
                context: this,
                success: function(response) {
                    if(response.success) {
                        // Update UI state
                        $button.toggleClass('added', isAdding)
                              .text(isAdding ? 'Remove' : 'Add');
                        
                        // Update hidden field
                        if(isAdding) {
                            $('.optional-addons').append(
                                `<input type="hidden" name="optional_addons[]" 
                                        id="addon-${addonId}" 
                                        value="${addonId}">`
                            );
                        } else {
                            $(`#addon-${addonId}`).remove();
                        }
                        
                        // Refresh order summary
                        this.updateOrderSummary(true);
                    }
                },
                complete: function() {
                    this.hidePreloader();
                },
                error: function(xhr) {
                    console.error('Addon update failed:', xhr.responseText);
                    this.showFieldError('addons', 'Failed to update add-ons. Please try again.');
                }
            });
        },
        
        // Update the calculateAddonsTotal function
        calculateAddonsTotal: function() {
            // Calculate from both checkboxes and buttons
            let total = 0;
            
            // Checkbox addons
            total += $('.addon-checkbox:checked').toArray().reduce((sum, el) => 
                sum + parseFloat($(el).data('addon-price')), 0);
            
            // Button addons
            total += $('.addon-button.added').toArray().reduce((sum, el) => 
                sum + parseFloat($(el).data('addon-price')), 0);
        
            return total;
        },
        
        // Update the updateAddonsDisplay function
        updateAddonsDisplay: function() {
            let addonsHtml = '';
            
            // Checkbox addons
            $('.addon-checkbox:checked').each(function() {
                addonsHtml += this.getAddonHtml($(this).data());
            });
            
            // Button addons
            $('.addon-button.added').each(function() {
                addonsHtml += this.getAddonHtml({
                    'addon-title': $(this).data('addon-title'),
                    'addon-price': $(this).data('addon-price')
                });
            });
            
            $('.addon-item').remove();
            if(addonsHtml) {
                $('.subtotal').before(addonsHtml);
            }
        },
        
        getAddonHtml: function(data) {
            return `
                <div class="order-summary-item addon-item">
                    <span>${data['addon-title']}</span>
                    <span>${$('.woocommerce-Price-currencySymbol').first().text()}${parseFloat(data['addon-price']).toFixed(2)}</span>
                </div>`;
        },

        // Event Handlers
        handleDeliveryChange: function() {
            this.showPreloader();
            const deliveryOption = $(event.target).val().split(' - ');
            
            $.ajax({
                url: checkoutData.ajaxurl,
                type: 'POST',
                data: {
                    action: 'update_delivery_price',
                    delivery_option: deliveryOption[0],
                    price: parseFloat(deliveryOption[1]),
                    nonce: checkoutData.nonce
                },
                success: () => this.updateOrderSummary(true),
                complete: () => this.hidePreloader()
            });
        },

        handleAddonChange: function(event) {
            this.showPreloader();
            const $checkbox = $(event.target);
            
            clearTimeout(this.addonDebounce);
            this.addonDebounce = setTimeout(() => {
                $.ajax({
                    url: checkoutData.ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'update_addons',
                        addon_id: $checkbox.data('addon-id'),
                        is_checked: $checkbox.is(':checked'),
                        nonce: checkoutData.nonce,
                        _wpnonce: checkoutData.nonce
                    },
                    success: () => this.updateOrderSummary(true),
                    complete: () => this.hidePreloader()
                });
            }, 300);
        },

        debouncedUpdate: _.debounce(function() {
            this.updateOrderSummary(true);
        }, 300)
    };

    checkout.init();
});