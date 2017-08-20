var loadGatedContent = function( container, args, instance ) {
    var data = {
        container: container,
        action: 'getgatedcontent',
        ajax: true
    };
    
    var requestUrl = jQuery(location).attr('href');

    if (requestUrl.indexOf("?") >= 0) {
        requestUrl += '&ajax=true&guid=' + guid();
    } else {
        requestUrl += '?ajax=true&guid=' + guid();
    }

    jQuery('#'+container).addClass('ajaxcontent-loading');

    jQuery('html,body').animate({
       scrollTop: jQuery('#'+container).offset().top - 100
    });

    jQuery.ajax({
        url: requestUrl,
        data: data,
        type: 'POST',
        headers: { "cache-control": "no-cache" },
        cache: false,
        dataType : 'html',
        success: function(response) {

            var dataParts = deparam( jQuery(this)[0].data );

            var newGatedContent = jQuery("#" + dataParts.container, response );

            jQuery('#'+dataParts.container).html(newGatedContent.html());

            jQuery('#'+container).removeClass('ajaxcontent-loading');
        },
    });

}

deparam = function (querystring) {
    
    querystring = querystring.substring(querystring.indexOf('?')+1).split('&');
    var params = {}, pair, d = decodeURIComponent, i;
    
    for (i = querystring.length; i > 0;) {
        pair = querystring[--i].split('=');
        params[d(pair[0])] = d(pair[1]);
    }

    return params;
};

guid = function () {

    var d = new Date().getTime();
    if(window.performance && typeof window.performance.now === "function"){
        d += performance.now(); //use high-precision timer if available
    }
    var uuid = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
        var r = (d + Math.random()*16)%16 | 0;
        d = Math.floor(d/16);
        return (c=='x' ? r : (r&0x3|0x8)).toString(16);
    });
    return uuid;

  
}
