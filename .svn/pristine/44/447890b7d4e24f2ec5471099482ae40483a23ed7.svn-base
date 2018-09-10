jQuery(function ($) {
    if (jQuery('#ship-to-different-address-checkbox').is(':checked')) {
        jQuery("#shipping_state").change(function () {
        wf_edd_ajax_call();
        });
    } else {
        jQuery("#billing_state").change(function () {
        wf_edd_ajax_call();
        });
    }
});


function wf_edd_ajax_call(){
    jQuery.ajax({
        type: 'post',
        url: wc_checkout_params.ajax_url,
        data:
        {
            action: 'wf_estimated_delivery',
        }
    });
}