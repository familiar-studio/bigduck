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
 * Newfangled Progressive Profiling
 * 
 * Base Module Class
 *
 * Modules provide an interface
 * to define the forms, fields, and smart CTAs
 * that make up the progressive profiling system.
*/
class NfProfiling_Module
{
	function __construct( $parent=Array() ) {

		$this->parent = $parent;

	}

	function get_profiling_fields(){

		$default_profiling_fields = Array();

		if ($profiling_fields = $this->parent->get_master_fields_list() )
		{
			return $profiling_fields;
		}

		else
		{
			return $default_profiling_fields;
		}
	}

	function get_ajax_load_setting()
	{
		return $this->parent->get_ajax_load_setting();
	}

	function get_profile_field_steps()
	{
		if (!isset($this->parent->settings->options['profile_field_steps'])) {
			return 3;
		}

		$result = $this->parent->settings->options['profile_field_steps'];

		if (is_numeric($result)) {
			return $result;
		}

		// Default
		return 3;
	}

	function get_auto_hide_fields()
	{
		if (!isset($this->parent->settings->options['auto_hide'])) {
			return false;
		}

		$result = $this->parent->settings->options['auto_hide'];

		if (trim($result) && $result == 'auto_hide') {
			return true;
		}

		// Default
		return false;
	}

	function get_user_profile_email(){

		if ($user_email = $this->parent->get_user_profile_email() )
		{
			return $user_email;
		}

		else
		{
			return '';
		}
	}

	function get_user_profile(){

		$default_user_profile = Array();

		if ($user_profile = $this->parent->get_user_profile() )
		{
			return $user_profile;
		}

		else
		{
			return $default_user_profile;
		}
	}

	function update_profile( $email, $profile ) {

		$this->parent->update_user_profile( $email, $profile );

	}

	function define_fields() {
		return Array();
	}

	function init_listeners() {
		return;
	}

	function prepare_fields( $form ) {
		return Array();
	}

	function validate_submission( $validation_result ) {
		return Array();
	}

	function store_submission( $entry, $form ) {
		return Array();
	}

	function get_smartcta_forms() {
		return Array();
	}

	function render_form( $formid, $formname ){
		return;
	}

	function has_cta_been_submitted( $formid ){
		return false;
	}
}