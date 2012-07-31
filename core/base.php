<?php
// Register base model file.
require_once('model.php');

class THEBASE {

	/* ------------------ */
	/*  STATIC VARIABLES  */
	/* ------------------ */

	/* PRIVATE */
	// Turns true after first initiation.
	private static $s_initiated = false;

	private static $s_registeredSources = array();
	private static $s_registeredJsVars = array();
	private static $s_registeredAdminJsVars = array();

	private static $s_callbacks = array(); // HOLDS CALLBACK METHODS 
	private static $_singletons = array();

	protected static $sBasePath_;
	protected static $sFolderName_;
	protected static $sProjectFile_;
	protected static $sTextdomain_;
	protected static $sBaseUrl_;
	protected static $sTextID_;

	public static $THECLASSES = array('THEWPMASTER', 'THEWPSETTINGS', 'THEWPUPDATES', 'THEMASTER', 'THEDEBUG', 'THESETTINGS', 'THEBASE');

	// The Args needed by the Object to get initiated
	private $_masterInitiated = false;
	private $_requiredInitArgs = array();
	protected $requiredInitArgs = array();
	
	protected $_mastersInitArgs = array();
	private $_initArgs = array();
	
	// This is the Place of all Singleton Instances
	
	// Temporary Globales Used by: 
	private $_temp_globals = array();
	
	
	/** The Constructor gets called by every subclass
	 *
	 * @param array $initArgs
	 * @return mixed returns false if a required init Arg is missing or Instance if Subclass is Singleton
	 * @access public
	 * @package base
	 * @date Nov 10th 2011
	 */
	function __construct( $initArgs ) {
		if( !isset( $this->constructing ) || $this->constructing !== true ) {
			throw new Exception("ERROR: THEBASE is not ment to be constructed directly.", 1);
			return false;
		} else {
			unset( $this->constructing );
		}

		self::s_init();

		if( $initArgs === 'MINIMASTER' ) {
			return $this;
		}
		
		
		// cjeck for required Init args (defined by protected $requiredInitArgs = array();)
		$err = $this->get_requiredArgsError(
			$initArgs,
			array_merge(
				$this->_requiredInitArgs,
				$this->requiredInitArgs
			)
		);
		
		if( $err === false ) {
			// no error -> Set Init args as Class Variables
			foreach( $initArgs as $key => $value ) {
				$this->$key = $value;
			}
			// initiate the Object

			if( isset( $this->isMaster ) && $this->isMaster === true ) {
				unset( $initArgs['isMaster'] );
				$this->_mastersInitArgs = $initArgs;
				self::do_callback( 'beforeMasterInit', $this );
				$this->_masterInit();
			} else {
				self::do_callback( 'initiated', $this, array( 'class' => get_class($this) ) );
			}
		} else {
			throw new ErrorException('<strong>THEMASTER - Required args Error:</strong> ' . $err . ' in ' . ucfirst( $initArgs['projectType'] ). ' "' . $initArgs['projectName'] . '"' , 1);
			return false;
		}
	}

	protected static function check_singleton_( $name = null ) {
		/* check if Called Class is meant to be a Singleton (Subclass: static $singleton = true;)
		 * and if it is stored in THEMASTERs private $singletons array
		 */

		$name = isset( $name ) ? $name :  strtolower( get_called_class() );
		if( isset( self::$_singletons[$name] )
		 && is_object( ( $r = self::$_singletons[$name] ) ) 
		 && isset( $r->singleton ) && $r->singleton === true
		) {
			return $r;
		} else {
			return false;
		}
	}

	private static function s_init() {
		if( !self::$s_initiated ) {
			self::session();

			self::$sProjectFile_ = THEMASTER_PROJECTFILE;
			self::$sBasePath_ = dirname( self::$sProjectFile_ ) . DS;
			self::$sFolderName_ = basename( self::$sBasePath_ );
			self::$sTextdomain_ = pathinfo( self::$sProjectFile_, PATHINFO_FILENAME );
			self::$sTextID_ = self::$sFolderName_ . '/' . basename( self::$sProjectFile_ );

			if( function_exists( 'plugins_url' )) {
				self::$sBaseUrl_ = plugins_url( self::$sTextdomain_ );
			}
			
			if( THESETTINGS::get_setting( 'errorReporting', self::$sTextID_ ) === true ) {
				error_reporting(E_ALL);
				ini_set('display_errors', 1);
			} else {
				error_reporting(0);
				ini_set('display_errors', 0);
			}

			self::$s_initiated = true;
			self::do_callback( 'afterBaseS_init' );
		}
	}
	
