<?php
namespace Xiphe\THEDEBUG;

use Xiphe as X;

/**
 * ADEBUG Class is a single debug.
 *
 * @copyright Copyright (c) 2012, Hannes Diercks
 * @author    Hannes Diercks <xiphe@gmx.de>
 * @link      https://github.com/Xiphe/THEDEBUG/
 * @package   THEDEBUG
 */
class ADEBUG extends X\Base {
	const DEBUG_TYPE_OK = 0;
	const DEBUG_TYPE_DEBUG = 1;
	const DEBUG_TYPE_INFO = 2;
	const DEBUG_TYPE_WARNING = 3;
	const DEBUG_TYPE_ERROR = 4;

	private static $_setableOptions = array(
		'name',
		'type',
		'backTraceOffset',
		'modus'
	);

	public $ID;
	public $variable;
	public $variableType;
	public $name = '';
	public $type = 'Debug';
	public $backTraceOffset;
	public $modus;
	public $file;
	public $line;
	public $backTrace;
	public $putOnDestruction = false;
	public $beforePut;

	/**
	 * Initiate a new Debug instance.
	 */
	public function __construct($args = array(''))
	{
		/* Ensure no instances can be crated without THEDEBUG being enabled */
		if (!X\THEDEBUG::isEnabled()) {
			throw new ADEBUGException("THEDEBUG is not enabled - You should not create ADEBUG object by yourself anyways.");
		}

		/* Check if debugs only should be put when the debug GET variable is set */
		if (X\THEDEBUG::$ensureByGet && !isset($_GET['debug'])) {
			return;
		}

		/* at first: get the back-trace so future functions wont manipulate it */
		$this->backTrace = debug_backtrace();

		/* Set the global modus */
		$this->modus = X\THEDEBUG::$modus;

		/* Get the global backTraceOffset */
		$this->backTraceOffset = X\THEDEBUG::$backTraceOffset;

		/* Set the passed arguments as instance variables */
		$this->_allocateArgs($args);

		/* Get the "called in" information by using the back-trace */
		$this->setLineAndFile();
	}

	/**
	 * Convert type string into type key
	 * 
	 * @param  string $type
	 * @return integer
	 */
	public function findTypeKey($type) {
		return array_search(ucfirst($type), X\THEDEBUG::$debugTypeMap);
	}

	/**
	 * Get the current type id
	 * 
	 * @return integer
	 */
	public function currentTypeKey() {
		return $this->findTypeKey($this->type);
	}

	/**
	 * Retrieve an option array and set valid keys as instance variables.
	 * 
	 * @param array $options
	 */
	public function setOptsByArray($options)
	{
		foreach (self::$_setableOptions as $optionKey) {
			if (isset($options[$optionKey])) {
				$this->$optionKey = $options[$optionKey];
			}
		}

		return $this;
	}

	/**
	 * Get the current function or method scope according to the backtraceOffset
	 *
	 * @return object
	 */
	public function getScope() {
		$calledHere = $this->backTrace[$this->backTraceOffset+1];

		if (isset($calledHere['class'])) {
			return (object) array(
				'type' => 'Method',
				'name' => $calledHere['class'].$calledHere['type'].$calledHere['function']
			);
		} elseif(isset($calledHere['function'])) {
			return (object) array(
				'type' => 'Function',
				'name' => $calledHere['function']
			);
		} else {
			return (object) array(
				'type' => 'global'
			);
		}
	}

	/**
	 * Generate a hash from the file and line
	 *
	 * @return string the id
	 */
	public function getID() {
		if (!isset($this->ID)) {
			$this->ID = md5($this->file.$this->line);
		}

		return $this->ID;
	}

	/**
	 * Use the backTraceOffset and find the file and line in which the debug was called
	 *
	 * @return  void
	 */
	public function setLineAndFile()
	{
		if (isset($this->backTrace[$this->backTraceOffset])) {
			/* Reset the ID because its based on the current line and file */
			$this->ID = null;

			$calledHere = $this->backTrace[$this->backTraceOffset];
			if (isset($calledHere['line'])) {
				$this->line = $calledHere['line'];
			}
			if (isset($calledHere['file'])) {
				$this->file = $calledHere['file'];
			}
		}

		return $this;
	}

