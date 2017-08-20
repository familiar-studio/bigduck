
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
    
    var history = new Array();
    var returnOnClose;
    var console_active_class = 'console-active';
    
    //---------------------------------------------------------------------------------------------------------
    //  Public functions
    //---------------------------------------------------------------------------------------------------------
    var public = function ( consoleID ) {
        
        //---------------------------------------------------------------------------------------------------------
        //  Constructor
        //---------------------------------------------------------------------------------------------------------
        if (consoleID && !consoleToolbar)
        {
            initEnviroment( consoleID );
        }
        
        //---------------------------------------------------------------------------------------------------------
        //  Set all the windows to be listening
        //---------------------------------------------------------------------------------------------------------
        this.resetKeyListeners = function() {
            
            jQueryConsole(top.window.document).unbind('keyup');
            jQueryConsole(consoleToolbar.iframe.document).unbind('keyup');
            jQueryConsole(consoleWorkspace.iframe.document).unbind('keyup');
            jQueryConsole(PageWindow).unbind('resize');
            
            jQueryConsole(top.window.document).keyup(function(e) {
                if( e.keyCode == 27 ) {
                    toggleConsole();
                }
            });
            
            jQueryConsole(consoleToolbar.iframe.document).keyup(function(e) {
                if( e.keyCode == 27 ) {
                    toggleConsole();
                }
            });
            
            jQueryConsole(consoleWorkspace.iframe.document).keyup(function(e) {
                if( e.keyCode == 27 ) {
                    toggleConsole();
                }
            });

            jQueryConsole(PageWindow).resize(function(e) {
                                
                if( consoleWorkspace.fullscreen ) {
                
                    widthVal  = ( jQueryConsole(top).width()  ) - 250;
                    heightVal = ( jQueryConsole(top).height() ) - 0;
                
                    consoleWorkspace.css( 'width',widthVal );
                    consoleWorkspace.css( 'height',heightVal );
                }
                
                else
                {
                    heightVal = ( jQueryConsole(top).height() ) - 0;
                    consoleWorkspace.css( 'height',heightVal );
                }
            });
        };  
        
        //---------------------------------------------------------------------------------------------------------
        //  Public: Highlight the active section in the toolbar
        //---------------------------------------------------------------------------------------------------------
        this.setActiveSection = function( idVal ) {
        
            if (activeSection)
            {
                var toolbarObj  = jQueryConsole(consoleToolbar.iframe.document);
                var sectionObj  = jQueryConsole('.' + activeSection, toolbarObj);
                
                sectionObj.removeClass('toolbar-block-active');
            }
            
            if (idVal)
            {
                var toolbarObj  = jQueryConsole(consoleToolbar.iframe.document);
                var sectionObj  = jQueryConsole('.' + idVal, toolbarObj);
                
                if (sectionObj)
                {
                    sectionObj.addClass('toolbar-block-active');
                    activeSection = idVal;
                }
            }
        };
        
        //---------------------------------------------------------------------------------------------------------
        //  Public: Change the width of the workspace
        //---------------------------------------------------------------------------------------------------------
        this.growWorkspace = function( increase ) {
            
            consoleWorkspace.animate({
                width: ( consoleWorkspace.width() + increase )
            });
        };
        
        //---------------------------------------------------------------------------------------------------------
        //  Public: Change the width of the workspace
        //---------------------------------------------------------------------------------------------------------
        this.shrinkWorkspace = function( decrease ) {
            
            consoleWorkspace.animate({
                width: ( consoleWorkspace.width() - decrease )
            });
        };
        
        //---------------------------------------------------------------------------------------------------------
        //  Public: 
        //---------------------------------------------------------------------------------------------------------
        this.hideToolbar = function() {
            
            $('#workspace-tools', consoleWorkspace).hide();
        };
        
        //---------------------------------------------------------------------------------------------------------
        //  Public: 
        //---------------------------------------------------------------------------------------------------------
        this.showToolbar = function() {
            
            $('#workspace-tools', consoleWorkspace).show();
        };

        //---------------------------------------------------------------------------------------------------------
        //  Public: Show/load the workspace overlay
        //---------------------------------------------------------------------------------------------------------
        this.loadWorkspace = function( url, widthVal, heightVal, postVars, disableAnimation, disableHistory, maintainCollapesdState  ) {
        
            if (!disableHistory)
            {
                var encodeurl = encodeURIComponent(url);
                history.push( encodeurl+','+widthVal+','+heightVal+','+postVars );
            }
                
            if (!widthVal || !heightVal)
            {
                returnOnClose = url+','+widthVal+','+heightVal+','+postVars;
                widthVal  = ( jQueryConsole(top).width()  ) - 250;
            //  heightVal = ( jQueryConsole(top).height() ) - 0;
                heightVal = ( jQueryConsole(top).height() ) - 0;
                consoleWorkspace.fullscreen = true;
            }
            
            else
            {
                consoleWorkspace.fullscreen = false;
                heightVal = ( jQueryConsole(top).height() ) - 0;
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
            
            consoleDimm.css('display', 'block');
            consoleToolbar.css('opacity','100');

            consoleWorkspace.iframe.location.replace( 'about:blank' );              
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
                consoleWorkspace.css('width', widthVal );
                consoleWorkspace.iframe.location.replace( url );
            }
            
            
            else {
                PageBody.css('overflow', 'hidden');
                consoleWorkspace.show();
                consoleWorkspace.animate({
                            width: widthVal
                        }, 250, 'linear', function(){                       
                    consoleWorkspace.iframe.location.replace( url );
                });
            }               
        };
        
        //---------------------------------------------------------------------------------------------------------
        //  Public: Hide workspace overlay
        //---------------------------------------------------------------------------------------------------------
        this.closeWorkspace = function()  {
            
            consoleWorkspace.fullscreen = false;
            consoleWorkspace.css('width', '0');
            consoleDimm.css('display', 'none');

        
            consoleToolbar.css('opacity','.95');
            
            PageBody.css('overflow', 'auto');
            consoleWorkspace.fadeOut( 'fast' );
            consoleWorkspace.iframe.location.replace( 'about:blank' );              
            returnOnClose = '';
            searchProcess = '';
            history = new Array();
            focusTop();
            this.setActiveSection('');
        };
        
        //---------------------------------------------------------------------------------------------------------
        //  Public: Send the workspace history back
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
        //  Public: On complete, either go back to the last 'fullscreen' view, or close the workspace
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
        //  Public: Update the status message
        //---------------------------------------------------------------------------------------------------------
        this.updateStatus = function()  {
            
            var toolbarObj  = jQueryConsole(consoleToolbar.iframe.document);
            var statusBlock = jQueryConsole('#status-block', toolbarObj);
            
            jQueryConsole.get('/consolemgr/getstatus.php', function(data) {
                                
                if (data) {
                    statusBlock.fadeOut(300);
                    statusBlock.html(data);
                    statusBlock.fadeIn(300);
                    statusBlock.delay(500).fadeOut(2000);
                }
            });
        };
        
        //---------------------------------------------------------------------------------------------------------
        //  Public: Add a url to the history array
        //---------------------------------------------------------------------------------------------------------
        this.addHistory = function(url)  {
            
        };
        
        //---------------------------------------------------------------------------------------------------------
        //  Public: Make the workspace blank while something is processing
        //---------------------------------------------------------------------------------------------------------
        this.emptyWorkspace = function(url)  {
            
            consoleWorkspace.iframe.location.replace( 'about:blank' );              

        };
        
        //---------------------------------------------------------------------------------------------------------
        //  Public: Make the workspace blank while something is processing
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
        //  Public: Make the workspace blank while something is processing
        //---------------------------------------------------------------------------------------------------------
        this.consoleDoSearch = function(findtext)  {
            
            if (findtext.length<4)
            {
                return;
            }

            url = '/consolemgr/_views/Search.php?findtext=' + encodeURIComponent(findtext) + '&cid=' + this.consoleID;
            this.loadWorkspace(url,'800','650', '', '', 1 );
            returnOnClose = url+',800';
        };
    
    
        //---------------------------------------------------------------------------------------------------------
        //  toggleHelp
        //---------------------------------------------------------------------------------------------------------
        this.toggleHelp = function() {
            
            var toolbarObj  = jQueryConsole(consoleToolbar.iframe.document);
            
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
        //  expandToolbar
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
            }
        };
        
        //---------------------------------------------------------------------------------------------------------
        //  shrinkToolbar
        //---------------------------------------------------------------------------------------------------------
        this.shrinkToolbar = function(set) {
            
            if (toolbarCollapsed || set)
            {
                if (set)
                {
                    var toolbarObj  = jQueryConsole(consoleToolbar.iframe.document);
                                
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
                consoleToolbar.addClass( 'toolbar-collapsed' );
            }
        };
        
        
        //---------------------------------------------------------------------------------------------------------
        //  Collapse/expand the toolbar panel
        //---------------------------------------------------------------------------------------------------------
        this.toggleToolbarCollapse = function( obj ) {
                        
            if (toolbarCollapsed) {
                toolbarCollapsed = false;
                createCookie('toolbarCollapsed', 0);    
                consoleToolbar.removeClass( 'toolbar-collapsed' );
            }
            
            else {
                consoleToolbar.css( 'height', '45px' );         
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
        };
    };

    //---------------------------------------------------------------------------------------------------------
    //  Private functions
    //---------------------------------------------------------------------------------------------------------
    //---------------------------------------------------------------------------------------------------------
    //  Init
    //---------------------------------------------------------------------------------------------------------
    initEnviroment = function( consoleID ) {
        
        var newDate = new Date;
        var uid = newDate.getTime();
        
        iframeHTML        = '<iframe ';
        iframeHTML       += '     id="consoleToolbarIframe" ';
        iframeHTML       += '     name="consoleToolbarIframe" ';
        iframeHTML       += '     width="100%" ';
        iframeHTML       += '     height="100%" frameborder="0" ';
        iframeHTML       += '     ALLOWTRANSPARENCY="true" ';
        iframeHTML       += '     src="about:blank" onMouseover="top.CMSConsole.expandToolbar()" onMouseOut="top.CMSConsole.shrinkToolbar();">';
        iframeHTML       += '</iframe>';
        
        newDiv           = document.createElement("div"); 
        newDiv.id        = 'consoleToolbar';
        newDiv.innerHTML = iframeHTML;
        document.body.appendChild(newDiv);
        
        var cssNode      = document.createElement('link');
        cssNode.type     = 'text/css';
        cssNode.rel      = 'stylesheet';
        cssNode.href     = '/consolemgr/css/consolemgr-init.css';
        cssNode.media    = 'screen';
        document.getElementsByTagName("head")[0].appendChild(cssNode);
        
        newContents              = '<div id="workspace-tools">';
        newContents             += '    <a href="javascript:CMSConsole.closeWorkspace();"><img src="/consolemgr/images/workspace-close.png" width="20" height="20" border="0" /></a>';
        newContents             += '    <a href="javascript:CMSConsole.backWorkspace();"><img src="/consolemgr/images/workspace-back.png" width="20" height="20" border="0" /></a>';
        newContents             += '</div>';
        newContents             += '<iframe id="consoleWorkspaceIframe" name="consoleWorkspaceIframe"';
        newContents             += '    width="100%" ';
        newContents             += '    height="100%" frameborder="0" ';
        newContents             += '     ALLOWTRANSPARENCY="true" ';
        newContents             += '    src="about:blank">';
        newContents             += '</iframe>';
            
        newDiv                  = document.createElement("div"); 
        newDiv.id               = 'consoleWorkspace';
        newDiv.innerHTML        = newContents; 
        document.body.appendChild(newDiv);
        
        newDiv                  = document.createElement("div"); 
        newDiv.id               = 'consoleDimm';
        document.body.appendChild(newDiv);
        
        history                 = new Array();
        Page                    = jQueryConsole(top.document);
        PageBody                = jQueryConsole(top.document.body);
        PageWindow              = jQueryConsole(top);
        
        consoleToolbar          = jQueryConsole('#consoleToolbar');
        consoleWorkspace        = jQueryConsole('#consoleWorkspace');
        consoleDimm             = jQueryConsole('#consoleDimm');
        consoleToolbar.iframe   = window.frames['consoleToolbarIframe'];
        consoleWorkspace.iframe = window.frames['consoleWorkspaceIframe'];  
        
        consoleStatus           = readCookie('console');
        toolbarCollapsed        = readCookie('toolbarCollapsed');
        toolbarCollapsed        = ( toolbarCollapsed == 1 ? true : false );
        
        if (consoleStatus == 1)
        {
            setTimeout( "showConsole();", 500 );
        }       
        
        focusTop(); 
    };
            
    //---------------------------------------------------------------------------------------------------------
    //  Show hide the console windows
    //---------------------------------------------------------------------------------------------------------
    toggleConsole = function() {
        
        if (consoleStatus == 1) {
            createCookie('console', 0);
            consoleStatus = 0;
            hideConsole();
        }
        
        else {
            createCookie('console', 1);
            consoleStatus = 1;
            showConsole();
        }
    };

    //---------------------------------------------------------------------------------------------------------
    //  Private: Show the console, workspace windows
    //---------------------------------------------------------------------------------------------------------
    showConsole = function() {
        
        if (!toolbarLoaded)
        {
            loadToolbar( '/consolemgr/_views/Console.php?id='+this.consoleID+'&collapsed=' + ( toolbarCollapsed ? 1 : 0 ) );  
            toolbarLoaded = true;   
        }
        
        PageBody.addClass(console_active_class);
        
        finalWidth      = '0px';
        animateSpeed    = 150;
        
        if (toolbarCollapsed)
        {
            top.CMSConsole.shrinkToolbar(1);
        }
        
        consoleToolbar.animate({
            marginLeft: finalWidth
        }, animateSpeed, function() {
            
            if (consoleWorkspace.iframe.location != 'about:blank')
            {
                consoleDimm.css('display', 'block');
                consoleWorkspace.css('display', 'block');
                PageBody.css('overflow', 'hidden');
            }
        });
    };
    
    //---------------------------------------------------------------------------------------------------------
    //  Private: Hide the console, workspace windows
    //---------------------------------------------------------------------------------------------------------
    hideConsole = function() {
        
        consoleDimm.css('display', 'none');
        consoleToolbar.css('margin-left','-200px');
        consoleWorkspace.css('display', 'none');
        PageBody.removeClass(console_active_class);
        PageBody.css('overflow', 'auto');
        focusTop(); 
    };
        
    //---------------------------------------------------------------------------------------------------------
    //  Utility: generate an unique ID
    //---------------------------------------------------------------------------------------------------------
    uId = function() {
        var newDate = new Date;
        var uid = newDate.getTime();
        return uid;
    };
    
    //---------------------------------------------------------------------------------------------------------
    //  Show/load the toolbar overlay
    //---------------------------------------------------------------------------------------------------------
    loadToolbar = function( url ) {
        consoleToolbar.iframe.location.replace( url );
    };
    
    //---------------------------------------------------------------------------------------------------------
    //  Utility: Send the focus back to the main page
    //              hack for FF 5+
    //---------------------------------------------------------------------------------------------------------
    focusTop = function() {
        
        newInput                = document.createElement("input"); 
        newInput.id             = 'focusTopTarget';
        newInput.style.position = 'fixed'; 
        newInput.style.top      = '1px'; 
        newInput.style.left     = '1px'; 
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
//  Onready, taken from jQuery source
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