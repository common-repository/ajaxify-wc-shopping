<?php
/**
 * The template for displaying content only pages.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package  AjaxifyWoocommerceShopping
 * @since    1.1.0
 */

defined( 'ABSPATH' ) || exit;

?>

<!DOCTYPE html>

<html class="no-js" <?php language_attributes(); ?>>

	<head>

		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0" >

		<link rel="profile" href="https://gmpg.org/xfn/11">

		<?php wp_head(); ?>

	</head>

	<body <?php body_class(); ?>>

		<?php
			wp_body_open();
		?>

		<?php 

		if( is_product() ) {

			global $post;

			$product_id = $post->ID;

			global $product;

			$product = wc_get_product( $product_id );

			require_once(AWCS_PATH . 'templates/single-product-override/override-single-product-summary.php');

		} else if( is_add_payment_method_page() ) {

			do_action( 'woocommerce_account_content' );

		} else if( is_order_received_page() ) {

			echo '<div class="awcs_order_received_frame" style="visibility: hidden">';
			echo '<header class="woocommerce-products-header">
	                <h1 class="woocommerce-products-header__title page-title">' . __( 'Order received', 'ajaxify-woocommerce-shopping' ) . '</h1>
	              </header>';
            echo do_shortcode('[woocommerce_checkout]');
            echo '</div>';

		} else if( is_checkout() ) {

			$checkout_heading = 'Checkout';
	        
	        echo '<header class="woocommerce-products-header">
	                <h1 class="woocommerce-products-header__title page-title">' . __( $checkout_heading, 'ajaxify-woocommerce-shopping' ) . '</h1>
	              </header>';

			define('WOOCOMMERCE_CHECKOUT', true);
            echo do_shortcode('[woocommerce_checkout]');

		}

		?>

		<?php wp_footer(); ?>

	</body>
</html>