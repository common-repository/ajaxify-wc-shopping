<?php
/**
 * Setting callback functions
 *
 * @package AjaxifyWoocommerceShopping
 * @version 1.1.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists('Awcs_Callback_Functions') ) {

    /**
    * Callback functions class
    */
    class Awcs_Callback_Functions
    {

    	private $general_settings;

    	public function __construct() {

    		$this->general_settings = get_option( 'awcs-general-settings' );

    	}

    	public function callback_text( $args ) {

            if( isset( $args['section'] ) ) {

                switch ( $args['section'] ) {
                
                    case 'awcs-general-settings':
                        $value = isset($this->general_settings[$args['id']]) && !empty($this->general_settings[$args['id']]) ? $this->general_settings[$args['id']] : '';
                        break;         

                    default:
                        break;
                }

            }

            $type        = isset( $args['type'] ) ? $args['type'] : 'text';
            $placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';
            $class       = isset( $args['class'] ) && !empty( $args['class'] ) ? $args['class'] : '';
            $required    = isset( $args['required'] ) && !empty( $args['required'] ) ? $args['required'] : '';

            $html = sprintf( '<input type="%1$s" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" placeholder="%5$s" class="form-control input-md %6$s" %7$s />', esc_attr($type), esc_attr($args['section']), esc_attr($args['id']), esc_attr($value), esc_attr($placeholder), esc_attr($class), esc_attr($required) );

            if( $args['desc'] )
                $html .= '<p>'.$args['desc'].'</p>';

            $allowed_html = array(
                'input' => array(
                    'class' => array(),
                    'id'  => array(),
                    'name'   => array(),
                    'type' => array(),
                    'value' => array(),
                    'placeholder' => array(),
                ),
                'p' => array(
                    'class' => array(),
                )
            );

            echo wp_kses( $html, $allowed_html );

        }

        public function callback_select( $args ) {

            if( isset( $args['section'] ) ) {

                switch ( $args['section'] ) {
                
                    case 'awcs-general-settings':
                        $value = isset($this->general_settings[$args['id']]) && !empty($this->general_settings[$args['id']]) ? $this->general_settings[$args['id']] : '';
                        break;         
                            
                    default:
                        break;
                }

            }

            $html = '';

            if( $args['id'] == "number-carousel-items" ) {

                $class  = isset( $args['class'] ) && !empty( $args['class'] ) ? $args['class'] : '';
                $size  = isset( $args['size'] ) && !empty( $args['size'] ) ? $args['size'] : 'regular';

                $html  .= sprintf( '<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">', esc_attr($class), "select_max_carousel_length", esc_attr($args['id']) );

                if( !empty($value) && !array_key_exists( $value, $args['options'] ) ) {
                    $html .= sprintf( '<option value="%s" %s>%s</option>', esc_attr($value), selected( $value, $value, false ), esc_attr($value) );
                }

                foreach ( $args['options'] as $key => $label ) {
                    $html .= sprintf( '<option value="%s" %s>%s</option>', esc_attr($key), selected( $value, $key, false ), esc_attr($label) );               
                }

                $html .= sprintf( '</select>' );

                $html .= sprintf( '<input type="number" size="%1$s" class="awcs_max_carousel_length" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" class="form-control input-md" />', esc_attr($size), esc_attr($args['section']), esc_attr($args['id']), esc_attr($value) );

                if( $args['desc'] )
                    $html .= '<p>'.$args['desc'].'</p>';

            } else {

                $class  = isset( $args['class'] ) && !empty( $args['class'] ) ? $args['class'] : '';

                $html  .= sprintf( '<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">', esc_attr($class), esc_attr($args['section']), esc_attr($args['id']) );

                foreach ( $args['options'] as $key => $label ) {
                    $html .= sprintf( '<option value="%s" %s>%s</option>', esc_attr($key), selected( $value, $key, false ), esc_attr($label) );               
                }

                $html .= sprintf( '</select>' );

                if( $args['desc'] )
                    $html .= '<p>'.$args['desc'].'</p>';

            }
            
            $allowed_html = array(
                'select' => array(
                    'class' => array(),
                    'id'  => array(),
                    'name'   => array(),
                    'type' => array(),
                    'value' => array(),
                    'placeholder' => array(),
                ),
                'option' => array(
                    'value' => array(),
                    'selected' => array(),
                ),
                'p' => array(
                    'class' => array(),
                )
            );

            echo wp_kses( $html, $allowed_html );
        }

        public function callback_checkbox( $args ) {

            if( isset( $args['section'] ) ) {

                $name = $args['section'];

                switch ($args['section']) {
                
                    case 'awcs-general-settings':
                        $value = isset($this->general_settings[$args['id']]) && !empty($this->general_settings[$args['id']]) ? $this->general_settings[$args['id']] : '';
                        break;         
                    
                    default:                        
                        break;
                }

            }

            if ($value && $value != 'on') { 
                $status = ""; 
            } else {                
                $status = " checked";
            }

            $class = isset( $args['class'] ) && !empty( $args['class'] ) ? $args['class'] : 'checkbox';

            $html = sprintf(
                '<input type="checkbox" class="%1$s" name="%2$s[%3$s]" %4$s />', esc_attr($class), esc_attr($name), esc_attr($args['id']), esc_attr($status)
            );

            $html .= sprintf( '<input type="hidden" name="%1$s[%2$s]" value="' .esc_attr($name). '-section" />', esc_attr($name), esc_attr($args['id']) . "-flag" );

            $html .= '<p>'.$args['desc'].'</p>';

            $allowed_html = array(
                'input' => array(
                    'class' => array(),
                    'id'  => array(),
                    'name'   => array(),
                    'type' => array(),
                    'value' => array(),
                    'placeholder' => array(),
                    'checked' => array(),
                ),
                'p' => array(
                    'class' => array(),
                )
            );

            echo wp_kses( $html, $allowed_html );
        }

        public function callback_multi_category_select( $args ) {

            if( isset( $args['section'] ) ) {

                switch ( $args['section'] ) {
                
                    case 'awcs-general-settings':
                        $categories = isset($this->general_settings[$args['id']]) && !empty($this->general_settings[$args['id']]) ? $this->general_settings[$args['id']] : '';
                        break;         
                    
                    default:
                        break;
                }

            }

            $class = isset( $args['class'] ) && !empty( $args['class'] ) ? $args['class'] : '';

            $selected_category_ids = array();

            if( $categories ) {
                foreach ($categories as $key => $category_id) {
                    array_push( $selected_category_ids, $category_id );
                }
            }

            $html = '';

            $html .= '<div class="options_group">';

            $html .= '<p class="form-field">';

            $html  .= sprintf( '<select class="%1$s" multiple="multiple" name="%2$s[%3$s][]" id="%2$s[%3$s]" data-placeholder="' . esc_attr_e( 'Select category', 'woocommerce' ) . '">', esc_attr($class), esc_attr($args['section']), esc_attr($args['id']) );

            $all_categories   = get_terms( 'product_cat', 'orderby=name&hide_empty=0' );

            if ( $all_categories ) {
                foreach ( $all_categories as $cat ) {
                    $html .= sprintf( '<option value="%s" %s>%s</option>', esc_attr( $cat->term_id ), wc_selected( $cat->term_id, $selected_category_ids ), esc_html( $cat->name ) );  
                }
            }

            $html .= sprintf( '</select>' );

            $html .= '</p>';
                
            $html .= '</div>';

            if( $args['desc'] )
                $html .= '<p>'.$args['desc'].'</p>';

            $allowed_html = array(
                'div' => array(
                    'class' => array(),
                ),
                'select' => array(
                    'class' => array(),
                    'id'  => array(),
                    'name'   => array(),
                    'multiple' => array(),
                    'value' => array(),
                    'data-placeholder' => array(),
                ),
                'option' => array(
                    'value' => array(),
                    'selected' => array(),
                ),
                'p' => array(
                    'class' => array(),
                )
            );

            echo wp_kses( $html, $allowed_html );
        }

        public function callback_multi_product_select( $args ) {

            global $product;

            if( isset( $args['section'] ) ) {

                switch ( $args['section'] ) {
                
                    case 'awcs-general-settings':
                        $products = isset($this->general_settings[$args['id']]) && !empty($this->general_settings[$args['id']]) ? $this->general_settings[$args['id']] : '';
                        break;         
                    
                    default:
                        break;
                }

            }

            $class = isset( $args['class'] ) && !empty( $args['class'] ) ? $args['class'] : '';

            $selected_product_ids = array();

            if( $products ) {
                foreach ($products as $key => $product_id) {
                    array_push( $selected_product_ids, $product_id );
                }
            }

            $html = '';

            $html .= '<div class="options_group">';

            $html .= '<p class="form-field">';

            $html  .= sprintf( '<select class="%1$s" multiple="multiple" name="%2$s[%3$s][]" id="%2$s[%3$s]" data-placeholder="' . esc_attr_e( 'Select product', 'woocommerce' ) . '">', esc_attr($class), esc_attr($args['section']), esc_attr($args['id']) );

            $query_args = array(
                'post_type' => 'product',
                'post_status' => 'publish',
                'ignore_sticky_posts' => true,
                'no_found_rows'       => true,
                'posts_per_page'      => -1,
                'update_post_meta_cache' => false,
                'update_post_term_cache' => false,
                'fields' => 'ids'
            );

            $all_products = new WP_Query( $query_args );

            $product_ids = wp_parse_id_list( $all_products->posts );

            foreach ( $product_ids as $product_id ) {

                $product = wc_get_product( $product_id );

                if ( is_object( $product ) ) {
                    $html .= sprintf( '<option value="%s" %s>%s</option>', esc_attr( $product_id ), wc_selected( $product_id, $selected_product_ids ), htmlspecialchars( wp_kses_post( $product->get_formatted_name() ) ) );
                }

            }

            wp_reset_postdata();

            $html .= sprintf( '</select>' );

            $html .= '</p>';
                
            $html .= '</div>';

            if( $args['desc'] )
                $html .= '<p>'.$args['desc'].'</p>';

            $allowed_html = array(
                'div' => array(
                    'class' => array(),
                ),
                'select' => array(
                    'class' => array(),
                    'id'  => array(),
                    'name'   => array(),
                    'multiple' => array(),
                    'value' => array(),
                    'data-placeholder' => array(),
                ),
                'option' => array(
                    'value' => array(),
                    'selected' => array(),
                ),
                'p' => array(
                    'class' => array(),
                )
            );

            echo wp_kses( $html, $allowed_html );          
        }

        public function callback_number( $args ) {

            if( isset( $args['section'] ) ) {

                switch ( $args['section'] ) {
                
                    case 'awcs-general-settings':
                        $value = isset($this->general_settings[$args['id']]) && !empty($this->general_settings[$args['id']]) ? $this->general_settings[$args['id']] : '';
                        break;         
                    
                    default:
                        break;
                }

            }

            $type        = isset( $args['type'] ) ? $args['type'] : 'text';
            $placeholder = isset( $args['placeholder'] ) ? $args['placeholder'] : '';
            $class       = isset( $args['class'] ) && !empty( $args['class'] ) ? $args['class'] : '';
            $required    = isset( $args['required'] ) && !empty( $args['required'] ) ? $args['required'] : '';

            $html = sprintf( '<input type="%1$s" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" placeholder="%5$s" class="form-control input-md %6$s" %7$s />', esc_attr($type), esc_attr($args['section']), esc_attr($args['id']), esc_attr($value), esc_attr($placeholder), esc_attr($class), esc_attr($required) );

            if( $args['desc'] )
                $html .= '<p>'.$args['desc'].'</p>';

            $allowed_html = array(
                'input' => array(
                    'class' => array(),
                    'id'  => array(),
                    'name'   => array(),
                    'type' => array(),
                    'value' => array(),
                    'placeholder' => array(),
                ),
                'p' => array(
                    'class' => array(),
                )
            );

            echo wp_kses( $html, $allowed_html );
        }

        public function callback_radio( $args ) {            

            if( isset( $args['section'] ) ) {

                $name = $args['section'];

                switch ($args['section']) {
                
                    case 'awcs-general-settings':
                        $value = isset($this->general_settings[$args['id']]) && !empty($this->general_settings[$args['id']]) ? $this->general_settings[$args['id']] : '';
                        break;         
                          
                    default:                        
                        break;
                }

            }

            $required = isset( $args['required'] ) && !empty( $args['required'] ) ? $args['required'] : '';

            $html = '<fieldset>';

            foreach ($args['options'] as $key => $label) {
                
                $html .= sprintf( '<label for="%1$s[%2$s]">', $name, $key );

                $html .= sprintf( '<input type="radio" class="radio" id="%1$s[%3$s]" name="%1$s[%2$s]" value="%3$s" %4$s %5$s />', esc_attr($name), esc_attr($args['id']), esc_attr($key), checked( $value, $key, false ), esc_attr($required) );
                
                $html .= sprintf( '%1$s</label><br>', esc_attr($label) );
            }

            $html .= '</fieldset>';

            if( $args['desc'] )
                $html .= '<p>'.$args['desc'].'</p>';

            $allowed_html = array(
                'fieldset' => array(
                    'class' => array(),
                ),
                'label' => array(
                    'for' => array(),
                ),
                'input' => array(
                    'class' => array(),
                    'id'  => array(),
                    'name'   => array(),
                    'type' => array(),
                    'value' => array(),
                    'placeholder' => array(),
                    'checked' => array(),
                ),
                'p' => array(
                    'class' => array(),
                ),
                'br' => array(
                    'class' => array(),
                )
            );

            echo wp_kses( $html, $allowed_html );

        }

    }

}