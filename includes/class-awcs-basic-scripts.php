<?php
/**
 * Handle basic frontend scripts
 *
 * @package AjaxifyWoocommerceShopping
 * @version 1.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if( ! class_exists('Awcs_Basic_Scripts') ) {

	/**
	 * Basic frontend scripts class.
	 */
	class Awcs_Basic_Scripts {

		/**
		 * Contains an array of registered script handles.
		 *
		 * @var array
		 */
		private static $scripts = array();

		/**
		 * Contains an array of registered style handles.
		 *
		 * @var array
		 */
		private static $styles = array();

		/**
		 * Contains an array of localized script handles.
		 *
		 * @var array
		 */
		private static $wp_localize_scripts = array();

		/**
		 * Hook in methods.
		 */
		public static function init() {
			add_action( 'wp_enqueue_scripts', array( __CLASS__, 'awcs_load_scripts' ) );
			add_action( 'wp_print_scripts', array( __CLASS__, 'awcs_localize_printed_scripts' ), 5 );
			add_action( 'wp_print_footer_scripts', array( __CLASS__, 'awcs_localize_printed_scripts' ), 5 );
		}

		/**
		 * Get styles for the frontend.
		 *
		 * @return array
		 */
		public static function get_styles() {
			
			$version = AWCS_VERSION;

			return apply_filters(
				'awcs_enqueue_styles',
				array(
					'awcs-frontend-style' => array(
						'src'     => self::get_asset_url( 'assets/css/awcs-style.min.css' ),
						'deps'    => '',
						'version' => $version,
						'media'   => 'all',
						'has_rtl' => false,
					),
				)
			);
		}

		/**
		 * Return asset URL.
		 *
		 * @param string $path Assets path.
		 * @return string
		 */
		private static function get_asset_url( $path ) {
			return apply_filters( 'awcs_get_asset_url', AWCS_URL . $path );
		}

		/**
		 * Register a script for use.
		 *
		 * @uses   wp_register_script()
		 * @param  string   $handle    Name of the script. Should be unique.
		 * @param  string   $path      Full URL of the script, or path of the script relative to the WordPress root directory.
		 * @param  string[] $deps      An array of registered script handles this script depends on.
		 * @param  string   $version   String specifying script version number.
		 * @param  boolean  $in_footer Whether to enqueue the script before </body> instead of in the <head>. Default 'false'.
		 */
		private static function register_script( $handle, $path, $deps = array( 'jquery' ), $version = AWCS_VERSION, $in_footer = true ) {
			self::$scripts[] = $handle;
			wp_register_script( $handle, $path, $deps, $version, $in_footer );
		}

		/**
		 * Register and enqueue a script for use.
		 *
		 * @uses   wp_enqueue_script()
		 * @param  string   $handle    Name of the script. Should be unique.
		 * @param  string   $path      Full URL of the script, or path of the script relative to the WordPress root directory.
		 * @param  string[] $deps      An array of registered script handles this script depends on.
		 * @param  string   $version   String specifying script version number.
		 * @param  boolean  $in_footer Whether to enqueue the script before </body> instead of in the <head>. Default 'false'.
		 */
		private static function enqueue_script( $handle, $path = '', $deps = array( 'jquery' ), $version = AWCS_VERSION, $in_footer = true ) {
			if ( ! in_array( $handle, self::$scripts, true ) && $path ) {
				self::register_script( $handle, $path, $deps, $version, $in_footer );
			}
			wp_enqueue_script( $handle );
		}

		/**
		 * Register a style for use.
		 *
		 * @uses   wp_register_style()
		 * @param  string   $handle  Name of the stylesheet. Should be unique.
		 * @param  string   $path    Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
		 * @param  string[] $deps    An array of registered stylesheet handles this stylesheet depends on.
		 * @param  string   $version String specifying stylesheet version number.
		 * @param  string   $media   The media for which this stylesheet has been defined. Accepts media types like 'all', 'print' and 'screen', or media queries like '(orientation: portrait)' and '(max-width: 640px)'.
		 * @param  boolean  $has_rtl If has RTL version to load too.
		 */
		private static function register_style( $handle, $path, $deps = array(), $version = AWCS_VERSION, $media = 'all', $has_rtl = false ) {
			self::$styles[] = $handle;
			wp_register_style( $handle, $path, $deps, $version, $media );

			if ( $has_rtl ) {
				wp_style_add_data( $handle, 'rtl', 'replace' );
			}
		}

		/**
		 * Register and enqueue a styles for use.
		 *
		 * @uses   wp_enqueue_style()
		 * @param  string   $handle  Name of the stylesheet. Should be unique.
		 * @param  string   $path    Full URL of the stylesheet, or path of the stylesheet relative to the WordPress root directory.
		 * @param  string[] $deps    An array of registered stylesheet handles this stylesheet depends on.
		 * @param  string   $version String specifying stylesheet version number.
		 * @param  string   $media   The media for which this stylesheet has been defined. Accepts media types like 'all', 'print' and 'screen', or media queries like '(orientation: portrait)' and '(max-width: 640px)'.
		 * @param  boolean  $has_rtl If has RTL version to load too.
		 */
		private static function enqueue_style( $handle, $path = '', $deps = array(), $version = AWCS_VERSION, $media = 'all', $has_rtl = false ) {
			if ( ! in_array( $handle, self::$styles, true ) && $path ) {
				self::register_style( $handle, $path, $deps, $version, $media, $has_rtl );
			}
			wp_enqueue_style( $handle );
		}

		/**
		 * Register all scripts.
		 */
		private static function register_scripts() {
			
			$version = AWCS_VERSION;

			$register_scripts = array(
				'awcs-jquery-validate-min'                 => array(
					'src'     => self::get_asset_url( 'assets/js/ext/jquery.validate.min.js' ),
					'deps'    => array( 'jquery' ),
					'version' => null,
				),
				'awcs-wc-global'                  => array(
					'src'     => self::get_asset_url( 'assets/js/awcs-common-functions.min.js' ),
					'deps'    => array('jquery'),
					'version' => null,
				),
				'awcs-manage-target-element'             => array(
					'src'     => self::get_asset_url( 'assets/js/awcs-manage-target-element.min.js' ),
					'deps'    => array( 'jquery' ),
					'version' => null,
				),
				'awcs-wc-basic-page-contents'                 => array(
					'src'     => self::get_asset_url( 'assets/js/awcs-basic-wc-page-contents.min.js' ),
					'deps'    => array( 'jquery' ),
					'version' => null,
				),
				'awcs-wc-basic-find-products'      => array(
					'src'     => self::get_asset_url( 'assets/js/awcs-basic-find-products.min.js' ),
					'deps'    => array( 'jquery' ),
					'version' => null,
				),
				'awcs-wc-basic-single-product'                => array(
					'src'     => self::get_asset_url( 'assets/js/awcs-basic-single-product.min.js' ),
					'deps'    => array( 'jquery' ),
					'version' => null,
				),
				'awcs-override-single-product'                => array(
					'src'     => self::get_asset_url( 'assets/js/awcs-override-single-product.min.js' ),
					'deps'    => array( 'jquery' ),
					'version' => null,
				),
				'awcs-wc-basic-cart-functions'           => array(
					'src'     => self::get_asset_url( 'assets/js/awcs-basic-cart-functions.min.js' ),
					'deps'    => array( 'jquery' ),
					'version' => null,
				),
				'awcs-wc-basic-my-account'                    => array(
					'src'     => self::get_asset_url( 'assets/js/awcs-basic-my-account.min.js' ),
					'deps'    => array( 'jquery' ),
					'version' => null,
				),
				'awcs-wc-add-to-cart-variation'             => array(
					'src'     => self::get_asset_url( 'woocommerce/js/awcs-wc-add-to-cart-variation.min.js' ),
					'deps'    => array( 'jquery', 'wp-util', 'jquery-blockui' ),
					'version' => null,
				),
				'awcs-wc-add-to-cart'   => array(
					'src'     => self::get_asset_url( 'assets/js/awcs-wc-add-to-cart.min.js' ),
					'deps'    => array( 'jquery' ),
					'version' => null,
				),
			);

			foreach ( $register_scripts as $name => $props ) {
				self::register_script( $name, $props['src'], $props['deps'], $props['version'] );
			}
		
		}

		/**
		 * Register/queue frontend scripts.
		 */
		public static function awcs_load_scripts() {

			global $post;

			if ( ! did_action( 'before_woocommerce_init' ) ) {
				return;
			}

			// CSS Styles.
			$enqueue_styles = self::get_styles();

			if ( $enqueue_styles ) {
				foreach ( $enqueue_styles as $handle => $args ) {
					if ( ! isset( $args['has_rtl'] ) ) {
						$args['has_rtl'] = false;
					}

					self::register_style( $handle, $args['src'], $args['deps'], $args['version'], $args['media'], $args['has_rtl'] );
				}
			}

			self::register_scripts();

			// Password strength meter. Load in checkout, account login and edit account page.
			if ( ( 'no' === get_option( 'woocommerce_registration_generate_password' ) && ! is_user_logged_in() ) || is_edit_account_page() || is_lost_password_page() ) {
					wp_enqueue_script( 'wc-password-strength-meter' );
			}

			if( !is_admin() ) {
				wp_enqueue_style( 'awcs-frontend-style' );
				wp_enqueue_script( 'wc-add-payment-method' );
				wp_enqueue_script( 'wc-lost-password' );
			}

			$general_settings = Awcs_Retrieve_General_Settings::getInstance();

			$override_single_product = $general_settings->awcs_override_single_product_template();

			if( $override_single_product )
				wp_enqueue_script( 'awcs-override-single-product' );
			
		}

		/**
		 * Localize a script once.
		 * @param string $handle Script handle the data will be attached to.
		 */
		private static function localize_script( $handle ) {
			if ( ! in_array( $handle, self::$wp_localize_scripts, true ) && wp_script_is( $handle ) ) {
				$data = self::awcs_get_script_data( $handle );

				if ( ! $data ) {
					return;
				}

				$name                        = str_replace( '-', '_', $handle ) . '_params';
				self::$wp_localize_scripts[] = $handle;
				wp_localize_script( $handle, $name, apply_filters( $name, $data ) );
			}
		}

		/**
		 * Return data for script handles.
		 *
		 * @param  string $handle Script handle the data will be attached to.
		 * @return array|bool
		 */
		private static function awcs_get_script_data( $handle ) {

			global $wp;

			$awcs_settings = Awcs_Retrieve_General_Settings::getInstance();

			switch ( $handle ) {

				case 'awcs-wc-basic-single-product':

					$general_settings = Awcs_Retrieve_General_Settings::getInstance();

					$override_single_product = $general_settings->awcs_override_single_product_template();

					$params = array(
						'override_single_product' => $override_single_product
					);

					break;

				case 'awcs-wc-global':

					$target_element = $awcs_settings->awcs_get_target_element();

					$single_product_selector = $awcs_settings->awcs_get_single_product_selector();

					$category_selector = $awcs_settings->awcs_get_category_selector();

					$tag_selector = $awcs_settings->awcs_get_tag_selector();
					
					$isPaymentGatewayAvailable = false;
					
					if ( WC()->payment_gateways->get_available_payment_gateways() ) {
						$isPaymentGatewayAvailable = true;
					}

					$params = array(
						'target_element' => $target_element,
						'single_product_selector' => $single_product_selector,
						'category_selector' => $category_selector,
						'tag_selector' => $tag_selector,
						'ajax_url' => admin_url( 'admin-ajax.php' ),
						'home_url' => strip_tags( stripslashes( filter_var( esc_url( home_url( '/' ) ), FILTER_VALIDATE_URL ) ) ),
						'page_title' => wp_get_document_title(),
						'shop_url' => strip_tags( stripslashes( filter_var( wc_get_page_permalink( 'shop' ), FILTER_VALIDATE_URL ) ) ),
						'cart_url' => strip_tags( stripslashes( filter_var( wc_get_cart_url(), FILTER_VALIDATE_URL ) ) ),
						'checkout_url' => strip_tags( stripslashes( filter_var( wc_get_checkout_url(), FILTER_VALIDATE_URL ) ) ),
						'account_url' => strip_tags( stripslashes( filter_var( wc_get_page_permalink( 'myaccount' ), FILTER_VALIDATE_URL ) ) ),
						'account_orders_url' => strip_tags( stripslashes( filter_var( esc_url( wc_get_endpoint_url( 'orders' ) ), FILTER_VALIDATE_URL ) ) ),
						'account_address_url' => strip_tags( stripslashes( filter_var( esc_url( wc_get_endpoint_url( 'edit-address' ) ), FILTER_VALIDATE_URL ) ) ),
						'account_edit_url' => strip_tags( stripslashes( filter_var( esc_url( wc_get_endpoint_url( 'edit-account' ) ), FILTER_VALIDATE_URL ) ) ),
						'account_billing_address' => strip_tags( stripslashes( filter_var( esc_url( wc_get_endpoint_url( 'edit-address', 'billing' ) ), FILTER_VALIDATE_URL ) ) ),
						'account_shipping_address' => strip_tags( stripslashes( filter_var( esc_url( wc_get_endpoint_url( 'edit-address', 'shipping' ) ), FILTER_VALIDATE_URL ) ) ),
						'isPaymentGatewayAvailable' => $isPaymentGatewayAvailable,
						'ajax_loading_img' => awcs_loading_icon_exists() ? null : '<img src="'. AWCS_URL . "assets/image/spinner.gif" .'" width="32" height="32" style="border: none;" alt="" />',
						'single_loading_img' => '<img src="'. AWCS_URL . "assets/image/spinner.gif" .'" width="32" height="32" style="border: none;" alt="" />',
					);
					break;

				case 'awcs-wc-basic-page-contents':

					$params = array(
						'ajax_url'                  => WC()->ajax_url(),
						'wc_ajax_url'               => WC_AJAX::get_endpoint( '%%endpoint%%' ),
						'update_order_review_nonce' => wp_create_nonce( 'update-order-review' ),
						'apply_coupon_nonce'        => wp_create_nonce( 'apply-coupon' ),
						'remove_coupon_nonce'       => wp_create_nonce( 'remove-coupon' ),
						'option_guest_checkout'     => get_option( 'woocommerce_enable_guest_checkout' ),
						'checkout_url'              => WC_AJAX::get_endpoint( 'checkout' ),
						'is_checkout'               => is_checkout() && empty( $wp->query_vars['order-pay'] ) && ! isset( $wp->query_vars['order-received'] ) ? 1 : 0,
						'i18n_checkout_error'       => esc_attr__( 'Error processing checkout. Please try again.', 'woocommerce' ),
					);
					break;

				case 'awcs-wc-add-to-cart-variation':
					
					wc_get_template( 'single-product/add-to-cart/variation.php' );
					$params = array(
						'wc_ajax_url' => WC_AJAX::get_endpoint( '%%endpoint%%' ),
						'i18n_no_matching_variations_text' => esc_attr__( 'Sorry, no products matched your selection. Please choose a different combination.', 'woocommerce' ),
						'i18n_make_a_selection_text' => esc_attr__( 'Please select some product options before adding this product to your cart.', 'woocommerce' ),
						'i18n_unavailable_text' => esc_attr__( 'Sorry, this product is unavailable. Please choose a different combination.', 'woocommerce' ),
					);
					break;
					
				default:
					$params = false;
			}

			return apply_filters( 'awcs_get_script_data', $params, $handle );
		}

		/**
		 * Localize scripts only when enqueued.
		 */
		public static function awcs_localize_printed_scripts() {
			foreach ( self::$scripts as $handle ) {
				self::localize_script( $handle );
			}
		}

	}

}

Awcs_Basic_Scripts::init();