<?php
/**
 * This is the content that displays on an Upcoming Webinar post, AFTER the 
 * user has completed the form
 * 
 * To customize, move to your theme folder, and name 'nfwebinars-upcoming-registered-content.php'
*/

//  Get and process the post-registration content
$webinar_upcoming_text = get_post_meta( get_the_id(), 'webinar_upcoming_text', true );
$webinar_upcoming_text = wpautop( htmlspecialchars_decode($webinar_upcoming_text) );

//  Display the post-registration content
echo '<div class="webinar-upcoming-registered-text">' . $webinar_upcoming_text . '</div>';