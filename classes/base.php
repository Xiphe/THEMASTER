<?php
// Version 2.0.12
require_once('model.php');
class THEBASE {
	// Gets True if THEMASTER is initiated
	public $initiated = FALSE;
	
	// The Args needed by the Object to get initiated
	private $_requiredInitArgs = array();
	protected $requiredInitArgs = array();
	
	protected $_mastersInitArgs = array();
	private $_initArgs = array();
	
	// This is the Place of all Singleton Instances
	protected static $_singletons = array(); 
	
	// Temporary Globales Used by: 
	private $_temp_globals = array();
	
	public static $registeredSources = array();
	public static $registeredJsVars = array();
	public static $registeredAdminJsVars = array();
	
	/** The Constructor gets called by every subclass
	 *
	 * @param array $initArgs
	 * @return mixed returns false if a required init Arg is missing or Instance if Subclass is Singleton
	 * @access public
	 * @package base
	 * @date Nov 10th 2011
	 */
	function __construct($initArgs) {
		$this->_initArgs = $initArgs;
		if($this->_get_setting('errorReporting') === true) {
			error_reporting(E_ALL);
			ini_set('display_errors', 1);
		} else {
			error_reporting(0);
			ini_set('display_errors', 0);
		}
				
		if(!isset($initArgs['original']) || $initArgs['original'] !== true) {
			throw new Exception('__construct() method chain of THEMASTER overwritten.');
			return false;
		}
		
		/* check if Called Class is meant to be a Singleton (Subclass: static $singleton = true;)
		 * and if it is stored in THEMASTERs private $singletons array
		 */
		$cc = strtolower(get_called_class());
		if(isset(self::$_singletons[$cc]) && is_object(($r = self::$_singletons[$cc])) 
				&& isset($r->singleton) && $r->singleton === true)
			return $r;
		
		// cjeck for required Init args (defined by protected $requiredInitArgs = array();)
		
		$err = $this->get_requiredArgsError($initArgs, array_merge($this->_requiredInitArgs, $this->requiredInitArgs));
		if($err === false) {
			// no error -> Set Init args as Class Variables
			foreach ($initArgs as $key => $value) {
				$this->$key = $value;
			}
			
			// initiate the Object
			if(in_array(get_class($this), array('THEWPMASTER', 'THEMASTER', 'THEDEBUG', 'THEUPDATES', 'THESETTINGS')))
				$this->_masterInit($initArgs);
		} else {
			throw new Exception('Required args Error: '.$err.' in Class: '.get_class($this));
			return false;
		}
	}
	
	function _initArgs($initArgs) {
		$this->_requiredInitArgs = array_merge($this->_requiredInitArgs, $initArgs);
	}
	
	public function cleanupPath($path) {
		return preg_replace('/[\/\\\]+/', DS, $path);
	}
	
	/** Getter for Static Variables of Named Classes
	 *
	 * @param string $classname
	 * @param string $key
	 * @return mixed
	 * @date Nov 10th 2011
	 */
	function get_static_var($classname, $key) {
		$vars = get_class_vars($classname);
		return $vars[$key];
	}
	
	/** Getter for the Singleton Instance of Class
	 *
	 * @return mixed The instance or false
	 * @date Nov 10th 2011
	 */
	public static function inst() {
		$called = get_called_class();
		if(isset(self::$_singletons[strtolower($called)])
			&& is_object(($r = self::$_singletons[strtolower($called)])))
				return $r;
		elseif(isset(self::$_singletons['master']))
			return self::$_singletons['master'];
		return false;
	}
	
	/** The init - does nothing for subclasses und calls _masterInit if its called by THEMASTER
	 *
	 * @param array $initArgs
	 * @return void
	 * @access protected
	 * @date Jul 28th 2011
	 */
	protected function init($initArgs) {
		
	}
	
	// TODO: Documentation
	protected function _register_temp_global($key, $value) {
		$this->_temp_globals[$key] = $value;
	}
	protected function _set_temp_globals() {
		foreach ($this->_temp_globals as $key => $value) {
			$GLOBALS[$key] = $value;
		}
	}
	protected function _unset_temp_globals() {
		foreach ($this->_temp_globals as $key => $value) {
			if(isset($GLOBALS[$key]))
				unset($GLOBALS[$key]);
		}
	}
		
