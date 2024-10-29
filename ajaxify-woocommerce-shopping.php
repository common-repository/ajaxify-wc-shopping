<?php
/**
 * Plugin Name:       Single Page Shopping
 * Plugin URI:        https://awcs.spplugins.com/
 * Description:       Enjoy shopping without reloading your website.
 * Version:           1.1.0
 * Requires at least: 5.6
 * Tested up to:      6.0
 * Requires PHP:      7.2
 * Author:            SP Plugins
 * Author URI:        https://spplugins.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ajaxify-woocommerce-shopping
 * Domain Path:       /languages/
 *
 * @package AjaxifyWoocommerceShopping
 */

defined( 'ABSPATH' ) || exit;

// Check if woocommerce is active
if ( ! in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    exit;
}

// Plugin Constants
if ( ! defined('AWCS_PATH') ) {
	define('AWCS_PATH', plugin_dir_path(__FILE__));
}
	
if ( ! defined('AWCS_URL') ) {
	define('AWCS_URL', plugin_dir_url(__FILE__));
}
	
if ( ! defined('AWCS_VERSION') ) {
	define('AWCS_VERSION', '1.1.0');
}

if ( ! defined( 'AWCS_PLUGIN_FILE' ) ) {
    define( 'AWCS_PLUGIN_FILE', __FILE__ );
}

// Load text domain
add_action( 'plugins_loaded', 'awcs_plugin_load_text_domain' );

function awcs_plugin_load_text_domain() {
    load_plugin_textdomain( 'ajaxify-woocommerce-shopping', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

// Include classes, hooks, functions
require_once(AWCS_PATH . 'settings/class-awcs-callback-functions.php');
require_once(AWCS_PATH . 'settings/class-awcs-admin-settings.php');
require_once(AWCS_PATH . 'settings/class-awcs-retrieve-general-settings.php');

require_once(AWCS_PATH . 'includes/awcs-common-hooks.php');
require_once(AWCS_PATH . 'includes/awcs-common-functions.php');

require_once(AWCS_PATH . 'includes/class-awcs-basic-scripts.php');

require_once(AWCS_PATH . 'includes/class-awcs-products-query.php');
require_once(AWCS_PATH . 'includes/class-awcs-wc-page-contents.php');
require_once(AWCS_PATH . 'includes/class-awcs-wc-cart-actions.php');
require_once(AWCS_PATH . 'includes/class-awcs-find-products.php');
require_once(AWCS_PATH . 'includes/class-awcs-my-account.php');
require_once(AWCS_PATH . 'includes/class-awcs-form-handler.php');

// Include admin scripts
function awcs_include_admin_scripts( $hook ) {

	if ( 'settings_page_awcs-settings' != $hook ) {
        return;
    }

	wp_enqueue_style( 'woocommerce_admin_styles' );

	wp_enqueue_style( 'awcs-wc-jquery-ui', AWCS_URL . 'assets/css/ext/jquery-ui.css', array(), null, 'all');

	wp_enqueue_script( 'jquery-ui-tabs' );

    wp_enqueue_script( 'jquery-blockui' );

	wp_enqueue_script( 'wc-enhanced-select' );

    $admin_params = array(
    	'ajax_loading_img' => '<img src="'. AWCS_URL . "assets/image/spinner.gif" .'" width="24" height="24" style="border: none;" alt="" />',
	);

    $settings_script_handle = 'awcs-admin-setting';

	wp_register_script( $settings_script_handle, AWCS_URL . 'assets/js/awcs-setting-tabs.min.js', array('jquery'), null, true );

	$admin_param_name = str_replace( '-', '_', $settings_script_handle ) . '_params';

	wp_localize_script( $settings_script_handle, $admin_param_name, $admin_params );

	wp_enqueue_script( $settings_script_handle );
}

if( is_admin() )
	add_action('admin_enqueue_scripts', 'awcs_include_admin_scripts', 99);

// Include frontend scripts
function awcs_include_scripts() {

	// Woocommerce scripts
	if ( current_theme_supports( 'wc-product-gallery-zoom' ) ) {
        wp_enqueue_script( 'zoom' );
    }

    if ( current_theme_supports( 'wc-product-gallery-slider' ) ) {
        wp_enqueue_script( 'flexslider' );
    }

    if ( current_theme_supports( 'wc-product-gallery-lightbox' ) ) {
        wp_enqueue_script( 'photoswipe-ui-default' );
        wp_enqueue_style( 'photoswipe-default-skin' );
        add_action( 'wp_footer', 'woocommerce_photoswipe' );
    }

    wp_enqueue_script( 'jquery-blockui' );

    wp_enqueue_script( 'woocommerce' );
	wp_enqueue_script( 'wc-cart-fragments' );

	// Awcs scripts
	wp_enqueue_script( 'awcs-jquery-validate-min' );

	wp_enqueue_script( 'awcs-wc-global' );

	wp_enqueue_script('awcs-manage-target-element');

	wp_enqueue_script('awcs-wc-basic-page-contents');

	wp_enqueue_script('awcs-wc-basic-find-products');

	wp_enqueue_script('awcs-wc-basic-single-product');

	wp_enqueue_script('awcs-wc-basic-cart-functions');

	wp_enqueue_script('awcs-wc-basic-my-account');

	wp_enqueue_script( 'awcs-wc-add-to-cart-variation' );

	wp_enqueue_script('awcs-wc-add-to-cart');

}

if( !is_admin() )
	add_action('wp_enqueue_scripts', 'awcs_include_scripts', 99);