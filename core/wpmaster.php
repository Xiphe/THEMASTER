<?php
/*
 !THE MASTER - a base for plugins and themes
 Copyright (C) 2012 Hannes Diercks

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

/*
 * Include parent class.
 * Include the model basic class
 */
require_once( 'wpupdates.php' );
require_once( 'wpmodel.php' );

/**
 * THEWPMASTER is the last class inside !THE MASTER and the one that
 * Wordpress plugins and themes should extend.
 *
 * @copyright Copyright (c) 2012, Hannes Diercks
 * @author  Hannes Diercks <xiphe@gmx.de>
 * @version 3.0.0
 * @link    https://github.com/Xiphe/-THE-MASTER/
 * @package !THE MASTER
 */
class THEWPMASTER extends THEWPUPDATES {


	/* ------------------ */
	/*  STATIC VARIABLES  */
	/* ------------------ */
	

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
	 * Array of Posts
	 *
	 * TODO: Check if needed.
	 * @access private
	 * @var    array
	 */
	// private static $s_postCache = array();
	

	/**
	 * Available registered content tags.
	 *
	 * @access private
	 * @var    array
	 */
	private static $s_contentTags = array();

	/**
	 * Flag to check if the admin notices had allready been sent out.
	 *
	 * @access private
	 * @var    bool
	 */
	private static $s_adminNoticesSent = false;
	
	private static $s_ftp_conn_id;
	private static $s_folderStructure = array(
		'classes' => 0755,
		// 'models' => 0755,
		'res' => array(
			'chmod' => 0755,
			'css' => 0777,
			'includes' => 0755,
			'js' => 0777,
			'less' => 0755,
		),
		// 'views' => 0755,
	);


	/* ---------------------- */
	/*  CONSTRUCTION METHODS  */
	/* ---------------------- */

	/**
	 * The Constructor method
	 *
	 * @param  array $initArgs the initiation arguments.
	 * @access public
	 * @return void
	 */
	final public function __construct( $initArgs ) {
		if( is_object( ( $r = THEBASE::check_singleton_( get_class( $this ) ) ) ) ) {
			return $r;
		} else {
			$this->constructing = true;
		}

		/*
		 * Register "theversion" as required initiation argument.
		 */
		$this->add_requiredInitArgs_( 'version' );

		if( !self::$s_initiated ) {
			THEBASE::sRegister_callback( 'afterBaseS_init', array( 'THEWPMASTER', 'sinit' ) );
		}

		/*
		 * Pass ball to parent.
		 */
		parent::__construct( $initArgs );
	}
	
	/**
	 * One time initiaton. Called by THE BASE after construction.
	 *
	 * @access public
	 * @return void
	 */
	public static function sinit() {
		if( !self::$s_initiated ) {
			/*
			 * Register basic less and js files.
			 */
			if( function_exists( 'is_admin' ) ) {
				if( !is_admin() ) {
					THEBASE::reg_less( 'base' );
				} else {
					THEBASE::reg_adminLess( 'tm-admin' );
					THEBASE::reg_adminJs( 'tm-admin' );
				}
			}

			/*
			 * Call aditional hooks.
			 */
			self::s_hooks();

			/*
			 * Register hash rederect for login.
			 */
			if( isset( $GLOBALS['pagenow'] )
			 && $GLOBALS['pagenow'] == 'wp-login.php'
			 && isset($_GET['redirect_to']) 
			 && isset($_GET['forceRederect'])
			 && $_GET['forceRederect'] == 'hash'
			) {
				self::reg_js('twpm_login');
			}

			/*
			 * Define availability constant.
			 */
			define( 'WPMASTERAVAILABE', true );

			/*
			 * Prevent this from beeing executed twice.
			 */
			self::$s_initiated = true;
		}
	}

