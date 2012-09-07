<?php
namespace Xiphe\THEMASTER;

/*
 * Include parent class.
 */
require_once(THEMASTER_COREFOLDER.'wpbuilder.php');

/**
 * THEWPSETTINGS is used to manage Master Settings stored in the Wordpress DB.
 * Can fall back to THESETTINGS.
 *
 * @copyright Copyright (c) 2012, Hannes Diercks
 * @author    Hannes Diercks <xiphe@gmx.de>
 * @version   3.0.0
 * @link      https://github.com/Xiphe/-THE-MASTER/
 * @package   !THE MASTER
 */
class THEWPSETTINGS extends THEWPBUILDER {
	// TODO: ADD FUNCTIONALITY TO DELETE SETTINGS


	/* ------------------ */
	/*  STATIC VARIABLES  */
	/* ------------------ */

	/* PRIVATE */

	// Turns true after first initiation.
	private static $s_initiated = false;

	// Holds all current options.
	private static $s_userSettings = array();
	private static $s_defaultSettings = array();

	// Holds all registered setting options.
	private static $s_settings = array();

	private static $s_themeSettings;

	private static $s_storeSettings = false;


	/* ---------------------- */
	/*  CONSTRUCTION METHODS  */
	/* ---------------------- */


	/**
	 * The Constructor method
	 *
	 * @param	array	$initArgs	the initiation arguments
	 */
	function __construct( $initArgs ) {
		if( !isset( $this->constructing ) || $this->constructing !== true ) {
			throw new Exception( "ERROR: THEWPSETTINGS is not ment to be constructed directly.", 1 );
			return false;
		}

		if( !self::$s_initiated ) {
			self::$s_settings[ THETOOLS::get_textID( THEMASTER_PROJECTFILE ) ] = array(
				'name' => 'THE MASTER',
				'settings' => array( 'Xiphe\THEMASTER\THEWPSETTINGS', 'tmgl_settings' )
			);

			// Get all options from database.
			if( function_exists( 'get_option' ) ) {
				self::$s_userSettings = get_option( 'Xiphe\THEMASTER\allsettings', array() );
			}
			THEBASE::sRegister_callback( 'afterBaseS_init', array( 'Xiphe\THEMASTER\THEWPSETTINGS', 'sinit' ), 1, null, null, 1 );
		}

		parent::__construct($initArgs);
	}

	/**
	 * One time initiaton.
	 */
	public static function sinit() {
		if( !self::$s_initiated ) {
			// Register one-time-hooks.
			self::s_hooks();

			THEBASE::sRegister_callback( 'beforeMasterInit', array(
				'Xiphe\THEMASTER\THEWPSETTINGS', 'sCheckSettings'
			), '*' );

			if (function_exists('add_action')) {
                add_action('shutdown', array('Xiphe\THEMASTER\THEWPSETTINGS','sSaveTheSettings'));
            }

			// Prevent this from beeing executed twice.
			self::$s_initiated = true;
		}
	}

	/**
	 * Registeres one-time hooks for thewpupdates.
	 */
	private static function s_hooks() {
		if( function_exists( 'add_action' ) ) {
			add_action( 'admin_init', array( 'Xiphe\THEMASTER\THEWPSETTINGS', 'sAdmin_init' ) );
			add_action( 'admin_menu', array( 'Xiphe\THEMASTER\THEWPSETTINGS', 'sAdmin_menu' ) );
		}
	}
		
	protected function _masterInit() {
		if( !isset( $this ) ) {
			throw new Exception("_masterInit should not be called staticaly.", 1);
		}
		if( isset( $this->_masterInitiated ) && $this->_masterInitiated === true ) {
			return;
		}

		if( parent::_masterInit() ) {
			return true;
		}
	}

	public static function sCheckSettings( $obj ) {
		if( method_exists( $obj, 'settings' ) ) {
			self::$s_settings[ $obj->textID ]
				= array(
					'name' => $obj->projectName,
					'settings' => array( $obj, 'settings' )
				);
		}
	}


	public function get_setting( $key, $textID = null ) {
		if( $textID === null && isset( $this ) ) {
			$textID = $this->textID;
		}
		return self::_get_setting( $key, $textID );
	}

	public static function sGet_setting( $key, $textID = null ) {
		return self::_get_setting( $key, $textID );
	}

