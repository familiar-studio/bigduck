<?php
/**
 * Newfangled Webinars
 *
 * @package   Newfangled Webinars
 * @author    Newfangled
 * @link      http://newfangled.com/plugin-support-policy
 * @copyright Newfangled 2016
 */

//*********************************************************************************************************
/*
Plugin Name: Newfangled Webinars
Plugin URI: http://newfangled.com/plugin-support-policy
Description: Adds webinar functionality to posts. Webinars are either upcoming, and integrate a registration
via Act-On, or past, and require a form submission to view. 
Version: 2.0.9
Author: Newfangled
Author URI: http://newfangled.com
Text Domain: nfwebinars
Domain Path: /languages
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
    'https://kernl.us/api/v1/updates/5892535faff0b50f8e6ddc96/',
    __FILE__,
    'nfwebinars',
    1
);

//  Plugin constants
define( 'NFWEBINARS_VERSION', '2.0.9' );
define( 'NFWEBINARS_RELEASE_DATE', date_i18n( 'F j, Y', '1497459897 ' ) );
define( 'NFWEBINARS_DIR', plugin_dir_path( __FILE__ ) );
define( 'NFWEBINARS_URL', plugin_dir_url( __FILE__ ) );
define( 'NFWEBINARS_FORM_ID_FIELD_NAME', 'Webinar ID' );
define( 'NFWEBINARS_ACTON_ID_FIELD_NAME', 'Act-On ID' );

//  Define the plugin class
if (!class_exists("NFWebinars")) {

    /**
     *
     * Class: NFWebinars
     *
     * Adds webinar functionality to posts. Webinars are either upcoming, and 
     * integrate a registration via Act-On, or past, and require a form submission 
     * to view. 
     */
    class NFWebinars {
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
            add_action( 'init',                 array( $this,'process_access_code') );
            add_filter('pre_set_site_transient_update_plugins', array( $this, 'verifyLogging' ), 10, 1);
        }

        /**
         * Load plugin textdomain.
         *
         * @since 1.0.0
         */
        function load_textdomain() {
          load_plugin_textdomain( 'nfwebinars', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        }

        /**
         *
         * Start all of the hooks/listeners
         *
         */
        function init() {

            $this->load_settings();
            add_action("gform_after_submission",            array( $this,"store_submission"), 11, 3 );
            add_filter( "the_content",                      array( $this,"show_webinar_content" ), 1, 1);
            add_filter('nfprofiling_getsmartctas',          array( $this,"define_smart_ctas"), 11, 1);
            add_filter('nfprofiling_render_smartcta_form',  array( $this,"render_smartcta_form"), 11, 3);
            add_filter('nfprofiling_has_cta_been_submitted',array( $this,"check_cta_form"), 11, 1);

            add_action('wp_enqueue_scripts',                array( $this, 'init_ajaxscripts' ) );
        }

        /**
         *
         * Allow for custom meta options of posts that are 'webinars'
         *
         */
        function admin_init() {

            $this->load_settings();

            add_action( 'add_meta_boxes',       array( $this,'nfwebinars_add_meta_boxes'), 10, 2 );
            add_action( 'save_post',            array( $this,'nfwebinars_save_meta_box_data'), 1 );
            add_action('admin_enqueue_scripts', array( $this,'load_jquery_ui'));

        }

        /**
         *
         * Load a jquery ui theme for the tabs
         *
         */
        function load_jquery_ui() {
            global $wp_scripts;
            
            wp_enqueue_script('jquery-ui-core');// enqueue jQuery UI Core
            wp_enqueue_script('jquery-ui-tabs');// enqueue jQuery UI Tabs

            // get registered script object for jquery-ui
            $ui = $wp_scripts->query('jquery-ui-core');
         
            // tell WordPress to load the Smoothness theme from Google CDN
            // $protocol = is_ssl() ? 'https' : 'http';
            // $url = "$protocol://ajax.googleapis.com/ajax/libs/jqueryui/{$ui->ver}/themes/smoothness/jquery-ui.min.css";
            // wp_enqueue_style('jquery-ui-smoothness', $url, false, null);
        }
 


        /**
         *
         * Add meta boxes on post types that are 'webinars'
         *
         */
        function nfwebinars_add_meta_boxes( $post_type, $post=null) {

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

                    //  Is this post type allowed to be a webinar?
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
                
                add_action( 'admin_notices', Array($this,'verify_post_webinar_form') );

                //  Custom meta box to appear when editing a post that can be a webinar
                add_meta_box(
                    'nfwebinars',
                    __( 'Webinar', 'nfwebinars' ),
                    array( $this,'nfwebinars_meta_box_callback'),
                    $screen,
                    'normal',
                    'high'
                );
            }
        }

        /**
         *
         * Function: verify_post_webinar_content_form
         * 
         */
        function verify_post_webinar_form() {
            
            global $post;

            if (!$post) {
                return;
            }

            //  Get the plugin options
            if (!$plugin_settings = $this->settings->options) {
                return;
            }

            //  Is it a webinar?
            if (!$webinar_enable  = get_post_meta( $post->ID, 'webinar_enable', true )) {
                return;
            }

            // Is it upcoming and has an upcoming form been set? 
            if ( $this->is_webinar_upcoming( $post->ID ) && !$webinar_upcoming_form_id = get_post_meta( $post->ID, 'webinar_upcoming_form_id', true )) {
                
                ?>
                <div class="error notice">
                    <p><?php _e( 'Webinar error.', 'nfwebinars'); ?></p>
                    <p><?php _e( 'An upcoming webinar registration form must be selected', 'nfwebinars'); ?></p>
                </div>
                <?php

                return;

            }   

            //  Has a past form been set?
            if (!$webinar_past_form_id = get_post_meta( $post->ID, 'webinar_past_form_id', true )) {
                
                ?>
                <div class="error notice">
                    <p><?php _e( 'Webinar error.', 'nfwebinars'); ?></p>
                    <p><?php _e( 'A past webinar registration form must be selected', 'nfwebinars'); ?></p>
                </div>
                <?php

                return;

            }      

            //  Is it upcoming and is it a valid upcoming form?
            if ( $this->is_webinar_upcoming( $post->ID ) && $post_upcoming_form = $webinar_upcoming_form_id ) {

                if ( $form = GFAPI::get_form( $post_upcoming_form ) ) {

                    $has_correct_field = FALSE;

                    //  For each submitted field
                    foreach( $form['fields'] as $field ) {
                       
                       if ($field['label'] == NFWEBINARS_FORM_ID_FIELD_NAME ) {

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
                            <p><?php _e( 'Webinar error.', 'nfwebinars'); ?></p>
                            <p><?php _e( 'The upcoming webinar registration form, <i>' . $form['title'] . '</i>, is missing the required field ' . NFWEBINARS_FORM_ID_FIELD_NAME . ', with the following values:', 'nfwebinars'); ?></p>

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

            //  Is it a valid past form?
            if ( $post_past_form = $webinar_past_form_id ) {

                if ( $form = GFAPI::get_form( $post_past_form ) ) {

                    $has_correct_field = FALSE;

                    //  For each submitted field
                    foreach( $form['fields'] as $field ) {
                       
                       if ($field['label'] == NFWEBINARS_FORM_ID_FIELD_NAME ) {

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
                            <p><?php _e( 'Webinar error.', 'nfwebinars'); ?></p>
                            <p><?php _e( 'The past webinar registration form, <i>' . $form['title'] . '</i>, is missing the required field ' . NFWEBINARS_FORM_ID_FIELD_NAME . ', with the following values:', 'nfwebinars'); ?></p>

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


            //  Are we doing an Act-On integration for upcoming webinars?
            if (isset($plugin_settings['acton_webinar']) && $webinar_upcoming_form_id) {

                if ( $form = GFAPI::get_form( $webinar_upcoming_form_id ) ) {

                    $has_correct_field = FALSE;

                    //  For each submitted field
                    foreach( $form['fields'] as $field ) {
                       
                       if ($field['label'] == NFWEBINARS_ACTON_ID_FIELD_NAME ) {

                            if (    isset($field->inputName) && 
                                    $field->inputName == 'acton_id'
                            ){
                                $has_correct_field = TRUE;
                                break;
                           }

                        }
                    }

                    if (!$has_correct_field) {
                        ?>
                        <div class="error notice">
                            <p><?php _e( 'Webinar error.', 'nfwebinars'); ?></p>
                            <p><?php _e( 'The global upcoming webinar registration form, <i>' . $form['title'] . '</i>, is missing the required field ' . NFWEBINARS_ACTON_ID_FIELD_NAME . ', with the following values:', 'nfwebinars'); ?></p>

                            <table style="border:1px solid #CCC;">
                            <tr><td>Parameter Name:&nbsp;</td><td>acton_id</td></tr>
                            </table>
                            <br/>

                        </div>
                        <?php
                    }
                }
            }

        }

        /**
         *
         * Build the meta boxes on post types that are 'webinars'
         *
         */
        function nfwebinars_meta_box_callback( $post ) {

            //  Get the plugin options
            if (!$plugin_settings = $this->settings->options) {
                return;
            }

            // Add an nonce field so we can check for it later.
            wp_nonce_field( 'nfwebinars_meta_box', 'nfwebinars_meta_box_nonce' );

            //  Get the meta values that currently exist
            $webinar_enable  = get_post_meta( $post->ID, 'webinar_enable', true );
            $webinar_date  = get_post_meta( $post->ID, 'webinar_date', true );
            $webinar_upcoming_text   = get_post_meta( $post->ID, 'webinar_upcoming_text', true );
            $webinar_past_text   = get_post_meta( $post->ID, 'webinar_past_text', true );
            $webinar_upcoming_form_id = get_post_meta( $post->ID, 'webinar_upcoming_form_id', true );
            $webinar_past_form_id = get_post_meta( $post->ID, 'webinar_past_form_id', true );
            $webinar_upcoming_acton_id = get_post_meta( $post->ID, 'webinar_upcoming_acton_id', true );
            $past_webinar_available = get_post_meta( $post->ID, 'past_webinar_available', true );
            $past_webinar_not_available_message = get_post_meta( $post->ID, 'past_webinar_not_available_message', true );
            $past_webinar_transcript = get_post_meta( $post->ID, 'past_webinar_transcript', true );
            $past_webinar_not_available_message = $past_webinar_not_available_message ? $past_webinar_not_available_message : 'This webinar will be available soon.';

            $enable_access_code =  get_post_meta( $post->ID, 'enable_access_code', true );
            $access_code =  get_post_meta( $post->ID, 'access_code', true );
            $access_code_message =  get_post_meta( $post->ID, 'access_code_message', true );

            if ($plugin_settings['always_webinar']) { 

                //  Show the plugin functionality description - only if 'simple' mode is being used
                if (!$plugin_settings['disable_autocontent']) {
                    echo '<i>Visitors to this post will at first see only the post content, followed by either the upcoming or past webinar registration form. Once they have registered, that visitor will see the custom field \'Upcoming Webinar Registered Content\' or \'Past Webinar Registered Content\', entered below.</i><br/><br/>';
                    echo '<input type="hidden" id="webinar_enable" name="webinar_enable" value="1" />';
                    echo '<div style="display: block;" id="webinar_more">';
                   echo '<hr/><br/>';
                }

            } else {

                //  Show the plugin functionality description - only if 'simple' mode is being used
                if (!$plugin_settings['disable_autocontent']) {
                    echo '<i>When \'Webinar\' is selected, visitors to this post will at first see only the post content, followed by either the upcoming or past webinar registration form. Once they have registered, that visitor will see the custom field \'Upcoming Webinar Registered Content\' or \'Past Webinar Registered Content\', entered below.</i><br/><br/>';
                }

                //  Enable checkbox for this post
                echo '<label for="webinar_enable">';

                if (isset($webinar_enable) && $webinar_enable == 1) {

                    echo '<input type="checkbox" checked id="webinar_enable" name="webinar_enable" value="1" />';

                }  else {
                  
                    echo '<input type="checkbox" id="webinar_enable" name="webinar_enable" value="1"  />';
                
                }

                _e( 'Webinar', 'nfwebinars' );

                echo '</label><br/><br/> ';

                if (isset($webinar_enable) && $webinar_enable == 1) {
                    
                    echo '<div style="display: block;" id="webinar_more">';

                } else { 

                    echo '<div style="display: none;" id="webinar_more">';

                }

                echo '<hr/><br/>';

            }
            
            //  Get the Wordpress timezone
            $wp_timezone = $this->get_wp_timezone();

            //  Webinar Date
             echo '<label for="webinar_date" style="font-weight:bold;">';
            _e( 'Webinar Date', 'nfwebinars' );
            echo '</label><p style="max-width:500px;"><i>mm/dd/YYYY hh:mm am. This is the date/time that the webinar takes place, and dictates whether the \'Upcoming Webinar\' or \'Past Webinar\' form is displayed.</i></p>';
            
            echo '<p style="max-width:500px;"><i>Note - your WordPress timezone setting of <b>' . $wp_timezone . '</b> will be used to determine the cuttoff time between upcoming and past webinars.<br/><br/></i></p>';
            
            echo '<input id="webinar_date" name="webinar_date" value="' . $webinar_date . '"/><br/>';

           
            echo '<br/><br/>';

            //  Upcoming Form selector
            $upcoming_form_html = '';

            if ($plugin_settings['default_upcomingform']) {

                $upcoming_form_html .= '<input type="hidden" id="webinar_upcoming_form_id" name="webinar_upcoming_form_id" value="' . $plugin_settings['default_upcomingform'] . '" />';


            } else {

               $upcoming_form_html .= '<label for="webinar_upcoming_form_id" style="font-weight:bold;">';
               $upcoming_form_html .= 'Registration Form';
               $upcoming_form_html .= '</label><br/><br/> ';
               $upcoming_form_html .= '<select id="webinar_upcoming_form_id" name="webinar_upcoming_form_id"><option value=""></option>';

                $forms = RGFormsModel::get_forms( null, 'title' );

                foreach( $forms as $form )
                {
                    if ($webinar_upcoming_form_id && $webinar_upcoming_form_id == $form->id)
                    {
                         $upcoming_form_html .= '<option selected value="' . $form->id . '">' . $form->title . '</option>';
                    }

                    else
                    {
                          $upcoming_form_html .= '<option value="' . $form->id . '">' . $form->title . '</option>';
                    }
                }

                $upcoming_form_html .= '</select><br/><br/>';
            }


            //  Past Form selector
            $past_form_html = '';

            if ($plugin_settings['default_pastform']) {

                $past_form_html .= '<input type="hidden" id="webinar_past_form_id" name="webinar_past_form_id" value="' . $plugin_settings['default_pastform'] . '" />';

            } else {

                $past_form_html .= '<label for="webinar_past_form_id" style="font-weight:bold;">';
                $past_form_html .= 'Registration Form';
                $past_form_html .= '</label><br/><br/> ';
                $past_form_html .= '<select id="webinar_past_form_id" name="webinar_past_form_id"><option value=""></option>';

                $forms = RGFormsModel::get_forms( null, 'title' );

                foreach( $forms as $form )
                {
                    if ($webinar_past_form_id && $webinar_past_form_id == $form->id)
                    {
                        $past_form_html .= '<option selected value="' . $form->id . '">' . $form->title . '</option>';
                    }

                    else
                    {
                        $past_form_html .= '<option value="' . $form->id . '">' . $form->title . '</option>';
                    }
                }

                $past_form_html .= '</select><br/><br/>';
            }
            
            //  Past Webinar Available
            $past_form_html .= '<label for="past_webinar_available">';

            if (isset($past_webinar_available) && $past_webinar_available == 1) {

                $past_form_html .= '<input type="checkbox" checked id="past_webinar_available" name="past_webinar_available" value="1" />';
                $past_form_html .= '<b>Past Webinar is Available</b></label><br/>';

            }  else {
              
                $past_form_html .= '<input type="checkbox" id="past_webinar_available" name="past_webinar_available" value="1"  />';
                $past_form_html .= '<b>Past Webinar is Available</b></label><br/>';
            }

            $past_form_html .= '<p style="max-width:700px;"><i>Checking this will cause the \'Past Webinar Registration\' form to show, followed by the \'Post-Registration Content\' (usually containing an embedded video) upon submission. Leaving this unchecked means that video has not yet been uploaded, so the form will not show, and instead the \'Past Webinar Not Available\' message will display instead.</i></p>';
            
            $past_form_html .= '<p><label for="past_webinar_not_available_message" style="display:block;font-weight:bold;margin-bottom:10px;font-size:12px;">Past Webinar Not Yet Available Message</label>';
            $past_form_html .= '<input style="max-width:50%;width:50%;" type="text" id="past_webinar_not_available_message" name="past_webinar_not_available_message" value="' . $past_webinar_not_available_message . '"></p>';

            //  Protected content editor
            if (!$plugin_settings['disable_autocontent']) {
            
                ?>
                <div id="webinar-tabs">
                    <ul>
                        <li><a class="nav-tab" href="#webinars-upcoming-content">Upcoming Webinar Content</a></li>
                        <li><a class="nav-tab" href="#webinars-past-content">Past Webinar Content</a></li>
                    </ul>
                
                    
                    <div id="webinars-upcoming-content" class="tab">
                        <br/>
                        <?php print $upcoming_form_html ?>
                        
                        <?php
                        
                        //  Are we doing an Act-On integration for upcoming webinars?
                        if ($plugin_settings['acton_webinar']) {

                            echo '<label for="webinar_upcoming_acton_id" style="font-weight:bold;">';
                            _e( 'Act-On Webinar Form ID', 'nfwebinars' );
                            echo '</label><p  style="max-width:700px;"><i>The Act-On ID can be seen by looking at the list of "Public URLs". For instance, if the public URL is: http://xxxxx.actonsoftware.com/acton/form/xxxxx/000c:d-0001/0/-/-/-/-/index.htm, then the Act-On ID would be \'000c\'.</i></p>';
                            echo '<input id="webinar_upcoming_acton_id" name="webinar_upcoming_acton_id" value="' . $webinar_upcoming_acton_id . '"/><br/><br/><br/>';
                        }

                        ?>

                        <?php
                        echo '<label for="webinar_upcoming_text" style="font-weight:bold;">';
                        _e( 'Post-Registration Content', 'nfwebinars' );
                        echo '</label><p  style="max-width:700px;"><i>The \'Post Registration Content\' is the message that displays after the upcoming registration form has been submitted. A user returning to this page will continue to see this content, once they have registered.</i></p><br/>';
                        wp_editor( htmlspecialchars_decode($webinar_upcoming_text), 'nfwebinars_meta_box_webinar_upcoming_text', $settings = array('textarea_name'=>'webinar_upcoming_text', 'editor_height' => 325) );
                        ?>

                        
                    </div>   

                    <div id="webinars-past-content"  class="tab">
                        <br/>
                        <?php print $past_form_html ?>
                        <br/><hr/><br/>
                        <?php
                        echo '<label for="webinar_past_text" style="font-weight:bold;">';
                        _e( 'Post-Registration Content', 'nfwebinars' );
                        echo '</label><p  style="max-width:700px;"><i>The \'Post Registration Content\' is the message that displays after the past webinar registration form has been submitted. This usually contains an embedded video of the webinar. A user returning to this page will continue to see this content, once they have registered.</i></p><br/>';
                        wp_editor( htmlspecialchars_decode($webinar_past_text), 'nfwebinars_meta_box_webinar_past_text', $settings = array('textarea_name'=>'webinar_past_text', 'editor_height' => 325) );
                        ?>
                        <br/><br/>
                        <?php
                        echo '<label for="past_webinar_transcript" style="font-weight:bold;">';
                        _e( 'Webinar Transcript', 'nfwebinars' );
                        echo '</label><p  style="max-width:700px;"><i>Optional transcript text of the past webinar. This will appear both below the form (before the user registers) and below the post-registration content (after the user has registered). This is useful for adding indexable content.</i></p><br/>';
                        wp_editor( htmlspecialchars_decode($past_webinar_transcript), 'nfwebinars_meta_box_webinar_past_transcript', $settings = array('textarea_name'=>'past_webinar_transcript', 'editor_height' => 325) );
                        ?>
                        <br/><hr/><br/>
                        <?php
                        print '<label for="enable_access_code">';

                        if (isset($enable_access_code) && $enable_access_code == 1) {

                            print '<input type="checkbox" checked id="enable_access_code" name="enable_access_code" value="1" />';
                            print '<b>Enable Access Code</b></label><br/>';

                        }  else {
                          
                            print '<input type="checkbox" id="enable_access_code" name="enable_access_code" value="1"  />';
                            print '<b>Enable Access Code</b></label><br/>';
                        }

                        print '<p  style="max-width:700px;"><i>By default, users will be required to complete the registration form to view a Past Webinar, unless they have already been cookied by previously registering. By enabling an access code, they will be given the option to enter that code instead, bypassing the registration form. This access code should be provided to them manually, such as via a bulk emailing from your Marketing Automation system. This option is particularly useful if your webinar registration forms involve a payment option.</i></p>';

                        if (!$access_code) {
                            $access_code = uniqid();
                        }

                        print '<p><label for="access_code" style="display:block;font-weight:bold;margin-bottom:10px;font-size:12px;">Access Code</label>';
                        print '<input style="max-width:50%;width:50%;" type="text" id="access_code" name="access_code" value="' . $access_code . '"></p>';

                        if (!$access_code_message) {
                            $access_code_message = 'Have an access code? Enter it here to view the webinar.';
                        }

                        print '<p><label for="access_code_message" style="display:block;font-weight:bold;margin-bottom:10px;font-size:12px;">Access Code Message</label>';
                        print '<input style="max-width:50%;width:50%;" type="text" id="access_code_message" name="access_code_message" value="' . $access_code_message . '"></p>';


                        ?>

                    </div>                 
                </div>
                <?php
            }

            echo '</div>';

            ?>
            <script>
            jQuery(function($) {
                $('#webinar_enable').change(function() {
                    if($(this).is(":checked")) {
                        $('#webinar_more').show();
                    } else {
                        $('#webinar_more').hide();
                    }
                });

                $('#disable_past_registration').change(function() {
                    if($(this).is(":checked")) {
                        console.log( 1 );

                        $('#past-registration-wrapper').show();
                    } else {
                        $('#past-registration-wrapper').hide();
                    }
                });

                $('#disable_upcoming_registration').change(function() {
                    if($(this).is(":checked")) {
                        $('#upcoming-webinar-wrapper').show();
                    } else {
                        $('#upcoming-webinar-wrapper').hide();
                    }
                });

                $( "#webinar-tabs" ).tabs({
                    selected: 1,
                    create: function() {
                        $( "#webinar-tabs" ).css('visibility', 'visible');
                    }
                });
            });

            </script>
            <style>

                #webinar-tabs {
                    visibility: hidden;
                    border: none!important;
                    font-family: -apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen-Sans,Ubuntu,Cantarell,"Helvetica Neue",sans-serif!important;
                }
                #webinar-tabs * {
                    outline: none!important;
                }

                #webinar-tabs ul {
                    padding-top: 0px!important;
                    padding-bottom: 0px!important;
                    margin-top: 0px!important;
                    margin-bottom: -3px!important;
                    border: none!important;
                    height: 37px!important;
                    overflow: hidden!important;
                    background: transparent!important;
                }
                
                #webinar-tabs ul li a  {
                    border: none!important;
                    outline: none!important;
                    background-color: #DDD!important;
                    margin-bottom: 0px!important;
                    font-size: 12px!important;
                    font-weight: 600!important;
                    padding: 5px 10px!important;
                }

                #webinar-tabs ul li {
                    margin-bottom: 0px!important;
                    outline: none!important;
                    border: none!important;
                    background: transparent!important;
                    top: 0!important;
                    outline: 0;

                }

                #webinar-tabs ul li.ui-tabs-active a,
                #webinar-tabs ul li.ui-tabs-hover a,
                #webinar-tabs ul li.ui-tabs-active a,
                #webinar-tabs ul li.ui-state-active a {
                    background-color: #FFF!important;
                    border: 1px solid #AAA!important;
                    border-bottom: none!important;
                    font-size: 12px!important;
                    outline: 0;
                }

                #webinar-tabs .tab {
                    padding: 20px;
                    background: #FFF!important;
                    border: 1px solid #AAA!important;
                    font-size: 12px!important;

                }

                .ui-state-active a, .ui-state-hover a, .ui-state-visited a, .ui-state-focus a, .ui-state-focus:focus {
                    outline:none!important;
                    outline-width: 0!important;
                 }
            }

            </style>
            <?php
        }

        /**
         *
         * Save the meta data
         *
         */
        function nfwebinars_save_meta_box_data( $post_id ) {

            //  Valid nonce?
            if ( ! isset( $_POST['nfwebinars_meta_box_nonce'] ) ) {
                return;
            }

            //  Verify the nonce
            if ( ! wp_verify_nonce( $_POST['nfwebinars_meta_box_nonce'], 'nfwebinars_meta_box' ) ) {
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

            //  Webinar content enable is checked
            if (isset($_POST['webinar_enable']) && $_POST['webinar_enable'] == 1)
            {
                $webinar_enable   = TRUE;
            }

            //  Webinar content enable is not checked
            else
            {
                $webinar_enable   = false;
            }

            //  Get the other values
            $webinar_upcoming_text   = htmlspecialchars( $_POST['webinar_upcoming_text'] );
            $webinar_past_text   = htmlspecialchars( $_POST['webinar_past_text'] );
            $webinar_upcoming_form_id  = htmlspecialchars( $_POST['webinar_upcoming_form_id'] );
            $webinar_past_form_id = htmlspecialchars( $_POST['webinar_past_form_id'] );
            $webinar_date = htmlspecialchars( $_POST['webinar_date'] );
            $webinar_upcoming_acton_id = htmlspecialchars( $_POST['webinar_upcoming_acton_id'] );

            $past_webinar_available = htmlspecialchars( $_POST['past_webinar_available'] );
            $past_webinar_not_available_message = htmlspecialchars( $_POST['past_webinar_not_available_message'] );
            $past_webinar_transcript = htmlspecialchars( $_POST['past_webinar_transcript'] );

            $enable_access_code = htmlspecialchars( $_POST['enable_access_code'] );
            $access_code = htmlspecialchars( $_POST['access_code'] );
            $access_code_message = htmlspecialchars( $_POST['access_code_message'] );

            //  Save the values
            update_post_meta( $post_id, 'webinar_enable', $webinar_enable );
            update_post_meta( $post_id, 'webinar_upcoming_text', $webinar_upcoming_text );
            update_post_meta( $post_id, 'webinar_past_text', $webinar_past_text );
            update_post_meta( $post_id, 'webinar_upcoming_form_id', $webinar_upcoming_form_id );
            update_post_meta( $post_id, 'webinar_past_form_id', $webinar_past_form_id );
            update_post_meta( $post_id, 'webinar_date', $webinar_date );
            update_post_meta( $post_id, 'webinar_upcoming_acton_id', $webinar_upcoming_acton_id );

            update_post_meta( $post_id, 'past_webinar_available', $past_webinar_available );
            update_post_meta( $post_id, 'past_webinar_not_available_message', $past_webinar_not_available_message );
            update_post_meta( $post_id, 'past_webinar_transcript', $past_webinar_transcript );

            update_post_meta( $post_id, 'enable_access_code', $enable_access_code );
            update_post_meta( $post_id, 'access_code', $access_code );
            update_post_meta( $post_id, 'access_code_message', $access_code_message );

        }

        /**
         *
         * Cache getter
         *
         */
        function cache_get( $cachename )
        {
            return wp_cache_get( $cachename );
        }

        /**
         *
         * Cache setter
         *
         */
        function cache_set( $cachename, $object )
        {
            wp_cache_set( $cachename, $object, 'nfwebinars', 660 );
        }

        /**
         *
         * Load the plugin settings
         *
         */
        function load_settings() {

            if (isset($this->settings)) {
                return;
            }

            if ( $nfwebinars_settings = $this->cache_get( 'nfwebinars_settings' ) ) {
                $this->settings = $nfwebinars_settings;
                return;
            }

            if (!$this->settings && !class_exists("NFWebinars_Settings")) {
                require(NFWEBINARS_DIR . 'newfangled-webinars-settings.php');
            }

            $this->settings = new NFWebinars_Settings( $this );
            $this->cache_set( 'nfwebinars_settings', $this->settings );
        }

        /**
         *
         * Get the system timezone (defaulting to EST)
         *
         */
        function get_wp_timezone() {
            if (!$wp_timezone = get_option('timezone_string')) {
                $wp_timezone = 'US/Eastern';
            }

            return $wp_timezone;
        }

        /**
         *
         * Is the webinar upcoming?
         *
         */
        function is_webinar_upcoming( $id='' ) {

            global $post;

            $this_post = get_post( $id );

            //  Get the plugin settings
            if (!$plugin_settings = $this->settings->options) {

                //  No, not a webinar
                return false;
            }

            //  Get the post object
            $post_type_object = get_post_type_object( $this_post->post_type );

            //  Get the post type desc
            $post_type_desc = $post_type_object->labels->name;

            //  Is it allowed to be a webinar?
            if (!isset($plugin_settings[$post_type_desc])) {

                //  No, not a webinar
                return false;
            }

            //  Get the meta data
            $webinar_enable  = get_post_meta( $id, 'webinar_enable', true );
            $webinar_date  = get_post_meta( $id, 'webinar_date', true );

            //  Is webinar enabled?
            if (!$webinar_enable) {

                //  No, not a webinar
                return false;
            }

            //  Set the previous PHP timezone
            $previous_timezone = date_default_timezone_get();
            
            //  Get the WP timezone 
            $wp_timezone = $this->get_wp_timezone();

            //  Set the WP timezone as the PHP timezone
            date_default_timezone_set($wp_timezone);
            
            //  Is it in the future?
            if (strtotime($webinar_date) > time()) {

                //  Restore the previous PHP timezone
                date_default_timezone_set( $previous_timezone );

                //  Yes
                return true;

            } else {

                //  Restore the previous PHP timezone
                date_default_timezone_set( $previous_timezone );

                //  No
                return false;

            }
        }

        /**
         *
         * Is the user regsitered for the webinar?
         *
         */
        function is_webinar_registered( $id='' ) {

            global $post;

            $this_post = get_post( $id );

            //  Get the plugin settings
            if (!$plugin_settings = $this->settings->options) {

                //  No, not a webinar
                return false;
            }

            //  Get the post object
            $post_type_object = get_post_type_object( $this_post->post_type );

            //  Get the post type desc
            $post_type_desc = $post_type_object->labels->name;

            //  Is it allowed to be a webinar?
            if (!isset($plugin_settings[$post_type_desc])) {

                //  No, not a webinar
                return false;
            }

            //  Get the meta data
            $webinar_enable  = get_post_meta( $id, 'webinar_enable', true );
            $webinar_upcoming_form_id  = get_post_meta( $id, 'webinar_upcoming_form_id', true );

            //  Is webinar enabled?
            if (!$webinar_enable) {

                //  No, not a webinar
                return false;
            }

            //  Get the ids
            $post_id = $id;
            $form_id = $webinar_upcoming_form_id;
            $key_name = $this->getWebinarUniqueId( $post_id );

            //  Has the user already completed the form?
            if (isset($_COOKIE[$key_name])) {

                //  Yes, registered
                return true;
            }

            //  No, not registered
            return true;
        }

        /**
         *
         * Show the webinar - either with form, or without
         *
         */
        function show_webinar_content( $content='' ) {

            global $post, $prevpost;

            $prevpost = $post;

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
            $webinar_enable   = get_post_meta( $post->ID, 'webinar_enable', true );
            $webinar_upcoming_form_id  = get_post_meta( $post->ID, 'webinar_upcoming_form_id', true );
            $webinar_past_form_id  = get_post_meta( $post->ID, 'webinar_past_form_id', true );

            //  Do we have everything?
            if (!$webinar_enable || (!$webinar_upcoming_form_id && !$webinar_past_form_id)) {
                return $content;
            }

            //  Get the ids
            $post_id = $post->ID;
            $form_id = $webinar_upcoming_form_id;
            $key_name = $this->getWebinarUniqueId( $post_id );

            $ajax_script = '';

            //  Allow for loading of the gated content via Ajax
            if ($this->get_ajax_load_setting()) {

                //  Are we already running an ajax call?
                if (isset($_POST['action']) && $_POST['action'] == 'getwebinarcontent') {
                } else {

                    //  Add the js code for this form
                    $ajax_script .= "<script>(function (NfWebinarContentLoader, $) {loadWebinarContent('webinar_content_" . $post_id . "');}(window.NfWebinarContentLoader = window.NfWebinarContentLoader || {}, jQuery));</script>";
                }
            }

            //  Is it an upcoming webinar?
            if ( $this->is_webinar_upcoming( $post_id )) {

                //  Has the webinar been regsitered?
                if (!$ajax_script && isset($_COOKIE[$key_name])) {

                    $post = get_post($post_id);
                    
                    ob_start();
                    
                    if (file_exists(get_stylesheet_directory() . '/nfwebinars-upcoming-registered-content.php' )) {

                        include( get_stylesheet_directory() . '/nfwebinars-upcoming-registered-content.php' );

                    } else {

                        include( NFWEBINARS_DIR . '/templates/upcoming-registered-content.php' );

                    }

                    $post = $prevpost;

                    $content = ob_get_clean();
                }

                //  Otherwise, show the pre-form template
                else if ($webinar_upcoming_form_id)  {

                    $post = get_post($post_id);
                    
                    ob_start();

                    if (file_exists(get_stylesheet_directory() . '/nfwebinars-upcoming-form-content.php' )) {

                        include( get_stylesheet_directory() . '/nfwebinars-upcoming-form-content.php' );

                    } else {

                        include( NFWEBINARS_DIR . '/templates/upcoming-form-content.php' );

                    }
                
                    $post = $prevpost;

                    $content = ob_get_clean();
                }

            }

            //  Or a past webinar?
            else {

                //  Has the webinar been regsitered?
                if (!$ajax_script && isset($_COOKIE[$key_name])) {

                    $post = get_post($post_id);
                    
                    ob_start();
                    
                    if (file_exists(get_stylesheet_directory() . '/nfwebinars-past-registered-content.php' )) {

                        include( get_stylesheet_directory() . '/nfwebinars-past-registered-content.php' );

                    } else {

                        include( NFWEBINARS_DIR . '/templates/past-registered-content.php' );

                    }

                    $post = $prevpost;

                    $content = ob_get_clean();
                }

                //  Otherwise, show the pre-form template
                else if ($webinar_past_form_id)  {

                    $post = get_post($post_id);
                    
                    ob_start();

                    if (file_exists(get_stylesheet_directory() . '/nfwebinars-past-form-content.php' )) {

                        include( get_stylesheet_directory() . '/nfwebinars-past-form-content.php' );

                    } else {

                        include( NFWEBINARS_DIR . '/templates/past-form-content.php' );

                    }
                
                    $post = $prevpost;

                    $content = ob_get_clean();
                }

            }

            $post = $prevpost;

            //  Return the content
            return '<div id="webinar_content_' . $post_id . '">' . $content . '</div>' . $ajax_script;
        }


        /**
         *
         * Store a webinar registration
         *
         */
        function process_access_code(){

            global $POST;

            //  Was an access code form submitted?
            if (!isset( $_POST['webinar-id'] ) || empty( $_POST['webinar-id'] )) {
                return;
            }

            if (!isset( $_POST['webinar-access-code'] ) || empty( $_POST['webinar-access-code'] )) {
                return;
            }

            //  Get the post values
            $webinar_id = htmlspecialchars( $_POST['webinar-id'] );
            $webinar_access_code = htmlspecialchars( $_POST['webinar-access-code'] );

            //  Get the webinar flag
            $webinar_enable  = get_post_meta( $webinar_id, 'webinar_enable', true );

            //  Is it a webinar?
            if ($webinar_enable) {
               
                //   Get the actual access code
                if ($actual_access_code =  get_post_meta( $webinar_id, 'access_code', true )) {
                    
                    // Do they match?
                    if ($webinar_access_code == $actual_access_code) {

                        //  Yes, set the cookie
                        $key_name = $this->getWebinarUniqueId( $post_id );
                        setcookie($key_name, 1, time()+31556926 ,'/');
                        
                        //  Redirect to the webinar details page
                        $post_url = get_permalink( $webinar_id );

                        ?>
                        <script>
                            if ( window.top.location.href == '<?php print $post_url ?>') {
                                window.top.location.href = '<?php print $post_url ?>#webinar-registered';
                                window.top.location.reload(true);
                            } else if ( window.top.location.href == '<?php print $post_url ?>#webinar-registered') {
                                window.top.location.href = '<?php print $post_url ?>#webinar-registered';
                                window.top.location.reload(true);
                            } else {
                                window.top.location.href = '<?php print $post_url ?>#webinar-registered';
                            }
                        </script>
                        <?php

                        //  Wait for the redirect to happen
                        exit;

                    } else {

                        //  No, record the error message
                        $this->access_code_error = 'Invalid access code';
                        return;

                    }

                }
            }
        }

        /**
         *
         * Store a webinar registration
         *
         */
        function store_submission($form, $entry){

            //  Assume noting
            $is_webinar = FALSE;

            //  Get the form entry
            if ($entry) {
                
                //  For each submitted field
                foreach( $entry['fields'] as $field ) {
                    
                    //  Look for the field 'Webinar ID'
                    if ($field['label'] == NFWEBINARS_FORM_ID_FIELD_NAME && isset( $form[ $field['id'] ])) {
                        
                        //  Get the page id
                        $page_id  = $form[ $field['id'] ];

                        //  Get the webinar flag
                        $webinar_enable  = get_post_meta( $page_id, 'webinar_enable', true );

                        //  Is it a webinar?
                        if ($webinar_enable) {
                           
                            //   Yes, get the form id
                            $webinar_upcoming_form_id  = get_post_meta( $page_id, 'webinar_upcoming_form_id', true );
                            $webinar_past_form_id  = get_post_meta( $page_id, 'webinar_past_form_id', true );
                            $is_webinar = true;
                            break;

                        }
                    }
                }
            }

            //  Any webinar info in this form?
            if (!$is_webinar) {
                return;
            }

            //  Is the form submitted the webinar form?
            if ($webinar_upcoming_form_id != $form['form_id'] && 
                $webinar_past_form_id != $form['form_id']) {
                return;
            }

            //  Get the ids
            $post_id = $page_id;
            $form_id = $webinar_upcoming_form_id;
            $post_url = get_permalink( $post_id );
            $key_name = $this->getWebinarUniqueId( $post_id );

            //  Set the completion cookie
            if ( !isset($_COOKIE[$key_name])) {
                setcookie($key_name, 1, time()+31556926 ,'/');
            }

            //  Redirect to the webinar details page
            ?>
            <script>
                if ( window.top.location.href == '<?php print $post_url ?>') {
                    window.top.location.href = '<?php print $post_url ?>#webinar-registered';
                    window.top.location.reload(true);
                } else if ( window.top.location.href == '<?php print $post_url ?>#webinar-registered') {
                    window.top.location.href = '<?php print $post_url ?>#webinar-registered';
                    window.top.location.reload(true);
                } else {
                    window.top.location.href = '<?php print $post_url ?>#webinar-registered';
                }
            </script>
            <?php

            //  Wait for the redirect to happen
            exit;

        }

        /**
         *
         * Define webinar content Smart CTAs
         *
         */
        function define_smart_ctas( $cta_items ){

            //  Get the plugin options
            if (!$plugin_settings = $this->settings->options) {
                return $cta_items;
            }

            //  Are webinar Smart CTAs enabled?
            if (!$plugin_settings['make_smartctas']) {
                return $cta_items;
            }

            //  A CTA to show the most recent webinar
            $item = Array();
            $item['module']     = 'webinar';
            $item['moduledesc'] = 'Webinar';
            $item['id']         = 'webinar_mostrecent';
            $item['name']       = 'All Webinars';
            $item['function']   = Array($this, 'get_next_available_webinar_cta');
            $item['details']    = 'Cycle through all the Webinar CTAs, ordered by published date, until the user has completed all of them.';
            $cta_items[]        = $item;
            
            //  A CTA to show the latest webinar
            $item = Array();
            $item['module']     = 'webinar';
            $item['moduledesc'] = 'Webinar';
            $item['id']         = 'webinar_latest';
            $item['name']       = 'Lastest Webinar';
            $item['function']   = Array($this, 'get_latest_webinar_cta');
            $item['details']    = 'Show the latest Webinar post, ordered by published date.';
            $cta_items[]        = $item;

            
            //  Get all the 'webinar' posts
            $args = array(
               'post_status' => 'publish',
               'post_type' => get_post_types(), 
               'posts_per_page' => -1,
               'orderby' => 'date',
               'order'   => 'DESC',
               'meta_query' => array(
                   array(
                       'key' => 'webinar_enable',
                       'value' => 1,
                       'compare' => '=',
                   )
               )
            );
            $webinar_posts = new WP_Query($args);

            //  Any posts found?
            if( $webinar_posts->have_posts()) {

                //  Store the global post obj
                global $post, $prevpost;
                $prevpost = $post;

                //  For each webinar post
                while($webinar_posts->have_posts()) {

                    //  Load up the post obj
                    $webinar_posts->the_post();

                    //  Get the form id
                    $webinar_upcoming_form_id  = get_post_meta( get_the_ID(), 'webinar_upcoming_form_id', true );

                    //  Set the Smart CTA instance  
                    $item = Array();
                    $item['module']     = 'webinar';
                    $item['moduledesc'] = 'Webinar';
                    $item['id']         = 'webinar_' . get_the_id();
                    $item['name']       = get_the_title();
                    $item['details']    = 'Webinar - Post ID #' . get_the_id();
                    $cta_items[]        = $item;
                }

                //  Restore the global post
                $post = $prevpost;
            }

            //  Return the Smart CTA instances
            return $cta_items;

        }

        /**
         *
         * Render a webinar Smart CTA
         *
         */
        function render_smartcta_form( $instance_item, $show_formtitle=false, $show_formdesc=false ){

            //  Get the plugin options
            if (!$plugin_settings = $this->settings->options) {
                return $instance_item;
            }

            //  Are webinar content Smart CTAs enabled?
            if (!$plugin_settings['make_smartctas']) {
                return $instance_item;
            }

            //  Is the smart cta instance of the type 'webinar'?
            if ($instance_item['module'] == 'webinar') {

                //  Get the instance details
                $id_parts = explode( '_', $instance_item['id'] );
                $form_id = $id_parts[0];
                $post_id = $id_parts[1];
                $key_name = $this->getWebinarUniqueId( $post_id );
                $webinar_enable   = get_post_meta( $post_id, 'webinar_enable', true );
                //$webinar_upcoming_form_id  = get_post_meta( $post_id, 'webinar_upcoming_form_id', true );

                //  Do we have everything we need?
                if (!$webinar_enable || isset($_COOKIE[$key_name])) {
                    return $instance_item;
                }

                //  Store the global post
                global $post, $prevpost;
                $prevpost = $post;

                //  Load up the instance post
                $post = get_post($post_id);

                //  Include the smart cta template

                if (file_exists(get_stylesheet_directory() . '/nfwebinars-smart-cta.php' )) {

                    include( get_stylesheet_directory() . '/nfwebinars-smart-cta.php' );

                } else {

                    include( NFWEBINARS_DIR . '/templates/smart-cta.php' );

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
         *
         * See if a webinar Smart CTA has been completed
         *
         */
        function check_cta_form( $instance_item ){

            //  Get the plugin options
            if (!$plugin_settings = $this->settings->options) {
                return $instance_item;
            }

            //  Are webinar Smart CTAs enabled?
            if (!$plugin_settings['make_smartctas']) {
                return $instance_item;
            }

            //  Is the smart cta instance of the type 'webinar'?
            if ($instance_item['module'] == 'webinar') {

                //  Get the instance details
                $id_parts = explode( '_', $instance_item['id'] );
                $form_id = $id_parts[0];
                $post_id = $id_parts[1];
                $key_name = $this->getWebinarUniqueId( $post_id );
                
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
         *
         * Get the next available cta for the user
         *
         */
        function get_next_available_webinar_cta(){

            //  Get the plugin options
            if (!$plugin_settings = $this->settings->options) {
                return $cta_items;
            }

            //  Are gated content Smart CTAs enabled?
            if (!$plugin_settings['make_smartctas']) {
                return $cta_items;
            }

            //  Get all the 'webinar' posts
            $args = array(
               'post_status' => 'publish',
               'post_type' => get_post_types(), 
               'posts_per_page' => -1,
               'orderby' => 'date',
               'order'   => 'DESC',
               'meta_query' => array(
                   array(
                       'key' => 'webinar_enable',
                       'value' => 1,
                       'compare' => '=',
                   )
               )
            );
            $webinar_posts = new WP_Query($args);

            //  Any posts found?
            if( $webinar_posts->have_posts()) {

                global $nfprofiling;

                //  Store the global post obj
                global $post, $prevpost;
                $prevpost = $post;

                //  For each gated post
                while($webinar_posts->have_posts()) {

                    //  Load up the post obj
                    $webinar_posts->the_post();

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

                    $checked_item = $this->check_cta_form( $item );

                    if (!isset($checked_item['submitted']) || $checked_item['submitted'] != true) {
                        return $checked_item;
                    }
                }
            }
        }

        /**
         *
         * Get the latest webinar
         *
         */
        function get_latest_webinar_cta(){

            //  Get the plugin options
            if (!$plugin_settings = $this->settings->options) {
                return $cta_items;
            }

            //  Are gated content Smart CTAs enabled?
            if (!$plugin_settings['make_smartctas']) {
                return $cta_items;
            }

            //  Get all the 'webinar' posts
            $args = array(
               'post_status' => 'publish',
               'post_type' => get_post_types(), 
               'posts_per_page' => 1,
               'orderby' => 'date',
               'order'   => 'DESC',
               'meta_query' => array(
                   array(
                       'key' => 'webinar_enable',
                       'value' => 1,
                       'compare' => '=',
                   )
               )
            );
            $webinar_posts = new WP_Query($args);

            //  Any posts found?
            if( $webinar_posts->have_posts()) {

                global $nfprofiling;
                
                //  Store the global post obj
                global $post, $prevpost;
                $prevpost = $post;

                //  For each gated post
                while($webinar_posts->have_posts()) {

                    //  Load up the post obj
                    $webinar_posts->the_post();

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
                'ajax_loadwebinarcontent', 
                NFWEBINARS_URL . '/js/ajax_webinarcontent.js', 
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
        function getWebinarUniqueId( $post_id ) {
            
            $salt = 'ykMskkihwUigQHThLRuAFYjcHCx3THNVEeFAikNG';
            return md5($post_id . $salt);

        }    
    }
}

//  Initialize the plugin
global $nfwebinars;
if (class_exists("NFWebinars") && !$nfwebinars) {
    $nfwebinars = new NFWebinars();
}