	/**
	 * fire the appropriate output method for the current modus
	 * 
	 * @return void
	 */
	public function put() {
		switch ($this->modus) {
		case 'FirePHP':
			$this->putFirePhp();
			break;
		default:
			$this->putInline();
			break;
		}
	}

	/**
	 * Pass the debug to firePHP
	 * 
	 * @return void
	 */
	public function putFirePhp() {
		X\THEDEBUG::i()->doCallback('beforePut', array(&$this));
		$this->doCallback('beforePut', array(&$this));

		$FirePHP = X\THEDEBUG::getFirePHP();

		switch ($this->variableType) {
		case 'boolean':
			$this->variable = '(boolean) '.($this->variable ? 'true' : 'false');
			break;
		case 'NULL':
			$this->variable = '(null) NULL';
			break;
		case 'string':
			$this->variable = '"'.$this->variable.'"';
		default:
			break;
		}

		switch ($this->currentTypeKey()) {
		case 2:
			$method = 'info';
			break;
		case 3:
			$method = 'warn';
			break;
		case 4:
			$method = 'error';
			break;
		default:
			$method = 'log';
			break;
		}

		call_user_func_array(
			array($FirePHP, $method),
			array(
				$this->variable,
				$this->name,
				array(
					'File' => $this->file,
					'Line' => $this->line
		        )
		    )
		);
	}

	/**
	 * Just write the f**k into the browser!
	 * 
	 * @return void
	 */
	public function putInline() {
		X\THEDEBUG::i()->doCallback('beforePut', array(&$this));
		$this->doCallback('beforePut', array(&$this));

		echo '<div style="font-family:sans-serif;text-align:left;border:1px solid ';
		echo X\THEDEBUG::getColor($this->currentTypeKey(), 'b');
		echo ';background:';
		echo X\THEDEBUG::getColor($this->currentTypeKey(), 'bg');
		echo ';color:';
		echo X\THEDEBUG::getColor($this->currentTypeKey(), 'f');
		echo ';padding:10px;margin:20px;"><h3 style="font-size:30px;';
		echo 'text-transform:uppercase;float:left;margin: 0 10px 0 0;">';
		echo $this->type;
		echo '</h3><small style="position:relative;top:4px;font-size:11px;">';
		echo 'File: '.$this->file.' - Line <strong>'.$this->line.'</strong>';
		echo '<br />Type: '.$this->variableType.'</small><br style="display: inline; clear: both;" />';
		echo '<pre style="text-align:left;color:#000;background:rgba(255,255,255,0.2);';
		echo 'padding:5px;margin:5px 0 0;">';
		if (!empty($this->name)) {
			echo $this->name.': ';
		}
		var_dump($this->variable);
		echo '</pre></div>';
	}

	/**
	 * Check which arguments were passed and name them.
	 * 
	 * @param  array $arguments
	 * @return void
	 */
	private function _allocateArgs($arguments)
	{
		if (empty($arguments)) {
			return;
		}

		/* First argument is the variable to be debugged */
		$this->variable = $arguments[0];
		$this->variableType = gettype($this->variable);

		/* 
		 * The second and third argument can be either an integer representing
		 * the type, a string used as a message or an array containing multiple options
		 */
		for ($i=1; $i < 3; $i++) { 
			if (!isset($arguments[$i])) {
				return;
			}

			if (is_string($arguments[$i])) {
				$this->name = $arguments[$i];
			} elseif(is_int($arguments[$i])) {
				$this->type = X\THEDEBUG::$debugTypeMap[$arguments[$i]];
			} elseif(is_array($arguments[$i]) || is_object($arguments[$i])) {
				$this->setOptsByArray((array) $arguments[$i]);
			}
		}
		
		/* The fourth argument can be the back-trace offset */
		if (isset($arguments[3])) {
			if (is_int($arguments[3])) {
				$this->backTraceOffset = $arguments[3];
			}
		}
	}

	/**
	 * Deallocator
	 */
	public function __destruct() {
		if ($this->putOnDestruction) {
			$this->put();
		}
	}
}

class ADEBUGException extends \Exception {
};