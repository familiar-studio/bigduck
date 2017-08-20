<?php
/**
 * This is the content that displays on a Past Webinar post, BEFORE the 
 * user has completed the form
 *
 * To customize, move to your theme folder, and name 'nfwebinars-past-form-content.php'
 */

global $nfwebinars;

//  Get the webinar values
$past_webinar_available = get_post_meta( $post->ID, 'past_webinar_available', true );
$past_webinar_not_available_message = get_post_meta( $post->ID, 'past_webinar_not_available_message', true );
$past_webinar_transcript = get_post_meta( $post->ID, 'past_webinar_transcript', true );
$past_webinar_transcript = wpautop( htmlspecialchars_decode($past_webinar_transcript) );
$enable_access_code =  get_post_meta( $post->ID, 'enable_access_code', true );
$access_code =  get_post_meta( $post->ID, 'access_code', true );
$access_code_message =  get_post_meta( $post->ID, 'access_code_message', true );

//  Build the template
echo '<div class="webinar-past-form-content">' . $content . '</div>';

if ($past_webinar_available) {

    if ($enable_access_code && $access_code) {

        ?>
        <div class="webinar-past-form-access-code">

            <p><?php print $access_code_message ?></p>

            <?php 

            if ($acccess_code_error = $nfwebinars->acccess_code_error) {

                echo '<div class="webinar-past-form-access-code-error">' . $acccess_code_error . '</div>';
            
            }

            ?>
            
            <form action="" method="POST">

                <input type="hidden" name="webinar-id" value="<?php print get_the_id(); ?>" />
            
                <input type="text" name="webinar-access-code" />
                
                <button type="submit">Submit</button>
    
            </form>

        </div>

        <?php

    }

    if (!$webinar_past_form_id = get_post_meta( get_the_id(), 'webinar_past_form_id', true )) {

        global $nfwebinars;

        $webinar_past_form_id = $nfwebinars->settings->default_pastform;
    }

    echo '<div class="webinar-past-form-form">';

    //  Include the form
    if ($nfwebinars && 
        isset( $nfwebinars->settings) && 
        isset( $nfwebinars->settings) && 
        TRUE == $nfwebinars->settings->options['disable_gf_ajax']) {

        echo '[gravityform id="' . $webinar_past_form_id . '" title="false" description="false" ajax="false"]';

    } else {

        //  Include the form with AJAX disabled
        echo '[gravityform id="' . $webinar_past_form_id . '" title="false" description="false" ajax="true"]';

    }

    echo '</div>';

    if ($past_webinar_transcript) {
    
        //  Include the form with AJAX enabled (default)
        echo '<br/><div class="webinar-past-transcript">' . $past_webinar_transcript . '</div>';
    
    }

} else {

    echo '<div class="webinar-past-form-content">' . $content . '</div>';
    
    echo '<br/>';
    
    echo '<div class="webinar-not-available"><p>' . $past_webinar_not_available_message . '</p></div>';

}