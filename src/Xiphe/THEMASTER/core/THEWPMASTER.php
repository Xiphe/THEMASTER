<?php
/*
 !THE MASTER - a base for plugins and themes
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

namespace Xiphe\THEMASTER\core;

use Xiphe as X;

/**
 * THEWPMASTER is the last class inside !THE MASTER and the one that
 * Wordpress plugins and themes should extend.
 *
 * @copyright Copyright (c) 2013, Hannes Diercks
 * @author  Hannes Diercks <xiphe@gmx.de>
 * @version 3.0.3
 * @link    https://github.com/Xiphe/-THE-MASTER/
 * @package !THE MASTER
 */
class THEWPMASTER extends THEWPUPDATES {


    /* ------------------ *
     *  STATIC VARIABLES  *
     * ------------------ */
    

    /* PUBLIC */

    /**
     * Holds the current User Object if available.
     *
     * @access public
     * @var    object
     */
    public static $sCurrentUser;

    /* PROTECTED */

    /* PRIVATE */

    /**
     * turns true after first initiation.
     *
     * @access private
     * @var    bool
     */
    private static $s_initiated = false;

    /**
     * Array of passed Notes to be printet via print_adminMessages()
     *
     * @access private
     * @var    array
     */
    private static $s_notes = array(); 

    /**
     * Flag to check if the admin notices had allready been sent out.
     *
     * @access private
     * @var    bool
     */
    private static $s_adminNoticesSent = false;

    /**
     * Available registered content tags.
     *
     * @access private
     * @var    array
     */
    private static $s_contentTags = array();


    private static $s_ftp_conn_id;

    /**
     * Array of all project versions.
     *
     * @access private
     * @var array
     */
    private static $s_theVersions = array();

    /**
     * Flag turns true if a project has been updated and allows sSaveTheVersions
     * to store the new versions into the database.
     *
     * @access private
     * @var    boolean
     */
    private static $s_storeTheVersions = false;


    public static $currentNavItem;


    /* -------------------- *
     *  INSTANCE VARIABLES  *
     * -------------------- */

    /* PROTECTED */

    protected $folderStructure_;


    /* ---------------------- *
     *  CONSTRUCTION METHODS  *
     * ---------------------- */

    /**
     * The Constructor method
     *
     * @param  array $initArgs the initiation arguments.
     * @access public
     * @return void
     */
    final public function __construct($initArgs) {
        if (is_object(($r = THEBASE::check_singleton_(get_class($this))))) {
            return $r;
        } else {
            $this->constructing = true;
        }

        /*
         * Register "theversion" as required initiation argument.
         */
        $this->add_requiredInitArgs_('version');

        if (!self::$s_initiated) {
            if (function_exists('load_plugin_textdomain')) {
                @load_plugin_textdomain('themaster', false, '_themaster/languages/');
            }
            THEBASE::sRegister_callback('afterBaseS_init', array(THE::WPMASTER, 'sinit'));
        }

        /*
         * Pass ball to parent.
         */
        $obj = parent::__construct($initArgs);


        if ($initArgs === 'MINIMASTER') {
            if (\Xiphe\THEMASTER\WP()) {
                $this->_versionCheck();
            }
        }
        return $obj;
    }
    
    /**
     * One time initiaton. Called by THE BASE after construction.
     *
     * @access public
     * @return void
     */
    public static function sinit() {
        if (!self::$s_initiated) {

            if( function_exists( 'get_option' ) ) {
                self::$s_theVersions = get_option( 'Xiphe\THEMASTER\theVersions', array() );
            }

            if (function_exists('add_action')) {
                add_action('shutdown', array(THE::WPMASTER,'sSaveTheVersions'));
            }

            /*
             * Call aditional hooks.
             */
            self::s_hooks();

            /*
             * Register basic less and js files.
             */
            if (!function_exists('is_admin') || !is_admin()) {
                THEBASE::sReg_less('base');
            } else {
                THEBASE::sReg_adminLess('tm-admin');
                THEBASE::sReg_adminJs('tm-admin');
                add_action('plugins_loaded', function() {
                    THEBASE::sGet_instance('FileSelect');
                });
            }

            /*
             * Register hash rederect for login.
             */
            if (isset( $GLOBALS['pagenow'])
             && $GLOBALS['pagenow'] == 'wp-login.php'
             && isset($_GET['redirect_to']) 
             && isset($_GET['forceRederect'])
             && $_GET['forceRederect'] == 'hash'
            ) {
                THEBASE::reg_js('tm-login');
            }

            /*
             * Define availability constant.
             */
            define('WPMASTERAVAILABE', true);

            /*
             * Prevent this from beeing executed twice.
             */
            self::$s_initiated = true;
        }
    }

