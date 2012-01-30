<?php
require_once('debug.php');
class THEMASTER extends THEDEBUG {	

	static $BrowserObj = null;
	static $browser = null;
	static $browserVersion = null;
	
	// Browser Translation Table for use with Browser Class
	private $_browserArray = array(
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
		'ga' => 'ANDROID',
		'gc' => 'CHROME',
		'gb' => 'GOOGLEBOT',
		'slp' => 'SLURP',
		'w3c' => 'W3CVALIDATOR',
		'bb' => 'BLACKBERRY',
	);

	function __construct($initArgs) {
		$initArgs['original'] = true;
		$this->_initArgs(array(
			'prefix',
			'basePath',
			'baseUrl',
			'projectName',
			'textdomain'
		));
		parent::__construct($initArgs);
		
	}
	
	protected function _masterInit($initArgs) {
		return parent::_masterInit($initArgs);
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
	public function isValidEmail($email){
		return eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $email);
	}
	
	/** Tries to get a deeper array or object variable
	 *
	 * @param mixed $obj the array or object
	 * @param string $path the variable path (foo|bar|x will search for $obj->foo['bar']['x'])
	 * @return mixes the found variable or null
	 * @date Dez 20th 2011
	 */
	public function recursive_get($obj, $path) {		
		foreach(explode('|', $path) as $key) {
			if(is_object($obj)) {
				if(isset($obj->$key))
					$obj = $obj->$key;
				else
					return null;
			}
			elseif(isset($obj[$key]))
				$obj = $obj[$key];
			else
				return null;
				// throw new Exception("tried to get undefined path: ".$path, 1);
		}
		return $obj;
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
	 * @param string $sBrowsers a string formated Browser Array 
	 * @return bool
	 * @access public
	 * @date Dec 28th 2011
	 */
	public function is_browser($sBrowsers) {
		foreach(explode('||', $sBrowsers) as $browser) {
			$b = preg_split('/([\d\.]+)/', $browser, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			if(!isset($this->_browserArray[$b[0]]))
				continue; //TODO: THROW ERROR.
			$version = isset($b[1]) ? $b[1] : null;
			$strict = null;
			if(isset($b[2]))
				$strict = $b[2] == 's' ? true : 'soft';
			if($this->_is_browser($this->_browserArray[$b[0]], $version, $strict) === true)
				return true;
		}
		return false;
	}
	
	/** Private Subfunction for $this->is_browser, initiates the Browser Class and compares Versions etc.
	 *
	 * @param string $browser the Browser transpated string *see $this->_browserArray
	 * @param null|int $version optional browser version
	 * @param null|true|'soft' $strict null for greater than, true for 100% match requirement, 'soft' allows 2 == 2.0.0
	 * @return bool
	 * @access private
	 * @date Dec 28th 2011
	 */
	private function _is_browser($browser, $version = null, $strict = null) {
		if(self::$BrowserObj == null) {
			require_once(dirname(dirname(__FILE__)).'classes'.DS.'Browser'.DS.'Browser.php');
			self::$BrowserObj = new Browser;
			self::$browserVersion = self::$BrowserObj->getVersion();
			self::$browser = self::$BrowserObj->getBrowser();
		}
		if(self::$browser == $this->get_classConstant('Browser', 'BROWSER_'.$browser)) { 
			if($version === null)
				return true;
			elseif($strict === null && version_compare(self::$browserVersion, $version, '>=')) {
				return true;
			} elseif($strict === 'soft') {
				$s1 = preg_replace('/0\./', '', self::$browserVersion);
				$s2 = preg_replace('/0\./', '', $version);
				if(substr($s1, 0, strlen($s2)) === $s2)
					return true;
			} elseif($strict === true && self::$browserVersion == $version)
				return true;
			
		}
		return false;
	}
	
	public function get_classConstant($sClassName, $sConstantName) {
	    $oClass = new ReflectionClass($sClassName);
	    return $oClass->getConstant($sConstantName);
	}
	
	
	/** Determines if the script is called from an iPad device
	 *
	 * @return bool
	 * @access public
	 * @date Jul 27th 2011
	 */
	public function is_iPad() {
		if(preg_match('#iPad#', $_SERVER['HTTP_USER_AGENT']) === 1)
			return true;
		else
			return false;
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
		if(stristr($type, 'p')) {
			$source .= $punctuation;
		}
		if(strstr($type, 'a')) {
			$source .= $alpha;
		}
		if(strstr($type, 'A')) {
			$source .= $Alpha;
		}
		if(stristr($type, 'n')) {
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
	
	
	
} ?>