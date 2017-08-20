<?php
/**
 * Newfangled Progressive Profiling
 *
 * @package   Newfangled Progressive Profiling
 * @author    Newfangled
 * @link      https://bitbucket.org/newfangled_web/newfangled-progressive-profiling
 * @copyright Newfangled 2016
 */

/**
 * Gravity Forms module. 
 *
 * Modules provide an interface
 * to define the forms, fields, and smart CTAs
 * that make up the progressive profiling system.
 *
 * This module interfaces directly with the Gravity
 * Forms plugins. All gravity form fields are available
 * as possible progressive profiling fields, and all 
 * gravity forms are available as smart CTAs. 
*/
class NfProfiling_GravityForms extends NfProfiling_Module
{
    var $modulename = 'Gravity Forms';
    var $moduleid   = 'gravityforms';
    var $moduledesc = 'This module interfaces directly with the Gravity Forms plugins. All gravity form fields are available as possible progressive profiling fields, and all gravity forms are available as smart CTAs. ';
    /**
     * Init the wordpress filters and actions
     */
    function init_listeners() {
        add_filter( 'gform_validation',                 Array($this,  "validate_submission"), 10, 1 );
        add_filter( "gform_pre_render",                 Array($this,  "prepare_fields"),      10, 1 );
        add_action( "gform_after_submission",           Array($this,  "store_submission"),    10, 2 );
        add_filter( 'gform_tabindex',                   Array($this,  'gform_tabindexer'),    10, 2);
        add_filter( 'wp_head',                          Array($this,  'preload_scripts'),      1 );

        add_filter( 'gform_get_form_filter',            Array($this,  'ajax_formload'),       999,  2);
        add_action( 'wp_enqueue_scripts',               Array( $this, 'init_ajaxscripts' ) );
        add_action( 'wp_head',                          Array( $this, 'init_csscode' ) );
        add_action( 'wp_ajax_process_loadform',         Array( $this, 'process_loadform' ) ); 
        add_action( 'wp_ajax_nopriv_process_loadform',  Array( $this, 'process_loadform' ) );

    }

    /**
     * Since the smartcta could contain a gravity form, lets
     * make sure that every page preloads the standard gravityforms
     * scripts and styles
     */
    function preload_scripts() {
        
        //  Is this enabled?
        if (!$this->parent->settings->options['force_scripts_header']) {
            return;
        }

        //  Has the Gravity Forms form model been defined?
        if (!class_exists('RGFormsModel')) {
            return;
        }

        //  Has the Gravity Forms display controlled 
        //  been defined?
        if (!class_exists('GFFormDisplay')) {
            return;
        }

        //  Get a list of the forms
        if ($forms = RGFormsModel::get_forms()) {
            
            //  Get the ID of the first one
            if ( $any_formid = $forms[0]->id ) {
                
                //  Enque and print the form styles and scripts
                GFFormDisplay::enqueue_form_scripts( $any_formid, true );
                GFFormDisplay::print_form_scripts( $any_formid, true );
            }
        }
    }

    /**
     * Fix Gravity Form Tabindex Conflicts
     * http://gravitywiz.com/fix-gravity-form-tabindex-conflicts/
     *
     * @param  integer  $tab_index - the current index
     * @param  array $form - the current form
     *
     * @return integer $tab_index - the new index
     */
    function gform_tabindexer( $tab_index, $form = false ) {
        $starting_index = 1000; // if you need a higher tabindex, update this number
        if( $form )
            add_filter( 'gform_tabindex_' . $form['id'], array($this, 'gform_tabindexer'), 10, 2);
        return GFCommon::$tab_index >= $starting_index ? GFCommon::$tab_index : $starting_index;
    }

    /**
     * Return a master list of all the gravity form fields
     */
    function define_fields() {

        $this_fields        = Array();
        $supported_fields   = array("checkbox", "radio", "select", "text", "website", "textarea", "email", "number", "phone", "multiselect");

        $forms = RGFormsModel::get_forms();

        if ($forms)
        {
            foreach( $forms as $form_item )
            {
                $form = RGFormsModel::get_form_meta($form_item->id);

                if ($form['fields'])
                {
                    foreach( $form['fields'] as $field )
                    {
                        if (in_array($field['type'], $supported_fields))
                        {
                            $this_fields[ $form['title'] ][] = $field['label'];
                        }
                    }
                }
            }
        }

        return $this_fields;
    }