    /**
     * Registers one-time hooks for thewpmaster.
     *
     * @access private
     * @return void
     */
    private static function s_hooks() {
        /*
         * Return if Wordpress is not available.
         */
        if (!function_exists('add_action')) {
            return;
        }

        add_filter('cron_schedules', function ($schedules) {
            $schedules['weekly'] = array(
                'interval' => 604800,
                'display' => __('Once Weekly')
            );
            $schedules['now'] = array(
                'interval' => 1,
                'display' => __('Every Time (for debugging)')
            );
            return $schedules;
        });
 

        /*
         * Register verry own one time init when wp is available.
         */
        add_action('wp_enqueue_scripts', array(THE::WPMASTER, 'twpm_enqueue'), 99, 0);
        add_action('admin_head', array(THE::WPMASTER, 'twpm_enqueue'), 99, 0);
        add_action('login_head', array(THE::WPMASTER, 'twpm_enqueue'), 99, 0);

        /*
         * Register callbacks for printing js-variables.
         */
        add_action('wp_enqueue_scripts', array(THE::WPMASTER, 'print_xiphe_init_js'), 0, 0);
        add_action('admin_enqueue_scripts', array(THE::WPMASTER, 'print_xiphe_init_js'), 0, 0);

        /*
         * Register callbacks for printing js-variables.
         */
        add_action('wp_head', array(THE::WPMASTER, 'twpm_print_jsVars'), 999, 0);
        add_action('admin_head', array(THE::WPMASTER, 'twpm_print_adminJsVars'), 999, 0);

        /*
         * Save the current nav object.
         */
        add_filter('wp_nav_menu_objects', array(THE::WPMASTER, 'twpm_wp_nav_menu_objects'));

        /*
         * Spice the Login Screen 
         */
        add_action('login_head', array(THE::WPMASTER, 'twpm_login_head'));
        add_action('wp_login', array(THE::WPMASTER, 'twpm_wp_login'));
        add_filter('login_message', array(THE::WPMASTER, 'twpm_loginMsg'));
        add_action('wp_ajax_twpm_hashRederect', array(THE::WPMASTER, 'twpm_ajax_hashRederect'));
        add_action('wp_ajax_nopriv_twpm_hashRederect', array(THE::WPMASTER, 'twpm_ajax_hashRederect'));

        /*
         * Register callback for admin notices.
         */
        add_action('admin_notices', array(THE::WPMASTER, 'twpm_admin_notices'), 999, 0);

        /*
         * Js Var Cache
         */
        add_action('wp_ajax_twpm_jsVars', array(THE::WPMASTER, 'twpm_ajax_jsVars'));
        add_action('wp_ajax_nopriv_twpm_jsVars', array(THE::WPMASTER, 'twpm_ajax_jsVars'));

        /*
         * Cronjobs
         */
        add_action('xiphe_themaster_checkjsvarcache', array(THE::WPMASTER, 'twpm_checkJsVarCache'));
        add_action('xiphe_themaster_responsiveimagecache', array('Xiphe\THEMASTER\classes\ResponsiveImages', 'checkCache'));
        
        /*
         * Register callback for plugin dependency check.
         */
        add_action('after_setup_theme', array(THE::WPMASTER, 'twpm_check_initiated'));

        add_action('init', array(THE::WPMASTER, 'twpm_wpinit'));
    }


    /* --------------------------- *
     *  MASTER INITIATION METHODS  *
     * --------------------------- */
    
