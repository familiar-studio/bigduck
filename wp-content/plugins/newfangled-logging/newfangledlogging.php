<?php
/**
 * Newfangled Logging
 *
 * @package   Newfangled Logging
 * @author    Newfangled
 * @link      http://newfangled.com/plugin-support-policy
 * @copyright Newfangled 2016
 */

//*********************************************************************************************************
/*
Plugin Name: Newfangled Logging
Plugin URI: http://newfangled.com/plugin-support-policy
Description: Integrate error logging with Sentry. Note: this plugin is required for all other Newfangled plugins
Version: 2.0.2
Author: Newfangled
Author URI: http://newfangled.com
Copyright: Newfangled 2016
*/
//*********************************************************************************************************

//	Don't load directly
if (!function_exists('is_admin')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

//	Plugin constants
define( 'NFLOGGING_VERSION', 			'2.0.2' );
define( 'NFLOGGING_RELEASE_DATE', 		date_i18n( 'F j, Y', '1485984913' ) );
define( 'NFLOGGING_DIR', 				plugin_dir_path( __FILE__ ) );
define( 'NFLOGGING_URL', 				plugin_dir_url( __FILE__ ) );

define( 'VERIFY_PASSED', 1 );
define( 'VERIFY_FAILED', 2 );

//	Provide plugin updates
require 'plugin_update_check.php';
$MyUpdateChecker = new PluginUpdateChecker_2_0 (
    'https://kernl.us/api/v1/updates/58924da5d253d976c4380eff/',
    __FILE__,
    'nflogging',
    1
);

//	Define the plugin class
if (!class_exists("NFLogging")) {

	/**
	 *
	 * Class: NFLogging
	 * 
	 */
	class NFLogging {
		
		var $client;
		var $key = 'https://fbd6ec4c4c1d4abeb33c9b368bb918a1:2a3b739da2eb4955a3e2e88c6258fd0b@app.getsentry.com/74751';
		var $settings;

		private static $_this;
		
		static function this() {
			return self::$_this;
		}
		
		function __construct() {    

			self::$_this = $this;
			add_action( 'init', array($this,'init') );
		}

		function init() {

			$this->load_settings();		

		}

		/**
		 * Load the plugin settings
		 */
		function load_settings() {

			if (!$this->settings && !class_exists("NFLogging_Settings")) { 
			
				require(NFLOGGING_DIR . 'newfangledlogging-settings.php');

				$this->settings = new NFLogging_Settings();	
				$this->options  = $this->settings->options;
	            $this->plugin_key = $this->options['plugin_key'];
	
				return $this->options;
			}
		}

		/**
		 * A simple error logging utility
		 * 
		 * @param  string $msg
		 * @param  array $parms
		 * @param  array $vars
		 */
		function logError( $msg, $params=Array(), $vars=Array() ) {
			
			if ( isset( $this->options['enable_logging'] ) )  {

				if (isset( $this->options['logging_key'] ) ) {
					$this->key = $this->options['logging_key'];
				}

				//	Include the Raven library, a wrapper for Sentry connectivity
				require_once dirname(__FILE__) . '/raven/lib/Raven/Autoloader.php';
				Raven_Autoloader::register();

				//	Has the Sentry client been loaded yet?
				if ($this->client == null) {

					//	No, load it
					$this->client = new Raven_Client($this->key);
				}				
				
				//	Did it load successfully?
				if ($this->client) {

					//	Send the error
					$event_id = $this->client->getIdent($this->client->captureMessage($msg, null, null, true, null));	
				}
			}
		}

		/**
		 * Make sure we have a valid licence key
		 * 
		 */
		function verifyLogging( $plugin_name='' ) {

			if ($verifystatus = get_transient('nfplugins_verifystatus')) {

		        return $verifystatus;
			}
			
			$verifystatus = VERIFY_PASSED;

			$this_options = get_option( 'nflogging_options' );

			if (!$plugin_key = $this_options['plugin_key']) {
				
				$verifystatus = VERIFY_FAILED;

			} else {

				$domain = get_site_url();

				$ch = curl_init( "https://insight-engine.newfangled.com/api/v1/validate?token=" . $plugin_key . '&domain=' . $domain );
	            curl_setopt($ch, CURLOPT_HEADER,            1);
	            curl_setopt($ch, CURLOPT_RETURNTRANSFER,    true);
	            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,    10);
	            curl_setopt($ch, CURLOPT_TIMEOUT,           10);
	            curl_setopt($ch, CURLOPT_VERBOSE,           0);       

	                                
	            $output = curl_exec($ch);
	            $info = curl_getinfo($ch);
	            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	            curl_close($ch);

	            if ($httpcode == 403) {
					$verifystatus = VERIFY_FAILED;
	            }
	        }

	        if ($verifystatus == VERIFY_FAILED) {

	        	$verifycount = get_transient( 'nfplugins_verifycount' );

	        	if (++$verifycount == 3) {

	        		set_transient( 'nfplugins_verifycount', 0 );


	        	} else {

	        		$verifystatus = VERIFY_PASSED;
	        		set_transient( 'nfplugins_verifycount', $verifycount );

	        	}

	        	$this->logError( 'Plugin verification failure #' . $verifycount . ' - ' . $domain . ' - ' . $plugin_key . ' - ' . $verifycount );


	        } else {

	        	set_transient( 'nfplugins_verifycount', 0 );

	        }

	        set_transient( 'nfplugins_verifystatus', $verifystatus, 600 );
	        
		    return $verifystatus;

		}
	} 
}

//  Initialize the plugin
global $nflogging;
if (class_exists("NFLogging") && !$nflogging) {
	$nflogging = new NFLogging();   
}