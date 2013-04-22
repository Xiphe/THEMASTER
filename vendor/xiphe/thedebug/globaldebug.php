<?php
if (!function_exists('debug')) {
	/**
	 * Create an ADEBUG object and put it.
	 * 
	 * @param  mixed  $variable the variable you want to debug.
	 * @param  mixed  			optional string for name/message, integer for type or array containing options
	 * @param  mixed            optional same as above
	 * @param  integer          backTraceOffset
	 * 
	 * @return mixed  return the passed variable for later usage.
	 */
	function debug() {
		Xiphe\THEDEBUG::$backTraceOffset += 2;
		$r = call_user_func_array(array('Xiphe\THEDEBUG', 'debug'), func_get_args());
		Xiphe\THEDEBUG::$backTraceOffset -= 2;
		return $r;
	}
}

if (!function_exists('diebug')) {
	/**
	 * The "Killer", debug the passed variable die!
	 *
	 * @param  mixed  $variable optional the variable you want to debug.
	 * @param  mixed  			optional string for name/message, integer for type or array containing options
	 * @param  mixed            optional same as above
	 * @param  integer          backTraceOffset
	 * 
	 * @return void
	 */
	function diebug() {
		Xiphe\THEDEBUG::$backTraceOffset += 2;
		call_user_func_array(array('Xiphe\THEDEBUG', 'diebug'), func_get_args());
	}
}

if (!function_exists('getDebugObject')) {
	/**
	 * Just return the ADEBUG instance for later usage.
	 *
	 * @param mixed $variable
	 * @param  mixed  			optional string for name/message, integer for type or array containing options
	 * @param  mixed            optional same as above
	 * @param  integer          backTraceOffset
	 *
	 * @return ADEBUG
	 */
	function getDebugObject() {
		Xiphe\THEDEBUG::$backTraceOffset += 2;
		$r =  call_user_func_array(array('Xiphe\THEDEBUG', 'getDebugObject'), func_get_args());
		Xiphe\THEDEBUG::$backTraceOffset -= 2;
		return $r;
	}
}

if (!function_exists('deprecate')) {
	/**
	 * Flag a function as deprecated by placing this method inside it.
	 *
	 * @param string  $alternative an alternative function that should be used instead.
	 * @param boolean $continue    true to die
	 *
	 * @return void
	 */
	function deprecate() {
		Xiphe\THEDEBUG::$backTraceOffset += 2;
		$r =  call_user_func_array(array('Xiphe\THEDEBUG', 'deprecate'), func_get_args());
		Xiphe\THEDEBUG::$backTraceOffset -= 2;
		return $r;
	}
}

if (!function_exists('countbug')) {
	/**
	 * Count how often this line is visited and put the debug at the end of the script.
	 *
	 * @param string $message optional message
	 *
	 * @return void
	 */
	function countbug() {
		Xiphe\THEDEBUG::$backTraceOffset += 2;
		$r =  call_user_func_array(array('Xiphe\THEDEBUG', 'count'), func_get_args());
		Xiphe\THEDEBUG::$backTraceOffset -= 2;
		return $r;
	}
}