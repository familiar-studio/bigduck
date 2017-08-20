if (typeof(jQuery) != 'undefined')
{
	jQuery( document ).on( 'keyup change paste', 'input[name="post_title"]', function(){

		top.CMSConsole.editorState = true;
		top.CMSConsole.setTempTitle( jQuery(this).val() );

	});

	jQuery( document ).on( 'keyup change paste', 'textarea.wp-editor-area[name="content"]', function(){

		top.CMSConsole.editorState = true;
		top.CMSConsole.setTempContent( jQuery(this).val() );

	});

	jQuery( document.body ).on('click', 'a', function(event) {
	    if ( jQuery(this).attr('href') !== "" ) {
			
	    	var targetLink = jQuery(this).attr('href');

	    	
	    	if (targetLink.indexOf("?tab") != -1)
	    	{
	    		return;
	    	}

	    	if (targetLink.indexOf(".php") == -1)
	    	{
	    		return;
	    	}

	    	if (targetLink.indexOf("action=logout") != -1)
	    	{
	    		window.top.location.href = targetLink; 

	    	}

	    	if (jQuery(this).hasClass('nav-tab-link'))
	    	{
	    		return;
	    	}

	    	if (jQuery(this).hasClass('menu-top'))
	    	{

	    		var _jQuery = jQuery;

	    		jQuery('.wp-has-current-submenu' ).each(function(){
	    			_jQuery(this).removeClass( 'wp-has-current-submenu' );
	    			_jQuery(this).parent().removeClass( 'wp-menu-open' );
	    			_jQuery(this).parent().removeClass( 'selected' );
	    			_jQuery(this).parent().parent().removeClass( 'selected' );
	    		});

	    		jQuery(this).addClass( 'wp-has-current-submenu' );
				jQuery(this).parent().addClass( 'wp-has-current-submenu' );
	    	}

			event.preventDefault();

			if (targetLink.indexOf("/wp-admin/") != -1)
	    	{
	    		top.CMSConsole.loadWorkspace( targetLink );
	    	}

	    	else
	    	{
	    		top.CMSConsole.loadWorkspace( '/wp-admin/' + targetLink );
	    	}
	    }
	});

	jQuery(document.body).keyup(function(e) {
		if( e.keyCode == 27 ) {
			top.CMSConsole.toggleConsole();
		}
	});
}