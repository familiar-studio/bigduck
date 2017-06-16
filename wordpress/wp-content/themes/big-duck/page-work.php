<?php
/**
 * The main template file
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since   Timber 0.1
 */


$context = Timber::get_context();
$context['posts'] = Timber::get_posts();
$args = array(
  'post_type' => 'bd_case_study',
);
$context['case_studies'] = Timber::get_posts($args);
$args = array(
  'post_type' => 'bd_client',
);
$context['clients'] = Timber::get_posts($args);
$context['foo'] = 'baz';
$templates = array( 'page-work.twig' );
if ( is_home() ) {
	array_unshift( $templates, 'home.twig' );
}
Timber::render( $templates, $context );
