<?php 
namespace Xiphe;

/**
 * A little collection of standalone functions and libs for everyday usage.
 *
 * @copyright Copyright (c) 2013, Hannes Diercks
 * @author    Hannes Diercks <info@xiphe.net>
 * @link      https://github.com/Xiphe/-THE-MASTER/
 * @license   http://www.gnu.org/licenses/gpl-2.0.txt GNU GENERAL PUBLIC LICENSE
 * @package   !THE MASTER
 */

/*
 Copyright (C) 2013 Hannes Diercks

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 
 You should have received a copy of the GNU General Public License along
 with this program; if not, write to the Free Software Foundation, Inc.,
 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

class THETOOLS {
    /* ------------------ *
     *  STATIC VARIABLES  *
     * ------------------ */
    
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

    private static $s_filters = array(
        's' => FILTER_SANITIZE_STRING,
        'e' => FILTER_SANITIZE_EMAIL,
        'u' => FILTER_SANITIZE_URL,
        'i' => FILTER_VALIDATE_INT,
        'f' => FILTER_VALIDATE_FLOAT,
        'b' => FILTER_VALIDATE_BOOLEAN
    );

    /**
     * Flag for preventing PHPQuery to be only initiated once.
     *
     * @access private 
     * @var boolean
     */
    private static $s_phpQueryInitiated = false;

    /**
     * An optional url that will be used by get_currentUrl instead of the real one.
     *
     * @var boolean|string
     */
    private static $fakeCurrentUrl = false;


    /* ---------------- *
     *  STATIC METHODS  *
     * ---------------- */


    /**
     * Use Glen Scotts UrlNormalizer to normalize the passed url.
     * 
     * @param  string $url 
     * @return string
     */
    public static function normalizeUrl($url) {
        if (!class_exists('URLNormalizer')) {
            require_once(realpath(__DIR__.'/../../classes').'/URLNormalizer.php'); 
        }

        $un = new \URLNormalizer();
        $un->setUrl($url);
        return $un->normalize();
    }

    /**
     * Basic encryption method.
     *
     * http://www.tfonfara.de/php-encryptdecrypt-funktion-mit-base64-und-schlussel.xhtml
     *
     * @param string $string the string that needs encryption.
     * @param string $key    a key string for the encryption.
     *
     * @return string the encrypted string.
     */
    public static function encrypt($string, $key) {
        $result = '';
        for ($i=0; $i<strlen ($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key))-1, 1);
            $char = chr(ord($char)+ord($keychar));
            $result .= $char;
        }

        return base64_encode($result);
    }

    /**
     * Basic decryption method.
     *
     * http://www.tfonfara.de/php-encryptdecrypt-funktion-mit-base64-und-schlussel.xhtml
     *
     * @param string $string a string encrypted by THETOOLS::encrypt().
     * @param string $key    a key string for the decryption.
     *
     * @return string the decrypted string.
     */
    public static function decrypt($string, $key) {
        $result = '';
        $string = base64_decode($string);

        for ($i=0; $i<strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($key, ($i % strlen($key))-1, 1);
            $char = chr(ord($char)-ord($keychar));
            $result .= $char;
        }

        return $result;
    }

    /**
     * Return the folder on with the both given paths split.
     * 
     * @param  string  $a      first path
     * @param  string  $b      second path
     * @param  boolean $modify set true to directly shorten the passed paths to be relative to the return values
     * @param  string  $DS     the directory separator that should be used.
     * @return string
     */
    public static function getPathsBase(&$a, &$b, $modify = false, $DS = DS) {
        if ($modify) {
            $str1 = &$a;
            $str2 = &$b;
        } else {
            $str1 = $a;
            $str2 = $b;
        }

        $str1 = explode($DS, self::unify_slashes($str1, $DS));
        $str2 = explode($DS, self::unify_slashes($str2, $DS));
        $match = array();
        for ($i=0; $i < count($str1); $i++) { 
            if ($str1[$i] === $str2[$i]) {
                $match[] = $str1[$i];
            } else {
                break;
            }
        }
        $match = implode($DS, $match);
        $str1 = substr(implode($DS, $str1), strlen($match));
        $str2 = substr(implode($DS, $str2), strlen($match));

        return $match;
    }

    /**
     * Checks if the given Path is existent and generates missing folders.
     *
     * @param string  $path  the path
     * @param integer $chmod chmod for new folders
     *
     * @return boolean  true if path is available false if not.
     */
    public static function buildDir($path, $chmod = 0775) {
        deprecated('mkdir($dir, null, true);');
        if (is_dir($path)) {
            return true;
        }
        $dir = DS;
        foreach(explode(DS, self::unify_slashes($path, DS, true)) as $f) {
            if (empty($f)) {
                continue;
            }
            $dir .= $f.DS;
            if (is_dir($dir)) {
                continue;
            }

            if (is_writable(dirname($dir))) {
                mkdir($dir, $chmod);
            } else {
                return false;
            }
        }
        return true;
    }

    /**
     * Builds an excerpt from a longer text.
     *
     * @param string  $text      the input text
     * @param integer $maxlength maximal length of the text
     * @param string  $end       a string that will be attached to the short version of $text
     *
     * @return string
     */
    public static function shorten($text, $maxlength = 140, $end = '[...]') {
        $maxlength++;
        if (mb_strlen($text) > $maxlength) {
            $subex = mb_substr($text, 0, $maxlength - 5);
            $exwords = explode(' ', $subex);
            $excut = - (mb_strlen($exwords[count($exwords)-1]));
            if ($excut < 0) {
                $text = mb_substr($subex, 0, $excut);
            } else {
                $text = $subex;
            }
            $text .= $end;
        }
        return $text;
    }

    /**
     * Parses a list-string from an array, an object or a testlist seperated by $inputSep.
     *
     * @param mixed  $input     array, object or string with list entrys.
     * @param string $lastSep   the text for the last seperator. Default = " or "
     * @param string $stdSep    the text for all other seperators. Default = ", "
     * @param string $inputSep  the string that explodes the $input if it is a string.
     * 
     * @return string  a nice readable list of things.
     */
    public static function readableList($input, $lastSep = null, $defSep = ', ', $inputSep = '|', $itemWrap = '')
    {
        if ($lastSep === null) {
            $lastSep = ' '.__('or', 'themaster').' ';
        } 
        $r = '';
        if (is_string($input)) {
            $input = explode($inputSep, $input);
        }
        $l = count($input)-1;

        if (!empty($itemWrap)) {
            $input = self::arrayWrap($input, $itemWrap);
        }

        foreach ($input as $k => $v) {
            if ($k == 0) {
                $r .= $v;
            } elseif ($k == $l) {
                $r .= $lastSep.$v;
            } else {
                $r .= $defSep.$v;
            }
        }
        return $r;
    }

    /**
     * Wrap each entry of an array
     * 
     * @param  array        $input the array 
     * @param  array|string $wrap  the wrap
     * @return array
     */
    public static function arrayWrap($input, $wrap)
    {
        $w1 = '';
        $w2 = '';
        if (is_array($wrap)) {
            $w1 = $wrap[0];
            if (isset($wrap[1])) {
                $w2 = $wrap[1];
            }
        } elseif (!is_string($wrap)) {
            throw new \Exception(sprintf('Invalid Type %s for $wrap', gettype($wrap)), 1);
            return false;
        } else {
            $w1 = $wrap;
            $w2 = $wrap;
        }

        return array_map(
           function ($el) use ($w1, $w2) {
              return $w1.$el.$w2;
           },
           $input
        );
    }
    
    /**
     * Creates a sprite image at $dest from image files in $imgs array.
     *
     * @access public
     * @param  array   $imgs    array of image files.
     * @param  string  $dest    filepath to where the sprite should be saved.
     * @param  integer $spacing numbers of pixels between the images.
     * @return object           object containing the sprite dimensions and the offsets of each image.
     */
    public static function create_sprite($imgs = array(), $dest = null, $spacing = 5, $type = 'png') {
        switch ($type) {
        case 'jpg':
        case 'jpeg':
            $save = 'imagejpeg';
            $create = 'imagecreatefromjpeg';
            if ($dest === null) {
                $dest = 'sprite.jpg';
            }
            break;
        case 'png':
        default:
            $save = 'imagepng';
            $create = 'imagecreatefrompng';
            if ($dest === null) {
                $dest = 'sprite.png';
            }
            break;
        }


        $spriteWidth = 0;
        $spriteHeight = 0;

        foreach ($imgs as $k => $file) {
            list($w, $h) = getimagesize($file);
            // make sure out icon is a 32px sq icon
            if ($h > $spriteHeight) {
                $spriteHeight = $h;
            }
            $spriteWidth += $w;
            if($k < count($imgs)-1) {
                $spriteWidth += $spacing;
            }
        }
        $r = self::sc(array(
            'sprite' => self::sc(array(
                'width' => $spriteWidth,
                'height' => $spriteHeight,
            )),
            'positions' => self::sc(array()),
            'sizes' => self::sc(array())
        ));

        $img = imagecreatetruecolor($spriteWidth, $spriteHeight);

        $background = imagecolorallocate($img, 0, 0, 0);
        $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127); 
        imagecolortransparent($img, $background);
        imagealphablending($img, false);
        imagesavealpha($img, true);

        $pos = 0;
        foreach ($imgs as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);
            $tmp = call_user_func_array($create, array($file));
            list($w, $h) = getimagesize($file);
            $r->positions->$name = -$pos;
            $r->sizes->$name = self::sc(array('width' => $w, 'height' => $h));

            if($pos > 0 && $spacing > 0) {
                imagefilledrectangle($img, $pos, 0, $pos + $spacing, $spriteHeight, $transparent);
                $pos += $spacing;
            }
            imagecopy($img, $tmp, $pos, 0, 0, 0, $spriteWidth, $spriteHeight);
            $pos += $w;
            imagedestroy($tmp);
        }

        call_user_func_array(
            $save,
            array($img, $dest, ($save == 'imagejpeg' ? 100 : 0))
        );
        imagedestroy($img);

        return $r;
    }

    public static function getResizedImgName($path, $p) {
        if (is_array($p)) {
            $width = $p[0];
            $height = $p[1];
        } else {
            $size = getimagesize($path);
            if (!is_float($p)) {
                $p = intval($p)/100;
            }
            $width = round($size[0]*$p);
            $height = round($size[1]*$p);
        }

        $r = dirname($path).DS;
        $r .= preg_replace('/-[0-9]+x[0-9]+/', '', pathinfo($path, PATHINFO_FILENAME));
        $r .= '-'.$width.'x'.$height.'.'.pathinfo($path, PATHINFO_EXTENSION);
        return $r;
    }

    public static function resizeImg($type, $path, $p, $target = null)
    {
        switch ($type) {
            case 'image/gif':
            case 'gif':
                $original = imagecreatefromgif($path);
                break;
            case 'image/png':
            case 'png':
                $original = imagecreatefrompng($path);
                break;
            default:
                $original = imagecreatefromjpeg($path);
                break;
        }

        if (is_array($p)) {
            $width = $p[0];
            $height = $p[1];
        } else {
            $size = getimagesize($path);
            if (!is_float($p)) {
                $p = intval($p)/100;
            }
            $width = round($size[0]*$p);
            $height = round($size[1]*$p);
        }

        $new_image = imagecreatetruecolor($width, $height);
        if (!isset($target)) {
            $target = dirname($path).DS;
            $target .= preg_replace('/-[0-9]+x[0-9]+/', '', pathinfo($path, PATHINFO_FILENAME));
            $target .= '-'.$width.'x'.$height.'.'.pathinfo($path, PATHINFO_EXTENSION);
            $r = $target;
        }

        if (in_array($type, array('image/gif', 'gif', 'image/png', 'png'))) {
            imagealphablending($new_image, false);
            imagesavealpha($new_image,true);
            $transparent = imagecolorallocatealpha($new_image, 255, 255, 255, 127);
            imagefilledrectangle($new_image, 0, 0, $width, $height, $transparent);
        }

        imagecopyresampled(
            $new_image,
            $original, 
            0, 0, 0, 0,
            $width,
            $height,
            imagesx($original),
            imagesy($original)
        );

        switch ($type) {
            case 'image/gif':
            case 'gif':
                $t = imagegif($new_image, $target);
                break;
            case 'image/png':
            case 'png':
                $t = imagepng($new_image, $target, 0, PNG_NO_FILTER);
                break;
            default:
                $t = imagejpeg($new_image, $target, 100);
                break;
        }
        if (!isset($r)) {
            $r = $t;
        }
        imagedestroy($original);
        imagedestroy($new_image);
        return $r;
    }

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
        $r = new \stdClass();
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
            if (!class_exists('phpQuery')) {
                throw new THETOOLSException("phpQuery not available");
            } else {
                self::$s_phpQueryInitiated = true;
            }
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
        return \phpQuery::newDocument($html, $contentType);
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
    private static function _is_browser($browser, $version = 'x', $operator = '>=')
    {
        self::get_browserObj();
        if (class_exists('Ikimea\Browser\Browser') &&
            self::$s_browser == self::get_classConstant('Ikimea\Browser\Browser', 'BROWSER_'.$browser)
        ) {
            if ($version === 'x' || $version === '.x') {
                if ($operator === '!=') {
                    return false;
                } else {
                    return true;
                }
            }

            if (strpos($version, '.x') !== -1 && $operator === '==') {
                return (
                    self::softerVersionCompare(
                        self::$s_browserVersion,
                        str_replace('.x', '.0', $version),
                        '>='
                    ) && self::softerVersionCompare(
                        self::$s_browserVersion,
                        str_replace('.x', '.99999999', $version),
                        '<='
                    )
                );
            } elseif (strpos($version, '.x') !== -1 && strpos($operator, '>') !== -1) {
                $version = str_replace('.x', '.0', $version);
            } elseif (strpos($version, '.x') !== -1 && strpos($operator, '<') !== -1) {
                $version = str_replace('.x', '.99999999', $version);
            }

            return self::softerVersionCompare(self::$s_browserVersion, $version, $operator);
        }
        return false;
    }

    /**
     * By Sina Salek - http://php.net/manual/de/function.version-compare.php#92935
     * 
     * @param  string $version1
     * @param  string $version2
     * @param  string $operand
     * @return boolean
     */
    public static function softerVersionCompare($version1, $version2, $operand) {
        $v1Parts = explode('.', $version1);
        $version1 .= str_repeat('.0', 3-count($v1Parts));
        $v2Parts = explode('.', $version2);
        $version2 .= str_repeat('.0', 3-count($v2Parts));
        $version1 = str_replace('.x', '.1000', $version1);
        $version2 = str_replace('.x', '.1000', $version2);       
        return version_compare($version1, $version2, $operand);
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
        $phones = 'iph||ipo||ga||bb';
        $mobile =  $phones.'||ipa';

        if ($sBrowsers == 'phone') {
            return self::is_browser($phones);
        } elseif ($sBrowsers == 'no-phone') {
            return !self::is_browser($phones);
        } else if ($sBrowsers == 'mobile') {
            return self::is_browser($mobile);
        } elseif ($sBrowsers == 'desktop') {
            return !self::is_browser($mobile);
        }

        foreach (explode('||', $sBrowsers) as $browser) {
            // $browser = 'ie';
            $m;
            preg_match('/([a-z]+)([<>!=]+)?([0-9.\*x]+)?/', $browser, $m);
            

            if (count($m) >= 2) {
                $browserKey = $m[1];
            }

            switch (count($m)) {
            case 4:
                $operator = $m[2];
                $version = $m[3];
                break;
            case 3:
                if (is_numeric($m[2])) {
                    $operator = '>=';
                    $version = $m[2];
                } else {
                    $error = sprintf(__('Missing operator in browserstring "%s" in THETOOLS::is_browser()', 'themaster'), $browser);
                }
                break;
            case 2:
                $operator = '==';
                $version = 'x';
                break;
            default:
                $error = sprintf(__('Invalid format of browserstring "%s" in THETOOLS::is_browser()', 'themaster'), $browser);
                break;
            }

            if (empty($error) && !isset(self::$s_browserArray[$browserKey])) {
                $error = sprintf(
                    __('Unknown Browserkey "%s" in THETOOLS::is_browser()', 'themaster'),
                     $browserKey
                );
            }


            if (!empty($error)) {
                throw new THETOOLSException($error);
            }

            if (empty($operator)) {
                $operator = '>=';
            }

            if (empty($version)) {
                $version = 'x';
            } else {
                $version = str_replace('*', 'x', $version);
            }

            if (self::_is_browser(self::$s_browserArray[$browserKey], $version, $operator) === true) {
                return true;
            }
        }
        return false;
    }

    /**
     * Generates and caches the Browser-Object
     *
     * @access private
     * @return object
     */
    public static function get_browserObj()
    {
        if (!class_exists('\Ikimea\Browser\Browser')) {
            throw new THETOOLSException("Browser Class could not be loaded");
        }

        if (self::$s_BrowserObj === null) {
            self::$s_BrowserObj = new \Ikimea\Browser\Browser;
            self::$s_browserVersion = self::$s_BrowserObj->getVersion();
            self::$s_browser = self::$s_BrowserObj->getBrowser();
        }

        return self::$s_BrowserObj;
    }

    /**
     * Unset the current browser object for testing purposes.
     * 
     * @return void
     */
    public static function unset_browserObj() {
        self::$s_BrowserObj = null;
    }

    /**
     * Getter for the Browser.
     *
     * @access public
     * @return string
     */
    public static function get_browser()
    {
        self::get_browserObj();
        return self::$s_browser;
    }

    /**
     * Getter for the Browser Version.
     *
     * @access public
     * @return string
     */
    public static function get_browserVersion()
    {
        self::get_browserObj();
        return self::$s_browserVersion;
    }

    /**
     * Getter for the Browsers Layout Engine.
     *
     * @access public
     * @return string
     */
    public static function get_layoutEngine()
    {
        self::get_browserObj();
        switch (self::$s_browser) {
            case 'iCab':
            case 'OmniWeb':
            case 'Safari':
            case 'iPhone':
            case 'iPod':
            case 'iPad':
            case 'Chrome':
            case 'Android':
            case 'BlackBerry':
                return 'WebKit';

            case 'Firebird':
            case 'Firefox':
            case 'Iceweasel':
            case 'Mozilla':
            case 'IceCat':
            case 'Galeon':
                return 'Gecko';

            case 'Internet Explorer':
            case 'Pocket Internet Explorer':
                return 'Trident';

            case 'Opera':
            case 'Opera Mini':
                return 'Presto';
            
            case 'Konqueror':
                return 'KHTML';

            case 'Amaya':
                return 'Amaya';

            default:
                return false;
        }
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
     * @param string|array $filter part or parts of the url structure that should be ignored.
     *
     * @return string the current URL
     */
    public static function get_currentUrl($filter = array(), $queryFilter = array(), $queryFilterMethod = 'remove')
    {
        if (!is_array($filter) && !empty($filter)) {
            $filter = array($filter);
        }

        if (!empty(self::$fakeCurrentUrl)) {
            $pageURL = self::$fakeCurrentUrl;
        } else {
            $pageURL = self::get_realCurrentUrl();
        }

        if (empty($filter)) {
            return $pageURL;
        }
        $urlParts = parse_url($pageURL);

        if (!in_array('http', $filter)) {
            $pageURL = preg_replace('#.*://#', '', $pageURL);
        }

        if (!in_array('name', $filter) && !in_array('host', $filter) && array_key_exists('host', $urlParts)) {
            $pageURL = str_replace($urlParts['host'], '', $pageURL);
        }

        if (!in_array('port', $filter) && array_key_exists('port', $urlParts)) {
            $pageURL = str_replace(':'.$urlParts['port'], '', $pageURL);
        }
        
        if (!in_array('path', $filter) && array_key_exists('path', $urlParts)) {
            $pageURL = str_replace($urlParts['path'], '', $pageURL);
        }

        if ((in_array('request', $filter) || in_array('query', $filter)) && array_key_exists('query', $urlParts)) {
            if (!empty($queryFilter) && is_array($queryFilter)) {
                parse_str($urlParts['query'], $query);
                

                self::filter_data($query, $queryFilter, $queryFilterMethod);

                $pageURL = explode('?', $pageURL);

                if (empty($query)) {
                    unset($pageURL[1]);
                } else {
                    $pageURL[1] = http_build_query($query);
                }

                $pageURL = implode('?', $pageURL);
            }
        } else {
            $pageURL = preg_replace('/\?.*/', '', $pageURL);
        }

        return $pageURL;
    }

    /**
     * Get the current url based on $_SERVER
     *
     * by http://www.webcheatsheet.com/PHP/get_current_page_url.php
     *
     * @return [type] [description]
     */
    public static function get_realCurrentUrl()
    {
        $pageURL = 'http';
        if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {
            $pageURL .= "s";
        }
        $pageURL .= "://".$_SERVER["SERVER_NAME"];
        if (isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] != "80") {
            $pageURL .= $_SERVER["SERVER_PORT"];
        }
        $pageURL .= $_SERVER["REQUEST_URI"];
        return $pageURL;
    }

    /**
     * Set an url that will be used by get_currentUrl instead of the real current url.
     *
     * @param string $url
     */
    public static function set_currentUrl($url)
    {
        self::$fakeCurrentUrl = $url;
    }

    /**
     * Remove a faked current url set by set_currentUrl()
     *
     * @return void
     */
    public static function reset_currentUrl()
    {
        self::$fakeCurrentUrl = false;
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
     * Internal method for the slasher/deslasher methods.
     * 
     * @param  string  $str   the target.
     * @param  string  $slash the slash.
     * @param  boolean $pre   start or end of target?
     * @param  boolean $add   add slash if not present?
     * @param  boolean $unify unify all slashes in target?
     * @return string         the (un)slashed target.
     */
    private static function _slash($str, $slash, $pre, $add, $unify)
    {
        if ($unify) {
            $str = self::unify_slashes($str, $slash);
        }

        $r  = ($add && $pre ? $slash : '');
        $r .= ($pre ? ltrim($str, $slash) : rtrim($str, $slash));
        $r .= ($add && !$pre ? $slash : '');

        return $r;
    }

    /**
     * remove potential slash from the end of the string.
     *
     * @access public
     * @param  string  $str    the string to be unslashed
     * @param  boolean $unify  set true to unify all slashes in $str
     * @return string          the unslashed string
     */
    public static function unSlash($str, $unify = false)
    {
        return self::_slash($str, '/', false, false, $unify);
    }

    /**
     * remove potential DIRECTORY_SEPARATOR from the end of the string.
     *
     * @access public
     * @param  string  $str    the string to be unslashed
     * @param  boolean $unify  set true to unify all slashes in $str
     * @return string          the unslashed string
     */
    public static function unDS($str, $unify = false)
    {
        return self::_slash($str, DS, false, false, $unify);
    }

    /**
     * remove potential backslash from the end of the string.
     *
     * @access public
     * @param  string  $str    the string to be unbackslashed
     * @param  boolean $unify  set true to unify all slashes in $str
     * @return string          the unbackslashed string
     */
    public static function unBackSlash($str, $unify = false)
    {
        return self::_slash($str, '\\', false, false, $unify);
    }

    /**
     * add slash to the end of the string.
     *
     * @access public
     * @param  string  $str    the string to be slashed
     * @param  boolean $unify  set true to unify all slashes in $str
     * @return string          the slashed string
     */
    public static function slash($str, $unify = false)
    {
        return self::_slash($str, '/', false, true, $unify);
    }

    /**
     * add DIRECTORY_SEPARATOR to the end of the string.
     *
     * @access public
     * @param  string  $str    the string to be slashed
     * @param  boolean $unify  set true to unify all slashes in $str
     * @return string          the slashed string
     */
    public static function DS($str, $unify = false)
    {
        return self::_slash($str, DS, false, true, $unify);
    }

    /**
     * add backslash to the end of the string.
     *
     * @access public
     * @param  string  $str    the string to be backslash
     * @param  boolean $unify  set true to unify all slashes in $str
     * @return string          the backslashed string
     */
    public static function backSlash($str, $unify = false)
    {
        return self::_slash($str, '\\', false, true, $unify);
    }

    /**
     * remove potential slash from the front of the string.
     *
     * @access public
     * @param  string  $str    the string to be unslashed
     * @param  boolean $unify  set true to unify all slashes in $str
     * @return string          the unslashed string
     */
    public static function unPreSlash($str, $unify = false)
    {
        return self::_slash($str, '/', true, false, $unify);
    }

    /**
     * remove potential DIRECTORY_SEPARATOR from the front of the string.
     *
     * @access public
     * @param  string  $str    the string to be unslashed
     * @param  boolean $unify  set true to unify all slashes in $str
     * @return string          the unslashed string
     */
    public static function unPreDS($str, $unify = false)
    {
        return self::_slash($str, DS, true, false, $unify);
    }

    /**
     * remove potential backslash from the front of the string.
     *
     * @access public
     * @param  string  $str    the string to be unbackslashed
     * @param  boolean $unify  set true to unify all slashes in $str
     * @return string          the unbackslashed string
     */
    public static function unPreBackSlash($str, $unify = false)
    {
        return self::_slash($str, '\\', true, false, $unify);
    }

    /**
     * add slash to the front of the string.
     *
     * @access public
     * @param  string  $str    the string to be slashed
     * @param  boolean $unify  set true to unify all slashes in $str
     * @return string          the slashed string
     */
    public static function preSlash($str, $unify = false)
    {
        return self::_slash($str, '/', true, true, $unify);
    }

    /**
     * add DIRECTORY_SEPARATOR to the front of the string.
     *
     * @access public
     * @param  string  $str    the string to be slashed
     * @param  boolean $unify  set true to unify all slashes in $str
     * @return string          the slashed string
     */
    public static function preDS($str, $unify = false)
    {
        return self::_slash($str, DS, true, true, $unify);
    }

    /**
     * add backslash to the front of the string.
     *
     * @access public
     * @param  string  $str    the string to be backslashed
     * @param  boolean $unify  set true to unify all slashes in $str
     * @return string          the backslashed string
     */
    public static function preBackSlash($str, $unify = false)
    {
        return self::_slash($str, '\\', true, true, $unify);
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
     * Deletes ../ in pathes and cleans it with self::unify_slashes()
     *
     * @access public
     * @param  string $path input path.
     * @return string
     */
    public static function get_directPath($path)
    {
        return str_replace('..' . DS, '', self::unify_slashes($path, DS));
    }
    
    /** 
     * Replaces / & \ to DIRECTORY_SEPERATOR in $path
     *
     * @access public
     * @param string $path input path
     * @return string
     */
    public static function get_cleanedPath($path)
    {
        deprecated('unify_slashes');
        return self::unify_slashes($path, DS);
    }
    
    /** 
     * Replaces / & \ to $slash in $path
     *
     * @access public
     * @param string  $path        input path
     * @param string  $slash       the new slash char default: DIRECTORY_SEPERATOR
     * @param boolean $allowDouble set true to allow multiple slashes following each other. (//)
     * 
     * @return string
     */
    public static function unify_slashes($path, $slash = DS, $allowDouble = false)
    {
        if ($allowDouble) {
            return preg_replace("/[\/\\\]/", $slash, $path);
        } else {
            return preg_replace("/[\/\\\]+/", $slash, $path);
        }
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
        $Class = new \ReflectionClass($className);
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
     * @param  string  $dir         the directory.
     * @param  mixed   $filenameKey set true to use the filename as array key.
     * @param  array   $filter      an array with strings. Files starting with this string will be ignored.
     * @param  boolean $positive    set true to invert the filter. Only files not starting
     *                              with the filter string will be ignored
     *
     * @return array
     */
    public static function get_dirArray($dir, $filenameKey = false, $filter = null, $positive = false)
    {
        /*
         * Normalize the Filter.
         */
        if (empty($filter)) {
            $filter = array('.', '_');
        } elseif(is_object($filter)) {
            $filter = self::ar($filter, null, true);
        } elseif(!is_array($filter)) {
            $filter = array($filter);
        }

        if ($handle = opendir($dir)) {
            $r = array();
            $i = 0;
            while (false !== ($file = readdir($handle))) {
                $ok = !$positive;
                if (!empty($filter)) {
                    foreach ($filter as $f) {
                        if ($positive && strpos($file, $f) === 0) {
                            $ok = true;
                            break;
                        } elseif(!$positive && strpos($file, $f) === 0) {
                            $ok = false;
                            break;
                        }
                    }
                }
                if (!$ok) {
                    continue;
                }

                $k = $filenameKey ? $file : $i;
                $r[$k] = $file;
                $i++;
            }
            return $r;
        } else {
            return false;
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
    public static function getRequiredArgsError($args, $requiredArgs)
    {
        if (!is_array($args) && !is_object($args)) {
            return sprintf(__('invalid type "%s" for $args', 'themaster'), gettype($args));
        }
        if (!is_array($requiredArgs) && !is_object($args)) {
            return sprintf(__('invalid type "%s" for $requiredArgs', 'themaster'), gettype($requiredArgs));
        }

        $missing = array();
        foreach ($requiredArgs as $req) {
            if (!@isset($args[$req]) && !@isset($args->$req)) {
                $missing[] = $req;
            }
        }
        
        if (count($missing) == 0) {
            return false;
        } else {
            return sprintf(
                __('Missing %s as %s.', 'themaster'),
                self::readableList(
                    $missing,
                    ' '.__('and').' ',
                    ', ',
                    null,
                    '"'
                ),
                (count($missing) === 1 ? 'a key' : 'keys')
            );
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
     * Parses an url adds and removes get-query arguments and rebuilds the url.
     *
     * @param string $url       the url string
     * @param array  $filterArr Array of query keys that should be removed or kept.
     * @param string $method    'remove' deletes all $filterArr keys from query,
     *                          'keep' deletes all args that are not in $filterArr
     * @param array  $add       optional array of values to be added to the query
     * @return void
     */
    public static function filter_urlQuery(&$url, $filterArr, $method = null, array $add = array()) {
        if ($method === null) {
            $method = 'remove';
        }

        $pUrl = parse_url($url);
        $qry;
        if (isset($pUrl['query'])) {
            parse_str($pUrl['query'], $qry);
        } else {
            $qry = array();
        }
        
        self::filter_data($qry, $filterArr, $method, $add);

        $pUrl['query'] = http_build_query($qry);
        if (empty($pUrl['query'])) {
            unset($pUrl['query']);
        }
        $url = self::unparse_url($pUrl);
    }

    /**
     * Filters entries of an array.
     *
     * @param string $data      the source
     * @param array  $filterArr Array of keys keys that should be removed or kept.
     * @param string $method    'remove' deletes all $filterArr keys from $data,
     *                          'keep' deletes all keys that are not in $filterArr
     * @param array $add        optional array of values to be added to $data
     * @return array|object
     */
    public static function filter_data(&$data, $filterArr, $method = null, $add = false)
    {
        if ($method === null) {
            $method = 'keep';
        }

        foreach ($data as $k => $v) {
            if (($method == 'remove' && in_array($k, $filterArr))
             || ($method == 'keep' && !in_array($k, $filterArr))
            ) {
                if (is_array($data)) {
                    unset($data[$k]);
                } elseif (is_object($data)) {
                    unset($data->$k);
                }
            }
        }

        if (!empty($add)) {
            $data = self::merge_data($data, $add);
        }

        return $data;
    }

    /**
     * Allows merging of arrays and objects. same usage
     * as array_merge.
     *
     * @param array|object $a target
     * @param array|object $b addition
     *
     * @return array|object same type as $a
     */
    public static function merge_data($a, $b)
    {
        $args = func_get_args();
        $t = $args[0];
        $array = is_array($t);

        foreach ($args as $k => $a) {
            $args[$k] = self::ar($a);
        }

        $args = call_user_func_array('array_merge', $args);
        
        if (!$array) {
            $args = self::sc($args);
        }
        
        return $args;
    }

    /**
     * Allows deep merging of arrays and objects. same usage
     * as array_merge_recursive.
     *
     * @param array|object $a target
     * @param array|object $b addition
     *
     * @return array|object same type as $a
     */
    public static function merge_data_recursive($a, $b)
    {
        $args = func_get_args();
        $t = $args[0];
        $array = is_array($t);

        foreach ($args as $k => $a) {
            $args[$k] = self::ar($a, null, true);
        }

        $args = call_user_func_array('array_merge_recursive', $args);
        
        if (!$array) {
            $args = self::sc($args, null, true);
        }

        return $args;
    }

    /**
     * returns an array containing the keys of $data, starting with the given prefix.
     * 
     * $data = array( 'foo_bar1' => 'bar, 'bar_bar2' => 'foo' );
     * filtered by $match = 'foo' will return array( 'foo_bar1' => 'bar );
     *
     * @access public
     * @param  string $prefix  the beginning of the $data keys that should be returned
     * @param  mixed  $data    the data.
     * @param  array  $filter  array of keys that are expected and their types as value
     * @return array
     */
    public static function filter_data_by($prefix, $data, $filter = false)
    {
        $args = array();
        if ($filter == false) {
            foreach ($data as $key => $value) {
                if (substr($key, 0, 1) == '_') {
                    $key = substr($key, 1, strlen($key));           
                }
                if (strlen($key) > strlen($prefix)
                 && substr($key, 0, strlen($prefix)) == $prefix
                ) {
                    $args[str_replace($prefix.'_', '', $key)] = $value;
                }
            }
        } else {
            foreach ($filter as $name => $filterKey) {
                if (isset($data[$prefix.'_'.$name])) {
                    $args[$name] = filter_var(
                        $data[$prefix.'_'.$name],
                        self::$s_filters[$filterKey]
                    );
                } else {
                    $args[$name] = null;
                }
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
     * @param  string $prefix  the beginning of the $_POST keys that should be returned
     * @param  array  $filter  array of keys that are expected and their types as value
     * @return array
     */
    public static function filter_postDataBy($prefix, $filter = false)
    {
        return self::filter_data_by($prefix, $_POST, $filter);
    }

    /**
     * returns an array containing the keys of $_GET, starting with the given prefix.
     * 
     * $_GET = array( 'foo_bar1' => 'bar, 'bar_bar2' => 'foo' );
     * filtered by $match = 'foo' will return array( 'foo_bar1' => 'bar );
     *
     * @access public
     * @param  string $prefix  the beginning of the $_GET keys that should be returned
     * @param  array  $filter  array of keys that are expected and their types as value
     * @return array
     */
    public static function filter_getDataBy($prefix, $filter = false)
    {
        return self::filter_data_by($prefix, $_GET, $filter);
    }

    /**
     * returns an array containing the keys of $_REQUEST, starting with the given prefix.
     * 
     * $_REQUEST = array( 'foo_bar1' => 'bar, 'bar_bar2' => 'foo' );
     * filtered by $match = 'foo' will return array( 'foo_bar1' => 'bar );
     *
     * @access public
     * @param  string $prefix  the beginning of the $_REQUEST keys that should be returned
     * @param  array  $filter  array of keys that are expected and their types as value
     * @return array
     */
    public static function filter_requestDataBy($prefix, $filter = false)
    {
        return self::filter_data_by($prefix, $_REQUEST, $filter);
    }
}

class THETOOLSException extends \Exception {
}