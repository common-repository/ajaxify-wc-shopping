<?php
/**
 * The template for displaying single product content
 *
 * @package AjaxifyWoocommerceShopping
 * @version 1.1.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

/**
 * Hook: woocommerce_before_single_product.
 *
 * @hooked woocommerce_output_all_notices - 10
 */
do_action( 'woocommerce_before_single_product' );

if ( post_password_required() ) {
	echo get_the_password_form(); // WPCS: XSS ok.
	return;
}
?>
<div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>

	<?php
	/**
	 * Hook: awcs_single_product_content.
	 *
	 * @hooked awcs_single_product_content - 10
	 */
	do_action( 'awcs_single_product_content', $product_url );

	/**
	 * Hook: awcs_after_single_product_summary.
	 *
	 * @hooked woocommerce_output_product_data_tabs - 10
	 * @hooked woocommerce_upsell_display - 15
	 * @hooked woocommerce_output_related_products - 20
	 */
	do_action( 'awcs_after_single_product_summary' );
	?>
	
</div>

<?php do_action( 'awcs_after_single_product' ); ?>