	/** Adds required Init Args to Masters required args
	 *
	 * @param array $initArgs
	 * @return void
	 * @date Jan 22th 2012
	 */
	protected function add_requiredInitArgs_( $initArgs ) {
		if( is_array( $initArgs )) {
			$this->_requiredInitArgs = array_merge( $this->_requiredInitArgs, $initArgs );
		} elseif( is_string( $initArgs )) {
			array_push( $this->_requiredInitArgs, $initArgs );
		}
	}
	
	public function delete_invalidPathChars($path, $regex = '!_-\w\/\\\ ') {
		return preg_replace('/[^'.$regex.']/', '', $path);
	}
	
	/** Checks if the path has invalid characters.
	 *
	 * @param string $path
	 * @param bool $clean set true to call self::get_directPath() on $path
	 * @param string $regex the regex of forbidden chars
	 * @return void
	 * @date Jan 22th 2012
	 */
	public function is_cleanPath($path, $clean = false, $regex = '!_-\w\/\\\ ') {
		if($clean === true)
			$path = $this->get_directPath($path);
		if(preg_match('/[^'.$regex.']/', $path)) {
			return false;
		}
		return $path;
	}
	
	public function get_verryCleanedDirectPath($path) {
		return $this->delete_invalidPathChars($this->get_directPath($path));
	}
	
	/** Deletes ../ in pathes and cleans it with self::get_cleanedPath()
	 *
	 * @param string $path input path
	 * @return string
	 * @date Jan 22th 2012
	 */
	public function get_directPath($path) {
		// $this->debug('call of get_directPath');
		return str_replace('..'.DS, '', $this->get_cleanedPath($path));
	}
	
	// public function get_directPath($sPath) {
		// return str_replace(array('..'.DS, '.'.DS), '', str_replace(array(
			// '/_', '\\_', '/', '\\', 
		// ), DS, strtolower(preg_replace('/[^\w\_\.]+/', '', $sPath))));
	// }

	
	// Deprecated since 2.1.0
	public function cleanupPath($path)  {
		$this->deprecated('THEBASE::get_cleanedPath()');
		return $this->get_cleanedPath($path);
	}
	
