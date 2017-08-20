var pageTabIndex = 2000;

var loadAjaxForm = function( form_id, random_id, page_title ) {
    
    //  Prepare the data to send to the remote 
    //  form rendering ajax call
    var data = {
        action: 'process_loadform',
        nonce: AjaxController.nonce,
        form_id: form_id,
        random_id: random_id
    };

    //  Pass the post-title dynamically
    var ajax_url = AjaxController.url;
    
    if (ajax_url.indexOf("?") >= 0) {
        ajax_url += '&post_title=' + encodeURIComponent( page_title );
    } else {
        ajax_url += '?post_title=' + encodeURIComponent( page_title );
    }

    //  Apply the loading class to the container
    jQuery('#gform_' + random_id ).addClass( 'ajaxcontent-loading' );

    //  Make the remote ajax call
    jQuery.ajax({
        url: ajax_url,
        data: data,
        type: 'POST',
        dataType: "json",
        headers: { "cache-control": "no-cache" },
        cache: false,
        success: function(response) {
            
            //  Get the response HTML
            var fieldsContainer = jQuery(".gform_body", response.data.form_html);
            var scriptsElements = jQuery( response.data.form_javascript );
            
            //  Get the original form ID
            var originalRandomId = response.data.original_random_id;

            //  Replace the form contents
            jQuery('#gform_' + originalRandomId + ' .gform_body').html( fieldsContainer );
            jQuery('#gform_' + originalRandomId).append( scriptsElements );

            //  Remove the loading class
            jQuery('#gform_' + originalRandomId ).removeClass( 'ajaxcontent-loading' );

            //  Update the tab index on the fields in the loaded form
            jQuery('#gform_' + originalRandomId + ' [tabindex]').each( function(){

                ++pageTabIndex;

                jQuery( this ).attr( 'tabindex', pageTabIndex );
            });

            //  On form load, lets make sure that any previously selected checkboxes
            //  get checked, and the [selected] flag removed from the value.
            jQuery('#gform_' + originalRandomId + ' input[value*=\'[selected]\']').each( function(){
                jQuery( this ).attr( 'checked', true );
                newVal = jQuery( this ).val().replace('[selected]','');
                jQuery( this ).val(newVal);
            });
        },
    });

}

//  On form load, lets make sure that any previously selected checkboxes
//  get checked, and the [selected] flag removed from the value. We're doing this
//  here to make sure validation-failure driven form loads are also processed. 
jQuery(document).bind('gform_post_render', function(e, formId, current_page){
    
    //  Check the selected checkboxes
    jQuery('#gform_' + formId + ' input[value*=\'[selected]\']').each( function(){
        jQuery( this ).attr( 'checked', true );
        newVal = jQuery( this ).val().replace('[selected]','');
        jQuery( this ).val(newVal);
    });

});
