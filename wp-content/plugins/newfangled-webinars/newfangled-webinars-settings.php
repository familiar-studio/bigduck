<?php
/**
 * Newfangled Webinars
 *
 * @package   Newfangled Webinars
 * @author    Newfangled
 * @link      https://bitbucket.org/newfangled_web/newfangled-webinars
 * @copyright Newfangled 2016
 */

//	Don't load directly
if (!function_exists('is_admin')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

//	Define the settings class
if (!class_exists("NFWebinars_Settings")) {
	
	class NFWebinars_Settings {

		//	Default settings
		public static $default_settings = array( 'include_types' => Array() );
		var $pagehook, $page_id, $settings_field, $options;

		function __construct( $parent=Array() ) {	
		
			$this->page_id 			= 'nfwebinars';
			$this->settings_field 	= 'nfwebinars_options';
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
						NfWebinars_Settings::$default_settings );
		

			add_action( 'admin_notices', array($this, 'verify_global_webinar_form') );

		}

		function admin_menu() {
			
			if ( ! current_user_can('update_plugins') ) {
				return;
			}
		
			//	Add a new submenu to the standard Settings panel
			$this->pagehook = $page =  add_options_page(	__('Newfangled Webinars', 
															'nfwebinars'), 
															__('Newfangled Webinars', 
															'nfwebinars'), 'administrator', $this->page_id, array($this,'render') );
			
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


			$title = __('Newfangled Webinars', 'nfwebinars');
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

			add_meta_box( 'nfwebinars-general', 
							__( 'General Settings', 'nfwebinars' ), 
							array( $this, 'general_box' ), 
							$this->pagehook, 
							'main' );

			add_meta_box( 'nfwebinars-types', 
							__( 'Include Types', 'nfwebinars' ), 
							array( $this, 'types_box' ), 
							$this->pagehook, 
							'main' );

			add_meta_box( 	'nfwebinars-advanced', 
							__( 'Advanced', 'nfwebinars' ), 
							array( $this, 'advanced_box' ), 
							$this->pagehook, 'main' );

			add_meta_box( 	'nfwebinars-version', 
							__( 'Information', 'nfwebinars' ), 
							array( $this, 'info_box' ), 
							$this->pagehook, 'main' );
		
		}

		function advanced_box() {
		
			?>
			<p>        
				<table>
					<tr>
						<td valign="top">
							<input type="checkbox" name="<?php echo $this->get_field_name( 'disable_autocontent' ); ?>" id="<?php echo $this->get_field_id( 'disable_autocontent' ); ?>" value="<?php print 'disable_autocontent' ?>" <?php echo isset($this->options['disable_autocontent']) ? 'checked' : '';?> /> 
						</td>
						<td valign="top">
							<label for="<?php echo $this->get_field_id( 'disable_autocontent' ); ?>">
								<?php _e( 'Manually Control Webinar Content Display', 'nfwebinars' ); ?>
						        <p style="max-width:800px;">			
								 <i>By default, making a post a webinar also provides you with two content areas, in which to enter the upcoming and past webinar content to appear after the forms are completed. This is the simplest way to use this plugin. 
								 Optionally, you can control manually what displays after the forms are completed, at the template level. This requires adding your own custom fields, and writing your own logic in your templates. Use the function:
								 	<ul>
								 		<li>• $nfwebinars->is_webinar_registered()
								 		<li>• $nfwebinars->is_webinar_upcoming()
								 	</ul>
								 	to determine if the current post should show the form, or not.</i>
								 </p>
								</label>
						</td>
					</tr>
				</table>
	           
	           
	        </p>


			<?php 
		}

		function general_box() {
		
			
    		?>
    		<p>
    		<i>Posts that are 'webinars' will display the main content on their details page, followed by the upcoming or past webinar registration form. Once this form is filled out, the details page will display the upcoming or past webinar content </i>
    		</p>
    		<hr/>
			<p>        
				<table>
					<tr>
						<td valign="top">
							<input type="checkbox" name="<?php echo $this->get_field_name( 'enable_webinars' ); ?>" id="<?php echo $this->get_field_id( 'enable_webinars' ); ?>" value="<?php print 'enable_webinars' ?>" <?php echo isset($this->options['enable_webinars']) ? 'checked' : '';?> /> 
						</td>
						<td valign="top">
							<label for="<?php echo $this->get_field_id( 'enable_webinars' ); ?>">
								<?php _e( 'Enable Webinars', 'enable_webinars' ); ?><br/>
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
								<?php _e( 'Use as Smart CTAs', 'nfwebinars' ); ?><br/>
								 <i>If enabled, each post flagged as 'Webinar' will appear in the list of Smart CTAs as a discrete item.</i>
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
							<input type="checkbox" name="<?php echo $this->get_field_name( 'acton_webinar' ); ?>" id="<?php echo $this->get_field_id( 'acton_webinar' ); ?>" value="<?php print 'acton_webinar' ?>" <?php echo isset($this->options['acton_webinar']) ? 'checked' : '';?> /> 
						</td>
						<td valign="top">
							<label for="<?php echo $this->get_field_id( 'acton_webinar' ); ?>">
								<?php _e( 'Act-On Webinar Integration', 'nfwebinars' ); ?><br/>
								 <i>If enabled, upcoming webinar registrations will use Act-On's built in webinar integration. </i>
								</label>
						</td>
					</tr>
				</table>
	            
	           
	        </p>
			<?php 


			?>
			<p>      
				<label for="<?php echo $this->get_field_id( 'default_pastform' ); ?>">
				<?php _e( 'Default Past Webinar Registration Form', 'nfwebinars' ); ?><br/>
				 <i>If selected, all posts marked as 'Webinar' will use this form for upcoming registrations. Otherwise, the form will need to be selected on a case-by-case basis.</i>
				</label><br/><br/>

				<select id="default_pastform" name="<?php echo $this->get_field_name( 'default_pastform' ); ?>" id="<?php echo $this->get_field_id( 'default_pastform' ); ?>"><option value=""></option>
				<?php

	            $forms = RGFormsModel::get_forms( null, 'title' );

	            foreach( $forms as $form )
	            {
	                if ($this->options['default_pastform'] == $form->id)
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
			<?php 

			?>
			<p>      
				<label for="<?php echo $this->get_field_id( 'default_upcomingform' ); ?>">
				<?php _e( 'Default Upcoming Webinar Registration Form', 'nfwebinars' ); ?><br/>
				 <i>If selected, all posts marked as 'Webinar' will use this form for upcoming registrations. Otherwise, the form will need to be selected on a case-by-case basis.</i>
				</label><br/><br/>

				<select id="default_upcomingform" name="<?php echo $this->get_field_name( 'default_upcomingform' ); ?>" id="<?php echo $this->get_field_id( 'default_upcomingform' ); ?>"><option value=""></option>
				<?php

	            $forms = RGFormsModel::get_forms( null, 'title' );

	            foreach( $forms as $form )
	            {
	                if ($this->options['default_upcomingform'] == $form->id)
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
            <label for="<?php echo $this->get_field_id('ajax_content_loading' ); ?>"><?php _e( 'Show Webinar Content via Ajax','nfgated' ); ?></label>

            <br/><br/>

            <i>If the site uses full-page caching (such as required by WP Engine) use this setting. Otherwise, leave disabled.</i>

            <br/><hr><br/>

            <input type="checkbox" name="<?php echo $this->get_field_name( 'disable_gf_ajax' ); ?>" id="<?php echo $this->get_field_id( 'disable_gf_ajax' ); ?>" value="1" <?php echo isset($this->options['disable_gf_ajax']) ? 'checked' : '';?> /> 
            <label for="<?php echo $this->get_field_id('disable_gf_ajax' ); ?>"><?php _e( 'Disable Gravity Forms Ajax','nfgated' ); ?></label>

            <br/><br/>

            <i>By default, the registration Gravity Forms will use Gravity Form's ajax functionality. Use this setting to disable it.</i>

			<?php 
		}

		function types_box() {
		
			if (!$post_types = get_post_types())
		    {
		        return;
		    }

			?>
			<i>Select the post types that can be flagged as a Webinar. These types will then contain the 'Webinar' meta box.</i>
			<?php

			foreach( $post_types as $post_type )
	    	{   
	    		$post_type_object   = get_post_type_object( $post_type );
	        	$post_type_desc     = $post_type_object->labels->name;

	    		?>
				<p>        
					<input type="checkbox" name="<?php echo $this->get_field_name( $post_type_desc ); ?>" id="<?php echo $this->get_field_id( $post_type_desc ); ?>" value="<?php print $post_type_desc ?>" <?php echo isset($this->options[$post_type_desc]) ? 'checked' : '';?> /> 
					<label for="<?php echo $this->get_field_id( $post_type_desc ); ?>"><?php _e( $post_type_desc, 'nfwebinars' ); ?></label>
		            <br/>
		        </p>
				<?php 

			}

			?>
			<hr/><br/>
			<p>        
				<input type="checkbox" name="<?php echo $this->get_field_name( 'always_webinar' ); ?>" id="<?php echo $this->get_field_id( 'always_webinar' ); ?>" value="1" <?php echo isset($this->options['always_webinar']) ? 'checked' : '';?> /> 
				<label for="<?php echo $this->get_field_id( 'always_webinar' ); ?>"><?php _e( 'Always make these post types a Webinar', 'nfwebinars' ); ?></label>
	            <br/>
	        </p>
	        <p style="max-width:800px;">
	        <i>If unchecked, the above selected post types will have an optional checkbox, to designate them as a webinar. If checked, the above post types will <b>always</b> by considered to be a webinar.</i>
	        </p>
			<?php 
		}

		function info_box() {
		
			?>
			<p>
				<strong><?php _e( 'Version:', 'nfwebinars' ); ?></strong> <?php echo NFWEBINARS_VERSION; ?> <?php echo '&middot;'; ?> 
				<strong><?php _e( 'Released:', 'nfwebinars' ); ?></strong> <?php echo NFWEBINARS_RELEASE_DATE; ?>
			</p>
			<p>
				For more information and support details, please view our <a href="https://www.newfangled.com/plugin-support-policy" target="_blank">plugin support policy</a>. 
			</p>
			<p>
				&copy; <?php date('Y') ?> <a href="https://www.newfangled.com" target="_blank">Newfangled.com</a>.
			</p>
			<?php
		
		}

		/**
		 * Make sure that any global forms selected contain 
		 * all of the nessisary hidden fields
		 */
		function verify_global_webinar_form() {
		    
		    //	Is there a default upcoming webinar form set?
			if (isset($this->options['default_upcomingform'])) {

				if ( $global_upcoming_form = $this->options['default_upcomingform'] ) {

					if ( $form = GFAPI::get_form( $global_upcoming_form ) ) {

						$has_correct_field = FALSE;

						//  For each submitted field
		                foreach( $form['fields'] as $field ) {
		                   
		                   if ($field['label'] == NFWEBINARS_FORM_ID_FIELD_NAME ) {
 
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
						        <p><?php _e( 'Webinar error.', 'nfwebinars'); ?></p>
						        <p><?php _e( 'The global upcoming webinar registration form, <i>' . $form['title'] . '</i>, is missing the required field ' . NFWEBINARS_FORM_ID_FIELD_NAME . ', with the following values:', 'nfwebinars'); ?></p>

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


			//	Is there a default past webinar form set?
			if (isset($this->options['default_pastform'])) {

				if ( $global_past_form = $this->options['default_pastform'] ) {

					if ( $form = GFAPI::get_form( $global_past_form ) ) {

						$has_correct_field = FALSE;

						//  For each submitted field
		                foreach( $form['fields'] as $field ) {
		                   
		                   if ($field['label'] == NFWEBINARS_FORM_ID_FIELD_NAME ) {
 
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
						        <p><?php _e( 'Webinar error.', 'nfwebinars'); ?></p>
						        <p><?php _e( 'The global past webinar registration form, <i>' . $form['title'] . '</i>, is missing the required field ' . NFWEBINARS_FORM_ID_FIELD_NAME . ', with the following values:', 'nfwebinars'); ?></p>

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

			//	Are we doing an Act-On integration for upcoming webinars?
			if (isset($this->options['acton_webinar']) && isset($this->options['default_upcomingform'])) {

				if ( $global_upcoming_form = $this->options['default_upcomingform'] ) {

					if ( $form = GFAPI::get_form( $global_upcoming_form ) ) {

						$has_correct_field = FALSE;

						//  For each submitted field
		                foreach( $form['fields'] as $field ) {
		                   
		                   if ($field['label'] == NFWEBINARS_ACTON_ID_FIELD_NAME ) {
 
		                   		if (	isset($field->inputName) && 
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
		}
	} 
}
