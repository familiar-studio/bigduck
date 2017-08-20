var loadTracker = function( host ) {
    
    var referrer =  document.referrer;
    var utm_campaign = getUrlParameter('utm_campaign');
    var utm_content = getUrlParameter('utm_content');
    var utm_source = getUrlParameter('utm_source');
    var utm_medium = getUrlParameter('utm_medium');

    var data = {
        action: 'process_loadtracker',
        nonce: AjaxTrackerController.nonce,
        referrer: referrer,
        host: host,
        utm_campaign: utm_campaign,
        utm_content: utm_content,
        utm_source: utm_source,
        utm_medium: utm_medium
    };

    jQuery.ajax({
        url: AjaxTrackerController.url,
        data: data,
        type: 'POST',
        headers: { "cache-control": "no-cache" },
        cache: false,
        success: function(response) {
              
            var sessionId = response.data.tracking_code;
            var pageLink = window.location.href;
            var pageTitle = document.title ? document.title : pageLink;
            var externalID;

            if (response.data.getexternalid_js) {

                var getexternalidJs = response.data.getexternalid_js;

                if (getexternalidJs) {
                    eval( getexternalidJs );
                }
            }

            var parms = { token:            response.data.token,
                          sessionid:        sessionId,
                          pagelink:         pageLink,
                          pagetitle:        pageTitle,
                          contentid:        '',
                          referrer:         response.data.referrer,
                          urlroot:          response.data.host,
                          utm_campaign:     response.data.utm_campaign,
                          utm_content:      response.data.utm_content,
                          utm_source:       response.data.utm_source,
                          utm_medium:       response.data.utm_medium,
                          external_id:      externalID,
                          external_source:  response.data.external_source,
            };

            jQuery.ajax( {
                url: response.data.tracking_url + "/api/v1/pagehit",
                data: parms,
                cache: false,
                success: function( response ){
                    
                    if (response) {
                        name = 'nfsession';
                        value = response;
                        days = 3650; 
                        var expires;

                        if (days) {
                            var date = new Date();
                            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                            expires = "; expires=" + date.toGMTString();
                        } else {
                            expires = "";
                        }
                        document.cookie = escape(name) + "=" + escape(value) + expires + "; path=/";
                    }

                }
            });


        },
    });

}

var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
};