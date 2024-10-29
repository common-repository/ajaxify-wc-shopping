/**
*
* File awcs-common-functions.js
*
* Common functions
*
*/

// Add body classes specific to wc page
function awcs_insert_body_classes( classes ) {
    jQuery( document.body ).addClass( classes );
}

// Block UI element
var block = function( $node ) {
    if ( ! is_blocked( $node ) ) {
        $node.addClass( 'awcs_loading' ).block( {
            message: awcs_wc_global_params.ajax_loading_img,
            css: {
                backgroundColor: 'transparent',
                border: 0,
                lineHeight: '32px',
                width: '32px',
                height: '32px'
            },
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        } );
    }
};

// Unblock UI element
var unblock = function( $node ) {
    $node.removeClass( 'awcs_loading' ).unblock();
};

// Check if blocked
var is_blocked = function( $node ) {
    return $node.is( '.awcs_loading' ) || $node.parents( '.awcs_loading' ).length;
};

// Disable variation form buttons
function awcs_hide_variation_data( $form ) {

    var $resetVariations = $form.find( '.reset_variations' ),
        $single_add_to_cart_button = $form.find('.single_add_to_cart_button, .awcs_loop_variation_add_to_cart_button');

    if ( $single_add_to_cart_button.is('.disabled') ) {
        return;
    }

    $resetVariations.css( 'visibility', 'hidden' );

    $form
        .find( '.single_add_to_cart_button, .awcs_loop_variation_add_to_cart_button' )
        .removeClass( 'wc-variation-is-unavailable' )
        .addClass( 'disabled wc-variation-selection-needed' );
    $form
        .find( '.woocommerce-variation-add-to-cart' )
        .removeClass( 'woocommerce-variation-add-to-cart-enabled' )
        .addClass( 'woocommerce-variation-add-to-cart-disabled' );

}