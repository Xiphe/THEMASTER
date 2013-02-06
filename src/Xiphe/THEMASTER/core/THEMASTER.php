<?php
namespace Xiphe\THEMASTER\core;

use Xiphe as X;

/**
 * THEMASTER is last class in !THE MASTERS that does not make intense use of 
 * Wordpress functionality. It can be extended by non-WP projects.
 *
 * @copyright Copyright (c) 2013, Hannes Diercks
 * @author    Hannes Diercks <xiphe@gmx.de>
 * @version   3.0.0
 * @link      https://github.com/Xiphe/THEMASTER/
 * @package   THEMASTER
 */
class THEMASTER extends THESETTINGS {

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

    private static $s_initiatedProjects = array();
    private static $s_toBeInitiated = array();
    private static $_masterCronstructed = false;


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
     * @access public
     * @param   array   $initArgs   the initiation arguments
     * @return mixed returns false if a required initiation Argument is missing
     *         or Instance if Subclass is Singleton
     */
    function __construct( $initArgs )
    {
        if ((!isset($this->constructing) || $this->constructing !== true)
        && is_object(($r = THEBASE::check_singleton_()))) {
            return $r;
        } else {
            $this->constructing = true;
        }

        $this->add_requiredInitArgs_( array(
            'namespace',
            'basePath',
            'baseUrl',
            'projectName'
        ) );

        if( !self::$s_initiated ) {
            THEBASE::sRegister_callback( 'afterBaseS_init', array(THE::MASTER, 'sinit' ) );
        }

        return parent::__construct( $initArgs );
    }

    /**
     * One time initiaton.
     */
    public static function sinit() {
        if (!self::$s_initiated) {

            if (defined('XIPHE_HTML_AVAILABLE') && XIPHE_HTML_AVAILABLE === true) {
                // Register !html as available plugin
                self::$s_initiatedProjects[] = 'html';
                self::$s_initiatedProjects[] = '!html';
            }

            // Register themaster as available plugin.
            self::$s_initiatedProjects[] = 'themaster';
            self::$s_initiatedProjects[] = '!themaster';

            // Prevent this from beeing executed twice.
            self::$s_initiated = true;
        }
    }

    protected function _masterInit() {
        if( !isset( $this ) ) {
            throw new Exception("_masterInit should not be called staticaly.", 1);
        }
        if( isset( $this->_masterInitiated ) && $this->_masterInitiated === true ) {
            return;
        }

        /*
         * If the project has required plugins and one of them is not available:
         */
        if( isset( $this->requiredPlugins )
         && ( is_array( $this->requiredPlugins ) || is_object( $this->requiredPlugins ) )
         && !X\THETOOLS::group_in_array($this->requiredPlugins, self::$s_initiatedProjects)
        ) {
            /*
             * Register for later initiation into the toBeInitiated array.
             */
            if( !isset( self::$s_toBeInitiated[ $this->textdomain ] ) ) {
                self::$s_toBeInitiated[ $this->textdomain ] = array(
                    'required' => $this->requiredPlugins,
                    'method' => array( $this, '_masterInit' ),
                    'type' => $this->projectType
                );
                THEBASE::sRegSingleton(false, get_class($this), true);
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
                
                if (class_exists(THE::WPMASTER)) {
                    return true;
                } else {
                    $this->_masterInitiated();
                }
            }
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
        } catch (\Exception $e) {
            if (class_exists(THE::WPMASTER)) {
                THEWPMASTER::sTryError( $e );
            } else {
                self::sTryToError( $e );
            }
        }
    }

    static public function sTryToError( $e ) {
        echo $e->getMessage() . "\nFile: " . $e->getFile() . "\nLine: " . $e->getLine();
    }

    public function get_uninitiated() {
        return self::$s_toBeInitiated;
    }

    public function get_initiated() {
        return self::$s_initiatedProjects;
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
        if( $this->_get_setting( 'debug', X\THETOOLS::get_textID( THEMASTER_PROJECTFILE ) ) === true
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

}
// if( !defined('THEMINIMASTERAVAILABLE') ) {
//  $GLOBALS['THEMINIMASTER'] = new THEMASTER('MINIMASTER');
//  define('THEMINIMASTERAVAILABLE', true);
// }
?>