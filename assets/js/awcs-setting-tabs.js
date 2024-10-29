/**
*
* File awcs-setting-tabs.js
*
* Setting Tab Functions
*
* Admin setting save actions
*
*/

(function ($) {

	$(document).ready(function() {

		$( '#awcs-settings-tabs' ).tabs();

		awcs_save_settings( 'awcs-general-settings' );

	});

})(jQuery);

// Saving settings with AJAX
function awcs_save_settings(section) {

    jQuery("#"+section).submit(function() { 

    	jQuery('.awcs-admin-notification').css("padding", "10px").fadeIn('medium');

        jQuery('.awcs-admin-notification').html('<div class="text-info" role="status">' + awcs_admin_setting_params.ajax_loading_img + '<span class="sr-only" style="margin-left: 10px;">Saving ...</span></div>').fadeIn("slow");

        jQuery("html, body").animate({ scrollTop: 0 }, 1200);

        jQuery(this).ajaxSubmit({

            success: function() {               

                jQuery('.awcs-admin-notification').hide().fadeIn("slow").html('<span style="margin-right: 10px;">&#10004;</span><span style="font-weight: bold;">Settings Successfully Saved</span>');

            }

        }); 
            
        setTimeout("jQuery('.awcs-admin-notification').fadeOut('slow')", 12000);
        return false; 

    });

}