    /**
     * Initiation for a new Instance of THEWPMASTER, generates a new Master
     *
     * @param array $initArgs see $this->requiredInitArgs for required keys
     * @return void
     * @access private
     * @date Jul 28th 2011
     */
    final protected function _masterInit() {
        if( !isset( $this ) ) {
            throw new \Exception("_masterInit should not be called staticaly.", 1);
        }
        if( isset( $this->_masterInitiated ) && $this->_masterInitiated === true ) {
            return;
        }

        if (parent::_masterInit()) {
            $this->_versionCheck();

            if (is_dir($this->basePath.DS.'languages')) {
                if ($this->projectType == 'plugin') {
                    load_plugin_textdomain($this->textdomain, false, $this->folderName.'/languages/');
                } elseif ($this->projectType == 'theme') {
                    $path = dirname(X\THEMASTER\get_wpInstallPath($this->projectFile, true)).DS.'languages'.DS;
                    load_theme_textdomain($this->textdomain, $path);
                }
            }

            if ($this->projectType != 'theme') {
                if( method_exists( $this, 'activate' ) ) {
                    register_activation_hook( $wpishPath, array( $this, 'activate' ) );
                }

                if( method_exists( $this, 'deactivate' ) ) {
                    register_deactivation_hook( $wpishPath, array( $this, 'deactivate' ) );
                }
            }

            $this->_masterInitiated();
        }
    }

    /**
     * Checks the last stored version from db against the current project version
     * and calls the "update" method if the project is newer.
     * The update method should return true to get it's new version stored to db.
     *
     * @access private
     * @return void
     */
    protected function _versionCheck() {
        if (!function_exists('is_admin')
         || !is_admin()
         || !function_exists('get_option')
         || !function_exists('update_option')
        ) {
            return;
        }

        if (!isset($this)) {
            throw new Exception('Invalid call of _versionCheck', 1);
        }

        if (get_class($this) == THE::WPMASTER) {
            $textdomain = THE::WPMASTER;
            $version = THEBASE::$sVersion;
        } else {
            $textdomain = get_class($this);
            $version = $this->version;
        } 

        if (!isset(self::$s_theVersions[$textdomain])
         || version_compare(self::$s_theVersions[$textdomain], $version, '<')
        ) {
            if (get_class($this) == THE::WPMASTER) {
                $ok = self::_masterUpdate();
            } else {
                if (isset($this->folderStructure_) && !is_array($this->folderStructure_)) {
                    unset($this->folderStructure_);
                }
                THEWPBUILDER::check_folderStructure_(
                    $this->basePath,
                    isset($this->folderStructure_) ? $this->folderStructure_ : null
                );
                $ok = $this->update();
            }

            if ($ok == true) {
                self::$s_theVersions[$textdomain] = $version;
                self::$s_storeTheVersions = true;
            }
        }
    }

    /**
     * Checks if some plugins could not be initiated caused by unavailable
     * required plugins.
     *
     * @access public
     * @return void
     */
    public static function twpm_check_initiated() {
        if( count( ( $uninitiateds = THEMASTER::get_uninitiated() ) ) > 0 ) {
            $uninit = array(
                'theme' => array(),
                'plugin' => array()
            );
            $required = array(
                'theme' => array(),
                'plugin' => array()
            );
            foreach( $uninitiateds as $k => $p ) {
                $t = $p['type'];
                foreach( $p['required'] as $req ) {
                    if( !in_array( $req, THEMASTER::get_initiated() )
                     && !in_array( $req, $required[$t] )
                    ) {
                        array_push( $required[$t], $req );
                    }
                }
                array_push( $uninit[$t], $k );
            }
            foreach( array( 'theme', 'plugin' ) as $t ) {
                if( count( $uninit[$t] ) > 0 ) {
                    $singular = $t === 'theme' ? __( 'Theme', 'themaster' ) : __( 'Plugin', 'themaster' );
                    $plural = $t === 'theme' ? __( 'Themes', 'themaster' ) : __( 'Plugins', 'themaster' );

                    self::set_adminMessage( 
                        sprintf( 
                            __( '**Dependency Error:** The %1$s **%2$s** will most likely not be functional because of the unavailability of **%3$s**.', 'themaster' ),
                            ( count( $uninit[$t] ) > 1 ? $plural : $singular ),
                            implode( ', ', $uninit[$t] ),
                            implode( ', ', $required[$t] )
                        ),
                        'error'
                    );
                }
            }
        }
    }


    /* ------------------------------- *
     *  ACTIVATION AND UPDATE METHODS  *
     * ------------------------------- */

