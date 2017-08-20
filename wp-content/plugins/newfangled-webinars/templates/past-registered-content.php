<?php
/**
 * This is the content that displays on a Past Webinar post, AFTER the 
 * user has completed the form
 * 
 * To customize, move to your theme folder, and name 'nfwebinars-past-registered-content.php'
*/

//  Get and process the transcript content
$past_webinar_transcript = get_post_meta( $post->ID, 'past_webinar_transcript', true );
$past_webinar_transcript = wpautop( htmlspecialchars_decode($past_webinar_transcript) );

//  Get and process the post-registration content
$webinar_past_text = get_post_meta( get_the_id(), 'webinar_past_text', true );
$webinar_past_text = htmlspecialchars_decode($webinar_past_text);

//  Display the post-form content
echo '<div class="webinar-past-registered-text">' . $webinar_past_text . '</div>';

//  Display the transcript content
if ($past_webinar_transcript) {
    echo '<br/><div class="webinar-past-transcript">' . $past_webinar_transcript . '</div>';
}
