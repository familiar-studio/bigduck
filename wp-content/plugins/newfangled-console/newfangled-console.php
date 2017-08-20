<?php
/**
 * Newfangled Console
 *
 * @package   Newfangled Console
 * @author    Newfangled
 * @link      http://newfangled.com/plugin-support-policy
 * @copyright Newfangled 2016
 */

//*********************************************************************************************************
/*
Plugin Name: Newfangled Console
Plugin URI: http://newfangled.com/plugin-support-policy
Description: Integrated admin interface. Allows all back-end functionality to be accessed via a persistant, hidable overlay, which can be toggled via the 'esc' key.
Version: 2.1.1
Author: Newfangled
Author URI: http://newfangled.com
Text Domain: nfconsole
Domain Path: /languages
Copyright: Newfangled 2016
*/
//*********************************************************************************************************

//	Don't load directly
if (!function_exists('is_admin')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit();
}

//	Provide plugin updates
require 'plugin_update_check.php';
$MyUpdateChecker = new PluginUpdateChecker_2_0 (
    'https://kernl.us/api/v1/updates/58925405aff0b50f8e6ddc97/',
    __FILE__,
    'nfconsole',
    1
);

//	Plugin constants
define( 'NFCONSOLE_VERSION', '2.1.1' );
define( 'NFCONSOLE_RELEASE_DATE', date_i18n( 'F j, Y', '1493840579' ) );
define( 'NFCONSOLE_DIR', plugin_dir_path( __FILE__ ) );
define( 'NFCONSOLE_URL', plugin_dir_url( __FILE__ ) );


