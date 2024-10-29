<?php
/**
 * Settings
 *
 * @package AjaxifyWoocommerceShopping
 * @version 1.1.0
 */

    /**
    * Settings class
    */
    class Awcs_Settings
    {
       
        private $general_settings, $single_page_settings, $ticker_settings;

        public function __construct()
        {
            add_action( 'admin_menu', array( $this, 'awcs_settings_submenu' ) );
            add_action( 'admin_init', array( $this, 'awcs_settings_page_fields' ) );
        }
        
        public function awcs_settings_submenu()
        {
            add_submenu_page( 'options-general.php', 'Ajaxify Woocommerce Shopping', 'Awcs', 'administrator', 'awcs-settings', array($this,'awcs_settings_page') );
        }

        public function awcs_settings_page()
        {

            require_once(AWCS_PATH . 'templates/admin/awcs-setting-tabs.php');

            $tabs = $this->awcs_settings_tabs();

            foreach($tabs as $tab) {

                switch ($tab['id']) {
                    
                    case 'awcs-general-settings':
                        $this->general_settings = get_option( $tab['id'] );
                        break;
                    
                    default:                    
                        break;
                }
                
            }

            $tabCount = 0;

            foreach($tabs as $tab) 
            {   
                $tabCount++; 
            ?>

              <div id="awcs-settings-tab-<?php echo esc_attr($tabCount); ?>" class="awcs-settings-tab">
                
                <form method="post" action="options.php" id="<?php echo esc_attr($tab['id']); ?>">
                <?php
                    settings_fields( $tab['id'] );
                    do_settings_sections( $tab['id'] );
                    submit_button();
                ?>
                </form>

              </div>  

            <?php 

            }

            echo '</div>';

        }

        public function awcs_settings_page_fields()
        {

            if (is_admin()) {
                if ( isset($_GET['page']) && $_GET['page'] == 'awcs-settings' ) {
                    wp_enqueue_script( 'jquery-form' );
                }
            }
            
            $tabs = $this->awcs_settings_tabs();
            $fields = $this->awcs_settings_fields();

            $callback_functions = new Awcs_Callback_Functions();

            foreach ( $tabs as $tab ) {
                add_settings_section( $tab['id'], $tab['title'], '', $tab['id'] );
            }

            foreach ( $fields as $section => $field ) {

                if (is_array($field) || is_object($field))
                  foreach ( $field as $option ) {

                    $id = isset( $option['id'] ) ? $option['id'] : '';
                    $type = isset( $option['type'] ) ? $option['type'] : 'text';
                    $label = isset( $option['label'] ) ? $option['label'] : '';
                    $callback = isset( $option['callback'] ) ? $option['callback'] : array( $callback_functions, 'callback_' . $type );

                    $args = array(
                        'id'                => $id,
                        'class'             => isset( $option['class'] ) ? $option['class'] : '',
                        'required'          => isset( $option['required'] ) && !empty( $option['required'] ) ? $option['required'] : '',
                        'label_for'         => "{$section}[{$id}]",
                        'desc'              => isset( $option['desc'] ) ? $option['desc'] : '',
                        'name'              => $label,
                        'section'           => $section,
                        'size'              => isset( $option['size'] ) ? $option['size'] : null,
                        'options'           => isset( $option['options'] ) ? $option['options'] : '',
                        'std'               => isset( $option['default'] ) ? $option['default'] : '',
                        'sanitize_callback' => isset( $option['sanitize_callback'] ) ? $option['sanitize_callback'] : '',
                        'type'              => $type,
                        'title'             => isset( $option['title'] ) ? $option['title'] : '',
                        'placeholder'       => isset( $option['placeholder'] ) ? $option['placeholder'] : ''
                    );

                    add_settings_field( "{$section}[{$id}]", $label, $callback, $section, $section, $args );
                }
            }

            foreach ( $tabs as $tab ) {
                register_setting( $tab['id'], $tab['id'], array( $this, 'awcs_sanitize_options' ) );
            }
        
        }

        public function awcs_settings_tabs()
        {
            $tabs = array(
                array(
                    'id' => 'awcs-general-settings',
                    'title' => __('General Settings', 'ajaxify-woocommerce-shopping')
                ),
            );

            return $tabs;
        }

        public function awcs_settings_fields() {

            $settings_fields = array(

                'awcs-general-settings' => array(

                    array(
                        'id' => 'target-element',
                        'label' => __('Target Element *', 'ajaxify-woocommerce-shopping'),
                        'desc' => __('Target element is where ajax content will be loaded. It should be an unique element. Generally, it is the main content area of your website.', 'ajaxify-woocommerce-shopping'),
                        'class' => '',
                        'required' => 'required',
                        'placeholder' => __('Target Element', 'ajaxify-woocommerce-shopping'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'id' => 'awcs_general_product_categories',
                        'label' => __('Select categories', 'ajaxify-woocommerce-shopping'),
                        'desc' => __('Show products only from selected categories when products first load in shop page. It does not apply for category/tag page products. <br /><b>( Leaving blank will consider all products )</b>', 'ajaxify-woocommerce-shopping'),
                        'class' => 'wc-enhanced-select',
                        'placeholder' => __('Select Category', 'ajaxify-woocommerce-shopping'),
                        'type' => 'multi_category_select',
                        'default' => ''
                    ),
                    array(
                        'id' => 'awcs_general_exclude_products',
                        'label' => __('Exclude products', 'ajaxify-woocommerce-shopping'),
                        'desc' => __('Above selected products will be excluded in shop page. It does not apply for category/tag page products.', 'ajaxify-woocommerce-shopping'),
                        'class' => 'wc-enhanced-select',
                        'placeholder' => __('Select Product', 'ajaxify-woocommerce-shopping'),
                        'type' => 'multi_product_select',
                        'default' => ''
                    ),
                    array(
                        'id' => 'product-display-option',
                        'label' => __('Product Display Option', 'ajaxify-woocommerce-shopping'),
                        'desc' => __('', 'ajaxify-woocommerce-shopping'),
                        'options' => array (
                            'paginate'   => 'Paging, Sorting',
                            'limit_product'  => 'Limit number of products'
                        ),
                        'class' => 'awcs-product-display-option',
                        'required' => '',
                        'placeholder' => __('', 'ajaxify-woocommerce-shopping'),
                        'type' => 'radio',
                        'default' => ''
                    ),
                    array(
                        'id' => 'limit-number-product',
                        'label' => __('Limit number of products / Products per page', 'ajaxify-woocommerce-shopping'),
                        'desc' => __('Maximum number of products to display or products per page if pagination is selected.', 'ajaxify-woocommerce-shopping'),
                        'class' => 'awcs-limit-number-product',
                        'required' => '',
                        'placeholder' => __('Number of products', 'ajaxify-woocommerce-shopping'),
                        'type' => 'number',
                        'default' => ''
                    ),
                    array(
                        'id' => 'products-sort-by',
                        'label' => __('Product Sort By', 'ajaxify-woocommerce-shopping'),
                        'desc' => __('Determines how products are sorted when the products first load in shop/category/tag archive page.', 'ajaxify-woocommerce-shopping'),
                        'options' => array (
                            'menu_order'   => 'Default sorting',
                            'popularity'  => 'Sort by popularity',
                            'rating'  => 'Sort by average rating',
                            'date'  => 'Sort by latest',
                            'price'  => 'Sort by price: low to high',
                            'price-desc' => 'Sort by price: high to low'
                        ),
                        'size' => 6,
                        'class' => 'awcs_products_sort_by',
                        'placeholder' => __('Product Sort By', 'ajaxify-woocommerce-shopping'),
                        'type' => 'select',
                        'default' => ''
                    ),                    
                    array(
                        'id' => 'awcs-single-product-selector',
                        'label' => __('Custom single product selector', 'ajaxify-woocommerce-shopping'),
                        'desc' => __('Comma separated list of jquery selectors. Use this option if your active theme has custom product listing.', 'ajaxify-woocommerce-shopping'),
                        'class' => '',
                        'required' => '',
                        'placeholder' => __('Selectors', 'ajaxify-woocommerce-shopping'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'id' => 'awcs-category-selector',
                        'label' => __('Custom category selector', 'ajaxify-woocommerce-shopping'),
                        'desc' => __('Comma separated list of jquery selectors. Use this option if your active theme has custom category selector.', 'ajaxify-woocommerce-shopping'),
                        'class' => '',
                        'required' => '',
                        'placeholder' => __('Selectors', 'ajaxify-woocommerce-shopping'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'id' => 'awcs-tag-selector',
                        'label' => __('Custom tag selector', 'ajaxify-woocommerce-shopping'),
                        'desc' => __('Comma separated list of jquery selectors. Use this option if your active theme has custom tag selector.', 'ajaxify-woocommerce-shopping'),
                        'class' => '',
                        'required' => '',
                        'placeholder' => __('Selectors', 'ajaxify-woocommerce-shopping'),
                        'type' => 'text',
                        'default' => ''
                    ),
                    array(
                        'id' => 'awcs-remove-loading-icon',
                        'label' => __('Remove ajax loading icon', 'ajaxify-woocommerce-shopping'),
                        'desc' => __('Remove ajax loading icon if your active theme has overlapping icon.', 'ajaxify-woocommerce-shopping'),
                        'class' => '',
                        'type' => 'checkbox'
                    ),
                    array(
                        'id' => 'awcs-single-product-template',
                        'label' => __('Single product template', 'ajaxify-woocommerce-shopping'),
                        'desc' => __('Default = As it is served by woocommerce template hierarchy.<br />Select "Override to resolve conflict" only if your active theme has different functionality for product image gallery that conflict with default one.', 'ajaxify-woocommerce-shopping'),
                        'options' => array (
                            'default'   => 'Default',
                            'override'  => 'Override to resolve conflict'
                        ),
                        'class' => 'awcs-single-product-template',
                        'required' => '',
                        'placeholder' => __('', 'ajaxify-woocommerce-shopping'),
                        'type' => 'radio',
                        'default' => ''
                    )

                ),                 
                       
            );

            return $settings_fields;
        }
        
        public function awcs_sanitize_options( $input )
        {
            $new_input = array();

            if( isset( $input['target-element'] ) )
                $new_input['target-element'] = sanitize_text_field( $input['target-element'] );

            if( isset( $input['awcs_general_product_categories'] ) )
                $new_input['awcs_general_product_categories'] = $input['awcs_general_product_categories'];

            if( isset( $input['awcs_general_exclude_products'] ) )
                $new_input['awcs_general_exclude_products'] = $input['awcs_general_exclude_products'];

            if( isset( $input['product-display-option'] ) )
                $new_input['product-display-option'] = sanitize_text_field( $input['product-display-option'] );

            if( isset( $input['limit-number-product'] ) )
                $new_input['limit-number-product'] = intval( $input['limit-number-product'] );

            if( isset( $input['products-sort-by'] ) )
                $new_input['products-sort-by'] = sanitize_text_field( $input['products-sort-by'] );

            if( isset( $input['awcs-single-product-selector'] ) )
                $new_input['awcs-single-product-selector'] = sanitize_text_field( $input['awcs-single-product-selector'] );

            if( isset( $input['awcs-category-selector'] ) )
                $new_input['awcs-category-selector'] = sanitize_text_field( $input['awcs-category-selector'] );

            if( isset( $input['awcs-tag-selector'] ) )
                $new_input['awcs-tag-selector'] = sanitize_text_field( $input['awcs-tag-selector'] );
            
            if( isset( $input['awcs-single-product-template'] ) )
                $new_input['awcs-single-product-template'] = sanitize_text_field( $input['awcs-single-product-template'] );

            if ( isset( $input['awcs-remove-loading-icon'] ) ) {
                $new_input['awcs-remove-loading-icon'] = filter_var($input['awcs-remove-loading-icon'], FILTER_SANITIZE_STRING);
            } else if( isset( $input['awcs-remove-loading-icon-flag'] ) ) {
                 $new_input['awcs-remove-loading-icon'] = filter_var("off", FILTER_SANITIZE_STRING);
            }

            return $new_input;
        }

    }

    if( is_admin() )
        $awcs_settings = new Awcs_Settings();