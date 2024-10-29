<?php
/**
 * Products Query
 *
 * @package AjaxifyWoocommerceShopping
 * @version 1.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
* Products Query class.
*/
class Awcs_Products_Query {

	/**
	 * Products page title.
	 *
	 * @since 1.1.0
	 * @var   array
	 */
	protected $page_title = '';

	/**
	 * Settings.
	 *
	 * @since 1.1.0
	 * @var   array
	 */
	protected $settings = array();

	/**
	 * Products query args.
	 *
	 * @since 1.1.0
	 * @var   array
	 */
	protected $args = array();

	/**
	 * Initialize products query.
	 *
	 * @since 1.1.0
	 * @param array  $settings.
	 */
	public function __construct( $settings = array() ) {
		$this->page_title = $this->awcs_wc_page_title();
		$this->settings = array_merge( $this->awcs_init_query_settings(), $this->awcs_admin_general_settings() );
		$this->settings = array_merge( $this->settings, $settings );
		$this->args = $this->awcs_get_args();
	}

	/**
	 * Initialize query settings.
	 *
	 * @since  1.1.0
	 * @return array
	 */
	protected function awcs_init_query_settings() {

		$settings = array(
			'limit'          => '-1',
			'columns'        => '',
			'rows'           => '',
			'orderby'        => '',
			'order'          => '',
			'ids'            => '',  
			'skus'           => '',
			'category'       => '',  
			'cat_operator'   => 'IN',
			'exclude_products' => '',
			'attribute'      => '',
			'terms'          => '',
			'terms_operator' => 'IN',
			'tag'            => '',    
			'tag_operator'   => 'IN',     
			'visibility'     => 'visible',
			'class'          => '',
			'page'           => 1, 
			'paginate'       => false,
			'cache'          => true,
		);

		if ( ! absint( $settings['columns'] ) ) {
			$settings['columns'] = wc_get_default_products_per_row();
		}

		return $settings;
	}

	/**
	 * Retrieve admin general settings.
	 *
	 * @since  1.1.0
	 * @return array
	 */
	protected function awcs_admin_general_settings() {

		$awcs_general_settings = Awcs_Retrieve_General_Settings::getInstance();

		$categories = $awcs_general_settings->awcs_get_product_categories();

		$products = $awcs_general_settings->awcs_get_excluded_products();

		$paginate = $awcs_general_settings->awcs_get_product_display_option();

		$limit = $awcs_general_settings->awcs_limit_numberof_products();

		$orderby = $awcs_general_settings->awcs_get_products_sortby();

		$categories = implode( ',' , $categories );

		$products = implode( ',' , $products );

		if( empty( $_POST['category'] ) && empty( $_POST['tag'] ) ) {

			$settings = array(
				'category'         => $categories,
				'exclude_products' => $products,
				'paginate'       => $paginate == "paginate" ? true : false,
				'limit'          => $limit,
			);

		} else {

			$settings = array(
				'limit'          => $limit,
			);

		}	

		return $settings;
	}

	/**
	 * Get page title.
	 *
	 * @since 1.1.0
	 * @var   array
	 */
	protected function awcs_wc_page_title() {

		$page_title = woocommerce_page_title( false );

		if( isset($_POST['category']) && ! empty($_POST['category']) ) {
			$page_title = ucfirst( sanitize_text_field($_POST['category']) );
		}

		if( isset($_POST['tag']) && ! empty($_POST['tag']) ) {
			$page_title = ucfirst( sanitize_text_field($_POST['tag']) );
		}

		return $page_title;
	}