	/** Replaces / & \ to DIRECTORY_SEPERATOR in $path
	 *
	 * @param string $path input path
	 * @return string
	 * @date Jan 22th 2012
	 */
	public function get_cleanedPath($path) {
		return preg_replace("/[\/\\\]+/", DS, $path);
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
		elseif( defined( 'THEMINIWPMASTERAVAILABLE' ) )
			return $GLOBALS[ 'THEMINIWPMASTER' ];
		elseif( defined( 'THEMINIMASTERAVAILABLE' ) )
			return $GLOBALS['THEMINIMASTER'];
		return false;
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
	public function get_dirArray(
		$dir,
		$key = 'filename',
		$filter = array(
			1, array('.', '_')
		)
	) {
		if ( $handle = opendir( $dir ) ) {
			$r = array();
			$i = 0;
			while ( false !== ( $file = readdir( $handle ) ) ) {
				if ( !in_array( substr( $file, 0, $filter[0] ), $filter[1] ) ) {
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
		// TODO: UNCOMMENT
		// if( !isset( $_SESSION ) && !headers_sent() )
		// 	session_start();

		// if( isset( $_SESSION ) )
		// 	return $_SESSION;
	}
	
	/** Initiation for a new Instance of THEMASTER, generates a new Submaster XYMaster
	 *
	 * @param array $initArgs see $this->requiredInitArgs for required keys
	 * @return void
	 * @access private
	 * @date Jul 28th 2011
	 */
	protected function _masterInit() {
		// if( isset($this->slug) && !isset( self::$_singletons['masters'][ $this->slug ] ) )
		// 	self::$_singletons['masters'][ $this->slug ] = $this;
		
			
		// $name = strtoupper($this->prefix).'Master';
		
		// global $$name;

		// $this->debug( $this, 'called' );


		return true;
		// $this->diebug( 'tot' );

		// try {
		// 	$r = $this->get_instance( 'Master' );
		// 	$this->initiated = TRUE;
		// 	return $r;
		// } catch (Exception $e) {
		// 	if( class_exists('THEWPMASTER') ) {
		// 		THEWPMASTER::sMasterInitError( $e, $initArgs );
		// 	} else {
		// 		echo '<p>' . $e . '</p>';
		// 	}
		// }
	}

	protected function _masterInitiated() {
		if( $this->_masterInitiated  !== true ) {
			self::do_callback( 'initiated', $this, array( 'class' => get_class($this) ) );
			$this->_masterInitiated = true;
		}
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
				if( is_array( $temp['args'] ) ) {
					$view = $temp['args'][0];
					unset($temp['args'][0]);
					$args = isset($temp['args']) ? $temp['args'] : array();
				} else {
					$view = $temp['args'];
					unset( $temp['args'] );
					$args = $temp;
				}
				if( isset( $page ) ) {
					$args['Page'] = $page;
				}
			}
			
			foreach( array( $this->basePath, self::$sBasePath_ ) as $basePath ) {
				$file = $basePath . 'views' . DS . $this->get_directPath( $view ) . '.php';

				if( file_exists( $file ) ) {
					if( !is_array( $args ) ) $args = array( $args );
					$ar = array( get_class($this) => $this );
					if( ( $HTML = $this->get_HTML() ) )
						$ar['HTML'] = $HTML;
					
					extract(array_merge(
						$ar, 
						$args
					));
					ob_start();
						include( $file );
					return ob_get_clean();
				}
			}

			throw new Exception('Error: View File not Found (' . $file . ')', 1);
		} catch(Exception $e) {
			$this->debug($e);
		}
	}

	/** 
	 *  getter for Own HTML Class, falls back on global HTML class 
	 *  if object does not have its own HTML class
	 *
	 * @param bool $silence switch for disabling the error throwing
	 * @return class HTML or null if $silence = true or false
	 * @access public
	 * @date Dez 14th 2011
	 * @since 2.0.12
	 */
	public function get_HTML( $silence = false ) {
		if( !isset( $this ) || !isset( $this->HTML ) ) {
			return self::sget_HTML( $silence );
		} else {
			return $this->HTML;
		}
	}
		
	public static function sget_HTML( $silence = false ) {
		if( isset( $GLOBALS['HTML'] ) )
			return $GLOBALS['HTML'];
		elseif( $silence !== true )
			throw new Exception( "HTML Class Needed but not available.", 1 );
		else
			return false;
	}
	
	public function reg_jsVar( $name, $var ) {
		if( !isset( self::$s_registeredJsVars[$name] ) )
			self::$s_registeredJsVars[$name] = $var;
	}
	
	public function reg_adminJsVar( $name, $var ) {
		if( !isset( self::$s_registeredAdminJsVars[$name] ) )
			self::$s_registeredAdminJsVars[$name] = $var;
	}
	
	public function reg_js( $filename, $vars = false ) {
		self::_reg_source( 'js', $filename, $vars );
	}
	
	public function reg_less( $filename, $vars = false ) {
		self::_reg_source( 'less', $filename, $vars );
	}
	
	public function reg_css( $filename, $vars = false ) {
		self::_reg_source( 'css', $filename, $vars );
	}

	public function reg_adminJs( $filename, $vars = false ) {
		self::_reg_source( 'js', $filename, $vars, true );
	}
	
	public function reg_adminLess( $filename, $vars = false ) {
		self::_reg_source( 'less', $filename, $vars, true );
	}
	
	public function reg_adminCss( $filename, $vars = false ) {
		self::_reg_source( 'css', $filename, $vars, true );
	}
	

	/**
	 * Registeres the source into THEBASE::$s_registeredSources.
	 * 
	 * @param  string  $source   the source type
	 * @param  string  $filename filename of the source
	 * @param  mixed   $vars     false or array to give additional get parameters to the source
	 * @param  boolean $admin    flag indicating if source is for admin or frontpage
	 * @return boolean           flag indicating if source is registered successfully
	 */
	private function _reg_source( $source, $filename, $vars = false, $admin = false ) {
		$foa = ( $admin ? 'admin' : 'front' );


		$lfn = strtolower($filename);
		if( isset( self::$s_registeredSources[$foa][$source][$lfn] ) )
			return true;
		
		try {

			// @ added 12-05-10
			if( ( $t = substr( $filename, 0, 7 ) ) == 'http://' || $t == 'https:/' ) {
				// $this->diebug( $filename );
				self::$s_registeredSources[$foa][$source][$lfn] = $filename;
				return true;
			}

			$path = DS . 'res' . DS . $source . DS . $lfn;
			if( isset( $this ) && isset( $this->basePath ) && is_dir( $this->basePath . $path ) && file_exists( $this->basePath . $path ) ) {
				foreach($this->get_dirArray( $this->basePath . $path ) as $subFile ) {
					if( ( $sfx = $this->get_suffix( $subFile ) ) == $source ) {
						$this->_reg_source( 
							$source,
							$lfn . DS . substr( $subFile, 0, strlen( $subFile ) - strlen( $sfx )-1 ),
							$vars,
							$admin
						);
					}
				}
				return;
			}


			$path .= is_array( $vars ) ? '.' . $source . '.php' : '.' . $source;

			$pureFile = $path;

			$paths = array();
			if( isset( $this ) ) {
				array_push( $paths, array(
					'basePath' => $this->basePath,
					'baseUrl' => $this->baseUrl
				) );
			}
			array_push( $paths, array(
				'basePath' => self::$sBasePath_,
				'baseUrl' => self::$sBaseUrl_
			) );
			
			foreach( $paths as $k => $p ) {
				extract( $p );

				if( file_exists( ( $file = $basePath . $pureFile ) ) ) {
					if( $source == 'less' ) {
						self::$s_registeredSources[$foa][$source][$lfn] = true;
						
						$lfn .= '.less';
						$path = 'res' . DS . 'css' . DS . $lfn . '.css';
						$target = $basePath . $path;
						
						if( !file_exists( ( $target ) ) 
						 || filemtime( $file ) > filemtime( $target )
						) {
							require_once( self::$sBasePath_ . 'classes' . DS . 'lessPHP' . DS . 'lessc.inc.php' );
							require_once( self::$sBasePath_ . 'classes' . DS . 'CSSfix' . DS . 'CSSfix.php' );
							$content = file( $file );
							
							// if($content[0] !== ($import = '@import "../../../../plugins/themaster/res/less/elements.less";'."\n")) {
							$import = "// themaster //\n@import \"elements.less\";\n// End: themaster //\n\n";
							if( !isset( $content[3] )
							 || $content[0] . $content[1] . $content[2] . $content[3] !== $import
							) {
								$content = $import . implode( '', $content );
								file_put_contents( $file, $content );
							}
							try {
								$LessC = new lessc( $file );
								$LessC->importDir = array( 
									'', 
									$basePath . 'res' . DS . 'less'
								);
								$CSS = $LessC->parse();
								$CSSfix = new CSSfix();
								$CSSfix->from_string( $CSS );
								if( !file_exists( ( $target = $basePath . $path ) ) ) {
									if( !is_dir( dirname( $target ) ) ) {
										mkdir( dirname( $target ) );
									}
									$h = fopen( $target, 'w' );
									fclose( $h );
									unset( $h );
								}
								file_put_contents( $target, $CSSfix->generate( false ) );

								// lessc::ccompile($file, ($target = $basePath.$path));
							} catch( Exception $e) {
								THEDEBUG::debug('LESS ERROR: '.$e->getMessage()." \nFile: ".$e->getFile()." \nLine: ".$e->getLine(), 4);
								return false;
							}
						}
						$source = 'css';
					}
					
					$path = str_replace( DS, '/', $path );
					$url = $baseUrl . '/' . $path;
					$url .= is_array($vars) ? '?' . $this->arrayToGet($vars) : '';

					if( defined('HTMLCLASSAVAILABLE') ) {
						if( !is_object( self::get_HTML() ) ) {
							THEDEBUG::debug( 'callstack' );
							THEDEBUG::diebug( $lfn );
						}
						self::$s_registeredSources[$foa][$source][$lfn] = self::get_HTML()->escape_mBB( $url );
					} else {
						self::$s_registeredSources[$foa][$source][$lfn] = $url;
					}

					return true;
				} elseif( $k+1 === count( $paths ) ) {
					throw new Exception(strtoupper($source).' File not found: '.$filename.' | '.$file);
					return false;
				}
			}
		} catch(Exception $e) {
			THEDEBUG::debug($e->getMessage()."\nFile: ".$e->getFile()."\nLine: ".$e->getLine(), 4);
			return false;
		}
	}
	
	public function echo_sources($admin = false) {
		if(empty(self::$s_registeredSources)) return;
		
		$sources = $admin == 'admin' ? self::$s_registeredSources['admin'] : self::$s_registeredSources['front'];
		unset($sources['less']);
		if(empty($sources)) return;

		$HTML = $this->get_HTML();
		foreach($sources as $type => $files) {
			foreach($files as $file => $url) {
				$HTML->$type($url);
			}
		}
	}

	public static function sGet_registeredSources() {
		return self::$s_registeredSources;
	}

	public static function sGet_registeredJsVars() {
		return self::$s_registeredJsVars;
	}

	public static function sGet_registeredAdminJsVars() {
		return self::$s_registeredAdminJsVars;
	}
	
	public function echo_jsVars() {
		$HTML = $this->get_HTML();
		$HTML->sg_script();
		foreach(self::$s_registeredJsVars as $name => $var) {
			$HTML->blank('var '.$name.' = '.json_encode($var).';');
			unset(self::$s_registeredJsVars[$name]);
		}
		$HTML->end();
	}
	
	public function echo_AdmimJsVars() {
		$HTML = $this->get_HTML();
		$HTML->sg_script();
		foreach(self::$s_registeredAdminJsVars as $name => $var) {
			$HTML->blank('var '.$name.' = '.json_encode($var));
			unset(self::$s_registeredAdminJsVars[$name]);
		}
		$HTML->end();
	}
	
	
	public function incl($sSource, $include = false) {
		$sSource = $this->get_verryCleanedDirectPath($sSource).'.php';
		
		if(file_exists(($path = $this->basePath.DS.'res'.DS.'includes'.DS.$sSource))) {
			return $include ? include($path) : $path;
		} elseif(file_exists(($path = dirname(dirname(__FILE__)).DS.'res'.DS.'includes'.DS.$sSource))) {
			return $include ? include($path) : $path;
		}
		return false;
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
	public function reg_model( $modelname, $staticInits = null ) {
		if( class_exists( ( $fullModelname = strtoupper( $this->prefix ) . $modelname ) ) ) return true;
		try {
			if( file_exists( 
				( $inclPath = $this->basePath . DS . 'models' . DS . strtolower( $modelname ) . '.php' ) 
			) ) {
				include( $inclPath );
				if( is_array( $staticInits ) ) {
					foreach( $staticInits as $key => $value ) {
						$fullModelname::$$key = $value;
					}
				}
				return true;
			} else {
				throw new Exception('Model File not found: '.$fullModelname.' | ' . $inclPath);
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
	public function get_instance( $classname, $initArgs = array() ) {

		if( $classname === 'Master' ) {
			if( isset( $initArgs['prefix'] )
		 	 && isset( $initArgs['basePath'] )
		 	) {
				$prefix = $initArgs['prefix'];
				$basePath = $initArgs['basePath'];
		 	} else {
		 		throw new Exception( 'Master initiation misses arguments (prefix & basePath)', 1);
		 		return false;
		 	}
		} elseif( isset( $this ) ) {
			$prefix = $this->prefix;
			$basePath = $this->basePath;
		} else {
			throw new Exception( 'Can not call nonmaster classes statically.', 1 );
			return false;
		}

		$classname = trim( $classname );
		$filename = strtolower( $classname );
		$lcn = strtolower( $prefix . $classname );

		if(isset(self::$_singletons[$lcn]) && is_object(self::$_singletons[$lcn]))
			return self::$_singletons[$lcn];

		foreach( array( $basePath, self::$sBasePath_ ) as $k => $basePath ) {
			if( file_exists( ( $file = $basePath . 'classes' . DS . $filename . '.php' ) ) ) {
				include_once( $file );
				
				$classname = ( $k === 0 ? $prefix : '' ) . $classname;

				if( !class_exists( $classname ) ) {
					throw new Exception('<strong>!THE MASTER ERROR:</strong> Class ' . $classname . ' is not available.', 2);
				}

				$args = isset( $this ) ? array_merge(
						$this->_mastersInitArgs,
						array_merge( $this->_initArgs, $initArgs )
				) : $initArgs;

				self::register_callback(
					'initiated', 
					function( $obj ) {
						if( isset( $obj->singleton ) && $obj->singleton === true ) {
							THEBASE::sRegSingleton( $obj );
						}

						if( isset( $obj->HTML ) && $obj->HTML === true ) {
							if( defined( 'HTMLCLASSAVAILABLE' ) && HTMLCLASSAVAILABLE === true ) {
								$obj->HTML = new HTML( $obj->baseUrl );
							} else {
								throw new Exception( '<strong>!THE MASTER ERROR:</strong> Class "'
									. get_class( $obj ) . '" should have been initiated whith HTML Object,'
									. ' but it seems as the HTML Class file is not available.', 4 );
							}
						}

						if( method_exists( $obj, '_hooks' ) )
							$obj->_hooks();
						if( method_exists( $obj, 'hooks' ) )
							$obj->hooks();
						if( method_exists( $obj, 'init' ) )
							$obj->init();
					},
					1,
					function( $condArgs, $givenArgs ) {
						return $condArgs['class'] === $givenArgs['class'];
					},
					array( 'class' => $classname )
				);

				$obj = new $classname( $args );

				if( !$obj )
					throw new Exception( '<strong>!THE MASTER ERROR:</strong> Class was not initiated: ' . $classname, 3 );
				
				return $obj;
			} 
		}
		throw new Exception( '<strong>!THE MASTER ERROR:</strong> Class File for ' . $classname
			. ' not found --- Should be '
			. '<em>' . $basePath . 'classes' . DS . strtolower($classname) . '.php</em>'
			. ' or <em>' . self::$sBasePath_ . 'classes' . DS . strtolower($classname) . '.php</em>.',
			1
		);
	}

	public static function sRegSingleton( $obj ) {
		$name = get_class( $obj );
		$lcn = strtolower( $name );
		if( isset( self::$_singletons[ $lcn ] ) ) {
			throw new Exception('Invalid double construction of singleton "' . $name . '"', 1 );
		} else {
			self::$_singletons[ $lcn ] = $obj;
		}
	}

	public function register_callback( $name, $cb, $times = 1, $condition = null, $conditionArgs = array(), $position = 10 ) {
		self::$s_callbacks[$name][$position][] = array(
			'condition' => $condition,
			'cb' => $cb,
			'times' => $times,
			'conditionArgs' => $conditionArgs
		);
	}

	public function do_callback( $name, $callbackArgs = array(), $doConditionArgs = array() ) {
		if( isset( self::$s_callbacks[$name] ) 
		 && count( self::$s_callbacks[$name] ) > 0
		) {
			ksort( self::$s_callbacks[$name] );

			foreach( array( 'callbackArgs', 'doConditionArgs' ) as $k ) {
				if( !is_array( $$k ) ) {
					$$k = array( $$k );
				}
			}
			foreach( self::$s_callbacks[$name] as $nr => $cbg ) {
				foreach( $cbg as $k => $v ) {
					extract( $v );

					if( !isset( $condition )
					 || ( !is_callable( $condition ) && $condition )
					 || call_user_func_array( $condition, array( $doConditionArgs, $conditionArgs ) )
					) {
						array_unshift( $callbackArgs, $cb );
						call_user_func_array( array( 'THEMASTER', 'tryTo' ), $callbackArgs );
						
						if( $times !== '*' ) {
							$times--;
							if( $times <= 0 ) {
								unset( self::$s_callbacks[$name][$nr][$k] );
							} else {
								self::$s_callbacks[$name][$nr][$k]['times'] = $times;
							}
						}
					}
				}
			}
		}
	}
		
	// end of hooking chain.
	protected function _hooks() { }

	/** returns an array containing the keys of $data, starting with the given prefix
	 *  $data = array( 'foo_bar1' => 'bar, 'bar_bar2' => 'foo' );
	 *  filtered by $match = 'foo' will return array( 'foo_bar1' => 'bar );
	 *
	 * @param string $match the beginning of the $data keys that should be returned
	 * @param array|class $data
	 * @return array
	 * @access public
	 * @date Feb 29th 2012
	 */
	public function filter_data_by( $match, $data ) {
		$args = array();
		foreach($data as $key => $value) {
			if(substr($key, 0, 1) == '_')
				$key = substr($key, 1, strlen($key));			
			if(strlen($key) > strlen($match) && substr($key, 0, strlen($match)) == $match)
				$args[str_replace($match.'_', '', $key)] = $value;
		}
		return $args;
	}
	
	
	public function filter_post_data_by( $string ) {
		$this->deprecated('THEBASE::filter_postDataBy()');
		return $this->filter_data_by( $string, $_POST );
	}
	/** returns an array containing the keys of $_POST, starting with the given prefix
	 *  $_POST = array( 'foo_bar1' => 'bar, 'bar_bar2' => 'foo' );
	 *  filtered by $match = 'foo' will return array( 'foo_bar1' => 'bar );
	 *
	 * @param string $match the beginning of the $_POST keys that should be returned
	 * @return array
	 * @access public
	 * @date Feb 29th 2012
	 */
	public function filter_postDataBy( $string ) {
		return $this->filter_data_by( $string, $_POST );
	}
	/** returns an array containing the keys of $_GET, starting with the given prefix
	 *  $_GET = array( 'foo_bar1' => 'bar, 'bar_bar2' => 'foo' );
	 *  filtered by $match = 'foo' will return array( 'foo_bar1' => 'bar );
	 *
	 * @param string $match the beginning of the $_GET keys that should be returned
	 * @return array
	 * @access public
	 * @date Feb 29th 2012
	 */
	public function filter_getDataBy( $string ) {
		return $this->filter_data_by( $string, $_GET );
	}
	/** returns an array containing the keys of $_REQUEST, starting with the given prefix
	 *  $_REQUEST = array( 'foo_bar1' => 'bar, 'bar_bar2' => 'foo' );
	 *  filtered by $match = 'foo' will return array( 'foo_bar1' => 'bar );
	 *
	 * @param string $match the beginning of the $_REQUEST keys that should be returned
	 * @return array
	 * @access public
	 * @date Feb 29th 2012
	 */
	public function filter_requestDataBy( $string ) {
		return $this->filter_data_by( $string, $_REQUEST );
	}
	
	// end of update chain.
	public function update() {
		return true;
	}

	public function get_textID( $file ) {
		return basename( dirname( $file ) ) . '/' . basename( $file );
	}
	
	public function arrayToGet(array $arr) {
		$r = array();
		foreach($arr as $key => $value) {
			$r[] = $key.'='.$value;
		}
		return implode('&', $r);
	}
	
	/** slices the given string by the last . and returns the last characters.
	 *
	 * @param string $file a url, path or filename
	 * @return string the suffix
	 * @date Mrz 27th 2012
	 */
	public function get_suffix($file) {
		return substr( $file, strrpos( $file, '.' )+1, strlen( $file ) );
	}

	/** fallback method for THEDEBUG
	 *
	 * @param mixed $var the variable to be debugged
	 * @return void
	 * @access public
	 * @date Jan 21th 2012
	 */
	public function debug($var) {
		echo '<pre>Debug:'."\n";
		var_dump($var);
		echo '<pre>';
	}
}

if(!function_exists('__')) {
	function __( $text ) {
		return $text;
	}
}
if(!function_exists('_e')) {
	function _e( $text ) {
		echo $text;
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