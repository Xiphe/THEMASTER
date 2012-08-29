<?php
// Include parent class.
require_once('settings.php');

class THEMASTER extends THESETTINGS {	

	/* ------------------ */
	/*  STATIC VARIABLES  */
	/* ------------------ */

	/* PRIVATE */

	// Turns true after first initiation.
	private static $s_initiated = false;

	private static $s_BrowserObj = null;
	private static $s_browser = null;
	private static $s_browserVersion = null;
	
	private static $s_phpQueryInitiated = false;

	private static $s_initiatedProjects = array();
	private static $s_toBeInitiated = array();
	private static $_masterCronstructed = false;

	// Browser Translation Table for use with Browser Class
	private static $s_browserArray = array(
		'oa' => 'OPERA',
		'wtv' => 'WEBTV',
		'np' => 'NETPOSITIVE',
		'ie' => 'IE',
		'pie' => 'POCKET_IE',
		'gln' => 'GALEON',
		'knq' => 'KONQUEROR',
		'icb' => 'ICAB',
		'ow' => 'OMNIWEB',
		'ph' => 'PHOENIX',
		'fb' => 'FIREBIRD',
		'ff' => 'FIREFOX',
		'mz' => 'MOZILLA',
		'amy' => 'AMAYA',
		'lnx' => 'LYNX',
		'as' => 'SAFARI',
		'iph' => 'IPHONE',
		'ipo' => 'IPOD',
		'ipa' => 'IPAD',
		'ga' => 'ANDROID',
		'gc' => 'CHROME',
		'gb' => 'GOOGLEBOT',
		'slp' => 'SLURP',
		'w3c' => 'W3CVALIDATOR',
		'bb' => 'BLACKBERRY',
	);

	/* -------------------- */
	/*  INSTANCE VARIABLES  */
	/* -------------------- */


	/* PROTECTED */


	protected $_r = array(
		'status' => 'error',
		'msg' => 'nothing happened.',
		'errorCode' => -1
	);
	
	protected $_baseErrors = array();
	

	/* ---------------------- */
	/*  CONSTRUCTION METHODS  */
	/* ---------------------- */


	/**
	 * The Constructor method
	 *
	 * @param	array	$initArgs	the initiation arguments
	 */
	function __construct( $initArgs ) {
		if( !isset( $this->constructing )
		 && $this->constructing !== true
		 && is_object( ( $r = THEBASE::check_singleton_() ) ) ) {
			return $r;
		} else {
			$this->constructing = true;
		}

		$this->add_requiredInitArgs_( array(
			'prefix',
			'basePath',
			'baseUrl',
			'projectName'
		) );

		if( !self::$s_initiated ) {
			THEBASE::sRegister_callback( 'afterBaseS_init', array( 'THEMASTER', 'sinit' ) );
		}

		parent::__construct( $initArgs );
	}

	/**
	 * One time initiaton.
	 */
	public static function sinit() {
		if( !self::$s_initiated ) {

			if( defined( 'HTMLCLASSAVAILABLE' ) && HTMLCLASSAVAILABLE === true ) {
				// Register !html as available plugin
				array_push( self::$s_initiatedProjects, '!html' );
			}

			// Register themaster as available plugin.
			array_push( self::$s_initiatedProjects, '!themaster' );

			// Prevent this from beeing executed twice.
			self::$s_initiated = true;
		}
	}

	final public function tryTo( $func ) {
		self::sTryTo( $func );
	}

	final public static function sTryTo( $func ) {
		$args = func_get_args();
		unset( $args[0] );
		try {
			return call_user_func_array( $func, $args );
		} catch( Exception $e ) {
			if( class_exists( 'THEWPMASTER' ) ) {
				THEWPMASTER::sTryError( $e );
			} else {
				self::sTryToError( $e );
			}
		}
	}

	static public function sTryToError( $e ) {
		echo $e->getMessage() . "\nFile: " . $e->getFile() . "\nLine: " . $e->getLine();
	}

