/**
*
* File awcs-wc-add-to-cart.js
*
* Add to cart from Single product
*
* Variable product add to cart
*
*/

jQuery(document).ready(function($) {

	// Add to cart
	$(document).on('click', '.single_add_to_cart_button, .awcs_loop_variation_add_to_cart_button', function (e) {

	    e.preventDefault();

	    var $thisbutton = $(this);

	    $thisbutton.addClass( 'loading' );
	        
	    block($thisbutton);

	    if( ( $thisbutton.closest('.awcs_wc_single_product_wrapper').find('.awcs_single_product_type').length && "grouped" == $thisbutton.closest('.awcs_wc_single_product_wrapper').find('.awcs_single_product_type').val() ) || $thisbutton.closest('.awcs_product_type_grouped ').length ) {
	    	
	    	var $form = $thisbutton.closest('form.cart');

	    	$(document.body).trigger('adding_to_cart', [$thisbutton, $form.serialize()]);

		    $.ajax({
		        type: $thisbutton.attr('method'),
		        url: $thisbutton.attr('action'),
		        data: $form.serialize(),
		        beforeSend: function (response) {
	            	$thisbutton.removeClass('added').addClass('loading');
		        },
		        complete: function (response) {
		            $thisbutton.addClass('added').removeClass('loading');
		            unblock($thisbutton);
		        },
		        success: function(response) {
		        	if( $thisbutton.parent().find( '.added_to_cart' ).length === 0 )
		        		$thisbutton.after( '<a href="' + wc_add_to_cart_params.cart_url + '" class="added_to_cart wc-forward" title="' +
	                            wc_add_to_cart_params.i18n_view_cart + '">' + wc_add_to_cart_params.i18n_view_cart + '</a>' );
		        	$(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisbutton]);
		        }
		    });

	    	return;
	    }

	    if( $thisbutton.closest('.product-type-external').length ) {
	    	$thisbutton.removeClass('loading');
		    unblock($thisbutton);
	    	var external_link = $thisbutton.closest('form').attr('action');
	    	window.open( external_link );
	    	return;
	    }

	    var $form = $thisbutton.closest('form.cart'),
	        id = $thisbutton.val(),
	        product_qty = $form.find('input[name=quantity]').val() || 1,
	        product_id = $form.find('input[name=product_id]').val() || id,
	        variation_id = $form.find('input[name=variation_id]').val() || 0;

	    if ( $thisbutton.is('.disabled') ) {
	    	if ( $thisbutton.is('.wc-variation-is-unavailable') ) {
		        window.alert( awcs_wc_add_to_cart_variation_params.i18n_unavailable_text );
		    } else if ( $thisbutton.is('.wc-variation-selection-needed') ) {
		        window.alert( awcs_wc_add_to_cart_variation_params.i18n_make_a_selection_text );
		    }
		    return;
		}

	    var variation_data = {};

	    var attributeFields = $form.find( '.variations select' );

	    attributeFields.each( function() {
	        var attribute_name = $( this ).data( 'attribute_name' ) || $( this ).attr( 'name' );
	        var value          = $( this ).val() || '';

	        variation_data[ attribute_name ] = value;
	    });

	    var data = {
	        action: 'awcs_wc_add_to_cart',
	        product_id: product_id,
	        product_sku: '',
	        quantity: product_qty,
	        variation_id: variation_id,
	        variation: variation_data
	    };

	    $(document.body).trigger('adding_to_cart', [$thisbutton, data]);

	    $.ajax({
	        type: 'post',
	        url: awcs_wc_global_params.ajax_url,
	        data: data,
	        beforeSend: function (response) {
	            $thisbutton.removeClass('added').addClass('loading');
	        },
	        complete: function (response) {
	            $thisbutton.addClass('added').removeClass('loading');
	            unblock($thisbutton);
	        },
	        success: function (response) {

	            if (response.error && response.product_url) {

	                alert("Sorry, attribute missing for this variation. Please contact with admin.");
	                return;

	            } else {
	         
	                $thisbutton.removeClass( 'loading' );
	            
	                if ( response.fragments ) {
	                    $thisbutton.addClass( 'added' );
	                }

	                if ( response.fragments && $thisbutton.parent().find( '.added_to_cart' ).length === 0 ) {
	                    $thisbutton.after( '<a href="' + wc_add_to_cart_params.cart_url + '" class="added_to_cart wc-forward" title="' +
	                            wc_add_to_cart_params.i18n_view_cart + '">' + wc_add_to_cart_params.i18n_view_cart + '</a>' );
	                }
	                
	                $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $thisbutton]);
	                
	            }
	        }
	    });

	    return false;
	});

	$(document).on('click', 'a.add_to_cart_button', function() {
	    block( $(this) );
	});

	$(document).on('added_to_cart', function() {
	    unblock( $('.add_to_cart_button') );
	});

});