	/**
	 * Update query settings from HTTP request.
	 *
	 * @since  1.1.0
	 * @return array
	 */
	protected function awcs_update_query_settings() {

		if( empty( $this->settings['limit'] ) )
			$this->settings['limit'] = Awcs_Find_Products::per_page();

		if( isset($_POST['orderby']) && ! empty($_POST['orderby']) ) {
			$this->settings['orderby'] = sanitize_text_field($_POST['orderby']);
		}

		if( isset($_POST['order']) && ! empty($_POST['order']) ) {
			$this->settings['order'] = sanitize_text_field($_POST['order']);
		}

		if( isset($_POST['page']) && ! empty($_POST['page']) ) {
			$this->settings['page'] = sanitize_text_field($_POST['page']);
		}

		if( isset($_POST['attribute']) && ! empty($_POST['attribute']) ) {
			$this->settings['attribute'] = sanitize_text_field($_POST['attribute']);
		}

		if( isset($_POST['terms']) && ! empty($_POST['terms']) ) {
			$this->settings['terms'] = sanitize_text_field($_POST['terms']);
		}

		if( isset($_POST['category']) && ! empty($_POST['category']) ) {
			$this->settings['category'] = sanitize_text_field($_POST['category']);
		}

		if( isset($_POST['tag']) && ! empty($_POST['tag']) ) {
			$this->settings['tag'] = sanitize_text_field($_POST['tag']);
		}

		if( isset($_POST['exclude_products']) && ! empty($_POST['exclude_products']) ) {

			$exclude_products = array();

			if (is_array($_POST['exclude_products'])) {
	            foreach ($_POST['exclude_products'] as $product) {
	                array_push( $exclude_products, sanitize_text_field($product) );
	            }
	        } else {
	            array_push( $exclude_products, sanitize_text_field($_POST['exclude_products']) );
	        }

			$this->settings['exclude_products'] = $exclude_products;
		}

	}

	/**
	 * Get query args.
	 *
	 * @since  1.1.0
	 * @return array
	 */
	protected function awcs_get_args() {

		$this->awcs_update_query_settings();

		$query_args = array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'ignore_sticky_posts' => true,
			'update_post_meta_cache' => false,
			'update_post_term_cache' => false,
			'no_found_rows'       => false === wc_string_to_bool( $this->settings['paginate'] ),
			'orderby'             => empty( $_POST['orderby'] ) ? $this->settings['orderby'] : wc_clean( wp_unslash( sanitize_text_field($_POST['orderby']) ) ),
		);

		$orderby_value         = explode( '-', $query_args['orderby'] );
		$orderby               = esc_attr( $orderby_value[0] );
		$order                 = ! empty( $orderby_value[1] ) ? $orderby_value[1] : '';
		$query_args['orderby'] = $orderby;
		$query_args['order']   = $order;

		if ( wc_string_to_bool( $this->settings['paginate'] ) ) {
			$this->settings['page'] = absint( empty( $_POST['page'] ) ? 1 : sanitize_text_field($_POST['page']) );
		}

		if ( ! empty( $this->settings['rows'] ) ) {
			$this->settings['limit'] = $this->settings['columns'] * $this->settings['rows'];
		}

		$ordering_args         = WC()->query->get_catalog_ordering_args( $query_args['orderby'], $query_args['order'] );
		$query_args['orderby'] = $ordering_args['orderby'];
		$query_args['order']   = $ordering_args['order'];
		if ( $ordering_args['meta_key'] ) {
			$query_args['meta_key'] = $ordering_args['meta_key'];
		}

		$query_args['posts_per_page'] = intval( $this->settings['limit'] );

		if ( 1 < $this->settings['page'] ) {
			$query_args['paged'] = absint( $this->settings['page'] );
		}

		$query_args['meta_query'] = WC()->query->get_meta_query();

		$query_args['tax_query']  = array();

		// Taxonomies.
		$this->set_tax_query_args( $query_args );

		// Exclude products.
		$this->awcs_exclude_products( $query_args );

		$query_args = apply_filters( 'awcs_products_query', $query_args, $this->settings );

		// Always query only IDs.
		$query_args['fields'] = 'ids';