	protected function _masterInit() {
		if( !isset( $this ) ) {
			throw new Exception("_masterInit should not be called staticaly.", 1);
		}
		if( isset( $this->_masterInitiated ) && $this->_masterInitiated === true ) {
			return;
		}

		if( isset( $this->requiredPlugins )
		 && ( is_array( $this->requiredPlugins ) || is_object( $this->requiredPlugins ) )
		 && !$this->group_in_array( $this->requiredPlugins, self::$s_initiatedProjects )
		) {
			if( !isset( self::$s_toBeInitiated[ $this->textdomain ] ) ) {
				self::$s_toBeInitiated[ $this->textdomain ] = array(
					'required' => $this->requiredPlugins,
					'method' => array( $this, '_masterInit' ),
					'type' => $this->projectType
				);
			}
			return false;
		} else {
			if( isset( self::$s_toBeInitiated[ $this->textdomain ] ) ) {
				unset( self::$s_toBeInitiated[ $this->textdomain ] );
			}

			if( parent::_masterInit() ) {
				array_push( self::$s_initiatedProjects, $this->textdomain );
				
				foreach( self::$s_toBeInitiated as $k => $call ) {
					call_user_func( $call['method'] );
				}
				
				if( class_exists( 'THEWPMASTER' ) ) {
					return true;
				} else {
					$this->_masterInitiated();
				}
			}
		}
	}

	public function get_uninitiated() {
		return self::$s_toBeInitiated;
	}

	public function get_initiated() {
		return self::$s_initiatedProjects;
	}
	
	public function group_in_array( $needels, $haystack ) {
		foreach( $needels as $k ) {
			if( !in_array( $k, $haystack ) ) {
				return false;
			}
		}
		return true;
	}

	public function toAlpha($string) {
		return preg_replace("/[^a-zA-Z]/", '', $string);
	}
	
	public function toAlphaNumeric($string) {
		return preg_replace("/[^a-zA-Z0-9]/", '', $string);
	}
	
	public function toSlugForm($string) {
		return preg_replace("/[^a-zA-Z0-9\_]/", '', $string);
	}
	
	/** Checks if given string is a valid E-Mail
	 *
	 * @param string $email
	 * @return bool
	 * @access public
	 * @date Jul 28th 2011
	 */
	public function isValidEmail( $email ){
		return preg_match( "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/", $email );
	}
	
	/**
	 * Handy function to dive into an array or object without knowing what it is
	 * nested keys can be separated by a pipe so it's possible to get a deeper
	 * key with only one call
	 *
	 * @access public
	 * @param  mixed $obj   array or object
	 * @param  string $path the key or key-path (foo|bar)
	 * @return mixed        the keys value or null if not found
	 */
	final public function recursive_get( $obj, $path ) {	
		foreach( explode( '|', $path ) as $key ) {
			if( is_object( $obj ) ) {
				if( isset( $obj->$key ) )
					$obj = $obj->$key;
				else
					return null;
			}
			elseif( isset( $obj[$key] ) )
				$obj = $obj[$key];
			else {
				return null;
			}
		}
		return $obj;
	}

	/**
	 * Shorthand for Basics::recursive_get()
	 *
	 * @access public
	 * @param  mixed $obj   array or object
	 * @param  string $path the key or key-path (foo|bar)
	 * @return mixed        the keys value or null if not found
	 */
	final public function rget( $obj, $path ) {
		return self::recursive_get( $obj, $path );
	}

	/**
	 * Handy function to insert deeper data into an array or object.
	 * Functionality is similar to recursive_get.
	 * keys will be generated if they do not exist and $format == 'stdClass' || 'array'
	 *
	 * @access public
	 * @param  mixed  &$obj   the array or object in which the data should be inserted
	 * @param  string $path   the key or key-path (foo|bar|some|thing)
	 * @param  mixed  $value  the variable to set to the end of the path
	 * @param  string $type   the type of the new generated sub-keys set to something other
	 *                        than array or stdClass to prevent generation
	 * @return mixed          the object with new data
	 */
	final public function recursive_set( &$obj, $path, $value, $type = 'stdClass' ) {
		foreach( explode( '|', $path ) as $key ) {
			if( is_object( $obj ) ) {
				if( !isset( $obj->$key ) ) {
					if( $type = 'stdClass' ) {
						$obj->$key = new stdClass;
					} elseif( $type = 'array' ) {
						$obj->$key = array();;
					} else {
						return false;
					}
				}
				$obj = &$obj->$key;
			} else {
				if( !isset( $obj[$key] ) ) {
					if( $type = 'stdClass' ) {
						$obj[$key] = new stdClass;
					} elseif( $type = 'array' ) {
						$obj[$key] = array();;
					} else {
						return false;
					}
				}
				$obj = &$obj[$key];
			}	
			
		}
		return $obj = $value;
	}

	/**
	 * Shorthand for THEMASTER::recursive_set().
	 *
	 * @access public
	 * @param  mixed  &$obj   the array or object in which the data should be inserted
	 * @param  string $path   the key or key-path (foo|bar|some|thing)
	 * @param  mixed  $value  the variable to set to the end of the path
	 * @param  string $type   the type of the new generated sub-keys set to something other
	 *                        than array or stdClass to prevent generation
	 * @return mixed          the object with new data
	 */
	final public function rset( &$obj, $path, $value, $type = 'stdClass' ) {
		self::recursive_set( $obj, $path, $value, $type );
	}