    /**
     * Called on destruction and saves the current options into database
     * if THEWPMASTER::$s_storeTheVersions is true.
     *
     * @access public
     * @return void
     */
    public static function sSaveTheVersions() {
        if( self::$s_storeTheVersions && function_exists( 'update_option' ) ) {
            update_option('Xiphe\THEMASTER\theVersions', self::$s_theVersions);
            self::$s_storeTheVersions = false;
        }
    }

    /**
     * Activation method for !THE MASTER
     *
     * @access private
     * @return void
     */
    public static function _masterActivate() {
        wp_schedule_event(time(), 'daily', 'xiphe_themaster_checkjsvarcache');
        wp_schedule_event(time(), 'weekly', 'xiphe_themaster_responsiveimagecache');
        return parent::_masterActivate();
    }

    /**
     * This method is called if !THE MASTER is being updated.
     *
     * @access private
     * @return bool always true
     */
    protected static function _masterUpdate() {
        self::check_folderStructure_(self::$sBasePath);
        return parent::_masterUpdate();
    }

    /**
     * Deactivation method for !THE MASTER
     *
     * @access private
     * @return void
     */
    public static function _masterDeactivate() {
        return parent::_masterDeactivate();
    }


    /* ------------------------- */
    /*  PUBLIC INTERNAL METHODS  */
    /* ------------------------- */
    
    final public static function print_xiphe_init_js()
    {
        $script = file_get_contents(self::$sBasePath.'res'.DS.'js'.DS.'xiphe_init.min.js');
        $script = preg_replace('/\r\n|\n|\r/', ' ', $script);
        $script = "<script type=\"text/javascript\">$script</script>";

        if (($HTML = self::sGet_HTML())) {
            $HTML->blank($script);
        } else {
            echo $script;
        }
    }

    final public static function twpm_wp_nav_menu_objects($sorted_menu_items)
    {   
        foreach ($sorted_menu_items as $menu_item) {
            if ($menu_item->current) {
                self::$currentNavItem = $menu_item;
            }
        }
        return $sorted_menu_items;
    }

    /**
     * This method catches errors from THEMASTER::sTryTo() method
     * and prints them as admin messages.
     *
     * @access public
     * @return void
     */
    final public static function sTryError( $e ) {
        $msg = sprintf( 
            __( '**THEMASTER RUNTIME Exception:**/||Message: //%1$s///||In File: //%2$s// &bull; Line //%3$s//', 'themaster' ),
            $e->getMessage(),
            $e->getFile(),
            $e->getLine()
        );
        if(is_admin() && !self::$s_adminNoticesSent) {
            self::set_adminMessage( 
                $msg,
                'error'
            );
        } else {
            X\THEDEBUG::debug( $msg, 'error', 4 ); 
        }
    }
        
    /**
     * Pints javascript variables registered by THEBASE::reg_adminJsVar().
     *
     * @access public
     * @return void
     */
    final public static function twpm_print_adminJsVars() {
        self::twpm_print_jsVars(true);
    }

    /**
     * Prints javascript variables registered by THEBASE::reg_jsVar().
     *
     * @access public
     * @param  boolean $admin switch to THEBASE::reg_adminJsVar()s registered variables.
     * @return void
     */
    final public static function twpm_print_jsVars($admin = false) {
        if ($admin) {
            $js = THEBASE::sGet_registeredAdminJsVars();
        } else {
            $js = THEBASE::sGet_registeredJsVars();
        }

        $checksum = md5($js);

        $key = "xiphe_themaster_jsvarcache_$checksum";
        if (!get_option($key, false)) {
            update_option(
                $key,
                (object) array(
                    'admin' => $admin,
                    'creation' => time(),
                    'content' => $js
                )
            );
        }

        $url = str_replace('&', '&#038;', add_query_arg(array(
            'action' => 'twpm_jsVars',
            'id' => $checksum
        ), admin_url('admin-ajax.php')));

        if (is_object($HTML = THEBASE::sGet_HTML(true))) {
            THEBASE::sGet_HTML()->script(array('src' => $url));
        } else {
            echo "<script src=\"$url\" type=\"text/javascript\"></script>";
        }
    }

    final public static function twpm_ajax_jsVars()
    {
        $vars = get_option('xiphe_themaster_jsvarcache_'.esc_attr($_GET['id']));

        if (!empty($vars) && (!$vars->admin || (is_admin() && is_user_logged_in()))) {
            header("Content-type: text/javascript");
            echo $vars->content;
        }
        exit;
    }
    
