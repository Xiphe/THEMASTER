<?php
/*
Plugin Name: !THE MASTER
Plugin URI: http://plugins.red-thorn.de/libary/!themaster/
Description: A Plugin to provide global access to the THEWPMASTER class. THEWPMASTER provides a lot of handy functions for plugins an themes.
Version: 2.2
Date: 2012-01-17
Author: Hannes Diercks 
Author URI: http://red-thorn.de/
Update Server: http://plugins.red-thorn.de/api/index.php
*/

define('THEUPDATES_UPDATABLE_THEMASTER', '!themaster');
if(!defined('DS'))
	define('DS', DIRECTORY_SEPARATOR);

if(!defined('WPMASTERAVAILABE')) {
	require_once(dirname(__FILE__).'/core/wpmaster.php');
}

function init_THEWPMASTER(Array $initArgs = null) {
	if(null === $initArgs) {
		echo '<p>Error: init_THEWPMASTER called without $initArgs.</p>';
		return false;
	} 
	try {
		$r = new THEWPMASTER($initArgs);
	} catch(Exception $e) {
		echo '<p>'.$e->getMessage().'<br />File:'.$e->getFile().' Line:'.$e->getLine().'</p>';
		$r = false;
	}
	return $r;
}
?>