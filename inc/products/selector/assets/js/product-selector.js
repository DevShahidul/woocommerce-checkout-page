// js/product-selector.js
jQuery(function($) {
   const $selector = $('#product-selector');
   const $preloader = $('.checkout-sidebar .preloader-overlay');
   let isProcessing = false;
   

   // Handle product selection
   $selector.on('change', function() {
        if(isProcessing) return;

       const $selected = $(this).find('option:selected');
       const productId = $(this).val();

       
       if(!productId){
        $preloader.removeClass('active');
        return;
       }
       
       isProcessing = true;
       // Show preloader
       showPreloader();

        const price = $selected.data('price');
        const name = $selected.text();

        const emptyCart = () => {
            return $.ajax({
                url: wc_checkout_params.ajax_url,
                type: 'POST',
                data: { action: 'empty_cart' }
            });
        };

        const addToCart = () => {
            return $.ajax({
                url: wc_checkout_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'woocommerce_add_to_cart',
                    product_id: productId,
                    quantity: 1
                }
            });
        };

         // Execute sequence with proper cleanup
         emptyCart()
         .then(addToCart)
         .then(response => {
             if(response.error && response.product_url) {
                 window.location = response.product_url;
                 return;
             }

             $(document.body).trigger('update_checkout', {
                 update_shipping_method: false
             });

             $('.order-summary-item.product-info').html(`
                 <span class="item-name">${name}</span>
                 <span class="item-price">
                     <span class="woocommerce-Price-amount amount">
                     ${price.toFixed(2)}
                     </span>
                 </span>
             `);
         })
         .fail(error => {
             console.error('Operation failed:', error);
         })
         .always(() => {
             isProcessing = false;
             hidePreloader();
         });
           

        // $('.woocommerce-Price-currencySymbol').first().text() + price.toFixed(2)

        // // Clear existing cart contents
        // $.ajax({
        //     url: wc_checkout_params.ajax_url,
        //     type: 'POST',
        //     data: {
        //         action: 'empty_cart'
        //     },
        //     success: function() {
        //         // Add new product to cart
        //         $.ajax({
        //             url: wc_checkout_params.ajax_url,
        //             type: 'POST',
        //             data: {
        //                 action: 'woocommerce_add_to_cart',
        //                 product_id: productId,
        //                 quantity: 1
        //             },
        //             success: function(response) {
        //                 if(response.error && response.product_url) {
        //                     window.location = response.product_url;
        //                     return;
        //                 }

        //                 // Trigger WooCommerce to update all fragments
        //                 $(document.body).trigger('update_checkout', {
        //                     update_shipping_method: false
        //                 });

        //                 // Update custom elements
        //                 $('.order-summary-item.product-info').html(`
        //                     <span class="item-name">${name}</span>
        //                     <span class="item-price">
        //                         <span class="woocommerce-Price-amount amount">
        //                         ${price.toFixed(2)}
        //                         </span>
        //                     </span>
        //                 `);
        //             },
        //             complete: function() {
        //                 // Hide preloader after both operations
        //                 hidePreloader();
        //             },
        //             error: function() {
        //                 // Handle AJAX error
        //                 hidePreloader();
        //                 console.error('Add to cart failed');
        //             }
        //         });
        //     },
        //     error: function() {
        //         // Handle empty cart error
        //         hidePreloader();
        //         console.error('Empty cart failed');
        //     }
        // });

   });

   function showPreloader() {
    $('.order-summary').addClass('loading');
    $('.preloader-overlay').addClass('active');
   }

   function hidePreloader() {
    $('.order-summary').removeClass('loading');
    $('.preloader-overlay').removeClass('active');
   }

//     // Update order summary
//    function updateOrderSummary(productData) {
//         const $productInfo = $(".order-summary-item.product-info");
        
//         // Show loading state during update
//         showPreloader();
        
//         // Update product name
//         $productInfo.find('.item-name').text(productData.name);
//         // Update price with WooCommerce formatting
//         $('.item-price').html(productData.price_html);
//         // Update cart subtotal
//         $('.subtotal-amount .woocommerce-Price-amount').html(productData.price_html);
//         // Optional: Update cart quantity
//         $('.product-quantity').text('1');

//         $.ajax({
//             url: checkoutData.ajaxurl,
//             type: 'POST',
//             data: {
//                 action: 'update_cart_totals',
//                 nonce: checkoutData.nonce
//             },
//             success: function(response) {
//                 if (response.success) {
//                     $('.subtotal-amount').html(response.data.subtotal);
//                     $('.total-amount').html(response.data.total);
//                 }
//             },
//             complete: function() {
//                 // Hide preloader after update
//                 hidePreloader();
//             },
//             error: function() {
//                 hidePreloader();
//             }
//         });
//     }

    // Handle WooCommerce fragments update
    $(document.body).on('updated_checkout', function() {
        if(!isProcessing) return;

        // Update totals from WooCommerce data
        const totals = wc_checkout_params.totals;
        
        $('.subtotal-amount .amount').html(totals.subtotal);
        $('.total-amount .amount').html(totals.total);
        $('.shipping-amount .amount').html(totals.shipping_total);
        
        // Update any custom fee displays
        if(totals.fees) {
            $('.fee-amount .amount').html(totals.fees);
        }
    });

     // Error handling for AJAX failures
     $(document).ajaxError(function(event, jqxhr, settings, thrownError) {
        isProcessing = false;
        hidePreloader();
        console.error('AJAX Error:', settings.url, thrownError);
    });

});