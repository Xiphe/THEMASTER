<?php
namespace __namespace__;

use Xiphe\THEMASTER as TM;

if( !defined( 'WPMASTERAVAILABE' ) || WPMASTERAVAILABE != true ) {
	add_action( 'admin_notices', function() {
		echo '<div class="error"><p>Warning - The Theme "__projectName__" could not be initiated because Plugin <a href="http://plugins.red-thorn.de/libary/!themaster/">!THE MASTER</a> is not available.</p></div>';
	});
} else {
	TM\INIT( __FILE__ );
}

function extr() {
	if( !class_exists('__namespace__\Master') ) {
		printf( __( 'An Error occured: The current Theme could not be initiated. Please contact an %sAdministrator%s.', 'themaster' ),
			'<a href="mailto:' . get_bloginfo( 'admin_email' ) . '">',
			'</a>'
		);
		die();
	} else {
		return Master::extr();
	}
}
?>