	/**
	 * Registeres one-time hooks for thewpmaster.
	 *
	 * @access private
	 * @return void
	 */
	private static function s_hooks() {
		// Return if Wordpress is not available.
		if( !function_exists( 'add_action' ) ) return;

		// Register verry own one time init when wp is available.
		add_action( 'init', array( 'THEWPMASTER', 'twpm_wpinit' ), 100, 0 );

		// Register callbacks for printing js-variables.
		add_action( 'wp_head', array( 'THEWPMASTER', 'twpm_print_jsVars' ), 0, 0 );
		add_action( 'admin_head', array( 'THEWPMASTER', 'twpm_print_adminJsVars' ), 0, 0 );

		add_action( 'login_head', array( 'THEWPMASTER', 'twpm_login_head' ) );
		add_action( 'wp_login', array( 'THEWPMASTER', 'twpm_wp_login' ) );
		add_filter( 'login_message', array( 'THEWPMASTER', 'twpm_loginMsg' ) );
		add_action( 'wp_ajax_twpm_hashRederect', array( 'THEWPMASTER', 'ajax_hashRederect' ));
		add_action( 'wp_ajax_nopriv_twpm_hashRederect', array( 'THEWPMASTER', 'ajax_hashRederect' ));

		// Register callback for admin notices.
		add_action( 'admin_notices', array( 'THEWPMASTER', 'twpm_admin_notices' ) );

		// Register callback for printing debugs from THEDEBUG.
		add_action( 'shutdown', array( 'THEDEBUG', 'print_debugcounts' ), 0, 0 );
		if( THEDEBUG::get_mode() === 'summed' ) {
			add_action( 'shutdown', array( 'THEDEBUG', 'print_debug' ), 0, 0 );
		}

		// Register callback for plugin dependency check.
		add_action( 'after_setup_theme', array( 'THEWPMASTER', 'check_initiated' ) );
	}


	/* --------------------------- */
	/*  MASTER INITIATION METHODS  */
	/* --------------------------- */
	
	/**
	 * Initiation for a new Instance of THEWPMASTER, generates a new Submaster XYMaster
	 *
	 * @param array $initArgs see $this->requiredInitArgs for required keys
	 * @return void
	 * @access private
	 * @date Jul 28th 2011
	 */
	final protected function _masterInit() {
		if( !isset( $this ) ) {
			throw new Exception("_masterInit should not be called staticaly.", 1);
		}
		if( isset( $this->_masterInitiated ) && $this->_masterInitiated === true ) {
			return;
		}

		if( parent::_masterInit() ) {
			$this->_versionCheck();
			
			if( is_dir( $this->basePath . DS . 'languages' ) ) {
				load_plugin_textdomain( $this->textdomain, false, $this->basePath . DS . 'languages' . DS );
			}

			if( method_exists( $this, 'activate' ) ) {
				register_activation_hook( $this->projectFile, array( $this, 'activate' ) );
			}

			if( method_exists( $this, 'deactivate' ) ) {
				register_activation_hook( $this->projectFile, array( $this, 'deactivate' ) );
			}

			$this->_masterInitiated();
		}
	
		// TODO: REIMPLEMENT	
		// if(!isset(self::$_hooked['masterVersionCheck'])) {
		// 	$this->_versionCheck('themaster', $this);
		// 	self::$_hooked['masterVersionCheck'] = true;
		// }
		
	}