    final public static function twpm_checkJsVarCache()
    {
        global $wpdb;

        $query = "
            SELECT *
            FROM wp_options
            WHERE option_name LIKE 'xiphe_themaster_jsvarcache_%'
        ";
        
        foreach ($wpdb->get_results($query) as $jsCache) {
            $data = unserialize($jsCache->option_value);
            if (time()-60*60*48 > intval($data->creation)) {
                delete_option($jsCache->option_name);
            }
        }
    }

    /**
     * Registeres 
     * @return [type] [description]
     */
    final public static function twpm_enqueue() {
        wp_enqueue_script('jquery');

        foreach( THEBASE::sGet_registeredSources() as $dest => $sources) {
            if ($dest == 'front' && is_admin()
             || $dest == 'admin' && !is_admin()
            ) {
                continue;
            }

            foreach($sources as $type => $files) {
                foreach($files as $file => $url) {
                    $id = substr(md5($file), 2, 8).'-'.preg_replace('/[^A-Za-z0-9-_]/', '-', pathinfo($file, PATHINFO_FILENAME));
                    if($type == 'js') {
                        wp_enqueue_script($id, $url);
                    } elseif($type == 'css') {
                        wp_enqueue_style($id, $url);
                    }
                }
            }
        }
    }
    
    /**
     * Default Hooks addet to every subclass called via get_instance()
     *
     * @access public
     * @return void
     */
    final public function _hooks() {
        parent::_hooks();
        foreach( array( 'actions_', 'filters_' ) as $hooktype ) {
            if( isset( $this->$hooktype ) && is_array( $this->$hooktype ) ) {
                foreach ( $this->$hooktype as $k => $hooks ) {
                    if (!is_array($hooks)) {
                        $hooks = array($hooks);
                    }
                    foreach ($hooks as $hook) {
                        $e = explode( '|', $hook );
                        $method = is_int( $k ) ? $e[0] : $k;
                        $method = str_replace('-', '_', $method);
                        $e[-1] = $e[0];
                        $e[0] = array( $this, $method );
                        if( method_exists( $this, $method ) ) {
                            ksort( $e );
                            call_user_func_array(
                                $hooktype === 'actions_' ? 'add_action' : 'add_filter',
                                $e
                            );
                        } else {
                            throw new \Exception('THEMASTER ERROR: Should call Hook ' . $e[-1] . ' to unexistent method ' . $method . ' in class ' . get_class( $this ) . '.', 1);
                        }
                    }
                }

            } 
            
        }
        // if( method_exists( $this, 'wpinit' ) ) {
        //  $prio = isset( $this->wpinitPriority ) ? $this->wpinitPriority : null;
        //  add_action( 'init', array( $this, 'wpinit' ), $prio );
        // }
    }
    
    final public static function twpm_wpinit()
    {
        if (is_admin()
         && (current_user_can('edit_posts') || current_user_can('edit_pages'))
         && get_user_option('rich_editing') == 'true'
        ) {
            add_filter('mce_css', array(THE::WPMASTER, "twpm_mce_css"));
            add_filter("mce_external_plugins", array(THE::WPMASTER, "twpm_tinymce_plugin"));
            add_filter('mce_buttons_2', array(THE::WPMASTER, 'twpm_myplugin_button'));
        }
    }

    final public function twpm_mce_css($mce_css)
    {
        if (!empty($mce_css)) {
            $mce_css .= ',';
        }

        $mce_css .= THEBASE::$sBaseUrl.'res/css/mce.css';

        return $mce_css;
    }

    final public static function twpm_myplugin_button($buttons) {
        array_unshift($buttons, "twpm_clear", "twpm_sep", "twpm_clearsep", '|');
        return $buttons;
    }

    final public static function twpm_tinymce_plugin($plugin_array) {
        $plugin_array['twpm_clear'] = THEBASE::$sBaseUrl.'res/js/tinymce/twpm_clear.js';
        $plugin_array['twpm_sep'] = THEBASE::$sBaseUrl.'res/js/tinymce/twpm_sep.js';
        $plugin_array['twpm_clearsep'] = THEBASE::$sBaseUrl.'res/js/tinymce/twpm_clearsep.js';
        return $plugin_array;
    }

