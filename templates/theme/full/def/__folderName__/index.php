<?php
namespace __namespace__;

if( !isset( $Master ) ) extract( extr() );
// **EXTENDED** //
get_header();
	$HTML->s_div( '#primary' )
		->s_div( '#content|role=main' );

		if ( have_posts() ) {
			while ( have_posts() ) {
				the_post();
				get_template_part( 'content', get_post_format() );
			}
		} else {
			get_template_part( 'noresults' );
		}
	$HTML->end( '#primary' );
get_sidebar();
get_footer();
// **EXTENDED_END** //
?>