   /**
    * Before a Gravity Form is displayed, apply the 
    * progressive profiling rules to it. This does
    * things like make fields required, add css
    * classes, and hide fields.
    *
    * @param  object $form - the form being shown
    *
    * @return object $form - the updated form
    */
   function prepare_fields($form) {

        //  If the forms are being loaded via ajax,
        //  only prepopulate them if this is an 
        //  ajax call.
        if ($this->get_ajax_load_setting()) {
            
            $doing_ajax = false;

            if (defined('DOING_AJAX') && DOING_AJAX) {
                $doing_ajax = true;
            }

            if (isset($_POST['gform_submit'])) {
                $doing_ajax = true;
            }

            if (isset($_GET['ajax'])) {
                $doing_ajax = true;
            }

            if (isset($_POST['ajax'])) {
                $doing_ajax = true;
            }

            if (!$doing_ajax) {
                return $form;
            }
        }

        $profiling_fields       = $this->get_profiling_fields();
        $user_profile           = $this->get_user_profile();

        $profiling_fields_count = $this->get_profile_field_steps();
        $get_auto_hide_fields   = $this->get_auto_hide_fields();
        $requiredcount          = 0;

        if ($profiling_fields)
        {
            foreach( $form['fields'] as $idx => $field )
            {
                if ($field['type'] == 'honeypot')
                {
                    continue;
                }

                // if ($field['adminOnly'] == 1) 
                // {
                //     continue;
                // }

                if ($field['conditionalLogic'])
                {
                    if ($field['label'] == 'Other' || $field['label'] == 'other')
                    {
                        if ($prev_class == 'progressiveprofiling-prefilled') {
                        
                            if ($get_auto_hide_fields) {
                                $form['fields'][ $idx ]['cssClass'] .= ' gf_invisible progressiveprofiling-otherprefilled';
                            } else {
                                $form['fields'][ $idx ]['cssClass'] .= ' progressiveprofiling-otherprefilled';
                            }

                            if ( isset($user_profile[$field['label']]) and $user_profile[$field['label']] )
                            {
                                $form['fields'][ $idx ]['defaultValue'] = $user_profile[ $field['label'] ];

                            }
                        }


                    }

                    if ( isset($user_profile[$field['label']]) and $user_profile[$field['label']] )
                    {
                        if ($field['type'] == 'checkbox')
                        {
                            $selected = $user_profile[$field['label']];

                            foreach( $selected as $sid => $selected_item ) 
                            {
                                $selected[ $sid ] = trim( $selected_item );
                            }
                            
                            $choices = $field['choices'];

                            foreach ($choices as $cid => $choice)
                            {                            
                                if (in_array(trim($choice['value']), $selected))
                                {
                                    $choices[$cid]['isSelected'] = true;
                                    
                                    if ($this->get_ajax_load_setting()) {
                                        $choices[$cid]['value'] = $choices[$cid]['value'] . '[selected]';
                                    }
                                }
                            }

                            $form['fields'][ $idx ]['choices'] = $choices;
                        }
                    }

                    continue;
                }

                if ($field['isRequired'])
                {
                    if ( isset($user_profile[$field['label']]) and $user_profile[$field['label']] )
                    {
                       if ($field['type'] == 'checkbox')
                        {
                            $selected = $user_profile[$field['label']];

                            foreach( $selected as $sid => $selected_item ) 
                            {
                                $selected[ $sid ] = trim( $selected_item );
                            }
                            
                            $choices = $field['choices'];


                            foreach ($choices as $cid => $choice)
                            {         
                                if (in_array(trim($choice['value']), $selected))
                                {
                                    $choices[$cid]['isSelected'] = true;
                                    
                                    if ($this->get_ajax_load_setting()) {
                                        $choices[$cid]['value'] = $choices[$cid]['value'] . '[selected]';
                                    }
                                }
                            }

                            $form['fields'][ $idx ]['choices'] = $choices;
                        }
                        else
                        {
                            $form['fields'][ $idx ]['defaultValue'] = $user_profile[ $field['label'] ];
                        }
                    }

                    else
                    {
                        ++$requiredcount;
                    }

                    continue;
                }

                $enabled_profile_fields = $this->parent->settings->options['enabled_profile_fields'];

                if ( !trim( $field['label']))
                {
                    continue;
                }

                if ( !array_key_exists($field['label'], $enabled_profile_fields))
                {
                    continue;
                }

                $prev_class = '';

                if ( isset($user_profile[$field['label']]) and $user_profile[$field['label']] )
                {
                    if ($field['type'] == 'checkbox')
                    {
                        $selected = $user_profile[$field['label']];

                        foreach( $selected as $sid => $selected_item ) 
                        {
                            $selected[ $sid ] = trim( $selected_item );
                        }
                        
                        $choices = $field['choices'];

                        foreach ($choices as $cid => $choice)
                        {                            
                            if (in_array(trim($choice['value']), $selected))
                            {
                                $choices[$cid]['isSelected'] = true;
                                
                                if ($this->get_ajax_load_setting()) {
                                    $choices[$cid]['value'] = $choices[$cid]['value'] . '[selected]';
                                }
                            }
                        }

                        $form['fields'][ $idx ]['choices'] = $choices;
                    }
                    else
                    {
                        $form['fields'][ $idx ]['defaultValue'] = $user_profile[ $field['label'] ];
                    }

                    if ($get_auto_hide_fields) {
                        $form['fields'][ $idx ]['cssClass'] .= ' gf_invisible progressiveprofiling-prefilled';
                    } else {
                        $form['fields'][ $idx ]['cssClass'] .= ' progressiveprofiling-prefilled';
                    }

                    $prev_class = 'progressiveprofiling-prefilled';
                }

                elseif (++$requiredcount <= $profiling_fields_count)
                {
                    $form['fields'][ $idx ]['cssClass']   .= ' progressiveprofiling-required';
                    $form['fields'][ $idx ]['isRequired'] = 1;

                    $prev_class = 'progressiveprofiling-required';
                }

                else
                {
                    if ($get_auto_hide_fields) {
                        $form['fields'][ $idx ]['cssClass'] .= ' gf_invisible progressiveprofiling-notrequired';
                    } else { 
                        $form['fields'][ $idx ]['cssClass'] .= ' progressiveprofiling-notrequired';
                    }

                    $prev_class = 'progressiveprofiling-notrequired';
                }

                
                //  Create the safe selector name with multibyte support
                if (function_exists('mb_strtolower')) 
                {
                    $selectorname = mb_strtolower( $field['label'] );
                    $selectorname = mb_ereg_replace( "^[\.]|[^a-zA-Z0-9.]", '', $selectorname );
                } 

                //  Otherwise, fallback if multibyte is not available
                else 
                {
                    $selectorname = preg_replace('/\W+/','',strtolower(strip_tags($field['label'])));
                }

                $form['fields'][ $idx ]['cssClass'] .= ' profilevalue-' . $selectorname;
            }
        }

        return $form;
    }