    /** Can be called to print Admin Messages setted via set_adminMessage()
     *
     * @return void
     * @access public
     * @date Sep 22th 2011
     */
    public static function twpm_admin_notices() {
        if(!isset($_SESSION['tm_admin_notes']) || !is_array($_SESSION['tm_admin_notes']))
            $_SESSION['tm_admin_notes'] = array();
        $messages = array_merge( $_SESSION['tm_admin_notes'], self::$s_notes);
        unset($_SESSION['tm_admin_notes']);

        if( is_object( $HTML = self::sget_HTML(true) ) ){
            foreach( $messages as $note ) {
                $HTML->s_div( $note['attr'] )->b_p( $note['inner'] )->end();
            }
        } else {
            echo '<div class="updated info">'.
                '<p><strong>!THE MASTER INFO:</strong> !HTML Class is not available. Install the !HTML Plugin for full features.</p></div>';
            
            foreach( $messages as $note ) {
                echo '<div class="updated" style="border-color: #dfdfdf; background-color: #fcfcfc;"><p>' . $note['inner'] . '</p></div>';
            }
        }
        self::$s_adminNoticesSent = true;
    }
    
    /** Setter for Admin Messages
     *
     * @param string $message the message
     * @param mixed $attr optional attrs in HTML Class style default "updated"
     * @return void
     * @access protected
     * @date Jul 28th 2011
     */
    protected function set_adminMessage($message, $attr = 'blank', $session = false) {
        if( is_string( $attr ) 
         && !strstr( $attr, '|' )
         && !in_array( $attr, array( 'updated', 'error' ))
        ) {
            $attr .= ' updated';
        }
        if(empty($message))
            return;
        if(!$session)
            self::$s_notes[md5($message)] = array('inner' => $message, 'attr' => $attr);
        else {
            $_SESSION['tm_admin_notes'][md5($message)] = array('inner' => $message, 'attr' => $attr);
        }
    }
    
    /** returns the current user or a specific key of current user
     *
     * @param string the key name or null for User Object
     * @return mixed
     * @access public
     * @date Jul 29th 2011
     */
    public function get_user($key = null) {
        if(!isset(self::$sCurrentUser)) {
            self::$sCurrentUser = get_userdata($GLOBALS['user_ID']);
        }
        if($key == null)
            return self::$sCurrentUser;
        else
            return X\THETOOLS::rget(self::$sCurrentUser, $key);
    }
    
    public function set_user($key, $value) {
        $this->get_user();
        self::$sCurrentUser->$key = $value;
    }
    
    /** returns "the_content()"
     *
     * @param object $post post object, containing the content
     * @return string
     * @access public
     * @date Jul 29th 2011
     */
    public function get_filtered_content($post) {
        $content = str_replace(']]>', ']]&gt;', apply_filters('the_content', $post->post_content));
        if(substr($content, 0, 5) == '<p><p' && $this->minify(substr($content, strlen($content)-9, 9), true) == '</p></p>') {
            $content = substr($content, 3, strlen($content)-7);
        }
        return $content;
    }
    
    /**
     * @deprecated since 3.0
     */
    public function fireContentTag($tag) {
        X\THEDEBUG::deprecated('Wordpress\'s do_shortcode()', false);
        // http://codex.wordpress.org/Function_Reference/do_shortcode      
    }
    
    /**
     * This function hookes into "the_content" and replaces [$tag] with $callback
     *
     * @access public
     * @deprecated since 3.0 
     * @param string $tag the [tag] that should be replaced
     * @param mixed $callback array or string of method or function containing the replacement
     * @return void
     */
    protected function add_contentTag($tag, $callback) {
        X\THEDEBUG::deprecated('Wordpress\'s add_shortcode()', false);
        // http://codex.wordpress.org/Shortcode_API
    }
    
    /**
     * The hook callback from add_contentTag() called on "the_content"
     *
     * @access public
     * @deprecated since 3.0
     * @param string $content the content string
     * @return string the new content string
     */
    public static function do_ContentTags($content) {
        X\THEDEBUG::deprecated('Wordpress\'s do_shortcode()', false);
        // http://codex.wordpress.org/Function_Reference/do_shortcode
    }
    
