<?php
/**
 * Newfangled Logging
 *
 * @package   Newfangled Logging
 * @author    Newfangled
 * @link      https://bitbucket.org/newfangled_web/newfangled-logging
 * @copyright Newfangled 2016
 */

//	Don't load directly
if (!function_exists('is_admin')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

//	Define the settings class
if (!class_exists("NFLogging_Settings")) {

	/**
	 *
	 * Class: NFLogging_Settings
	 * 
	 */
	class NFLogging_Settings {

		//	Default settings
		public static $default_settings = 
			array( 	
				  	'enable_logging' 	=> true,
				  	'logging_key' 		=> 'https://fbd6ec4c4c1d4abeb33c9b368bb918a1:2a3b739da2eb4955a3e2e88c6258fd0b@app.getsentry.com/74751' );
		var $pagehook, $page_id, $settings_field, $options;

		function __construct() {	
		
			$this->page_id 			= 'nflogging';
			$this->settings_field 	= 'nflogging_options';
			$this->options 			= get_option( $this->settings_field );

			add_action( 'admin_init', array($this, 'admin_init'), 20 );
			add_action( 'admin_menu', array($this, 'admin_menu'), 20) ;

		}
		
		function admin_init() {
		
			register_setting( 	$this->settings_field, 
								$this->settings_field, 
								array($this, 'sanitize_theme_options') );
			
			add_option( $this->settings_field, 
						NFLogging_Settings::$default_settings );
		
		}

		function admin_menu() {
			
			if ( ! current_user_can('update_plugins') ) {
				return;
			}
		
			//	Add a new submenu to the standard Settings panel
			$this->pagehook = $page =  add_options_page(	__('Newfangled Logging', 
															'nflogging'), 
															__('Newfangled Logging', 
															'nflogging'), 'administrator', $this->page_id, array($this,'render') );
			
			//	Executed on-load. Add all metaboxes.
			add_action( 'load-' . $this->pagehook, array( $this, 'metaboxes' ) );

			//	Include js, css, or header *only* for our settings page
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

			$title = __('Newfangled Logging', 'nflogging');
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
					
					//	Close postboxes that should be closed
					$('.if-js-closed').removeClass('if-js-closed').addClass('closed');
					
					//	Postboxes setup
					postboxes.add_postbox_toggles('<?php echo $this->pagehook; ?>');
				});
				//]]>
			</script>
			<?php 

		}
		
		function metaboxes() {

			add_meta_box( 'nflogging-conditions', 
							__( 'Settings', 'nflogging' ), 
							array( $this, 'condition_box' ), 
							$this->pagehook, 
							'main' );

			add_meta_box( 'nflogging-pluginkey', 
							__( 'Plugin Key', 'nflogging' ), 
							array( $this, 'pluginkey_box' ), 
							$this->pagehook, 
							'main' );

			add_meta_box( 	'nflogging-version', 
							__( 'Information', 'nflogging' ), 
							array( $this, 'info_box' ), 
							$this->pagehook, 'main' );
		}

		function condition_box() {
			
			?>
			<p>        
				<input type="checkbox" name="<?php echo $this->get_field_name( 'enable_logging' ); ?>" id="<?php echo $this->get_field_id( 'enable_logging' ); ?>" value="1" <?php echo isset($this->options['enable_logging']) ? 'checked' : '';?> /> 
				<label for="<?php echo $this->get_field_id( 'enable_logging' ); ?>"><?php _e( 'Enable Error Logging via Sentry', 'nflogging' ); ?></label>
	            <br/>
	        </p>
	        <p>        
				<label for="<?php echo $this->get_field_id( 'logging_key' ); ?>"><?php _e( 'Sentry Access Key', 'logging_key' ); ?></label><br/>
				<input style="width:100%;margin-top:10px;" type="text" name="<?php echo $this->get_field_name( 'logging_key' ); ?>" id="<?php echo $this->get_field_id( 'logging_key' ); ?>" value="<?php print $this->options['logging_key'] ?>" /> 
	            <br/>
	        </p>

	        <?php 

		}

		function pluginkey_box() {
			
			?>
			<p>        
				<label for="<?php echo $this->get_field_id( 'plugin_key' ); ?>"><?php _e( 'Plugin License Key', 'plugin_key' ); ?></label><br/>
				<input style="width:100%;margin-top:10px;" type="text" name="<?php echo $this->get_field_name( 'plugin_key' ); ?>" id="<?php echo $this->get_field_id( 'plugin_key' ); ?>" value="<?php print $this->options['plugin_key'] ?>" /> 
	            <br/>
	        </p>

			<?php 

		}

		function info_box() {

			?>
			<p>
				<strong><?php _e( 'Version:', 'nflogging' ); ?></strong> <?php echo NFLOGGING_VERSION; ?> <?php echo '&middot;'; ?> 
				<strong><?php _e( 'Released:', 'nflogging' ); ?></strong> <?php echo NFLOGGING_RELEASE_DATE; ?>
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