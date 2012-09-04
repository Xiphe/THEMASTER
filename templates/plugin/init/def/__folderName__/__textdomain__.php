<?php
/*
Plugin Name: __projectName__
*/
// Description: 
// Plugin URI: 
// Namespace: __namespace__
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
// Branch: 
// Please fill in additional Plugin information.

namespace __namespace__;

use Xiphe\THEMASTER as TM;

// **EXTENDED_END** //
if( !defined( 'WPMASTERAVAILABE' ) || WPMASTERAVAILABE != true ) {
	add_action( 'admin_notices', function() {
		echo '<div class="error"><p>Warning - The Plugin "__projectName__" could not be initiated because Plugin <a href="http://plugins.red-thorn.de/libary/!themaster/">!THE MASTER</a> is not available.</p></div>';
	});
} else {
	TM\BUILD(__tmminimal____tmtemplate__);
}
?>