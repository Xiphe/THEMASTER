<?php
// Register base model file.
require_once('model.php');
require_once('debug.php');
require_once('tools.php');

class THEBASE {

	/* ------------------ *
	 *  STATIC VARIABLES  *
	 * ------------------ */

	/* PRIVATE */

	/**
	 * Turns true after first initiation.
	 *
	 * @access private
	 * @var    boolean
	 */
	private static $s_initiated = false;

	/**
	 * Holds the js/css files that will be echoed in the header.
	 * 
	 * @access private
	 * @var    array
	 */
	private static $s_registeredSources = array();

	/**
	 * Holder for js-variables that will be echoed in the frontend-header.
	 *
	 * @access private
	 * @var    array
	 */
	private static $s_registeredJsVars = array();

	/**
	 * Holder for js-variables that will be echoed in the backend-header.
	 *
	 * @access private
	 * @var    array
	 */
	private static $s_registeredAdminJsVars = array();

	/**
	 * Holder for !THEMASTERs internal callbacks
	 *
	 * @access private
	 * @var    array
	 */
	private static $s_callbacks = array();

	/**
	 * Holder for all singleton classes. Populated by THEBASE::get_instance().
	 *
	 * @access private
	 * @var    array
	 */
	private static $_singletons = array();


	/* PUBLIC */

	/**
	 * The basepath for !THEMASTER.
	 *
	 * @access public
	 * @var    string
	 */
	public static $sBasePath;

	/**
	 * The foldername of !THEMASTER
	 *
	 * @access public
	 * @var    string
	 */
	public static $sFolderName;

	/**
	 * The projectfile of !THEMASTER
	 *
	 * @access public
	 * @var    string
	 */
	public static $sProjectFile;

	/**
	 * The textdomain of !THEMASTER
	 *
	 * @access public
	 * @var    string
	 */
	public static $sTextdomain;

	/**
	 * The baseurl of !THEMASTER
	 *
	 * @access public
	 * @var    string
	 */
	public static $sBaseUrl;

	/**
	 * The textid of !THEMASTER
	 *
	 * @access public
	 * @var    string
	 */
	public static $sTextID;

	/**
	 * Array of all classes based on THEBASE
	 *
	 * @access public
	 * @var    array
	 */
	public static $THECLASSES = array(
		'THEWPMASTER',
		'THEWPSETTINGS',
		'THEWPUPDATES',
		'THEWPBUILDER',
		'THEMASTER',
		'THEDEBUG',
		'THESETTINGS',
		'THEBASE'
	);


	/* -------------------- *
	 *  INSTANCE VARIABLES  *
	 * -------------------- */

	/* PRIVATE */

	/**
	 * Flag to identify if the master has been initiated.
	 * Set in THEBASE::_masterInitiated() method.
	 *
	 * @access private
	 * @var    boolean
	 */
	private $_masterInitiated = false;

	/**
	 * Array of keys that will be required on a master init.
	 * New keys can be passed by THEBASE::add_requiredInitArgs_().
	 *
	 * @access private
	 * @var    array
	 */
	private $_requiredInitArgs = array();

	/**
	 * ?!?
	 *
	 * @access private
	 * @var array
	 */
	private $_initArgs = array();

	/**
	 * Array of temporary globals used by THEBASE::_unset_temp_globals(),
	 * THEBASE::_set_temp_globals() and THEMASE::_register_temp_global()
	 *
	 * @access private
	 * @var array 
	 */
	private $_temp_globals = array();


	/* PROTECTED */

	/**
	 * Backup for new class definitions.
	 * A Master can overwrite this variable to add his own required
	 * initiation keys.
	 *
	 * @access protected
	 * @var array
	 */
	protected $requiredInitArgs = array();
	
	/**
	 * Holds the original Array of initiation arguments passed on construction.
	 *
	 * @access protected
	 * @var array
	 */ 
	protected $_mastersInitArgs = array();
	

