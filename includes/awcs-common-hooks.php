<?php
/**
 * AWCS Common Hooks
 *
 * @package  AjaxifyWoocommerceShopping
 * @version  1.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Content Wrappers
 *
 * @see awcs_output_content_wrapper()
 * @see awcs_output_content_wrapper_end()
 */
add_action( 'awcs_before_main_content', 'awcs_output_content_wrapper', 10 );
add_action( 'awcs_after_main_content', 'awcs_output_content_wrapper_end', 10 );

/**
 * Breadcrumbs
 *
 * @see awcs_wc_breadcrumb()
 */
add_action( 'awcs_before_main_content', 'awcs_wc_breadcrumb', 20 );

/**
*  Add product-id attribute to download item
*/
add_action( 'woocommerce_account_downloads_column_download-product', 'awcs_account_downloads_column', 10, 1 );

/**
*  Add product-id field @ order item meta end
*/
add_action( 'woocommerce_order_item_meta_end', 'awcs_order_item_meta_product_id', 20, 4 );

/**
* Add product id field
*/
add_action( 'woocommerce_before_shop_loop_item_title', 'awcs_add_data_product_id' );

/**
*  Template redirect
*/
add_filter( 'template_include', 'awcs_template_redirect', PHP_INT_MAX );

/**
*  Override single product
*/
add_action( 'awcs_single_product_content', 'awcs_single_product_content', 10, 1 );

/**
* Show / hide admin bar
*/
add_filter( 'show_admin_bar' , 'awcs_show_hide_admin_bar' );

/**
*  Adding product-id attribute to order item product link
*/
add_filter( 'woocommerce_order_item_name', 'awcs_order_item_name' , 20, 3 );

/**
*  Default product order by
*/
add_filter( 'woocommerce_default_catalog_orderby', 'awcs_default_catalog_orderby' );

/**
 * Products Loop
 *
 * @see woocommerce_result_count()
 * @see woocommerce_catalog_ordering()
 */
add_action( 'awcs_before_shop_loop', 'woocommerce_result_count', 20 );
add_action( 'awcs_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

/**
 * Notices
 */
add_action( 'awcs_before_shop_loop', 'woocommerce_output_all_notices', 10 );

/**
 * Single product template override hooks
 */

/**
 * Product Summary Box.
 *
 * @see woocommerce_template_single_title()
 * @see woocommerce_template_single_rating()
 * @see woocommerce_template_single_price()
 * @see woocommerce_template_single_excerpt()
 * @see woocommerce_template_single_meta()
 * @see woocommerce_template_single_sharing()
 * @see woocommerce_template_single_add_to_cart()
 */
add_action( 'awcs_single_product_summary', 'woocommerce_template_single_title', 5 );
add_action( 'awcs_single_product_summary', 'woocommerce_template_single_rating', 10 );
add_action( 'awcs_single_product_summary', 'woocommerce_template_single_price', 10 );
add_action( 'awcs_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
add_action( 'awcs_single_product_summary', 'woocommerce_template_single_meta', 40 );
add_action( 'awcs_single_product_summary', 'woocommerce_template_single_sharing', 50 );
add_action( 'awcs_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );

/**
 * After Single Products Summary Div.
 *
 * @see woocommerce_output_product_data_tabs()
 * @see woocommerce_upsell_display()
 * @see woocommerce_output_related_products()
 */
add_action( 'awcs_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
add_action( 'awcs_after_single_product_summary', 'woocommerce_upsell_display', 15 );
add_action( 'awcs_after_single_product_summary', 'woocommerce_output_related_products', 20 );