	/** Static function to Get Quick access to Master and HTML Class 
	 *  extract(XYMaster::extr());
	 *
	 * @return array
	 * @access public
	 * @date Jul 28th 2011
	 */
	public static function extr() {
		$called = get_called_class();
		if(
			isset(self::$_singletons[strtolower($called)])
			&& is_object(self::$_singletons[strtolower($called)])
		) {
			$inst = self::$_singletons[strtolower($called)];
		} elseif(class_exists($called) && ($inst = THEBASE::inst()))
			$inst = $inst->get_instance($called);
		else 
			return false;
		
		if(isset($inst->HTML))
			return array('HTML' => $inst->HTML, $called => $inst);
		else
			return array($called => $inst);
	}
	
	/** returns an array of files in a specific folder, excluding files starting with . or _
	 *
	 * @param string $dir the
	 * @param mixed $key option for the array key of each file number or filename possible, default: the filename
	 * @return array
	 * @access public
	 * @date Jul 29th 2011
	 */
	public function get_dir_array($dir, $key = 'filename') {
		if ($handle = opendir($dir)) {
			$r = array();
			$i = 0;
			while (false !== ($file = readdir($handle))) {
				if(!in_array(substr($file, 0, 1), array('.', '_'))) {
					$k = $key == 'filename' ? $file : $i;
					$r[$k] = $file;
					$i++;
				}
			}
			return $r;
	    }
	}
	
	/** Checks for an existing Session and Starts a new one if nothing is found
	 *
	 * @return void
	 * @date Nov 10th 2011
	 */
	public function session() {
		if(!isset($_SESSION))
			session_start();
		return $_SESSION;
	}
	
	/** Initiation for a new Instance of THEMASTER, generates a new Submaster XYMaster
	 *
	 * @param array $initArgs see $this->requiredInitArgs for required keys
	 * @return void
	 * @access private
	 * @date Jul 28th 2011
	 */
	protected function _masterInit($initArgs) {
		$this->session();
		
		$this->debug('_masterInit: '.get_called_class());
		
		$this->reg_less('base');
		$this->reg_admin_less('base');
		
		if(!isset(self::$_singletons['master']))
			self::$_singletons['master'] = $this;
		
		$this->_mastersInitArgs = $initArgs;
		
		// $name = strtoupper($this->prefix).'Master';
		
		// global $$name;
		$this->get_instance('Master');
	
		$this->initiated = TRUE;
	}
	
	protected function _storeSingleton($name, $obj) {
		
	}
	
	/** Checks if Array 1 has all required keys, specified by Array 2
	 * 
	 * @param Array $args
	 * @param Array $requiredArgs
	 * @return String/False Error string or False if no Error found
	 * @access protected
	 * @date Jul 29th 2011
	 */
	protected function get_requiredArgsError($args, $requiredArgs) {
		if(!is_array($args))
			return '$args is not an array';
		if(!is_array($requiredArgs))
			return '$required is not an array';
		foreach($requiredArgs as $req) {
			if(!isset($args[$req])) {
				return $req.' is required.';
			}
		}
		return FALSE;
	}
	
	/** wrapping function to directly echo a view
	 *
	 * @param String $view the view name
	 * @param Array/Empty $args optional args passed to the view
	 * @param mixed $temp catching param for wordpress calls
	 * @return void
	 * @access public
	 * @date Jul 29th 2011
	 */
	public function view($view, $args = null, $temp = null) {
		echo $this->get_view($view, $args, $temp);	
	}
		
	/** Trys to get a view File named example.php from 
	 * "views"-Subfolder of defined basePath and return its output
	 * automaticly searches in a subfolder with classname then in 
	 * root view folder. Other foldernames can be specified with 
	 * underscores folder_file - this will be taken at first.
	 * 
	 * @param String $view the view name
	 * @param Array/Empty $args optional args passed to the view
	 * @param mixed $temp catching param for wordpress calls
	 * @return String View String
	 * @access public
	 * @date Jul 29th 2011
	 */
	public function get_view($view, $args = array(), $temp = null) {
		try {
			/** sth. for compability with Wordpress functions TODO: get more specific **/
			if(is_object($view)) {
				$page = $view;
				$view = $args;
			}
			if(is_array($view)) {
				$temp = $view;
				$view = $temp['args'][0];
				unset($temp['args'][0]);
				$args = isset($temp['args']) ? $temp['args'] : array();
			}
			
			/** check for underscores and search for underfolders in view-folder **/
			$e = explode('_', $view); 
			switch(count($e)) {
				case 1:
					/** no underscores **/
					if(is_dir($this->basePath.'/views/'.strtolower(get_class($this))))
						/** folder with classname found **/
						$folder = strtolower(get_class($this)).'/';
					else
						$folder = '';
					$file = $e[0];
					break;
				case 2:
					/** underscore fund **/
					$folder = $e[0].'/';
					$file = $e[1];
					break;
				default:
					/** currently no support for multiple undersoces/folders TODO: enable deeper folder stacks **/
					$folder = 'FALSE';
					throw new Exception("Error: too Much '_' in $view", 1);
					break;
			}
			$file = strtolower($file);
			if(file_exists($this->basePath.'/views/'.$folder.$file.'.php')) {
				if(!is_array($args)) $args = array($args);
				$ar = array(get_class($this) => $this);
				if(($HTML = $this->get_HTML()))
					$ar['HTML'] = $HTML;
				
				extract(array_merge(
					$ar, 
					$args
				));
				ob_start();
					include($this->basePath.'/views/'.$folder.$file.'.php');
				return ob_get_clean();
			} else {
				throw new Exception('Error: View File not Found ('.$this->basePath.'/views/'.$folder.$file.'.php)', 1);
			}
		} catch(Exception $e) {
			$this->debug($e);
		}
	}

