<?php
/**
 * AWCS My Account Events
 *
 * @package  AjaxifyWoocommerceShopping
 * @version  1.1.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists('Awcs_WC_My_Account') ) {
		
	/**
	 * My account class
	 */
	class Awcs_WC_My_Account {

		/**
		* Initialize events
		*/
		public static function init() {

			$awcs_events = array(
			    'awcs_my_account_orders',
			    'awcs_my_account_view_order',
			    'awcs_order_again_view_cart',
			    'awcs_wc_order_pay',
			    'awcs_my_account_dashboard',
			    'awcs_my_account_downloads',
			    'awcs_my_account_edit_address',
			    'awcs_my_account_edit_address_screen',
			    'awcs_my_account_payment_methods',
			    'awcs_my_account_account_details',
			    'awcs_lost_password_form',
			    'awcs_confirmation_lost_password_email_sent'
			);

			foreach ( $awcs_events as $ajax_event ) {
				add_action( 'wp_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
				add_action( 'wp_ajax_nopriv_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}

		}

		/**
		*  Account dashboard
		*/
		public static function awcs_my_account_dashboard() {

		    wc_get_template(
		        'myaccount/dashboard.php',
		        array(
		            'current_user' => get_user_by( 'id', get_current_user_id() ),
		        )
		    );

		    wp_die();
		}

		/**
		*  Show orders list
		*/
		public static function awcs_my_account_orders() {

		    $current_page = isset( $_POST['current_page'] ) ? absint( $_POST['current_page'] ) : 1;

		    do_action( 'woocommerce_account_orders_endpoint', $current_page );

		    wp_die();
		}

		/**
		*  View single order
		*/
		public static function awcs_my_account_view_order() {

		    $order_id = $_POST['order_id'] ? sanitize_text_field($_POST['order_id']) : '';

		    do_action( 'woocommerce_account_view-order_endpoint', $order_id );

		    wp_die();
		}

		/**
		*  Show cart filled with selected order items
		*/
		public static function awcs_order_again_view_cart() {
		    
		    awcs_display_notice( 'The cart has been filled with the items from your previous order.' );
		    $page = 'cart'; $title='Cart'; $id = 'awcs-wc-cart'; $class = '';
		    $isSinglePage = ( isset($_POST['isSinglePage']) && !empty($_POST['isSinglePage']) ) ? sanitize_text_field($_POST['isSinglePage']) : '';
		    awcs_print_content_section( $page, $title, $id, $class, $isSinglePage );
		    wp_die();
		}

		/**
		*  Downloads form
		*/
		public static function awcs_my_account_downloads() {

		    wc_get_template( 'myaccount/downloads.php' );

		    wp_die();
		}

		/**
		*  Edit address form action
		*/
		public static function awcs_my_account_edit_address() {

		    do_action( 'woocommerce_account_edit-address_endpoint' );

		    wp_die();
		}

		/**
		*  Show edit address form
		*/
		public static function awcs_my_account_edit_address_screen() {

		    $type = $_POST['endpoint'] ? sanitize_text_field($_POST['endpoint']) : 'billing';

		    do_action( 'woocommerce_account_edit-address_endpoint', $type );

		    wp_die();
		}

		/**
		*  Show payment methods
		*/
		public static function awcs_my_account_payment_methods() {

		    wc_get_template( 'myaccount/payment-methods.php' );

		    wp_die();
		}

		/**
		*  Show account details form
		*/
		public static function awcs_my_account_account_details() {

		    do_action( 'woocommerce_account_edit-account_endpoint' );

		    wp_die();
		}

		/**
		*  Show order pay form
		*/
		public static function awcs_wc_order_pay() {

		    do_action( 'before_woocommerce_pay' );

		    $order_id = absint( $_POST['order_id'] );

		    $order = wc_get_order( $order_id );

		    $available_gateways = WC()->payment_gateways->get_available_payment_gateways();

		    if ( count( $available_gateways ) ) {
		        current( $available_gateways )->set_current();
		    }

		    echo '<article id="awcs-wc-order-pay" class="page type-page status-publish hentry">
		            <header class="entry-header">
		                <h1 class="entry-title">Pay for order</h1>
		            </header>
		            <div class="entry-content"><div class="woocommerce">';

		    wc_get_template(
		        'checkout/form-pay.php',
		        array(
		            'order'              => $order,
		            'available_gateways' => $available_gateways,
		            'order_button_text'  => apply_filters( 'woocommerce_pay_order_button_text', __( 'Pay for order', 'woocommerce' ) ),
		        )
		    );

		    echo '</div></div></article>';

		    wp_die();
		}

		/**
		*  Show lost password form
		*/
		public static function awcs_lost_password_form() {
		    
		    $page = 'lost-password'; $title='Lost password'; $id = 'awcs-wc-lost-password'; $class = '';
		    awcs_print_content_section( $page, $title, $id, $class );
		    wp_die();
		}
		    
		/**
		*  Confirmation of lost password retrieve
		*/
		public static function awcs_confirmation_lost_password_email_sent() {
		    
		    $page = 'lost-password-confirmation'; $title='Lost password'; $id = 'awcs-wc-lost-password-confirmation'; $class = '';
		    awcs_print_content_section( $page, $title, $id, $class );
		    wp_die();
		}
    
	}

}

Awcs_WC_My_Account::init();