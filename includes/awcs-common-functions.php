<?php
/**
 * AWCS Common Functions
 *
 * @package  AjaxifyWoocommerceShopping
 * @version  1.1.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Common Functions
 */
if ( ! function_exists( 'awcs_output_content_wrapper' ) ) {

    /**
     * Output the start of the page wrapper.
     */
    function awcs_output_content_wrapper() {

        $wrapper = '<div id="awcs_shop_content_wrapper" style="padding: 20px;">';

        $allowed_html = array(
            'div' => array(
                'id' => array(),
                'style' => array()
            )
        );

        echo wp_kses( $wrapper, $allowed_html );

    }
}

if ( ! function_exists( 'awcs_output_content_wrapper_end' ) ) {

    /**
     * Output the end of the page wrapper.
     */
    function awcs_output_content_wrapper_end() {
        echo '</div>';
    }
}

if ( ! function_exists( 'awcs_wc_breadcrumb' ) ) {

    /**
     * Output the WooCommerce Breadcrumb.
     *
     * @param array $args Arguments.
     */
    function awcs_wc_breadcrumb( $args = array() ) {
        
        $args = wp_parse_args(
            $args,
            apply_filters(
                'awcs_breadcrumb_defaults',
                array(
                    'delimiter'   => '&nbsp;&#47;&nbsp;',
                    'wrap_before' => '<nav class="woocommerce-breadcrumb">',
                    'wrap_after'  => '</nav>',
                    'before'      => '',
                    'after'       => '',
                    'home'        => _x( 'Home', 'breadcrumb', 'woocommerce' ),
                )
            )
        );

        $breadcrumbs = new WC_Breadcrumb();

        if ( ! empty( $args['home'] ) ) {
            $breadcrumbs->add_crumb( $args['home'], apply_filters( 'woocommerce_breadcrumb_home_url', home_url() ) );
        }

        $shop_page_id = wc_get_page_id( 'shop' );
        $shop_page    = get_post( $shop_page_id );

        if ( isset( $_POST['category'] ) && ! empty( $_POST['category'] ) ) {

            $category = sanitize_text_field( $_POST['category'] );

            $category_term = get_term_by( 'slug', $category, 'product_cat' );
            awcs_get_term_ancestors( $breadcrumbs, $category_term );
            $breadcrumbs->add_crumb( $category_term->name, get_term_link( $category_term, 'product_cat' ) );

        } else if ( isset( $_POST['tag'] ) && ! empty( $_POST['tag'] ) ) {

            $tag = sanitize_text_field( $_POST['tag'] );

            $tag_term = get_term_by( 'slug', $tag, 'product_tag' );
            awcs_get_term_ancestors( $breadcrumbs, $tag_term );
            $breadcrumbs->add_crumb( $tag_term->name, get_term_link( $tag_term, 'product_tag' ) );

        } else if ( $shop_page_id && $shop_page && intval( get_option( 'page_on_front' ) ) !== $shop_page_id ) {

            $breadcrumbs->add_crumb( get_the_title( $shop_page ), get_permalink( $shop_page ) );

        } 

        if ( isset( $_POST['page'] ) && 'subcategories' !== woocommerce_get_loop_display_mode() ) {

            $page = sanitize_text_field( $_POST['page'] );

            $breadcrumbs->add_crumb( sprintf( __( 'Page %d', 'woocommerce' ), $page ) );
        }    

        $args['breadcrumb'] = $breadcrumbs->generate();

        /**
         * WooCommerce Breadcrumb hook
         *
         * @hooked WC_Structured_Data::generate_breadcrumblist_data() - 10
         */
        do_action( 'woocommerce_breadcrumb', $breadcrumbs, $args );

        wc_get_template( 'global/breadcrumb.php', $args );

    }

}

/**
*  Get term ancestors
*/
function awcs_get_term_ancestors( $breadcrumbs, $term ) {

    $ancestors = get_ancestors( $term->term_id, 'product_cat' );
    $ancestors = array_reverse( $ancestors );

    foreach ( $ancestors as $ancestor ) {
        $ancestor = get_term( $ancestor, 'product_cat' );

        if ( ! is_wp_error( $ancestor ) && $ancestor ) {
            $breadcrumbs->add_crumb( $ancestor->name, get_term_link( $ancestor, 'product_cat' ) );
        }
    }

}

