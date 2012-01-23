<?php
/*
Plugin Name: !THE MASTER
Plugin URI: http://plugins.red-thorn.de/libary/!themaster/
Description: A Plugin to provide global access to the THEWPMASTER class. THEWPMASTER provides a lot of handy functions for plugins an themes.
Version: 2.0.6
Date: 2012-01-23
Author: Hannes Diercks 
Author URI: http://red-thorn.de/
Update Server: http://plugins.red-thorn.de/api/index.php
*/

define('THEUPDATES_UPDATABLE_THEMASTER', '!themaster');
if(!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);

if(!defined('WPMASTERAVAILABE')) {
	require_once(dirname(__FILE__).DS.'classes'.DS.'wpmaster.php');
}
register_activation_hook(__FILE__, '_masterInstall');
function _masterInstall() {
	die('install');
	require_once(dirname(__FILE__).DS.'classes'.DS.'wpmaster.php');
	THEWPMASTER::_masterInstall();
}

?>