		return $query_args;
	}

	/**
	 * Return query results.
	 *
	 * @since  1.1.0
	 * @return array
	 */
	protected function awcs_query_results() {

		$transient_name = 'awcs_product_loop_' . md5( wp_json_encode( $this->args ) );

		if ( 'rand' === $this->args['orderby'] ) {
			$rand_index      = wp_rand( 0, max( 1, absint( apply_filters( 'woocommerce_product_query_max_rand_cache_count', 5 ) ) ) );
			$transient_name .= $rand_index;
		}

		$transient_version = WC_Cache_Helper::get_transient_version( 'product_query' );
		$cache             = wc_string_to_bool( $this->settings['cache'] ) === true;
		$transient_value   = $cache ? get_transient( $transient_name ) : false;

		if ( isset( $transient_value['value'], $transient_value['version'] ) && $transient_value['version'] === $transient_version ) {
			$results = $transient_value['value'];
		} else {
			$query = new WP_Query( $this->args );

			$paginated = ! $query->get( 'no_found_rows' );

			$results = (object) array(
				'ids'          => wp_parse_id_list( $query->posts ),
				'total'        => $paginated ? (int) $query->found_posts : count( $query->posts ),
				'total_pages'  => $paginated ? (int) $query->max_num_pages : 1,
				'per_page'     => (int) $query->get( 'posts_per_page' ),
				'current_page' => $paginated ? (int) max( 1, $query->get( 'paged', 1 ) ) : 1,
			);

			if ( $cache ) {
				$transient_value = array(
					'version' => $transient_version,
					'value'   => $results,
				);
				set_transient( $transient_name, $transient_value, DAY_IN_SECONDS * 30 );
			}
		}

		// Remove ordering query arguments which may have been added by get_catalog_ordering_args.
		WC()->query->remove_ordering_args();

		return apply_filters( 'awcs_products_query_results', $results, $this );
	}

	/**
	 * Get Products.
	 *
	 * @since  1.1.0
	 * @return array
	 */
	public function awcs_get_products() {
		
		$columns  = absint( $this->settings['columns'] );
		$products = $this->awcs_query_results();

		if ( $products && $products->ids ) {
			// Prime caches to reduce future queries.
			if ( is_callable( '_prime_post_caches' ) ) {
				_prime_post_caches( $products->ids );
			}

			// Setup the loop.
			wc_setup_loop(
				array(
					'columns'      => $columns,
					'name'         => 'products',
					'is_search'    => false,
					'is_paginated' => wc_string_to_bool( $this->settings['paginate'] ),
					'total'        => $products->total,
					'total_pages'  => $products->total_pages,
					'per_page'     => $products->per_page,
					'current_page' => $products->current_page,
				)
			);

			$original_post = $GLOBALS['post'];

			/**
			 * Hook: awcs_before_main_content.
			 *
			 * @hooked awcs_output_content_wrapper - 10 (outputs opening divs for the content)
			 * @hooked awcs_wc_breadcrumb - 20
			 * @hooked WC_Structured_Data::generate_website_data() - 30
			 */
			do_action( 'awcs_before_main_content' );

			?>
			<header class="woocommerce-products-header">
				<?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>
					<h1 class="woocommerce-products-header__title page-title"><?php _e( $this->page_title, 'ajaxify-woocommerce-shopping'); ?></h1>
				<?php endif; ?>

				<?php
				/**
				 * Hook: woocommerce_archive_description.
				 *
				 * @hooked woocommerce_taxonomy_archive_description - 10
				 * @hooked woocommerce_product_archive_description - 10
				 */
				do_action( 'woocommerce_archive_description' );
				?>
			</header>

			<?php
			/**
			 * Hook: awcs_before_shop_loop.
			 *
			 * @hooked woocommerce_output_all_notices - 10
			 * @hooked woocommerce_result_count - 20
			 * @hooked woocommerce_catalog_ordering - 30
			 */
			do_action( 'awcs_before_shop_loop' );

			woocommerce_product_loop_start();

			if ( wc_get_loop_prop( 'total' ) ) {
				foreach ( $products->ids as $product_id ) {

					$GLOBALS['post'] = get_post( $product_id ); 
					setup_postdata( $GLOBALS['post'] );

					/**
					 * Hook: woocommerce_shop_loop.
					 */
					do_action( 'woocommerce_shop_loop' );

					// Render product template.
					wc_get_template_part( 'content', 'product' );

				}
			}

			$GLOBALS['post'] = $original_post;

			woocommerce_product_loop_end();

			/**
			 * Hook: woocommerce_after_shop_loop.
			 *
			 * @hooked woocommerce_pagination - 10
			 */
			do_action( 'woocommerce_after_shop_loop' );

			wp_reset_postdata();
			wc_reset_loop();

		} else {
			/**
			 * Hook: woocommerce_no_products_found.
			 *
			 * @hooked wc_no_products_found - 10
			 */
			do_action( 'woocommerce_no_products_found' );
		}

		/**
		 * Hook: awcs_after_main_content.
		 *
		 * @hooked awcs_output_content_wrapper_end - 10 (outputs closing divs for the content)
		 */
		do_action( 'awcs_after_main_content' );

	}

	/**
	 * Set taxonomy query args.
	 *
	 * @since 3.2.0
	 * @param array $query_args Query args.
	 */
	protected function set_tax_query_args( &$query_args ) {

		$query_args['tax_query'][] = array(
			'taxonomy'         => 'product_visibility',
			'terms'            => 'exclude-from-catalog',
			'field'            => 'name',
			'operator'         => 'NOT IN',
			'include_children' => false,
		);

		if ( ! empty( $this->settings['category'] ) ) {
			$categories = array_map( 'sanitize_title', explode( ',', $this->settings['category'] ) );
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
				'operator'         => $this->settings['cat_operator'],

				/*
				 * When cat_operator is AND, the children categories should be excluded,
				 * as only products belonging to all the children categories would be selected.
				 */
				'include_children' => 'AND' === $this->settings['cat_operator'] ? false : true,
			);
		}

		if ( ! empty( $this->settings['tag'] ) ) {
			$query_args['tax_query'][] = array(
				'taxonomy' => 'product_tag',
				'terms'    => array_map( 'sanitize_title', explode( ',', $this->settings['tag'] ) ),
				'field'    => 'slug',
				'operator' => $this->settings['tag_operator'],
			);
		}

		if ( ! empty( $this->settings['attribute'] ) || ! empty( $this->settings['terms'] ) ) {
			$taxonomy = strstr( $this->settings['attribute'], 'pa_' ) ? sanitize_title( $this->settings['attribute'] ) : 'pa_' . sanitize_title( $this->settings['attribute'] );
			$terms    = $this->settings['terms'] ? array_map( 'sanitize_title', explode( ',', $this->settings['terms'] ) ) : array();
			$field    = 'slug';

			if ( $terms && is_numeric( $terms[0] ) ) {
				$field = 'term_id';
				$terms = array_map( 'absint', $terms );
				// Check numeric slugs.
				foreach ( $terms as $term ) {
					$the_term = get_term_by( 'slug', $term, $taxonomy );
					if ( false !== $the_term ) {
						$terms[] = $the_term->term_id;
					}
				}
			}

			// If no terms were specified get all products that are in the attribute taxonomy.
			if ( ! $terms ) {
				$terms = get_terms(
					array(
						'taxonomy' => $taxonomy,
						'fields'   => 'ids',
					)
				);
				$field = 'term_id';
			}

			// We always need to search based on the slug as well, this is to accommodate numeric slugs.
			$query_args['tax_query'][] = array(
				'taxonomy' => $taxonomy,
				'terms'    => $terms,
				'field'    => $field,
				'operator' => $this->settings['terms_operator'],
			);
		}

	}

	/**
	 * Exclude products.
	 *
	 * @since 3.2.0
	 * @param array $query_args Query args.
	 */
	protected function awcs_exclude_products( &$query_args ) {

		if( ! empty( $this->settings['exclude_products'] ) ) {

			$products = array_map( 'sanitize_title', explode( ',', $this->settings['exclude_products'] ) );

			if ( is_numeric( $products[0] ) ) {
				$products = array_map( 'absint', $products );
			}

			$query_args['post__not_in'] = $products;
		}
	}

}