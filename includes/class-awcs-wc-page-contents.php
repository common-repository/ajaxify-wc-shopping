<?php
/**
 * AWCS WC Page Contents
 *
 * @package  AjaxifyWoocommerceShopping
 * @version  1.1.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists('Awcs_WC_Page_Content') ) {
		
	/**
	* Page Content Class
	*/
	class Awcs_WC_Page_Content {

		/**
		* Initialize events
		*/
		public static function init() {

			$awcs_events = array(
				'awcs_wc_view_shop',
				'awcs_wc_view_cart',
				'awcs_verify_checkout_content',
				'awcs_wc_view_checkout',
				'awcs_wc_view_account',
				'awcs_wc_single_product'
			);

			foreach ( $awcs_events as $ajax_event ) {
				add_action( 'wp_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
				add_action( 'wp_ajax_nopriv_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}

			add_action( 'pre_get_posts', array( __CLASS__, 'awcs_pre_get_posts_query' ) );

		}

		/**
		*  Filter for shop page when products first load
		*/
		public static function awcs_pre_get_posts_query( $q ) {

			if ( ! $q->is_main_query() ) return;

			$awcs_general_settings = Awcs_Retrieve_General_Settings::getInstance();

			$categories = $awcs_general_settings->awcs_get_product_categories();

			$products = $awcs_general_settings->awcs_get_excluded_products();

			$paginate = $awcs_general_settings->awcs_get_product_display_option();

			$limit = $awcs_general_settings->awcs_limit_numberof_products();

			$orderby = $awcs_general_settings->awcs_get_products_sortby();

			$no_pagination = !empty( $paginate ) && $paginate == "limit_product" ? true : false;

			if ( ! is_admin() && is_shop() ) {

				$query_args = array();

				$categories = implode( ',' , $categories );

				if ( ! empty( $categories ) ) {
					$categories = array_map( 'sanitize_title', explode( ',', $categories ) );
					$field      = 'slug';

					if ( is_numeric( $categories[0] ) ) {
						$field      = 'term_id';
						$categories = array_map( 'absint', $categories );
						// Check numeric slugs.
						foreach ( $categories as $cat ) {
							$the_cat = get_term_by( 'slug', $cat, 'product_cat' );
							if ( false !== $the_cat ) {
								$categories[] = $the_cat->term_id;
							}
						}
					}

					$query_args['tax_query'][] = array(
						'taxonomy'         => 'product_cat',
						'terms'            => $categories,
						'field'            => $field,
						'operator'         => 'IN'
					);
				}

				$products = implode( ',' , $products );

				if( ! empty( $products ) ) {

					$products = array_map( 'sanitize_title', explode( ',', $products ) );

					if ( is_numeric( $products[0] ) ) {
						$products = array_map( 'absint', $products );
					}

					$query_args['post__not_in'] = $products;
				}

				if( !empty($query_args['tax_query']) ) {

					$q->set( 
						'tax_query', $query_args['tax_query']
					);

				}

				if( !empty($query_args['post__not_in']) ) {

					$q->set( 
						'post__not_in', $query_args['post__not_in']
					);

				}

				if( $no_pagination ) {
					remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
					remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
					remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination', 10 );
				}

				if( !empty($limit) ) {

					$q->set(
						'posts_per_page', $limit
					);
				}

				if( !empty($orderby) ) {

					$q->set(
						'orderby', $orderby
					);
				}

			}

			if( ! is_admin() && ( is_product_category() || is_product_tag() ) ) {
					
				if( $no_pagination ) {
					remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
					remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
					remove_action( 'woocommerce_after_shop_loop', 'woocommerce_pagination', 10 );
				}

				if( !empty($limit) ) {

					$q->set(
						'posts_per_page', $limit
					);
				}

				if( !empty($orderby) ) {

					$q->set(
						'orderby', $orderby
					);
				}

			}

			remove_action( 'pre_get_posts', 'awcs_pre_get_posts_query' );
		}

		/**
		*  Display shop page contents
		*/
		public static function awcs_wc_view_shop() {

			$awcs_general_settings = Awcs_Retrieve_General_Settings::getInstance();

			$paginate = $awcs_general_settings->awcs_get_product_display_option();

			$paginate = !empty( $paginate ) && $paginate == "limit_product" ? false : true;

			$orderby = $awcs_general_settings->awcs_get_products_sortby();

		    $products_query = new Awcs_Products_Query( array( 'paginate' => $paginate, 'orderby' => $orderby ) );

			$products_query->awcs_get_products();
			
			wp_die();
		}

		/**
		*  Display cart contents
		*/
		public static function awcs_wc_view_cart() {

		    $page = 'cart'; $title = 'Cart'; $id = 'awcs-wc-cart'; $class = '';
		    awcs_print_content_section( $page, $title, $id, $class );
			wp_die();
		}

		/**
		*  Display checkout contents
		*/
		public static function awcs_wc_view_checkout() {

		    $page = 'checkout'; $title = 'Checkout'; $id = 'awcs-wc-checkout'; $class = '';
		    awcs_print_content_section( $page, $title, $id, $class );
			wp_die();
		}

		/**
		*  Updated checkout content
		*/
		public static function awcs_verify_checkout_content() {

			if ( WC()->cart->get_cart_contents_count() == 0 ) {

				$wc_pages = array(
					'checkout' => do_shortcode( '[woocommerce_cart]' ),
					'isCartEmpty' => true
				);
        		
			} else {

				$wc_pages = array(
					'checkout' => do_shortcode( '[woocommerce_checkout]' ),
					'isCartEmpty' => false
				);

			}

			echo wp_send_json( $wc_pages );

			wp_die();
		}

		/**
		*  Display my account contents
		*/
		public static function awcs_wc_view_account() {

			?>

			<style>

				.woocommerce-MyAccount-navigation ul {
					margin-left: 0px;
				}

				.woocommerce-MyAccount-navigation ul li a {
					text-decoration: none;
				    display: block;
				}

				ul.woocommerce-PaymentMethods li.woocommerce-PaymentMethod img {
					display: inline-block;
					width: 40px;
					padding-left: 3px;
					margin: 0;
				}

				.woocommerce-account .woocommerce-MyAccount-navigation {
					float: left;
				}

				.woocommerce-account .woocommerce-MyAccount-content {
					float: right;
				}

			</style>

			<?php
			
		    $page = 'myaccount'; $title = 'My Account'; $id = 'awcs-wc-my-account'; $class = '';
		    awcs_print_content_section( $page, $title, $id, $class );
			wp_die();
		}

		/**
		*  Display single product page
		*/
		public static function awcs_wc_single_product() {

			$general_settings = Awcs_Retrieve_General_Settings::getInstance();

			$override_single_product = $general_settings->awcs_override_single_product_template();

		    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : '';
		    $product_name = isset($_POST['product_name']) ? sanitize_text_field($_POST['product_name']) : '';
		    
			if ( intval($product_id) ) {

		        wp( 'p=' . $product_id . '&post_type=product' );

		        /**
				 * awcs_before_main_content hook.
				 *
				 * @hooked awcs_output_content_wrapper - 10 (outputs opening divs for the content)
				 * @hooked awcs_breadcrumb - 20
				 */
				do_action( 'awcs_before_main_content' );

				$single_product_wrapper = '<div class="awcs_wc_single_product_wrapper">';

				$allowed_html = array(
				    'div' => array(
				        'class' => array()
				    )
				);

				echo wp_kses( $single_product_wrapper, $allowed_html );

		        while ( have_posts() ) :

		            the_post();

		            if( $override_single_product ) {

		            	$product_url = ( isset( $_POST['product_url'] ) && !empty( $_POST['product_url'] ) ) ? esc_url($_POST['product_url']) : '';

		            	awcs_override_single_product( $product_id, $product_url );

		            	?>

		            	<div class="awcs_product_meta product_meta" style="visibility: hidden; height: 0px;">

		            		<div class="posted_in"><a href="#"></a></div>

		            		<div class="tagged_as"><a href="#"></a></div>

		            		<div class="woocommerce-grouped-product-list-item" id=""><a href="#"></a></div>

		            	</div>

		            	<?php
		            	
		            } else {

			            wc_get_template_part( 'content', 'single-product' );
			            
			        }

			        global $product;

			        $product_type = $product->get_type();

			        $product_type_field = '<input type="hidden" class="awcs_single_product_type" value="'. esc_attr($product_type) .'" />';

			        $allowed_html = array(
				        'input' => array(
				            'class' => array(),
				            'type' => array(),
				            'value' => array()
				        )
				    );

				    echo wp_kses( $product_type_field, $allowed_html );

		        endwhile;

		        wp_reset_postdata();

		        $product_info_fields = '<input type="hidden" class="awcs_single_product_name" value="'. esc_attr($product_name) .'" />';

		        $product_info_fields .= '<input type="hidden" class="awcs_single_product_id" value="'. esc_attr($product_id) .'" />';

		        $product_info_fields .= '</div>';

		        $allowed_html = array(
				    'input' => array(
				        'class' => array(),
				        'type' => array(),
				        'value' => array()
				    ),
				    'div' => array()
				);

				echo wp_kses( $product_info_fields, $allowed_html );

		        /**
				 * awcs_after_main_content hook.
				 *
				 * @hooked awcs_output_content_wrapper_end - 10 (outputs closing divs for the content)
				 */
				do_action( 'awcs_after_main_content' );

		    }

		    wp_die();
		}

	}

}

Awcs_WC_Page_Content::init();