	public static function _get_setting( $key, $textID = null, $noDefaults = false, $silent = false ) {
		if( $textID === null ) {
			throw new Exception( 'Tried to get setting "' . $key . '" without textID.' );
			return;
		}
		// THEDEBUG::debug(  self::$s_settings, 'self::$s_settings' );

		// Check if setting was forced to sth.
		if( ( $setting = THESETTINGS::_get_forcedSetting( $key, $textID ) ) !== null ) {
			return $setting;
		}

		// Check for settings from database.
		elseif( isset( self::$s_userSettings[$textID][$key] ) ) {
			return self::$s_userSettings[$textID][$key];

		// Check for settings defined by constants.
		} elseif( ( $setting = THESETTINGS::_get_setting( $key, $textID, true, true ) ) !== null ) {
			return $setting;

		// Check for default settings in THEWPSETTINGS.
		} elseif( ( $settings = self::s_getSettings( $textID ) )
		 && isset( $settings[$key]['default'] )
		) {
			return $settings[$key]['default'];

		// Check for default settings in THESETTINGS.
		} elseif( ( $setting = THESETTINGS::_get_setting( $key, $textID ) ) !== null ) {
			return $setting;

		// ERROR.
		} else {
			throw new Exception('Tried to get non-existent Setting "'.$key.'".');
		}
	}

	private static function s_getSettings( $textID ) {
		if ( isset( self::$s_settings[$textID]['settings'] ) ) {
			if ( is_callable( self::$s_settings[$textID]['settings'] ) ) {
				self::$s_settings[$textID]['settings'] =
					call_user_func( self::$s_settings[$textID]['settings'] );
			}
			return self::$s_settings[$textID]['settings'];
		}
		return false;
	}

	public static function sAdmin_init() {
		add_action( 'wp_ajax_tm-savesetting', array( 'Xiphe\THEMASTER\THEWPSETTINGS', 'sSave_settings' ) );
	}

	public static function sAdmin_menu() {
		if( current_user_can( 'manage_options' ) ) {
			foreach( self::$s_settings as $k => $s ) {
				if( pathinfo( $k, PATHINFO_EXTENSION ) !== 'css' && $GLOBALS['pagenow'] === 'plugins.php'  ) {
					add_filter( 'plugin_action_links_' . $k , array( 'Xiphe\THEMASTER\THEWPSETTINGS', 'sInject_settingsLink' ), 10, 2 );
					add_action( 'after_plugin_row_' . $k, array( 'Xiphe\THEMASTER\THEWPSETTINGS', 'inject_settingsRow'), 10, 3 );
				} elseif( pathinfo( $k, PATHINFO_EXTENSION ) == 'css' ) {
					self::$s_themeSettings = $k;
					add_theme_page(
						__( 'Settings', 'themaster' ),
						__( 'Settings', 'themaster' ),
						'manage_options',
						'tm_themesettings',
						array( 'Xiphe\THEMASTER\THEWPSETTINGS', 'sAdd_themeSettings' )
					);
				}
			}
		}
	}

	public static function sAdd_themeSettings() {

		if( isset( self::$s_themeSettings ) && ( $k = self::$s_themeSettings ) !== false
		 && ( $allSettings = self::s_getSettings( $k ) ) !== false
		 && is_object( ( $HTML = self::get_HTML( true ) ) )
		) {
			$HTML->s_div( '.tm-settings body' )
				->h1( __( 'Theme Settings', 'themaster' ) )
				->s_div( '.tm-settingswrap' );
				// ->s_form( 'action=' . $GLOBALS['pagenow'] . '?page\=tm_themesettings' );

			$args = self::$s_settings[ $k ];

			foreach( $allSettings as $key => $setting ) {
				if( is_string( $setting ) ) {
					if( $setting === 'sep' || $setting === 'seperator' )
						$setting = array( 'type' => 'seperator' );
					elseif( is_string( $key ) ) {
						$setting = array(
							'type' => $key,
							'value' => $setting
						);
					} else {
						$setting = array(
							'type' => 'text',
							'value' => $setting
						);
					}
					$key = false;
				}
				self::s_buildSettingsRow( $key, $setting, $k, $HTML, $args );
			}

			$HTML->s_div( '.tm-settingwrap tm-savewrap' )
				->button( __( 'Save', 'themaster' ), 'button' )
				->s_span( 'tm-loading hidden' )
					->img( 'alt=loading...|src=' . get_admin_url() . 'images/wpspin_light.gif' )
				->end()
				->span( null, 'tm-message' )
				->hidden( 'name=tm-nonce|value=' . wp_create_nonce( 
					$k . 'tmsaveSettings'
				) )
				->hidden( 'name=tm-settingkey|value=' . $k )
				->hidden( 'name=action|value=tm-savesetting' )

			// $HTML
			// 	->s_div( '.tm-savewrap' )
			// 	->button( __( 'Save', 'themaster' ), '.button-primary|type=submit' )

				->end( '.tm-settings' );
		}
	}