	/**
	 * Handy function to unset a deep value of a class or an array.
	 *
	 * @access public
	 * @param  mixed  $obj  the class or array containing the value to be unset.
	 * @param  string $path the key or key-path (foo|bar|some|thing)
	 * @return mixed        true if unset, false if key does not exist, null if key is invalid.
	 */
	final public function recursive_unset( &$obj, $path ) {
		$i = 1;
		foreach( ( $l = explode( '|', $path ) ) as $key ) {
			if( is_object( $obj ) ) {
				if( isset( $obj->$key ) ) {
					if( count( $l ) === $i ) {
						unset( $obj->$key );
						return true;
					}
					$obj = &$obj->$key;
				} else {
					return false;
				}
			} else {
				if( isset( $obj[$key] ) ) {
					if( count( $l ) === $i ) {
						unset( $obj[$key] );
						return true;
					}
					$obj = &$obj[$key];
				} else {
					return false;
				}
			}	
			$i++;
		}
		return null;
	}

	/**
	 * Shorthand for THEMASTER::recursive_unset()
	 *
	 * @access public
	 * @param  mixed  $obj  the class or array containing the value to be unset.
	 * @param  string $path the key or key-path (foo|bar|some|thing)
	 * @return mixed        true if unset, false if key does not exist, null if key is invalid.
	 */
	final public function runset( &$obj, $path ) {
		return self::recursive_unset( $obj, $path );
	}


	
	/**
	 * remove potential slash from the end of the string.
	 *
	 * @access public
	 * @param  string $str the string to be unslashed
	 * @param  string $slash the slash can be set to backslash or whatever here.
	 * @return string      the unslashed string
	 */
	final public function unSlash( $str, $slash = '/' ) {
		if( substr( $str, strlen( $str ) - 1, strlen( $str ) ) === $slash ) {
			return substr( $str, 0, strlen( $str ) - 1 );
		}
		return $str;
	}

	/**
	 * add slash to the end of the string.
	 *
	 * @access public
	 * @param  string $str   the string to be slashed
	 * @param  string $slash the slash can be set to backslash or whatever here.
	 * @return string        the slashed string
	 */
	final public function slash( $str, $slash = '/' ) {
		if( substr( $str, strlen( $str ) - 1, strlen( $str ) ) !== $slash ) {
			return $str . $slash;
		}
		return $str;
	}

	/**
	 * remove potential slash from the front of the string.
	 *
	 * @access public
	 * @param  string $str the string to be unslashed
	 * @param  string $slash the slash can be set to backslash or whatever here.
	 * @return string      the unslashed string
	 */
	final public function unPreslash( $str, $slash = '/' ) {
		if( substr( $str, 0, 1 ) === $slash ) {
			return substr( $str, 1, strlen( $str ) );
		}
		return $str;
	}

	/**
	 * add slash to the front of the string.
	 *
	 * @access public
	 * @param  string $str   the string to be slashed
	 * @param  string $slash the slash can be set to backslash or whatever here.
	 * @return string        the slashed string
	 */
	final public function preslash( $str, $slash = '/' ) {
		if( substr( $str, 0, 1 ) !== $slash ) {
			return $slash . $str;
		}
		return $str;
	}

