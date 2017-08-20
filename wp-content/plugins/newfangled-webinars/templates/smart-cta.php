<?php
/**
 *
 * If the gated content is being displayed as a Smart CTA, this
 * template is used
 *
 * To customize, move to your theme folder, and name 'nfgated-pre-form-content.php'
 * 
 */

global $nfwebinars;

$is_upcoming = $nfwebinars->is_webinar_upcoming( get_the_id() );

?>
<div class="widget-smartcta widget-smartcta-gatedcontent">
    <div class="heading">
        <h3><?php the_title() ?></h3>
    </div>
    <?php 

    if ( has_post_thumbnail( $_post->ID ) ) {
        echo '<a href="' . get_permalink( $_post->ID ) . '" title="' . esc_attr( $_post->post_title ) . '">';
        echo get_the_post_thumbnail( $_post->ID, 'thumbnail' );
        echo '</a>';
    }

    ?>
    <p>
        <?php echo strip_tags( $post->post_excerpt ); ?>
    </p>
    <?php

    echo '<div class="smartcta-link-wrapper">';
    echo '<a class="smartcta-link" href="' . get_permalink( $_post->ID ) . '" title="' . esc_attr( $_post->post_title ) . '">';
    
    if ($is_upcoming) {
        echo 'Register Now';
    } else {
        echo 'Watch Now';
    }

    echo '</a>';
    echo '</div>';
?>
</div>
