/**
*
* File awcs-basic-my-account.js
*
* Basic my account contents
*
*/

(function($) {

	// Ajax call for MyAccount content
	function awcs_ajax_get_content(data, target, title) {

		var $this = $(this);

		block( target );

		$.ajax({
			type: 'post',
			url:  awcs_wc_global_params.ajax_url,
			data: data,
			success: function(content) {
				target.html(content).promise().done( function() {
					awcs_target.find('.entry-header .entry-title, header .page-title').text(title);
					if( target == awcs_target ) {
						var pm_link = $(document).find('li.woocommerce-MyAccount-navigation-link--payment-methods a');
	                    if( pm_link.length ) {
	                        pm_link.attr( 'href', pm_link.attr('href') + '?referrer=pm' );
	                    }
					}
				});
			},
			complete: function(response) {
				
				unblock( target );

				$.scroll_to_notices( $( '[role="alert"], .entry-title, .page-title' ) );

			}
		});

	}

	// MyAccount Account Login Link - redirect to dashboard
	$(document).on('click', '.woocommerce form.woocommerce-form-login .woocommerce-form-login__submit', function(e) {

		e.preventDefault();

		awcs_adjust_target_element( $(this) );

		var woocommerce_login_nonce = $('#woocommerce-login-nonce').val();
		var username = $(this).closest('form').find('#username').val();
		var password = $(this).closest('form').find('#password').val();
		var rememberme = $(this).closest('form').find('#rememberme').val();

		var $form = $(this).closest('form');
		block( $form );

		var data = {
			action: 'awcs_process_login',
			'woocommerce_login_nonce': woocommerce_login_nonce,
			'username': username,
			'password': password,
			'rememberme': rememberme,
			'login': 'Log in'
		}

		var title = 'My account';

		$.ajax({
			type: 'post',
			url: awcs_wc_global_params.ajax_url,
			data: data,
			success: function(response) {
				$.ajax({
					type: 'post',
					url:  awcs_wc_global_params.ajax_url,
					data: { action: 'awcs_wc_view_account' },
					success: function(content) {
						awcs_target.html(content).promise().done( function() {
							awcs_target.find('.entry-header .entry-title, header .page-title').text(title);
							var pm_link = $(document).find('li.woocommerce-MyAccount-navigation-link--payment-methods a');
		                    if( pm_link.length ) {
		                        pm_link.attr( 'href', pm_link.attr('href') + '?referrer=pm' );
		                    }
						});
					},
					complete: function(response) {
						unblock( $form );
					}
				});
			}
		});
		
	});

	// MyAccount show lost password form
	$(document).on('click', '.woocommerce .woocommerce-form-login .woocommerce-LostPassword a', function(e) {

		e.preventDefault();

		awcs_adjust_target_element( $(this) );

		var data = {
			action: 'awcs_lost_password_form'
		}

		var title = 'My account';

		awcs_ajax_get_content(data, awcs_target, title);

	});

	// MyAccount submit reset password form
	$(document).on('submit', 'form.woocommerce-ResetPassword', function(e) {

		e.preventDefault();

		awcs_adjust_target_element( $(this) );

		var $form = $(this);

		$form.append('<input type="hidden" name="action" value="awcs_process_lost_password_form" />');

		var form_data = $form.serializeArray();

		block( $form );

		$.ajax({
			type: 'post',
			url:  awcs_wc_global_params.ajax_url,
			data: form_data,
			success: function(response) {

				var data = {
					action: 'awcs_confirmation_lost_password_email_sent'
				}

				var title = 'Lost password';

				awcs_ajax_get_content(data, awcs_target, title);

			},
			complete: function(response) {
				unblock( $form );
			}
		});

	});

	// MyAccount Account Register Link - redirect to dashboard
	$(document).on('click', '.woocommerce form.woocommerce-form-register .woocommerce-form-register__submit', function(e) {

		e.preventDefault();

		awcs_adjust_target_element( $(this) );

		var $form = $(this).closest('form');

		$form.append('<input type="hidden" name="action" value="awcs_process_registration" />');

		var data = $form.serializeArray();

		var title = 'My account';

		awcs_ajax_get_content(data, awcs_target, title);

	});

	// MyAccount Dashboard click event
	$(document).on('click', 'li.woocommerce-MyAccount-navigation-link--dashboard a', function(e) {

		e.preventDefault();

		var data = {
			action: 'awcs_my_account_dashboard'
		}

		var target = $(document).find('div.woocommerce .woocommerce-MyAccount-content');

		var title = 'My account';

		awcs_ajax_get_content(data, target, title);

	});

	// MyAccount Dashboard Links
	$(document).on('click', '.woocommerce .woocommerce-MyAccount-content a', function(e) {

		e.preventDefault();

		var link = $(this).attr('href');
		
		if( awcs_wc_global_params.account_orders_url.indexOf(link) >= 0 ) {
			awcs_get_account_orders();
		} else if( awcs_wc_global_params.account_address_url.indexOf(link) >= 0 ) {
			awcs_get_edit_address_endpoint();
		} else if( awcs_wc_global_params.account_edit_url.indexOf(link) >= 0 ) {
			awcs_get_edit_account_endpoint();
		} else if( awcs_wc_global_params.account_billing_address.indexOf(link) >= 0 ) {
			awcs_get_edit_address_screen( 'billing' );
		} else if( awcs_wc_global_params.account_shipping_address.indexOf(link) >= 0 ) {
			awcs_get_edit_address_screen( 'shipping' );
		} else if( link.indexOf( '/my-account/customer-logout') >= 0 ) {
			var $target = $(this);
			block( $target );

			$.ajax({
				type: 'GET',
				url: $target.attr('href'),
				dataType: 'html',
				success:  function( response ) {
					var title = 'My account';
						
					var data = {
						action: 'awcs_wc_view_account'
					}

					awcs_ajax_get_content(data, awcs_target, title);
				},
				complete: function() {

					unblock( $target );

					$.scroll_to_notices( $( '[role="alert"], .entry-title, .page-title' ) );

				}
			});
		}

	});

	// MyAccount Orders click event
	$(document).on('click', 'li.woocommerce-MyAccount-navigation-link--orders a', function(e) {

		e.preventDefault();

		awcs_get_account_orders();

	});

	// MyAccount Orders prev/next page click
	$(document).on('click', '.woocommerce .woocommerce-MyAccount-content .woocommerce-Pagination a.woocommerce-button--next, .woocommerce .woocommerce-MyAccount-content .woocommerce-Pagination a.woocommerce-button--previous', function(e) {

		e.preventDefault();

		var page = $(this).attr('href').split('orders/')[1].replace('/', '');

		var data = {
			action: 'awcs_my_account_orders',
			'current_page': page
		}

		var target = $(document).find('div.woocommerce .woocommerce-MyAccount-content');

		var title = 'Orders';

		awcs_ajax_get_content(data, target, title);

	});

	// MyAccount Order - Pay Link
	$(document).on('click', '.woocommerce .woocommerce-MyAccount-content table.woocommerce-MyAccount-orders a.pay', function(e) {

		e.preventDefault();

		awcs_adjust_target_element( $(this) );

		var $target = $(this);

		$.ajax( {
				type:     'GET',
				url:      $target.attr('href'),
				dataType: 'html',
				success:  function( response ) {

					var order_link = $target.attr('href').split('/?pay_for_order');

					order_link = order_link[0];

					var order_key = $target.attr('href').split('key=wc_order_');

					var order_id = order_link.split('order-pay/')[1];

					var data = {
						action: 'awcs_wc_order_pay',
						'order_id': order_id
					}

					var title = 'Pay for order';

					$.ajax({
						type: 'post',
						url:  awcs_wc_global_params.ajax_url,
						data: data,
						success: function(content) {
							awcs_target.html(content).promise().done( function() {
								$(document).find('form#order_review').css("width", "100%");
								$(this).closest('article').find('.entry-header .entry-title').text(title);
								$(document).find('form#order_review').append('<input type="hidden" name="order_id" value="'+ order_id +'" />');
								$(document).find('form#order_review').append('<input type="hidden" name="key" value="'+ order_key[1] +'" />');
								$(document).find('form#order_review').append('<input type="hidden" name="action" value="awcs_wc_process_payment" />');
								$(document).find('form#order_review input[name="_wp_http_referer"]').val($target.attr('href'));
							});
						}
					});

				}
		} );

	});

	// MyAccount Submit Pay for order
	$(document).on('click', 'form#order_review #place_order', function(e) {

		e.preventDefault();

		awcs_adjust_target_element( $(this) );

		var $form = $(this).closest('form');

		var data = $form.serializeArray();

		var title = 'Order Confirmation';

		$.ajax({
			type: 'post',
			url:  awcs_wc_global_params.ajax_url,
			data: data,
			success: function(content) {
				awcs_target.html(content).promise().done( function() {
					awcs_target.find('.entry-header .entry-title, header .page-title').text(title);
				});
			}
		});

	});

	// MyAccount Order - View Link
	$(document).on('click', '.woocommerce .woocommerce-MyAccount-content table.woocommerce-MyAccount-orders a.view', function(e) {

		e.preventDefault();

		var order_link = $(this).attr('href').split('view-order/');

		order_link = order_link[1];

		var order_id = order_link.replace('/', '');

		var data = {
			action: 'awcs_my_account_view_order',
			'order_id': order_id
		}

		var target = $(document).find('div.woocommerce .woocommerce-MyAccount-content');

		var title = 'Order #'+order_id;

		awcs_ajax_get_content(data, target, title);

	});

	// MyAccount Order - Cancel Link
	$(document).on('click', '.woocommerce .woocommerce-MyAccount-content table.woocommerce-MyAccount-orders a.cancel', function(e) {

		e.preventDefault();

		var $target = $(this);

		$.ajax( {
				type:     'GET',
				url:      $target.attr('href'),
				dataType: 'html',
				success:  function( response ) {
					
					var data = {
						action: 'awcs_my_account_dashboard'
					}

					var target = $(document).find('div.woocommerce .woocommerce-MyAccount-content');

					var title = 'My account';

					awcs_ajax_get_content(data, target, title);

				},
				complete: function() {

					$.scroll_to_notices( $( '[role="alert"], .entry-title, .page-title' ) );
					
				}
		} );

	});

	// MyAccount View Order
	$(document).on('click', 'tr.woocommerce-orders-table__row td.woocommerce-orders-table__cell-order-number a', function(e) {

		e.preventDefault();

		var order_link = $(this).text().split('#');

		var data = {
			action: 'awcs_my_account_view_order',
			'order_id': order_link[1]
		}

		var target = $(document).find('div.woocommerce .woocommerce-MyAccount-content');

		var title = 'Order #'+order_link[1];

		awcs_ajax_get_content(data, target, title);

	});

	// MyAccount View Order - Order Again Link
	$(document).on('click', '.woocommerce .woocommerce-MyAccount-content .woocommerce-order-details .order-again a', function(e) {

		e.preventDefault();

		var $content = $('.woocommerce .woocommerce-MyAccount-content');
		var $target = $(this);

		block( $content );
		block( $target );

		// Make call to actual form post URL.
		$.ajax( {
				type:     'GET',
				url:      $target.attr('href'),
				dataType: 'html',
				success:  function( response ) {
					
					var data = {
			            action: 'awcs_order_again_view_cart',
			            'isSinglePage': $target.parents('#awcs-single-page-my-account').length ? 'single-page' : ''
			        };

			        $.ajax({
			            type: 'post',
			            url: awcs_wc_global_params.ajax_url,
			            data: data,
			            success: function (response) {

			            	$( '.woocommerce-error, .woocommerce-message, .woocommerce-info' ).remove();
			            	
			            	awcs_target.html(response);			            	
			                
			                jQuery(document.body).trigger('wc_fragment_refresh');
			            },
						complete: function() {

							$.scroll_to_notices( $( '[role="alert"], .entry-title, .page-title' ) );
						
						}
			        });

			        return false;

				},
				complete: function() {
					unblock( $content );
					unblock( $target );
				}
		} );

	});

	// MyAccount Downloads click event
	$(document).on('click', 'li.woocommerce-MyAccount-navigation-link--downloads a', function(e) {

		e.preventDefault();

		var data = {
			action: 'awcs_my_account_downloads'
		}

		var target = $(document).find('div.woocommerce .woocommerce-MyAccount-content');

		var title = 'Downloads';

		awcs_ajax_get_content(data, target, title);

	});

	// MyAccount Edit address click event
	$(document).on('click', 'li.woocommerce-MyAccount-navigation-link--edit-address a', function(e) {

		e.preventDefault();

		awcs_get_edit_address_endpoint();

	});

	// MyAccount Edit address billing/shipping address
	$(document).on('click', 'li.woocommerce-MyAccount-navigation-link-orders a', function(e) {

		e.preventDefault();

		$(document).find('div.woocommerce .woocommerce-MyAccount-content').html('<h1>billing/shipping address</h1>');

	});

	// MyAccount Edit address - save address - redirect to addresses
	$(document).on('click', 'div.woocommerce .woocommerce-MyAccount-content .woocommerce-address-fields button[name="save_address"]', function(e) {

		e.preventDefault();

		var $form = $(this).closest('form');

		var data = $form.serializeArray();

		var target = $(document).find('div.woocommerce .woocommerce-MyAccount-content');

		var title = 'Addresses';

		block( $(this) );

		$.ajax({
			type: 'post',
			url:  awcs_wc_global_params.ajax_url,
			data: data,
			complete: function() {
							
				awcs_get_edit_address_endpoint();
				
				$.scroll_to_notices( $( '[role="alert"], .entry-title, .page-title' ) );
				
			}
		});

	});

	// MyAccount Payment methods click event
	$(document).on('click', 'li.woocommerce-MyAccount-navigation-link--payment-methods a', function(e) {

		e.preventDefault();

		var $this = $(this);

		block( $this );

		var data = {
			action: 'awcs_my_account_payment_methods'
		}

		var target = $(document).find('div.woocommerce .woocommerce-MyAccount-content').first();

		block( target );

		var title = 'Payment methods';

		var pm_url = awcs_wc_global_params.account_url + 'payment-methods/';
	
		var pm_page = '<div id="awcs_pm_wrapper" style="display: block;">';

		pm_page += '<iframe id="awcs_pm_iframe" src="' + pm_url + '" frameborder="0" allowtransparency="true" scrolling="no" allowpaymentrequest="true" data-origwidth="" data-origheight="" style="width: 100%; height: 3400px;" ></iframe>';

		pm_page += '</div>';

		target.html( pm_page );

		$('#awcs_pm_iframe').on('load', function() {

			unblock( target );

			unblock( $this );

			awcs_target.find('.entry-header .entry-title, header .page-title').text(title);
			unblock( $('.woocommerce .woocommerce-MyAccount-content a') );

			var pm_frame = $('#awcs_pm_iframe').contents();

			pm_frame.find("a").click(function() {
		        block( target );
		    });

		});

	});

	// MyAccount Account details click event
	$(document).on('click', 'li.woocommerce-MyAccount-navigation-link--edit-account a', function(e) {

		e.preventDefault();

		awcs_get_edit_account_endpoint();

	});

	// MyAccount Account details save account - redirect to dashboard
	$(document).on('click', 'div.woocommerce .woocommerce-MyAccount-content form.woocommerce-EditAccountForm button[name="save_account_details"]', function(e) {

		e.preventDefault();

		var $form = $(this).closest('form');

		var data = $form.serializeArray();

		var target = $(document).find('div.woocommerce .woocommerce-MyAccount-content');

		var title = 'My account';

		awcs_ajax_get_content(data, target, title);

	});

	// MyAccount Customer Logout click event
	$(document).on('click', '.woocommerce .woocommerce-MyAccount-navigation li.woocommerce-MyAccount-navigation-link--customer-logout a', function(e) {

		e.preventDefault();

		awcs_adjust_target_element( $(this) );

		var $target = $(this);

		block( $target );

		$.ajax({
			type:     'GET',
			url:      $target.attr('href'),
			dataType: 'html',
			success:  function( response ) {
					
				var data = {
					action: 'awcs_wc_view_account'
				}

				var title = 'My account';

				awcs_ajax_get_content(data, awcs_target, title);

			},
			complete: function() {

				unblock( $target );
				
				$.scroll_to_notices( $( '[role="alert"], .entry-title, .page-title' ) );
				
			}
		});

	});

	function awcs_get_account_orders() {

		var data = {
			action: 'awcs_my_account_orders'
		}

		var target = $(document).find('div.woocommerce .woocommerce-MyAccount-content');

		var title = 'Orders';

		awcs_ajax_get_content(data, target, title);

	}

	function awcs_get_edit_address_endpoint() {

		var data = {
			action: 'awcs_my_account_edit_address'
		}

		var target = $(document).find('div.woocommerce .woocommerce-MyAccount-content');

		var title = 'Addresses';

		awcs_ajax_get_content(data, target, title);

	}

	function awcs_get_edit_address_screen( endpoint ) {

		var data = {
			action: 'awcs_my_account_edit_address_screen',
			'endpoint': endpoint
		}

		var target = $(document).find('div.woocommerce .woocommerce-MyAccount-content');

		var title = 'Addresses';

		awcs_ajax_get_content(data, target, title);

	}

	function awcs_get_edit_account_endpoint() {

		var data = {
			action: 'awcs_my_account_account_details'
		}

		var target = $(document).find('div.woocommerce .woocommerce-MyAccount-content');

		var title = 'Account details';

		awcs_ajax_get_content(data, target, title);

	}

	$(document).ready(function() {
		
		if( window.location.href.indexOf('my-account/payment-methods/?referrer=pm') >= 0 ) {

			var title = 'Payment methods';
			
			var target = $(document).find('div.woocommerce .woocommerce-MyAccount-content').first();

			var pm_url = awcs_wc_global_params.account_url + 'payment-methods/';
		
			var pm_page = '<div id="awcs_pm_wrapper" style="display: block;">';

			pm_page += '<iframe id="awcs_pm_iframe" src="' + pm_url + '" frameborder="0" allowtransparency="true" scrolling="no" allowpaymentrequest="true" data-origwidth="" data-origheight="" style="width: 100%; height: 3400px;" ></iframe>';

			pm_page += '</div>';

			target.html( pm_page );

			awcs_adjust_target_element( target );

			$('#awcs_pm_iframe').on('load', function() {

				unblock( target );

				awcs_target.find('.entry-header .entry-title, header .page-title').text(title);
				unblock( $('.woocommerce .woocommerce-MyAccount-content a') );

				var pm_frame = $('#awcs_pm_iframe').contents();

				pm_frame.find("a").click(function() {
			        block( target );
			    });

			});
		}

	});

})(jQuery);