    /**
     * When a Gravity Form is being validated, apply the
     * progressive profiling validation logic to it
     *
     * @param  array $validation_result - the default validation result
     *
     * @return array $validation_result - the modified validation result
     */
    function validate_submission( $validation_result ) {

        $profiling_fields       = $this->get_profiling_fields();
        $user_profile           = $this->get_user_profile();
        $profiling_fields_count = $this->get_profile_field_steps();
        $enabled_profile_fields = $this->parent->settings->options['enabled_profile_fields'];

        if ($form = $validation_result["form"])
        {
            foreach($form['fields'] as $idx => $field)
            {
                if ($field['type'] == 'honeypot')
                {
                    continue;
                }

                // if ($field['adminOnly'] == 1) 
                // {
                //     continue;
                // }

                if ( !trim( $field['label']))
                {
                    continue;
                }

                if ( !array_key_exists($field['label'], $enabled_profile_fields))
                {
                    continue;
                }

                if ( $user_profile[ $field['label'] ] )
                {
                    continue;
                }


                $failed = false;

                if ($field['type'] == 'checkbox')
                {
                    // This is a checkbox - are any of the choices
                    // selected?
                    $selected = false;

                    foreach ($field['inputs'] as $field_input)
                    {
                        $post_key = 'input_'.str_replace('.', '_', $field_input['id']);
                        if (rgpost($post_key))
                        {
                            $selected = true;
                            break;
                        }
                    }

                    if ( ! $selected)
                    {
                        $failed = true;
                    }
                }
                else
                {
                    $postval = rgpost("input_{$field['id']}");

                    if ( ! $postval)
                    {
                        $failed = true;
                    }
                }

                if ($failed)
                {
                    if ($field['conditionalLogic'])
                    {
                        continue;
                    }

                    $field['failed_validation']     = true;
                    $field['validation_message']    = 'This field is required.';

                    $validation_result['form']['fields'][ $idx ] = $field;
                    $validation_result['is_valid']  = false;
                }

                if (++$validate_count == $profiling_fields_count)
                {
                    return $validation_result;
                }
            }
        }

        return $validation_result;
    }

