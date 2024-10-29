<?php
/**
 * Retrive General Settings
 *
 * @package AjaxifyWoocommerceShopping
 * @version 1.1.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists('Awcs_Retrieve_General_Settings') ) {

    /**
    * Finding General Settings Class
    */
    class Awcs_Retrieve_General_Settings
    {

    	private static $instance = null;

    	private $general_settings;

    	private function __construct() {

    		$this->general_settings = get_option( 'awcs-general-settings' );

    	}

    	public static function getInstance() {

		    if (self::$instance == null)
		    {
		      self::$instance = new Awcs_Retrieve_General_Settings();
		    }
		 
		    return self::$instance;
	    }

    	public function awcs_get_target_element() {

    		$target_element = '';

			if( $this->general_settings != null && !empty($this->general_settings) ) {

				if( ! empty( $this->general_settings['target-element'] ) ) {

					$target_element = esc_html($this->general_settings['target-element']);

				}

			}

			return $target_element;
    	}

    	public function awcs_get_product_categories() {

    		return ( isset( $this->general_settings['awcs_general_product_categories'] ) && !empty( $this->general_settings['awcs_general_product_categories'] ) ) ? $this->general_settings['awcs_general_product_categories'] : array();
    	}

    	public function awcs_get_excluded_products() {

    		return ( isset( $this->general_settings['awcs_general_exclude_products'] ) && !empty( $this->general_settings['awcs_general_exclude_products'] ) ) ? $this->general_settings['awcs_general_exclude_products'] : array();
    	}

    	public function awcs_get_product_display_option() {

    		return ( isset( $this->general_settings['product-display-option'] ) && !empty( $this->general_settings['product-display-option'] ) ) ? esc_html($this->general_settings['product-display-option']) : '';
    	}

    	public function awcs_limit_numberof_products() {

    		return ( isset( $this->general_settings['limit-number-product'] ) && !empty( $this->general_settings['limit-number-product'] ) ) ? intval( $this->general_settings['limit-number-product'] ) : '';
    	}

    	public function awcs_get_products_sortby() {

    		return ( isset( $this->general_settings['products-sort-by'] ) && !empty( $this->general_settings['products-sort-by'] ) ) ? esc_html($this->general_settings['products-sort-by']) : '';
    	}

        public function awcs_override_single_product_template() {

            return ( isset( $this->general_settings['awcs-single-product-template'] ) && !empty( $this->general_settings['awcs-single-product-template'] ) && esc_html($this->general_settings['awcs-single-product-template']) == "override" ) ? true : false;
        }

        public function awcs_get_single_product_selector() {

            return ( isset( $this->general_settings['awcs-single-product-selector'] ) && !empty( $this->general_settings['awcs-single-product-selector'] ) ) ? trim( $this->general_settings['awcs-single-product-selector'], "," ) : '';
        }

        public function awcs_get_category_selector() {

            return ( isset( $this->general_settings['awcs-category-selector'] ) && !empty( $this->general_settings['awcs-category-selector'] ) ) ? trim( $this->general_settings['awcs-category-selector'], "," ) : '';
        }

        public function awcs_get_tag_selector() {

            return ( isset( $this->general_settings['awcs-tag-selector'] ) && !empty( $this->general_settings['awcs-tag-selector'] ) ) ? trim( $this->general_settings['awcs-tag-selector'], "," ) : '';
        }

        public function awcs_remove_loading_icon() {
            
            return ( isset( $this->general_settings['awcs-remove-loading-icon'] ) && !empty( $this->general_settings['awcs-remove-loading-icon'] ) && esc_html($this->general_settings['awcs-remove-loading-icon']) == "on" ) ? true : false;
        }

    }

}