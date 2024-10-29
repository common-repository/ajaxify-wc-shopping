/**
*
* File awcs-manage-target-element.js
*
* Get / Set / Adjust target element
*
* Target element is where ajax content will be loaded. It should be an unique element.
*
*/

function awcs_get_target_element() {

    var target = '';

    if( jQuery(window.top.document).find(awcs_wc_global_params.target_element).length ) {

        target = jQuery(window.top.document).find(awcs_wc_global_params.target_element).first();
    
    } else if( jQuery(window.top.document).find('main').length ) {

        target = jQuery(window.top.document).find('main').first();
    
    } else {

        if( top.location.href.indexOf('/wp-admin/') >=0 ) {

            return false;
        
        } else {

            /**
            *
            * Show alert for debugging purpose
            *
            * window.alert('Target element is missing. Please contact with site admin.');
            *
            */

        }

        return false;
    }

    return target;
}

function awcs_set_target_element( target ) {

    if( typeof awcs_target === 'undefined' || ( awcs_target != target ) ) {

        window.awcs_target = target;

    }

    return;
}

function awcs_adjust_target_element( $this ) {

    var target = awcs_get_target_element();

    /**
    *
    * $this will be used to check for single page shopping
    *
    */

    awcs_set_target_element( target );
}

// Default
awcs_set_target_element( awcs_get_target_element() );