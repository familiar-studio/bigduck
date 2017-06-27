<?php
/**
 * This is the content that displays on a Gated Content post, AFTER the 
 * user has completed the form
 * 
 * To customize, move to your theme folder, and name 'nfgated-post-form-content.php'
*/

//  Get and process the post-registration content
$gated_content_post_form_text = get_post_meta( get_the_id(), 'gated_content_post_form_text', true );
$gated_content_post_form_text = wpautop( htmlspecialchars_decode($gated_content_post_form_text) );

//  Display the post-registration content
echo '<h1>Hello World</h1><div class="gated-content-registered-text">' . $gated_content_post_form_text . '</div>';