    /**
     * When a Gravity Form submission is being
     * stored, also store the progressive profiling
     * values
     *
     * @param  array $entry - the submitted values
     * @param  array $form - the form meta data
     */
    function store_submission($entry, $form){

        $profiling_fields       = $this->get_profiling_fields();
        $user_profile           = $this->get_user_profile();
        $user_profile_email     = $this->get_user_profile_email();
        $user_email_value       = '';
        $enabled_profile_fields = $this->parent->settings->options['enabled_profile_fields'];
        
        //  Get the email address from the stored profile
        if ($profiling_fields)
        {
            foreach($form['fields'] as $idx => $field)
            {
                if (strtolower( $field['label'] ) == 'email' )
                {
                    $user_email_value = rgpost("input_{$field['id']}");
                }

                else if (strtolower( $field['label'] ) == 'emailaddress' )
                {
                    $user_email_value = rgpost("input_{$field['id']}");
                }

                else if (strtolower( $field['label'] ) == 'email address' )
                {
                    $user_email_value = rgpost("input_{$field['id']}");
                }
            }
        }

        //  New email address? Reset the profile fields
        if ($user_email_value != $user_profile_email)
        {
            $user_profile = Array();
        }

        //  Parse the profile fields
        if ($profiling_fields)
        {
            foreach($form['fields'] as $idx => $field)
            {
                // Is this a progressive profile field? If not,
                // we're done here.
                if ( !array_key_exists( $field['label'], $enabled_profile_fields))
                {
                    continue;
                }

                if ($field['type'] == 'checkbox')
                {
                    // Go through all the options and check
                    // to see if we have a value.
                    $selected = array();

                    foreach ($field['inputs'] as $input)
                    {
                        $post_key = 'input_'.str_replace('.', '_', $input['id']);
                        if (rgpost( $post_key))
                        {
                            $selected[] = rgpost($post_key);
                        }
                    }

                    $user_profile[ $field['label'] ] = $selected;
                }
                else if ($field_value = rgpost("input_{$field['id']}"))
                {
                    $user_profile[ $field['label'] ] = $field_value;
                }
            }
        }

        // Add form UID to this user's submissions.
        if ($user_email_value)
        {
            $uid = $this->moduleid . $form['id'];
            $user_profile['submissions'][ $uid ] = true;

            $this->update_profile($user_email_value, $user_profile);
        }
    }

    /**
     * Get the Gravity Forms that are available as
     * Smart CTAs.
     *
     * @param  boolean $ignore_excluded - should we include forms that have been excluded in the settings?
     *
     * @return array $availble_forms - all the available smart CTA forms
     */
    function get_smartcta_forms( $ignore_excluded=true ){

        $availble_forms = Array();

        $forms = RGFormsModel::get_forms();

        if ($forms)
        {
            foreach( $forms as $form_item )
            {
                if ($ignore_excluded) {
                    if (isset($this->parent->settings->options['smartcta_excludeform_' . $form_item->id])) {
                        continue;
                    }
                }
                
                $form = RGFormsModel::get_form_meta($form_item->id);

                if ($form['fields'])
                {
                    $form_parms               = Array();
                    $form_parms['module']     = $this->moduleid;
                    $form_parms['moduledesc'] = $this->modulename;
                    $form_parms['id']         = $form_item->id;
                    $form_parms['name']       = $form['title'];
                    $form_parms['details']    = 'Gravity Form - Form ID #' . $form_item->id;
                    $availble_forms[]         = $form_parms;

                }
            }
        }

        return $availble_forms;
    }

