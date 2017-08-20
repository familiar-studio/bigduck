<?php
/**
 * This is the content that displays on an Upcoming Webinar post, BEFORE the 
 * user has completed the form
 *
* To customize, move to your theme folder, and name 'nfwebinars-upcoming-form-content.php'
*/

global $nfwebinars;

//  Get the upcoming form ID
$webinar_upcoming_form_id = get_post_meta( get_the_id(), 'webinar_upcoming_form_id', true );

//  Get the webinar date/time
$webinar_date  = get_post_meta( get_the_id(), 'webinar_date', true );

//  Is there a webinar acton id?
if ( $acton_id  = get_post_meta( get_the_id(), 'webinar_upcoming_acton_id', true ) ) {

    //  Yes, make it available for the 
    //  gravity form to use
    $_GET['acton_id'] = $acton_id;
}

//  Store the previous PHP timezone
$previous_timezone = date_default_timezone_get();

//  Get the WP timezone 
$wp_timezone = $this->get_wp_timezone();

//  Set the WP timezone as the PHP timezone
date_default_timezone_set($wp_timezone);

//  Show the Webinar Date
echo '<h4 class="webinar-upcoming-form-date">Webinar Date: ' . date('F j, Y, g:i a T', strtotime($webinar_date) ) . '</h4>';

//  Show the pre-form content
echo '<div class="webinar-upcoming-form-content">' . $content . '</div>';

//  Wrap the form
echo '<div class="webinar-upcoming-form-form">';

//  Include the form
if ($nfwebinars && 
    isset( $nfwebinars->settings) && 
    isset( $nfwebinars->settings) && 
    TRUE == $nfwebinars->settings->options['disable_gf_ajax']) {

    //  Include the form with AJAX disabled
    echo '[gravityform id="' . $webinar_upcoming_form_id . '" title="false" description="false" ajax="false"]';

} else {
    
    //  Include the form with AJAX enabled (default)
    echo '[gravityform id="' . $webinar_upcoming_form_id . '" title="false" description="false" ajax="true"]';
}

//  End the form wrapper
echo '</div>';

//  Restore the previous PHP timezone
date_default_timezone_set( $previous_timezone );