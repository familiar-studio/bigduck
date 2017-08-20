<?php
/**
 * Newfangled Progressive Profiling
 *
 * @package   Newfangled Progressive Profiling
 * @author    Newfangled
 * @link      http://newfangled.com/plugin-support-policy
 * @copyright Newfangled 2016
 */

//*********************************************************************************************************
/*
Plugin Name: Newfangled Progressive Profiling
Plugin URI: http://newfangled.com/plugin-support-policy
Description: Progressive profiling management. Uses plugins to interface with individual form sources (Gravity Forms, etc).
Version: 2.0.9
Author: Newfangled
Author URI: http://newfangled.com
Text Domain: nfprofiling
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

//  Plugin constants
define( 'NFPROFILING_VERSION',      '2.0.9' );
define( 'NFPROFILING_RELEASE_DATE', date_i18n( 'F j, Y', '1499706564' ) );
define( 'NFPROFILING_DIR',          plugin_dir_path( __FILE__ ) );
define( 'NFPROFILING_URL',          plugin_dir_url( __FILE__ ) );

//  Provide plugin updates
require 'plugin_update_check.php';
$MyUpdateChecker = new PluginUpdateChecker_2_0 (
    'https://kernl.us/api/v1/updates/58920dd9d253d976c4380efb/',
    __FILE__,
    'nfprofiling',
    1
);

//  Define the plugin class
if (!class_exists("NFProfiling")) {

    /**
     *
     * Class: NFProfiling
     *
     * Provides progressive profiling for gravity forms, as well as the smart CTA system.
     *
     * Submodules in the /modules directory provide specific ingrations with systems like 
     * Gravity Forms, as well as defining the available CTAs for the Smart CTA picker. 
     * New modules can be added to integrate with other systems, such as a different 
     * form builder plugin. 
     * 
     */
    class NFProfiling {
        var $modules, 
            $data, 
            $settings, 
            $options_page;
        
        private static $_this;

        //  Never show the same CTA twice on one page
        public static $shown_ctas;

        static function this() {
            return self::$_this;
        }
        
        function __construct() {    

            self::$_this = $this;

            add_action( 'init',                 array( $this, 'init') );
            add_action( 'admin_init',           array( $this, 'admin_init') );
            add_action( 'plugins_loaded',       array( $this, 'load_textdomain' ));
            add_filter('pre_set_site_transient_update_plugins', array( $this, 'verifyLogging' ), 10, 1);
            $this->load_widget();

        }

        /**
         * Load plugin textdomain.
         */
        function load_textdomain() {
          load_plugin_textdomain( 'nfprofiling', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
        }

        /**
         * Start the listeners, and init the modules, settings, and scripts
         *
         * Also sets a cookie indicating that profiling has been started
         */
        function init() {
            
            add_action( 'media_buttons',                        array( $this, 'add_form_button' ), 999 );
            add_action( 'admin_print_footer_scripts',           array( $this, 'add_mce_popup' ) );
            add_filter('nfprofiling_getsmartctas',              array( $this, 'define_smart_ctas'), 1, 1);
            
            add_action( 'wp_enqueue_scripts',                   array( $this, 'init_ajaxscripts' ) );

            add_shortcode( 'smartcta', array($this,  'shortcode_smartcta'),     10, 2);

            $this->register_scripts();
            $this->load_modules();
            $this->load_settings();
            $this->init_module_listeners();

            $expires = time() + (3600 * 1000 * 24 * 365);
            SetCookie( 'nfprofiling_started', true, $expires, '/', '', 0, 0 );
        }

        /**
         * Load the admin scripts
         */
        function admin_init() {

            $this->load_settings();
            $this->enqueue_admin_scripts();
        }

        /**
         * Register the scripts to handle the 'Smart CTA' media button popup interface
         */
        function register_scripts() {

            $base_url = NFPROFILING_URL;
            $version  = NFPROFILING_VERSION;

            wp_register_script( 'nfprofiling_shortcode_ui', $base_url . "js/shortcode-ui.js", array( 'jquery' ), $version, true );
        }

        /**
         * Enqueue the scripts to handle the 'Smart CTA' media button popup interface
         */
        function enqueue_admin_scripts() {
            wp_enqueue_script( 'nfprofiling_shortcode_ui' );
            wp_enqueue_script( 'jquery-ui-core' );
            wp_enqueue_script( 'jquery-ui-sortable' );
        }

        /**
         * Cache getter (currently disabled)
         */
        function cache_get( $cachename ) {
        //  return wp_cache_get( $cachename );
        //  return get_transient( $cachename );
        }

        /**
         * Cache setter (currently disabled)
         */
        function cache_set( $cachename, $object ) {
        //  wp_cache_set( $cachename, $object, 'nfprofiling', 660 );
        //  set_transient( $cachename, $object, 660 );
        }

        /**
         * Shortcode to show a smart CTA
         * 
         * @param  array $atts - the shortcode attributes
         *
         * @return string - the generated html
         */
        function shortcode_smartcta( $atts ){

            extract(shortcode_atts(array(
                'id' => 0,
                'display_title' => false
            ), $atts));

            ob_start();
            $buffer_html = '';

            $ids = explode( ',', $id );

            if ($ids) {

                $available_smartctas = $this->get_smartcta_forms();
                $cta_shown = false;

                foreach( $ids as $id )
                {
                    if (!$cta_shown) {
                        
                        foreach( $available_smartctas as $instance_form ) {
                            if ($instance_form['id'] == $id) {

                                if (isset($instance_form['function'])) {

                                    $this_class = $instance_form['function'][0];
                                    $this_func = $instance_form['function'][1];
                                    
                                    if ($this_class && $this_func) {
                                        if (is_callable( Array( $this_class, $this_func ))) {
                                            $instance_form = call_user_func( Array( $this_class, $this_func ));
                                        }
                                    }
                                
                                }

                                if (!$instance_form) {
                                    continue;
                                }

                                if ($this->has_cta_been_submitted( $instance_form )) {
                                    continue;
                                }

                                foreach( $this->modules as $module ) {

                                    if ($module->moduleid == $instance_form['module']) {

                                        $module->render_form( $instance_form['id'], $instance_form['name'] );

                                    }
                                }

                                $instance_form = apply_filters( 'nfprofiling_render_smartcta_form', $instance_form );

                                $cta_shown = true;
                            }
                        }
                    }
                }
            }

            $buffer_html = ob_get_clean();
            return $buffer_html;

        }

        /**
         * Load the sub-modules, which provide the actual integration with external 
         * sources such as Gravity Forms
         */
        function load_modules() {

            if ( $nfprofiling_modules = $this->cache_get( 'nfprofiling_modules' ) ) {
                $this->modules = $nfprofiling_modules;
                return;
            } 

            if (!class_exists("NfProfiling_Module")) { 
                require(NFPROFILING_DIR . 'modules/nfprofiling_module.php');
            }

            $this->modules = Array();
            $nfprofiling_moduleclasses = Array();

            $dir = new DirectoryIterator(dirname(__FILE__) . '/modules');
            
            foreach ($dir as $fileinfo) {
                
                if (!$fileinfo->isDot())  {
                    
                    //  Ignore the abstract class
                    if ($fileinfo->getFilename() == 'nfprofiling_module.php') {
                        continue;
                    }

                    //  Ignore hidden Mac OS files
                    if ($fileinfo->getFilename() == '.DS_Store') {
                        continue;
                    }

                    //  Include the module file
                    $filename = $fileinfo->getFilename();
                    require(NFPROFILING_DIR . 'modules/' . $filename );
                }
            }
            
            if ($nfprofiling_moduleclasses) {
                foreach( $nfprofiling_moduleclasses as $classname ) {
                    $this->modules[] = new $classname( $this );
                }
            }

            $this->cache_set( 'nfprofiling_modules', $this->modules );

        }

        /**
         * Init the settings class 
         */
        function load_settings() {

            if (isset($this->settings)) {
                return;
            }
            
            if ( $nfprofiling_settings = $this->cache_get( 'nfprofiling_settings' ) ) {
                $this->settings = $nfprofiling_settings;
                return;
            } 

            if (!$this->settings && !class_exists("NFProfiling_Settings")) { 
                require(NFPROFILING_DIR . 'newfangled-progressive-profiling-settings.php');
            }

            $this->settings = new NFProfiling_Settings( $this );    
            $this->cache_set( 'nfprofiling_settings', $this->settings );
        }

        /**
         * Init the widget class 
         */
        function load_widget() {

            if (!class_exists("NFProfiling_Widget")) { 
                require_once(NFPROFILING_DIR . 'newfangled-progressive-profiling-widget.php');
            }
        }

        /**
         * Init the data connection class 
         */
        function load_dataconnection() {

            if (isset($this->dataconnection)) {
                return;
            }
            
            if (!isset($this->dataconnection) && !class_exists("NFProfiling_Data")) { 
                require(NFPROFILING_DIR . 'newfangled-progressive-profiling-data.php');
            }

            $this->dataconnection = new NFProfiling_Data( $this );  
            
            
        }

        /**
         * Get a master list of all the fields that can be progressivly profiled, 
         * as defined by the active sub-module(s)
         *
         * @return array - the list of fields
         */
        function get_master_fields_list() {

            $this->load_settings();

            if (!$this->modules) {
                return;
            }

            if (isset($this->master_fields_list)) {
                return $this->master_fields_list;
            }

            if ( $nfprofiling_masterfields = $this->cache_get( 'nfprofiling_masterfields' ) ) {
                $this->master_fields_list = $nfprofiling_masterfields;
                return;
            } 
            
            $master_fields_list = Array();

            foreach( $this->modules as $module )
            {
                if (!isset($module->modulename)) {
                    continue;
                }

                if (!isset($this->settings->options['enabled_modules'][ $module->modulename ])) {
                    continue;
                }

                $module_name   = $module->modulename;
                $module_fields = $module->define_fields();

                if ($module_fields) {
                    foreach( $module_fields as $instancename => $fields ) {
                        if ($fields) {
                            foreach( $fields as $field ) {
                                $master_fields_list[ $field ][ $module_name ][] = $instancename;
                            }
                        }
                    }
                }
            }

            ksort($master_fields_list);

            $this->master_fields_list = $master_fields_list;
            $this->cache_set( 'nfprofiling_masterfields', $this->master_fields_list );

            return $this->master_fields_list;
        }

        /**
         * Get the email address from the current user's profile
         * 
         * @return string - the user's email
         */
        function get_user_profile_email() {

            $this->load_dataconnection();
            $this->dataconnection->update_table();
            
            if ($user_profile_email = $this->dataconnection->get_profile_email()) {
                return $user_profile_email;
            } else {
                return '';
            }
        }

        /**
         * Get the current user's profile, if it exists
         * 
         * @return array - the current user's profile
         */
        function get_user_profile() {

            if (isset($this->user_profile)) {
                return $this->user_profile;
            }

            $this->load_dataconnection();
            $this->dataconnection->update_table();
            
            if ($user_profile = $this->dataconnection->get_profile()) {
                $this->user_profile = $user_profile;
                return $user_profile;
            } else {
                return Array();
            }
        }

        /**
         * Update the user's profile
         * 
         * @param  string $user_email_value - the email address of the user to store
         * @param  array $user_stored_profile  - the existing profile to update
         */
        function update_user_profile( $user_email_value, $user_stored_profile ) {

            $this->load_dataconnection();
            $this->dataconnection->update_profile($user_email_value, $user_stored_profile );
        }

        /**
         * Get the last of available CTAs. These are provided by the sub-modules, or externally
         * via hooks. 
         * 
         * @param  boolean $ignore_excluded - the plugin settings allows for some ctas to be excluded
         *
         * @return array - the list of available CTAs to choose from when adding a smart CTA
         */
        function get_smartcta_forms( $ignore_excluded=true ) {

            if (!$this->modules) {
                return;
            }

            if ( $nfprofiling_availableforms = $this->cache_get( 'nfprofiling_availableforms' ) ) {
                return $nfprofiling_availableforms;
            } 

            $available_forms = Array();

            foreach( $this->modules as $module ) {
                if (!isset($this->settings->options['enabled_modules'][ $module->modulename ])) {
                    continue;
                }

                $module_name      = $module->modulename;
                $moduleid         = $module->moduleid;
                $available_forms += $module->get_smartcta_forms( $ignore_excluded );
                $available_forms += apply_filters( 'nfprofiling_getsmartctas', $available_forms, $module_name, $moduleid );
            }

            $this->cache_set( 'nfprofiling_availableforms', $available_forms );
            return $available_forms;
        }

        /**
         * Render a cta item. Rendering will be handled by the source that provides
         * the cta - either a submodule, or an external source via a hook
         * 
         * @param  array $instance_form - the cta item being rendered
         *
         * @return boolean - tells whether the cta was rendered successfully
         */
        function render_smartcta_form( $instance_form )
        {
            if (!$this->modules) {
                return false;
            }

            foreach( $this->modules as $module ) {
                if ($module->moduleid == $instance_form['module']) {
                    $module->render_form( $instance_form['id'], $instance_form['name'] );
                    return true;
                }
            }

            $show_title = $this->settings->options['smartcta_showtitle'];
            $show_desc = $this->settings->options['smartcta_showdesc'];
            $show_title = ($show_title == 1) ? true : false;
            $show_desc = ($show_desc == 1) ? true : false;

            $instance_form = apply_filters( 'nfprofiling_render_smartcta_form', $instance_form, $show_title, $show_desc );

            if ($instance_form['rendered'] == true) {
                return true;
            }
        
            return false;
        }

        /**
         * Has the specified CTA been completed by the user?
         * 
         * @param  array $instance_form - the CTA item
         *
         * @return boolean 
         */
        function has_cta_been_submitted( $instance_form )
        {
            if (!$this->modules) {
                return false;
            }

            if ($this->shown_ctas) {
                if (in_array($instance_form['id'], $this->shown_ctas)) {
                    return true;
                }
            }

            foreach( $this->modules as $module ) {
                if ($module->moduleid == $instance_form['module']) {
                    if ($module->has_cta_been_submitted( $instance_form['id'] )) {
                        return true;
                    }
                }
            }

            $instance_form = apply_filters( 'nfprofiling_has_cta_been_submitted', $instance_form );

            if ($instance_form['submitted'] == true) {
                return true;
            }

            $this->shown_ctas[] = $instance_form['id'];
            return false;
        }

        /**
         * Load the sub-module hook listeners, if any
         */
        function init_module_listeners() {

            if (!$this->modules) {
                return;
            }

            $master_fields_list = Array();

            foreach( $this->modules as $module ) {

                if (!isset($module->modulename)) {
                    continue;
                }

                if (!isset($this->settings->options['enabled_modules'][ $module->modulename ]))
                {
                    continue;
                }

                $module_name   = $module->modulename;
                $module_fields = $module->init_listeners();
            }
        }
    
        /**
         *  Action target that adds the 'Insert Smart CTA' button to the post/page edit screen
         */
        function add_form_button() {

            // display button matching new UI
            echo '<style>.gform_media_icon{
                    background-position: center center;
                    background-repeat: no-repeat;
                    background-size: 16px auto;
                    float: left;
                    height: 16px;
                    margin: 0;
                    text-align: center;
                    width: 16px;
                    padding-top:10px;
                    }
                    .gform_media_icon:before{
                    color: #999;
                    padding: 7px 0;
                    transition: all 0.1s ease-in-out 0s;
                    }
                    .wp-core-ui a.gform_media_link{
                     padding-left: 0.4em;
                    }
                 </style>
                  <a href="#" class="button nfprofiling_media_link" id="add_smartcta" title="' . esc_attr__( 'Add Smart CTA', 'nfprofiling' ) . '">' . esc_html__( 'Add Smart CTA', 'nfprofiling' ) . '</a>';
        }

        /**
         * Action target that displays the popup to insert a form to a post/page
         */
        function add_mce_popup() {
            ?>
            <script>
                function InsertSmartCtaForm() {
                    var form_ids = jQuery("#add_smartcta_ids").val();
                    window.send_to_editor("[smartcta ids=\"" + form_ids + "\"]");
                }
            </script>

            <div id="select-smart-ctas" style="display:none;">
                <div id="select-smart-ctas-ui-wrap" class="wrap">
                    <div id="select-smart-ctas-ui-container">
                        <?php 
                            $widget_class = new NFProfiling_Widget();
                            $widget_class->form();
                        ?>
                        <input type="button" class="button-primary" id="select-smart-ctas-submit" value="Insert" />
                        <a id="nfprofiling-cancel-shortcode" class="button" style="color:#bbb;" href="#">Cancel</a>
                    </div>
                </div>
            </div>

        <?php
        }

        /**
         * Define the 'global' Smart CTAs
         * 
         * @param  array $cta_items
         *
         * @return array - the CTA option
         */
        function define_smart_ctas( $cta_items ){

            global $nfwebinars, $nfgated;

            //  A CTA to show all the gated content AND webinars
            if (is_object($nfwebinars) && is_object($nfgated)) {
                $item = Array();
                $item['module']     = 'global';
                $item['moduledesc'] = 'Global';
                $item['id']         = 'allwebinarsandwhitepapers';
                $item['name']       = 'All Gated Content and Webinars';
                $item['function']   = Array($this, 'get_next_available_either_cta');
                $item['details']    = 'Cycle through all the Gated Content and Webinar CTAs, ordered by published date, until the user has completed all of them.';
                $cta_items[]        = $item;
            }

            //  Return the Smart CTA instances
            return $cta_items;

        }

        /**
         * Get the next available Gated Content *OR* Webinar cta for the user
         * 
         * @return array -  the CTA to show
         */
        function get_next_available_either_cta(){

            global $nfwebinars, $nfgated;

            //  Get the plugin options
            if (!$plugin_settings = $this->settings->options) {
                return $cta_items;
            }

            //  Get all the 'gated' posts
            $args = array(
               'post_status' => 'publish',
               'post_type' => get_post_types(), 
               'posts_per_page' => -1,
               'orderby' => 'date',
               'order'   => 'DESC',
               'meta_query' => array(
                   'relation' => 'OR',
                   array(
                       'key' => 'gated_content_enable',
                       'value' => 1,
                       'compare' => '=',
                   ),
                   array(
                       'key' => 'webinar_enable',
                       'value' => 1,
                       'compare' => '=',
                   )
               )
            );
            $either_posts = new WP_Query($args);

            //  Any posts found?
            if( $either_posts->have_posts()) {

                //  Store the global post obj
                global $post, $prevpost;
                $prevpost = $post;

                //  For each gated post
                while($either_posts->have_posts()) {

                    //  Load up the post obj
                    $either_posts->the_post();

                    global $nfprofiling;

                    //  Get the form id
                    if (get_post_meta( get_the_ID(), 'gated_content_enable', true )) {

                        $gated_content_form_id  = get_post_meta( get_the_ID(), 'gated_content_form_id', true );
                        
                        //  Set the Smart CTA instance  
                        $item = Array();
                        $item['module']     = 'gatedcontent';
                        $item['moduledesc'] = 'Gated Content';
                        $item['id']         = $gated_content_form_id . '_' . get_the_id();
                        $item['name']       = 'Gated Content: ' . get_the_title();

                        if ($nfprofiling->shown_ctas) {
                            if (in_array($item['id'], $nfprofiling->shown_ctas)) {
                                continue;
                            }
                        }

                        $checked_item = $nfgated->check_cta_form( $item );

                        if (!isset($checked_item['submitted']) || $checked_item['submitted'] != true) {
                            return $checked_item;
                        }

                    } else {
                        
                        //  Set the Smart CTA instance  
                        $item = Array();
                        $item['module']     = 'webinar';
                        $item['moduledesc'] = 'Webinar';
                        $item['id']         = 'webinar_' . get_the_id();
                        $item['name']       = 'Webinar: ' . get_the_title();

                        if ($nfprofiling->shown_ctas) {
                            if (in_array($item['id'], $nfprofiling->shown_ctas)) {
                                continue;
                            }
                        }

                        $checked_item = $nfwebinars->check_cta_form( $item );

                        if (!isset($checked_item['submitted']) || $checked_item['submitted'] != true) {
                            
                            return $checked_item;
                        }

                    }
                }
            }
        }

        /**
         * Determine if ajax form and CTA loading is being used
         * 
         * @return boolean
         */
        function get_ajax_load_setting()
        {
            if (!isset($this->settings->options['ajax_form_loading'])) {
                return false;
            }

            $result = $this->settings->options['ajax_form_loading'];

            if ('ajax_form_loading' === $result) {
                return true;
            }

            // Default
            return false;
        }

        /**
         * If fullpage caching is enabled, init the javascript
         * resources that control the ajax form loading
         */
        function init_ajaxscripts() {
            
            //  Is ajax form loading enabled?
            if (!$this->get_ajax_load_setting()) {
                return;
            }

            //  Add the ajax script
            wp_enqueue_script( 
                'ajax_loadsmartcta', 
                NFPROFILING_URL . '/js/ajax_smartctas.js', 
                array('jquery'), 
                TRUE 
            );

            //  Add the listener
            wp_localize_script( 
                'ajax_loadsmartcta', 
                'AjaxController', 
                array(
                    'url' => admin_url( 'admin-ajax.php' ),
                    'nonce' => wp_create_nonce( "process_loadsmartctawidget_nonce" ),
                )
            );
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
    }
}

//  Initialize the plugin
global $nfprofiling;
if (class_exists("NFProfiling") && !$nfprofiling) {
    $nfprofiling = new NFProfiling();   
}
