/**
* File awcs-common-functions.min.js
* Common functions
*/
function awcs_insert_body_classes(a){jQuery(document.body).addClass(a)}var block=function(a){is_blocked(a)||a.addClass("awcs_loading").block({message:awcs_wc_global_params.ajax_loading_img,css:{backgroundColor:"transparent",border:0,lineHeight:"32px",width:"32px",height:"32px"},overlayCSS:{background:"#fff",opacity:.6}})},unblock=function(a){a.removeClass("awcs_loading").unblock()},is_blocked=function(a){return a.is(".awcs_loading")||a.parents(".awcs_loading").length};
function awcs_hide_variation_data(a){var b=a.find(".reset_variations");a.find(".single_add_to_cart_button, .awcs_loop_variation_add_to_cart_button").is(".disabled")||(b.css("visibility","hidden"),a.find(".single_add_to_cart_button, .awcs_loop_variation_add_to_cart_button").removeClass("wc-variation-is-unavailable").addClass("disabled wc-variation-selection-needed"),a.find(".woocommerce-variation-add-to-cart").removeClass("woocommerce-variation-add-to-cart-enabled").addClass("woocommerce-variation-add-to-cart-disabled"))}
;