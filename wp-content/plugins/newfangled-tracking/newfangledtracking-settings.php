<?php
/**
 * Newfangled Tracking
 *
 * @package   Newfangled Tracking
 * @author    Newfangled
 * @link      https://bitbucket.org/newfangled_web/newfangled-tracking
 * @copyright Newfangled 2016
 */

//  Don't load directly
if (!function_exists('is_admin')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

//  Define the settings class
if (!class_exists("NFTracking_Settings")) {

    class NFTracking_Settings {

        //  Default settings
        public static $default_settings = 
            array(  
                    'enable_tracking'           => true,
                    'tracking_server_url'       => 'https://tracker.newfangled.com' );
        var $pagehook, $page_id, $settings_field, $options;

        function __construct() {    
        
            $this->page_id          = 'nftracking';
            $this->settings_field   = 'nftracking_options';
            $this->options          = get_option( $this->settings_field );

            add_action( 'admin_init', array($this, 'admin_init'), 20 );
            add_action( 'admin_menu', array($this, 'admin_menu'), 20) ;

        }
        
        function admin_init() {
        
            register_setting(   $this->settings_field, 
                                $this->settings_field, 
                                array($this, 'sanitize_theme_options') );
            
            add_option( $this->settings_field, 
                        NFTracking_Settings::$default_settings );
        
        }

        function admin_menu() {
            
            if ( ! current_user_can('update_plugins') ) {
                return;
            }
        
            //  Add a new submenu to the standard Settings panel
            $this->pagehook = $page =  add_options_page(    __('Newfangled Tracking', 
                                                            'nftracking'), 
                                                            __('Newfangled Tracking', 
                                                            'nftracking'), 'administrator', $this->page_id, array($this,'render') );
            
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

            $title = __('Newfangled Tracking', 'nftracking');
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
                    
                    //  Close postboxes that should be closed
                    $('.if-js-closed').removeClass('if-js-closed').addClass('closed');
                    
                    //  Postboxes setup
                    postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
                });
                //]]>
            </script>
            <?php 

        }
        
        function metaboxes() {

            add_meta_box( 'nftracking-conditions', 
                            __( 'Settings', 'nftracking' ), 
                            array( $this, 'condition_box' ), 
                            $this->pagehook, 
                            'main' );

            add_meta_box(   'nftracking-version', 
                            __( 'Information', 'nftracking' ), 
                            array( $this, 'info_box' ), 
                            $this->pagehook, 'main' );
        }

        function condition_box() {
            
            ?>
            <p>        
                <input type="checkbox" name="<?php echo $this->get_field_name( 'enable_tracking' ); ?>" id="<?php echo $this->get_field_id( 'enable_tracking' ); ?>" value="enable_tracking" <?php echo isset($this->options['enable_tracking']) ? 'checked' : '';?> /> 
                <label for="<?php echo $this->get_field_id( 'enable_tracking' ); ?>"><?php _e( 'Enable Newfangled Tracking', 'nftracking' ); ?></label>
                <br/>
            </p>
            <p>        
                <label for="<?php echo $this->get_field_id( 'tracking_server_url' ); ?>"><?php _e( 'Tracking Service URL', 'nftracking' ); ?></label><br/>
                <input style="width:100%;margin-top:10px;" type="text" name="<?php echo $this->get_field_name( 'tracking_server_url' ); ?>" id="<?php echo $this->get_field_id( 'tracking_server_url' ); ?>" value="<?php print $this->options['tracking_server_url'] ?>" /> 
                <br/>
            </p>
            <p>        
                <label for="<?php echo $this->get_field_id( 'tracking_server_token' ); ?>"><?php _e( 'Tracking Service API Token', 'nftracking' ); ?></label><br/>
                <input style="width:100%;margin-top:10px;" type="text" name="<?php echo $this->get_field_name( 'tracking_server_token' ); ?>" id="<?php echo $this->get_field_id( 'tracking_server_token' ); ?>" value="<?php print $this->options['tracking_server_token'] ?>" /> 
                <br/>
            </p>

            <br/><hr><br/>

            <input type="checkbox" name="<?php echo $this->get_field_name( 'ajax_tracker_loading' ); ?>" id="<?php echo $this->get_field_id( 'ajax_tracker_loading' ); ?>" value="ajax_tracker_loading" <?php echo isset($this->options['ajax_tracker_loading']) ? 'checked' : '';?> /> 
            <label for="<?php echo $this->get_field_id('ajax_tracker_loading' ); ?>"><?php _e( 'Load Tracker via Ajax','nfgated' ); ?></label>

            <br/><br/>

            <i>If the site uses full-page caching (such as required by WP Engine) use this setting. Otherwise, leave disabled.</i>

            <?php 

        }

        function info_box() {

            ?>
            <p>
                <strong><?php _e( 'Version:', 'nftracking' ); ?></strong> <?php echo NFTRACKING_VERSION; ?> <?php echo '&middot;'; ?> 
                <strong><?php _e( 'Released:', 'nftracking' ); ?></strong> <?php echo NFTRACKING_RELEASE_DATE; ?>
            </p>
            <p>
                For more information and support details, please view our <a href="https://www.newfangled.com/plugin-support-policy" target="_blank">plugin support policy</a>. 
            </p>
            <p>
                &copy; <?php date('Y') ?> <a href="https://www.newfangled.com" target="_blank">Newfangled.com</a>.
            </p>
            <?php
             
        }
    } 
}