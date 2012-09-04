<?php
namespace __namespace__;

if( !isset( $Master ) ) extract( extr() );

	$HTML->s_article( '#post-0|.post no-results not-found' )
		->s_header( '.entry-header' )
			->h1( __( 'Nothing Found', '__textdomain__' ), '.entry-title' )
		->end()
		->s_div( '.entry-content' )
			->p( __( 'Apologies, but no results were found for the requested archive.'
					. ' Perhaps searching will help find a related post.', '__textdomain__' ) );
			get_search_form();
	$HTML->end( '#post-0' );
?>