    /** killer for THEBASE::echo_sources(), sources will be included by
     *
     * @return void
     */
    public function echo_sources( $admin = false ) {
    }
    public function force_echo_sources() {
        parent::echo_sources();
    }
        
    
    public function get_models(
        $modelname,
        $conditions = null,
        $orderby = null,
        $oder = 'DESC',
        $modelInit = array()
    )
    {
        $fullModelname = $this->namespace.'\models\\'.$modelname;

        if (!class_exists($fullModelname)) {
            throw new \Exception('Model "'.$fullModelname.'" not available/existing for THEBASE::get_models().', 1);
            return false;
        }
        
        if (!isset($fullModelname::$table) ||
            empty( $fullModelname::$table) ||
            !is_string(($table = $fullModelname::$table))
        ) {
            throw new Exception($fullModelname . '::$table not defined - unable to get models.', 1);
            return false;
        }
        
        $and = "\n";
        if (is_array($conditions)) {
            foreach ($conditions as $key => $value) {
                $value = (is_numeric($value) ? '' : '"').$value.(is_numeric($value) ? '' : '"');
                $and .= 'AND '.$key.' = '.$value."\n";
            }
        }
        
        $orderby = $orderby == null ? '' : 'ORDER BY '.$orderby.' '.$oder;

        global $wpdb;
        $query = 'SELECT *
            FROM '.$table.'
            WHERE 1 = 1'.
            $and . $orderby.
            ';';
        $r = array();
        
        // $this->diebug($query);
        
        foreach ($wpdb->get_results($query) as $result) {
            $temp = new $fullModelname($result);
            foreach ($modelInit as $func => $args) {
                call_user_func(array($temp, $func), $args);
            }
            array_push($r, $temp);
        };
        return $r;
    }
    
    public function twpm_login_head() {
        if( isset($_GET['redirect_to']) 
         && isset($_GET['forceRederect'])
         && ( $_GET['forceRederect'] == 'true' || $_GET['forceRederect'] == 'hash' )
        ) {
            THEBASE::reg_jsVar('ajaxurl', admin_url('admin-ajax.php'));
            THEBASE::reg_jsVar('twpm_rederect', true);
            THEBASE::echo_jsVars();
            X\THETOOLS::session();
            $_SESSION['twpm_loginrederect'] = $_GET['redirect_to'];
            // self::inst()->debug( $_SESSION );
        }
    }
    
    private static function _get_loginRederectUrl( $full = true ) {
        X\THETOOLS::session();
        $t = false;
        if( isset( $_SESSION['twpm_loginrederect'] ) ) {
            $t = $_SESSION['twpm_loginrederect'];
            if( $full && isset( $_SESSION['twpm_loginrederectHash'] ) ) {
                $t .= '#'.$_SESSION['twpm_loginrederectHash'];
            }
        }
        return $t;
    }
    
    private function _del_loginRederectSession() {
        X\THETOOLS::session();
        if( isset( $_SESSION['twpm_loginrederect'] ) ) 
            unset( $_SESSION['twpm_loginrederect'] );
        if( isset( $_SESSION['twpm_loginrederectHash'] ) ) 
            unset( $_SESSION['twpm_loginrederectHash'] );
    }
    
    public function twpm_wp_login() {
            // self::inst()->diebug( $_SESSION );
            // die();
        if( ( $t = self::inst()->_get_loginRederectUrl() ) ) {

            self::inst()->_del_loginRederectSession();
            header('Location: '.$t);
            exit();
        }
    }
    
    public function twpm_ajax_hashRederect() {
        // $t = self::inst();
        // $t->session();
        $_SESSION['twpm_loginrederectHash'] = str_replace('#', '', $t->get_cleanedPath($_REQUEST['hash']));
        $t->_exit('ok', '', 0);
    }
    
    public function twpm_loginMsg( $msg ) {
        if( ( $t = self::_get_loginRederectUrl( false ) ) ) {
            $HTML = THEBASE::sGet_HTML();
            $link = $HTML->r_a(
                $t.$HTML->r_span(null, '#tm_loginhash'),
                'href='.$HTML->escape_mBB($t).'|#tm_loginlink'
            );
            $msg = $HTML->r_p(
                sprintf( 
                    __('You will be redirected to "%s" after you\'ve successfully logged in.', 'themaster'),
                    $link
                ),
                'message'
            ).$msg;
        }
        return $msg;
    }
        
}
?>