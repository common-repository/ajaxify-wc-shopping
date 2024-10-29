<?php
/**
 * AWCS Find Products
 *
 * @package  AjaxifyWoocommerceShopping
 * @version  1.1.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists('Awcs_Find_Products') ) {
		
	/**
	*  Find Products Class.
	*/
	class Awcs_Find_Products {

		/**
		*  Initialize ajax events.
		*/
		public static function init() {

			$awcs_events = array(
				'awcs_wc_paged_products',
				'awcs_wc_sorted_products',
				'awcs_wc_categorized_products',
				'awcs_wc_tagged_products',
				'awcs_wc_view_numberof_products',
				'awcs_get_breadcrumb_with_page',
				'awcs_show_result_count'
			);

			foreach ( $awcs_events as $ajax_event ) {
				add_action( 'wp_ajax_' . $ajax_event, array( __CLASS__, $ajax_event ) );
				add_action( 'wp_ajax_nopriv_' . $ajax_event, array( __CLASS__, $ajax_event ) );
			}

		}

		/**
		*  Display products archieve on paging.
		*/
		public static function awcs_wc_paged_products() {

			add_filter( 'awcs_products_query', 'wc_setup_paging', 11, 2 );

		    $products_query = new Awcs_Products_Query( array( 'paginate' => false ) );
			$products_query->awcs_get_products();

		    wp_die();
		}

		/**
		*  Display products archieve on catalog sorting.
		*/
		public static function awcs_wc_sorted_products() {

			$orderby = isset($_POST['orderby']) && !empty($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : '';

		    $products_query = new Awcs_Products_Query( array( 'paginate' => false, 'orderby' => $orderby ) );
			$products_query->awcs_get_products();

		    wp_die();
		}

		/**
		*  Display products archieve of a catagory.
		*/
		public static function awcs_wc_categorized_products() {

			$category = isset( $_POST['category'] ) ? strtolower( sanitize_text_field( $_POST['category'] ) ) : '';

			$awcs_general_settings = Awcs_Retrieve_General_Settings::getInstance();
			$paginate = $awcs_general_settings->awcs_get_product_display_option();
			$paginate = !empty( $paginate ) && $paginate == "limit_product" ? false : true;

			$products_query = new Awcs_Products_Query( array( 'paginate' => $paginate, 'category' => $category ) );
			$products_query->awcs_get_products();

			wp_die();
		}

		/**
		*  Display products archieve of a tag.
		*/
		public static function awcs_wc_tagged_products() {

			$tag = isset( $_POST['tag'] ) ? strtolower( sanitize_text_field( $_POST['tag'] ) ) : '';

			$awcs_general_settings = Awcs_Retrieve_General_Settings::getInstance();
			$paginate = $awcs_general_settings->awcs_get_product_display_option();
			$paginate = !empty( $paginate ) && $paginate == "limit_product" ? false : true;

			$products_query = new Awcs_Products_Query( array( 'paginate' => $paginate, 'tag' => $tag ) );
			$products_query->awcs_get_products();

			wp_die();
		}

		/**
		*  Display certain number of products
		*/
		public static function awcs_wc_view_numberof_products() {

			$limit = isset( $_POST['limit'] ) && intval( $_POST['limit'] ) > 0 ? intval( $_POST['limit'] ) : -1;

			$category = isset( $_POST['category'] ) ? strtolower( sanitize_text_field( $_POST['category'] ) ) : '';
			$tag = isset( $_POST['tag'] ) ? strtolower( sanitize_text_field( $_POST['tag'] ) ) : '';

			if( !empty($category) ) {
				$products_query = new Awcs_Products_Query( array( 'paginate' => false, 'limit' => $limit, 'category' => $category ) );
			} elseif( !empty($tag) ) {
				$products_query = new Awcs_Products_Query( array( 'paginate' => false, 'limit' => $limit, 'tag' => $tag ) );
			} else {
				$products_query = new Awcs_Products_Query( array( 'paginate' => false, 'limit' => $limit ) );
			}

			$products_query->awcs_get_products();

			wp_die();
		}

		/**
		*  Return number of products per page
		*/
		public static function per_page() {

			$awcs_general_settings = Awcs_Retrieve_General_Settings::getInstance();

			$limit = $awcs_general_settings->awcs_limit_numberof_products();

			if( empty( $limit ) ) {
				$limit = apply_filters( 'loop_shop_per_page', wc_get_default_products_per_row() * wc_get_default_product_rows_per_page() );
			} 
			
			return apply_filters( 'awcs_loop_shop_per_page', $limit );
		}

		/**
		*  Update breadcrumb with page number
		*/
		public static function awcs_get_breadcrumb_with_page() {

			$category = isset( $_POST['category'] ) ? sanitize_text_field( $_POST['category'] ) : '';
			$tag = isset( $_POST['tag'] ) ? sanitize_text_field( $_POST['tag'] ) : '';
			$page = isset( $_POST['page'] ) ? sanitize_text_field( $_POST['page'] ) : '';

			awcs_wc_breadcrumb( array() );

			wp_die();
		}

		/**
		*  Update result count text via ajax
		*/
		public static function awcs_show_result_count() {

			$category = isset( $_POST['category'] ) ? sanitize_text_field( $_POST['category'] ) : '';
			$tag = isset( $_POST['tag'] ) ? sanitize_text_field( $_POST['tag'] ) : '';

			$current = isset($_POST['page']) ? sanitize_text_field( $_POST['page'] ) : 1;
			$per_page = self::per_page();

			if ( !empty($category) ) {

				$query_args = array(
			        'post_type'   => 'product',
			        'post_status' => 'publish',
			        'orderby'     => 'menu_order title',
			        'product_cat' => $category,
			        'no_found_rows' => true,
			        'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
			        'tax_query'   => array( array(
					    'taxonomy'  => 'product_visibility',
					    'terms'     => array( 'exclude-from-catalog' ),
					    'field'     => 'name',
					    'operator'  => 'NOT IN',
					 ) ),
			        'posts_per_page' => -1,
			        'fields' => 'ids'
			    );

			} elseif ( !empty($tag) ) {

				$query_args = array(
			        'post_type'   => 'product',
			        'post_status' => 'publish',
			        'orderby'     => 'menu_order title',
			        'product_tag' => $tag,
			        'no_found_rows' => true,
			        'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
			        'tax_query'   => array( array(
					    'taxonomy'  => 'product_visibility',
					    'terms'     => array( 'exclude-from-catalog' ),
					    'field'     => 'name',
					    'operator'  => 'NOT IN',
					 ) ),
			        'posts_per_page' => -1,
			        'fields' => 'ids'
			    );

			} else {

				$query_args = array(
			        'post_type'   => 'product',
			        'post_status' => 'publish',
			        'orderby'     => 'menu_order title',
			        'no_found_rows' => true,
			        'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
			        'tax_query'   => array( array(
					    'taxonomy'  => 'product_visibility',
					    'terms'     => array( 'exclude-from-catalog' ),
					    'field'     => 'name',
					    'operator'  => 'NOT IN',
					 ) ),
			        'posts_per_page' => -1,
			        'fields' => 'ids'
			    );

				$awcs_general_settings = Awcs_Retrieve_General_Settings::getInstance();

				$categories = $awcs_general_settings->awcs_get_product_categories();

				$products = $awcs_general_settings->awcs_get_excluded_products();

				if( !empty($categories) ) {

					$categories = array_map( 'sanitize_title', $categories );
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

				if( !empty( $products ) ) {

					$products = array_map( 'sanitize_title', $products );

					if ( is_numeric( $products[0] ) ) {
						$products = array_map( 'absint', $products );
					}

					$query_args['post__not_in'] = $products;
					
				}

			}

			$query = new WP_Query( $query_args );

			$paginated = ! $query->get( 'no_found_rows' );

			$results = (object) array(
				'ids'          => wp_parse_id_list( $query->posts ),
				'total'        => $paginated ? (int) $query->found_posts : count( $query->posts )
			);

			$total = $results->total;

			?>
			<p class="woocommerce-result-count">
				<?php

				if ( 1 === intval( $total ) ) {
					_e( 'Showing the single result', 'woocommerce' );
				} elseif ( $total <= $per_page || -1 === $per_page ) {
					/* translators: %d: total results */
					printf( _n( 'Showing all %d result', 'Showing all %d results', $total, 'woocommerce' ), $total );
				} else {
					$first = ( $per_page * $current ) - $per_page + 1;
					$last  = min( $total, $per_page * $current );
					/* translators: 1: first result 2: last result 3: total results */
					printf( _nx( 'Showing %1$d&ndash;%2$d of %3$d result', 'Showing %1$d&ndash;%2$d of %3$d results', $total, 'with first and last result', 'woocommerce' ), $first, $last, $total );
				}
				
				?>
			</p>

			<?php

			wp_reset_postdata();

			wp_die();
		}

	}

}

Awcs_Find_Products::init();