	/** getter for Own HTML Class, falls back on global HTML class 
	 *  if object does not have its own HTML class
	 *
	 * @param bool $silence switch for disabling the error throwing
	 * @return class HTML or null if $silence = true or false
	 * @access public
	 * @date Dez 14th 2011
	 * @since 2.0.12
	 */
	public function get_HTML($silence = false) {
		if(isset($this->HTML))
			return $this->HTML;
		else
			return self::sget_HTML($silence);
	}
		
	public static function sget_HTML($silence = false) {
		if(isset($GLOBALS['HTML']))
			return $GLOBALS['HTML'];
		elseif($silence !== true)
			throw new Exception("HTML Class Needed but not available.", 1);
		else
			return false;
	}
	
	public function reg_jsVar($name, $var) {
		if(!isset(self::$registeredJsVars[$name]))
			self::$registeredJsVars[$name] = $var;
	}
	
	public function reg_adminJsVar($name, $var) {
		if(!isset(self::$registeredAdminJsVars[$name]))
			self::$registeredAdminJsVars[$name] = $var;
	}
	
	public function reg_js($filename, $vars = false) {
		$this->_reg_source('js', $filename, $vars);
	}
	
	public function reg_less($filename, $vars = false) {
		$this->_reg_source('less', $filename, $vars);
	}
	
	public function reg_css($filename, $vars = false) {
		$this->_reg_source('css', $filename, $vars);
	}

	public function reg_admin_js($filename, $vars = false) {
		$this->_reg_source('js', $filename, $vars, true);
	}
	
	public function reg_admin_less($filename, $vars = false) {
		$this->_reg_source('less', $filename, $vars, true);
	}
	
	public function reg_admin_css($filename, $vars = false) {
		$this->_reg_source('css', $filename, $vars, true);
	}
	
	/** __documentation__
	 *
	 * @param __mixed__ $__foo__ __description__
	 * @return __void__ __description__
	 * @access private
	 * @date Dez 15th 2011
	 */
	private function _reg_source($source, $filename, $vars = false, $admin = false) {
		$lfn = strtolower($filename);
		if(isset(self::$registeredSources[($admin ? 'admin' : 'front')][$source][$lfn]))
			return true;
		
		try {
			$path = DS.'res'.DS.$source.DS.$lfn.'.'.$source;
			$path .= is_array($vars) ? '.php' : '';
			$basePath = $this->basePath;
			$file = $basePath.$path;
			$baseUrl = $this->baseUrl;
			
			$masterBasePath = dirname(dirname(__FILE__));
			$masterFile = $masterBasePath.$path;
			if(file_exists($masterFile)) {
				$basePath = $masterBasePath;
				$file = $masterFile;
				$baseUrl = plugins_url('!themaster');
			}
			
			
			if(file_exists($file)) {
				if($source == 'less') {
					require_once(dirname(__FILE__).DS.'lessPHP'.DS.'lessc.inc.php');
					self::$registeredSources['front'][$source][$lfn] = true;
					$lfn .= '.less';
					$path = DS.'res'.DS.'css'.DS.$lfn.'.css';
					try {
						lessc::ccompile($file, ($target = $basePath.$path));
					} catch(Exception $e) {
						$this->debug('LESS ERROR: '.$e->getMessage()." \nFile: ".$e->getFile()." \nLine: ".$e->getLine(), 4);
						return false;
					}
					$source = 'css';
				}
				
				$path = str_replace(DS, '/', $path);
				$url = $baseUrl.$path;
				$url .= is_array($vars) ? '?'.$this->arrayToGet($vars) : '';
				if($admin)
					self::$registeredSources['admin'][$source][$lfn] = $this->get_HTML()->escape_mBB($url);
				else
					self::$registeredSources['front'][$source][$lfn] = $this->get_HTML()->escape_mBB($url);
				return true;
			} else {
				throw new Exception(strtoupper($source).' File not found: '.$filename.' | '.$file);
				return false;
			}
		} catch(Exception $e) {
			$this->debug($e->getMessage()."\nFile: ".$e->getFile()."\nLine: ".$e->getLine(), 4);
			return false;
		}
	}
	
