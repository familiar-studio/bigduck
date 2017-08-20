<?php
/**
 * Newfangled Tracking
 *
 * @package   Newfangled Tracking
 * @author    Newfangled
 * @link      http://newfangled.com/plugin-support-policy
 * @copyright Newfangled 2016
 */

//*********************************************************************************************************
/*
Plugin Name: Newfangled Tracking
Plugin URI: http://newfangled.com
Description: Integrate with Newfangled's visitor tracking system
Version: 2.1.3
Author: Newfangled
Author URI: http://newfangled.com
Text Domain: nftracking
Domain Path: /languages
Copyright: Newfangled 2016
*/
//*********************************************************************************************************
//  Don't load directly
if (!function_exists('is_admin')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

//  Provide plugin updates
require 'plugin_update_check.php';
$MyUpdateChecker = new PluginUpdateChecker_2_0 (
    'https://kernl.us/api/v1/updates/589251709f929649d4bc4f0b/',
    __FILE__,
    'nftracking',
    1
);

//  Plugin constants
define( 'NFTRACKING_VERSION',        '2.1.3' );
define( 'NFTRACKING_RELEASE_DATE',   date_i18n( 'F j, Y', '1493840579 ' ) );
define( 'NFTRACKING_DIR',            plugin_dir_path( __FILE__ ) );
define( 'NFTRACKING_URL',            plugin_dir_url( __FILE__ ) );

//  Define the plugin class
if (!class_exists("NFTracking")) {
class NFTracking {

    var $settings, $options_page;

    private static $_this;
    
    static function this() {
        return self::$_this;
    }

    function __construct() {    

        self::$_this = $this;

        add_action( 'init', array($this,'init') );
        add_action( 'plugins_loaded', array($this,'load_textdomain' ));
        add_filter('pre_set_site_transient_update_plugins', array( $this, 'verifyLogging' ), 10, 1);
    }

    /**
     * Define all the actions/filters
     */
    function init() {

        if ( $this->tracking_enabled() ) {

            add_action('wp_footer',                                      array($this,'generateTrackingJS'),                 100);
            add_filter('screen_layout_columns',                          array($this,'screen_layout_columns')   );
            add_filter('get_user_option_screen_layout_dashboard',        array($this,'screen_layout_dashboard') );
            add_action("gform_entry_detail",                             array($this,"add_to_details"),                     10, 2);
            add_action("gform_after_submission",                         array($this,"recordConversion"),                   10, 2);
            add_action("publish_post",                                   array($this,"recordNewContentActivity"),           10, 2);
            //add_action('wp_dashboard_setup',                             array($this,'init_siteactivity_dashboad_widget')   );
            add_action("admin_menu",                                   array($this,"registerDashboardPage" ));
            $this->load_settings();     

            add_action( 'wp_enqueue_scripts',                           array( $this, 'init_ajaxscripts' ) );
            add_action( 'wp_ajax_process_loadtracker',                  array( $this, 'process_loadtracker' ) ); 
            add_action( 'wp_ajax_nopriv_process_loadtracker',           array( $this, 'process_loadtracker' ) );

            $this->tracking_server_url = $this->options['tracking_server_url'];
            $this->tracking_server_token = $this->options['tracking_server_token'];

        }
    }

    /**
     * Add the admin pages
     *
     * @since 1.3
     */
    function registerDashboardPage() {

        add_menu_page(  
            'Insights', 
            'Insights', 
            'edit_posts', 
            'nftracking/dashboard.php', 
            array($this,"renderDashboardPage"), 
            'dashicons-lightbulb', 2  );

        add_submenu_page(   
            'nftracking/dashboard.php', 
            'Visitors', 
            'Visitors', 
            'edit_posts', 
            'nftracking/dashboard-visitors.php', 
            array($this,"renderVisitorsPage")  );

        add_submenu_page(   
            'nftracking/dashboard.php', 
            'Conversions', 
            'Conversions', 
            'edit_posts', 
            'nftracking/dashboard-conversions.php', 
            array($this,"renderConversionsPage")  );

        // add_submenu_page( 
        //     'nftracking/dashboard.php', 
        //     'Outbound Emails', 
        //     'Outbound Emails', 
        //     'edit_posts', 
        //     'nftracking/dashboard-emails.php', 
        //     array($this,"renderEmailsPage")  );

        add_submenu_page( 
            'nftracking/dashboard.php', 
            'Content Publication', 
            'Content Publication', 
            'edit_posts', 
            'nftracking/dashboard-content.php', 
            array($this,"renderContentActivityPage")  );

        add_submenu_page( 
            'nftracking/dashboard.php', 
            'Google Analytics', 
            'Google Analytics', 
            'edit_posts', 
            'nftracking/dashboard-analytics.php', 
            array($this,"renderAnalyticsPage")  );
    }

    /**
     * Render the dashboard page
     *
     * @since 1.3
     */
    function renderDashboardPage() {
        
        ?>
        <div class="wrap">
            <iframe style="margin-top:20px;" src="<?php print $this->tracking_server_url ?>/api/v1/<?php print $this->tracking_server_token ?>/dashboard"></iframe>
            </div>
        </div>
        <?php

        $this->includeAdminJavascript();
    }

    /**
     * Render the visitors page
     *
     * @since 1.3
     */
    function renderVisitorsPage() {
        
        ?>
        <div class="wrap">
            <iframe style="margin-top:20px;" src="<?php print $this->tracking_server_url ?>/api/v1/dashboard/<?php print $this->tracking_server_token ?>"></iframe>
            </div>
        </div>
        <?php

        $this->includeAdminJavascript();
    }

    /**
     * Render the conversions page
     *
     * @since 1.3
     */
    function renderConversionsPage() {
        
        ?>
        <div class="wrap">
            <iframe style="margin-top:20px;" src="<?php print $this->tracking_server_url ?>/api/v1/<?php print $this->tracking_server_token ?>/conversions"></iframe>
            </div>
        </div>
        <?php 

        $this->includeAdminJavascript();

        if ( is_plugin_active( 'gravityforms/gravityforms.php' ) ) 
        {
            ?>
            <script>
            function listenMessage(msg) {
                window.location = '/wp-admin/admin.php?page=gf_entries&view=entries&id=3&sort=0&dir=DESC&s=' + msg.data + '&star=null&read=null&field_id=0&operator=contains';
            }

            if (window.addEventListener) {
                window.addEventListener("message", listenMessage, false);
            } else {
                window.attachEvent("onmessage", listenMessage);
            }
            </script>
            <?php
        }
    }

    /**
     * Render the outbound emails page
     *
     * @since 1.3
     */
    function renderEmailsPage() {
        
        ?>
        <div class="wrap">
            <iframe style="margin-top:20px;" src="<?php print $this->tracking_server_url ?>/api/v1/<?php print $this->tracking_server_token ?>/campaigns"></iframe>
            </div>
        </div>
        <?php 

        $this->includeAdminJavascript();
    }

    /**
     * Render the content activity page
     *
     * @since 1.3
     */
    function renderContentActivityPage() {
        
        ?>
        <div class="wrap">
            <iframe style="margin-top:20px;" src="<?php print $this->tracking_server_url ?>/api/v1/<?php print $this->tracking_server_token ?>/content"></iframe>
            </div>
        </div>
        <?php

        $this->includeAdminJavascript();
    }

    /**
     * Render the google analytics page
     *
     * @since 1.4
     */
    function renderAnalyticsPage() {
        
        ?>
        <div class="wrap">
            <iframe style="margin-top:20px;" src="<?php print $this->tracking_server_url ?>/api/v1/<?php print $this->tracking_server_token ?>/analytics"></iframe>
            </div>
        </div>
        <?php

        $this->includeAdminJavascript();
    }

    /**
     * Javacript to include on the admin pages
     *
     * @since 1.3
     */
    function includeAdminJavascript() {
        
        ?>
        <script>
        function resizeIframe() {

            var offset = jQuery('.wrap>iframe').offset();
            var top = offset.top;
            var Offset = top + 70;

            jQuery('body.wp-admin').css('overflow', 'hidden');
            var height = jQuery(window).height();
            var width = jQuery(window).width();
            height = height - Offset;
            width = width - 50;
            jQuery('iframe').css('height', height);
            jQuery('iframe').css('width', '100%');
        }

        jQuery(document).ready(function() {
            jQuery( window ).on('resize', resizeIframe );
            resizeIframe();
            setTimeout( resizeIframe, 1000 );
        });
        </script>

        <?php
    }

    

    /**
     * Load plugin textdomain.
     */
    function load_textdomain() {
        load_plugin_textdomain( 'nftracking', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
    }

    /**
     * Load the plugin settings
     * 
     * @return object
     */
    function load_settings() {

        if (!$this->settings && !class_exists("NFTracking_Settings")) { 
        
            require(NFTRACKING_DIR . 'newfangledtracking-settings.php');

            $this->settings = new NFTracking_Settings();    
            $this->options  = $this->settings->options;
            
            return $this->options;
        }
    }

    /**
     * Determine if the tracking should be used
     * 
     * @return boolean
     */
    function tracking_enabled() {

        if (isset( $this->tracking_enabled_flag )) {
            return $this->tracking_enabled_flag;
        }

        $this->load_settings();

        if ( isset( $this->options['enable_tracking'] ) )  {
            $this->tracking_enabled_flag = true;
            return true;
        }

        else {
            $this->tracking_enabled_flag = false;
            return false;
        }
    }

    /**
     * Set the number of columns for the dashboard
     * 
     * @param  array $columns
     *
     * @return array
     */
    function screen_layout_columns($columns) {
    
        $columns['dashboard'] = 1;
        return $columns;
    
    }

    /**
     * Set the number of columns for the dashboard
     */
    function screen_layout_dashboard() { 

        return 1; 

    }

    /**
     * Hook to add the widget to the dashboard
     */
    function init_siteactivity_dashboad_widget() {

        global $wp_meta_boxes;
        
        wp_add_dashboard_widget(
            'siteactivity_dashboad_widget', 
            'Visitor Activity', 
            Array( $this, 'siteactivity_dashboad_widget_function') );
    }

    /**
     * Actually add the widget to the dashboard
     */
    function siteactivity_dashboad_widget_function() {
    
        print '<div style="width:100%;height:1000px;padding:0;margin:0;">
            <iframe style="width:100%;height:100%;padding:0;margin:0;" src="' . $this->tracking_server_url . '/api/v1/dashboard/' . $this->tracking_server_token . '"></iframe>
        </div>';
    
    }

    /**
     * Show the session details on form entries
     * 
     * @param object $form
     * @param object $lead
     */
    function add_to_details($form, $lead) {

        $user_email_value = '';

        foreach( $form['fields'] as $idx => $field )
        {
            if (strtolower( $field['label'] ) == 'email' )
            {
                $user_email_value = $lead[$field['id']];
            }

            else if (strtolower( $field['label'] ) == 'emailaddress' )
            {
                $user_email_value = $lead[$field['id']];
            }

            else if (strtolower( $field['label'] ) == 'email address' )
            {
                $user_email_value = $lead[$field['id']];
            }
        }

        if ($user_email_value)
        {
            print '<div class="postbox">';
            print '<h3>Visitor Details</h3>';
            print '<iframe style="width:100%;height:800px" src="' . $this->tracking_server_url . '/api/v1/' . $this->tracking_server_token . '/sessionemail/' . urlencode( $user_email_value ) . '"></iframe>';
            print '</div>';
        }

    }

    /**
     * Send an error to the Newfangled Login plugin
     * 
     * @param  object $msg
     */
    function nflog_error($msg){

        global $nflogging;

        if (!is_object($nflogging)) {
            return;
        }

        if (!method_exists( $nflogging, 'logError')) {
            return;
        }

        $nflogging->logError( $msg );
    }

    /**
     * Track a form submission
     * 
     * @param  object $entry
     * @param  object $form
     */
    function recordConversion($entry, $form){

        try {
            
            //  Get the current user's session id
            $sessionid  = $_COOKIE['nfsession'];

            //  Get he page details that the conversion occured on
            $pagelink   = get_permalink( $post->ID );
            $pagetitle  = wp_title(' ', false);
            $contentid  = get_the_id();

            //  We need to figure out the first name, last name, and email fields
            //  Email is the most important, the conversion wont track without it
            $firstname = '';
            $lastname = '';
            $email = '';

            foreach ($form['fields'] as $field)
            {
                if (strtolower( $field['label'] ) == 'first name' || 
                    strtolower( $field['label'] ) == 'firstname') { 

                    if (isset($entry[$field['id']])) {
                        $firstname = trim( $entry[$field['id']] );
                    }
                }

                if (strtolower( $field['label'] ) == 'last name' || 
                    strtolower( $field['label'] ) == 'lastname') { 

                    if (isset($entry[$field['id']])) {
                        $lastname = trim( $entry[$field['id']] );
                    }
                }

                if (strtolower( $field['label'] ) == 'email' || 
                    strtolower( $field['label'] ) == 'email address') { 

                    if (isset($entry[$field['id']])) {
                        $email = trim( $entry[$field['id']] );
                    }
                }
            }
            
            //  Do we have what we need?
            if (!$email) {
                $this->nflog_error( 'Error recording conversion - invalid email address' );
                return;
            }

            //  Build the post fields
            $post_fields = http_build_query(
                array( 
                    'token' => $this->tracking_server_token,
                    'sessionid' => $sessionid,
                    'pagelink' => $pagelink,
                    'pagetitle' => $pagetitle,
                    'contentid' => $contentid,
                    'firstname' => $firstname,
                    'lastname' => $lastname,
                    'email' => $email,
                    'formid' => $entry[id],
                    'formtype' => $entry[form_id],
                    'conversiondesc' => 'Website Form - ' . $form['title']
            ));

            //  Push the conversion
            $ch = curl_init( $this->tracking_server_url . "/api/v1/conversion?token=" . $this->tracking_server_token );
            curl_setopt($ch, CURLOPT_HEADER,            0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,    true);
            curl_setopt($ch, CURLOPT_POST,              1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,    10);
            curl_setopt($ch, CURLOPT_TIMEOUT,           10);
            curl_setopt($ch, CURLOPT_POSTFIELDS,        $post_fields );
                                
            $output = curl_exec($ch);
            curl_close($ch);

            //  Get the session id if one was returned, 
            //  that means the user wasn't tracked before
            if ($output)
            {
                $expires = time() + ( 365 * 86400 * 10 );
                SetCookie( 'nfsession', $output, $expires, '/', '', 0, 0 );
            }

            //  Otherwise, record the error
            else {
                $this->nflog_error( 'Error Recording Conversion' );
            }
        }

        catch(Exception $e){
            $this->nflog_error( 'Error Recording Conversion' );
            return;
        }

        return;
    }

    /**
     * Track content activity
     * 
     * @param  object $post
     */
    function recordDraftContentActivity( $post ){

        //  Push the activity
        try {
        
            $pagetitle      = $post->post_title;
            $type           = $post->post_type;
            $publishdate    = $post->post_modified;
            $wordcount      = str_word_count( strip_tags( $post->post_content ));
            $link           = get_permalink( $ID );
            $authorname     = get_author_name( $post->post_author );

            $post_fields = http_build_query(
                array( 
                    'token' => $this->tracking_server_token,
                    'pagelink' => $link,
                    'pagetitle' => $pagetitle,
                    'contenttype' => $type,
                    'wordcount' => $wordcount,
                    'publishdate' => $publishdate,
                    'authorname' => $authorname
            ));

            $ch = curl_init( $this->tracking_server_url . "/api/v1/contentactivity?token=" . $this->tracking_server_token );
            curl_setopt($ch, CURLOPT_HEADER,            0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,    true);
            curl_setopt($ch, CURLOPT_POST,              1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,    10);
            curl_setopt($ch, CURLOPT_TIMEOUT,           10);
            curl_setopt($ch, CURLOPT_POSTFIELDS,        $post_fields);
                                
            $output = curl_exec($ch);
            curl_close($ch);
        }

        catch(Exception $e){
            $this->logError( 'Error recording draft content activity' );
            return;
        }

        return;
    }

    /**
     * Track content activity
     * 
     * @param  object $post
     */
    function recordNewContentActivity( $post ){

        $post = get_post( $post );

        //  If the post is being edited after being published, 
        //  do not count it as a content activity
        if ($post->post_date != $post->post_modified) {
            return;
        }

        //  Push the activity
        try {

            $postid         = $post->ID;
            $pagetitle      = $post->post_title;
            $type           = $post->post_type;
            $publishdate    = $post->post_modified;
            $status         = $post->post_status;
            $wordcount      = str_word_count( strip_tags( $post->post_content ));
            $link           = get_permalink( $post->ID );
            $authorname     = get_author_name( $post->post_author );

            if ($status == 'inherit') {
                return;
            }

            if ($postid) {
                
                if ($post_meta = get_post_meta($postid)) {

                    foreach( $post_meta as $post_meta_name => $post_meta_value ) {
                        if ($meta_wordcount = str_word_count( strip_tags( $post_meta_value[0] ))) {

                            if ($meta_wordcount > 1) {
                                $wordcount += $meta_wordcount;
                            }

                        }
                    }
                }
            }

            //  Build the post fields
            $post_fields = http_build_query(
                array( 
                    'token' => $this->tracking_server_token,
                    'pagelink' => $link,
                    'pagetitle' => $pagetitle,
                    'contenttype' => $type,
                    'wordcount' => $wordcount,
                    'publishdate' => $publishdate,
                    'authorname' => $authorname,
                    'postid' => $postid,
                    'status' => $status
            ));

            //  Push the conversion
            $ch = curl_init( $this->tracking_server_url . "/api/v1/contentactivity?token=" . $this->tracking_server_token );
            curl_setopt($ch, CURLOPT_HEADER,            0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,    true);
            curl_setopt($ch, CURLOPT_POST,              1);
            curl_setopt($ch, CURLOPT_POSTFIELDS,        $post_fields );
                                
            

            $output = curl_exec($ch);
            curl_close($ch);
        }

        catch(Exception $e){
            $this->logError( 'Error recording new content activity' );
            return;
        }
    }

    /**
     * Add the tracking javascript to the site[type]
     */
    function generateTrackingJS(){
        
        global $post, $_SERVER, $_GET;
        
        $pagelink = '';
        $pagetitle = '';
        $pageid = '';
        
        $referrer       = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';
        $urlroot        = isset( $_SERVER['HTTP_HOST'] )    ? $_SERVER['HTTP_HOST'] : '';
        $utm_campaign   = isset( $_GET['utm_campaign'] )    ? $_GET['utm_campaign'] : '';
        $utm_content    = isset( $_GET['utm_content'] )     ? $_GET['utm_content'] : '';
        $utm_source     = isset( $_GET['utm_source'] )      ? $_GET['utm_source'] : '';
        $utm_medium     = isset( $_GET['utm_medium'] )      ? $_GET['utm_medium'] : '';

        //  Get the external tracking id if one
        //  exists
        $getExternalIDJS = '';
        $external_source = '';

        if (class_exists('GFActOn')) {
            if (method_exists('GFActOn', 'getActOnTrackingIDJavascript')) {
                $getExternalIDJS = GFActOn::getActOnTrackingIDJavascript();
                $external_source = 'acton';
            }
        }

        // //  Do not track localhost-served sites
        // // $whitelist = array(
        // //     '127.0.0.1',
        // //     '::1'
        // // );

        // if (in_array($_SERVER['REMOTE_ADDR'], $whitelist)){
        //     return;
        // }

        //  Defer the loading
        if ($this->get_ajax_load_setting()) {

            //  Add the js code for this form
            $js_string = "
                <script>
                (function (NfTrackerLoader, $) {

                    loadTracker('" . $urlroot . "');

                }(window.NfTrackerLoader = window.NfTrackerLoader || {}, jQuery));
                </script>";

            print $js_string;
            return;
        }

        ob_start();

        ?>
        <script>
        jQuery(document).ready(function() {

            setTimeout( function(){

                var sessionId = readCookie('nfsession') || 0;
                var pageLink = window.location.href;
                var pageTitle = document.title ? document.title : pageLink;
                var externalID;

                <?php print $getExternalIDJS; ?>

                var parms = { token:            '<?php print $this->tracking_server_token ?>',
                              sessionid:        sessionId,
                              pagelink:         pageLink,
                              pagetitle:        pageTitle,
                              contentid:        '',
                              referrer:         '<?php print $referrer ?>',
                              urlroot:          '<?php print $urlroot ?>',
                              utm_campaign:     '<?php print $utm_campaign ?>',
                              utm_content:      '<?php print $utm_content ?>',
                              utm_source:       '<?php print $utm_source ?>',
                              utm_medium:       '<?php print $utm_medium ?>',
                              external_id:      externalID,
                              external_source:  '<?php print $external_source; ?>'
                };

                jQuery.ajax( {
                    url: "<?php print $this->tracking_server_url ?>/api/v1/pagehit",
                    data: parms,
                    cache: false,
                    success: function( response ){
                        
                        if (response) {
                            createCookie( 'nfsession', response, 3650 )
                        }
                        
                    }
                });
            }, 1000 );

        });

        function createCookie(name, value, days) {
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

        function readCookie(name) {
            var nameEQ = escape(name) + "=";
            var ca = document.cookie.split(';');
            for (var i = 0; i < ca.length; i++) {
                var c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) return unescape(c.substring(nameEQ.length, c.length));
            }
            return null;
        }

        function eraseCookie(name) {
            createCookie(name, "", -1);
        }

        </script>               
        <?php

        $buffer = ob_get_clean();
        $buffer = str_replace(array("\r\n","\r","\t","\n",'  ','    ','     '), '', $buffer);
    
        print $buffer;
    
    } 


    /**
     * Determine if ajax content loading is being used
     * 
     * @return boolean
     */
    function get_ajax_load_setting()
    {
        if (!isset($this->settings->options['ajax_tracker_loading'])) {
            return false;
        }

        $result = $this->settings->options['ajax_tracker_loading'];

        if ('ajax_tracker_loading' === $result) {
            return true;
        }

        // Default
        return false;
    }

    /**
     * If fullpage caching is enabled, init the javascript
     * resources that control the ajax tracker loading
     */
    function init_ajaxscripts() {
        
        //  Require jQuery
        wp_enqueue_script( 'jquery' );

        //  Is ajax form loading enabled?
        if (!$this->get_ajax_load_setting()) {
            return;
        }

        //  Add the ajax script
        wp_enqueue_script( 
            'ajax_loadtracker', 
            NFTRACKING_URL . '/js/ajax_tracker.js', 
            array('jquery'), 
            TRUE 
        );

        //  Add the listener
        wp_localize_script( 
            'ajax_loadtracker', 
            'AjaxTrackerController', 
            array(
                'url'   => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( "process_loadtracker_nonce" ),
            )
        );
    }

    /**
     * Ajax listener to load the tracker. Used when fullpage caching
     * is enabled, to allow cookies to be processed and the user's
     * tracking cookie to be accessed.
     *
     * @return json
     */
    function process_loadtracker() {
        
        //  Is ajax form loading enabled?
        if (!$this->get_ajax_load_setting()) {

            //  No, do nothing
            wp_die();
        }

        //  Verify the ajax call
        check_ajax_referer( 'process_loadtracker_nonce', 'nonce' );

        //  Get the form ID and the original random ID
        $referrer = isset( $_POST['referrer'] ) ? htmlspecialchars( $_POST['referrer'] ) : '';
        $host = isset( $_POST['host'] ) ? htmlspecialchars( $_POST['host'] ) : '';
        $utm_campaign = isset( $_POST['utm_campaign'] ) ? htmlspecialchars( $_POST['utm_campaign'] ) : '';
        $utm_content = isset( $_POST['utm_content'] ) ? htmlspecialchars( $_POST['utm_content'] ) : '';
        $utm_source = isset( $_POST['utm_source'] ) ? htmlspecialchars( $_POST['utm_source'] ) : '';
        $utm_medium = isset( $_POST['utm_medium'] ) ? htmlspecialchars( $_POST['utm_medium'] ) : '';
        
        //  Get the external tracking id if one
        //  exists
        $getExternalIDJS = '';
        $external_source = '';

        if (class_exists('GFActOn')) {

            if (method_exists('GFActOn', 'getActOnTrackingIDJavascript')) {
                $getExternalIDJS = GFActOn::getActOnTrackingIDJavascript();
                $external_source = 'acton';
            }
        }

        //  Build the response
        $response = Array( 
            'tracking_code' => $_COOKIE['nfsession'],
            'referrer' => $referrer, 
            'urlroot' => $host,
            'tracking_url' => $this->tracking_server_url,
            'token' => $this->tracking_server_token,
            'utm_campaign' => $utm_campaign,
            'utm_content' => $utm_content,
            'utm_source' => $utm_source,
            'utm_medium' => $utm_medium,
            'getexternalid_js' => $getExternalIDJS,
            'external_source' => $external_source

        );

        //  Return the response
        wp_send_json_success( $response );
        
        //  All done
        wp_die();
    }

    /**
     * Verify the Newfangled Logging plugin is active
     */
    function verifyLogging( $transient ) {
        
        global $nflogging;

        if (!method_exists($nflogging, 'verifyLogging') OR (VERIFY_FAILED == $nflogging->verifyLogging())) {
            
            deactivate_plugins( plugin_basename( __FILE__ ) );    

        }

        return $transient;
    }
}}

//  Initialize the plugin
global $nftracking;
if (class_exists("NFTracking") && !$nftracking) {
    $nftracking = new NFTracking();   
}
