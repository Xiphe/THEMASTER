<?php
namespace Xiphe;

use Xiphe\THEMASTER\core as core;
use Xiphe as X;

/**
 * THEDEBUG initiates ADEBUG instances.
 *
 * @copyright Copyright (c) 2012, Hannes Diercks
 * @author    Hannes Diercks <xiphe@gmx.de>
 * @link      https://github.com/Xiphe/THEDEBUG/
 * @package   THEDEBUG
 */
class THEDEBUG extends X\Base {

	/**
	 * Toggle to turn all debugs on and off.
	 * 
	 * @var boolean
	 */
	private static $_enabled = true;

	/**
	 * A global instance of FirePHP
	 * 
	 * @var FirePHP
	 */
	private static $_FirePHP;

	/**
	 * Hold debug objects that will count how often they were called
	 *
	 * @var array
	 */
	private static $counts = array();

	/**
	 * The global modus, can be overwritten by an ADEBUG object
	 * Accepts 'inline' or 'FirePHP' 
	 * 
	 * 'mail' and 'summed' were removed by 4.0
	 * 
	 * @var string
	 */
	public static $modus = 'inline';

	/**
	 * True requires the GET variable "debug" set in order to put debugs.
	 *
	 * @var boolean
	 */
	public static $ensureByGet = false;

	/**
	 * Global value for the backTraceOffset
	 *
	 * @var integer
	 */
	public static $backTraceOffset = 1;


	/**
	 * Colors for inline output
	 * 
	 * @var array
	 */
	public static $colors = array(
		array(
			'b' => '#3a3',
			'bg' => '#aea',
			'f' => '#050'
		), array(
			'b' => '#ccc',
			'bg' => '#fff',
			'f' => '#000'
		), array(
			'b' => '#0af',
			'bg' => '#cef',
			'f' => '#058'
		), array(
			'b' => '#eb0',
			'bg' => '#fe8',
			'f' => '#740'
		), array(
			'b' => '#f31',
			'bg' => '#faa',
			'f' => '#500'
		)
	);

	/**
	 * Maps the type keys to type strings
	 * @var array
	 */
	public static $debugTypeMap = array(
		'Ok',
		'Debug',
		'Info',
		'Warning',
		'Error'
	);

	protected $_singleton = true;

	/**
	 * Get the value of _enabled
	 * 
	 * @return boolean
	 */
	public static function isEnabled()
	{
		return self::$_enabled;
	}

	/**
	 * Enable THEDEBUG
	 *
	 * @return void
	 */
	public static function enable()
	{
		self::$_enabled = true;
	}

	/**
	 * Disable THEDEBUG
	 *
	 * @return void
	 */
	public static function disable()
	{
		self::$_enabled = false;
	}

	/**
	 * Get the color for inline debugs
	 * 
	 * @param  integer $nr   debug type
	 * @param  string  $type color-key
	 * @return string
	 */
	public static function getColor($nr, $type)
	{
		return self::$colors[$nr][$type];
	}

	/**
	 * Retrieve an instance of FirePHP
	 *
	 * @param array $options optional global options.
	 * @return FirePHP
	 */
	public static function getFirePHP($options = null)
	{
		if (!isset(self::$_FirePHP) || !empty($options)) {
			if (!class_exists('\FirePHP')) {
				throw new THEDEBUGException("FirePHP not available");
			}

			/* Start output buffer so we can debug after headers would be sent regular */
			if (!isset(self::$_FirePHP)) {
				ob_start();
			}
			
			if (!empty($options)) {
				\FB::setOptions($options);
			}

			self::$_FirePHP = \FirePHP::getInstance(true);
		}
		return self::$_FirePHP;		
	}