//	Define the plugin class
if (!class_exists("NFConsole")) {

	/**
	 *
	 * Class: NFConsole
	 * 
	 */
	class NFConsole {
		var $settings, $options_page;

		private static $_this;
		
		static function this() {
        	return self::$_this;
        }
		
		/**
		 * Add the handlers
		 */
		function __construct() {	

	        self::$_this = $this;


			add_action( 'init', 			array($this,'init'), 1 );
			add_action( 'admin_init', 		array($this,'admin_init') );
			add_action( 'wp_login', 		array($this,'logininit'), 10, 2);	
			add_action( 'plugins_loaded',   array($this,'load_textdomain' ));
            add_filter('pre_set_site_transient_update_plugins', array( $this, 'verifyLogging' ), 10, 1);

		}

		/**
		 * Load plugin textdomain.
		 */
		function load_textdomain() {
		  load_plugin_textdomain( 'nfconsole', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
		}

		/**
		 * Start the console
		 */
		function init() {

			
			if ( $this->console_enabled() ) {
				
				//	Require jQuery
				wp_enqueue_script( 'jquery' );

				//	Hide the admin bar
				add_filter( 'show_admin_bar', '__return_false' );

				//	Add the plugin scripts to the page
				add_action( 'wp_enqueue_scripts', array($this,'newfangled_console_init') );

				//	Include the console wrappers
				add_action('wp_footer', array( $this, 'add_footer_html' ), 100 );

				//	Hide the console elements, initially
				add_filter( 'body_class', array( $this, 'hide_console_elements_initially' ) );
			}

			if ( $this->editlinks_enabled() ) {

            	add_filter( "the_excerpt", array( $this,"prepend_edit_links" ), 999, 1);
            	add_filter( "the_content", array( $this,"prepend_edit_links" ), 999, 1);
				add_filter("gform_form_tag", array( $this, "gform_adminshortcut"), 10, 2);

			}
		}
		
		/**
		 * Start the admin views inside the console
		 */
		function admin_init() {

			if ( $this->console_enabled() )  {
				
				//	Add the plugin styles to the admin pages
				add_action( 'admin_enqueue_scripts', array( $this, 'newfangled_console_admin_style'		));
				add_action( 'admin_head', 			 array( $this, 'control_toolbar_display'			));
				add_filter( 'tiny_mce_before_init',  array( $this, 'my_tiny_mce_before_init' 			));
				add_action( 'admin_footer', 		 array( $this, 'add_admin_footer_html' 				));
				//add_action( 'redirect_post_location',array( $this, 'admin_post_saved' 					), 999 );
				add_action( 'wp_dashboard_setup', 	 array( $this, 'remove_dashboard_widgets' 			));
				add_action( 'admin_head', 	 		 array( $this, 'remove_admin_menu_from_workspace' 	));
			}
		}

		/**
		 * Workspace pages dont need the admin menu
		 * 
		 * @param  array
		 */
		function remove_admin_menu_from_workspace( $init ) {
		
			global $_GET, $menu;

			if ($_GET['console'] != 1 ) {
				$menu = Array();
			}
		}

		/**
		 * Add functionality to tinymce to allow on-page preview
		 *  
		 * @param  array $init
		 *
		 * @return array
		 */
		function my_tiny_mce_before_init( $init ) {
		    // $init['setup'] = "function( ed ) { ed.onKeyUp.add( function( ed, e ) { 
		    
		    // 	var tempContent = ed.getContent();
		    // 	window.parent.window.CMSConsole.setTempContent( tempContent );

		    // }); }";
		    // return $init;

		    return $init;
		}
		
		/**
		 * Load the plugin settings
		 * 
		 * @return object
		 */
		function load_settings() {

			if (!$this->settings && !class_exists("NFConsole_Settings")) { 
			
				require(NFCONSOLE_DIR . 'newfangled-console-settings.php');

				$this->settings = new NFConsole_Settings();	
				$this->options  = $this->settings->options;
				return $this->options;
			}
		}

		/**
		 * Determine if the console should be used
		 * 
		 * @return boolean
		 */
		function console_enabled() {

			if (isset( $this->console_enabled_flag )) {
				return $this->console_enabled_flag;
			}

			if (!current_user_can( 'manage_options' )) {
				$this->console_enabled_flag = false;
				return false;
			}

			$this->load_settings();

			if ( $this->options['enable_console'] )  {
				$this->console_enabled_flag = true;
				return true;
			}

			else {
				$this->console_enabled_flag = false;
				return false;
			}
		}

		/**
		 * Determine if we should auto-add edit links
		 * 
		 * @return boolean
		 */
		function editlinks_enabled() {

			if (!$this->console_enabled() ) {
				$this->editlinks_enabled_flag = false;
				return false;
			}
			
			if (isset( $this->editlinks_enabled_flag )) {
				return $this->editlinks_enabled_flag;
			}

			if (!current_user_can( 'manage_options' )) {
				$this->editlinks_enabled_flag = false;
				return false;
			}

			$this->load_settings();

			if ( $this->options['enable_edit_links'] )  {
				$this->editlinks_enabled_flag = true;
				return true;
			}

			else {
				$this->editlinks_enabled_flag = false;
				return false;
			}
		}

		/**
		 * On init, include the required css
		 */
			function newfangled_console_init() {

			wp_register_style( 'nfconsolecss', NFCONSOLE_URL . '/assets/css/consolemgr-init.css' );
			
			wp_enqueue_style(  'dashicons' );
			wp_enqueue_style(  'nfconsolecss' );

		}

		/**
		 * On init, add the console html to the page footer
		 */
		function add_footer_html() {

			$html  = '<div id="consoleToolbar" class="toolbar-collapsed" style="opacity: 100; display: block; margin-left: 0px;"><div class="toolbar-logo"></div><a href="' . wp_logout_url( home_url() ) . '" class="header-logout">';
			$html .= __( 'Sign Out', 'nfconsole' );
			$html .= '</a><div id="toolbar-header-right"><a class="icon" href="javascript:void(0);" onclick="top.CMSConsole.toggleConsole2(1);"></a></div><div id="iframe-outer"><iframe id="consoleToolbarIframe" name="consoleToolbarIframe" width="100%" height="100%" frameborder="0" onload="if (typeof(top.CMSConsole) != \'undefined\'){top.CMSConsole.toolbarOverrideCSS()}" allowtransparency="true" src="" style="opacity: 1;"></iframe></div></div>';
			$html .= '<div id="consoleWorkspace" style="width: 70%; display: none;"><div id="iframe-wrapper" style="width: 100%; height: 100%; opacity: 1;" "=""><iframe id="consoleWorkspaceIframe" name="consoleWorkspaceIframe" width="100%" onload="if (typeof(top.CMSConsole) != \'undefined\'){top.CMSConsole.workspaceOverrideCSS()}" height="100%" frameborder="0" allowtransparency="true" style="min-width:800px!important;" src=""></iframe></div><a onclick="top.CMSConsole.toggleConsole2(1);" id="consoleWorkspace-close"></a></div>';
			$html .= '<div id="consoleDimm" style="opacity: 0; display: none;"></div>';
			$html .= '<div id="console-edit-menu" style="display: none;"></div>';
			$html .= '<script src="' . NFCONSOLE_URL . '/assets/js/consolemgr.js"></script>';
			$html .= '<script>var nfConsoleRoot = \'' . site_url() . '\';</script>';

			print $html;
		}

		/**
		 * Add a class to the page body to hide the console elements on initial page load
		 * 
		 * @param  array $classes
		 *
		 * @return array
		 */
		function hide_console_elements_initially( $classes ) {
		
			//	Add 'class-name' to the $classes array
			$classes[] = 'console-invisible';
		
			//	Return the $classes array
			return $classes;
		}

		/**
		 * On init, if on an admin page, add the console html to the page footer
		 */
		function add_admin_footer_html() {


			if (isset( $_GET['editorState'] ) && 
				$_GET['editorState'] == 1)
			{
				$html  = '<script type="text/javascript">jQuery(document).ready(function() {top.CMSConsole.editorState = true })</script>';
				$html .= '<style>#local-storage-notice,#wpadminbar{ display: none!important; }#wpwrap #wpcontent{ padding-top: 0px!important; }</style>';
			}

			else
			{
				$html  = '<script type="text/javascript">jQuery(document).ready(function() {top.CMSConsole.editorState = false })</script>';
			}

			print $html;
		}

		/**
		 * Refresh the parent window if content was just saved
		 * 
		 * @param  int $post_id
		 */
		function admin_post_saved( $post_id ) {

			if (defined('DOING_AJAX') && DOING_AJAX) 
			{
				return;
			}

			if (isset($_SERVER['HTTP_REFERER']))
			{
				if (strpos($_SERVER['HTTP_REFERER'], '&editorState=1') !== false)
				{
					print '<script type="text/javascript">window.top.window.location.reload();</script>';
					exit;
				}
			}
		}
		
		/**
		 * Add some inline CSS to override some admin elements
		 */
		function control_toolbar_display(){

			global $__GET;
			
			if (isset($_GET['disable_console']) && $_GET['disable_console'] == true ) {
				return;
			}
			?>
			<script>
				if( typeof( window.top.CMSConsole ) == 'undefined' ) {
					window.top.location = '<?php print site_url() ?>';
				}

			</script>
			<?php
		
			echo '<style>
					#wpcontent
					{
						margin-left: 0px!important;
						padding-top: 32px!important;
						margin-right: 0px!important;
					}

					#adminmenuwrap,
					#adminmenuback,
					#wp-admin-bar-wp-logo,
					#wp-admin-bar-site-name
					{
						display: none!important;
					}

					#wpadminbar
					{
						background-color: #555!important;
						height: 32px!important;
						position: fixed!important;
						top: 0!important;
						left: 0!important;
						width: 100%!important;
						min-width: 600px!important;
						z-index: 99999!important;
					}

					html.wp-toolbar
					{
						padding-top: 0px!important;
					}

					#screen-meta-links,
					#screen-meta
					{
						margin-right: 86px;
					}

					#wpfooter {
						margin-left: 25px!important;
					}
			</style>';
		}

		/**
		 * On init, add some admin css and js
		 * 
		 * @return sting - inline css and js
		 */
		function newfangled_console_admin_style() {
		
			wp_register_style( 'nfconsolecss', 			NFCONSOLE_URL . '/assets/css/override-workspace.css' );
			wp_enqueue_style(  'nfconsolecss' );

			$html = '<script src="' . NFCONSOLE_URL . '/assets/js/override-links.js"></script>';
			print $html;
		}

		/**
		 * On login, redirect back to the site homepage
		 * 
		 * @param  object $user_login
		 * @param  object $user
		 */
		function logininit( $user_login, $user )
		{
			if( array_key_exists( 'administrator', $user->caps ) && 
				$this->console_enabled() )
		    {
		        wp_redirect( site_url( '/', 'http' ), 301 );
		        exit;
		    }
		}

		/**
		 * Remove the dashboard widgets
		 */
		function remove_dashboard_widgets () {

			global $wp_meta_boxes, $_GET;

			if ( isset( $_GET['console'] ) && intval( $_GET['console'] ) == 1 ) {
			    $wp_meta_boxes['dashboard'] = Array();
			}
		}

		/**
		 * Add the edit links when the_content() is called
		 * 
		 * @param  string $content - the original content
		 *
		 * @return string $content - the updated content
		 */
		function prepend_edit_links( $content ) {

		  	global $post;

		  	if ($edit_link_html = $this->edit_link()) {

				$content = $edit_link_html . $content;

			}
			
			return $content;
		}

		/**
		 * Add the 'edit' link, and the page builder link if available
		 */
		function edit_link( $main=false ) {
		  	
		  	global $post;

		  	$link_options = Array();
			$post_type_object = get_post_type_object( get_post_type() );

			if ($edit_link = get_edit_post_link())
			{

				$edit_link = str_replace( site_url(), '', $edit_link );

				$link_options[] = Array( 'class' => 'edit-option', 'desc' => 'Edit ' . $post_type_object->labels->singular_name, 'link' => $edit_link );
			    $link_options[] = Array( 'class' => 'edit-option', 'desc' => 'View All ' . $post_type_object->labels->name, 'link' => '/wp-admin/edit.php?post_type=' . get_post_type() );
		  		$link_options[] = Array( 'class' => 'edit-option', 'desc' => 'Add New ' . $post_type_object->labels->singular_name, 'link' => '/wp-admin/post-new.php?post_type=' . get_post_type() );
			}

			if ($link_options)
			{
				return '<p><div class="post-edit-link entry-edit" rel-options="' . urlencode( json_encode( $link_options ) ) . '"></div></p>';
			}
		}

		/**
		 * Add the edit link to gravity forms
		 */
		function gform_adminshortcut($form_tag, $form) {
			
			$new_form_tag  	  = '';
			$new_form_tag    .= $form_tag;

			$link_options     = Array();


			$link_options[] = Array( 'class' => 'edit-option', 'desc' => 'View Entries', 'link' => '/wp-admin/admin.php?page=gf_entries&id=' . $form['id'] );
			$link_options[] = Array( 'class' => 'edit-option', 'desc' => 'Form Settings',  'link' => '/wp-admin/admin.php?page=gf_edit_forms&view=settings&id=' . $form['id'] );
			$link_options[] = Array( 'class' => 'edit-option', 'desc' => 'Edit Form Fields', 'link' => '/wp-admin/admin.php?page=gf_edit_forms&id=' . $form['id'] );
			
			if ($link_options)
			{
				$new_form_tag .= '<p><div class="post-edit-link entry-edit form-admin-link" rel-options="' . urlencode( json_encode( $link_options ) ) . '"></div></p>';
			}


			return $new_form_tag;
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

//	Initialize the plugin
global $nfconsole;
if (class_exists("NFConsole") && !$nfconsole) {
    $nfconsole = new NFConsole();	
}
