<?php
/**
 * Newfangled Progressive Profiling
 *
 * @package   Newfangled Progressive Profiling
 * @author    Newfangled
 * @link      https://bitbucket.org/newfangled_web/newfangled-progressive-profiling
 * @copyright Newfangled 2016
 */

//*********************************************************************************************************
//
//	Newfangled Progressive Profiling 
//	Gravity Forms module
//
//*********************************************************************************************************
	array_push( $nfprofiling_moduleclasses, 'NfProfiling_PMPro' );

	class NfProfiling_PMPro extends NfProfiling_Module
	{
	//	var $modulename = 'Paid Memberships Pro';
    //    var $moduleid   = 'pmpro';

    //*********************************************************************************************************
    //  Function: init_listeners()
    //---------------------------------------------------------------------------------------------------------
    //  What it does: 
    //*********************************************************************************************************
        function init_listeners() {

            add_filter('pmpro_checkout_level',                  array($this,  "prepare_fields"), 10,    1 );
    //      add_filter("gform_pre_render",                      array($this,  "prepare_fields"),      10, 1 );
    //      add_action("gform_after_submission",                array($this,  "store_submission"),    10, 2 );        

        }

    //*********************************************************************************************************
    //  Function: define_fields()
    //---------------------------------------------------------------------------------------------------------
    //  What it does: 
    //*********************************************************************************************************
       function define_fields() {
			
            $this_fields = Array();

            $this_fields[ 'Registration' ][] = 'First Name';
            $this_fields[ 'Registration' ][] = 'Last Name';
            $this_fields[ 'Registration' ][] = 'Email';
            $this_fields[ 'Registration' ][] = 'Company';
            $this_fields[ 'Registration' ][] = 'Title';
            $this_fields[ 'Registration' ][] = 'Industry';

            return $this_fields;
        }

    //*********************************************************************************************************
    //  Function: prepare_fields()
    //---------------------------------------------------------------------------------------------------------
    //  What it does: 
    //*********************************************************************************************************
    	function prepare_fields( $pmpro_level ) {

            $profiling_fields = $this->get_profiling_fields();
            $user_profile     = $this->get_user_profile();

            if ($profiling_fields && $user_profile)
            {
                foreach( $user_profile as $name => $value )
                {
                    $_SESSION[ $name ] = $value; 
                }
            }

            global $bfirstname, $_REQUEST;
            $_REQUEST['bfirstname'] = $_SESSION['First Name'];
            $_REQUEST['blastname']  = $_SESSION['Last Name'];
            $_REQUEST['bemail']     = $_SESSION['Email'];

            return $pmpro_level;
        }

    //*********************************************************************************************************
    //  Function: validate_submission()
    //---------------------------------------------------------------------------------------------------------
    //  What it does: 
    //*********************************************************************************************************
        function validate_submission( $validation_result ) {

		      /*
        	$profiling_fields 		= $this->get_profiling_fields();
			$user_profile 			= $this->get_user_profile();
            $profiling_fields_count = 3;
  
            if ($form = $validation_result["form"])
            {
                foreach($form['fields'] as $idx => $field)
                {
                    if ( !array_key_exists($field['label'], $profiling_fields))
                    {
                        continue;
                    }

                    if ( $user_profile[ $field['label'] ] )
                    {
                        continue;
                    }

                    if (!$field_value = rgpost("input_{$field['id']}"))
                    {
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
                    /*

        }

    //*********************************************************************************************************
    //  Function: store_submission()
    //---------------------------------------------------------------------------------------------------------
    //  What it does: 
    //*********************************************************************************************************
        function store_submission($entry, $form){

                /*
            $profiling_fields 	  = $this->get_profiling_fields();
			$user_profile 		  = $this->get_user_profile();
            $user_email_value     = '';

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

                    if ( !array_key_exists( $field['label'], $profiling_fields))
                    {
                        continue;
                    }

                    if ($field_value = rgpost("input_{$field['id']}"))
                    {
                        $user_profile[ $field['label'] ] = $field_value;
                    }
                }
            }

            if ($user_profile && $user_email_value)
            {
                $uid = $this->moduleid . $form['id'];
                $user_profile['submissions'][ $uid ] = true;
                $this->update_profile($user_email_value, $user_profile );
            }
                /*

        }

    //*********************************************************************************************************
    //  Function: get_smartcta_forms()
    //---------------------------------------------------------------------------------------------------------
    //  What it does: 
    //*********************************************************************************************************
        function get_smartcta_forms(){

                /*
            $availble_forms = Array();

            $forms = RGFormsModel::get_forms();

            if ($forms)
            {
                foreach( $forms as $form_item )
                {
                    $form = RGFormsModel::get_form_meta($form_item->id);

                    if ($form['fields'])
                    {
                        $form_parms           = Array();
                        $form_parms['module'] = $this->moduleid;
                        $form_parms['moduledesc'] = $this->modulename;
                        $form_parms['id']     = $form_item->id;
                        $form_parms['name']   = $form['title'];
                        $availble_forms[]     = $form_parms;

                    }
                }
            }

            return $availble_forms;
            */
	    }

    //*********************************************************************************************************
    //  Function: render_form()
    //---------------------------------------------------------------------------------------------------------
    //  What it does: 
    //*********************************************************************************************************
       function render_form( $formid, $formname ){

        /*
            ?>
            <div class="sidebar-item sidebar-form">
            <h2><?php print $formname ?></h2>
            <?php
                
                gravity_form( $formid, false, false, false, null, true);

            ?>
            </div>
            <?php
        */

        }

    //*********************************************************************************************************
    //  Function: has_cta_been_submitted()
    //---------------------------------------------------------------------------------------------------------
    //  What it does: 
    //*********************************************************************************************************
       function has_cta_been_submitted( $formid ){

        /*
            if ( $user_profile = $this->get_user_profile() )
            {
                $uid = $this->moduleid . $formid;

                if (isset($user_profile['submissions'][ $uid ] ))
                {
                    return true;
                }
            }

            return false;
        */

        }
    }