/**
*  Setup product paging arg
*/
function wc_setup_paging( $query_args = array(), $attr = array() ) {

    $query_args['paged'] = sanitize_text_field($_POST['page']);

    return $query_args;
}

/**
*  Product link @ order item
*/
function awcs_order_item_name( $link, $item, $is_visible ) {

    global $product;

    $product = $item->get_product();

    if( $product->get_type() == "variable" || $product->get_type() == "variation" ) {
        $product_id_attr = sprintf( ' product-id="%s" ', $product->get_parent_id() );
    } else {
        $product_id_attr = sprintf( ' product-id="%s" ', $product->get_id() );
    }

    $product_link = substr_replace($link, $product_id_attr, 2, 0);
    
    return $product_link;
}

/**
*  Product id field @ woocommerce order item meta end
*/
function awcs_order_item_meta_product_id( $item_id, $item, $order, $status ) {

    global $product;

    $product = $item->get_product();

    if( $product->get_type() == "variable" || $product->get_type() == "variation" ) {
        $product_id = $product->get_parent_id();
    } else {
        $product_id = $product->get_id();
    }

    $field = '<input type="hidden" class="awcs_order_item_product_id" value="' . esc_attr($product_id) . '" />';

    $allowed_html = array(
        'input' => array(
            'class' => array(),
            'type' => array(),
            'value' => array()
        )
    );

    echo wp_kses( $field, $allowed_html );
}

/**
*  Product id field @ before product title
*/
function awcs_add_data_product_id() {

    global $post;

    $field = '<input type="hidden" class="awcs_data_product_id" product_name="' . esc_attr(get_the_title($post->ID)) . '" value="' . esc_attr($post->ID) . '" />';

    $allowed_html = array(
        'input' => array(
            'class' => array(),
            'type' => array(),
            'value' => array(),
            'product_name' => array()
        )
    );

    echo wp_kses( $field, $allowed_html );
}

/**
*  Action callback method for adding nonce key for applying and removing coupon
*
*  Hook woocommerce_review_order_before_submit
*  Hook woocommerce_proceed_to_checkout
*
*/
function awcs_coupon_nonce() {

    wp_nonce_field( 'awcs_apply_coupon_nonce', 'awcs_apply_coupon_nonce' );
    wp_nonce_field( 'awcs_remove_coupon_nonce', 'awcs_remove_coupon_nonce' );

}

/**
*  Append URL parameter
*/
function awcs_append_url_parameters( $url ) {

    $params = parse_url( $url, PHP_URL_QUERY );

    if( $params ) {
        $url .= '&referrer=awcs';
    } else {
        $url .= '?referrer=awcs';
    }

    return $url;
}

/**
*  Display content area
*/
function awcs_print_content_section( $page, $title = '', $id = '', $class = '', $isSinglePage = '' ) {

    $limit = Awcs_Find_Products::per_page();

    if ( $page != 'sp-cart' && $page != 'sp-checkout' && empty($isSinglePage) ) {

        ?>

            <header class="woocommerce-products-header">
                <h1 class="woocommerce-products-header__title page-title">
                    <?php esc_html_e( $title, 'ajaxify-woocommerce-shopping' ); ?>
                </h1>
            </header>

        <?php
    }

    switch ($page) {

        case 'cart':

            add_action( 'woocommerce_proceed_to_checkout', 'awcs_coupon_nonce' );
            define('WOOCOMMERCE_CART', true);
            echo do_shortcode('[woocommerce_cart]');
            break;
        
        case 'checkout':

            if( WC()->cart->get_cart_contents_count() == 0 ) {

                add_action( 'woocommerce_proceed_to_checkout', 'awcs_coupon_nonce' );
                echo do_shortcode('[woocommerce_cart]');

            } else {

                add_action( 'woocommerce_review_order_before_submit', 'awcs_coupon_nonce' );
                define('WOOCOMMERCE_CHECKOUT', true);
                echo do_shortcode('[woocommerce_checkout]');

            }

            break;

        case 'myaccount':

            echo do_shortcode('[woocommerce_my_account]');
            break;

        case 'lost-password':

            WC_Shortcode_My_Account::lost_password();
            break;

        case 'lost-password-confirmation':
        
            wc_get_template( 'myaccount/lost-password-confirmation.php' );
            break;

        default:                
            break;
    }

}

