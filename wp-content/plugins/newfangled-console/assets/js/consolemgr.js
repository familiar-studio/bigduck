function saveActionComplete()
{	
	if (editListener == true)
	{
		jQueryConsole( document.body ).animate({'opacity': 0});
		CMSConsole.shrinkWorkspace( '900' );
		window.top.location.reload();
		editListener = false
	}
}

var editListener = false;

if (typeof(jQuery) != 'undefined')
{
	jQueryConsole = jQuery;
	var consoleID = 41302;
	jQueryConsole(function() {
		jQueryConsole( document.body ).on('click', 'a.edit-option', function(event) {
		    if ( jQueryConsole(this).attr('href') !== "" ) {
				event.preventDefault();
		    	CMSConsole.loadWorkspaceEditor( jQueryConsole(this).attr('href') + '&editorState=1' );
		    }
		});

		jQueryConsole( document.body ).on('click', 'a.admin-link', function(event) {
		    if ( jQueryConsole(this).attr('href') !== "" ) {
				event.preventDefault();
		    	CMSConsole.loadWorkspace( jQueryConsole(this).attr('href') );
		    	editListener = true;
		    }
		});

		editMenuObj = jQueryConsole('#console-edit-menu').detach();
		jQueryConsole('body').append(editMenuObj);
		var activeEditLink;

		jQueryConsole( document.body ).on('mouseover', '.post-edit-link', function(event) {

			var editOptions = jQueryConsole(this).attr('rel-options');

		    if ( editOptions ) {

		    	editOptions = decodeURIComponent( editOptions );
		    	editOptionsObj = jQueryConsole.parseJSON( editOptions );
		    	
		    	if (editOptionsObj)
		    	{
		    		consoleEditMenu = jQueryConsole('#console-edit-menu');

		    		var ePos = jQueryConsole(this).offset();

		    		var eTop = ePos.top + 12.5;
		    		var eLeft = ePos.left + 12.5;

		    		consoleEditMenu.empty();

		    		consoleEditMenu.append( '<div class="console-edit-menu-top"></div>' );

		    		jQueryConsole( editOptionsObj ).each( function( val, option ){ 
		    			consoleEditMenu.append( '<a rel="' + option['rel'] + '" class="' + option['class'] + '" href="' + option['link'] + '">' + option['desc'].replace( '+', ' ' ).replace( '+', ' ' ).replace( '+', ' ' ) + '</a>' );
		    		});

		    		newWidth = consoleEditMenu.css('width', 'auto').width() + 20;
		    		consoleEditMenu.css('width', '20px')

		    		newHeight = consoleEditMenu.css('height', 'auto').height() + 0;
		    		consoleEditMenu.css('width', '20px')

		    		consoleEditMenu.css( 'width', 	'20px');
		    		consoleEditMenu.css( 'height', 	'20px');
		    		consoleEditMenu.css( 'opacity', '0');

					consoleEditMenu.css( 'display', 'block');
		    		consoleEditMenu.css( 'top', 	eTop + 'px');
		    		consoleEditMenu.css( 'left', 	( eLeft )+ 'px');

		    		consoleEditMenu.animate( {'width': newWidth, 'height': newHeight, 'opacity': 1 }, 100);

		    		jQueryConsole(this).addClass('menu-open');
		    		activeEditLink = this;
		    	}

				event.preventDefault();
		    //	CMSConsole.loadWorkspaceEditor( jQueryConsole(this).attr('href') + '&editorState=1' );
		    
		    }
		});

		jQueryConsole( document.body ).on('mouseleave', '#console-edit-menu', function(event) {
		    consoleEditMenu.css( 'display', 'none');
		    jQueryConsole(activeEditLink).removeClass('menu-open');

		});
	});
}

jQueryConsole('body').addClass('console-invisible');

var CMSConsole;

function initConsole() {

	if (top.CMSConsole) {
		CMSConsole = top.CMSConsole;
		CMSConsole.resetKeyListeners();
		CMSConsole.updateStatus();
	}
	
	else if( consoleID ) {
		CMSConsole = new ConsoleMgr(consoleID);
		CMSConsole.consoleID = consoleID;
		CMSConsole.resetKeyListeners();
		focusTop();	
	}	
}

