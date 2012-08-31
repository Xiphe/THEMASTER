<?php 
namespace Xiphe\THEMASTER;

/**
 * THETOOLS is a collection of standalone methods used by !THE MASTER.
 *
 * @copyright Copyright (c) 2012, Hannes Diercks
 * @author    Hannes Diercks <xiphe@gmx.de>
 * @version   3.0.0
 * @link      https://github.com/Xiphe/-THE-MASTER/
 * @package   !THE MASTER
 */
class THETOOLS {

    /**
     * The Browserclass.
     *
     * @access private
     * @var object
     */
    private static $s_BrowserObj = null;

    /**
     * The current Browser
     *
     * @access private
     * @var string
     */
    private static $s_browser = null;

    /**
     * The current Browser Version
     *
     * @access private
     * @var version-string
     */
    private static $s_browserVersion = null;

    /**
     * Browser Translation Table for use with Browser Class
     *
     * @access private
     * @var array
     */
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

    /**
     * Flag for preventing PHPQuery to be only initiated once.
     *
     * @access private 
     * @var boolean
     */
    private static $s_phpQueryInitiated = false;

    /**
     * Performs a post request to given url passing given parameters.
     *
     * 
     * @param  string $url    the url
     * @param  mixed  $params array, class or string parameter. See THETOOLS::ar()
     * @return string         the output of the requested url.
     */
    public static function post($url, $params)
    {
        if (!is_array($params)) {
            $params = self::ar($params);
        }

        $query = http_build_query($params);

        /*
         * open connection.
         */
        $ch = curl_init();

        /*
         * set the url, number of POST vars, POST data.
         */
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, count($params));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $query);

        /*
         * execute post.
         */
        $r = curl_exec( $ch );

        /*
         * close connection.
         */
        curl_close($ch);

        return $r;
    }

    /**
     * Converts arrays, objects, integers or strings into arrays.
     *
     * @access public
     * @param  mixed  $a    the variable to be converted or the key if $b is set.
     * @param  mixed  $b    the value for key $a.
     * @param  mixed  $deep flag if the converting should be limited on first layer or
     *                      also be appended to deeper objects.
     * @return array        a fresh an clean array.
     */
    public static function ar($a, $b = null, $deep = false)
    {
        $r = array();
        if (is_array($a) || is_object($a)) {
            foreach ($a as $k => $v) {
                if ($deep == true && (is_array($v) || is_object($v))) {
                    $v = self::ar($v, null, true);
                }
                $r[$k] = $v;
            }
        } elseif (is_string($a) || is_int($a)) {
            if ($b !== null) {
                $r[$a] = $b;
            } else {
                $r[] = $a;
            }
        }
        return $r;
    }

    /**
     * Converts arrays, objects, integers or strings into stdClasses.
     *
     * @access public
     * @param  mixed  $a    the variable to be converted or the key if $b is set.
     * @param  mixed  $b    the value for key $a.
     * @param  mixed  $deep flag if the converting should be limited on first layer or
     *                      also be appended to deeper arrays.
     * @return array        a fresh an clean stdClass.
     */
    public static function sc( $a, $b = null, $deep = false )
    {
        $r = new stdClass();
        if (is_array($a) || is_object($a)) {
            foreach ($a as $k => $v) {
                if ($deep == true && (is_array($v) || is_object($v))) {
                    $v = self::sc($v, null, true);
                }
                $r->$k = $v;
            }
        } elseif (is_string($a) || is_int($a)) {
            $r->$a = $b;
        }
        return $r;
    }

    /**
     * Initiates PHPQuery.
     *
     * @access public
     * @return void
     */
    public static function initPhpQuery()
    {
        if (self::$s_phpQueryInitiated === false) {
            require_once(THEMASTER_PROJECTFOLDER.'classes'.DS.'phpQuery.php');
            self::$s_phpQueryInitiated = true;
        }
    }
    
    /**
     * Sets a new "DOM" for PHPQuery.
     *
     * @access public
     * @param  string $html
     * @param  string $contentType
     * @return void
     */
    public static function pq($html, $contentType = null)
    {
        self::initPhpQuery();
        phpQuery::newDocument('<div id="#themaster_phpquery_wrap_T3o0A5um8UfCK8Ba">'.$html.'</div>', $contentType);
    }
    
    /**
     * Returns the "DOM"-Markup.
     *
     * @access public
     * @return string
     */
    public static function get_pqHtml()
    {
        return pq('#themaster_phpquery_wrap_T3o0A5um8UfCK8Ba')->html();
    }

    /**
     * Switches the first two variables of an array while leaving its keys.
     * 
     * @param  array  $vars
     * @return array
     */
    public static function switchVars(array $vars)
    {
        $i = 0;
        foreach ($vars as $k => $v) {
            if ($i == 0) {
                $pk = $k;
                $pv = $v;
            } else {
                return array($pk => $v, $k => $pv);
            }
            $i++;
        }
    }

    /**
     * Echo wrapper for THETOOLS::get_randomString().
     * 
     * @access public
     * @param  integer $length the length of the string
     * @param  string  $type   keys of chartypes to be used. See beginning of declaration.
     * @return string
     */
    public static function randomString($length = 12, $type = 'pAan')
    {
        echo self::get_randomString($length, $type);
    }
    
    /**
     * Generates a passwordish random string
     *
     * @access public
     * @param  integer $length the length of the string
     * @param  string  $type   keys of chartypes to be used. See beginning of declaration.
     * @return string
     */
    public static function get_randomString($length = 12, $type = 'pAan')
    {
        $sources = array(
            'p' => '!#$%&()=?*+-_:.;,<>',        // Punctuation
            'a' => 'abcdefghijklmnopqrstuvwxyz', // alpha
            'A' => 'ABCDEFGHIJKLMNOPQRSTUVWXYZ', // Alpha
            'n' => '1234567890'                  // Numeric
        );
        
        $source = '';

        foreach ( $sources as $k => $s ) {
            if (stristr($type, $k)) {
                $source .= $s;
            }
        }

        if ($source == '') {
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

    /**
     * Private Submethod for THETOOLS::is_browser(), initiates the Browser Class and compares Versions etc.
     *
     * @access private
     * @param string           $browser the Browser transpated string *see self::$s_browserArray
     * @param null|int         $version optional browser version
     * @param null|true|'soft' $strict  null for greater than, true for 100% match requirement,
     *                        'soft' allows 2 == 2.0.0
     * @return bool
     */
    private static function _is_browser($browser, $version = null, $strict = null)
    {
        if (self::$s_BrowserObj == null) {
            require_once(THEMASTER_PROJECTFOLDER.'classes'.DS.'Browser'.DS.'Browser.php');
            self::$s_BrowserObj = new Browser;
            self::$s_browserVersion = self::$s_BrowserObj->getVersion();
            self::$s_browser = self::$s_BrowserObj->getBrowser();
        }
        if (self::$s_browser == self::get_classConstant('Browser', 'BROWSER_'.$browser)) { 
            if ($version === null)
                return true;
            elseif ($strict === null && version_compare(self::$s_browserVersion, $version, '>=')) {
                return true;
            } elseif ($strict === 'soft') {
                $s1 = preg_replace('/0\./', '', self::$s_browserVersion);
                $s2 = preg_replace('/0\./', '', $version);
                if (substr($s1, 0, strlen($s2)) === $s2) {
                    return true;
                }
            } elseif ($strict === true && self::$s_browserVersion == $version) {
                return true;
            }
        }
        return false;
    }

    /**
     * This function accepts specific formated browser strings
     *  ff6||ie7.1.6s||as2.2o will return true if the users browser is:
     *      Firefox 6 or any higher version (7, 8,..)
     *      Internet Explorer 7.1.6
     *      Apple Safari 2.2.0 or any heigher subversion (2.2.1, 2.2.7,..)
     *
     * @access public
     * @param  string $sBrowsers a string formated Browser Array or 'mobile' / 'desktop'
     * @return bool
     */
    public static function is_browser($sBrowsers)
    {
        $mobile = 'iph||ipo||ga||bb';
        
        if ($sBrowsers == 'mobile') {
            return self::is_browser($mobile);
        } elseif ($sBrowsers == 'desktop') {
            return !self::is_browser($mobile);
        }

        foreach (explode('||', $sBrowsers) as $browser) {
            $b = preg_split('/([\d\.]+)/', $browser, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
            if (!isset(self::$s_browserArray[$b[0]])) {
                $msg = sprintf(
                    __('Unknown Browserkey **%s** in THETOOLS::ins_browser()', 'themaster'),
                    $b[0]
                );
                throw new Exception($msg, 1);
                
            }
            $version = isset($b[1]) ? $b[1] : null;
            $strict = null;
            if (isset($b[2])) {
                $strict = $b[2] == 's' ? true : 'soft';
            }
            if (self::_is_browser(self::$s_browserArray[$b[0]], $version, $strict) === true) {
                return true;
            }
        }
        return false;
    }

    /**
     * deletes linebreaks and tabs from string
     *
     * @access public
     * @param  string $string
     * @param  bool   $noSpace if true spaces will be deleted, too
     * @return string
     */
    public static function minify($string, $noSpace = false)
    {
        $string = preg_replace("/\s+/", ' ', $string);
        return $noSpace ? str_replace(' ', '', $string) : $string;
    }

    /**
     * Replaces the subdomain of given url or adds it.
     *
     * @access public
     * @param  string $url
     * @param  string $newSubdomain
     * @return string
     */
    public static function replace_subdomain($url, $newSubdomain)
    {
        $url = parse_url($url);
        if(substr_count($url['host'], '.') >= 2) {
            $url['host'] = (str_replace(
                substr($url['host'], 0, strpos($url['host'], '.')),
                $newSubdomain,
                $url['host']
            ));
        } else {
            $url['host'] = $newSubdomain.'.'.$url['host'];
        }
        return self::unparse_url($url);
    }

    /**
     * reverses the result array of parse_url into an url string.
     *
     * @access public
     * @param  array  $parsedUrl a parsed url
     * @return string            a unparsed url
     */
    public static function unparse_url($parsedUrl)
    {
        $scheme   = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
        $host     = isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
        $port     = isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
        $user     = isset($parsedUrl['user']) ? $parsedUrl['user'] : '';
        $pass     = isset($parsedUrl['pass']) ? ':' . $parsedUrl['pass']  : '';
        $pass     = ($user || $pass) ? "$pass@" : '';
        $path     = isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
        $query    = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
        $fragment = isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : '';
        return "$scheme$user$pass$host$port$path$query$fragment"; 
    }

    /**
     * curPageURL returns the current url
     * 
     * by http://www.webcheatsheet.com/PHP/get_current_page_url.php
     *
     * @access public
     * @return string the current URL
     */
    public static function get_currentUrl()
    {
         $pageURL = 'http';
         if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
         }
         $pageURL .= "://";
         if (isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
         } else {
            $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
         }
         return $pageURL;
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
    public static function recursive_get($obj, $path)
    {    
        foreach (explode('|', $path) as $key) {
            if (is_object($obj)) {
                if (isset($obj->$key)) {
                    $obj = $obj->$key;
                } else {
                    return null;
                }
            } elseif (isset($obj[$key])) {
                $obj = $obj[$key];
            } else {
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
    public static function rget($obj, $path)
    {
        return self::recursive_get($obj, $path);
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
    public static function recursive_set(&$obj, $path, $value, $type = 'stdClass')
    {
        foreach (explode('|', $path) as $key) {
            if (is_object($obj)) {
                if (!isset($obj->$key)) {
                    if ($type = 'stdClass') {
                        $obj->$key = new stdClass;
                    } elseif ($type = 'array') {
                        $obj->$key = array();;
                    } else {
                        return false;
                    }
                }
                $obj = &$obj->$key;
            } else {
                if (!isset($obj[$key])) {
                    if ($type = 'stdClass') {
                        $obj[$key] = new stdClass;
                    } elseif ($type = 'array') {
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
    public static function rset(&$obj, $path, $value, $type = 'stdClass')
    {
        self::recursive_set($obj, $path, $value, $type);
    }

    /**
     * Handy function to unset a deep value of a class or an array.
     *
     * @access public
     * @param  mixed  $obj  the class or array containing the value to be unset.
     * @param  string $path the key or key-path (foo|bar|some|thing)
     * @return mixed        true if unset, false if key does not exist, null if key is invalid.
     */
    public static function recursive_unset(&$obj, $path)
    {
        $i = 1;
        foreach (($l = explode('|', $path)) as $key) {
            if (is_object($obj)) {
                if (isset($obj->$key)) {
                    if (count($l) === $i) {
                        unset($obj->$key);
                        return true;
                    }
                    $obj = &$obj->$key;
                } else {
                    return false;
                }
            } else {
                if (isset($obj[$key])) {
                    if (count($l) === $i) {
                        unset($obj[$key]);
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
    public static function runset(&$obj, $path)
    {
        return self::recursive_unset($obj, $path);
    }

    /**
     * Checks if all keys specified by $needels are present in $haystack
     *
     * @access public
     * @param  array   $needels  the required keys.
     * @param  array   $haystack the array that should contain the keys.
     * @return boolean           false if one key is missing.
     */
    public static function group_in_array($needels, $haystack)
    {
        foreach ($needels as $k) {
            if (!in_array($k, $haystack)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Deletes all non-a-Z chars from given string.
     *
     * @access public
     * @param  string $string
     * @return string
     */
    public static function toAlpha($string)
    {
        return preg_replace("/[^a-zA-Z]/", '', $string);
    }
    
    /**
     * Deletes all non-a-Z-0-9 chars from given string.
     *
     * @access public
     * @param  string $string
     * @return string
     */
    public static function toAlphaNumeric($string)
    {
        return preg_replace("/[^a-zA-Z0-9]/", '', $string);
    }
    
    /**
     * Deletes all non-a-Z-0-9_ chars from given string.
     *
     * @access public
     * @param  string $string
     * @return string
     */
    public static function toSlugForm($string)
    {
        return preg_replace("/[^a-zA-Z0-9\_]/", '', $string);
    }
    
    /**
     * Checks if given string is a valid E-Mail
     *
     * @access public
     * @param  string $email
     * @return bool
     */
    public static function isValidEmail($email)
    {
        return preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/", $email);
    }

    /**
     * remove potential slash from the end of the string.
     *
     * @access public
     * @param  string $str the string to be unslashed
     * @return string      the unslashed string
     */
    public static function unSlash($str)
    {
        return rtrim($str,'/');
    }

    /**
     * remove potential backslash from the end of the string.
     *
     * @access public
     * @param  string $str the string to be unbackslashed
     * @return string      the unbackslashed string
     */
    public static function unBackSlash($str)
    {
        return rtrim($str,'\\');
    }

    /**
     * add slash to the end of the string.
     *
     * @access public
     * @param  string $str the string to be slashed
     * @return string      the slashed string
     */
    public static function slash($str)
    {
        return rtrim($str,'/').'/';
    }

    /**
     * add backslash to the end of the string.
     *
     * @access public
     * @param  string $str the string to be backslash
     * @return string      the backslashed string
     */
    public static function backSlash($str)
    {
        return rtrim($str,'\\').'\\';
    }

    /**
     * remove potential slash from the front of the string.
     *
     * @access public
     * @param  string $str the string to be unslashed
     * @return string      the unslashed string
     */
    public static function unPreSlash($str)
    {
        return ltrim($str,'/');
    }

    /**
     * remove potential backslash from the front of the string.
     *
     * @access public
     * @param  string $str the string to be unbackslashed
     * @return string      the unbackslashed string
     */
    public static function unPreBackSlash($str)
    {
        return ltrim($str,'\\');
    }

    /**
     * add slash to the front of the string.
     *
     * @access public
     * @param  string $str the string to be slashed
     * @return string      the slashed string
     */
    public static function preSlash($str)
    {
        return '/'.ltrim($str,'/');
    }

    /**
     * add backslash to the front of the string.
     *
     * @access public
     * @param  string $str the string to be backslashed
     * @return string      the backslashed string
     */
    public static function preSlash($str)
    {
        return '\\'.ltrim($str,'\\');
    }
    
    /**
     * Deletes chars specified by $regex from $path
     *
     * Default regex allowes alphanumeric chars, "!", "_", "-", "/", "\" and " ".
     * 
     * @access public
     * @param  string $path  the path to be cleaned.
     * @param  string $regex the regex of valid chars.
     * @return string        the clean path
     */
    public static function delete_invalidPathChars($path, $regex = '!_-\w\/\\\ ')
    {
        return preg_replace('/[^'.$regex.']/', '', $path);
    }
    
    /**
     * Checks if the path has invalid characters.
     *
     * @access public
     * @param  string $path
     * @param  bool   $clean set true to call self::get_directPath() on $path
     * @param  string $regex the regex of valid chars
     * @return void
     */
    public static function is_cleanPath($path, $clean = false, $regex = '!_-\w\/\\\ ')
    {
        if ($clean === true) {
            $path = self::get_directPath($path);
        }
        if (preg_match('/[^' . $regex . ']/', $path)) {
            return false;
        }
        return $path;
    }
    
    /**
     * Converts to DIRECTORY_SEPERATOR, deletes ../ and invalid chars.
     *
     * valid chars are alphanumeric chars, "!", "_", "-", "/", "\" and " "
     *
     * @access public
     * @param  string $path an unclean path.
     * @return string       a clean path.
     */
    public static function get_verryCleanedDirectPath( $path )
    {
        return self::delete_invalidPathChars(self::get_directPath($path));
    }
    
    /**
     * Deletes ../ in pathes and cleans it with self::get_cleanedPath()
     *
     * @access public
     * @param  string $path input path.
     * @return string
     */
    public static function get_directPath($path)
    {
        return str_replace('..' . DS, '', self::get_cleanedPath($path));
    }
    
    
    /** 
     * Replaces / & \ to DIRECTORY_SEPERATOR in $path
     *
     * @access public
     * @param string $path input path
     * @return string
     * @date Jan 22th 2012
     */
    public static function get_cleanedPath($path)
    {
        return preg_replace("/[\/\\\]+/", DS, $path);
    }


    /**
     * Returns a class Constant.
     * 
     * @param  string $className
     * @param  string $constantName
     * @return mixed
     */
    public static function get_classConstant($className, $constantName)
    {
        $Class = new ReflectionClass($className);
        return $Class->getConstant($constantName);
    }

    /**
     * Getter for Static Variables of Named Classes
     *
     * @access public
     * @param  string $classname
     * @param  string $key
     * @return mixed
     */
    public static function get_static_var($classname, $key)
    {
        $vars = get_class_vars($classname);
        if (isset($vars[$key])) {
            return $vars[$key];
        }
    }

    /**
     * returns an array of files in a specific folder, excluding files starting with . or _
     *
     * The default filter can be adjusted as a third parameter.
     * Accepted is an array with the amount of chars that will be filtered at the beginning
     * of the file or folder name on the first index. The second index have to be another
     * array containing strings that are not accepted.
     * 
     * @access public
     * @param  string $dir    the directory
     * @param  mixed  $key    option for the array key of each file number or filename
     *                        possible, default: the filename
     * @param  array  $filter an array defining the. 
     * @return array
     */
    public static function get_dirArray($dir, $key = 'filename', $filter = null)
    {
        if (!isset($filter)) {
            $filter = array(1, array('.', '_'));
        }
        if ($handle = opendir($dir)) {
            $r = array();
            $i = 0;
            while (false !== ($file = readdir($handle))) {
                if (!in_array(substr($file, 0, $filter[0]), $filter[1])) {
                    $k = $key == 'filename' ? $file : $i;
                    $r[$k] = $file;
                    $i++;
                }
            }
            return $r;
        }
    }

    /**
     * Looks if a session has been started, stars one if not and returns it.
     * 
     * @return array the session.
     */
    public static function session()
    {
        if (!isset($_SESSION) && !headers_sent()) {
            session_start();
        }

        if (isset($_SESSION)) {
            return $_SESSION;
        }
    }

    /**
     * Checks if Array 1 has all required keys, specified by Array 2
     * 
     * @access public
     * @param  array $args         the array to be checked.
     * @param  array $requiredArgs the required keys.
     * @return string|false        Error string or false if no Error found.
     */
    public static function get_requiredArgsError($args, $requiredArgs)
    {
        if (!is_array($args)) {
            return __('$args is not an array', 'themaster');
        }
        if (!is_array($requiredArgs)) {
            return __('$required is not an array', 'themaster');
        }

        $missing = array();
        foreach ($requiredArgs as $req) {
            if (!isset($args[$req])) {
                $missing[] = $req;
            }
        }
        
        if (count($missing) == 0) {
            return false;
        } else {
            if (count($missing) == 1) {
                return sprintf(__('Missing "%s" as a key.', 'themaster'), $missing[0]);
            } else {
                $and = $missing[count($missing)-1];
                unset($missing[count($missing)-1]);
                $missing = implode(', ', $missing).' '.__('and', 'themaster').' '.$and;
                return sprintf(__('Missing "%s" as keys.', 'themaster'), $missing);
            }
        }
    }

    /**
     * Converts a given integer into a human readable file-size.
     *
     * from: http://codeaid.net/php/convert-size-in-bytes-to-a-human-readable-format-%28php%29
     *
     * @access public
     * @param  integer  $bytes    the basic size to be recalculated.
     * @param  integer $precision how much floating values?
     * @return string             the readable size
     */
    public static function bytesToSize($bytes, $precision = 2)
    {  
        $kilobyte = 1024;
        $megabyte = $kilobyte * 1024;
        $gigabyte = $megabyte * 1024;
        $terabyte = $gigabyte * 1024;

        if (($bytes >= 0) && ($bytes < $kilobyte)) {
            return $bytes.' B';
        } elseif (($bytes >= $kilobyte) && ($bytes < $megabyte)) {
            return round($bytes / $kilobyte, $precision).' KB';
        } elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
            return round($bytes / $megabyte, $precision).' MB';
        } elseif (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
            return round($bytes / $gigabyte, $precision).' GB';
        } elseif ($bytes >= $terabyte) {
            return round($bytes / $terabyte, $precision).' TB';
        } else {
            return $bytes.' B';
        }
    }

    /**
     * Generates a identifier string based on file and folder name of given path.
     * 
     * @param  string $file the path
     * @return string       the textID
     */
    public static function get_textID($file)
    {
        return basename(dirname($file)).'/'.basename($file);
    }

        /**
     * returns an array containing the keys of $data, starting with the given prefix.
     * 
     * $data = array( 'foo_bar1' => 'bar, 'bar_bar2' => 'foo' );
     * filtered by $match = 'foo' will return array( 'foo_bar1' => 'bar );
     *
     * @access public
     * @param  string      $match the beginning of the $data keys that should be returned
     * @param  array|class $data  the data.
     * @return array
     */
    public static function filter_data_by($match, $data)
    {
        $args = array();
        foreach ($data as $key => $value) {
            if (substr($key, 0, 1) == '_') {
                $key = substr($key, 1, strlen($key));           
            }
            if (strlen($key) > strlen($match)
             && substr($key, 0, strlen($match)) == $match
            ) {
                $args[str_replace($match.'_', '', $key)] = $value;
            }
        }
        return $args;
    }
    
    /**
     * returns an array containing the keys of $_POST, starting with the given prefix.
     * 
     * $_POST = array( 'foo_bar1' => 'bar, 'bar_bar2' => 'foo' );
     * filtered by $match = 'foo' will return array( 'foo_bar1' => 'bar );
     *
     * @access public
     * @param  string $match the beginning of the $_POST keys that should be returned
     * @return array
     */
    public static function filter_postDataBy($string)
    {
        return $this->filter_data_by($string, $_POST);
    }

    /**
     * returns an array containing the keys of $_GET, starting with the given prefix.
     * 
     * $_GET = array( 'foo_bar1' => 'bar, 'bar_bar2' => 'foo' );
     * filtered by $match = 'foo' will return array( 'foo_bar1' => 'bar );
     *
     * @access public
     * @param  string $match the beginning of the $_GET keys that should be returned
     * @return array
     */
    public static function filter_getDataBy($string)
    {
        return $this->filter_data_by($string, $_GET);
    }

    /**
     * returns an array containing the keys of $_REQUEST, starting with the given prefix.
     * 
     * $_REQUEST = array( 'foo_bar1' => 'bar, 'bar_bar2' => 'foo' );
     * filtered by $match = 'foo' will return array( 'foo_bar1' => 'bar );
     *
     * @access public
     * @param  string $match the beginning of the $_REQUEST keys that should be returned
     * @return array
     */
    public static function filter_requestDataBy($string)
    {
        return $this->filter_data_by($string, $_REQUEST);
    }


}
?>