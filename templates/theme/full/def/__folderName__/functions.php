<?php
/**
 * Functions file for __projectName__
 *
 * @category: Wordpress Theme
 * @package: __namespace__
 * @author: __author__
 * @license: __license__
 * @link: __projectURI__
 */

namespace __namespace__;

use Xiphe\THEMASTER as TM;

if (!defined('WPMASTERAVAILABE') || WPMASTERAVAILABE != true) {
	add_action('admin_notices', function() {
		echo '<div class="error"><p>Warning - The Theme "__projectName__" could not be initiated because Plugin '
			.'<a href="https://github.com/Xiphe/-THE-MASTER">!THE MASTER</a> is not available.</p></div>';
	});
} else {
	TM\INIT( __FILE__ );
}

function extr() {
	if (!class_exists('__namespace__\classes\Master') || !is_array($r = classes\Master::extr())) {
		printf(
			__(
				'An Error occured: The current Theme could not be initiated. Please contact an %sAdministrator%s.',
				'__textdomain__'
			),
			'<a href="mailto:'.get_bloginfo('admin_email').'">',
			'</a>'
		);
		die();
	} else {
		return $r;
	}
}