	public static function sSave_settings() {
		$obj = self::inst();

		// var_dump( $_REQUEST );
		if( !isset( $_REQUEST['tm-settingkey'] ) ) {
			$obj->_exit( 'error', 'tm-settingkey not available.', 5 );
		}
		$sK = $_REQUEST['tm-settingkey']; // setting key

		if( !is_admin()
		 || !current_user_can( 'manage_options' )
		 || !isset( $_REQUEST['tm-nonce'] )
		 || !wp_verify_nonce( $_REQUEST['tm-nonce'], 
		 		$sK . 'tmsaveSettings'
		 	)
		) {
			$obj->_exit('error', 'Authentification error.', 1);
		}

		if( !isset( self::$s_settings[ $sK ])) {
			$obj->_exit( 'error', 'invalid tm-settingkey.', 6 );
		}

		$regOpts = self::s_getSettings( $sK );

		$opts = array();
		$cK = 'tm-setting_' . preg_replace( '/[^a-z0-9-_]/', '', $sK ) . '_';
		foreach( $_REQUEST as $k => $v ) {
			$v = stripslashes($v);
			if( substr( $k, 0, strlen( $cK ) ) == $cK ) { // IGNORE NON tm-setting_domain_ requests
				$rSK = substr( $k, strlen( $cK ), strlen( $k ) ); // request setting key.
				if( !isset( $regOpts[$rSK] ) ) {
					$obj->_exit( 'error', 'cheatin?', 4 );
				}

				switch( $regOpts[$rSK]['type'] ) {
					case 'dropdown':
					case 'select':
						if( !isset( $regOpts[$rSK]['args'][$v] ) )
							$obj->_exit( 'error', 'cheatin?', 3 );
						break;
					case 'checkbox':
						$v = $v == 'on' ? true : false;
						break;
					case 'input':
						if( isset( $regOpts[$rSK]['validation'] ) ) {
							$vltn = $regOpts[$rSK]['validation'];
							if( is_string( $vltn ) 
							 && !preg_match('/' . $vltn . '/', $v)
							) {
								if( isset( $regOpts[$rSK]['errorMessage'] )) {
									$obj->_r['errorMsg'] = $regOpts[$rSK]['errorMessage'];
								}
								$obj->_r['id'] = $k;
								$obj->_exit( 'validationError', 'mismatch on validation', 2 );
							}
						}
						break;
					default:
						break;

				}
				$opts[ $rSK ] = $v;
			}
		}


		self::$s_userSettings[$sK] = $opts;
		self::$s_storeSettings = true;

		$obj->_exit( 'ok', __( 'Options updated', 'themaster' ), -1 );
	}

	public static function sInject_settingsLink( $links, $k ) {
		$args = self::$s_settings[ $k ];
		if( is_object( ( $HTML = self::get_HTML(true) ))) {
			$settingLink = $HTML->r_a(__('Settings', 'themaster'), array(
				'title' => sprintf( __('Show/Hide Settings for %s', 'themaster'), $args['name'] ),
				'href' => '#' . $k . '/Settings',
				'class' => 'tm-settings'
			));
			return array_merge( array( 'tm-settings' => $settingLink ), $links );
		} else {
			return $links;
		}
	}

	public function sSaveTheSettings() {
		if( self::$s_storeSettings && function_exists( 'update_option' ) ) {
            update_option('Xiphe\THEMASTER\allsettings', self::$s_userSettings);
            self::$s_storeSettings = false;
        }
	}

	public static function inject_settingsRow( $k ) {

		$args = self::$s_settings[ $k ];
		$allSettings = self::s_getSettings( $k );

		if ( is_object( ( $HTML = self::get_HTML( true ) ) ) ) {
			$id = '#' . preg_replace( '/[^a-z0-9-_]/', '', str_replace( ' ', '-', strtolower( $args['name'] ))) . '-settings';

			$HTML->s_tr( $id . '|.tm-setting-row closed tm-settings' )->td()->s_td('colspan=2')->s_div('.tm-settingswrap');
			foreach( $allSettings as $key => $setting ) {
				if( is_string( $setting ) ) {
					if( $setting === 'sep' || $setting === 'seperator' )
						$setting = array( 'type' => 'seperator' );
					elseif( is_string( $key ) ) {
						$setting = array(
							'type' => $key,
							'value' => $setting
						);
					} else {
						$setting = array(
							'type' => 'text',
							'value' => $setting
						);
					}
					$key = false;
				}
				self::s_buildSettingsRow( $key, $setting, $k, $HTML, $args );
			}
			$HTML->s_div( '.tm-settingwrap tm-savewrap' )
				->button( __( 'Save', 'themaster' ), 'button' )
				->s_span( 'tm-loading hidden' )
					->img( 'alt=loading...|src=' . get_admin_url() . 'images/wpspin_light.gif' )
				->end()
				->span( null, 'tm-message' )
				->hidden( 'name=tm-nonce|value=' . wp_create_nonce( 
					$k . 'tmsaveSettings'
				) )
				->hidden( 'name=tm-settingkey|value=' . $k )
				->hidden( 'name=action|value=tm-savesetting' )
				->end( $id );
		}
	}

