(function($) {

    $.nfListView = function(element, options) {

        // plugin's default options
        // this is private property and is  accessible only from inside the plugin
        var defaults = {

            app: 		'',
            method: 	'',
            parms: 		'',
            id: 		element.id,
            hash:		'',
            datasrc: 	'/consolemgr/json.php',
            datatype: 	'json',
            datactype:	'application/json',
            timeout: 	15000
        }

        // to avoid confusions, use "plugin" to reference the current instance of the object
        var plugin = this;
		plugin.thisId = element.id;

        // this will hold the merged default, and user-provided options
        // plugin's properties will be available through this object like:
        // plugin.settings.propertyName from inside the plugin or
        // element.data('pluginName').settings.propertyName from outside the plugin, where "element" is the
        // element the plugin is attached to;
        plugin.settings = {}

        var $element = $(element),  // reference to the jQuery version of DOM element the plugin is attached to
             element = element;        // reference to the actual DOM element

        // the "constructor" method that gets called when the object is created
        plugin.init = function() {

            // the plugin's final properties are the merged default and user-provided options (if any)
            plugin.settings = $.extend({}, defaults, options);

            // code goes here
            plugin.setupListView();

        }

        // public methods
        plugin.setupListView = function() {
			
			$.ajax({	url: 			plugin.settings.datasrc,
						dataType: 		plugin.settings.datatype,
						type: 			'GET',
						contentType: 	plugin.settings.datactype,
						data: 			 {	'jsonrpc': 	'2.0',
											'app': 		plugin.settings.app,
											'method': 	plugin.settings.method,
											'parms': 	plugin.settings.parms,
											'id': 		plugin.settings.id,
											'uid': 		plugin.settings.uid
					 					 },
						timeout: 		 4000,
						success: 		 plugin.buildListView,
						error:		 	plugin.retryListView
			}); 
			
			var containerObj 	= $('#'+plugin.settings.id);
			containerObj.addClass( 'loadingblock' );
		}
		
		
		plugin.updatelistView = function( parms ) {
			
			var cancelUpdate;
			
			$.each(parms, function(parmname,newvalue){
				
				if (parmname == 'action')
				{
					switch( newvalue )
					{	
						case 'D':
							if (!confirm("Are you sure you want to delete this?")) 
							{ 
								cancelUpdate = true;
								return false;
							}
						break;
					}
				}
				
				plugin.settings.parms[parmname] = newvalue;
			});
						
			if (!cancelUpdate)
			{
				plugin.setupListView();	
			}
		}
		
		plugin.retryListView = function( )
		{
			plugin.setupListView();
		}
		
		plugin.buildListView = function( response ) {
			
			top.CMSConsole.updateStatus();
			plugin.settings.parms['action'] = '';
			
			if (!response)
			{
				var containerObj 	= $('#'+plugin.settings.id);
		   		containerObj.removeClass( 'loadingblock' );

				containerObj.html( 'Error loading data' );
		   		return;
			}
			
			var resultColumns 	= response.columns;
			var resultRows		= response.rows;
			var resultTotal		= response.total;
			var resultPage		= response.page;
			var resultLimit		= response.limit;
			var resultSort		= response.sort;
			var resultSortDir	= response.sortdir;
			var resultListId	= response.id;
			
			var containerObj 	= $('#'+resultListId);
			var updateUrl 		= 'javascript:$(\'#'+resultListId+'\').data(\'nfListView\').updatelistView(';
			 
			if (resultColumns && resultRows[0])
			{	
				
				containerObj.removeClass( 'loadingblock' );

				var containerTableHtml = '<div class="listview-wrapper"><div class="loading-overlay"></div><table border="0" cellpadding="0" cellspacing="0" class="listview-table"><thead></thead><tbody></tbody></table></div><div class="pnav"></div>';
		   		containerObj.html( containerTableHtml );
			
				var containerHeaderHtml = '<tr>';
				
				$.each(resultColumns, function(i,column){
		     		
		     		if (column['width'])
					{
						var widthVal = ' style="width:'+column['width']+'px!important;" ';
					}
					
					else
					{
						var widthVal = '';
					}
					
					if (resultSort == column['sortfield'])
		     		{
		     			if (resultSortDir == 'asc')
		     			{
		     				containerHeaderHtml += '<th'+widthVal+' class="desc"><a href="' + updateUrl + '{\'sort\':\''+column['sortfield']+'\',\'sortdir\':\'desc\'})\">' + column['label'] + '</a></th>';
		     			}
		     			
		     			else
		     			{
		     				containerHeaderHtml += '<th'+widthVal+' class="asc"><a href="' + updateUrl + '{\'sort\':\''+column['sortfield']+'\',\'sortdir\':\'asc\'})\">' + column['label'] + '</a></th>';
		     			}
		     			
		     		}
		     		
		     		else if (column['sortfield'])
		     		{
		     			containerHeaderHtml += '<th'+widthVal+'><a href="' + updateUrl + '{\'sort\':\''+column['sortfield']+'\'})\">' + column['label'] + '</a></th>';
		     		}
		     		
		     		else if (column['hidelabel'])
		     		{
						containerHeaderHtml += '<th'+widthVal+'></th>';
		     		}
					
					else
					{
						containerHeaderHtml += '<th'+widthVal+'>' + column['label'] + '</th>';
		     		}
		     	});
		     	
		     	containerHeaderHtml += '</tr>';
		     	$('table thead',containerObj).html( containerHeaderHtml );
		
			
				if (resultRows[0])
				{	
					var containerRowsHtml = '';
					
					$.each(resultRows, function(i,row){
			     		
			     		var rowClass = (i % 2) ? 'row1' : 'row2';
			     		containerRowsHtml += '<tr class="' + rowClass + '">';
						
						$.each(resultColumns, function(i,column){
							
							var widthVal = '';
							
							if (column['width'])
							{
								widthVal = 'width:'+column['width']+'px!important;overflow:hidden;';
							}
							
							if (column['align'])
							{
								var cellStyle = ' style="'+widthVal+'text-align:' + column['align'] + '" ';
							}
							
							else
							{
								var cellStyle = ' style="' + widthVal + '" ';
							}
							
							if (row[column['label']]['action'])
							{
								switch( row[column['label']]['action'] )
								{
									case 'D':
									
									if ( row[column['label']]['id'] )
									{
			     						containerRowsHtml += '<td'+cellStyle+'><a href="' + updateUrl + '{\'action\':\'D\',\'id\':' + row[column['label']]['id'] + '})"><img style="margin-right:0px;" src="/consolemgr/images/icon_delete.png" border="0" width="10" /></a></td>';
									} 
									
									break;
								}
							
							}
							
							else if (row[column['label']]['link'])
							{
			     				containerRowsHtml += '<td'+cellStyle+'><a href="' + row[column['label']]['link'] + '">' + row[column['label']]['value'] + '</a></td>';
							}
			     			
			     			else
			     			{
			     				containerRowsHtml += '<td'+cellStyle+'>' + row[column['label']]['value'] + '</td>';
				     		}
				     	});
				     					
			     		containerRowsHtml += '</tr>';
			     	});
			     	
				 	
				 	$('table tbody', containerObj).html( containerRowsHtml );
			     
			     }
			}
						
		    else
		    {
		    	containerObj.html( 'None found' );
		    }
		    
		    
		    if (resultTotal && resultPage)
			{
				var containerPagesHtml = '';
						
				numPages = Math.round(parseFloat(resultTotal / resultLimit));
				
				if (resultPage == 1)
				{
					var startAt = 1;
				}
				
				else if ( resultPage == numPages)
				{
					var startAt = ( numPages > 5 ) ? (numPages-5) : 1;
				}
				
				else
				{
					var startAt = ( parseInt(resultPage) > 5 ) 	? (parseInt(resultPage)-2) : 1;
				}
										
				startAt = parseInt(resultPage) - 2;
				endAt 	= parseInt(resultPage) + 2;
				endLink = '';
				startLink = '';
				
				if ( startAt < 1 )
				{
					startAt = 1;
					
					if (numPages < 5)
					{
						endAt = numPages;
					}
					
					else
					{
						endAt = 5;
					}
				}
				
				if ( endAt > numPages )
				{
					endAt = numPages;
				}
				
				if ( startAt > 1 )
				{
					startLink = '<a href="' + updateUrl + '{\'page\':'+1+'})\">' + 1 + '...</a>';
				}
				
				if ( endAt < numPages )
				{
					endLink = '<a href="' + updateUrl + '{\'page\':'+numPages+'})\">...' + numPages + '</a>';
				}
									
				containerPagesHtml += startLink;
				
				for (i=startAt;i<=endAt;i++)
				{
					if (resultPage == i)
					{
						containerPagesHtml += ' <a class="active" href="' + updateUrl + '{\'page\':'+i+'})\">' + i + '</a>';
					}
					
					else
					{
						containerPagesHtml += ' <a href="' + updateUrl + '{\'page\':'+i+'})\">' + i + '</a>';
					}
				}
				
				containerPagesHtml += endLink;
				
				$('.pnav', containerObj).html( containerPagesHtml );
			}
		
		}
        
        // fire up the plugin!
        // call the "constructor" method
        plugin.init();

    }

    // add the plugin to the jQuery.fn object
    $.fn.nfListView = function(options) {

        // iterate through the DOM elements we are attaching the plugin to
        return this.each(function() {

            // if plugin has not already been attached to the element
            if (undefined == $(this).data('pluginName')) {

                // create a new instance of the plugin
                // pass the DOM element and the user-provided options as arguments
                var plugin = new $.nfListView(this, options);

                // in the jQuery version of the element
                // store a reference to the plugin object
                // you can later access the plugin and its methods and properties like
                // element.data('pluginName').publicMethod(arg1, arg2, ... argn) or
                // element.data('pluginName').settings.propertyName
                $(this).data('nfListView', plugin);

            }

        });

    }

})(jQuery);



