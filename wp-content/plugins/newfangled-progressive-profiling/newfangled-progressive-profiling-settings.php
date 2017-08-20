<?php
/**
 * Newfangled Progressive Profiling
 *
 * @package   Newfangled Progressive Profiling
 * @author    Newfangled
 * @link      https://bitbucket.org/newfangled_web/newfangled-progressive-profiling
 * @copyright Newfangled 2016
 */

//  Don't load directly
if (!function_exists('is_admin')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

//  Define the settings class
if (!class_exists("NFProfiling_Settings")) {

    /**
     *
     * Class: NFProfiling_Settings
     * 
     */
    class NFProfiling_Settings {

        //  Default settings
        public static $default_settings = array(    
            'enable_profiling' => true,
            'profile_field_steps' => 3,
            'enabled_profile_fields' => Array(),
            'master_fields' => Array() );
        
        var $pagehook, $page_id, $settings_field, $options;

        function __construct( $parent=Array() ) {   
        
            $this->page_id          = 'nfprofiling';
            $this->settings_field   = 'nfprofiling_options';
            $this->options          = get_option( $this->settings_field );
            $this->modules          = $parent->modules;
            $this->parent           = $parent;

            if (!$this->options)
            {
                $this->options = $this->default_settings;
            }

            add_action( 'admin_init', array($this, 'admin_init'), 20 );
            add_action( 'admin_menu', array($this, 'admin_menu'), 20) ;
        
        }
        
        function admin_init() {
        
            register_setting(   
                $this->settings_field, 
                $this->settings_field, 
                array($this, 'sanitize_theme_options') );
            
        //  add_option( $this->settings_field, 
        //              NFProfiling_Settings::$default_settings );
        
        }

        function admin_menu() {
            
            if ( ! current_user_can('update_plugins') ) {
                return;
            }
        
            //  Add a new submenu to the standard Settings panel
            $this->pagehook = $page =  add_options_page(    
                __('Newfangled Progressive Profiling', 'nfprofiling'), 
                __('Newfangled Progressive Profiling', 'nfprofiling'), 
                'administrator', 
                $this->page_id, 
                array($this,'render') );
            
            //  Executed on-load. Add all metaboxes.
            add_action( 'load-' . $this->pagehook, array( $this, 'metaboxes' ) );

            //  Include js, css, or header *only* for our settings page
            add_action("admin_print_scripts-$page", array($this, 'js_includes'));
        
        }
         
        function js_includes() {
        
            // Needed to allow metabox layout and close functionality.
            wp_enqueue_script( 'postbox' );
        
        }

        function sanitize_theme_options($options) {
        
            return $options;
        
        }

        protected function get_field_name( $name ) {

            return sprintf( '%s[%s]', $this->settings_field, $name );

        }

        protected function get_field_id( $id ) {

            return sprintf( '%s[%s]', $this->settings_field, $id );

        }

        function render() {
        
            global $wp_meta_boxes;

            $this->parent->load_settings();
            $this->parent->get_master_fields_list();

            $title = __('Newfangled Progressive Profiling', 'nfprofiling');
            ?>
            <div class="wrap">   
                <h2><?php echo esc_html( $title ); ?></h2>
            
                <form method="post" action="options.php">
                    <div class="metabox-holder">
                        <div class="postbox-container" style="width: 99%;">
                        <?php 
                            settings_fields($this->settings_field); 
                            do_meta_boxes( $this->pagehook, 'main', null );
                        ?>
                        </div>
                    </div>
                    <p>
                        <input type="submit" class="button button-primary" name="save_options" value="<?php esc_attr_e('Save Options'); ?>" />
                    </p>
                </form>
            </div>
            
            <!-- Needed to allow metabox layout and close functionality. -->
            <script type="text/javascript">
                //<![CDATA[
                jQuery(document).ready( function ($) {
                    // close postboxes that should be closed
                    $('.if-js-closed').removeClass('if-js-closed').addClass('closed');
                    // postboxes setup
                    postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
                });
                //]]>
            </script>
            <?php 

        }
        
        function metaboxes() {

            add_meta_box( 'nfprofiling-conditions', 
                            __( 'Settings', 'nfprofiling' ), 
                            array( $this, 'condition_box' ), 
                            $this->pagehook, 
                            'main' );

            add_meta_box(   'nfprofiling-fields', 
                            __( 'Profile Fields', 'nfprofiling' ), 
                            array( $this, 'fields_box' ), 
                            $this->pagehook, 'main' );

            add_meta_box(   'nfprofiling-smartcta', 
                            __( 'Smart CTA Settings', 'nfprofiling' ), 
                            array( $this, 'smartcta_box' ), 
                            $this->pagehook, 'main' );

            add_meta_box(   'nfprofiling-modules', 
                            __( 'Modules', 'nfprofiling' ), 
                            array( $this, 'modules_box' ), 
                            $this->pagehook, 'main' );

            
            add_meta_box(   'nfprofiling-version', 
                            __( 'Information', 'nfprofiling' ), 
                            array( $this, 'info_box' ), 
                            $this->pagehook, 'main' );
        
        }

        function condition_box() {
        
            ?>
            <p>
                <input type="checkbox" name="<?php echo $this->get_field_name( 'enable_profiling' ); ?>" id="<?php echo $this->get_field_id( 'enable_profiling' ); ?>" value="enable_profiling" <?php echo isset($this->options['enable_profiling']) ? 'checked' : '';?> /> 
                <label for="<?php echo $this->get_field_id( 'enable_profiling' ); ?>"><?php _e( 'Enable Progressive Profiling', 'nfprofiling' ); ?></label>

                <br/><br/>

                <i>Progressive profiling dynamically makes form fields required based on the fields that a user has previously filled out, with the goal of collecting as much data from the visitor as possible, the more forms they fill out. It also provides 'Smart CTAs' - widgets that show a progression of forms, one at a time, until the user has completed all of them.</i>

                <br/><br/>

                <hr><br/>

                
                <input type="checkbox" name="<?php echo $this->get_field_name( 'auto_hide' ); ?>" id="<?php echo $this->get_field_id( 'auto_hide' ); ?>" value="auto_hide" <?php echo isset($this->options['auto_hide']) ? 'checked' : '';?> /> 
                <label for="<?php echo $this->get_field_id( 'auto_hide' ); ?>"><?php _e( 'Automatically hide progressive profiling fields','nfprofiling' ); ?></label>
                
                <br/><br/>

                <i>If enabled, progressive profiling fields that have already been submitted, or are not yet required, will be hidden on all forms. If greater control is needed on a form-by-form basis, this may be disabled, and instead the classes '.progressiveprofiling-notrequired' and '.progressiveprofiling-prefilled' can be used to manually control this behavior.</i>

                <br/><br/><hr><br/>

                <label for="<?php echo $this->get_field_id( 'profile_field_steps' ); ?>"><?php _e( 'Fields Per Step', 'nfprofiling' ); ?>: </label>
                <select name="<?php echo $this->get_field_name( 'profile_field_steps' ); ?>">
                    <option <?php echo ($this->options['profile_field_steps'] == '3') ? ' selected' : '';?>>3</option>
                    <option <?php echo ($this->options['profile_field_steps'] == '2') ? ' selected' : '';?>>2</option>
                </select>

                <br/><br/>

                <i>The number of additional progressive profiling fields to require on each additional form submission.</i>

                <br/><br/><hr><br/>

                <input type="checkbox" name="<?php echo $this->get_field_name( 'ajax_form_loading' ); ?>" id="<?php echo $this->get_field_id( 'ajax_form_loading' ); ?>" value="ajax_form_loading" <?php echo isset($this->options['ajax_form_loading']) ? 'checked' : '';?> /> 
                <label for="<?php echo $this->get_field_id('ajax_form_loading' ); ?>"><?php _e( 'Show Progressive Profile values and Smart CTAs via Ajax','nfprofiling' ); ?></label>

                <br/><br/>

                <i>If the site uses full-page caching (such as required by WP Engine) use this setting. Otherwise, leave disabled.</i>


                <br/><br/><hr><br/>

                <input type="checkbox" name="<?php echo $this->get_field_name( 'force_scripts_header' ); ?>" id="<?php echo $this->get_field_id( 'force_scripts_header' ); ?>" value="force_scripts_header" <?php echo isset($this->options['force_scripts_header']) ? 'checked' : '';?> /> 
                <label for="<?php echo $this->get_field_id('force_scripts_header' ); ?>"><?php _e( 'Script/style compatability mode','nfprofiling' ); ?></label>

                <br/><br/>

                <i>If there are display or javascript issues related to forms (particulary when part of a smart CTA), enable this option to force the form resources to always load in the header on every page.</i>

                <br/><hr><br/>

                <input type="checkbox" name="<?php echo $this->get_field_name( 'disable_gf_ajax' ); ?>" id="<?php echo $this->get_field_id( 'disable_gf_ajax' ); ?>" value="1" <?php echo isset($this->options['disable_gf_ajax']) ? 'checked' : '';?> /> 
                <label for="<?php echo $this->get_field_id('disable_gf_ajax' ); ?>"><?php _e( 'Disable Gravity Forms Ajax','nfgated' ); ?></label>

                <br/><br/>

                <i>By default, Smart CTAs containing forms will use Gravity Form's ajax functionality. Use this setting to disable it.</i>


            </p>
            <?php

        }

        function modules_box() {

            if (!$modules_list = $this->parent->modules)
            {
                return;
            }

            $enabled_modules = $this->options['enabled_modules'];

            ?>
            <p><i>Modules provide an interface to define the forms, fields, and smart CTAs that make up the progressive profiling system.</i></p>

            <table class="widefat fixed" cellspacing="0">
                <thead>
                    <tr>
                        <th scope="col" id="cb" class="manage-column column-cb check-column" style="padding: 8px 10px;"><input type="checkbox" name="modules-all" /></th>
                        <th scope="col" id="active" class="manage-column" style="padding: 8px 10px;"><label for="modules-all"><?php _e("Module Name", "progressiveprofiling") ?></label></th>
                    </tr>
                </thead>
                <tbody class="list:user user-list">
                <?php 

                foreach( $modules_list as $module )
                {
                    if ($modulename = $module->modulename)
                    {
                        ?>
                        <tr>
                            <th scope="col" id="cb" class="manage-column column-cb check-column" style="padding: 8px 10px;"><input type="checkbox" name="<?php echo $this->get_field_name( 'enabled_modules' ); ?>[<?php echo $modulename ?>]" id="checkbox-<?php echo $modulename ?>" value="<?php echo $modulename ?>" <?php echo isset($this->options['enabled_modules'][ $modulename ]) ? 'checked' : '';?> /></th>
                            <th scope="col" id="active" class="manage-column check-column" style="padding: 8px 10px;"><label for="checkbox-<?php echo $modulename ?>"><?php _e( $modulename, 'nfprofiling' ); ?></label><p><i><?php _e($module->moduledesc, 'nfprofiling') ?></i></p></th>
                        </tr>
                        <?php
                    }
                }
                ?>
                </tbody>
            </table>
            <br/><br/>
            <?php

        }

        function info_box() {
        
            ?>
            <p>
                <strong><?php _e( 'Version:', 'nfprofiling' ); ?></strong> <?php echo NFPROFILING_VERSION; ?> <?php echo '&middot;'; ?> 
                <strong><?php _e( 'Released:', 'nfprofiling' ); ?></strong> <?php echo NFPROFILING_RELEASE_DATE; ?>
            </p>
            <p>
                For more information and support details, please view our <a href="https://www.newfangled.com/plugin-support-policy" target="_blank">plugin support policy</a>. 
            </p>
            <p>
                &copy; <?php date('Y') ?> <a href="https://www.newfangled.com" target="_blank">Newfangled.com</a>.
            </p>
            <?php
        
        }

        function fields_box() {

            if (!$master_fields_list = $this->parent->master_fields_list)
            {
                return;
            }

            $enabled_profile_fields = $this->options['enabled_profile_fields'];

            ?>
            <p>
                <i>The master list of profiling fields. Every form containing these fields will dynamically require more as the visitor submits more forms, with 2 or 3 additional fields required for each new form they complete.</i>
            <p>
            <table class="widefat fixed" cellspacing="0">
                <thead>
                    <tr>
                        <th scope="col" id="cb" class="manage-column column-cb check-column" style="padding: 8px 10px;"><input type="checkbox" /></th>
                        <th scope="col" id="active" class="manage-column" style="padding: 8px 10px;"><?php _e("Field", "progressiveprofiling") ?></th>
                        <th scope="col" class="manage-column" style="padding: 8px 10px;"><?php _e("Appears In", "progressiveprofiling") ?></th>
                    </tr>
                </thead>
                <tbody class="list:user user-list">
                <?php 

                foreach( $master_fields_list as $fieldname => $appears_in_modules )
                {
                    $appears_in_value = '';
                    asort($appears_in_modules);

                    if (!$appears_in_modules)
                    {
                        continue;
                    }

                    foreach( $appears_in_modules as $name => $instances )
                    {
                        asort($instances);
                        $appears_in_value .= '<b>' . $name . '</b>: ';
                        $appears_in_value .= implode( ', ', $instances );
                        $appears_in_value .= '<br/>';
                    }

                    ?>
                    <tr>
                        <th scope="col" id="cb" class="manage-column column-cb check-column" style="padding: 8px 10px;"><input type="checkbox" name="<?php echo $this->get_field_name( 'enabled_profile_fields' ); ?>[<?php echo $fieldname ?>]" value="<?php echo $fieldname ?>" <?php echo isset($this->options['enabled_profile_fields'][ $fieldname ]) ? 'checked' : '';?> /></th>
                        <th scope="col" id="active" class="manage-column check-column" style="padding: 8px 10px;"><?php _e( $fieldname, 'nfprofiling' ); ?></th>
                        <th scope="col" class="manage-column" style="padding: 8px 10px;"><?php print $appears_in_value ?></th>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
            <br/><br/>
            <?php
            
        }

        function smartcta_box() {
        
            ?>
            <p>
                <i>Note: may not apply to Smart CTAs with custom templates</i>
            <p>
                <input type="checkbox" name="<?php echo $this->get_field_name( 'smartcta_showtitle' ); ?>" id="<?php echo $this->get_field_id( 'smartcta_showtitle' ); ?>" value="1" <?php echo isset($this->options['smartcta_showtitle']) ? 'checked' : '';?> /> 
                <label for="<?php echo $this->get_field_id( 'smartcta_showtitle' ); ?>"><?php _e( 'Show Form Title in Smart CTAs', 'smartcta_showtitle' ); ?></label>
            </p>

            <p>
                <input type="checkbox" name="<?php echo $this->get_field_name( 'smartcta_showdesc' ); ?>" id="<?php echo $this->get_field_id( 'smartcta_showdesc' ); ?>" value="1" <?php echo isset($this->options['smartcta_showdesc']) ? 'checked' : '';?> /> 
                <label for="<?php echo $this->get_field_id( 'smartcta_showdesc' ); ?>"><?php _e( 'Show Form Description in Smart CTAs', 'smartcta_showdesc' ); ?></label>
            </p>
            <br/>
            <hr/>
            <br/>
            <b>Exclude From Smart CTA</b>
            <p>
                <i>Exclude these items from the list of available Smart CTAs</i>
            </p>
            <?php

            $forms = $this->parent->get_smartcta_forms( FALSE );

            if ($forms)
            {
                foreach( $forms as $form_item )
                {
                    ?>
                    <p>
                        <input type="checkbox" name="<?php echo $this->get_field_name( 'smartcta_excludeform_' . $form_item['id'] ); ?>" id="<?php echo $this->get_field_id( 'smartcta_excludeform_' . $form_item['id'] ); ?>" value="1" <?php echo isset($this->options['smartcta_excludeform_' . $form_item['id']]) ? 'checked' : '';?> /> 
                        <label for="<?php echo $this->get_field_id( 'smartcta_excludeform_' . $form_item['id'] ); ?>"><?php _e( $form_item['name'] ); ?></label>
                    </p>
                    <?php
                }
            }
        }
    } 
}