<?php 
/**
 * Global Namespace debug functions
 *
 * @copyright Copyright (c) 2013, Hannes Diercks
 * @author    Hannes Diercks <xiphe@gmx.de>
 * @version   3.0.2
 * @link      https://github.com/Xiphe/-THE-MASTER/
 * @package   !THE MASTER
 */

use Xiphe as X;

if (!function_exists('debug')) {
	function debug() {
		X\THEDEBUG::_set_btDeepth(7);
		call_user_func_array(array('Xiphe\THEDEBUG', 'debug'), func_get_args());
		X\THEDEBUG::_reset_btDeepth();
	}
}
if (!function_exists('diebug')) {
	function diebug() {
		X\THEDEBUG::_set_btDeepth(7);
		call_user_func_array(array('Xiphe\THEDEBUG', 'diebug'), func_get_args());
	}
}
if (!function_exists('rebug')) {
	function rebug() {
		X\THEDEBUG::_set_btDeepth(7);
		$r = call_user_func_array(array('Xiphe\THEDEBUG', 'rebug'), func_get_args());
		X\THEDEBUG::_reset_btDeepth();
		return $r;
	}
}
if (!function_exists('countbug')) {
	function countbug() {
		X\THEDEBUG::_set_btDeepth(7);
		call_user_func_array(array('Xiphe\THEDEBUG', 'countbug'), func_get_args());
		X\THEDEBUG::_reset_btDeepth();
	}
}

if (!function_exists('deprecated')) {
	function deprecated($alternative, $contunue = true, $bto = 0) {
		X\THEDEBUG::_set_btDeepth(7);
		$bto = $bto+2;
		return call_user_func_array(
			array('Xiphe\THEDEBUG', 'deprecated'),
			array($alternative, $contunue, $bto)
		);
		X\THEDEBUG::_reset_btDeepth();
	}
}