    /**
     * Render a Gravity Form in the context of 
     * a Smart CTA. 
     *
     * @param  integer $formid - the form being rendered
     * @param  string $formname - the name of the form
     */
    function render_form( $formid, $formname ){

        $form = GFFormsModel::get_form_meta($formid);
        $show_title = $this->parent->settings->options['smartcta_showtitle'];
        $show_desc = $this->parent->settings->options['smartcta_showdesc'];

        $show_title = ($show_title == 1) ? true : false;
        $show_desc = ($show_desc == 1) ? true : false;

        if (file_exists(get_stylesheet_directory() . '/nfprofiling-gravityforms-smartcta.php' )) {

            include( get_stylesheet_directory() . '/nfprofiling-gravityforms-smartcta.php' );

        } else {
            
            ?>
            <div class="sidebar-item sidebar-form sidebar-form-wrapper-<?php print $formid ?>">
            <?php

                if ($this->parent->settings->options['disable_gf_ajax']) {
                    
                    //  Include the form without AJAX
                    if ( headers_sent() && ! $disable_print_form_script ) {
                        GFFormDisplay::print_form_scripts( $form, false );
                    } else {
                        GFFormDisplay::enqueue_form_scripts( $form, false );
                    }

                    gravity_form( $formid, $show_title, $show_desc, false, null, false);

                } else {

                    //  Include the form with AJAX (default)
                    if ( headers_sent() && ! $disable_print_form_script ) {
                        GFFormDisplay::print_form_scripts( $form, true );
                    } else {
                        GFFormDisplay::enqueue_form_scripts( $form, true );
                    }

                    gravity_form( $formid, $show_title, $show_desc, false, null, true);


                }
            ?>
            </div>
            <?php
        }
    }

    /**
     * Has the given form/smart cta already been
     * submitted?
     *
     * @param  integer  $formid - the form in questions
     *
     * @return boolean
     */
    function has_cta_been_submitted( $formid ){

        $uid = $this->moduleid . $formid;

        if ($this->displayed_ctas[ $uid ])
        {
            return true;
        }

        if ( $user_profile = $this->get_user_profile() )
        {
            if (isset($user_profile['submissions'][ $uid ] ))
            {
                return true;
            }
        }

        $this->displayed_ctas[ $uid ] = true;
        return false;
    }

    /**
     * Extract the random id, if present, from the form html
     *
     * @param  string  $form_html - the original form html
     *
     * @return integer
     */
    function extract_random_id( $original_id, $form_html ) {

        //  Default to using the normal form ID
        $random_id = $original_id;

        //  Look for the hidden field with the random id
        $result = preg_match('/<input\b\s+(?=[^>]*name=(["\'])gform_random_id\1)(?=[^>]*type=(["\'])hidden\2)[^>]*value=(["\'])(\d+)\3[^>]*>/im',$form_html, $matches);

        //  Any matches?
        if ($matches) {

            //  Look for a match
            foreach( $matches as $match ) {

                //  Is this an integer match?
                if (intval( $match )) {

                    //  Yes, use the matched random id
                    $random_id = $match;
                }
            }
        }

        //  Return the found random ID, or else
        //  the original ID
        return $random_id;
    }

