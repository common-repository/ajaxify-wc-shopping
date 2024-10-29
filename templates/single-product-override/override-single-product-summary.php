<?php
/**
 * The template for overriding single product image gallery and summary
 *
 * @package AjaxifyWoocommerceShopping
 * @version 1.1.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

$awcs_class_product_summary = 'awcs_product_type_' . $product->get_type();

?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class( '', $product ); ?>>

	<?php
	/**
	 * Hook: woocommerce_before_single_product_summary.
	 *
	 * @hooked woocommerce_show_product_sale_flash - 10
	 * @hooked woocommerce_show_product_images - 20
	 */
	do_action( 'woocommerce_before_single_product_summary' );
	?>

	<div class="summary entry-summary <?php echo esc_attr($awcs_class_product_summary); ?>">
		<?php
		/**
		 * Hook: awcs_single_product_summary.
		 *
		 * @hooked woocommerce_template_single_title - 5
		 * @hooked woocommerce_template_single_rating - 10
		 * @hooked woocommerce_template_single_price - 10
		 * @hooked woocommerce_template_single_excerpt - 20
		 * @hooked woocommerce_template_single_add_to_cart - 30
		 * @hooked woocommerce_template_single_meta - 40
		 * @hooked woocommerce_template_single_sharing - 50
		 * @hooked WC_Structured_Data::generate_product_data() - 60
		 */
		do_action( 'awcs_single_product_summary' );
		?>
	</div>

</div>