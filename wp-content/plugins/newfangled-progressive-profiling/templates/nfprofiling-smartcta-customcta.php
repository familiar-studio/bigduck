<div class="smartcta-customcta smartcta-customcta-<?php print get_the_ID() ?>">

	<h4 class="smartcta-customcta-title"><?php the_title(); ?></h4>

	<div class="smartcta-customcta-summary">

		<?php the_excerpt(); ?>

	</div>

	<div class="smartcta-customcta-form">

		<?php 
		
		if ($form_id = get_field( 'cta_form')) {

			$_GET['cta_id'] = get_the_ID();
			$_GET['cta_title'] = get_the_title();
			gravity_form( $form_id['id'], false, false, false, null, true );
		}
		?>

	</div>

</div>