	public function echo_sources($admin = false) {
		if(empty(self::$registeredSources)) return;
		
		$sources = $admin == 'admin' ? self::$registeredSources['admin'] : self::$registeredSources['front'];
		unset($sources['less']);
		if(empty($sources)) return;

		$HTML = $this->get_HTML();
		foreach($sources as $type => $files) {
			foreach($files as $file => $url) {
				$HTML->$type($url);
			}
		}
	}
	
	public function echo_jsVars() {
		$HTML = $this->get_HTML();
		$HTML->sg_script();
		foreach(self::$registeredJsVars as $name => $var) {
			$HTML->blank('var '.$name.' = '.json_encode($var).';');
		}
		$HTML->end();
	}
	
	public function echo_AdmimJsVars() {
		$HTML = $this->get_HTML();
		$HTML->sg_script();
		foreach(self::$registeredAdminJsVars as $name => $var) {
			$HTML->blank('var '.$name.' = '.json_encode($var));
		}
		$HTML->end();
	}
	
	
	public function incl($sSource) {
		$sSource = $this->get_directPath($sSource);
		if(strstr($sSource, DS))
			$sSource .= '.php';
		else
			$sSource .= DS.$sSource.'.php';
		
		if(file_exists(($path = $this->basePath.DS.'res'.DS.'includes'.DS.$sSource))) {
			return $path;
		} elseif(file_exists(($path = dirname(dirname(__FILE__)).DS.'res'.DS.'includes'.DS.$sSource))) {
			return $path;
		}
		return false;
	}
	
	public function get_directPath($sPath) {
		return str_replace(array('..'.DS, '.'.DS), '', str_replace(array(
			'/_', '\\_', '/', '\\', 
		), DS, strtolower(preg_replace('/[^\w\_\.]+/', '', $sPath))));
	}
	