var ConsoleMgr = (function ( consoleID ) {
    
	var consoleHistory;
	var consoleToolbar;
	var consoleWorkspace;
	var consoleDimm;
	var Page;
	var PageBody;
	var consoleStatus;
	var searchProcess;
	var toolbarCollapsed;
	var toolbarLoaded;
	var searchQueue;
	var activeSection;
	var editorState;
	var consoleVisibility;
	
	var history = new Array();
	var returnOnClose;
	var console_active_class = 'console-active';
	
	//---------------------------------------------------------------------------------------------------------
	//	Public functions
	//---------------------------------------------------------------------------------------------------------
	var public = function ( consoleID ) {
    	
	    //---------------------------------------------------------------------------------------------------------
		//	Constructor
		//---------------------------------------------------------------------------------------------------------
		if (consoleID && !consoleToolbar)
		{
			initEnviroment( consoleID );
		}
		
		//---------------------------------------------------------------------------------------------------------
		//	Set all the windows to be listening
		//---------------------------------------------------------------------------------------------------------
		this.resetKeyListeners = function() {
			
			jQueryConsole(top.window.document).unbind('keyup');
			jQueryConsole(consoleToolbar.iframe.document).unbind('keyup');
			jQueryConsole(consoleWorkspace.iframe.document).unbind('keyup');
			
			jQueryConsole(top.window.document).keyup(function(e) {
				if( e.keyCode == 27 ) {
					toggleConsoleVisibility();
				}
			});
			
			jQueryConsole(consoleToolbar.iframe.document).keyup(function(e) {
				if( e.keyCode == 27 ) {
					toggleConsoleVisibility();
				}
			});
			
			jQueryConsole(consoleWorkspace.iframe.document).keyup(function(e) {
				if( e.keyCode == 27 ) {
					toggleConsoleVisibility();
				}
			});

			jQueryConsole(PageWindow).resize(function(e) {
								
				if( consoleWorkspace.fullscreen ) {
					heightVal = ( jQueryConsole(top).height() ) - 0;
					consoleWorkspace.css( 'height',heightVal );
				}
				
				else
				{
					heightVal = ( jQueryConsole(top).height() ) - 0;
					consoleWorkspace.css( 'height',heightVal );
				}
			});

			jQueryConsole(consoleDimm).mousedown(function(e) {
				hideConsole();
			});
		};	
		
		//---------------------------------------------------------------------------------------------------------
		//	Public: Highlight the active section in the toolbar
		//---------------------------------------------------------------------------------------------------------
		this.setActiveSection = function( idVal ) {
		
			if (activeSection)
			{
				var toolbarObj 	= jQueryConsole(consoleToolbar.iframe.document);
				var sectionObj  = jQueryConsole('.' + activeSection, toolbarObj);
				
				sectionObj.removeClass('toolbar-block-active');
			}
			
			if (idVal)
			{
				var toolbarObj 	= jQueryConsole(consoleToolbar.iframe.document);
				var sectionObj  = jQueryConsole('.' + idVal, toolbarObj);
				
				if (sectionObj)
				{
					sectionObj.addClass('toolbar-block-active');
					activeSection = idVal;
				}
			}
		};
		
		//---------------------------------------------------------------------------------------------------------
		//	Public: Change the width of the workspace
		//---------------------------------------------------------------------------------------------------------
		this.growWorkspace = function( increase ) {
			
			consoleWorkspace.animate({
				width: ( consoleWorkspace.width() + increase )
			});
		};
		
		//---------------------------------------------------------------------------------------------------------
		//	Public: Change the width of the workspace
		//---------------------------------------------------------------------------------------------------------
		this.shrinkWorkspace = function( decrease ) {
			
			consoleWorkspace.animate({
				width: ( consoleWorkspace.width() - decrease )
			});
		};
		
		//---------------------------------------------------------------------------------------------------------
		//	Public: 
		//---------------------------------------------------------------------------------------------------------
		this.hideToolbar = function() {
			
			$('#workspace-tools', consoleWorkspace).hide();
		};
		
		//---------------------------------------------------------------------------------------------------------
		//	Public: 
		//---------------------------------------------------------------------------------------------------------
		this.showToolbar = function() {
			
			$('#workspace-tools', consoleWorkspace).show();
		};

		//---------------------------------------------------------------------------------------------------------
		//	Public: Show/load the workspace overlay
		//---------------------------------------------------------------------------------------------------------
		this.loadWorkspace = function( url, widthVal, heightVal, postVars, disableAnimation, disableHistory, maintainCollapesdState  ) {
		
		//	showConsole();

			consoleWorkspace.iframe.location.replace( 'about:blank' );

		 	consoleToolbar.addClass( 'notransition' );

			if (!toolbarLoaded)
		  	{
		  		jQueryConsole('#consoleToolbar iframe').css('opacity',0);
				loadToolbar( nfConsoleRoot + '/wp-admin/index.php?console=1&id='+this.consoleID+'&collapsed=' + ( toolbarCollapsed ? 1 : 0 ) );  
				toolbarLoaded = true;	
		  	}

		  	consoleToolbar.removeClass( 'toolbar-collapsed' );
		  	consoleToolbar.removeClass( 'toolbar-collapsed-editorstate' );



		   	PageBody.addClass(console_active_class);
		  	consoleToolbar.css( 'marginLeft', '0px' );

			if (!disableHistory)
			{
				var encodeurl = encodeURIComponent(url);
				history.push( encodeurl+','+widthVal+','+heightVal+','+postVars );
			}
				
			if (!widthVal || !heightVal)
			{
				returnOnClose = url+','+widthVal+','+heightVal+','+postVars;
				widthVal  = ( jQueryConsole(top).width()  ) - 50;
			//	heightVal = ( jQueryConsole(top).height() ) - 0;
				heightVal = ( jQueryConsole(top).height() ) - 0;
				consoleWorkspace.fullscreen = true;
			}
			
			else
			{
				consoleWorkspace.fullscreen = false;
				heightVal = ( jQueryConsole(top).height() ) - 0;
			}
			
			if (!toolbarLoaded)
		  	{
				loadToolbar( nfConsoleRoot + '/wp-admin/index.php?console=1&id='+this.consoleID+'&collapsed=' + ( toolbarCollapsed ? 1 : 0 ) );  
			//	top.CMSConsole.loadWorkspace('/wp-admin/index.php','800','650', '', '', 1 );
				toolbarLoaded = true;	
		  	}

			if (toolbarCollapsed) {
				
				if (maintainCollapesdState)
				{
					this.expandToolbar(1);
				}
				
				else
				{
					this.expandToolbar(1);
				}
				
			}
			
			consoleDimm.css('display','block');
	  		consoleDimm.animate({'opacity': 1}, 250);

			jQueryConsole('#wrapper').addClass('overlay-blur');
			consoleToolbar.css('opacity','100');
			consoleToolbar.css('margin-left','0px');
			consoleToolbar.css('display','block');
			PageBody.removeClass('editorstate-active');

			jQueryConsole('#iframe-wrapper').animate({opacity:0}, 100);
			jQueryConsole('#workspace-tools').css({'opacity': 0});


		 	searchProcess = '';
		 	
		 	if (postVars) {
		 		
		 		var myForm = document.createElement("form");
				myForm.method="post" ;
				myForm.action = url ;
				myForm.target = "consoleWorkspaceIframe" ;
				
				var postVarsArray = {};
				postVars.replace(
					new RegExp("([^?=&]+)(=([^&]*))?", "g"),
					function($0, $1, $2, $3) { postVarsArray[$1] = $3; }
				);
				
				for (var name in postVarsArray) {
					var myInput = document.createElement("input") ;
					myInput.setAttribute("name", name) ;
					myInput.setAttribute("value", postVarsArray[name]);
					myForm.appendChild(myInput) ;
				}

				document.body.appendChild(myForm) ;		
		 		
		 		PageBody.css('overflow', 'hidden');
			 	consoleWorkspace.fadeIn( 'fast' );
			  	consoleWorkspace.animate({
		    				width: widthVal
		  				}, 250, function(){
		  			
		  			myForm.submit() ;
					document.body.removeChild(myForm) ;
		  		});		  		
		 	}
		 	
		 	else if (disableAnimation) {
			 	PageBody.css('overflow', 'hidden');
			 	consoleWorkspace.show();
			 	consoleWorkspace.css('width', '80%' );


		 		consoleWorkspace.iframe.location.replace( nfConsoleRoot+  url );
		 	}
		 	
		 	
		 	else {
			 	PageBody.css('overflow', 'hidden');
			 	consoleWorkspace.show();
			  	consoleWorkspace.animate({'width': '85%' }, 200 );
		  		
			 	
			 	consoleWorkspace.iframe.location.replace( nfConsoleRoot + url );
		  	}		


		 	consoleToolbar.removeClass( 'notransition' );

		};

		this.fullScreenWorkspace = function(){
						  	consoleWorkspace.animate({'width': '95%' }, 200 );

		}

		//---------------------------------------------------------------------------------------------------------
		//	Public: Show/load the workspace overlay
		//---------------------------------------------------------------------------------------------------------

		this.loadWorkspaceEditor = function( url, widthVal, heightVal, postVars, disableAnimation, disableHistory, maintainCollapesdState  ) {
			
			createCookie('editing', 1);

			return this.loadWorkspace( url, widthVal, heightVal, postVars, disableAnimation, disableHistory, maintainCollapesdState );

			if (!toolbarLoaded)
		  	{
		  		jQueryConsole('#consoleToolbar iframe').css('opacity',0);
				loadToolbar( nfConsoleRoot + '/wp-admin/index.php?console=1&id='+this.consoleID+'&collapsed=' + ( toolbarCollapsed ? 1 : 0 ) );  
				toolbarLoaded = true;	
		  	}

		  	jQueryConsole('#consoleToolbar').css('height','100%');
		  	consoleToolbar.addClass( 'toolbar-collapsed-editorstate' );


			createCookie('console', 1);
			consoleStatus = 1;
				
			consoleDimm.css('display','block');
	  		consoleDimm.animate({'opacity': 1}, 250);

	  		jQueryConsole('#iframe-wrapper').css('opacity',0);
			jQueryConsole('#workspace-tools').animate({'opacity': 1});
		 	PageBody.css('overflow', 'hidden');
			PageBody.addClass('editorstate-active');

			jQueryConsole('#iframe-wrapper').animate({opacity:0}, 100);

			consoleWorkspace.css('width', '0px');
			consoleWorkspace.css('left', '52px');
			consoleWorkspace.show();
			consoleWorkspace.animate({'width': '80%' }, 200, function(){
		//		jQueryConsole('#iframe-wrapper').animate({'opacity': 1});
			} );
			
			PageBody.animate({'margin-right': '-133px', 'margin-left': '133px' }, 200);

		 	consoleWorkspace.iframe.location.replace( nfConsoleRoot + url );
		
		    editListener = true;
		    CMSConsole.editorState = true;
		};
		
		//---------------------------------------------------------------------------------------------------------
		//	Public: Hide workspace overlay
		//---------------------------------------------------------------------------------------------------------
		this.closeWorkspace = function()  {
		 	
		 	consoleWorkspace.fullscreen = false;
		 	consoleWorkspace.css('width', '0');

		  	consoleDimm.animate({'opacity': 0}, 250, function(){
			  	consoleDimm.css('display','none');
		  	});

		  	jQueryConsole('#wrapper').removeClass('overlay-blur');
		  	consoleToolbar.removeClass( 'toolbar-collapsed-editorstate' );

			PageBody.css('overflow', 'auto');

			returnOnClose = '';
		 	searchProcess = '';
		 	history = new Array();
			focusTop();
			this.setActiveSection('');
		};
		
		//---------------------------------------------------------------------------------------------------------
		//	Public: Send the workspace history back
		//---------------------------------------------------------------------------------------------------------
		this.backWorkspace = function()  {
		 	
		 	var currentPage = '';
		 	var previousPage = '';
		 	var historyParts = new Array();
		 	
		 	currentPage  = history.pop();
		 	previousPage = history.pop();
		 	
		 	if (previousPage) {
		 		var historyParts = previousPage.split(',');
		 	}
		 	
		 	else {
		 		this.closeWorkspace();
		 	}
		 	
			if (historyParts[0]) {
		 		
				var url = decodeURIComponent(historyParts[0]);		 	
		 		this.loadWorkspace( url, parseInt(historyParts[1]), parseInt(historyParts[2]), historyParts[3] );
		 	}
		 	
		 	else {
		 		this.closeWorkspace();
		 	}
		};
		
		//---------------------------------------------------------------------------------------------------------
		//	Public: On complete, either go back to the last 'fullscreen' view, or close the workspace
		//---------------------------------------------------------------------------------------------------------
		this.actionComplete = function()  {
			
			if (returnOnClose) {
				
				var returnParts = returnOnClose.split(',');
				
				if (returnParts[0]) {
		 			this.loadWorkspace( returnParts[0], parseInt(returnParts[1]), parseInt(returnParts[2]), returnParts[3] );
			 	}
			 	
			 	else {
			 		this.closeWorkspace();
			 	}
			 	
				returnOnClose = returnOnClose;
			}
			
			else {
		 		top.location.reload(); 
			}
		};
		
		//---------------------------------------------------------------------------------------------------------
		//	Public: Update the status message
		//---------------------------------------------------------------------------------------------------------
		this.updateStatus = function()  {
			
		return;

			var toolbarObj 	= jQueryConsole(consoleToolbar.iframe.document);
			var statusBlock = jQueryConsole('#status-block', toolbarObj);
			
			jQueryConsole.get( nfConsoleRoot+  '/wp-content/plugins/newfangled-console/assets/getstatus.php', function(data) {
								
				if (data) {
					statusBlock.fadeOut(300);
					statusBlock.html(data);
					statusBlock.fadeIn(300);
					statusBlock.delay(500).fadeOut(2000);
				}
			});
		};
		
		//---------------------------------------------------------------------------------------------------------
		//	Public: Add a url to the history array
		//---------------------------------------------------------------------------------------------------------
		this.addHistory = function(url)  {
			
		};
		
		//---------------------------------------------------------------------------------------------------------
		//	Public: Make the workspace blank while something is processing
		//---------------------------------------------------------------------------------------------------------
		this.emptyWorkspace = function(url)  {
			
			consoleWorkspace.iframe.location.replace( 'about:blank' );		 		

		};
		
		//---------------------------------------------------------------------------------------------------------
		//	Public: Make the workspace blank while something is processing
		//---------------------------------------------------------------------------------------------------------
		this.consoleInitSearch = function(findtext)  {
			
			if (searchQueue)
			{
				clearTimeout(searchQueue);
				searchQueue = setTimeout( "CMSConsole.consoleDoSearch('" + findtext + "')", 500 );
			}
			
			else
			{
				searchQueue = setTimeout( "CMSConsole.consoleDoSearch('" + findtext + "')", 500 );
			}
			
			this.setActiveSection('section-cms');
		};
		
		//---------------------------------------------------------------------------------------------------------
		//	Public: Make the workspace blank while something is processing
		//---------------------------------------------------------------------------------------------------------
		this.consoleDoSearch = function(findtext)  {
			
			if (findtext.length<4)
			{
				return;
			}

			url = nfConsoleRoot + '/wp-content/plugins/newfangled-console/assets/search.php?findtext=' + encodeURIComponent(findtext) + '&cid=' + this.consoleID;
			this.loadWorkspace(url,'800','650', '', '', 1 );
			returnOnClose = url+',800';
		};
	
	
		//---------------------------------------------------------------------------------------------------------
		//	toggleHelp
		//---------------------------------------------------------------------------------------------------------
		this.toggleHelp = function() {
		  	
		  	var toolbarObj 	= jQueryConsole(consoleToolbar.iframe.document);
			
			helpObj = jQueryConsole('#help-details', toolbarObj);
			
			if (helpObj.css('display') == 'block')
			{
				helpObj.animate({opacity: 'toggle', height: 'toggle'}, 'fast' );
			}
		
			else
			{
				helpObj.animate({opacity: 'toggle', height: 'toggle'}, 'fast' );
			}
		};
		
		//---------------------------------------------------------------------------------------------------------
		//	expandToolbar
		//---------------------------------------------------------------------------------------------------------
		this.expandToolbar = function(set) {
		
			if (toolbarCollapsed)
			{
				if (set)
				{
					var toolbarObj = jQueryConsole(consoleToolbar.iframe.document);
					toolbarCollapsed = false;
					createCookie('toolbarCollapsed', 0);
				}
				
				consoleToolbar.removeClass( 'toolbar-collapsed' );
			  	consoleToolbar.removeClass( 'toolbar-collapsed-editorstate' );
				PageBody.addClass(console_active_class);
			}
		};
		
		//---------------------------------------------------------------------------------------------------------
		//	shrinkToolbar
		//---------------------------------------------------------------------------------------------------------
		this.shrinkToolbar = function(set) {
			
			if (toolbarCollapsed || set)
			{
				if (set)
				{
					var toolbarObj 	= jQueryConsole(consoleToolbar.iframe.document);
								
					PageBody.css('overflow', 'auto');
					consoleWorkspace.fullscreen = false;
					consoleWorkspace.iframe.location.replace( 'about:blank' );		 		
					returnOnClose = '';
					searchProcess = '';
					history = new Array();
					consoleToolbar.iframe.focus();
				
					toolbarCollapsed = true;							
					createCookie('toolbarCollapsed', 1);
				}
				
				this.closeWorkspace(1);
				$('#workspace-tools', consoleWorkspace).show();
				consoleToolbar.addClass( 'toolbar-collapsed' );
				PageBody.removeClass(console_active_class);
			  	consoleToolbar.removeClass( 'toolbar-collapsed-editorstate' );
			 	consoleWorkspace.css('width', '0');
			}
		};
		
		
		//---------------------------------------------------------------------------------------------------------
		//	Collapse/expand the toolbar panel
		//---------------------------------------------------------------------------------------------------------
		this.toggleToolbarCollapse = function( obj ) {
						
			if (toolbarCollapsed) {
				toolbarCollapsed = false;
				createCookie('toolbarCollapsed', 0);	
				consoleToolbar.removeClass( 'toolbar-collapsed' );
			  	consoleToolbar.removeClass( 'toolbar-collapsed-editorstate' );
				PageBody.addClass(console_active_class);
			}
			
			else {
				consoleToolbar.css( 'height', '45px' );			
				PageBody.css('overflow', 'auto');
			 	consoleWorkspace.css('width', '0');
				consoleWorkspace.fullscreen = false;
				consoleWorkspace.iframe.location.replace( 'about:blank' );		 		
				returnOnClose = '';
				searchProcess = '';
				history = new Array();
				consoleToolbar.iframe.focus();
				
				toolbarCollapsed = true;							
				createCookie('toolbarCollapsed', 1);					
				PageBody.removeClass(console_active_class);

			  	consoleToolbar.removeClass( 'toolbar-collapsed-editorstate' );
			}
		};

		this.toolbarOverrideCSS = function(){
			var cssLink = document.createElement("link") 
			cssLink.href = nfConsoleRoot+ "/wp-content/plugins/newfangled-console/assets/css/override-toolbar.css"; 
			cssLink .rel = "stylesheet"; 
			cssLink .type = "text/css"; 
			window.frames['consoleToolbarIframe'].document.body.appendChild(cssLink);

			var script=document.createElement('script');
			script.type='text/javascript';
			script.src= nfConsoleRoot+ '/wp-content/plugins/newfangled-console/assets/js/override-links.js';
			window.frames['consoleToolbarIframe'].document.body.appendChild(script);

			jQueryConsole('#adminmenu',window.frames['consoleToolbarIframe'].document).addClass('showlogo');
			setTimeout( "jQueryConsole('#consoleToolbar iframe').css('opacity',1)", 200 );
		};

		this.workspaceOverrideCSS = function(){
			
			jQueryConsole('#iframe-wrapper').css('opacity', 1);
			jQueryConsole('#workspace-tools').css('opacity', 1);

			var cssLink = document.createElement("link") 
			cssLink.href = nfConsoleRoot+ "/wp-content/plugins/newfangled-console/assets/css/override-workspace.css"; 
			cssLink .rel = "stylesheet"; 
			cssLink .type = "text/css"; 

			var doc=document.getElementById("consoleWorkspaceIframe").contentWindow.document;
			doc.body.appendChild(cssLink);

			

			var script=document.createElement('script');
			script.type='text/javascript';
			script.src= nfConsoleRoot + '/wp-content/plugins/newfangled-console/assets/js/override-links.js';
		};

		this.toggleConsole = function(){
			toggleConsole();
		}

		this.toggleConsoleVisibility = function(){ 
			toggleConsoleVisibility();
		}

		this.toggleConsole2 = function(){
			toggleConsole2();
		}

		this.setTempTitle = function( tempTitle ){
			
			if (top.CMSConsole.editorState)
		  	{
		  		jQueryConsole( '[role="main"] h1').html( tempTitle );
			}
		}

		this.setTempContent = function( tempContent ){
			
			if (top.CMSConsole.editorState)
		  	{
		  		jQueryConsole( '[role="main"] .content').html( tempContent );
			}
		}

	};

	//---------------------------------------------------------------------------------------------------------
	//	Private functions
	//---------------------------------------------------------------------------------------------------------
	//---------------------------------------------------------------------------------------------------------
	//	Init
	//---------------------------------------------------------------------------------------------------------
	initEnviroment = function( consoleID ) {
		
		var newDate = new Date;
		var uid = newDate.getTime();
		
		history 				= new Array();
		Page 					= jQueryConsole(top.document);
		PageBody 				= jQueryConsole(top.document.body);
		PageWindow 				= jQueryConsole(top);
		
		consoleToolbar 			= jQueryConsole('#consoleToolbar');
		consoleWorkspace 		= jQueryConsole('#consoleWorkspace');
		consoleDimm 			= jQueryConsole('#consoleDimm');
		consoleToolbar.iframe 	= window.frames['consoleToolbarIframe'];
		consoleWorkspace.iframe = window.frames['consoleWorkspaceIframe'];	
		
		consoleStatus 			= readCookie('console');
		consoleVisibility 		= readCookie('consoleVisibility');
		toolbarCollapsed 		= readCookie('toolbarCollapsed');
		toolbarCollapsed		= true;	
		consoleStatus			= 1;

		jQueryConsole(document).on('mouseenter', '.toolbar-collapsed', function(){
			
			if (jQueryConsole(this).hasClass( 'toolbar-collapsed-editorstate' ) )
			{
				return;
			}

			createCookie('console', 1);
			consoleStatus = 1;
			showConsole();
			PageBody.addClass(console_active_class);
		})

		jQueryConsole(document).on('mouseleave', '#consoleToolbar', function(){
			
		//	console.log( 1 );
		//	CMSConsole.shrinkToolbar();

			// if (jQueryConsole(this).hasClass( 'toolbar-collapsed-editorstate' ) )
			// {
			// 	return;
			// }

			// createCookie('console', 1);
			// consoleStatus = 1;
			// showConsole();
			// PageBody.addClass(console_active_class);
		})

		jQueryConsole(document).on('click', '.toolbar-collapsed-editorstate', function(){
			
			createCookie('console', 1);
			consoleStatus = 1;
			showConsole();
			PageBody.addClass(console_active_class);
		})

		if ( consoleVisibility == 0) {
			PageBody.addClass( 'console-invisible' );
		}

		else {
			PageBody.removeClass( 'console-invisible' );
		}


		focusTop();	
	};
			
	//---------------------------------------------------------------------------------------------------------
	//	Show hide the console windows
	//---------------------------------------------------------------------------------------------------------
	toggleConsole = function() {
		
		PageBody.removeClass('console-invisible');
		
		if (consoleStatus == 1) {
			createCookie('console', 0);
			consoleStatus = 0;
			hideConsole();
		}
		
		else {
			
			if (CMSConsole.editorState)
			{
				CMSConsole.loadWorkspaceEditor();
				consoleStatus = 1;
				return;

			}
			
			else
			{
				createCookie('console', 1);
				consoleStatus = 1;
				showConsole();
			}

		}
	};

	toggleConsoleVisibility = function() {


		consoleVisibility = readCookie( 'consoleVisibility' );

		if (consoleVisibility == null) {
			consoleVisibility = 1;
		}


		if (consoleVisibility == 1) {
		
			PageBody.addClass('console-invisible');

			createCookie('consoleVisibility', 0, 1);
			consoleVisibility = 0;
		}
		
		else {
			
			PageBody.removeClass('console-invisible');

			createCookie('consoleVisibility', 1, 1);
			consoleVisibility = 1;
		}

	}

	toggleConsole2 = function() {
		
		createCookie('console', 0);
		consoleStatus = 0;
		hideConsole();
	};

	//---------------------------------------------------------------------------------------------------------
	//	Private: Show the console, workspace windows
	//---------------------------------------------------------------------------------------------------------
	showConsole = function() {
	  	
	  	if (!toolbarLoaded)
	  	{
	  		jQueryConsole('#consoleToolbar iframe').css('opacity',0);
			loadToolbar( nfConsoleRoot + '/wp-admin/index.php?console=1&id='+this.consoleID+'&collapsed=' + ( toolbarCollapsed ? 1 : 0 ) );  
			toolbarLoaded = true;	
	  	}

	  	consoleToolbar.removeClass( 'toolbar-collapsed' );
	  	consoleToolbar.removeClass( 'toolbar-collapsed-editorstate' );
	 
	 	consoleWorkspace.css('left', '190px');
		PageBody.css('margin-right', '0px');
		PageBody.css('margin-left', '0px');
		PageBody.removeClass('editorstate-active');
		PageBody.removeClass('console-invisible');

	   	PageBody.addClass(console_active_class);
	  	
	 	finalWidth 		= '0px';
	  	animateSpeed 	= 150;
			
		consoleToolbar.animate({
	    	marginLeft: finalWidth
	  	}, animateSpeed, function() {
	  		
	  		if (consoleWorkspace.iframe.location != 'about:blank')
	  		{
	  			consoleDimm.css('display','block');
	  			consoleDimm.animate({'opacity': 1}, 100);

				jQueryConsole('#wrapper').addClass('overlay-blur');
	  			consoleWorkspace.css('display', 'block');
	  			consoleWorkspace.css('width', '85%');
				PageBody.css('overflow', 'hidden');
			}
	  	});
	};
	
	//---------------------------------------------------------------------------------------------------------
	//	Private: Hide the console, workspace windows
	//---------------------------------------------------------------------------------------------------------
	hideConsole = function() {
	  	
	  	consoleDimm.animate({'opacity': 0}, 250, function(){
		  	consoleDimm.css('display','none');
	  	});

		jQueryConsole('#wrapper').removeClass('overlay-blur');
		consoleToolbar.addClass('toolbar-collapsed');
		consoleWorkspace.css('display', 'none');
		consoleWorkspace.css('width', '0');
		PageBody.removeClass(console_active_class);
		PageBody.css('overflow', 'auto');
		PageBody.removeClass('console-invisible');
		focusTop();	

		consoleWorkspace.css('left', '190px');
		PageBody.css('margin-right', '0px');
		PageBody.css('margin-left', '0px');
		PageBody.removeClass('editorstate-active');

	 	consoleToolbar.removeClass( 'toolbar-collapsed-editorstate' );

	};
		
	//---------------------------------------------------------------------------------------------------------
	//	Utility: generate an unique ID
	//---------------------------------------------------------------------------------------------------------
	uId = function() {
		var newDate = new Date;
		var uid = newDate.getTime();
		return uid;
	};
	
	//---------------------------------------------------------------------------------------------------------
	//	Show/load the toolbar overlay
	//---------------------------------------------------------------------------------------------------------
	loadToolbar = function( url ) {
		consoleToolbar.iframe.location.replace( url );
	};
	
	//---------------------------------------------------------------------------------------------------------
	//	Utility: Send the focus back to the main page
	//				hack for FF 5+
	//---------------------------------------------------------------------------------------------------------
	focusTop = function() {
		
		newInput 				= document.createElement("input"); 
		newInput.id 			= 'focusTopTarget';
		newInput.style.position = 'fixed'; 
		newInput.style.top 		= '1px'; 
		newInput.style.left 	= '1px'; 
		window.top.document.body.appendChild(newInput);
		
		newInput.focus();
		
		window.top.document.body.removeChild(newInput);
	};
	
	//---------------------------------------------------------------------------------------------------------
	// Cookie functions taken from http://www.quirksmode.org/js/cookies.html by ppk
	//---------------------------------------------------------------------------------------------------------
	createCookie = function(name,value,days) {
	    if (days) {
	        var date = new Date();
	        date.setTime(date.getTime()+(days*24*60*60*1000));
	        var expires = "; expires="+date.toGMTString();
	    }
	    else var expires = "";
	    document.cookie = name+"="+value+expires+"; path=/";

	};
	
	readCookie = function(name) {
	    var nameEQ = name + "=";
	    var ca = document.cookie.split(';');
	    for(var i=0;i < ca.length;i++) {
	        var c = ca[i];
	        while (c.charAt(0)==' ') c = c.substring(1,c.length);
	        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	    }
	    return null;
	};
	
	eraseCookie = function(name) {
	    createCookie(name,"",-1);
	};
	// End attributed cookie functions
	
    return public;

})();

//---------------------------------------------------------------------------------------------------------
//	Onready, taken from jQuery source
//---------------------------------------------------------------------------------------------------------
function onReadyInit()
{
	if (arguments.callee.done) { 
		return;
	}
	
	arguments.callee.done = true;
	
	if (_timer) {
		clearInterval(_timer);
	}

	initConsole();
}

// For Mozilla/Opera9
if (document.addEventListener) {
	document.addEventListener("DOMContentLoaded", onReadyInit, false);
}

// For Internet Explorer
/*@cc_on @*/
/*@if (@_win32)
  document.write("<script id=__ie_onload defer src=javascript:void(0)><\/script>");
  var script = document.getElementById("__ie_onload");
  script.onreadystatechange = function() {
    if (this.readyState == "complete") {
      onReadyInit(); // call the onload handler
    }
  };
/*@end @*/

// For Safari
if (/WebKit/i.test(navigator.userAgent)) {
	var _timer = setInterval(function() {
		if (/loaded|complete/.test(document.readyState)) {
			onReadyInit();
		}
	}, 10);
}

// For other browsers
window.onload = onReadyInit;


