// js/product-selector.js
jQuery(function($) {
   const $selector = $('#product-selector');
   const $details = $('#product-details');
   const $quantity = $('#product-quantity');
   const $addButton = $('#add-to-cart-btn');
   const $message = $('#cart-message');
   const $selectedProduct = $('.selected-product');
   const $stockStatus = $('.stock-status');

   // Handle product selection
   $selector.on('change', function() {
       const $selected = $(this).find('option:selected');
       const productId = $(this).val();

       if(productId){
        const price = $selected.data('price');
        const name = $selected.text();

        // Update order summary front end
        $('.order-summary-item.product-info').html(`
            <span class="item-name">${name}</span>
            <span class="item-price">
                <span class="woocommerce-Price-amount amount">
                ${price.toFixed(2)}
                </span>
            </span>
            `
        );
        $('.woocommerce-Price-currencySymbol').first().text() + price.toFixed(2)

        $.ajax({
            url: wc_checkout_params.ajax_url,
            type: 'POST',
            data: {
                action: 'empty_cart'
            },
            success: function() {
                // Add selected product with quantity 1
                $.ajax({
                    url: wc_checkout_params.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'add_single_product',
                        product_id: productId
                    },
                    success: function(response) {
                        if (response.success) {
                            // Update order summary
                            $.ajax({
                                url: wc_checkout_params.ajax_url,
                                type: 'POST',
                                data: {
                                    action: 'update_checkout_product',
                                    product_id: productId
                                },
                                success: function(response) {
                                    if (response.success) {
                                        // Trigger WooCommerce to update checkout fragments
                                        updateOrderSummary(response.data);
                                        $(document.body).trigger('update_checkout');
                                    }else {
                                        console.log('Product update failed');
                                    }
                                },
                                error: function() {
                                    console.log('Error occurred. Please try again.');
                                }
                            });
                        }
                    },
                    error: function() {
                        console.log('AJAX request failed.');
                    }
                });
            }
        });
       }

       
       
    //    if (productId) {
    //        const price = $selected.data('price');
    //      //   const stock = $selected.data('stock');
    //        const name = $selected.text();
           

    //        $selectedProduct.html(`Selected: ${name}`);
           
    //        if (stock !== '') {
    //            $stockStatus.html(`Stock available: ${stock}`);
    //            $quantity.attr('max', stock);
    //        } else {
    //            $stockStatus.html('');
    //            $quantity.removeAttr('max');
    //        }

    //        $details.slideDown();
    //    } else {
    //        $details.slideUp();
    //    }
   });

    // Update order summary
   function updateOrderSummary(productData) {
        const $productInfo = $(".order-summary-item.product-info");
        // Update product name
        $productInfo.find('.item-name').text(productData.name);
        
        // Update price with WooCommerce formatting
        $('.item-price').html(productData.price_html);
        
        // Update cart subtotal
        $('.subtotal-amount .woocommerce-Price-amount').html(productData.price_html);
        
        // Optional: Update cart quantity
        $('.product-quantity').text('1');

        $.ajax({
            url: checkoutData.ajaxurl,
            type: 'POST',
            data: {
                action: 'update_cart_totals',
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

//    // Handle add to cart
//    $addButton.on('click', function() {
//        const productId = $selector.val();
//        const quantity = $quantity.val();

//        if (!productId) {
//            showMessage('Please select a product.', 'error');
//            return;
//        }

//        $addButton.prop('disabled', true).text('Adding...');

//        $.ajax({
//            url: ajax_object.ajax_url,
//            type: 'POST',
//            data: {
//                action: 'add_to_cart_ajax',
//                product_id: productId,
//                quantity: quantity,
//                nonce: ajax_object.nonce
//            },
//            success: function(response) {
//                if (response.success) {
//                    showMessage(response.message, 'success');
                   
//                    // Update cart count in header if it exists
//                    $('.cart-count').text(response.cart_count);
//                    $('.cart-total').html(response.cart_total);
                   
//                    // Reset form
//                    $selector.val('');
//                    $quantity.val(1);
//                    $details.slideUp();
//                } else {
//                    showMessage(response.message, 'error');
//                }
//            },
//            error: function() {
//                showMessage('Error occurred. Please try again.', 'error');
//            },
//            complete: function() {
//                $addButton.prop('disabled', false).text('Add to Cart');
//            }
//        });
//    });

//    // Helper function to show messages
//    function showMessage(text, type) {
//        $message
//            .removeClass('success error')
//            .addClass(type)
//            .html(text)
//            .fadeIn()
//            .delay(3000)
//            .fadeOut();
//    }
});