	/**
	 * Shorthand for getFirePHP
	 *
	 * @param array $options optional global options.
	 * @return FirePHP
	 */
	public static function FB($options = null)
	{
		return self::getFirePHP($options);
	}

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
	public static function debug($variable)
	{
		if (!self::$_enabled) {
			return;
		}

		if (self::$ensureByGet && !isset($_GET['debug'])) {
			return;
		}

		$debug = new THEDEBUG\ADEBUG(func_get_args());
		$debug->put();

		return $variable;
	}

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
	public static function diebug()
	{
		if (!self::$_enabled) {
			return;
		}

		if (self::$ensureByGet && !isset($_GET['debug'])) {
			return;
		}

		$args = func_get_args();

		$debug = new THEDEBUG\ADEBUG($args);

		/* don't debug if no arguments were passed */
		if (!empty($args)) {
			$debug->put();
		}
		
		/* Add a message so diebug can be called without arguments and still be found in the code */		
		$debug->variable = 'Script got murdered by diebug.';
		$debug->variableType = 'string';
		$debug->name = '';
		$debug->type = 'Info';
		$debug->put();

		/* DIE! */
		die();
	}

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
	public static function getDebugObject($variable)
	{
		if (!self::$_enabled) {
			return;
		}

		if (self::$ensureByGet && !isset($_GET['debug'])) {
			return;
		}

		return new THEDEBUG\ADEBUG(func_get_args());
	}

	/**
	 * Flag a function as deprecated by placing this method inside it.
	 *
	 * @param string  $alternative an alternative function that should be used instead.
	 * @param boolean $continue    true to die
	 *
	 * @return void
	 */
	public static function deprecate($alternative = '', $continue = true)
	{
		if (!self::$_enabled) {
			return;
		}

		if (self::$ensureByGet && !isset($_GET['debug'])) {
			return;
		}

		$debug = new THEDEBUG\ADEBUG();

		/* Get the deprecated method/function name */
		$scope = $debug->getScope();

		/* You can not deprecate the global namespace */
		if ($scope->type === 'global') {
			return;
		}

		/* Write a nice message */
		$message = sprintf(
			'Deprecated usage of %s "%s".',
			$scope->type,
			$scope->name
		);

		/* Add alternative hint if given */
		if (!empty($alternative)) {
			$message .= sprintf(
				' Please consider using "%s" instead.',
				$alternative
			);
		}

		/* Manipulate the offset so the debug will point to the deprecated method call */
		$debug->backTraceOffset++;
		$debug->setLineAndFile();

		/* Set the message, type and put */
		$debug->variable = $message;
		$debug->type = 'warning';
		$debug->variableType = 'string';
		$debug->put();

		if ($debug->modus === 'FirePHP') {
			self::$_FirePHP->trace('');
		}

		/* Die if continue is false */
		if (!$continue) {
			die();
		}
	}

	/**
	 * Count how often this line is visited and put the debug at the end of the script.
	 *
	 * @param string $message optional message
	 *
	 * @return void
	 */
	public static function count($message = '') {
		if (!self::$_enabled) {
			return;
		}

		if (self::$ensureByGet && !isset($_GET['debug'])) {
			return;
		}

		/* Get a new ADEBUG object */
		$debug = new THEDEBUG\ADEBUG(array(1));

		/* And its ID (calculated using file and line) */
		$ID = $debug->getID();

		/*
		 * just increment the counter and return if this is		
		 * not the first time called from this file and line,
		 */
		if (isset(self::$counts[$ID])) {
			unset($debug);
			self::$counts[$ID]->variable++;
			return;
		}

		/* Register output on destruction */
		$debug->putOnDestruction = true;

		/* Generate the message or insert the passed one */
		if (empty($message)) {
			$debug->name = sprintf(
				'File %s, Line %s was visited',
				$debug->file,
				$debug->line
			);
		} else {
			$debug->name = $message;
		}

		/* Register callback to add time/s to the counter */
		$debug->addCallback('beforePut', function (&$debug) {
			if ($debug->variable === 1) {
				$debug->variable .= ' time.';
			} else {
				$debug->variable .= ' times.';
			}
		});

		/* Add the debug to the collection of counts */
		self::$counts[$ID] = $debug;
	}
}

class THEDEBUGException extends \Exception {
}