	/** curPageURL returns the current url
	 * 
	 * @by http://www.webcheatsheet.com/PHP/get_current_page_url.php
	 * @return string the current URL
	 * @date Dez 19th 2011
	 */
	public function get_currentUrl() {
		 $pageURL = 'http';
		 if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
		 $pageURL .= "://";
		 if (isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] != "80") {
			$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
		 } else {
			$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
		 }
		 return $pageURL;
	}
	
	
	public function replace_subdomain($sUrl, $sNewSubdomain) {
		$blogUrl = parse_url($sUrl);
		$host = $blogUrl['host'];
		if(substr_count($host, '.') >= 2) {
			$host = (str_replace(
				substr($host, 0, strpos($host, '.')),
				$sNewSubdomain,
				$host
			));
		} else {
			$host = $sNewSubdomain.'.'.$host;
		}
		return $blogUrl['scheme'].'://'.$host
			.(isset($blogUrl['path']) ? $blogUrl['path'] : '')
			.(isset($blogUrl['query']) ? '?'.$blogUrl['query'] : '');
	}
		
	/** deletes linebreaks and tabs from string
	 *
	 * @param string $string
	 * @param bool $noSpace if true spaces will be deleted, too
	 * @return string
	 * @access public
	 * @date Jul 29th 2011
	 */
	public function minify($string, $noSpace = false) {
		$string = preg_replace("'\s+'", ' ', $string);
		return $noSpace ? str_replace(' ', '', $string) : $string;
	}
	
	/** Determines if the script is called whith an internet explorer
	 *
	 * @param int $lowerorequal sets the max version number
	 * @param bool $exact if true check only for specific version else check for version an below
	 * @return bool true if ie specificated ie was found
	 * @access public
	 * @date Jul 21th 2011
	 */
	public function is_ie($lowerorequal = 8, $exact = false) {
		$ie = false;
		if($exact) {
			if(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE '.$lowerorequal.'.') !== FALSE)
				$ie = true;
		} else {
			for ($i=5; $i <= $lowerorequal; $i++) { 
				if(strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE '.$i.'.') !== FALSE) {
					$ie = true;
					break;
				}
			}
		}
		return $ie;
	}
	
	/** This function accepts specific formated browser strings
	 *  ff6||ie7.1.6s||as2.2o will return true if the users browser is:
	 *		Firefox 6 or any higher version (7, 8,..)
	 * 		Internet Explorer 7.1.6
	 * 		Apple Safari 2.2.0 or any heigher subversion (2.2.1, 2.2.7,..)
	 *
	 * @param string $sBrowsers a string formated Browser Array or 'mobile' / 'desktop'
	 * @return bool
	 * @access public
	 * @date Dec 28th 2011
	 */
	public function is_browser($sBrowsers) {
		$mobile = 'iph||ipo||ga||bb';
		
		if( $sBrowsers == 'mobile' ) {
			return $this->is_browser( $mobile );
		} elseif( $sBrowsers == 'desktop' ) {
			return !$this->is_browser( $mobile );
		}

		foreach( explode( '||', $sBrowsers ) as $browser) {
			$b = preg_split( '/([\d\.]+)/', $browser, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY );
			if( !isset( self::$s_browserArray[$b[0]] ) )
				continue; //TODO: THROW ERROR.
			$version = isset( $b[1] ) ? $b[1] : null;
			$strict = null;
			if( isset( $b[2] ) )
				$strict = $b[2] == 's' ? true : 'soft';
			if( $this->_is_browser( self::$s_browserArray[$b[0]], $version, $strict ) === true )
				return true;
		}
		return false;
	}
	
	/** Private Subfunction for $this->is_browser, initiates the Browser Class and compares Versions etc.
	 *
	 * @param string $browser the Browser transpated string *see self::$s_browserArray
	 * @param null|int $version optional browser version
	 * @param null|true|'soft' $strict null for greater than, true for 100% match requirement, 'soft' allows 2 == 2.0.0
	 * @return bool
	 * @access private
	 * @date Dec 28th 2011
	 */
	private function _is_browser($browser, $version = null, $strict = null) {
		if(self::$s_BrowserObj == null) {
			require_once( dirname( dirname( __FILE__ ) ) . DS . 'classes' . DS . 'Browser' . DS . 'Browser.php' );
			self::$s_BrowserObj = new Browser;
			self::$s_browserVersion = self::$s_BrowserObj->getVersion();
			self::$s_browser = self::$s_BrowserObj->getBrowser();
		}
		if(self::$s_browser == $this->get_classConstant('Browser', 'BROWSER_'.$browser)) { 
			if($version === null)
				return true;
			elseif($strict === null && version_compare(self::$s_browserVersion, $version, '>=')) {
				return true;
			} elseif($strict === 'soft') {
				$s1 = preg_replace('/0\./', '', self::$s_browserVersion);
				$s2 = preg_replace('/0\./', '', $version);
				if(substr($s1, 0, strlen($s2)) === $s2)
					return true;
			} elseif($strict === true && self::$s_browserVersion == $version)
				return true;
			
		}
		return false;
	}
	
	public function get_classConstant($sClassName, $sConstantName) {
		$oClass = new ReflectionClass($sClassName);
		return $oClass->getConstant($sConstantName);
	}
	
	
	/** 
	 * Determines if the script is called from an iPad device
	 *
	 * @return bool
	 * @access public
	 * @date Jul 27th 2011
	 */
	public function is_iPad() {
		$this->debug('Deprecated use of is_iPad(), use is_browser("ipa") instead', 3);
		$this->debug('callstack');
		return $this->is_browser('ipa');
	}
	
	function randomString($length = 12, $type = 'pAan') {
		echo $this->get_randomString($length, $type);
	}
	
	function get_randomString($length = 12, $type = 'pAan') {
		$punctuation = '!#$%&()=?*+-_:.;,<>';
		$alpha = 'abcdefghijklmnopqrstuvwxyz';
		$Alpha = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$numeric = '1234567890';
		
		$source = '';
		if(stristr($type, 'p') || stristr($type, 'P')) {
			$source .= $punctuation;
		}
		if(strstr($type, 'a')) {
			$source .= $alpha;
		}
		if(strstr($type, 'A')) {
			$source .= $Alpha;
		}
		if(stristr($type, 'n') || stristr($type, 'N')) {
			$source .= $numeric;
		}
		if($source == '') {
			throw new Exception('Type Error in get_randomString', 1);
		}
		
		srand((double)microtime()*1000000);
		$i = 0; $r = '';
		while ($i < $length) {
			$num = rand() % strlen($source);
			$tmp = substr($source, $num, 1);
			$r = $r . $tmp;
			$i++;
		}
		return $r;
	}
	
	public function switchVars(array $vars) {
		$i = 0;
		foreach ($vars as $k => $v) {
			if($i == 0) {
				$pk = $k;
				$pv = $v;
			} else {
				return array($pk => $v, $k => $pv);
			}
			$i++;
		}
	}
	
	public function initPhpQuery() {
		if( self::$s_phpQueryInitiated === false ) {
			require_once( dirname( dirname( __FILE__ ) ) . DS . 'classes' . DS . 'phpQuery.php' );
			self::$s_phpQueryInitiated = true;
		}
	}
	
	public function pq($html, $contentType = null) {
		$this->initPhpQuery();
		$html = $this->get_HTML()->r_div($html, '#themaster_phpquery_wrap_T3o0A5um8UfCK8Ba');
		phpQuery::newDocument( $html, $contentType );
	}
	
	public function get_pqHtml() {
		return pq('#themaster_phpquery_wrap_T3o0A5um8UfCK8Ba')->html();
	}
	
	protected function rebuild_r() {
		$this->_r = array(
			'status' => 'error',
			'msg' => 'nothing happened.',
			'errorCode' => -1
		);
	}
	
	protected function _exit( $status = null, $msg = null, $errorCode = null ) {
		if( is_int( $status ) && isset( $this->_baseErrors[$status] ) ) {
			$msg = $this->_baseErrors[$status];
			$errorCode = $status;
			$status = 'error';
		}
		
		foreach( array('status', 'msg', 'errorCode') as $k ) {
			if( $$k !== null ) {
				$this->_r[$k] = $$k;
			}
		}
		if( $this->_get_setting( 'debug', THEBASE::get_textID( THEMASTER_PROJECTFILE ) ) === true
		 && isset( $_REQUEST['debug'] )
		 && $_REQUEST['debug'] == 'true'
		) {
			$this->debug( $this->_r, 'result', 3 );
			exit();
		}

		
		if( isset( $_REQUEST['callback'] ) ) {
			header( 'Content-Type: text/javascript' );
			echo preg_replace( '/[^\w-]/', '', $_REQUEST['callback'] ) . "(" . json_encode($this->_r) . ");";
		} else {
			echo json_encode($this->_r);
		}
		exit;
	}

	public function post( $url, $params ) {
		if( !is_array( $params ) ) {
			$params = $this->ar( $params );
		}

		$query = http_build_query( $params );

		//open connection
		$ch = curl_init();

		//set the url, number of POST vars, POST data
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_POST, count( $params ) );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, $query );

		//execute post
		$r = curl_exec( $ch );

		//close connection
		curl_close($ch);

		return $r;
	}

	public function ar( $a, $b = null ) {
		$r = array();
		if( is_array( $a ) || is_object( $a ) ) {
			foreach( $a as $k => $v ) {
				$r[$k] = $v;
			}
		} elseif( is_string( $a ) ) {
			if( $b !== null ) {
				$r[$a] = $b;
			} else {
				$r = array_push( $r, $a );
			}
		}
		return $r;
	}

	public function sc( $a, $b = null ) {
		$r = new stdClass();
		if( is_array( $a ) || is_object( $a ) ) {
			foreach( $a as $k => $v ) {
				$r->$k = $v;
			}
		} elseif( is_string( $a ) ) {
			$r->$a = $b;
		}
		return $r;
	}
}
// if( !defined('THEMINIMASTERAVAILABLE') ) {
// 	$GLOBALS['THEMINIMASTER'] = new THEMASTER('MINIMASTER');
// 	define('THEMINIMASTERAVAILABLE', true);
// }
?>