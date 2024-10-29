/**
*
* File awcs-basic-cart-functions.js
*
* Basic cart actions
*
* Update / change cart items - remove item, undo item, apply coupon, remove coupon, update cart
*
*/

(function ($) {

    // Avoid duplicate script
    if ( typeof wc_cart_params === 'undefined' ) {

        // Remove Item
        $( document ).on('click', '.woocommerce-cart-form .product-remove > a', function(evt) {

            evt.preventDefault();

            awcs_adjust_target_element( $(this) );
            
            var $a = $( evt.currentTarget );
            var $form = $a.parents( 'form' );

            block( $form );
            block( $( 'div.cart_totals' ) );

            var wpn = $(this).attr('href').split('&_wpnonce=');
            var wpm = wpn[0].split('remove_item=');

            $.ajax( {
                type:     'POST',
                url:      awcs_wc_global_params.ajax_url,
                data: { action: "awcs_wc_update_cart", 'wpnonce': wpn[1], 'remove_item': wpm[1], 'isSinglePage': $(this).parents('#awcs-single-page-cart').length ? 'single-page' : '' },
                success:  function( response ) {
                    awcs_target.html(response);
                    jQuery(document.body).trigger('wc_fragment_refresh');
                },
                complete: function() {
                    unblock( $form );
                    unblock( $( 'div.cart_totals' ) );
                    $.scroll_to_notices( $( '[role="alert"]' ) );
                }
            } );

        });


        // Undo Item
        $( document ).on('click', '.woocommerce-message .restore-item', function(e) {

            e.preventDefault();

            awcs_adjust_target_element( $(this) );

            block( $(this) );

            var wpn = $(this).attr('href').split('&_wpnonce=');
            var wpm = wpn[0].split('undo_item=');

            $.ajax( {
                type:     'POST',
                url:      awcs_wc_global_params.ajax_url,
                data: { action: "awcs_wc_update_cart", 'wpnonce': wpn[1], 'undo_item': wpm[1], 'isSinglePage': $(this).parents('#awcs-single-page-cart').length ? 'single-page' : '' },
                success:  function( response ) {
                    awcs_target.html(response);
                    jQuery(document.body).trigger('wc_fragment_refresh');
                },
                complete: function() {
                    unblock( $(this) );
                }
            } );

        });


        // Apply Coupon
        $( document ).on('click', '.woocommerce-cart-form :input[name="apply_coupon"]', function(e) {

            e.preventDefault();

            awcs_adjust_target_element( $(this) );

            var $form = $( '.woocommerce-cart-form' );

            block( $form );

            //var cart = this;
            var $text_field = $( '#coupon_code' );
            var coupon_code = $text_field.val();
            var wpn = $( '.woocommerce-cart-form' ).find('#woocommerce-cart-nonce').val();
            var awcs_apply_coupon_nonce = $('#awcs_apply_coupon_nonce').val();

            var data = {
                 action: "awcs_wc_update_cart",
                'wpnonce': wpn,
                'awcs_apply_coupon': awcs_apply_coupon_nonce,
                'coupon_code': coupon_code,
                'isSinglePage': $(this).parents('#awcs-single-page-cart').length ? 'single-page' : ''
            };

            $.ajax({
                type:     'POST',
                url:      awcs_wc_global_params.ajax_url,
                data:     data,
                success: function( response ) {
                    $( '.woocommerce-error, .woocommerce-message, .woocommerce-info' ).remove();
                    awcs_target.html(response);
                    $( document.body ).trigger( 'applied_coupon', [ coupon_code ] );
                },
                complete: function() {
                     unblock( $form );
                     $text_field.val( '' );
                     $.scroll_to_notices( $( '[role="alert"]' ) );
                }
            });

        });


        // Apply Coupon on keypress
        $( document ).on('keypress', '.woocommerce-cart-form #coupon_code', function(e) {

            if ( 13 === e.keyCode ) {

                e.preventDefault();

                awcs_adjust_target_element( $(this) );

                var $form = $( '.woocommerce-cart-form' );

                block( $form );

                var $text_field = $( '#coupon_code' );
                var coupon_code = $text_field.val();
                var wpn = $( '.woocommerce-cart-form' ).find('#woocommerce-cart-nonce').val();
                var awcs_apply_coupon_nonce = $('#awcs_apply_coupon_nonce').val();

                var data = {
                     action: "awcs_wc_update_cart",
                    'wpnonce': wpn,
                    'awcs_apply_coupon': awcs_apply_coupon_nonce,
                    'coupon_code': coupon_code,
                    'isSinglePage': $(this).parents('#awcs-single-page-cart').length ? 'single-page' : ''
                };

                $.ajax({
                    type:     'POST',
                    url:      awcs_wc_global_params.ajax_url,
                    data:     data,
                    success: function( response ) {
                        $( '.woocommerce-error, .woocommerce-message, .woocommerce-info' ).remove();
                        awcs_target.html(response);
                        $( document.body ).trigger( 'applied_coupon', [ coupon_code ] );
                    },
                    complete: function() {
                        unblock( $form );
                        $text_field.val( '' );
                        $.scroll_to_notices( $( '[role="alert"]' ) );
                    }
                });

            }

        });


        // Remove Coupon
        $( document ).on('click', '.cart-collaterals a.woocommerce-remove-coupon', function(e) {

            e.preventDefault();

            awcs_adjust_target_element( $(this) );

            var $wrapper = $(this).closest( '.cart_totals' );
            var $form = $( '.woocommerce-cart-form' );

            block( $form );
            block($wrapper);

            var $text_field = $( '#coupon_code' );
            var coupon_code = $text_field.val();
            var wpn = $( '.woocommerce-cart-form' ).find('#woocommerce-cart-nonce').val();
            var coupon = $(this).attr('data-coupon');
            var awcs_remove_coupon_nonce = $('#awcs_remove_coupon_nonce').val();

            var data = {
                 action: "awcs_wc_update_cart",
                'wpnonce': wpn,
                'awcs_remove_coupon': coupon,
                'awcs_remove_coupon_nonce': awcs_remove_coupon_nonce,
                'isSinglePage': $(this).parents('#awcs-single-page-cart').length ? 'single-page' : ''
            };

            $.ajax({
                type:     'POST',
                url:      awcs_wc_global_params.ajax_url,
                data:     data,
                success: function( response ) {
                    $( '.woocommerce-error, .woocommerce-message, .woocommerce-info' ).remove();
                    awcs_target.html(response);
                    jQuery(document.body).trigger('wc_fragment_refresh');
                    $( document.body ).trigger( 'applied_coupon', [ coupon_code ] );
                },
                complete: function() {
                    unblock( $form );
                    unblock($wrapper);
                    $text_field.val( '' );
                    $.scroll_to_notices( $( '[role="alert"]' ) );
                }
            });

        });


        // Update Cart
        $( document ).on('click', '.woocommerce-cart-form :input[name="update_cart"]', function(e) {

            e.preventDefault();

            var $this = $(this);

            awcs_adjust_target_element( $this );

            var $form = $( '.woocommerce-cart-form' );
            var wpn = $( '.woocommerce-cart-form' ).find('#woocommerce-cart-nonce').val();

            var sArray = [];
            var data = $form.serialize();
            data = data + "&action=" + 'awcs_wc_update_cart';
            data = data + "&wpnonce=" + wpn;
            data = data + "&update_cart=" + 'update_cart';

            block( $form );
            block( $( 'div.cart_totals' ) );

            // Make call to actual form post URL.
            $.ajax({
                type:     'POST',
                url:      awcs_wc_global_params.ajax_url,
                data:     data,
                success:  function( response ) {
                    
                    jQuery(document.body).trigger('wc_fragment_refresh');

                    $.ajax({
                        type:     'POST',
                        url:      awcs_wc_global_params.ajax_url,
                        data:     { action: 'awcs_wc_view_updated_cart', 'isSinglePage': $this.parents('#awcs-single-page-cart').length ? 'single-page' : '' },
                        success:  function( response ) {
                            awcs_target.html(response);
                        },
                        complete: function() {
                            unblock( $form );
                            unblock( $( 'div.cart_totals' ) );
                            $.scroll_to_notices( $( '[role="alert"]' ) );
                        }
                    });
                    
                }
            });

        });


        // Update Cart on keypress
        $( document ).on('keypress', '.woocommerce-cart-form input.qty', function(e) {

            if ( 13 === e.keyCode ) {

                e.preventDefault();

                var $this = $(this);

                awcs_adjust_target_element( $this );
            
                var $form = $( '.woocommerce-cart-form' );
                var wpn = $( '.woocommerce-cart-form' ).find('#woocommerce-cart-nonce').val();

                var sArray = [];
                var data = $form.serialize();
                data = data + "&action=" + 'awcs_wc_update_cart';
                data = data + "&wpnonce=" + wpn;
                data = data + "&update_cart=" + 'update_cart';

                block( $form );
                block( $( 'div.cart_totals' ) );

                // Make call to actual form post URL.
                $.ajax({
                    type:     'POST',
                    url:      awcs_wc_global_params.ajax_url,
                    data:     data,
                    success:  function( response ) {
                        
                        jQuery(document.body).trigger('wc_fragment_refresh');

                        $.ajax({
                            type:     'POST',
                            url:      awcs_wc_global_params.ajax_url,
                            data:     { action: 'awcs_wc_view_updated_cart', 'isSinglePage': $this.parents('#awcs-single-page-cart').length ? 'single-page' : '' },
                            success:  function( response ) {
                                awcs_target.html(response);                                
                            },
                            complete: function() {
                                unblock( $form );
                                unblock( $( 'div.cart_totals' ) );
                                $.scroll_to_notices( $( '[role="alert"]' ) );
                            }
                        });
                        
                    }

                });

            }

        });

    }

})(jQuery);