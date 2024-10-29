<?php
/**
 * Basic cart actions
 *
 * @package AjaxifyWoocommerceShopping
 * @version 1.1.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists('Awcs_WC_Cart_Action') ) {
		
	/**
	* Basic add to cart class
	*/
	class Awcs_WC_Cart_Action {

		/**
		* Initialize events
		*/
		public static function init() {

			$awcs_events = array(
				'awcs_wc_add_to_cart',
				'awcs_wc_update_cart',
				'awcs_wc_view_updated_cart',
			);

			foreach ( $awcs_events as $ajax_event ) {
				add_action( 'wp_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
				add_action( 'wp_ajax_nopriv_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}

		}

		/**
		*  Add to cart.
		*/
		public static function awcs_wc_add_to_cart() {

		    if ( ! isset( $_POST['product_id'] ) ) {
		        return;
		    }

		    $product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) );
		    $product           = wc_get_product( $product_id );
		    $quantity          = empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( intval($_POST['quantity']) ) );
		    $passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
		    $product_status    = get_post_status( $product_id );
		    $variation_id      = absint($_POST['variation_id']);
		    $variation         = isset( $_POST['variation'] ) ? $_POST['variation'] : array();

		    if ( $passed_validation && false !== WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation ) && 'publish' === $product_status ) {

		        do_action('woocommerce_ajax_added_to_cart', $product_id);

		        if ('yes' === get_option('woocommerce_cart_redirect_after_add')) {
		            wc_add_to_cart_message(array($product_id => $quantity), true);
		        }

		        WC_AJAX :: get_refreshed_fragments();

		    } else {

		        $data = array(
		            'error' => true,
		            'product_url' => apply_filters('woocommerce_cart_redirect_after_error', get_permalink($product_id), $product_id));

		        echo wp_send_json( esc_html($data) );
		    }

		    wp_die();
		}

		/**
		*  Apply coupon / remove coupon
		*  Update cart / Remove item from cart
		*  Undo remove item
		*/
		public static function awcs_wc_update_cart() {

		    $nonce_value = wc_get_var( $_REQUEST['woocommerce-cart-nonce'], wc_get_var( $_POST['wpnonce'], '' ) );
    
		    if ( ! empty( $_POST['awcs_apply_coupon'] ) && ! empty( $_POST['coupon_code'] ) && wp_verify_nonce ( $_POST['awcs_apply_coupon'], 'awcs_apply_coupon_nonce') ) {
		            WC()->cart->add_discount( wc_format_coupon_code( wp_unslash( sanitize_text_field($_POST['coupon_code']) ) ) );

		    } elseif ( isset( $_POST['awcs_remove_coupon'] ) && isset( $_POST['awcs_remove_coupon_nonce'] ) && wp_verify_nonce ( $_POST['awcs_remove_coupon_nonce'], 'awcs_remove_coupon_nonce') ) {
		            WC()->cart->remove_coupon( wc_format_coupon_code( urldecode( wp_unslash( sanitize_text_field($_POST['awcs_remove_coupon']) ) ) ) );
		            wc_add_notice( __( 'Coupon code removed successfully.', 'woocommerce' ), apply_filters( 'woocommerce_cart_updated_notice_type', 'success' ) );

		    } elseif ( ! empty( $_POST['remove_item'] ) && wp_verify_nonce( $nonce_value, 'woocommerce-cart' ) ) {

		            $cart_item_key = sanitize_text_field( $_POST['remove_item'] );
		            $cart_item     = WC()->cart->get_cart_item( $cart_item_key );

		            if ( $cart_item ) {
		                WC()->cart->remove_cart_item( $cart_item_key );

		                $product = wc_get_product( $cart_item['product_id'] );

		                /* translators: %s: Item name. */
		                $item_removed_title = apply_filters( 'woocommerce_cart_item_removed_title', $product ? sprintf( _x( '&ldquo;%s&rdquo;', 'Item name in quotes', 'woocommerce' ), $product->get_name() ) : __( 'Item', 'woocommerce' ), $cart_item );

		                // Don't show undo link if removed item is out of stock.
		                if ( $product && $product->is_in_stock() && $product->has_enough_stock( sanitize_text_field($cart_item['quantity']) ) ) {
		                    /* Translators: %s Product title. */
		                    $removed_notice  = sprintf( __( '%s removed.', 'woocommerce' ), $item_removed_title );
		                    $removed_notice .= ' <a href="' . esc_url( wc_get_cart_undo_url( $cart_item_key ) ) . '" class="restore-item">' . __( 'Undo?', 'woocommerce' ) . '</a>';
		                } else {
		                    /* Translators: %s Product title. */
		                    $removed_notice = sprintf( __( '%s removed.', 'woocommerce' ), $item_removed_title );
		                }

		                wc_add_notice( $removed_notice, apply_filters( 'woocommerce_cart_item_removed_notice_type', 'success' ) );
		            }            

		    } elseif ( ! empty( $_POST['undo_item'] ) && isset( $_POST['wpnonce'] ) && wp_verify_nonce( $nonce_value, 'woocommerce-cart' ) ) {

		            $cart_item_key = wp_unslash( sanitize_text_field($_POST['undo_item']) );

		            WC()->cart->restore_cart_item( $cart_item_key );

		    }

		    if ( ( ! empty( $_POST['awcs_apply_coupon'] ) || ! empty( $_POST['update_cart'] ) || ! empty( $_POST['awcs_proceed'] ) ) && ( wp_verify_nonce( $nonce_value, 'woocommerce-cart' ) || wp_verify_nonce( $nonce_value, 'woocommerce-process_checkout' ) ) ) {

		            $cart_updated = false;
		            $cart_totals  = isset( $_POST['cart'] ) ? wp_unslash( sanitize_text_field($_POST['cart']) ) : '';

		            if ( ! WC()->cart->is_empty() && is_array( $cart_totals ) ) {
		                foreach ( WC()->cart->get_cart() as $cart_item_key => $values ) {

		                    $_product = $values['data'];

		                    // Skip product if no updated quantity was posted.
		                    if ( ! isset( $cart_totals[ $cart_item_key ] ) || ! isset( $cart_totals[ $cart_item_key ]['qty'] ) ) {
		                        continue;
		                    }

		                    // Sanitize.
		                    $quantity = apply_filters( 'woocommerce_stock_amount_cart_item', wc_stock_amount( preg_replace( '/[^0-9\.]/', '', $cart_totals[ $cart_item_key ]['qty'] ) ), $cart_item_key );

		                    if ( '' === $quantity || $quantity === $values['quantity'] ) {
		                        continue;
		                    }

		                    // Update cart validation.
		                    $passed_validation = apply_filters( 'woocommerce_update_cart_validation', true, $cart_item_key, $values, $quantity );

		                    // is_sold_individually.
		                    if ( $_product->is_sold_individually() && $quantity > 1 ) {
		                        /* Translators: %s Product title. */
		                        wc_add_notice( sprintf( __( 'You can only have 1 %s in your cart.', 'woocommerce' ), $_product->get_name() ), 'error' );
		                        $passed_validation = false;
		                    }

		                    if ( $passed_validation ) {
		                        WC()->cart->set_quantity( $cart_item_key, $quantity, false );
		                        $cart_updated = true;
		                    }
		                }
		            }

		            // Trigger action - let 3rd parties update the cart if they need to and update the $cart_updated variable.
		            $cart_updated = apply_filters( 'woocommerce_update_cart_action_cart_updated', $cart_updated );

		            if ( $cart_updated ) {
		                WC()->cart->calculate_totals();
		            }

		    }

		    $isSinglePage = '';

		    if( isset($_POST['isSinglePage']) && !empty($_POST['isSinglePage']) ) {
		    	$isSinglePage = sanitize_text_field($_POST['isSinglePage']);
		    }

		    if (wp_verify_nonce( $nonce_value, 'woocommerce-process_checkout' )) {

		        add_action( 'woocommerce_review_order_before_submit', 'awcs_coupon_nonce' );

		        // Show checkout page after apply coupon / remove coupon from checkout page
		        $page = 'checkout'; $title = 'Checkout'; $id = 'awcs-wc-checkout'; $class = '';
		        awcs_print_content_section( $page, $title, $id, $class, $isSinglePage );

		    } else {

		        add_action( 'woocommerce_proceed_to_checkout', 'awcs_coupon_nonce' );

		        // Show cart page after apply coupon / remove coupon / remove cart item
		        $page = 'cart'; $title='Cart'; $id = 'awcs-wc-cart'; $class = '';
		        awcs_print_content_section( $page, $title, $id, $class, $isSinglePage );

		    }

		    wp_die();
		}

		/**
		*  View updated cart.
		*/
		public static function awcs_wc_view_updated_cart() {
    
		    wc_add_notice( __( 'Cart updated.', 'woocommerce' ), apply_filters( 'woocommerce_cart_updated_notice_type', 'success' ) );

		    $isSinglePage = '';

		    if( isset($_POST['isSinglePage']) && !empty($_POST['isSinglePage']) ) {
		    	$isSinglePage = sanitize_text_field($_POST['isSinglePage']);
		    }
		    
		    $page = 'cart'; $title='Cart'; $id = 'awcs-wc-cart'; $class = '';
		    awcs_print_content_section( $page, $title, $id, $class, $isSinglePage );
		    wp_die();
		}

	}

}

Awcs_WC_Cart_Action::init();