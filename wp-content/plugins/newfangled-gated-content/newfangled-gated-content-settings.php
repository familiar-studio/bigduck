<?php
/**
 * Newfangled Gated Content
 *
 * @package   Newfangled Gated Content
 * @author    Newfangled
 * @link      https://bitbucket.org/newfangled_web/newfangled-gated-content
 * @copyright Newfangled 2016
 */

//	Don't load directly
if (!function_exists('is_admin')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

//	Define the settings class
if (!class_exists("NFGated_Settings")) {
	
	/**
	 *
	 * Class: NFGated_Settings
	 * 
	 */
	class NFGated_Settings {

		//	Default settings
		public static $default_settings = array( 'include_types' => Array() );
		var $pagehook, $page_id, $settings_field, $options;

		function __construct( $parent=Array() ) {	
		
			$this->page_id 			= 'nfgated';
			$this->settings_field 	= 'nfgated_options';
			$this->options 			= get_option( $this->settings_field );

			if (!$this->options)
			{
				$this->options = $this->default_settings;
			}

			add_action( 'admin_init', array($this, 'admin_init'), 20 );
			add_action( 'admin_menu', array($this, 'admin_menu'), 20) ;
		
		}
		
		function admin_init() {
		
			register_setting( 	$this->settings_field, 
								$this->settings_field, 
								array($this, 'sanitize_theme_options') );
			
			add_option( $this->settings_field, 
						NFGated_Settings::$default_settings );
		

			add_action( 'admin_notices', array($this, 'verify_global_gated_content_form') );

		}

		function admin_menu() {
			
			if ( ! current_user_can('update_plugins') ) {
				return;
			}
		
			//	Add a new submenu to the standard Settings panel
			$this->pagehook = $page =  add_options_page(	__('Newfangled Gated Content', 
															'nfgated'), 
															__('Newfangled Gated Content', 
															'nfgated'), 'administrator', $this->page_id, array($this,'render') );
			
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

		function verify_global_gated_content_form() {
		    
			if (isset($this->options['default_form'])) {

				if ( $global_form = $this->options['default_form'] ) {

					if ( $form = GFAPI::get_form( $global_form ) ) {

						$has_correct_field = FALSE;

						//  For each submitted field
		                foreach( $form['fields'] as $field ) {
		                   
		                   if ($field['label'] == NFGATED_FORM_ID_FIELD_NAME ) {
 
		                   		if (	isset($field->inputName) && 
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

		}
		
		function render() {
		
			global $wp_meta_boxes;


			$title = __('Newfangled Gated Content', 'nfgated');
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

			add_meta_box( 'nfgated-general', 
							__( 'General Settings', 'nfgated' ), 
							array( $this, 'general_box' ), 
							$this->pagehook, 
							'main' );

			add_meta_box( 'nfgated-types', 
							__( 'Include Types', 'nfgated' ), 
							array( $this, 'types_box' ), 
							$this->pagehook, 
							'main' );

			add_meta_box( 	'nfgated-version', 
							__( 'Information', 'nfgated' ), 
							array( $this, 'info_box' ), 
							$this->pagehook, 'main' );
		
		}

		function general_box() {
		
			
    		?>
    		<p>
    		<i>Posts that are 'gated' will display the main content on their details page, followed by the gated content form. Once this form is filled out, the details page will display the main content, followed by a new custom field, ‘Protected Content’. </i>
    		</p>
    		<hr/>
			<p>        
				<table>
					<tr>
						<td valign="top">
							<input type="checkbox" name="<?php echo $this->get_field_name( 'enable_gatedcontent' ); ?>" id="<?php echo $this->get_field_id( 'enable_gatedcontent' ); ?>" value="<?php print 'enable_gatedcontent' ?>" <?php echo isset($this->options['enable_gatedcontent']) ? 'checked' : '';?> /> 
						</td>
						<td valign="top">
							<label for="<?php echo $this->get_field_id( 'enable_gatedcontent' ); ?>">
								<?php _e( 'Enable Gated Content', 'nfgated' ); ?><br/>
								</label>
						</td>
					</tr>
				</table>


	        </p>
			<?php 

			?>
			<p>        
				<table>
					<tr>
						<td valign="top">
							<input type="checkbox" name="<?php echo $this->get_field_name( 'disable_autocontent' ); ?>" id="<?php echo $this->get_field_id( 'disable_autocontent' ); ?>" value="<?php print 'disable_autocontent' ?>" <?php echo isset($this->options['disable_autocontent']) ? 'checked' : '';?> /> 
						</td>
						<td valign="top">
							<label for="<?php echo $this->get_field_id( 'disable_autocontent' ); ?>">
								<?php _e( 'Manually Control Protected Content Display', 'nfgated' ); ?><br/>
								 <i>By default, making a post 'gated' also provides you with a content area, in which to enter the content to appear after the form is completed. This is the simplest way to use this plugin. 
								 Optionally, you can control manually what displays after the form is completed, at the template level. This requires adding your own custom fields, and writing your own logic in your templates. Use the function $nf_gatedcontent->is_content_gated() to determine if the current post should show the gated form, or not.</i>
								</label>
						</td>
					</tr>
				</table>
	           
	           
	        </p>
			<?php 

			?>
			<p>        
				<table>
					<tr>
						<td valign="top">
							<input type="checkbox" name="<?php echo $this->get_field_name( 'make_smartctas' ); ?>" id="<?php echo $this->get_field_id( 'make_smartctas' ); ?>" value="<?php print 'make_smartctas' ?>" <?php echo isset($this->options['make_smartctas']) ? 'checked' : '';?> /> 
						</td>
						<td valign="top">
							<label for="<?php echo $this->get_field_id( 'make_smartctas' ); ?>">
								<?php _e( 'Use as Smart CTAs', 'nfgated' ); ?><br/>
								 <i>If enabled, each post flagged as 'Gated' will appear in the list of Smart CTAs as a discrete item.</i>
								</label>
						</td>
					</tr>
				</table>
	            
	           
	        </p>
			<?php 


			?>
			<p>      
				<label for="<?php echo $this->get_field_id( 'default_form' ); ?>">
				<?php _e( 'Default Gated Content Form', 'nfgated' ); ?><br/>
				 <i>If selected, all posts marked as 'Gated' will use this form. Otherwise, the form will need to be selected on a case-by-case basis.</i>
				</label><br/><br/>

				<select id="default_form" name="<?php echo $this->get_field_name( 'default_form' ); ?>" id="<?php echo $this->get_field_id( 'default_form' ); ?>"><option value=""></option>
				<?php

	            $forms = RGFormsModel::get_forms( null, 'title' );

	            foreach( $forms as $form )
	            {
	                if ($this->options['default_form'] == $form->id)
	                {
	                      echo '<option selected value="' . $form->id . '">' . $form->title . '</option>';
	                }

	                else
	                {
	                      echo '<option value="' . $form->id . '">' . $form->title . '</option>';
	                }
	            }

	            ?>
	            </select>
	           
	        </p>


	        <br/><hr><br/>

            <input type="checkbox" name="<?php echo $this->get_field_name( 'ajax_content_loading' ); ?>" id="<?php echo $this->get_field_id( 'ajax_content_loading' ); ?>" value="ajax_content_loading" <?php echo isset($this->options['ajax_content_loading']) ? 'checked' : '';?> /> 
            <label for="<?php echo $this->get_field_id('ajax_content_loading' ); ?>"><?php _e( 'Show Gated Content via Ajax','nfgated' ); ?></label>

            <br/><br/>

            <i>If the site uses full-page caching (such as required by WP Engine) use this setting. Otherwise, leave disabled.</i>

	        <br/><hr><br/>

            <input type="checkbox" name="<?php echo $this->get_field_name( 'disable_gf_ajax' ); ?>" id="<?php echo $this->get_field_id( 'disable_gf_ajax' ); ?>" value="1" <?php echo isset($this->options['disable_gf_ajax']) ? 'checked' : '';?> /> 
            <label for="<?php echo $this->get_field_id('disable_gf_ajax' ); ?>"><?php _e( 'Disable Gravity Forms Ajax','nfgated' ); ?></label>

            <br/><br/>

            <i>By default, the pre-gated content Gravity Form will use Gravity Form's ajax functionality. Use this setting to disable it.</i>


			<?php 
		}

		function types_box() {
		
			if (!$post_types = get_post_types())
		    {
		        return;
		    }

			?>
			<i>Select the post types that can be 'Gated'. These types will then contain the 'Gated Content' meta box.</i>
			<?php

			foreach( $post_types as $post_type )
	    	{   
	    		$post_type_object   = get_post_type_object( $post_type );
	        	$post_type_desc     = $post_type_object->labels->name;

	    		?>
				<p>        
					<input type="checkbox" name="<?php echo $this->get_field_name( $post_type_desc ); ?>" id="<?php echo $this->get_field_id( $post_type_desc ); ?>" value="<?php print $post_type_desc ?>" <?php echo isset($this->options[$post_type_desc]) ? 'checked' : '';?> /> 
					<label for="<?php echo $this->get_field_id( $post_type_desc ); ?>"><?php _e( $post_type_desc, 'nfgated' ); ?></label>
		            <br/>
		        </p>
				<?php 

			}

			?>
			<hr/><br/>
			<p>        
				<input type="checkbox" name="<?php echo $this->get_field_name( 'always_gated' ); ?>" id="<?php echo $this->get_field_id( 'always_gated' ); ?>" value="1" <?php echo isset($this->options['always_gated']) ? 'checked' : '';?> /> 
				<label for="<?php echo $this->get_field_id( 'always_gated' ); ?>"><?php _e( 'Always make these post types gated', 'nfgated' ); ?></label>
	            <br/>
	        </p>
	        <p style="max-width:800px;">
	        <i>If unchecked, the above selected post types will have an optional checkbox, to designate them as 'Gated'. If checked, the above post types will <b>always</b> by considered to be gated.</i>
	        </p>
			<?php 
		}

		function info_box() {
		
			?>
			<p>
				<strong><?php _e( 'Version:', 'nfgated' ); ?></strong> <?php echo NFGATED_VERSION; ?> <?php echo '&middot;'; ?> 
				<strong><?php _e( 'Released:', 'nfgated' ); ?></strong> <?php echo NFGATED_RELEASE_DATE; ?>
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
