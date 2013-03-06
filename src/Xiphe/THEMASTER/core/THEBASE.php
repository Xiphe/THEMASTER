<?php
namespace Xiphe\THEMASTER\core;

use Xiphe as X;

/**
 * THEBASE is the backbone of !THE MASTER
 *
 * @copyright Copyright (c) 2013, Hannes Diercks
 * @author    Hannes Diercks <xiphe@gmx.de>
 * @version   3.0.2
 * @link      https://github.com/Xiphe/THEMASTER/
 * @package   THEMASTER
 */
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
    private static $s_registeredJsVars = array(
        'admin' => array(),
        'front' => array()
    );

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
    private static $s_singletons = array();

    private static $s_themastersInitArgs = array();
    /* PUBLIC */

    /**
     * The namespace of !THEMASTER
     *
     * @access public
     * @var string
     */
    public static $sNameSpace = 'Xiphe\THEMASTER';

    /**
     * The version of !THEMASTER
     *
     * @access public
     * @var string
     */
    public static $sVersion;

    /**
     * The basepath of !THEMASTER.
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

    public static $X;

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
     * Array of temporary globals used by THEBASE::_unset_temp_globals(),
     * THEBASE::_set_temp_globals() and THEMASE::_register_temp_global()
     *
     * @access private
     * @var    array 
     */
    private $_temp_globals = array();


    /* PROTECTED */

    /**
     * Backup for new class definitions.
     * A Master can overwrite this variable to add his own required
     * initiation keys.
     *
     * @access protected
     * @var    array
     */
    protected $requiredInitArgs = array();
    
    /**
     * Holds the original Array of initiation arguments passed on construction.
     *
     * @access protected
     * @var    array
     */ 
    protected $_mastersInitArgs = array();
    
    /**
     * Subclasses can overwrite this to add init args to own instantiation.
     *
     * @access  protected
     * @var     array
     */
    protected $_initArgs = array();


    /* ---------------------- *
     *  CONSTRUCTION METHODS  *
     * ---------------------- */
    
    /** 
     * The Constructor gets called by every subclass
     *
     * @access public
     * @param  array $initArgs
     * @return mixed returns false if a required initiation Argument is missing
     *         or Instance if Subclass is Singleton
     */
    function __construct($initArgs)
    {
        /*
         * Prevent direct instancing of THEBASE.
         */
        if (!isset( $this->constructing ) || $this->constructing !== true) {
            throw new THEBASEException("ERROR: THEBASE is not ment to be constructed directly.");
            return false;
        } else {
            unset($this->constructing);
        }

        /*
         * Do one-time static initiation.
         */
        self::s_init();

        /*
         * Short initiation for MINIMASTER
         */
        if ($initArgs === 'MINIMASTER') {
            return $this;
        }
        
        /*
         * Merge required initiation keys and check for missing keys in $initArgs.
         */
        $reqInitArgs = array_merge(
            $this->_requiredInitArgs,
            $this->requiredInitArgs
        );
        $err = X\THETOOLS::getRequiredArgsError( $initArgs, $reqInitArgs );
        

        if ($err === false) {
            /*
             *  no error -> Set Init args as Class Variables
             */
            foreach ($initArgs as $key => $value) {
                $this->$key = $value;
            }

            /*
             * initiate the Object
             */
            if (isset($this->isMaster) && $this->isMaster === true) {
                /*
                 * Special Master initiation for masters
                 */
                unset($initArgs['isMaster']);
                $this->_mastersInitArgs = $initArgs;
                self::sdo_callback('beforeMasterInit', $this);
                $this->_masterInit();
            } else {
                /*
                 * Normal initiation for non-masters
                 */
                self::sdo_callback(
                    'initiated',
                    $this,
                    array('class' => get_class($this))
                );
            }
        } else {
            /*
             * Missing initiation keys -> throw error.
             */
            $msg = sprintf(
                '<strong>!THEMASTER - Required args Error:</strong> "%1$s" in %2$s "%3$s"',
                $err,
                isset($initArgs['projectType']) ? $initArgs['projectType'] : __('Project', 'themaster'),
                isset($initArgs['projectName']) ?  $initArgs['projectName'] : __('Unknown', 'themaster')
            );
            throw new THEBASEException($msg);
            return false;
        }
        return $this;
    }

    private static function s_init() {
        if( !self::$s_initiated ) {
            X\THETOOLS::session();

            self::$s_themastersInitArgs['projectFile']
                = self::$sProjectFile = THEMASTER_PROJECTFILE;

            $bp = dirname(self::$sProjectFile).DS;

            if (X\THEMASTER\WP()) {
                $bp = dirname(X\THEMASTER\get_wpInstallPath(self::$sProjectFile)).DS;
            }

            self::$s_themastersInitArgs['basePath']
                = self::$sBasePath = $bp;
            self::$s_themastersInitArgs['folderName']
                = self::$sFolderName = basename(self::$sBasePath);
            self::$s_themastersInitArgs['textdomain']
                = self::$sTextdomain = 'themaster';
            self::$s_themastersInitArgs['textID']
                = self::$sTextID = self::$sFolderName.'/'.basename(self::$sProjectFile);

            /* Pass settings to THEDEBUG */
            $toggle = THESETTINGS::sGet_setting('debug', self::$sTextID) ? 'enable' : 'disable';
            call_user_func(array('Xiphe\THEDEBUG', $toggle));
            X\THEDEBUG::$modus = THESETTINGS::sGet_setting('debugMode', self::$sTextID);
            X\THEDEBUG::$ensureByGet = THESETTINGS::sGet_setting('debugGet', self::$sTextID);

            if (!defined('DOING_AJAX') && !isset($_REQUEST['ajax'])) {
                X\THEDEBUG::debug(__(sprintf('Debug is on and Mode is set to %s.', X\THEDEBUG::$modus)), 2);
            }

            self::$s_themastersInitArgs['namespace'] = 'Xiphe\\THEMASTER';
            self::$s_themastersInitArgs['projectName'] = '!THE MASTER';
            self::$s_themastersInitArgs['updatable'] = true;

            if (class_exists(THE::WPBUILDER)) {
                self::$sVersion = THEWPBUILDER::get_initArgs(THEMASTER_PROJECTFILE,false);
                self::$s_themastersInitArgs['version']
                    = self::$sVersion = self::$sVersion['version'];
            }

            if( function_exists( 'plugins_url' )) {
                self::$s_themastersInitArgs['baseUrl']
                    = self::$sBaseUrl = X\THETOOLS::slash(plugins_url('_'.self::$sTextdomain));
            }
            
            if( THESETTINGS::sGet_setting( 'errorReporting', self::$sTextID ) === true ) {
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
        if( isset( self::$s_singletons[$name] )
         && is_object( ( $r = self::$s_singletons[$name] ) ) 
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

    public static function _masterDeactivate() {
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
            $this->_requiredInitArgs[] = $initArgs;
        }
    }
    
    /** Getter for the Singleton Instance of Class
     *
     * @return mixed The instance or false
     * @date Nov 10th 2011
     */
    public static function inst() {
        $called = get_called_class();
        if (isset(self::$s_singletons[$called])) {
            return self::$s_singletons[$called];
        } elseif (isset(self::$s_singletons['master'])) {
            return self::$s_singletons['master'];
        } elseif (defined('THEMINIWPMASTERAVAILABLE')) {
            return $GLOBALS['THEMINIWPMASTER'];
        } elseif (defined('THEMINIMASTERAVAILABLE')) {
            return $GLOBALS['THEMINIMASTER'];
        }
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
    public static function extr()
    {
        $called = get_called_class();

        if (
            isset(self::$s_singletons[$called])
        ) {
            if (!is_object(self::$s_singletons[$called])) {
                return false;
            }
            $inst = self::$s_singletons[$called];
        } elseif (class_exists($called))
            $inst = self::get_instance( $called );
        else  {
            return false;
        }
        
        $called = explode('\\', $called);
        $called = $called[count($called)-1];

        if (isset($inst->HTML)) {
            return array('HTML' => $inst->HTML, $called => $inst);
        } else {
            return array($called => $inst);
        }
    }
    
    
    
    /** Initiation for a new Instance of THEMASTER, generates a new Submaster XYMaster
     *
     * @param array $initArgs see $this->requiredInitArgs for required keys
     * @return void
     * @access private
     * @date Jul 28th 2011
     */
    protected function _masterInit() {
        return true;
    }

    protected function _masterInitiated()
    {
        if ($this->_masterInitiated !== true) {
            self::sdo_callback('initiated', $this, array('class' => get_class($this)));
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
    public function view($view, $args = null, $temp = null)
    {
        echo $this->get_view($view, $args, $temp);
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
    public function get_view($view, $args = array(), $temp = null)
    {
        try {
            /*
             * If called through Wordpress, the first param is the post object
             * not the view path.
             *
             * Example.
             * add_meta_box(
             *   'id',
             *   'Title',
             *   array($this, 'view'),
             *   'posttype',
             *   'advanced',
             *   'core',
             *   array('viewfile', 'foo' => 'bar', 'mooh' => 'something')
             * );
             */
            if (is_object($view)) {
                $page = $view;
                $view = $args;
            }
            /*
             * The view then might be the compact wp call
             */
            if (is_array($view)) {
                $temp = $view;
                /*
                 * check if arguments are array (additionals passed)
                 * and extract the first entry (the view file path)
                 */
                if (is_array($temp['args'])) {
                    $view = $temp['args'][0];
                    unset($temp['args'][0]);
                    $args = !empty($temp['args']) ? $temp['args'] : array();
                } else {
                    /*
                     * No additional arguments passed, args = viewfile.
                     */
                    $view = $temp['args'];
                    unset($temp['args']);
                    $args = $temp;
                }
                if (isset($page)) {
                    $args['Page'] = $page;
                }
            }
            
            foreach (array($this->basePath, self::$sBasePath ) as $basePath) {
                $file = $basePath.'views'.DS.X\THETOOLS::get_directPath($view).'.php';

                if (file_exists($file)) {
                    if (!is_array($args)) {
                        $args = array($args);
                    }

                    $class = explode('\\', get_class($this));
                    $class = $class[count($class)-1];
                    $ar = array($class => $this);
                    if (($HTML = $this->get_HTML())) {
                        $ar['HTML'] = $HTML;
                    }

                    if ($this->namespace === "Xiphe\THEMASTER") {
                        $ar['Master'] = THEMASTER::inst();
                    } else {
                        $ar['Master'] = call_user_func(array($this->namespace.'\classes\Master', 'inst'));
                    }

                    extract(array_merge(
                        $ar, 
                        $args
                    ));

                    ob_start();
                    include $file;
                    return ob_get_clean();
                }
            }

            throw new THEBASEException('Error: View File not Found (' . $file . ')');
        } catch(THEBASEException $e) {
            X\THEDEBUG::debug($e);
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
    final public function get_HTML( $silence = false ) {
        if( !isset( $this ) || !isset( $this->HTML ) ) {
            return self::sget_HTML( $silence );
        } else {
            return $this->HTML;
        }
    }
        
    final public static function sget_HTML( $silence = false ) {
        if( isset( $GLOBALS['HTML'] ) )
            return $GLOBALS['HTML'];
        elseif( $silence !== true )
            throw new THEBASEException( "HTML Class Needed but not available.", 1 );
        else
            return false;
    }
    
    final public function reg_jsVar($name, $var, $global = false) {
        self::s_reg_jsVar($name, $var, $global, $this->namespace, false);
    }

    final public function reg_adminJsVar($name, $var, $global = false) {
        self::s_reg_jsVar($name, $var, $global, $this->namespace, true);
    }
    
    final public static function sRegJsVar($name, $var, $global = false) {
        self::s_reg_jsVar($name, $var, $global, self::$sNameSpace, false);
    }

    final public static function sRegAdminJsVar($name, $var, $global = false) {
        self::s_reg_jsVar($name, $var, $global, self::$sNameSpace, true);
    }

    final public function reg_coffee($filename, $args = array()) {
        $this->_reg_source('coffee', $filename, $args);
    }
    final public function reg_css($filename, $args = array()) {
        $this->_reg_source('css', $filename, $args);
    }
    final public function reg_js($filename, $args = array()) {
        $this->_reg_source('js', $filename, $args);
    }
    final public function reg_less($filename, $args = array()) {
        $this->_reg_source('less', $filename, $args);
    }


    final public function reg_adminCoffee($filename, $args = array()) {
        $this->_reg_source('coffee', $filename, $args, true);
    }
    final public function reg_adminJs($filename, $args = array()) {
        $this->_reg_source('js', $filename, $args, true);
    }
    final public function reg_adminCss($filename, $args = array()) {
        $this->_reg_source('css', $filename, $args, true);
    }
    final public function reg_adminLess($filename, $args = array()) {
        $this->_reg_source('less', $filename, $args, true);
    }
    
    
    final public static function sReg_coffee($filename, $args = array()) {
        self::_reg_source('coffee', $filename, $args);
    }
    final public static function sReg_js($filename, $args = array()) {
        self::_reg_source('js', $filename, $args);
    }
    final public static function sReg_css($filename, $args = array()) {
        self::_reg_source('css', $filename, $args);
    }
    final public static function sReg_less($filename, $args = array()) {
        self::_reg_source('less', $filename, $args);
    }

    final public static function sReg_adminCoffee($filename, $args = array()) {
        self::_reg_source('coffee', $filename, $args, true);
    }
    final public static function sReg_adminJs($filename, $args = array()) {
        self::_reg_source('js', $filename, $args, true);
    }
    final public static function sReg_adminCss($filename, $args = array()) {
        self::_reg_source('css', $filename, $args, true);
    }
    final public static function sReg_adminLess($filename, $args = array()) {
        self::_reg_source('less', $filename, $args, true);
    }

    private static function s_reg_jsVar($name, $var, $global, $namespace, $admin = false) {
        $r = array();

        if ($admin) {
            $r['admin'] = array();
            $rl = &$r['admin'];
        } else {
            $r['front'] = array();
            $rl = &$r['front'];
        }

        if (!$global) {
            foreach (explode('\\', $namespace) as $k) {
                $k = strtolower($k);
                $rl[$k] = array();
                $rl = &$rl[$k];
            }
            $rl[$name] = $var;
        } else {
            $rl[$name] = $var;
        }

        self::$s_registeredJsVars = array_merge_recursive(self::$s_registeredJsVars, $r);
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
    private function _reg_source($source, $filename, $args = array(), $admin = false)
    {
        /*
         * Check if source is for backend or frontend.
         */
        $foa = ($admin ? 'admin' : 'front');

        /*
         * Check if source is url, register it and skip the rest if true.
         */
        if (in_array(substr($filename, 0, 7), array('http://', 'https:/'))) {
            self::$s_registeredSources[$foa][$source][$filename] = $filename;
            return true;
        }

        /*
         * Handle allowed arguments.
         */
        foreach (array('vars', 'folder', 'setTime') as $arg) {
            $$arg = isset($args[$arg]) ? $args[$arg] : false;
        }
        unset($args);

        $isRes = false;
        if (substr($filename, 0, 3) === '../') {
            $isRes = true;
        }

        /*
         * Disable ../ in filenames.
         */
        $filename = X\THETOOLS::get_directPath($filename);

        /*
         * Check if the filename contained a sub-folder.
         */
        $subfolder = dirname( $filename );
        if ($subfolder == '.') {
            $subfolder = '';
        } else {
            $subfolder .= DS;
        }

        /*
         * Strip potential sub-folder from filename.
         */
        $filename = basename( $filename );

        /*
         * If called on instance include the masters basePath and baseUrl into the
         * possible Paths.
         */
        if(isset($this)) {
            $paths[$this->basePath] = $this->baseUrl;
        }

        /*
         * Add !THE MASTERs basePath and BaseUrl as fall-back.
         */
        $paths[THEBASE::$sBasePath] = THEBASE::$sBaseUrl;

        $ePaths = array();

        /*
         * Generate the relative path from base-path to file..
         */
        $relpath = 'res';
        if (!$isRes) {
            $relpath .= DS.$source;
        }
        $relpath .= DS.$subfolder;

        $suffix = $source;
        if (is_array($vars) && $folder == false && $source !== 'less') {
            $suffix .= '.php';
        }

        /*
         * Circle through the path's
         */
        foreach ($paths as $path => $url) {
            /*
             * Generate the direct path to the file or folder.
             */
            $file = $path.$relpath.$filename.($folder == false ? '.'.$suffix : DS);

            $ePaths[] = dirname($file);
            /*
             * Check if file or folder exist.
             */
            if (
                ($folder == false && !file_exists($file))
             || ($folder == true && is_dir($file))
            ) {
                continue;
            /*
             * Or if they had been added previously.
             */
            } elseif (isset(self::$s_registeredSources[$foa][$source][$file])) {
                return true;
            } elseif ($folder == true) {
                /*
                 * Is folder and not added so get the valid files from the folder and add them.
                 */
                foreach (X\THETOOLS::get_dirArray($file) as $subFile) {
                    if (pathinfo($subFile, PATHINFO_EXTENSION) == $suffix) {
                        $this->_reg_source( 
                            $source,
                            $filename.DS.pathinfo($subFile, PATHINFO_FILENAME),
                            array(
                                'vars' => $vars,
                                'folder' => false,
                                'setTime' => $setTime
                            ),
                            $admin
                        );
                    }
                }
                self::$s_registeredSources[$foa][$source][$file] = true;
                return true;
            } elseif ($folder == false) {

                if ($source === 'coffee') {
                    $file = self::_handle_coffee($file, $foa);
                    $relpath = str_replace(DS.'coffee'.DS, DS.'js'.DS, $relpath);
                    $source = 'js';
                    $suffix = 'coffee.js';
                }

                if ($source == 'less') {
                    $file = self::_handle_less($file, $foa);
                    $relpath = str_replace(DS.'less'.DS, DS.'css'.DS, $relpath);
                    $source = 'css';
                    $suffix = 'less.css';
                }

                /*
                 * Is file -> Add it.
                 */
                $url = X\THETOOLS::slash($url).str_replace(DS, '/', $relpath).$filename.'.'.$suffix;

                if (!is_array($vars)) {
                    $vars = array();
                }
                if ($setTime) {
                    $vars = array_merge($vars, array('t' => filemtime($file)));
                }

                if (!empty($vars)) {
                    X\THETOOLS::filter_urlQuery($url, null, null, $vars);
                }


                self::$s_registeredSources[$foa][$source][$file] = $url;

                return true;
            }
            
        }


        $msg = sprintf(__( '%1$s-File **%2$s** not found. Expected here "%3$s"', 'themaster' ),
            strtoupper($source),
            "$filename.$source",
            implode(__('" or here "', 'themaster'), $ePaths)
        );
        throw new THEBASEException($msg,1);
    }

    /**
     * Internal method for THEBASE::_reg_source()
     *
     * Controls the creation of css files from less by checking the filetime of .css and .less
     * and converts new css from less if less in newer.
     * Also appends CSSfix to less.
     *
     * @access public
     * @param  string $file the less filepath
     * @param  string $foa  admin or front
     * @return string       the css filepath
     */
    private function _handle_less($file, $foa)
    {
        /*
         * Prevent double handling in runtime.
         */
        self::$s_registeredSources[$foa]['less'][$file] = true;
        /*
         * Predict the css path by replacing less with css in the filepath.
         */
        $cssFile = str_replace(
            array(
                DS.'less'.DS,
                '.less'
            ),
            array(
                DS.'css'.DS,
                '.less.css'
            ),
            $file
        );
        
        /*
         * If the file does not exist or the less is newer...
         */
        if (!file_exists($cssFile) 
         || filemtime($file) > filemtime($cssFile)
        ) {
            /*
             * Check if the target file is existent and writable.
             */
            if (!file_exists($cssFile)) {
                if (!is_dir(dirname($cssFile))) {
                    @mkdir(dirname($cssFile));
                }
                $h = @fopen($cssFile, 'w');
                if ($h) {
                    @fclose($h);
                }
                unset($h);
            }
            if (!file_exists($cssFile) || !is_writable($cssFile)) {
                throw new THEBASEException(
                    sprintf(
                        'LessCss target file "%s" is not existent or writable.',
                        $cssFile
                    ),
                    1
                );
            }

            /*
             * Include the libraries.
             */
            if (!class_exists('lessc')) {
                throw new THEBASEException("Unable to find the less compiler");
            }

            /*
             * Get the content from less file.
             */
            $c = file($file);

            /*
             * List of old entries header endings.
             */
            $endings = array(
                '// End: themaster //',
                '// Now have fun, writing LESS!'
            );

            /*
             * Determine witch baseUrl to use.
             */
            if (!isset($this) || strpos($file, self::$sBasePath) === 0) {
                $baseUrl = self::$sBaseUrl;
            } else {
                $baseUrl = $this->baseUrl;
            }
            
            /*
             * Put together a list of globals that can be used inside .less
             */
            $globals = array(
                'masterRes' => self::$sBaseUrl.'res/',
                'baseUrl' => $baseUrl,
            );
            if (\Xiphe\THEMASTER\WP()) {
                $uploadDir = wp_upload_dir();
                $globals['uploadUrl'] = X\THETOOLS::slash(X\THETOOLS::normalizeUrl($uploadDir['url']));
                $globals['home'] = X\THETOOLS::slash(X\THETOOLS::normalizeUrl(get_bloginfo('url')));
                $globals['wpurl'] = X\THETOOLS::slash(X\THETOOLS::normalizeUrl(get_bloginfo('wpurl')));
            }
            ksort($globals);

            /*
             * Write the header.
             */
            $lessHeader = '// globals:';
            foreach ($globals as $key => $value) {
                $lessHeader .= " @$key,";
            }
            $lessHeader = rtrim($lessHeader, ',')."\n";
            $lessHeader .= "// Have fun, writing LESS!";

            /*
             * End the last line of the header to the list of known header endings.
             */
            $iExp = explode("\n", $lessHeader);
            $endings[] = trim(end($iExp));

            /*
             * Delete old headers and add the new one.
             */
            $i = 0;
            while ($i < 10) {
                if (in_array(trim($c[$i]), $endings)) {
                    $c = array_splice($c, $i+1);
                    if (trim($c[0]) === '') {
                        $c = array_splice($c, 1);
                    }
                    break;
                }
                $i++;
            }
            file_put_contents($file, $lessHeader."\n\n".implode('', $c));

            /*
             * Make the globals available in .less
             */
            $less = array();
            foreach ($globals as $key => $value) {
                $less[] = "@$key: \"$value\";\n";
            }
            $less[] = "@import \"elements.less\";\n";
            $less = implode('', array_merge($less, $c));

            /*
             * Convert css and append CSSfix
             */
            try {
                $Less = new \lessc();
                $Less->addImportDir(dirname($file));
                $Less->addImportDir(THEMASTER_PROJECTFOLDER.'res'.DS.'less');
                
                $CSS = $Less->compile($less);

                $Crush = false;
                for ($i=0; $i < 15; $i++) { 
                    if (!isset($c[$i])) {
                        break;
                    } elseif (trim($c[$i]) === '// AppendCrush //') {
                        $Crush = true;
                        break;
                    }
                }

                if ($Crush) {
                    if (!class_exists('csscrush')) {
                        throw new THEBASEException("CSSCrush could not be loaded");
                    }
                    $CSS = \csscrush::string($CSS);
                    $CSS = $CSS->raw;
                }

                file_put_contents($cssFile, $CSS);
            } catch (\Exception $e) {
                X\THEDEBUG::debug('LESS ERROR: '.$e->getMessage()." \nFile: ".$e->getFile()." \nLine: ".$e->getLine(), 4);
                return false;
            }
        }

        if (!file_exists($cssFile)) {
            throw new THEBASEException("Error on .less generation \"$cssFile\" does not exist.", 1);
            return false;
        } else {
            return $cssFile;
        }
    }

    private function _handle_coffee($file, $foa)
    {
        /*
         * Prevent double handling in runtime.
         */
        self::$s_registeredSources[$foa]['coffee'][$file] = true;

        /*
         * Predict the css path by replacing less with css in the filepath.
         */
        $jsFile = str_replace(
            array(
                DS.'coffee'.DS,
                '.coffee'
            ),
            array(
                DS.'js'.DS,
                '.coffee.js'
            ),
            $file
        );

        /*
         * If the file does not exist or the less is newer...
         */
        if (!file_exists($jsFile) 
         || filemtime($file) > filemtime($jsFile)
        ) {
            /*
             * Check if the target file is existent and writable.
             */
            if (!file_exists($jsFile)) {
                if (!is_dir(dirname($jsFile))) {
                    @mkdir(dirname($jsFile));
                }
                $h = @fopen($jsFile, 'w');
                if ($h) {
                    @fclose($h);
                }
                unset($h);
            }
            if (!file_exists($jsFile) || !is_writable($jsFile)) {
                throw new THEBASEException(
                    sprintf(
                        'CoffeeScript target file "%s" is not existent or writable.',
                        $jsFile
                    ),
                    1
                );
            }

            touch($file);
            

            /*
             * Include the libraries.
             */
            if (!class_exists('CoffeeScript\Compiler')) {
                throw new THEBASEException("Unable to find the CoffeeScript Compiler");
            }


            /*
             * Get the content from less file.
             */
            $content = file_get_contents($file);

            try {
                $js = \CoffeeScript\Compiler::compile($content);
            } catch (\Exception $e) {
                X\THEDEBUG::debug($e);
                return;
            }

            file_put_contents($jsFile, $js);
        }

        if (!file_exists($jsFile)) {
            throw new THEBASEException("Error on .less generation \"$jsFile\" does not exist.");
            return false;
        } else {
            return $jsFile;
        }
    }
    
    /**
     * Print out links to registered sources.
     *
     * @access public
     * @param  boolean $admin true for admin sources
     * @return void
     */
    public function echo_sources($admin = false)
    {
        if (empty(self::$s_registeredSources)) {
            return;
        }
        
        $sources = $admin == 'admin' ? self::$s_registeredSources['admin'] : self::$s_registeredSources['front'];
        unset($sources['less']);
        if (empty($sources)) {
            return;
        }

        $HTML = self::sGet_HTML();
        foreach ($sources as $type => $files) {
            foreach ($files as $file => $url) {
                $HTML->$type($url);
            }
        }
    }

    /**
     * Getter for THEBASE::$s_registeredSources
     *
     * @access public
     * @return array
     */
    public static function sGet_registeredSources()
    {
        return self::$s_registeredSources;
    }

    /**
     * Getter for THEBASE::$s_registeredJsVars
     *
     * @access public
     * @return array
     */
    public static function sGet_registeredJsVars()
    {
        return self::$s_registeredJsVars['front'];
    }

    /**
     * Getter for THEBASE::$s_registeredAdminJsVars
     *
     * @access public
     * @return array
     */
    public static function sGet_registeredAdminJsVars()
    {
        return self::$s_registeredJsVars['admin'];
    }
    
    /**
     * Prints out the registered JsVariables.
     *
     * @access public
     * @return void
     */
    public function echo_jsVars($admin = false)
    {
        self::_echo_jsVars();
        $HTML = self::sGet_HTML();
        $HTML->sg_script();
        foreach (self::$s_registeredJsVars as $name => $var) {
            $HTML->blank('var '.$name.' = '.json_encode($var).';');
            unset(self::$s_registeredJsVars[$name]);
        }
        $HTML->end();
    }
    
    /**
     * Prints out the registered AdminJsVariables.
     *
     * @access public
     * @return void
     */
    public function echo_AdmimJsVars()
    {
        self::_echo_jsVars(true);
        $HTML = $this->get_HTML();
        $HTML->sg_script();
        foreach (self::$s_registeredAdminJsVars as $name => $var) {
            $HTML->blank('var '.$name.' = '.json_encode($var));
            unset(self::$s_registeredAdminJsVars[$name]);
        }
        $HTML->end();
    }

    private static function _echo_jsVars($admin = false)
    {
        $HTML = self::sGet_HTML();
        $HTML->sg_script();
        $admin = ($admin ? 'admin' : 'front');
        foreach (self::$s_registeredJsVars[$admin] as $name => $var) {
            $HTML->blank('var '.$name.' = '.json_encode($var).';');
            unset(self::$s_registeredJsVars[$admin][$name]);
        }
        $HTML->end();
    }
    
    /**
     * Prepares the including of a file in res/includes
     *
     * @access public
     * @param  string  $source  the filename or relative path + filename
     * @param  boolean $include true for direct including.
     * @return mixed            the full path to the file or bool as result from include.
     */
    final public function incl($source, $include = false)
    {
        $source = X\THETOOLS::get_verryCleanedDirectPath($source).'.php';
        $paths = array();
        if (isset($this)) {
            $paths[] = $this->basePath;
        }
        $paths[] = self::$sBasePath;

        foreach ($paths as $path) {
            if (file_exists(($path = $path.'res'.DS.'includes'.DS.$source))) {
                return $include ? include $path : $path;
            }
        }
        throw new THEBASEException('Tryed to include unexistent file "'.$source.'"');
    }
    
    
    /**
     * Includes a Model File from basePath/models/
     *
     * @access public
     * @param  string $modelname the Models name
     * @return string            namespace+model.
     */
    final public function reg_model($modelname, $staticInits = null)
    {
        $ns = array();
        if (isset($this)) {
            $ns[] = $this->namespace;
        }
        $ns[] = self::$sNameSpace;
        
        foreach ($ns as $namespace) {
            $mID = $namespace.'\models\\'.$modelname;

            if (class_exists($mID)) {
                if (is_array($staticInits)) {
                    foreach ($staticInits as $key => $value) {
                        $mID::$$key = $value;
                    }
                }
                return $mID;
            }
        }

        $msg = sprintf(
            __('**!THE MASTER ERROR:** Model File for %s not found.', 'themaster'),
            $modelname
        );
        throw new THEBASEException($msg);
    }
    
    /**
     * Checks if the model is available, includes the model file with THEBASE::reg_model()
     * and returns a new Model.
     *
     * @access public
     * @param  string $modelname the model name
     * @param  array  $initArgs  the initiation arguments for the model
     * @return object            the model
     */
    final public function new_model($modelname, $initArgs = null)
    {
        $ns = array();
        if (isset($this)) {
            $ns[] = $this->namespace;
        }
        $ns[] = self::$sNameSpace;
        
        foreach ($ns as $namespace) {
            $mID = $namespace.'\models\\'.$modelname;

            if (class_exists($mID)) {
                return new $mID($initArgs);
            }
        }

        return false;
    }

    /**
     * Shorthand for THEBASE::get_instance()
     *
     * @access public
     * @param  string $classname the name of the class
     * @param  array  $initArgs  optional initiation arguments for the instance.
     * @return mixed             the instance or false if not available.
     */
    final public function gi($classname, $initArgs = array())
    {
        if (isset($this)) {
            return $this->get_instance($classname, $initArgs);
        } else {
            return self::get_instance($classname, $initArgs);
        }
    }

    /**
     * Trys to get a class File named example.php from 
     * "classes"-Subfolder of defined basePath and return a 
     * instance of Example
     * 
     * @access public
     * @param  string $classname the name of the class
     * @param  array  $initArgs  optional initiation arguments for the instance.
     * @return mixed             the instance or false if not available.
     */
    final public function get_instance($name, $initArgs = array())
    {
        $paths = array();

        /*
         * Remove potential namespace from instance name.
         */
        $name = explode('\\', $name);
        $name = $name[count($name)-1];

        if ($name === 'Master') {
            if (isset($initArgs['basePath']) && $initArgs['namespace']) {
                $paths[]  = array(
                    'namespace' => $initArgs['namespace'],
                    'basePath' => $initArgs['basePath']
                );
            } else {
                throw new THEBASEException( 'Master initiation misses arguments (prefix & basePath)');
                return false;
            }
        } else {
            if (isset($this)
             && get_class($this) != THE::WPMASTER
             && isset($this->namespace)
             && isset($this->basePath)
            ) {
                $paths[] = array(
                    'namespace' => $this->namespace,
                    'basePath' => $this->basePath
                );
            }
            $paths[] = array(
                'namespace' => self::$sNameSpace,
                'basePath' => self::$sBasePath
            );
        }

        $ePaths = array();
        $buildClass = false;

        foreach ($paths as $k => $data) {
            extract($data);
            $classname = $namespace.'\classes\\'.$name;
            if ($buildClass === false && count($paths) > 1) {
                $buildClass = $classname;
            }

            if (isset(self::$s_singletons[$classname]) && is_object(self::$s_singletons[$classname])) {
                return self::$s_singletons[$classname];
            }

            if (class_exists($classname)) {

                if (isset($this)) {
                    $initArgs = array_merge(
                        $this->_mastersInitArgs,
                        $this->_initArgs,
                        $initArgs
                    );
                    $initArgs['_mastersInitArgs'] = $this->_mastersInitArgs;
                } else {
                    $initArgs = array_merge(
                        self::$s_themastersInitArgs,
                        $initArgs
                    );
                    $initArgs['_mastersInitArgs'] = self::$s_themastersInitArgs;
                }

                self::sRegister_callback(
                    'initiated', 
                    function ($obj) {
                        if (isset($obj->singleton) && $obj->singleton === true) {
                            THEBASE::sRegSingleton($obj, null, true);
                        } elseif(isset(self::$s_singletons[get_class($obj)])) {
                            unset(self::$s_singletons[get_class($obj)]);
                        }

                        if (isset($obj->HTML) && $obj->HTML === true) {
                            if (defined('XIPHE_HTML_AVAILABLE') && XIPHE_HTML_AVAILABLE === true) {
                                $obj->HTML = new X\HTML($obj->baseUrl);
                            } else {
                                throw new THEBASEException( '<strong>!THE MASTER ERROR:</strong> Class "'
                                    .get_class($obj).'" should have been initiated whith HTML Object,'
                                    .' but it seems as the HTML Class file is not available.'
                                );
                            }
                        }

                        if (method_exists($obj, '_hooks')) {
                            $obj->_hooks();
                        }
                        if (method_exists($obj, 'hooks')) {
                            $obj->hooks();
                        }
                        if (method_exists($obj, 'init')) {
                            $obj->init();
                        }
                    },
                    1,
                    function ($condArgs, $givenArgs) {
                        return $condArgs['class'] === $givenArgs['class'];
                    },
                    array('class' => $classname)
                );

                $obj = new $classname($initArgs);
                break;
            }
        }

        if(!isset($obj) || !$obj) {
            if (isset($this)
             && class_exists(THE::WPBUILDER)
             && isset($this->buildMissingClasses)
             && $this->buildMissingClasses === true
            ) {
                return THEWPBUILDER::sBuildClass($buildClass, $initArgs, $this);
            } else {
                $msg = sprintf(__( '**!THE MASTER ERROR:** Class File for %s expected here: "%s".', 'themaster' ),
                    $classname,
                    implode(__('" or here "', 'themaster'), $ePaths)
                );
                throw new THEBASEException($msg);
            }
        }
        return $obj;
    }

    /**
     * Saves a object into static singleton store.
     *
     * @access public
     * @param  mixed   $obj    the singleton instance to be registered or false as placeholder
     * @param  string  $key    the instance name if $obj == false
     * @param  boolean $force  set true to enable overwriting of singleton.
     * @return void
     */
    final public static function sRegSingleton($obj, $key = '', $force = false)
    {
        if ($obj == false) {
            if ($key == '') {
                throw new Exception('If registering a false object a key is required.', 1);
            }
            $cID = $key;
        } else {
            $cID = get_class($obj);
        }
        if (!$force && isset(self::$s_singletons[$cID])) {
            throw new THEBASEException('Invalid double construction of singleton "'.$cID.'"');
        } else {
            self::$s_singletons[$cID] = $obj;
        }
    }

    /**
     * Non-static wrapper for THEBASE::sRegister_callback();
     *
     * @access public
     * @param  string    $name          the name of the callback
     * @param  function  $cb            the callback function
     * @param  mixed     $times         how often the callback should be fired before being deleted. Integer or '*'
     * @param  function  $condition     a function returning a boolean to determine if the callback should be fired
     * @param  array     $conditionArgs arguments to be passed to the condition function
     * @param  integer   $position      the position of the callback lower number -> called early.
     * @return void
     */
    final public function register_callback($name, $cb, $times = 1, $condition = null, $conditionArgs = array(), $position = 10)
    {
        self::sRegister_callback($name, $cb, $times, $condition, $conditionArgs, $position);
    }

    /**
     * Registers a callback to be used later by THEBASE::do_callback()
     *
     * @access public
     * @param  string    $name          the name of the callback
     * @param  function  $cb            the callback function
     * @param  mixed     $times         how often the callback should be fired before being deleted. Integer or '*'
     * @param  function  $condition     a function returning a boolean to determine if the callback should be fired
     * @param  array     $conditionArgs arguments to be passed to the condition function
     * @param  integer   $position      the position of the callback lower number -> called early.
     * @return void
     */
    final public static function sRegister_callback($name, $cb, $times = 1, $condition = null, $conditionArgs = array(), $position = 10)
    {
        self::$s_callbacks[$name][$position][] = array(
            'condition' => $condition,
            'cb' => $cb,
            'times' => $times,
            'conditionArgs' => $conditionArgs
        );
    }

    /**
     * Non-static wrapper for THEBASE::sDo_callback();
     *
     * @access public
     * @param  string $name            the name of the callback
     * @param  array  $callbackArgs    array of arguments to be passed to the callback function.
     * @param  array  $doConditionArgs array of arguments to be passed to the condition function.
     * @return void
     */
    final public function do_callback($name, $callbackArgs = array(), $doConditionArgs = array())
    {
        self::sDo_callback($name, $callbackArgs, $doConditionArgs);
    }

    /**
     * Fires a previously registered callback
     *
     * @access public
     * @param  string $name            the name of the callback
     * @param  array  $callbackArgs    array of arguments to be passed to the callback function.
     * @param  array  $doConditionArgs array of arguments to be passed to the condition function.
     * @return void
     */
    final public static function sDo_callback($name, $callbackArgs = array(), $doConditionArgs = array())
    {
        if (isset(self::$s_callbacks[$name]) 
         && count(self::$s_callbacks[$name]) > 0
        ) {
            ksort(self::$s_callbacks[$name]);

            foreach (array('callbackArgs', 'doConditionArgs') as $k) {
                if (!is_array($$k)) {
                    $$k = array($$k);
                }
            }
            foreach (self::$s_callbacks[$name] as $nr => $cbg) {
                foreach ($cbg as $k => $data) {
                    extract($data);

                    if (!isset( $condition)
                     || (!is_callable($condition) && $condition)
                     || call_user_func_array($condition, array($doConditionArgs, $conditionArgs))
                    ) {
                        array_unshift($callbackArgs, $cb);
                        call_user_func_array(
                            array(THE::MASTER, 'sTryTo'),
                            $callbackArgs
                        );
                        
                        if ($times !== '*') {
                            $times--;
                            if ($times <= 0) {
                                unset(self::$s_callbacks[$name][$nr][$k]);
                            } else {
                                self::$s_callbacks[$name][$nr][$k]['times'] = $times;
                            }
                        }
                    }
                }
            }
        }
    }
        
    /**
     * End of hooking chain.
     *
     * @access protected
     * @return void
     */
    protected function _hooks() { }
    
    /**
     * End of update chain.
     *
     * @access protected
     * @return void
     */
    public function update()
    {
        return true;
    }

    /**
     * Catch all function to implement THETOOLS and THEDEBUG
     * 
     * @access public
     * @return void
     */
    final public function __call($method, $args)
    {
        /*
         * Check if called on instance and if the instance has a "call" method.
         */
        if (isset($this) && method_exists($this, 'call')) {
            return call_user_func_array(array($this,'call'), $args);
        }

        /*
         * Check if method is available in THETOOLS.
         */
        elseif (
            class_exists($class = 'Xiphe\THETOOLS')
         && method_exists($class, $method)
        ) {
            X\THEDEBUG::debug('Indirect call of THETOOLS::'.$method.' please try to call it directly', null, 3, 2);
            return call_user_func_array(array($class, $method), $args);
        }

        /*
         * Check if the method is available in THEDEBUG.
         */
        elseif (
            class_exists($class = 'Xiphe\THEDEBUG')
         && method_exists($class, $method )
        ) {
            X\THEDEBUG::debug('Indirect call of THEDEBUG::'.$method.' please try to call it directly', null, 3, 2);
            X\THEDEBUG::_set_btDeepth(7);
            return call_user_func_array(array($class, $method), $args);
            X\THEDEBUG::_reset_btDeepth();
        }

        /*
         * Check if method was "debug".
         */
        elseif ($method == 'debug') {
            echo '<pre>Debug:'."\n";
            var_dump($args);
            echo '<pre>';

        /*
         * Throw exception.
         */
        } else {
            X\THEDEBUG::debug('callstack', null, null, 2);
            throw new THEBASEException('Call to undefined method '.$method);
        }
    }
}

class THEBASEException extends \Exception {
}

/**
 * Fallback for translation in non-Wordpress environments.
 */
if (!function_exists('__')) {
    /**
     * Fallback for translation in non-Wordpress environments.
     *
     * @param  string $text incoming text
     * @return string       the same text :)
     */
    function __($text) {
        return $text;
    }
}
if (!function_exists('_e')) {
    /**
     * Fallback for translation in non-Wordpress environments.
     *
     * @param  string $text incoming text
     * @return void
     */
    function _e($text) {
        echo $text;
    }
}
?>