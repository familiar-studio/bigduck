<?php
/**
 * Newfangled Gated Content
 *
 * @package   Newfangled Gated Content
 * @author    Newfangled
 * @link      http://newfangled.com/plugin-support-policy
 * @copyright Newfangled 2016
 */

//*********************************************************************************************************
/*
Plugin Name: Newfangled Gated Content
Plugin URI: http://newfangled.com/plugin-support-policy
Description: Allows for content or post types to be designated as 'gated'. Gated content will require a form (form type selected per post) to be completed before the user can view the rest of the gated content. 
Version: 2.0.4
Author: Newfangled
Author URI: http://newfangled.com
Text Domain: nfgated
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
    'https://kernl.us/api/v1/updates/58924e999f929649d4bc4f0a/',
    __FILE__,
    'nfgated',
    1
);

//  Plugin constants
define( 'NFGATED_VERSION',      '2.0.4' );
define( 'NFGATED_RELEASE_DATE', date_i18n( 'F j, Y', '1497295084' ) );
define( 'NFGATED_DIR',          plugin_dir_path( __FILE__ ) );
define( 'NFGATED_URL',          plugin_dir_url( __FILE__ ) );
define( 'NFGATED_FORM_ID_FIELD_NAME', 'Gated Content ID' );

//  Define the plugin class
if (!class_exists("NFGated")) {

    /**
     *
     * Class: NFGated
     *
     * The Newfangled Gated Content provides the back-end logic and functionality to make specific posts or pages "gated" - 
     * meaning a form completion is required before the details of the post or page can be viewed. 
     */
    class NFGated {
        var $modules,
            $data,
            $settings,
            $options_page;

        private static $_this;

        static function this() {
            return self::$_this;
        }

        function __construct() {

            self::$_this = $this;
            add_action( 'init',                 array( $this,'init') );
            add_action( 'admin_init',           array( $this,'admin_init') );
            add_action( 'plugins_loaded',       array($this,'load_textdomain' ));
            add_filter('pre_set_site_transient_update_plugins', array( $this, 'verifyLogging' ), 10, 1);
        }

        /**
         * Load plugin textdomain.
         */
        function load_textdomain() {
          load_plugin_textdomain( 'nfgated', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        }

        /**
         * Start all of the hooks/listeners
         */
        function init() {

            $this->load_settings();
            add_action("gform_after_submission",            array( $this,"store_submission"), 11, 3 );
            add_filter( "the_content",                      array( $this,"show_gated_content" ), 1, 1);
            add_filter('nfprofiling_getsmartctas',          array( $this,"define_smart_ctas"), 11, 1);
            add_filter('nfprofiling_render_smartcta_form',  array( $this,"render_smartcta_form"), 11, 3);
            add_filter('nfprofiling_has_cta_been_submitted',array( $this,"check_cta_form"), 11, 1);

            add_action('wp_enqueue_scripts',                array( $this, 'init_ajaxscripts' ) );

        }

        /**
         * Allow for custom meta options of posts that are 'gateable'
         */
        function admin_init() {

            $this->load_settings();
            add_action( 'add_meta_boxes',   array( $this,'nfgated_add_meta_boxes'), 10, 2 );
            add_action( 'save_post',        array( $this,'nfgated_save_meta_box_data'), 1 );

        }

        /**
         * Add meta boxes on post types that are 'gateable'
         * 
         * @param  string $post_type
         * @param  object $post
         */
        function nfgated_add_meta_boxes( $post_type, $post=null) {

            $screens = array();

            //  Get the post types
            if (!$post_types = get_post_types()) {
                return;
            }

            //  Get the plugin options
            if (!$plugin_settings = $this->settings->options) {
                return;
            }

            $screen = get_current_screen();

            //  For each post type
            foreach( $post_types as $post_type ) {

                //  Are we editing that post type?
                if ($post_type == $screen->post_type) {

                    //  Get the complete post type object
                    $post_type_object   = get_post_type_object( $post_type );

                    //  Get the post type description
                    $post_type_desc     = $post_type_object->labels->name;

                    //  Is this post type allowed to be 'gated'?
                    if (!isset($plugin_settings[$post_type_desc])) {

                        continue;
                    
                    }

                    //  Yes, include it
                    $screens[] = $post_type_object->slug;
                }
            }

            //  Anything to do?
            if (!$screens) {
                return;
            }

            //  If so, add the meta boxes
            foreach ( $screens as $screen ) {
                
                add_action( 'admin_notices', Array($this,'verify_post_gated_content_form') );

                //  Custom meta box to appear when editing a post that can be 'gated'
                add_meta_box(
                    'nfgated',
                    __( 'Gated Content', 'nfgated' ),
                    array( $this,'nfgated_meta_box_callback'),
                    $screen,
                    'normal',
                    'high'
                );
            }
        }

        /**
         * Makes sure all the forms selected for a single post have the correct 
         * hidden fields. 
         */
        function verify_post_gated_content_form() {
            
            global $post;

            if (!$post) {
                return;
            }

            if (!$gated_content_enable  = get_post_meta( $post->ID, 'gated_content_enable', true )) {
                return;
            }
                
            if (!$gated_content_form_id = get_post_meta( $post->ID, 'gated_content_form_id', true )) {
                
                ?>
                <div class="error notice">
                    <p><?php _e( 'Gated Content error.', 'nfgated'); ?></p>
                    <p><?php _e( 'A gated content form must be selected', 'nfgated'); ?></p>
                </div>
                <?php

                return;

            }      

            if ( $post_form = $gated_content_form_id ) {

                if ( $form = GFAPI::get_form( $post_form ) ) {

                    $has_correct_field = FALSE;

                    //  For each submitted field
                    foreach( $form['fields'] as $field ) {
                       
                       if ($field['label'] == NFGATED_FORM_ID_FIELD_NAME ) {

                            if (    isset($field->inputName) && 
                                    $field->inputName == 'post_id' && 
                                    isset($field->defaultValue) && 
                                    $field->defaultValue == '{embed_post:ID}'
                            ){
                                $has_correct_field = TRUE;
                                break;
                           }

                        }
                    }

                    if (!$has_correct_field) {
                        ?>
                        <div class="error notice">
                            <p><?php _e( 'Gated Content error.', 'nfgated'); ?></p>
                            <p><?php _e( 'The global gated content form, <i>' . $form['title'] . '</i>, is missing the required field ' . NFGATED_FORM_ID_FIELD_NAME . ', with the following values:', 'nfgated'); ?></p>

                            <table style="border:1px solid #CCC;">
                            <tr><td>Default Value:&nbsp;</td><td>{embed_post:ID}</td></tr>
                            <tr><td>Parameter Name:&nbsp;</td><td>post_id</td></tr>
                            </table>
                            <br/>

                        </div>
                        <?php
                    }
                }
            }

        }

        /**
         * Build the meta boxes on post types that are 'gateable'
         * 
         * @param  object $post
         */
        function nfgated_meta_box_callback( $post ) {

            //  Get the plugin options
            if (!$plugin_settings = $this->settings->options) {
                return;
            }

            // Add an nonce field so we can check for it later.
            wp_nonce_field( 'nfgated_meta_box', 'nfgated_meta_box_nonce' );

            //  Get the meta values that currently exist
            $gated_content_enable  = get_post_meta( $post->ID, 'gated_content_enable', true );
            $gated_content_post_form_text   = get_post_meta( $post->ID, 'gated_content_post_form_text', true );
            $gated_content_form_id = get_post_meta( $post->ID, 'gated_content_form_id', true );

            if ($plugin_settings['always_gated']) { 

                //  Show the plugin functionality description - only if 'simple' mode is being used
                if (!$plugin_settings['disable_autocontent']) {
                    echo '<i>Visitors to this post will at first see only the post content, followed by the Gated Content form. Once this form has been completed, that visitor will see the custom field \'Protected Content\', entered below.</i><br/><br/>';
                    echo '<input type="hidden" id="gated_content_enable" name="gated_content_enable" value="1" />';
                    echo '<div style="display: block;" id="gated_content_more">';
                }

            } else {

                //  Show the plugin functionality description - only if 'simple' mode is being used
                if (!$plugin_settings['disable_autocontent']) {
                    echo '<i>When Gated Content is enabled, visitors to this post will at first see only the post content, followed by the Gated Content form. Once this form has been completed, that visitor will see the custom field \'Protected Content\', entered below.</i><br/><br/>';
                }
                
                //  Enable checkbox for this post
                echo '<label for="gated_content_enable">';

                if (isset($gated_content_enable) && $gated_content_enable == 1) {

                    echo '<input type="checkbox" checked id="gated_content_enable" name="gated_content_enable" value="1" />';

                }  else {
                  
                    echo '<input type="checkbox" id="gated_content_enable" name="gated_content_enable" value="1"  />';
                
                }

                _e( 'Make Content Gated', 'nfgated' );

                echo '</label><br/><br/> ';

                if (isset($gated_content_enable) && $gated_content_enable == 1) {
                    
                    echo '<div style="display: block;" id="gated_content_more">';

                } else { 

                    echo '<div style="display: none;" id="gated_content_more">';

                }
            }
            
            //  Form selector
            if ($plugin_settings['default_form']) {

                echo '<input type="hidden" id="gated_content_form_id" name="gated_content_form_id" value="' . $plugin_settings['default_form'] . '" />';

            } else {

                echo '<label for="gated_content_form_id" style="font-weight:bold;">';
                _e( 'Gated Content Form', 'nfgated' );
                echo '</label><br/> ';
                echo '<select id="gated_content_form_id" name="gated_content_form_id"><option value=""></option>';

                $forms = RGFormsModel::get_forms( null, 'title' );

                foreach( $forms as $form )
                {
                    if ( $check_form = GFAPI::get_form( $form->id ) ) {

                        foreach( $check_form['fields'] as $check_field) {

                            if ($check_field->label == 'Gated Content ID') {
                                
                                if ($gated_content_form_id && $gated_content_form_id == $form->id)
                                {
                                      echo '<option selected value="' . $form->id . '">' . $form->title . '</option>';
                                }

                                else
                                {
                                      echo '<option value="' . $form->id . '">' . $form->title . '</option>';
                                }
                            }
                        }
                    }
                }

                echo '</select><br/><br/>';
            }


            //  Protected content editor
            if (!$plugin_settings['disable_autocontent']) {
            
                echo '<hr/><br/>';
                echo '<label for="gated_content_post_form_text" style="font-weight:bold;">';
                _e( 'Protected Content', 'nfgated' );
                echo '</label><p  style="max-width:700px;"><i>The \'Protected Content\' is the content that displays after the gated content form has been submitted. A user returning to this page will continue to see this content, once they have submitted the form.</i></p><br/>';
                wp_editor( htmlspecialchars_decode($gated_content_post_form_text), 'nfgated_meta_box_gated_content_post_form_text', $settings = array('textarea_name'=>'gated_content_post_form_text') );
            
            }

            echo '</div>';

            ?>
            <script>
            jQuery(function($) {

                $('#gated_content_enable').change(function() {
                    if($(this).is(":checked")) {
                        $('#gated_content_more').show();
                    } else {
                        $('#gated_content_more').hide();
                    }
                });

            });

            </script>
            <?php
        }

        /**
         * Save the meta data
         * 
         * @param  integer $post_id
         */
        function nfgated_save_meta_box_data( $post_id ) {

            //  Valid nonce?
            if ( ! isset( $_POST['nfgated_meta_box_nonce'] ) ) {
                return;
            }

            //  Verify the nonce
            if ( ! wp_verify_nonce( $_POST['nfgated_meta_box_nonce'], 'nfgated_meta_box' ) ) {
                return;
            }

            //  Ignore auto-saves
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
                return;
            }

            //  Valid post_type?
            if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

                //  Can the user edit it?
                if ( ! current_user_can( 'edit_page', $post_id ) ) {
                    return;
                }

            } else {

                if ( ! current_user_can( 'edit_post', $post_id ) ) {
                    return;
                }
            }

            //  Gated content enable is checked
            if (isset($_POST['gated_content_enable']) && $_POST['gated_content_enable'] == 1)
            {
                $gated_content_enable   = TRUE;
            }

            //  Gated content enable is not checked
            else
            {
                $gated_content_enable   = false;
            }

            //  Get the other values
            $gated_content_post_form_text   = htmlspecialchars( $_POST['gated_content_post_form_text'] );
            $gated_content_form_id  = htmlspecialchars( $_POST['gated_content_form_id'] );

            //  Save the values
            update_post_meta( $post_id, 'gated_content_enable', $gated_content_enable );
            update_post_meta( $post_id, 'gated_content_post_form_text', $gated_content_post_form_text );
            update_post_meta( $post_id, 'gated_content_form_id', $gated_content_form_id );
        }

        /**
         * Cache getter
         * 
         * @param  string $cachename
         *
         * @return mixed
         */
        function cache_get( $cachename )
        {
            return wp_cache_get( $cachename );
        }

        /**
         * Cache setter
         * 
         * @param  string $cachename
         * @param  mixed $object
         */
        function cache_set( $cachename, $object )
        {
            wp_cache_set( $cachename, $object, 'nfgated', 660 );
        }

        /**
         * Load the plugin settings
         */
        function load_settings() {

            if (isset($this->settings)) {
                return;
            }

            if ( $nfgated_settings = $this->cache_get( 'nfgated_settings' ) ) {
                $this->settings = $nfgated_settings;
                return;
            }

            if (!$this->settings && !class_exists("NFGated_Settings")) {
                require(NFGATED_DIR . 'newfangled-gated-content-settings.php');
            }

            $this->settings = new NFGated_Settings( $this );
            $this->cache_set( 'nfgated_settings', $this->settings );
        }

        /**
         * Should the specified content be protected behind the Gated Content form?
         * 
         * @param  integer $id
         *
         * @return boolean
         */
        function is_content_gated( $id=0 ) {

            global $post;

            $this_post = get_post( $id );

            //  Get the plugin settings
            if (!$plugin_settings = $this->settings->options) {

                //  No, not gated
                return false;
            }

            //  Get the post object
            $post_type_object = get_post_type_object( $this_post->post_type );

            //  Get the post type desc
            $post_type_desc = $post_type_object->labels->name;

            //  Is it allowed to be gated?
            if (!isset($plugin_settings[$post_type_desc])) {

                //  No, not gated
                return false;
            }

            //  Get the meta data
            $gated_content_enable  = get_post_meta( $id, 'gated_content_enable', true );
            $gated_content_form_id  = get_post_meta( $id, 'gated_content_form_id', true );

            //  Is gated enabled?
            if (!$gated_content_enable) {

                //  No, not gated
                return false;
            }

            //  Get the ids
            $post_id = $id;
            $form_id = $gated_content_form_id;
            $key_name = $this->getGatedUniqueId( $form_id, $post_id );

            //  Has the user already completed the form?
            if (isset($_COOKIE[$key_name])) {

                //  No, not gated
                return false;
            }

            //  Yes, it should be gated
            return true;
        }

        /**
         * Show the gated content - either with form, or without
         * 
         * @param  string $content
         *
         * @return string
         */
        function show_gated_content( $content='' ) {

            global $post, $prevpost, $_GET;

            $prev_post = $post;

            //  Get the plugin settings
            if (!$plugin_settings = $this->settings->options) {
                return $content;
            }

            //  Get the current post type
            $post_type_object = get_post_type_object( get_post_type() );

            //  Get the current post description
            $post_type_desc = $post_type_object->labels->name;

            //  Is the post type allowed?
            if (!isset($plugin_settings[$post_type_desc])) {
                return $content;
            }

            //  Are we auto-modifying the post details content?
            if ($plugin_settings['disable_autocontent']) {
                return $content;
            }

            //  Get the meta values
            $gated_content_enable   = get_post_meta( $post->ID, 'gated_content_enable', true );
            $gated_content_form_id  = get_post_meta( $post->ID, 'gated_content_form_id', true );

            //  Do we have everything?
            if (!$gated_content_enable || !$gated_content_form_id) {
                return $content;
            }

            //  Get the ids
            $post_id = $post->ID;
            $form_id = $gated_content_form_id;
            $key_name = $this->getGatedUniqueId( $form_id, $post_id );

            $ajax_script = '';

            //  Allow for loading of the gated content via Ajax
            if ($this->get_ajax_load_setting()) {

                //  Are we already running an ajax call?
                if (isset($_POST['action']) && $_POST['action'] == 'getgatedcontent') {
                } else {

                    //  Add the js code for this form
                    $ajax_script = "<script>(function (NfGatedContentLoader, $) {loadGatedContent('gated_content_" . $post_id . "');}(window.NfGatedContentLoader = window.NfGatedContentLoader || {}, jQuery));</script>";
                }
            }

            // Get the protected content
            $gated_content_post_form_text = get_post_meta( $post->ID, 'gated_content_post_form_text', true );
            $gated_content_post_form_text = wpautop($gated_content_post_form_text);

            //  Has the gated content form been completed?
            //  Show the post-form template
            if (!$ajax_script && isset($_COOKIE[$key_name])) {

                $post = get_post($post_id);
                
                ob_start();
                
                if (file_exists(get_stylesheet_directory() . '/nfgated-post-form-content.php' )) {

                    include( get_stylesheet_directory() . '/nfgated-post-form-content.php' );

                } else {

                    include( NFGATED_DIR . '/templates/post-form-content.php' );

                }

                $post = $prev_post;

                $content = ob_get_clean();
            }

            //  Otherwise, show the pre-form template
            else if ($gated_content_form_id)  {

                $post = get_post($post_id);
                
                ob_start();

                if (file_exists(get_stylesheet_directory() . '/nfgated-pre-form-content.php' )) {

                    include( get_stylesheet_directory() . '/nfgated-pre-form-content.php' );

                } else {

                    include( NFGATED_DIR . '/templates/pre-form-content.php' );

                }
            
                $post = $prev_post;

                $content = ob_get_clean();
            }

            $post = $prev_post;
            
            //  Return the content
            return '<div id="gated_content_' . $post_id . '">' . $content . '</div>' . $ajax_script;
        }

        /**
         * Store a gated content form submission
         * 
         * @param  array $form
         * @param  array $entry
         */
        function store_submission($form, $entry){

            //  Assume noting
            $is_gated_page = FALSE;

            //  Get the form entry
            if ($entry) {
                
                //  For each submitted field
                foreach( $entry['fields'] as $field ) {
                    
                    //  Look for the field 'Gated Content ID'
                    if ($field['label'] == NFGATED_FORM_ID_FIELD_NAME && isset( $form[ $field['id'] ])) {
                        
                        //  Get the page id
                        $page_id  = $form[ $field['id'] ];

                        //  Get the gated flag
                        $gated_content_enable  = get_post_meta( $page_id, 'gated_content_enable', true );

                        //  Is it gated?
                        if ($gated_content_enable) {
                           
                            //   Yes, get the form id
                            $gated_content_form_id  = get_post_meta( $page_id, 'gated_content_form_id', true );
                            $is_gated_page = true;
                            break;

                        }
                    }
                }
            }

            //  Any gated details in this form?
            if (!$is_gated_page) {
                return;
            }

            //  Is the form submitted the gated content form?
            if ($gated_content_form_id != $form['form_id']) {
                return;
            }

            //  Get the ids
            $post_id = $page_id;
            $form_id = $gated_content_form_id;
            $post_url = get_permalink( $post_id );
            $key_name = $this->getGatedUniqueId( $form_id, $post_id );

            //  Set the completion cookie
            if ( !isset($_COOKIE[$key_name])) {
                setcookie($key_name, 1, time()+31556926 ,'/');
            }

            //  Redirect to the gated content details page
            ?>
            <script>
                if ( window.top.location.href == '<?php print $post_url ?>') {
                    window.top.location.href = '<?php print $post_url ?>#post-gated-content';
                    window.top.location.reload(true);
                } else if ( window.top.location.href == '<?php print $post_url ?>#post-gated-content') {
                    window.top.location.href = '<?php print $post_url ?>#post-gated-content';
                    window.top.location.reload(true);
                } else {
                    window.top.location.href = '<?php print $post_url ?>#post-gated-content';
                }
            </script>
            <?php

            //  Wait for the redirect to happen
            exit;

        }

        /**
         * Define gated content Smart CTAs
         * 
         * @param  array $cta_items
         *
         * @return array
         */
        function define_smart_ctas( $cta_items ){

            //  Get the plugin options
            if (!$plugin_settings = $this->settings->options) {
                return $cta_items;
            }

            //  Are gated content Smart CTAs enabled?
            if (!$plugin_settings['make_smartctas']) {
                return $cta_items;
            }

            //  A CTA to show all the gated content
            $item = Array();
            $item['module']     = 'gatedcontent';
            $item['moduledesc'] = 'Gated Content';
            $item['id']         = 'gatedcontent_mostrecent';
            $item['name']       = 'All Gated Content';
            $item['function']   = Array($this, 'get_next_available_gatedcontent_cta');
            $item['details']    = 'Cycle through all the Gated Content CTAs, ordered by published date, until the user has completed all of them.';
            $cta_items[]        = $item;

            //  A CTA to show the latest gated content
            $item = Array();
            $item['module']     = 'gatedcontent';
            $item['moduledesc'] = 'Gated Content';
            $item['id']         = 'gatedcontent_latest';
            $item['name']       = 'Lastest Gated Content';
            $item['function']   = Array($this, 'get_latest_gatedcontent_cta');
            $item['details']    = 'Show the latest Gated Content post, ordered by published date.';
            $cta_items[]        = $item;

            //  Get all the 'gated' posts
            $args = array(
               'post_status' => 'publish',
               'post_type' => get_post_types(), 
               'posts_per_page' => -1,
               'orderby' => 'date',
               'order'   => 'DESC',
               'meta_query' => array(
                   array(
                       'key' => 'gated_content_enable',
                       'value' => 1,
                       'compare' => '=',
                   )
               )
            );
            $gated_posts = new WP_Query($args);

            //  Any posts found?
            if( $gated_posts->have_posts()) {

                //  Store the global post obj
                global $post, $prevpost;
                $prevpost = $post;

                //  For each gated post
                while($gated_posts->have_posts()) {

                    //  Load up the post obj
                    $gated_posts->the_post();

                    //  Get the form id
                    $gated_content_form_id  = get_post_meta( get_the_ID(), 'gated_content_form_id', true );

                    //  Set the Smart CTA instance  
                    $item = Array();
                    $item['module']     = 'gatedcontent';
                    $item['moduledesc'] = 'Gated Content';
                    $item['id']         = $gated_content_form_id . '_' . get_the_id();
                    $item['name']       = get_the_title();
                    $item['details']    = 'Gated Content - Post ID #' . get_the_id();
                    $cta_items[]        = $item;
                }

                //  Restore the global post
                $post = $prevpost;
            }

            //  Return the Smart CTA instances
            return $cta_items;

        }

        /**
         * Render a gated content Smart CTA
         * 
         * @param  array $instance_item
         * @param  boolean $show_formtitle
         * @param  boolean $show_formdesc
         * 
         * @return array
         */
        function render_smartcta_form( $instance_item, $show_formtitle=false, $show_formdesc=false ){

            //  Get the plugin options
            if (!$plugin_settings = $this->settings->options) {
                return $instance_item;
            }

            //  Are gated content Smart CTAs enabled?
            if (!$plugin_settings['make_smartctas']) {
                return $instance_item;
            }

            //  Is the smart cta instance of the type 'gatedcontent'?
            if ($instance_item['module'] == 'gatedcontent') {

                //  Get the instance details
                $id_parts = explode( '_', $instance_item['id'] );
                $form_id = $id_parts[0];
                $post_id = $id_parts[1];
                $key_name = $this->getGatedUniqueId( $form_id, $post_id );
                $gated_content_enable   = get_post_meta( $post_id, 'gated_content_enable', true );
                $gated_content_form_id  = get_post_meta( $post_id, 'gated_content_form_id', true );

                //  Do we have everything we need?
                if (!$gated_content_enable || !$gated_content_form_id || isset($_COOKIE[$key_name])) {
                    return $instance_item;
                }

                //  Store the global post
                global $post, $prevpost;
                $prevpost = $post;

                //  Load up the instance post
                $post = get_post($post_id);

                //  Include the smart cta template

                if (file_exists(get_stylesheet_directory() . '/nfgated-smart-cta.php' )) {

                    include( get_stylesheet_directory() . '/nfgated-smart-cta.php' );

                } else {

                    include( NFGATED_DIR . '/templates/smart-cta.php' );

                }

                //  Restore the global post
                $post = $prev_post;

                //  Flag the instance as rendered
                $instance_item['rendered'] = true;
            }
        
            //  Return the instance item
            return $instance_item;
        }

        /**
         * See if a gated content Smart CTA has been completed
         * 
         * @param  array $instance_item
         * 
         * @return array
         */
        function check_cta_form( $instance_item ){

            //  Get the plugin options
            if (!$plugin_settings = $this->settings->options) {
                return $instance_item;
            }

            //  Are gated content Smart CTAs enabled?
            if (!$plugin_settings['make_smartctas']) {
                return $instance_item;
            }

            //  Is the smart cta instance of the type 'gatedcontent'?
            if ($instance_item['module'] == 'gatedcontent') {

                //  Get the instance details
                $id_parts = explode( '_', $instance_item['id'] );
                $form_id = $id_parts[0];
                $post_id = $id_parts[1];
                $key_name = $this->getGatedUniqueId( $form_id, $post_id );
                
                //  Has it been previously completed?
                if (isset($_COOKIE[$key_name]))
                {
                    //  Yes, return a positive match
                    $instance_item['submitted'] = true;
                    return $instance_item;
                }
            }
        
            //  Return the instance item
            return $instance_item;
        }

        /**
         * Get the next available Gated Content cta for the user
         * 
         * @return array
         */
        function get_next_available_gatedcontent_cta(){

            //  Get the plugin options
            if (!$plugin_settings = $this->settings->options) {
                return $cta_items;
            }

            //  Are gated content Smart CTAs enabled?
            if (!$plugin_settings['make_smartctas']) {
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
                   array(
                       'key' => 'gated_content_enable',
                       'value' => 1,
                       'compare' => '=',
                   )
               )
            );
            $gated_posts = new WP_Query($args);

            //  Any posts found?
            if( $gated_posts->have_posts()) {

                global $nfprofiling;

                //  Store the global post obj
                global $post, $prevpost;
                $prevpost = $post;

                //  For each gated post
                while($gated_posts->have_posts()) {

                    //  Load up the post obj
                    $gated_posts->the_post();

                    //  Get the form id
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

                    $checked_item = $this->check_cta_form( $item );

                    if (!isset($checked_item['submitted']) || $checked_item['submitted'] != true) {
                        return $checked_item;
                    }
                }

                $post = $prevpost;
            }
        } 

        
        /**
         * Get the most recently published gated post, or 
         * sorted by publish date
         * 
         * @return array
         */
        function get_latest_gatedcontent_cta(){

            //  Get the plugin options
            if (!$plugin_settings = $this->settings->options) {
                return $cta_items;
            }

            //  Are gated content Smart CTAs enabled?
            if (!$plugin_settings['make_smartctas']) {
                return $cta_items;
            }

            //  Get all the 'gated' posts
            $args = array(
               'post_status' => 'publish',
               'post_type' => get_post_types(), 
               'posts_per_page' => 1,
               'orderby' => 'date',
               'order'   => 'DESC',
               'meta_query' => array(
                   array(
                       'key' => 'gated_content_enable',
                       'value' => 1,
                       'compare' => '=',
                   )
               )
            );
            $gated_posts = new WP_Query($args);

            //  Any posts found?
            if( $gated_posts->have_posts()) {

                global $nfprofiling;

                //  Store the global post obj
                global $post, $prevpost;
                $prevpost = $post;

                //  For each gated post
                while($gated_posts->have_posts()) {

                    //  Load up the post obj
                    $gated_posts->the_post();

                    //  Get the form id
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

                    $checked_item = $this->check_cta_form( $item );

                    if (!isset($checked_item['submitted']) || $checked_item['submitted'] != true) {
                        return $checked_item;
                    }
                }
            }
        }   

        /**
         * Determine if ajax content loading is being used
         * 
         * @return boolean
         */
        function get_ajax_load_setting()
        {
            if (!isset($this->settings->options['ajax_content_loading'])) {
                return false;
            }

            $result = $this->settings->options['ajax_content_loading'];

            if ('ajax_content_loading' === $result) {
                return true;
            }

            // Default
            return false;
        }

        /**
         * If fullpage caching is enabled, init the javascript
         * resources that control the ajax content loading
         */
        function init_ajaxscripts() {
            
            //  Is ajax form loading enabled?
            if (!$this->get_ajax_load_setting()) {
                return;
            }

            //  Add the ajax script
            wp_enqueue_script( 
                'ajax_loadgatedcontent', 
                NFGATED_URL . '/js/ajax_gatedcontent.js', 
                array('jquery'), 
                TRUE 
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

        /**
         * Generate a unique cookie key for a gated content item
         * This is the cookie that we'll check to see if the user has
         * access to the content
         */
        function getGatedUniqueId( $form_id, $post_id ) {
            
            $salt = 'ykMskkihwUigQHThLRuAFYjcHCx3THNVEeFAikNG';
            return md5($form_id . '_' . $post_id . '_' . $salt);

        }
    }
}

//  Initialize the plugin
global $nfgated;
if (class_exists("NFGated") && !$nfgated) {
    $nfgated = new NFGated();
}
