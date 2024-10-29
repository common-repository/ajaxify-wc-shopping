/**
* File awcs-manage-target-element.min.js
* Get / Set / Adjust target element
* Target element is where ajax content will be loaded. It should be an unique element.
*/
function awcs_get_target_element(){if(jQuery(window.top.document).find(awcs_wc_global_params.target_element).length)var a=jQuery(window.top.document).find(awcs_wc_global_params.target_element).first();else if(jQuery(window.top.document).find("main").length)a=jQuery(window.top.document).find("main").first();else return top.location.href.indexOf("/wp-admin/"),!1;return a}function awcs_set_target_element(a){if("undefined"===typeof awcs_target||awcs_target!=a)window.awcs_target=a}
function awcs_adjust_target_element(a){a=awcs_get_target_element();awcs_set_target_element(a)}awcs_set_target_element(awcs_get_target_element());