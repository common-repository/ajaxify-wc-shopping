/**
*
*  awcs-override-single-product.js
*
*  Override single product template actions
*
*/

function awcs_load_single_product() {

    jQuery('.awcs_single_product_frame').each( function() {

	    jQuery(this).on('load', function() {

	    	var id = jQuery(this).attr("id");

	    	var iframe = document.getElementById( id );

	    	iframe.contentWindow.document.body.style.backgroundColor = '#ffffff'; // jQuery(this).closest('.product').css("background-color");
	    	iframe.contentWindow.document.body.style.backgroundImage = 'none';

	    	var product_content_height = iframe.contentWindow.document.body.scrollHeight > 630 ? 630 : iframe.contentWindow.document.body.scrollHeight;

	    	iframe.style.height = product_content_height + 'px';
	    	iframe.style.width  = '100%';

	    	var $product_content = jQuery('#' + id).contents();

	    	if( $product_content.find('.awcs-product-ticker-header, .awcs-product-ticker-footer').length )
        		$product_content.find('.awcs-product-ticker-header, .awcs-product-ticker-footer').hide();

	    	if( $product_content.find('.woocommerce-breadcrumb').length && jQuery(window.top.document).find('.woocommerce-breadcrumb').length ) {
	    		$product_content.find('.woocommerce-breadcrumb').hide();
	    	}		        	

		    var $variations = $product_content.find('form.variations_form select');

		    $variations.on('change', function() {

		        iframe.style.height = iframe.contentWindow.document.body.scrollHeight + 'px';
		        iframe.style.width  = '100%';

		    });

		    // Single page
		    var $product_summary = $product_content.find('.entry-summary').length ? $product_content.find('.entry-summary') : $product_content;

		    if( $product_summary.length ) {

		    	$product_summary.addClass('awcs_basic_single_product_summary');

		    	// Category click event
			    var $category_link = $product_summary.find('div.product_meta .posted_in a');

			    if( $category_link.length ) {

			    	$category_link.on('click', function(e) {

				    	e.preventDefault();

				    	var category = jQuery(this).text();

				    	jQuery('.awcs_product_meta .posted_in a').text( category );

				    	jQuery('.awcs_product_meta .posted_in a').click(); 	
				    
				    });
			    	
			    }

			    // Tag click event
			    var $tag_link = $product_summary.find('div.product_meta .tagged_as a');

			    if( $tag_link.length ) {

			    	$tag_link.on('click', function(e) {

				    	e.preventDefault();

				    	var tag = jQuery(this).text();

				    	jQuery('.awcs_product_meta .tagged_as a').text( tag );

				    	jQuery('.awcs_product_meta .tagged_as a').click(); 	
				    
				    });
			    	
			    }

			    if( $product_summary.find('.woocommerce-grouped-product-list-item').length ) {
				
					$product_summary.on('click', '.woocommerce-grouped-product-list-item a', function(e) {
						
						e.preventDefault();

						var alt_product_id = jQuery(this).closest('.woocommerce-grouped-product-list-item').attr('id');
						
						var product_name = jQuery(this).text();

						jQuery('.awcs_product_meta .woocommerce-grouped-product-list-item').attr( 'id', alt_product_id );

				    	jQuery('.awcs_product_meta .woocommerce-grouped-product-list-item a').text( product_name );

				    	jQuery('.awcs_product_meta .woocommerce-grouped-product-list-item a').click();

					});

				}

		    }

		    jQuery(this).attr("scrolling", "yes");

	    });

    });

}