    /**
     * If fullpage caching is enabled, we'll need to load
     * the user's progressive profiling fields via ajax.
     *
     * @param  string  $form_tag - the original form tag
     * @param  string  $form - the form object
     *
     * @return boolean
     */
    function ajax_formload( $form_string, $form ) {

        //  Is ajax form loading enabled?
        if (!$this->get_ajax_load_setting()) {

            //  No, do nothing?
            return $form_string;
        }

        //  If the forms are being loaded via ajax,
        //  only prepopulate them if this is an 
        //  ajax call.
        if ($this->get_ajax_load_setting()) {
            
            $doing_ajax = false;

            if (defined('DOING_AJAX') && DOING_AJAX) {
                $doing_ajax = true;
            }

            if (isset($_POST['gform_submit'])) {
                $doing_ajax = true;
            }

            if (isset($_GET['ajax'])) {
                $doing_ajax = true;
            }

            if (isset($_POST['ajax'])) {
                $doing_ajax = true;
            }

            if ($doing_ajax) {
                return $form_string;
            }
        }


        //  Get the random form id so we can match it later
        $random_id = $this->extract_random_id( $form['id'], $form_string );

        //  Define the ajax endpoint
        $ajax_url = admin_url( 'admin-ajax.php' );

        //  Get the title of the post/page we're on
        //  This may not work if the post is inside a loop 
        //  or some other bullshit.
        $post_title = get_the_title();

        //  Add the js code for this form
        $js_string = "<script>(function (NfFormLoader, jQuery) {loadAjaxForm(" . $form['id'] . ", " . $random_id . ", '" . $post_title. "');}(window.NfFormLoader = window.NfFormLoader || {}, jQuery));</script>";

        //  Return the form code with the appended js code
        return $form_string . $js_string;
    }

    /**
     * Ajax listener to load the form. Used when fullpage caching
     * is enabled, to allow cookies to be processed and the user's
     * progressive profile data to be loaded.
     *
     * @return json
     */
    function process_loadform() {
        
        //  Is ajax form loading enabled?
        if (!$this->get_ajax_load_setting()) {

            //  No, do nothing
            wp_die();
        }

        //  Verify the ajax call
        check_ajax_referer( 'process_loadform_nonce', 'nonce' );

        //  Get the form ID and the original random ID
        $form_id = isset( $_POST['form_id'] ) ? absint( $_POST['form_id'] ) : 0;
        $original_random_id = isset( $_POST['random_id'] ) ? absint( $_POST['random_id'] ) : 0;
        
        //  Do we have what we need to generate the form?
        if( true && $form_id ) {

            //  Load the form and grab the HTML
            ob_start();
            gravity_form( $form_id, true, false, false, false, true );
            $form_html = ob_get_clean();

        }


        //  Did we get form HTML?
        if ($form_html) {

            //  Extract the new random ID from the loaded form HTML
            $new_random_id = $this->extract_random_id( $form_id, $form_html );

            //  Replace the new random ID with the original id from the form 
            //  we're updating
            $form_html = str_replace( $new_random_id, $original_random_id, $form_html );

            //  Extract the new javascript 
            $form_javascript = '';
            preg_match_all('#<script(.*?)</script>#is', $form_html, $matches);
            foreach ($matches[0] as $value) {
                $form_javascript .= $value;
            }

            //  Build the response
            $response = Array( 
                'form_html' => $form_html,
                'form_javascript' => $form_javascript,
                'original_random_id' => $original_random_id
            );

            //  Return the response
            wp_send_json_success( $response );
        }
        else {

            //  Return the error
            wp_send_json_error( array( 'error' => 'Could not load form' ) );
        }

        //  All done
        wp_die();
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
            'ajax_loadgravityform', 
            NFPROFILING_URL . '/js/ajax_gravityforms.js', 
            array('jquery'), 
            TRUE 
        );

        //  Add the listener
        wp_localize_script( 
            'ajax_loadgravityform', 
            'AjaxController', 
            array(
                'url'   => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( "process_loadform_nonce" ),
            )
        );

        //  Add the CSS
        wp_enqueue_style( 
            'autohide_ajax_loading', 
            NFPROFILING_URL . '/css/autohide_ajax_loading.css'
        );
    }

    /**
     * If autohiding, make sure the .gf_invisible class
     * is set, even if gravityforms styles are disabled
     */
    function init_csscode() {

        //  Is ajax form loading enabled?
        if (!$this->get_auto_hide_fields()) {
            return;
        }

        //  Add the CSS
        wp_enqueue_style( 
            'autohide_progressive_fields', 
            NFPROFILING_URL . '/css/autohide_progressive_fields.css'
        );
    }

}

//  Add the module to the list of modules
array_push( $nfprofiling_moduleclasses, 'NfProfiling_GravityForms' );