	// from: http://codeaid.net/php/convert-size-in-bytes-to-a-human-readable-format-%28php%29
	function bytesToSize($bytes, $precision = 2) {  
	    $kilobyte = 1024;
	    $megabyte = $kilobyte * 1024;
	    $gigabyte = $megabyte * 1024;
	    $terabyte = $gigabyte * 1024;
	   
	    if (($bytes >= 0) && ($bytes < $kilobyte)) {
	        return $bytes . ' B';
	    } elseif (($bytes >= $kilobyte) && ($bytes < $megabyte)) {
	        return round($bytes / $kilobyte, $precision) . ' KB';
	    } elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
	        return round($bytes / $megabyte, $precision) . ' MB';
	    } elseif (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
	        return round($bytes / $gigabyte, $precision) . ' GB';
	    } elseif ($bytes >= $terabyte) {
	        return round($bytes / $terabyte, $precision) . ' TB';
	    } else {
	        return $bytes . ' B';
	    }
	}
	
	/** Includes a Model File from basePath/models/
	 *
	 * @param string $modelname the Models name
	 * @return bool flag if the model was included or not
	 * @access public
	 * @date Dez 15th 2011
	 */
	public function reg_model($modelname) {
		if(class_exists(strtoupper($this->prefix).$modelname)) return true;
		try {
			$lmn = strtolower($modelname);
			if(file_exists($this->basePath.'/models/'.$lmn.'.php')) {
				include($this->basePath.'/models/'.$lmn.'.php');
				return true;
			} else {
				throw new Exception('Model File not found: '.$modelname.' | '.$this->basePath.'/models/'.$lmn.'.php');
			}
		} catch(Exception $e) {
			$this->debug($e->getMessage()."\nFile: ".$e->getFile()."\nLine: ".$e->getLine(), 4);
			return false;
		}
	}
	
	/** Trys to get a class File named example.php from 
	 * "classes"-Subfolder of defined basePath and return a 
	 * instance of Example
	 * 
	 * @param String $classname
	 * @param Array/Empty $initArgs optional
	 * @return Object the requested Object
	 * @access public
	 * @date Jul 29th 2011
	 */
	public function get_instance($classname, $initArgs = array()) {
		
		// if(is_object($classname)) {
			// $initArgs['post'] = $classname;
			// $classname = $initArgs['args']['class'];
			// unset($initArgs['args']['class']);
			// $initArgs = array_merge($initArgs, $initArgs['args']['args']);
			// unset($initArgs['args']);
		// } // TODO: WTF?
		try {
			$filename = strtolower($classname);
			$lcn = strtolower($this->prefix.$classname);
			if(isset(self::$_singletons[$lcn]) && is_object(self::$_singletons[$lcn]))
				return self::$_singletons[$lcn];
			
			if(file_exists($this->basePath.'/classes/'.$filename.'.php')) {
				include_once($this->basePath.'/classes/'.$filename.'.php');
				
				$classname = strtoupper($this->prefix).$classname;
				$obj = new $classname(
					// array_merge(array('Master' => $this), 
					array_merge(
						$this->_mastersInitArgs,
						array_merge($this->_initArgs, $initArgs)
					// )
				));
				if(!$obj) {
					throw new Exception('Class was not initiated: '.$classname);
				} else {
					if(isset($obj->singleton) && $obj->singleton === true)
						self::$_singletons[$lcn] = $obj;
					if(isset($obj->HTML) && $obj->HTML === true) {
						if(defined('HTMLCLASSAVAILABLE') && HTMLCLASSAVAILABLE === true) {
							$obj->HTML = new HTML($obj->baseUrl);
						} else {
							throw new Exception('Class "'.$classname.'" should have been initiated whith HTML Object,'.
							' but it seems as the HTML Class file is not available could not be found');
						}
					}
					
					if(method_exists($obj, 'init'))
						$obj->init($initArgs);
					if(method_exists($this, '_hooks'))
						$this->_hooks($obj);
					if(method_exists($obj, 'hooks'))
						$obj->hooks();
					return $obj;
				}
			} else {
				throw new Exception('Class File not found: '.$classname.' | '.$this->basePath.'/classes/'.strtolower($classname).'.php');
			}
		} catch(Exception $e) {
			$this->debug($e->getMessage()."\nFile: ".$e->getFile()."\nLine: ".$e->getLine(), 4);
		}
	}
		
	// end of hooking chain.
	protected function _hooks() { }

	/** returns an array of $_POST data matched by the given string
	 *
	 * @param string $string the beginning of the $_POST keys that should be returned
	 * @return array
	 * @access public
	 * @date Jul 28th 2011
	 */
	public function filter_post_data_by($string) {
		$args = array();
		foreach($_POST as $key => $value) {
			if(substr($key, 0, 1) == '_')
				$key = substr($key, 1, strlen($key));			
			if(strlen($key) > strlen($string) && substr($key, 0, strlen($string)) == $string)
				$args[str_replace($string.'_', '', $key)] = $value;
		}
		return $args;
	}
	
	public function arrayToGet(array $arr) {
		$r = array();
		foreach($arr as $key => $value) {
			$r[] = $key.'='.$value;
		}
		return implode('&', $r);
	}
	
}


/******************************** 
 * Retro-support of get_called_class() 
 * Tested and works in PHP 5.2.4 
 * http://www.sol1.com.au/ 
 ********************************/ 
if(!function_exists('get_called_class')) { 
function get_called_class($bt = false,$l = 1) { 
    if (!$bt) $bt = debug_backtrace(); 
    if (!isset($bt[$l])) throw new Exception("Cannot find called class -> stack level too deep."); 
    if (!isset($bt[$l]['type'])) { 
        throw new Exception ('type not set'); 
    } 
    else switch ($bt[$l]['type']) { 
        case '::': 
            $lines = file($bt[$l]['file']); 
            $i = 0; 
            $callerLine = ''; 
            do { 
                $i++; 
                $callerLine = $lines[$bt[$l]['line']-$i] . $callerLine; 
            } while (stripos($callerLine,$bt[$l]['function']) === false); 
            preg_match('/([a-zA-Z0-9\_]+)::'.$bt[$l]['function'].'/', 
                        $callerLine, 
                        $matches); 
            if (!isset($matches[1])) { 
                // must be an edge case. 
                throw new Exception ("Could not find caller class: originating method call is obscured."); 
            } 
            switch ($matches[1]) { 
                case 'self': 
                case 'parent': 
                    return get_called_class($bt,$l+1); 
                default: 
                    return $matches[1]; 
            } 
            // won't get here. 
        case '->': switch ($bt[$l]['function']) { 
                case '__get': 
                    // edge case -> get class of calling object 
                    if (!is_object($bt[$l]['object'])) throw new Exception ("Edge case fail. __get called on non object."); 
                    return get_class($bt[$l]['object']); 
                default: return $bt[$l]['class']; 
            } 

        default: throw new Exception ("Unknown backtrace method type"); 
    } 
} 
}
?>