/**
*  Display notice
*/
if ( ! function_exists( 'awcs_display_notice' ) ) {

    function awcs_display_notice( $text ) {
        wc_print_notice( esc_html__( $text, 'woocommerce' ) );
    }
}

/**
*  Check if Storefront theme is activated
*/
if ( ! function_exists( 'awcs_is_storefront_activated' ) ) {
    /**
     * Query Storefront activation
     */
    function awcs_is_storefront_activated() {
        return class_exists( 'Storefront' ) ? true : false;
    }
}

/**
*  Check if others have loading icon
*/
if ( ! function_exists( 'awcs_loading_icon_exists' ) ) {
    /**
     *  Query if other theme / plugin active which has loading icon already
     */
    function awcs_loading_icon_exists() {

        // Storefront theme
        if( awcs_is_storefront_activated() ) {
            return true;
        }

        $awcs_settings = Awcs_Retrieve_General_Settings::getInstance();

        if( $awcs_settings->awcs_remove_loading_icon() ) {
            return true;
        }

        return false;
    }
}

/**
*  Get single product content
*/
if( ! function_exists( 'awcs_single_product_content' ) ) {

    function awcs_single_product_content( $url ) {

        global $post;

        $product_id = $post->ID;

        if( empty( $url ) )
            $url = get_permalink( $product_id );

        $url = awcs_append_url_parameters( $url );

        ?>

        <iframe class="awcs_single_product_frame" id="awcs_single_product_<?php echo esc_attr($product_id); ?>" product_id="<?php echo esc_attr($product_id); ?>" src="<?php echo esc_url($url); ?>" scrolling="no" frameborder="0" allowtransparency="true" allowpaymentrequest="true" data-origwidth="" data-origheight="" style="width: 100%;"></iframe>

        <?php
    }
}

/**
*  Override single product template to resolve conflict if there is any
*/
if ( ! function_exists( 'awcs_override_single_product' ) ) {

    function awcs_override_single_product( $product_id, $product_url ) {

        global $product;

        $product = wc_get_product( $product_id );

        require_once(AWCS_PATH . 'templates/single-product-override/content-single-product.php');
    }
}

/**
*  Template redirect
*/
if ( ! function_exists( 'awcs_template_redirect' ) ) {

    function awcs_template_redirect( $template ) {

        $location = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

        // payment method page, checkout page
        if( ( is_product() && isset( $_GET['referrer'] ) ) || ( is_add_payment_method_page() && ! isset( $_GET['referrer'] ) ) || ( is_checkout() && isset( $_GET['referrer'] ) ) || ( is_order_received_page() && strpos( $location, "referrer=" ) !== false ) ) {
            return AWCS_PATH . 'templates/content-only-page/awcs-content-only-page.php';
        }

        return $template;
    }

}

/**
*  Show / hide admin bar
*/
if ( ! function_exists( 'awcs_show_hide_admin_bar' ) ) {

    function awcs_show_hide_admin_bar() {

        $location = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';

        if( ( is_product() && isset( $_GET['referrer'] ) ) || ( is_add_payment_method_page() && ! isset( $_GET['referrer'] ) ) || ( is_checkout() && isset( $_GET['referrer'] ) ) || ( is_order_received_page() && strpos( $location, "referrer=" ) !== false ) ) {
            return false;
        }

        return true;
    }

}

/**
*  Add product-id attribute to download item
*/
if( ! function_exists( 'awcs_account_downloads_column' ) ) {

    function awcs_account_downloads_column( $download ) {

        $download_link = '<a product-id="' . esc_attr( $download['product_id'] ) . '" href="' . esc_url( $download['product_url'] ) . '">' . esc_html( $download['product_name'] ) . '</a>';

        $allowed_html = array(
            'a' => array(
                'product-id' => array(),
                'href' => array(),
                'title' => array()
            )
        );

        echo wp_kses( $download_link, $allowed_html );

    }

}

/**
*  Default product order by
*/
if( ! function_exists( 'awcs_default_catalog_orderby' ) ) {

    function awcs_default_catalog_orderby( $orderby ) {

        $awcs_general_settings = Awcs_Retrieve_General_Settings::getInstance();

        $new_orderby = $awcs_general_settings->awcs_get_products_sortby();

        if( ! empty($new_orderby) ) {
            return $new_orderby;
        }

        return $orderby;
    }

}