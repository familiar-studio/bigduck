<?php
/**
 * This is the content that displays on a Gated Content post, BEFORE the 
 * user has completed the form
 *
  * To customize, move to your theme folder, and name 'nfgated-pre-form-content.php'
*/

global $nfgated;

//  Get the gated content form ID
$gated_content_form_id = get_post_meta( get_the_id(), 'gated_content_form_id', true );

//  Show the pre-form content
echo $content;

//  Wrap the form
echo '|'.$gated_content_form_id;

//  Include the form shortcode
// if ($nfgated && 
//     isset( $nfgated->settings) && 
//     isset( $nfgated->settings) && 
//     TRUE == $nfgated->settings->options['disable_gf_ajax']) {

//     echo '[gravityform id="' . $gated_content_form_id . '" title="false" description="false" ajax="false"]';


// } else {

//     echo '[gravityform id="' . $gated_content_form_id . '" title="false" description="false" ajax="true"]';

// }

//  End the form wrapper
//echo '</div>';