	/**
	 * Checks the last stored version from db against the current project version
	 * and calls the "update" method if the project is newer.
	 * The update method should return true to get it's new version stored to db.
	 *
	 * @access private
	 * @return void
	 */
	private function _versionCheck() {
		if( !isset( $GLOBALS['pagenow'])
		 || ( $GLOBALS['pagenow'] != 'plugins.php' && $GLOBALS['pagenow'] != 'themes.php' )
		 || !function_exists( 'get_option' )
		 || !function_exists( 'update_option' )
		) {
			return;
		}

		if( ( defined( ( $name = 'THEVERSION_' . strtoupper( $this->textdomain ) ) ) && ( $version = constant( $name ) ) )
		 || ( isset( $this->version ) && ( $version = $this->version ) )
		) {
			$name = strtolower( $name );
			if( version_compare( get_option( $name ), $version, '<' ) ) {
				if( isset( $this->folderStructure ) && is_array( $this->folderStructure ) ) {
					THEBUILDER::check_folderStructure( $this->folderStructure, $this->basePath );
				}

				if( $this->update() ) {
					update_option($name, $version);
				} else {
					throw new Exception('Error: Update method for "'.$name.'" failed. (return !== true)', 1);
				}
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
	public function check_initiated() {
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

	/* ------------------------- */
	/*  PUBLIC INTERNAL METHODS  */
	/* ------------------------- */
	
	/**
	 * This method is called if !THE MASTER is being updated.
	 *
	 * @access private
	 * @return bool always true
	 */
	protected static function _masterUpdate() {
		$this->debug( 'THEWPMASTER::_masterUpdate called' );
		$this->_check_folderStructure( self::$s_folderStructure, dirname( THEMASTER_PROJECTFILE ) . DS );
		return parent::_masterUpdate();
	}

	/**
	 * Activation method for !THE MASTER
	 *
	 * @access private
	 * @return void
	 */
	public static function _masterActivate() {
		return parent::_masterActivate();
	}
	
	/**
	 * This method catches errors from THEMASTER::sTryTo() method
	 * and prints them as admin messages.
	 *
	 * @access public
	 * @return void
	 */
	public static function sTryError( $e ) {
		$msg = sprintf( 
			__( '**THEMASTER RUNTIME Exception:**/||Message: //%1$s///||In File: //%2$s// &bull; Line //%3$s//', 'themaster' ),
			$e->getMessage(),
			$e->getFile(),
			$e->getLine()
		);
		if( !self::$s_adminNoticesSent ) {
			self::set_adminMessage( 
				$msg,
				'error'
			);
		} else {
			THEDEBUG::debug( $msg, 'error', 4 ); 
		}
	}
	
	/**
	 * TODO: CHECK IF NEEDED AND GIVE CREDITS IF TRUE.
	 *
	 */
	private function _get_pluginSymlinkPath($file) {
		$this->debug( 'THEDBMASTER::_get_pluginSymlinkPath() called.' );
	    // If the file is already in the plugin directory we can save processing time.
	    if ( preg_match( '/'.preg_quote( WP_PLUGIN_DIR, '/' ).'/i', $file ) ) return $file;
	
		$path = '';
	    // Examine each segment of the path in reverse
	    foreach ( array_reverse( explode( '/', $file ) ) as $segment )
	    {
	        // Rebuild the path starting from the WordPress plugin directory
	        // until both resolved paths match.
	
	        $path = rtrim($segment .'/'. $path, '/');       
	
	        if ( __FILE__ == realpath( WP_PLUGIN_DIR . '/' . $path ) )
	        {
	            return WP_PLUGIN_DIR . '/' . $path;
	        }
	    }
	
	    // If all else fails, return the original path.
	    return $file;
	}
	
	public static function sMasterInitError( $e, $args ) {
		switch( $e->getCode() ) {
			case 1:
				self::set_adminMessage( 
					sprintf( __( '**!THE MASTER ERROR: Master Class File for Plugin "%1$s" not found.**/||The Plugin should have a ' . 
						'file called "master.php" defining a class called %2$s located in/||//%3$s//.', 'themaster' ),
						$args['projectName'],
						strtoupper( $args['prefix'] ) . 'Master',
						$args['basePath'] . DS . 'classes'
					),
					'error'
				);
				break;
			case 4: 
				self::set_adminMessage( 
					sprintf( __( '**!THE MASTER ERROR: HTML is not available.**/||The Plugin "%s" should have ' . 
						'been initiated along with it\'s own HTML-Class but !HTML seems to be unavailale.', 'themaster' ),
						$args['projectName']
					),
					'error'
				);
				break;
			default:
				self::set_adminMessage( 
					sprintf( __( '**!THE MASTER ERROR: Master Class for Plugin "%1$s" not found.**/||The Plugin should have a ' . 
						'file called "master.php" defining a class called %2$s located in/||//%3$s//.', 'themaster' ),
						$args['projectName'],
						strtoupper( $args['prefix'] ) . 'Master',
						$args['basePath'] . DS . 'classes'
					),
					'error'
				);
				break;
		}
	}
	
	public function twpm_print_adminJsVars() {
		self::twpm_print_jsVars(true);
	}
	public function twpm_print_jsVars($admin = false) {
		if( defined('HTMLCLASSAVAILABLE') ) {
			$HTML = self::inst()->get_HTML();
			$source = $admin ? THEBASE::sGet_registeredAdminJsVars() : THEBASE::sGet_registeredJsVars();
			$HTML->sg_script();
			foreach($source as $name => $var) {
				$HTML->blank('var '.$name.' = '.json_encode($var).';');
			}
			$HTML->end();
		}
	}
	
	public function twpm_wpinit() {
		wp_enqueue_script('jquery');

		foreach( THEBASE::sGet_registeredSources() as $dest => $sources) {
			foreach($sources as $type => $files) {
				foreach($files as $file => $url) {
					if($type == 'js') {
						wp_enqueue_script('twpm.'.$file, $url);
					} elseif($type == 'css') {
						wp_enqueue_style('twpm.'.$file, $url);
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
	public function _hooks() {
		parent::_hooks();
		foreach( array( 'actions_', 'filters_' ) as $hooktype ) {
			if( isset( $this->$hooktype ) && is_array( $this->$hooktype ) ) {
				foreach ( $this->$hooktype as $k => $hook ) {
					$e = explode( '|', $hook );
					$method = is_int( $k ) ? $e[0] : $k;
					$e[-1] = $e[0];
					$e[0] = array( $this, $method );
					if( method_exists( $this, $method ) ) {
						ksort( $e );
						call_user_func_array(
							$hooktype === 'actions_' ? 'add_action' : 'add_filter',
							$e
						);
					} else {
						throw new Exception('THEMASTER ERROR: Should call Hook ' . $e[-1] . ' to unexistent method ' . $method . ' in class ' . get_class( $this ) . '.', 1);
					}
				}

			} 
			
		}
		// if( method_exists( $this, 'wpinit' ) ) {
		// 	$prio = isset( $this->wpinitPriority ) ? $this->wpinitPriority : null;
		// 	add_action( 'init', array( $this, 'wpinit' ), $prio );
		// }
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
			self::$s_notes[] = array('inner' => $message, 'attr' => $attr);
		else {
			$_SESSION['tm_admin_notes'][] = array('inner' => $message, 'attr' => $attr);
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
			return self::recursive_get(self::$sCurrentUser, $key);
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
	
	public function fireContentTag($tag) {
		foreach(self::$s_contentTags as $cTag) {
			if( $tag == $cTag['tag'] ) {
				echo call_user_func( $cTag['cb'] );
				break;
			}
		}		
	}
	
	/** This function hookes into "the_content" and replaces [$tag] with $callback
	 *
	 * @param string $tag the [tag] that should be replaced
	 * @param mixed $callback array or string of method or function containing the replacement
	 * @return void
	 * @access public
	 * @date Dez 14th 2011
	 */
	protected function add_contentTag($tag, $callback) {
		if(!isset(self::$_hooked['the_content'])) {
			add_filter('the_content', array('THEWPMASTER', 'do_ContentTags'));
			self::$_hooked['the_content'] = true;
		}
		self::$_contentTags[] = array('tag' => $tag, 'cb' => $callback);
	}
	
	/** The hook callback from add_contentTag() called on "the_content"
	 *
	 * @param string $content the content string
	 * @return string the new content string
	 * @date Dez 14th 2011
	 */
	public static function do_ContentTags($content) {
		foreach(self::$_contentTags as $contentTag) {
			extract($contentTag);
			if(preg_match('/\['.$tag.'\]/', $content)) 
				$content = preg_replace('/(\<p\>)?\['.$tag.'\](\<\/p\>)?/', call_user_func($cb), $content);
		}
		return $content;
	}
	
	/** killer for THEBASE::echo_sources(), sources will be included by
	 *
	 * @return void
	 * @date Dez 15th 2011
	 */
	public function echo_sources( $admin = false ) {
	}
	public function force_echo_sources() {
		parent::echo_sources();
	}
	
	public function get_post($post_ID, $key = null) {
		if(!isset(self::$_postCache[$post_ID])) {
			$this->_set_temp_globals();
			$query = new WP_Query('ID='.$post_ID);
			$post = apply_filters('the_posts', $query->the_post(), $query);
			$this->_unset_temp_globals();
			self::$_postCache[$post_ID] = $post[0];
		}
			
		if($key == null)
			return self::$_postCache[$post_ID];
		if(count(($e = explode('|', $key))) > 0) {
			$r = self::$_postCache[$post_ID];
			foreach($e as $subkey) {
				if(is_object($r))
					$r = $r->$subkey;
				elseif(is_array($r)) {
					$r = $r[$subkey];
				} else
					break;
			}
			return $r;
		} elseif(isset(self::$_postCache[$post_ID]->$key))
			return self::$_postCache[$post_ID]->$key;
	}
	
	
	public function post_authored_by_user($post_ID, $user_ID = null) {
		$user_ID = $user_ID === null ? $this->get_user('ID') : intval($user_ID);
		if($this->get_post($post_ID, 'post_author') == $user_ID)
			return true;
		return false;
	}
	
	public function get_models( $modelname, $conditions = null, $orderby = null, $oder = 'DESC', $modelInit = array() ) {
		$fullModelname = strtoupper( $this->prefix ) . $modelname;
		if( !class_exists( $fullModelname ) ) {
			throw new Exception('Model "' . $fullModelname . '" not available/existing for THEBASE::get_models().', 1);
			return false;
		}
		
		if( !isset( $fullModelname::$table ) 
		 || empty( $fullModelname::$table ) 
		 || !is_string( ( $table = $fullModelname::$table ) )
		) {
			throw new Exception($fullModelname . '::$table not defined - unable to get models.', 1);
			return false;
		}
		
		$and = "\n";
		if( is_array( $conditions ) ) {
			foreach ($conditions as $key => $value) {
				$value = (is_numeric( $value ) ? '' : '"') . $value . (is_numeric( $value ) ? '' : '"');
				$and .= 'AND ' . $key . ' = ' . $value . "\n";
			}
		}
		
		$orderby = $orderby == null ? '' : 'ORDER BY ' . $orderby . ' ' . $oder;

		global $wpdb;
		$query = 'SELECT *
			FROM ' . $table . '
			WHERE 1 = 1' .
			$and . $orderby .
			';';
		$r = array();
		
		// $this->diebug($query);
		
		foreach( $wpdb->get_results( $query ) as $result ) {
			$temp = new $fullModelname( $result );
			foreach( $modelInit as $func => $args ) {
				call_user_func(array($temp, $func), $args);
			}
			array_push($r, $temp);
		};
		return $r;
	}
	
	public function twpm_login_head() {
		if(	isset($_GET['redirect_to']) 
		 && isset($_GET['forceRederect'])
		 && ( $_GET['forceRederect'] == 'true' || $_GET['forceRederect'] == 'hash' )
		) {
			$t = self::inst();
			$t->reg_jsVar('ajaxurl', admin_url('admin-ajax.php'));
			$t->reg_jsVar('twpm_rederect', true);
			$t->echo_jsVars();
			$t->session();
			$_SESSION['twpm_loginrederect'] = $_GET['redirect_to'];
			// self::inst()->debug( $_SESSION );
		}
	}
	
	private function _get_loginRederectUrl( $full = true ) {
		self::inst()->session();
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
		self::inst()->session();
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
	
	public function ajax_hashRederect() {
		// $t = self::inst();
		// $t->session();
		$_SESSION['twpm_loginrederectHash'] = str_replace('#', '', $t->get_cleanedPath($_REQUEST['hash']));
		$t->_exit('ok', '', 0);
	}
	
	public function twpm_loginMsg( $msg ) {
		if( ( $t = self::inst()->_get_loginRederectUrl( false ) ) ) {
			$msg = self::inst()->get_HTML()->r_p(
				sprintf( 
					__('You will be redirected to %s after you\'ve successfully logged in.', 'themaster'),
					'"'.$t.'<span id="twpm_hash"></span>"'
				),
				'message'
			).$msg;
		}
		return $msg;
	}
		
}
if( !defined( 'THEMINIWPMASTERAVAILABLE' ) ) {
	$GLOBALS['THEMINIWPMASTER'] = new THEWPMASTER( 'MINIMASTER' );
	define( 'THEMINIWPMASTERAVAILABLE', true );
}
?>