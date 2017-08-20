<?php
/**
 * Newfangled Console
 *
 * @package   Newfangled Console
 * @author    Newfangled
 * @link      https://bitbucket.org/newfangled_web/newfangled-console
 * @copyright Newfangled 2016
 */

//	Don't load directly
if (!function_exists('is_admin')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

//	Define the settings class
if (!class_exists("NFConsole_Settings")) {

	/**
	 *
	 * Class: NFConsole_Settings
	 * 
	 */
	class NFConsole_Settings {

		//	Default settings
		public static $default_settings = 
			array( 	
				  	'enable_console' 			=> true,
				  	'enable_console_logo' 		=> true );
		var $pagehook, $page_id, $settings_field, $options;

		function __construct() {	
		
			$this->page_id 			= 'nfconsole';
			$this->settings_field 	= 'nfconsole_options';
			$this->options 			= get_option( $this->settings_field );

			add_action( 'admin_init', array($this, 'admin_init'), 20 );
			add_action( 'admin_menu', array($this, 'admin_menu'), 20) ;
		
		}
		
		function admin_init() {
		
			register_setting( 	$this->settings_field, 
								$this->settings_field, 
								array($this, 'sanitize_theme_options') );
			
			add_option( $this->settings_field, 
						NFConsole_Settings::$default_settings );
		
		}

		function admin_menu() {
			
			if ( ! current_user_can('update_plugins') ) {
				return;
			}
		
			//	Add a new submenu to the standard Settings panel
			$this->pagehook = $page =  add_options_page(	__('Newfangled Console', 
															'nfconsole'), 
															__('Newfangled Console', 
															'nfconsole'), 'administrator', $this->page_id, array($this,'render') );
			
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

			$title = __('Newfangled Console', 'nfconsole');
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

			add_meta_box( 'nfconsole-conditions', 
							__( 'Settings', 'nfconsole' ), 
							array( $this, 'condition_box' ), 
							$this->pagehook, 
							'main' );

			add_meta_box( 	'nfconsole-version', 
							__( 'Information', 'nfconsole' ), 
							array( $this, 'info_box' ), 
							$this->pagehook, 'main' );
		}

		function condition_box() {
			
			?>
			<p>        
				<input type="checkbox" name="<?php echo $this->get_field_name( 'enable_console' ); ?>" id="<?php echo $this->get_field_id( 'enable_console' ); ?>" value="enable_console" <?php echo isset($this->options['enable_console']) ? 'checked' : '';?> /> 
				<label for="<?php echo $this->get_field_id( 'enable_console' ); ?>"><?php _e( 'Enable Newfangled Console', 'nfconsole' ); ?></label>
	            <br/><br/>
	            
				<input type="checkbox" name="<?php echo $this->get_field_name( 'enable_edit_links' ); ?>" id="<?php echo $this->get_field_id( 'enable_edit_links' ); ?>" value="enable_edit_links" <?php echo isset($this->options['enable_edit_links']) ? 'checked' : '';?> /> 
				<label for="<?php echo $this->get_field_id( 'enable_edit_links' ); ?>"><?php _e( 'Show Inline Edit Links', 'nfconsole' ); ?><br/><i>If checked, the Newfangled Console will attempt to automatically add inline edit links where-ever the_content() is called. Disable this if display issues occur.</i></label>
			</p>
			<p>        
				<label for="<?php echo $this->get_field_id( 'menu_offset_position' ); ?>"><?php _e( 'Menu Offset Position', 'nfconsole' ); ?></label><br/>
				<input style="width:100%;margin-top:10px;" type="text" name="<?php echo $this->get_field_name( 'menu_offset_position' ); ?>" id="<?php echo $this->get_field_id( 'menu_offset_position' ); ?>" value="<?php print $this->options['menu_offset_position'] ?>" /> 
	            <br/>
	        </p>
			<?php 

		}

		function info_box() {

			?>
			<p>
				<strong><?php _e( 'Version:', 'nfconsole' ); ?></strong> <?php echo NFCONSOLE_VERSION; ?> <?php echo '&middot;'; ?> 
				<strong><?php _e( 'Released:', 'nfconsole' ); ?></strong> <?php echo NFCONSOLE_RELEASE_DATE; ?>
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