	/* ---------------------- *
	 *  CONSTRUCTION METHODS  *
	 * ---------------------- */

	
	/** 
	 * The Constructor gets called by every subclass
	 *
	 * @param array $initArgs
	 * @return mixed returns false if a required init Arg is missing or Instance if Subclass is Singleton
	 * @access public
	 */
	function __construct( $initArgs ) {
		if( !isset( $this->constructing ) || $this->constructing !== true ) {
			throw new Exception( "ERROR: THEBASE is not ment to be constructed directly.", 1 );
			return false;
		} else {
			unset( $this->constructing );
		}

		self::s_init();

		if( $initArgs === 'MINIMASTER' ) {
			return $this;
		}
		
		
		// check for required Init args (defined by protected $requiredInitArgs = array();)
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
				self::sdo_callback( 'beforeMasterInit', $this );
				$this->_masterInit();
			} else {
				self::sdo_callback( 'initiated', $this, array( 'class' => get_class($this) ) );
			}
		} else {
			throw new ErrorException('<strong>THEMASTER - Required args Error:</strong> ' . $err . ' in ' . ucfirst( $initArgs['projectType'] ). ' "' . $initArgs['projectName'] . '"' , 1);
			return false;
		}
	}

	private static function s_init() {
		if( !self::$s_initiated ) {
			self::sSession();

			self::$sProjectFile = THEMASTER_PROJECTFILE;
			self::$sBasePath = dirname( self::$sProjectFile ) . DS;
			self::$sFolderName = basename( self::$sBasePath );
			self::$sTextdomain = pathinfo( self::$sProjectFile, PATHINFO_FILENAME );
			self::$sTextID = self::$sFolderName . '/' . basename( self::$sProjectFile );

			if( function_exists( 'plugins_url' )) {
				self::$sBaseUrl = plugins_url( self::$sTextdomain );
			}
			
			if( THESETTINGS::sGet_setting( 'errorReporting', self::$sTextID_ ) === true ) {
				// error_reporting( E_ALL );
				error_reporting( E_ALL & ~E_DEPRECATED & ~E_STRICT );
				ini_set('display_errors', 1);
			} else {
				error_reporting(0);
				ini_set('display_errors', 0);
			}

			self::$s_initiated = true;
			self::sdo_callback( 'afterBaseS_init' );
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

	protected static function _masterUpdate() {
		return true;
	}

	public static function _masterActivate() {
		return true;
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
			$inst = $inst->get_instance( $called );
		else 
			return false;
		
		if(isset($inst->HTML))
			return array('HTML' => $inst->HTML, $called => $inst);
		else
			return array($called => $inst);
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
			self::sdo_callback( 'initiated', $this, array( 'class' => get_class($this) ) );
			$this->_masterInitiated = true;
		}
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
	public function view( $view, $args = null, $temp = null ) {
		echo $this->get_view( $view, $args, $temp );	
	}
		
	/**
	 * Trys to get a view File named example.php from 
	 * "views"-Subfolder of defined basePath and return its output
	 * automaticly searches in a subfolder with classname then in 
	 * root view folder. Other foldernames can be specified with 
	 * underscores folder_file - this will be taken at first.
	 * 
	 * @access public
	 * @param  string      $view the view name
	 * @param  array/Empty $args optional args passed to the view
	 * @param  mixed       $temp catching param for wordpress calls
	 * @return string      View String
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
			
			foreach( array( $this->basePath, self::$sBasePath ) as $basePath ) {
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
				'basePath' => self::$sBasePath,
				'baseUrl' => self::$sBaseUrl
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
							require_once( self::$sBasePath . 'classes' . DS . 'lessPHP' . DS . 'lessc.inc.php' );
							require_once( self::$sBasePath . 'classes' . DS . 'CSSfix' . DS . 'CSSfix.php' );
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
					$url = THEMASTER::slash( $baseUrl ) . THEMASTER::unPreslash( $path );
					$url .= is_array($vars) ? '?' . $this->arrayToGet($vars) : '';

					if( defined('HTMLCLASSAVAILABLE') ) {
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
	
	
	public function incl( $source, $include = false ) {
		$source = THETOOLS::get_verryCleanedDirectPath( $sSource ).'.php';
		
		foreach( array( $this->basePath, self::$sBasePath ) as $path ) {
			if( file_exists( ( $path = $path . 'res' . DS . 'includes' . DS . $source ) ) ) {
				return $include ? include( $path ) : $path;
			}
		}

		throw new Exception( 'Tryed to include unexistent file "' . $source . '"', 1 );
	}
	
	
	/** Includes a Model File from basePath/models/
	 *
	 * @param string $modelname the Models name
	 * @return bool flag if the model was included or not
	 * @access public
	 * @date Dez 15th 2011
	 */
	final public function reg_model( $modelname, $staticInits = null ) {
		if( class_exists( ( $fullModelname = strtoupper( $this->prefix ) . $modelname ) ) ) return true;
		try {
			if( file_exists( 
				( $inclPath = $this->basePath . 'models' . DS . strtolower( $modelname ) . '.php' ) 
			) ) {
				include( $inclPath );
				if( is_array( $staticInits ) ) {
					foreach( $staticInits as $key => $value ) {
						$fullModelname::$$key = $value;
					}
				}
				if( !class_exists( $fullModelname ) ) {
					throw new Exception('Model not found: '.$fullModelname.' | ' . $inclPath);
				} else {
					return true;
				}
			} else {
				throw new Exception('Model File not found: '.$fullModelname.' | ' . $inclPath);
			}
		} catch(Exception $e) {
			$this->debug($e->getMessage()."\nFile: ".$e->getFile()."\nLine: ".$e->getLine(), 4);
			return false;
		}
	}
	
	final public function new_model( $modelname, $initArgs = null ) {
		if( !class_exists( ( $fullModelname = strtoupper( $this->prefix ) . $modelname ) ) ) {
			$this->reg_model( $modelname );
		}
		if( class_exists( $fullModelname ) ) {
			return new $fullModelname( $initArgs );
		}
	}

	final public function gi( $classname, $initArgs = array() ) {
		if( isset( $this ) ) {
			return $this->get_instance( $classname, $initArgs );
		} else {
			return self::get_instance( $classname, $initArgs );
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
	final public function get_instance( $classname, $initArgs = array() ) {

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

		foreach( array( $basePath, self::$sBasePath ) as $k => $basePath ) {
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

				self::sRegister_callback(
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

		if( isset( $this ) && class_exists( 'THEWPBUILDER' ) && $this->buildMissingClasses === true ) {
			return THEWPBUILDER::sBuildClass( $classname, $initArgs, $this );
		} else {
			throw new Exception( '<strong>!THE MASTER ERROR:</strong> Class File for ' . $classname
				. ' not found --- Should be '
				. '<em>' . $basePath . 'classes' . DS . strtolower($classname) . '.php</em>'
				. ' or <em>' . self::$sBasePath . 'classes' . DS . strtolower($classname) . '.php</em>.',
				1
			);
		}
	}

	final public static function sRegSingleton( $obj ) {
		$name = get_class( $obj );
		$lcn = strtolower( $name );
		if( isset( self::$_singletons[ $lcn ] ) ) {
			throw new Exception( 'Invalid double construction of singleton "' . $name . '"', 1 );
		} else {
			self::$_singletons[ $lcn ] = $obj;
		}
	}

	final public function register_callback( $name, $cb, $times = 1, $condition = null, $conditionArgs = array(), $position = 10 ) {
		self::sRegister_callback( $name, $cb, $times, $condition, $conditionArgs, $position );
	}

	final public static function sRegister_callback( $name, $cb, $times = 1, $condition = null, $conditionArgs = array(), $position = 10 ) {
		self::$s_callbacks[$name][$position][] = array(
			'condition' => $condition,
			'cb' => $cb,
			'times' => $times,
			'conditionArgs' => $conditionArgs
		);
	}

	final public function do_callback( $name, $callbackArgs = array(), $doConditionArgs = array() ) {
		self::sdo_callback(  $name, $callbackArgs, $doConditionArgs );
	}

	final public static function sdo_callback( $name, $callbackArgs = array(), $doConditionArgs = array() ) {
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
						call_user_func_array( array( 'THEMASTER', 'sTryTo' ), $callbackArgs );
						
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

	
	// end of update chain.
	public function update() {
		return true;
	}

	final public function __call( $method, $args ) {
		if( isset( $this ) && method_exists( $this, 'call' ) ) {
			return call_user_func_array( array( $this, 'call' ), $args );
		} elseif( class_exists( 'THETOOLS' ) && method_exists( 'THETOOLS', $method ) ) {
			return call_user_func_array( array( 'THETOOLS', $method ), $args );
		} elseif( class_exists( 'THEDEBUG' ) && method_exists( 'THEDEBUG', $method ) ) {
			THEDEBUG::_set_btDeepth( 7 );
			return call_user_func_array( array( 'THEDEBUG', $method ), $args );
			THEDEBUG::_reset_btDeepth();
		} elseif( $method == 'debug' ) {
			echo '<pre>Debug:'."\n";
			var_dump( $args );
			echo '<pre>';
		} else {
			throw new Exception( 'Call to undefined method ' . $method, 1 );
		}
	}
}

if ( !function_exists('__') ) {
	function __( $text ) {
		return $text;
	}
}
if ( !function_exists('_e') ) {
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