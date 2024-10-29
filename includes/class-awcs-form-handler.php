<?php
/**
 * Order, Payment and Account Form Handlers
 *
 * @package AjaxifyWoocommerceShopping
 * @version 1.1.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists('Awcs_Form_Handler') ) {
	
	/**
	 * Form Handler class.
	 */	
	class Awcs_Form_Handler {

		/**
		 * Initailize ajax handler/event
		 */
		public static function init() {

			$awcs_events = array(
			    'edit_address',
			    'save_account_details',
			    'awcs_process_login',
			    'awcs_process_registration',
			    'awcs_process_lost_password_form',
			    'awcs_wc_process_payment'
			);

			foreach ( $awcs_events as $ajax_event ) {
				add_action( 'wp_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
				add_action( 'wp_ajax_nopriv_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}

		}

	    /**
	    *
	    * Save Billing ang Shipping address
	    */
	    public static function edit_address() {

	        global $wp;

	        $nonce_value = wc_get_var( sanitize_text_field($_REQUEST['woocommerce-edit-address-nonce']), wc_get_var( sanitize_text_field($_REQUEST['_wpnonce']), '' ) );

	        if ( ! wp_verify_nonce( $nonce_value, 'woocommerce-edit_address' ) ) {
	            return;
	        }

	        if ( empty( $_POST['action'] ) || 'edit_address' !== $_POST['action'] ) {
	            return;
	        }

	        $user_id = get_current_user_id();

	        if ( $user_id <= 0 ) {
	            return;
	        }

	        $customer = new WC_Customer( $user_id );

	        if ( ! $customer ) {
	            return;
	        }

	        $load_address = isset( $wp->query_vars['edit-address'] ) ? wc_edit_address_i18n( sanitize_title( $wp->query_vars['edit-address'] ), true ) : 'billing';

	        $address_country = '';

	        if ( isset( $_POST[ $load_address . '_country' ] ) ) {
	            $address_country = sanitize_text_field($_POST[ $load_address . '_country' ]);
	        } else if ( isset( $_POST[ 'shipping_country' ] ) ) {
	            $load_address = 'shipping';
	            $address_country = sanitize_text_field($_POST[ 'shipping_country' ]);
	        }

	        if ( ! isset( $_POST[ $load_address . '_country' ] ) && ! isset( $_POST[ 'shipping_country' ] ) ) {
	            return;
	        }

	        $address = WC()->countries->get_address_fields( wc_clean( wp_unslash( $address_country ) ), $load_address . '_' );

	        foreach ( $address as $key => $field ) {

	            if ( ! isset( $field['type'] ) ) {
	                $field['type'] = 'text';
	            }

	            // Get Value.
	            if ( 'checkbox' === $field['type'] ) {
	                $value = isset( $_POST[ $key ] ) ? (int) sanitize_text_field($_POST[ $key ]) : '';
	            } else {
	                $value = isset( $_POST[ $key ] ) ? wc_clean( wp_unslash( sanitize_text_field($_POST[ $key ]) ) ) : '';
	            }

	            // Hook to allow modification of value.
	            $value = apply_filters( 'woocommerce_process_myaccount_field_' . $key, $value );

	            if ( ! empty( $value ) ) {
	                // Validation and formatting rules.
	                if ( ! empty( $field['validate'] ) && is_array( $field['validate'] ) ) {
	                    
	                    foreach ( $field['validate'] as $rule ) {

	                        switch ( $rule ) {

	                            case 'postcode':
	                                $country = wc_clean( wp_unslash( $address_country ) );
	                                $value   = wc_format_postcode( $value, $country );

	                                if ( '' !== $value && ! WC_Validation::is_postcode( $value, $country ) ) {
	                                    switch ( $country ) {
	                                        case 'IE':
	                                            $postcode_validation_notice = __( 'Please enter a valid Eircode.', 'woocommerce' );
	                                            break;
	                                        default:
	                                            $postcode_validation_notice = __( 'Please enter a valid postcode / ZIP.', 'woocommerce' );
	                                    }
	                                    
	                                }
	                                break;

	                            default:
	                            	break;
	                            
	                        }
	                    }
	                }
	            }

	            try {
	                // Set prop in customer object.
	                if ( is_callable( array( $customer, "set_$key" ) ) ) {
	                    $customer->{"set_$key"}( $value );
	                } else {
	                    $customer->update_meta_data( $key, $value );
	                }
	            } catch ( WC_Data_Exception $e ) {
	                // Set notices. Ignore invalid billing email, since is already validated.
	                if ( 'customer_invalid_billing_email' !== $e->getErrorCode() ) {
	                    wc_add_notice( $e->getMessage(), 'error' );
	                }
	            }
	        }

	        /**
	         * Hook: woocommerce_after_save_address_validation.
	         *
	         * Allow developers to add custom validation logic and throw an error to prevent save.
	         *
	         * @param int         $user_id User ID being saved.
	         * @param string      $load_address Type of address e.g. billing or shipping.
	         * @param array       $address The address fields.
	         * @param WC_Customer $customer The customer object being saved. @since 3.6.0
	         */
	        do_action( 'woocommerce_after_save_address_validation', $user_id, $load_address, $address, $customer );

	        if ( 0 < wc_notice_count( 'error' ) ) {
	            return;
	        }

	        $customer->save();

	        awcs_display_notice( 'Address changed successfully.' );

	        do_action( 'woocommerce_customer_save_address', $user_id, $load_address );

	        do_action( 'woocommerce_account_edit-address_endpoint' );

	        wp_die();
	    }

	    /**
	    *
	    * Save customer account details
	    */
	    public static function save_account_details() {

	        $nonce_value = wc_get_var( sanitize_text_field($_REQUEST['save-account-details-nonce']), wc_get_var( sanitize_text_field($_REQUEST['_wpnonce']), '' ) );

	        if ( ! wp_verify_nonce( $nonce_value, 'save_account_details' ) ) {
	            return;
	        }

	        if ( empty( $_POST['action'] ) || 'save_account_details' !== $_POST['action'] ) {
	            return;
	        }

	        $user_id = get_current_user_id();

	        if ( $user_id <= 0 ) {
	            return;
	        }

	        $account_first_name   = ! empty( $_POST['account_first_name'] ) ? wc_clean( wp_unslash( sanitize_text_field($_POST['account_first_name']) ) ) : '';
	        $account_last_name    = ! empty( $_POST['account_last_name'] ) ? wc_clean( wp_unslash( sanitize_text_field($_POST['account_last_name']) ) ) : '';
	        $account_display_name = ! empty( $_POST['account_display_name'] ) ? wc_clean( wp_unslash( sanitize_text_field($_POST['account_display_name']) ) ) : '';
	        $account_email        = ! empty( $_POST['account_email'] ) ? wc_clean( wp_unslash( sanitize_email($_POST['account_email']) ) ) : '';
	        $pass_cur             = ! empty( $_POST['password_current'] ) ? sanitize_text_field($_POST['password_current']) : ''; 
	        $pass1                = ! empty( $_POST['password_1'] ) ? sanitize_text_field($_POST['password_1']) : ''; 
	        $pass2                = ! empty( $_POST['password_2'] ) ? sanitize_text_field($_POST['password_2']) : ''; 
	        $save_pass            = true;

	        // Current user data.
	        $current_user       = get_user_by( 'id', $user_id );
	        $current_first_name = $current_user->first_name;
	        $current_last_name  = $current_user->last_name;
	        $current_email      = $current_user->user_email;

	        // New user data.
	        $user               = new stdClass();
	        $user->ID           = $user_id;
	        $user->first_name   = $account_first_name;
	        $user->last_name    = $account_last_name;
	        $user->display_name = $account_display_name;

	        // Handle required fields.
	        $required_fields = apply_filters(
	            'woocommerce_save_account_details_required_fields',
	            array(
	                'account_first_name'   => __( 'First name', 'woocommerce' ),
	                'account_last_name'    => __( 'Last name', 'woocommerce' ),
	                'account_display_name' => __( 'Display name', 'woocommerce' ),
	                'account_email'        => __( 'Email address', 'woocommerce' ),
	            )
	        );

	        if ( $account_email ) {

	            $account_email = sanitize_email( $account_email );
	            
	            $user->user_email = $account_email;
	        }

	        if ( ! empty( $pass_cur ) && empty( $pass1 ) && empty( $pass2 ) ) {
	            
	            $save_pass = false;

	        } elseif ( ! empty( $pass1 ) && empty( $pass_cur ) ) {
	            
	            $save_pass = false;

	        } elseif ( ! empty( $pass1 ) && empty( $pass2 ) ) {
	            
	            $save_pass = false;

	        } elseif ( ( ! empty( $pass1 ) || ! empty( $pass2 ) ) && $pass1 !== $pass2 ) {
	            
	            $save_pass = false;

	        } elseif ( ! empty( $pass1 ) && ! wp_check_password( $pass_cur, $current_user->user_pass, $current_user->ID ) ) {
	            
	            $save_pass = false;

	        }

	        if ( $pass1 && $save_pass ) {
	            $user->user_pass = $pass1;
	        }

	        // Allow plugins to return their own errors.
	        $errors = new WP_Error();
	        do_action_ref_array( 'woocommerce_save_account_details_errors', array( &$errors, &$user ) );

	        if ( wc_notice_count( 'error' ) === 0 ) {
	            wp_update_user( $user );

	            // Update customer object to keep data in sync.
	            $customer = new WC_Customer( $user->ID );

	            if ( $customer ) {
	                // Keep billing data in sync if data changed.
	                if ( is_email( $user->user_email ) && $current_email !== $user->user_email ) {
	                    $customer->set_billing_email( $user->user_email );
	                }

	                if ( $current_first_name !== $user->first_name ) {
	                    $customer->set_billing_first_name( $user->first_name );
	                }

	                if ( $current_last_name !== $user->last_name ) {
	                    $customer->set_billing_last_name( $user->last_name );
	                }

	                $customer->save();
	            }

	            do_action( 'woocommerce_save_account_details', $user->ID );

	            awcs_display_notice( 'Account details changed successfully.' );

	            wc_get_template(
	                'myaccount/dashboard.php',
	                array(
	                    'current_user' => get_user_by( 'id', get_current_user_id() ),
	                )
	            );

	            wp_die();
	        }

	    }

		/**
	     * Process the login form.
	     *
	     * @throws Exception On login error.
	     */
	    public static function awcs_process_login() {
	        
	        $nonce_value = wc_get_var( sanitize_text_field($_POST['woocommerce_login_nonce']), '' );

	        if ( isset( $_POST['login'], $_POST['username'], $_POST['password'] ) && wp_verify_nonce( $nonce_value, 'woocommerce-login' ) ) {

	            try {
	                $creds = array(
	                    'user_login'    => trim( wp_unslash( sanitize_text_field($_POST['username']) ) ),
	                    'user_password' => sanitize_text_field($_POST['password']), 
	                    'remember'      => isset( $_POST['rememberme'] ) ? sanitize_text_field($_POST['rememberme']) : '',
	                );

	                $validation_error = new WP_Error();
	                $validation_error = apply_filters( 'woocommerce_process_login_errors', $validation_error, $creds['user_login'], $creds['user_password'] );

	                if ( $validation_error->get_error_code() ) {
	                    throw new Exception( '<strong>' . __( 'Error:', 'woocommerce' ) . '</strong> ' . $validation_error->get_error_message() );
	                }

	                if ( empty( $creds['user_login'] ) ) {
	                    throw new Exception( '<strong>' . __( 'Error:', 'woocommerce' ) . '</strong> ' . __( 'Username is required.', 'woocommerce' ) );
	                }

	                // On multisite, ensure user exists on current site, if not add them before allowing login.
	                if ( is_multisite() ) {
	                    $user_data = get_user_by( is_email( $creds['user_login'] ) ? 'email' : 'login', $creds['user_login'] );

	                    if ( $user_data && ! is_user_member_of_blog( $user_data->ID, get_current_blog_id() ) ) {
	                        add_user_to_blog( get_current_blog_id(), $user_data->ID, 'customer' );
	                    }
	                }

	                // Perform the login.
	                $user = wp_signon( apply_filters( 'woocommerce_login_credentials', $creds ), is_ssl() );

	                if ( is_wp_error( $user ) ) {
	                    throw new Exception( $user->get_error_message() );
	                } else {

	                    if ( ! empty( $_POST['redirect'] ) ) {
	                        $redirect = wp_sanitize_redirect( $_POST['redirect']  );
	                    } elseif ( wc_get_raw_referer() ) {
	                        $redirect = wc_get_raw_referer();
	                    } else {
	                        $redirect = wc_get_page_permalink( 'myaccount' );
	                    }

	                    wp_redirect( wp_validate_redirect( apply_filters( 'woocommerce_login_redirect', remove_query_arg( 'wc_error', $redirect ), $user ), wc_get_page_permalink( 'myaccount' ) ) );
	                }
	            } catch ( Exception $e ) {
	                do_action( 'woocommerce_login_failed' );
	            }
	        }
	    }

		/**
	     * Process the registration form.
	     *
	     * @throws Exception On registration error.
	     */
	    public static function awcs_process_registration() {

	        $nonce_value = isset( $_POST['_wpnonce'] ) ? wp_unslash( sanitize_text_field($_POST['_wpnonce']) ) : '';
	        $nonce_value = isset( $_POST['woocommerce-register-nonce'] ) ? wp_unslash( sanitize_text_field($_POST['woocommerce-register-nonce']) ) : $nonce_value;
            
            $username = 'no' === get_option( 'woocommerce_registration_generate_username' ) && isset( $_POST['username'] ) ? wp_unslash( sanitize_text_field($_POST['username']) ) : '';

            $password = 'no' === get_option( 'woocommerce_registration_generate_password' ) && isset( $_POST['password'] ) ? sanitize_text_field($_POST['password']) : '';

            $email    = wp_unslash( sanitize_email($_POST['email']) );

            try {
                $validation_error  = new WP_Error();
                $validation_error  = apply_filters( 'woocommerce_process_registration_errors', $validation_error, $username, $password, $email );
                $validation_errors = $validation_error->get_error_messages();

                if ( 1 === count( $validation_errors ) ) {
                    throw new Exception( $validation_error->get_error_message() );
                } elseif ( $validation_errors ) {
                    
                    throw new Exception();
                }

                $new_customer = wc_create_new_customer( sanitize_email( $email ), wc_clean( $username ), $password );

                if ( is_wp_error( $new_customer ) ) {
                    throw new Exception( $new_customer->get_error_message() );
                }

                // Only redirect after a forced login - otherwise output a success notice.
                if ( apply_filters( 'woocommerce_registration_auth_new_customer', true, $new_customer ) ) {
                    wc_set_customer_auth_cookie( $new_customer );
                    if ( ! empty( $_POST['redirect'] ) ) {
                        $redirect = wp_sanitize_redirect( $_POST['redirect'] );
                    } elseif ( wc_get_raw_referer() ) {
                        $redirect = wc_get_raw_referer();
                    } else {
                        $redirect = wc_get_page_permalink( 'myaccount' );
                    }
               
                }
            } catch ( Exception $e ) {
                
            }
        
            awcs_display_notice( 'Registration Successfully Completed.' );
            $page = 'myaccount'; $title='My Account'; $id = 'awcs-wc-my-account'; $class = '';
            awcs_print_content_section( $page, $title, $id, $class );

	        wp_die();
	    }

	    /**
	    * process lost password form
	    */
	    public static function awcs_process_lost_password_form() {
	        Wc_Form_Handler::process_lost_password();
	    }

	    /**
	    * process order payment
	    */
	    public static function awcs_wc_process_payment() {

	        global $wp;

	        if ( isset( $_POST['woocommerce_pay'], $_POST['key'] ) ) {

	            $nonce_value = wc_get_var( sanitize_text_field($_REQUEST['woocommerce-pay-nonce']), wc_get_var( sanitize_text_field($_REQUEST['_wpnonce']), '' ) );

	            if ( ! wp_verify_nonce( $nonce_value, 'woocommerce-pay' ) ) {
	                return;
	            }

	            // Pay for existing order.
	            $order_key = sanitize_text_field( $_POST['key'] );
	            $order_id  = absint( $_POST['order_id'] );
	            $order     = wc_get_order( $order_id );	            

	            do_action( 'woocommerce_before_pay_action', $order );

	            WC()->customer->set_props(
	                array(
	                    'billing_country'  => $order->get_billing_country() ? $order->get_billing_country() : null,
	                    'billing_state'    => $order->get_billing_state() ? $order->get_billing_state() : null,
	                    'billing_postcode' => $order->get_billing_postcode() ? $order->get_billing_postcode() : null,
	                    'billing_city'     => $order->get_billing_city() ? $order->get_billing_city() : null,
	                )
	            );

	            WC()->customer->save();

	            if ( ! empty( $_POST['terms-field'] ) && empty( $_POST['terms'] ) ) {
	                
	                return;
	            }

	                // Update payment method.
	                if ( $order->needs_payment() ) {
	                    try {
	                        $payment_method_id = isset( $_POST['payment_method'] ) ? wc_clean( wp_unslash( sanitize_text_field($_POST['payment_method']) ) ) : false;

	                        if ( ! $payment_method_id ) {
	                            throw new Exception( __( 'Invalid payment method.', 'woocommerce' ) );
	                        }

	                        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
	                        $payment_method     = isset( $available_gateways[ $payment_method_id ] ) ? $available_gateways[ $payment_method_id ] : false;

	                        if ( ! $payment_method ) {
	                            throw new Exception( __( 'Invalid payment method.', 'woocommerce' ) );
	                        }

	                        $order->set_payment_method( $payment_method );
	                        $order->save();

	                        $payment_method->validate_fields();

	                        if ( 0 === wc_notice_count( 'error' ) ) {

	                            $result = $payment_method->process_payment( $order_id );

	                            // Redirect to success/confirmation/payment page.
	                            if ( isset( $result['result'] ) && 'success' === $result['result'] ) {

	                                $result = apply_filters( 'woocommerce_payment_successful_result', $result, $order_id );

	                                add_filter( 'woocommerce_order_item_name', 'awcs_order_item_name' , 11, 3);

	                                ?>

	                                <article id="awcs-wc-order-confirmation" class="page type-page status-publish hentry">
	                                        <header class="entry-header">
	                                            <h1 class="entry-title"><?php _e( 'Order received', 'ajaxify-woocommerce-shopping' ); ?></h1>
	                                        </header>
	                                        <div class="entry-content">
	                                            <?php order_received( $order_id ); ?>
	                                  		</div>
	                                </article>

	                                <?php
	                               
	                            }
	                        
	                        }

	                    } catch ( Exception $e ) {
	                       
	                    }
	                } else {
	                    // No payment was required for order.
	                    $order->payment_complete();
	                   
	                }
	               
	        }

	        wp_die();
	    }

	}

}

Awcs_Form_Handler::init();