	public static function s_buildSettingsRow( $name, $setting = null, $k = null, $HTML = null, $ags = null ) {

		extract( $setting );
		
		if( $name !== false ) {
			$HTML->s_div( '.tm-settingwrap' );	
			if( isset( self::$s_userSettings[$k][$name] )) {
				$default = self::$s_userSettings[$k][$name];
			}

			$name = 'tm-setting_' . preg_replace( '/[^a-z0-9-_]/', '', $k ) . '_' . $name;
			$label .= ':';
		}
		switch( $type ) {
			case 'checkbox':
				$HTML->checkbox( $name, $label, $default );
				break;
			case 'dropdown':
				$HTML->select(
					$name,
					$args,
					$default,
					$label
				);
				break;
			case 'input':
				$HTML->input( 
					array(
						'value' => $default,
						'name' => $name,
						'pattern' => ( isset( $validation ) ? $validation : null )
					),
					$label
				);
				break;
			case 'seperator':
				$HTML->hr();
				break;
			case 'text':
				$HTML->p( $value );
				break;
			case 'h1':
				$HTML->h1( $value );
				break;
			case 'h2':
				$HTML->h2( $value );
				break;
			case 'h3':
				$HTML->h3( $value );
				break;
			case 'h4':
				$HTML->h4( $value );
				break;
			case 'h5':
				$HTML->h5( $value );
				break;
			case 'h6':
				$HTML->h6( $value );
				break;
			// Missing Input validation
			// case is_callable( $type ):
			// 	call_user_func_array( $type,
			// 		array( 
			// 			'HTML' => $HTML,
			// 			'name' => $name,
			// 			'settings' => $settings,
			// 			'k' => $k,
			// 			'ags' => $ags
			// 		)
			// 	);
			// 	break;
			default:
				break;
		}
		if( isset( $description ) ) {
			$HTML->abbr( __('Info', 'themaster'), '.tm-settinginfo|title=' . $description );
		}
		if( $name !== false ) {
			$HTML->end( '.tm-settingwrap' );
		}

	}

	private static function tmgl_settings() {
		return array(
			/*'key' => array(
				'label' => ''
				'type' => '', // checkbox|dropdown|select|input|textarea
				'default' => '', // bool|name|array(names)|string
				'validation' => '', // function|regex OPTIONAL
				'args' => array() // OPTIONAL
				'description' => '' // OPTIONAL
			),*/
			'errorReporting' => array(
				'label' => __('Error Reporting', 'themaster'),
				'type' => 'checkbox',
				'default' => false
			),
			// 'sep',
			// 'h3' => 'Headline',
			// 'Lorem ipsum ilum dolor. Lorem ipsum ilum dolor Lorem ipsum ilum dolor Lorem ipsum ilum dolor Lorem ipsum ilum dolor Lorem ipsum ilum dolor Lorem ipsum ilum dolor.',
			'debug' => array(
				'label' => __('Debug', 'themaster'),
				'type' => 'checkbox',
				'default' => false
			),
			'debugGet' => array(
				'label' => __( 'GET-Mode', 'themaster' ),
				'type' => 'checkbox',
				'default' => false
			),
			'debugMode' => array(
				'label' => __('Debug-Mode', 'themaster'),
				'type' => 'dropdown',
				'default' => 'inline',
				'args' => array(
					'inline' => __('Inline', 'themaster'),
					'mail' => __('Mail', 'themaster'),
					'FirePHP' => __('FirePHP', 'themaster'),
					'summed' => __('Summed', 'themaster')
				)
			),
			'useHTML' => array( 
				'label' => __('Use HTML Class', 'themaster'),
				'type' => 'checkbox',
				'default' => true
			),
			'debugEmail' => array(
				'label' => __('Debug Email To', 'themaster'),
				'type' => 'input',
				'default' => '',
				'validation' => '^(([a-zA-ZäöüÄÖÜß0-9_\.\-])+\@(([a-zA-ZäöüÄÖÜß0-9\-])+\.)+([a-zA-Z0-9]{2,4}))?$',
				'errorMessage' => __('Please leave blank or use valid E-Mail.', 'themaster')
			),
			// 'debugEmailFrom' => 'noreply@uptoyou.de',
			'forceUpdates' => array(
				'label' => __('Force Updates', 'themaster'),
				'type' => 'checkbox',
				'default' => false,
				'description' => __( 'By default !THE MASTER will search for updates on every wp-chron call. '
					. 'Turn this on to search on every wordpress request. This also breaks the inline update hint '
					. 'on this page. - NOT RECOMENDET FOR PRODUCTION!'
				)
			),
		);
	}

} ?>