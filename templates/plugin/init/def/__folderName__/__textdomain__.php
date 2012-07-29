<?php
/*
Plugin Name: __projectName__
*/
// Description: 
// Plugin URI: 
// Text Domain: __textdomain__
// Version: 
// Tested: 
// Requires: 
// Date: __currentTime__
// Author: __currentUser__
// Author URI: 
// **EXTENDED** //
// *optional*
// Update Server: 
// Required Plugins: 

// Please fill in additional Plugin information.

// **EXTENDED_END** //
if( !defined( 'WPMASTERAVAILABE' ) || WPMASTERAVAILABE != true ) {
	add_action( 'admin_notices', function() {
		echo '<div class="error"><p>Warning - The Plugin "__projectName__" could not be initiated because Plugin <a href="http://plugins.red-thorn.de/libary/!themaster/">!THE MASTER</a> is not available.</p></div>';
	});
} else {
	BUILDTHEMASTER(__tmminimal____tmtemplate__);
}
?>