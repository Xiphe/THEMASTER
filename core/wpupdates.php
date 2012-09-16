<?php
namespace Xiphe\THEMASTER;

/*
 * Include parent class.
 */
require_once(THEMASTER_COREFOLDER.'wpsettings.php');

/**
 * THEWPUPDATES handles updates via the (WP-Project-Update-API)[https://github.com/Xiphe/WP-Project-Update-API]
 *
 * @copyright Copyright (c) 2012, Hannes Diercks
 * @author    Hannes Diercks <xiphe@gmx.de>
 * @version   3.0.0
 * @link      https://github.com/Xiphe/-THE-MASTER/
 * @package   !THE MASTER
 */
class THEWPUPDATES extends THEWPSETTINGS {

	/* ------------------ */
	/*  STATIC VARIABLES  */
	/* ------------------ */
	

	/* PRIVATE */

	// Turns true after first initiation.
	private static $s_initiated = false;

	// The default update server. can be overwritten by updateServer initiation argument.
	private static $s_defUpdateServer = 'http://plugins.red-thorn.de/v2/api/';

	// Holds all updatable plugins and themes.
	private static $s_updatables = array();

	// Flag to prevent constants from being checked twice.
	private static $s_constantsChecked = false;


	/* ---------------------- */
	/*  CONSTRUCTION METHODS  */
	/* ---------------------- */


	/**
	 * The Constructor method
	 *
	 * @param	array	$initArgs	the initiation arguments
	 */
	function __construct( $initArgs ) {
		if ( !isset( $this->constructing ) || $this->constructing !== true ) {
			throw new Exception("ERROR: THEWPUPDATES is not ment to be constructed directly.", 1);
			return false;
		}

		// Register "updatable" as required initation key.
		$this->add_requiredInitArgs_( array( 'updatable' ) );

		if ( !self::$s_initiated ) {
			THEBASE::sRegister_callback( 'afterBaseS_init', array( 'Xiphe\THEMASTER\THEWPUPDATES', 'sinit' ) );
		}

		// pass the Ball
		return parent::__construct( $initArgs );
	}

	/**
	 * One time initiaton.
	 */
	public static function sinit() {
		if ( !self::$s_initiated ) {
			// Register one-time-hooks.
			self::s_hooks();

			// Check if 
			self::_checkForcing();

			// Prevent this from beeing executed twice.
			self::$s_initiated = true;
		}
	}


	/* -------------------- */
	/*  INITIATION METHODS  */
	/* -------------------- */


	protected function _masterInit() {
		if ( !isset( $this ) ) {
			throw new Exception("_masterInit should not be called staticaly.", 1);
		}
		if ( isset( $this->_masterInitiated ) && $this->_masterInitiated === true ) {
			return;
		}

		if ( parent::_masterInit() ) {
			if ( isset( $this->updatable ) && $this->updatable == true ) {
				$this->updatable(
					$this->textID,
					( isset( $this->updateServer ) ? $this->updateServer : null )
				);
			}

			return true;
		}
	}
	
	/**
	 * Registeres one-time hooks for thewpupdates.
	 */
	private static function s_hooks() {
		if ( function_exists( 'add_action' ) ) {
			add_action( 'plugins_loaded', array( 'Xiphe\THEMASTER\THEWPUPDATES', '_checkConstants' ) );
		}

		if ( function_exists( 'add_filter' ) ) {
			add_filter( 'pre_set_site_transient_update_themes', array( 'Xiphe\THEMASTER\THEWPUPDATES', '_check_for_project_update' ) );
			add_filter( 'pre_set_site_transient_update_plugins', array( 'Xiphe\THEMASTER\THEWPUPDATES', '_check_for_project_update' ) );
			add_filter( 'themes_api', array( 'Xiphe\THEMASTER\THEWPUPDATES', '_project_api_call' ), 10, 3);
			add_filter( 'plugins_api', array( 'Xiphe\THEMASTER\THEWPUPDATES', '_project_api_call' ), 10, 3);
			// add_filter( 'upgrader_source_selection', array( 'THEWPUPDATES', 'sSourceSelection' ), 10, 3);
			// add_filter( 'plugins_api_result', function( $a ) {
			// 	THEDEBUG::debug( $a, 'pluginsApiResult' );
			// 	return $a;
			// } );
			// add_filter( 'unzip_file_use_ziparchive', function() { return false; } );
		}
	}

	// public static function sSourceSelection( $source, $remote_source, $Upgrader ) {

	// 	if( isset( $Upgrader->skin->plugin ) && $Upgrader->skin->plugin !== '' ) {
	// 		$name = explode( '/', $Upgrader->skin->plugin );
	// 		$name = $name[0];
	// 	} elseif( isset( $Upgrader->skin->plugin_info['TextDomain'] )
	// 	 && $Upgrader->skin->plugin_info['TextDomain'] !== ''
	// 	) {
	// 		$name = $Upgrader->skin->plugin_info['TextDomain'];
	// 	}

	// 	if( isset( $name ) && isset( self::$s_updatables[$name] ) ) {
	// 		$newSource = explode( DS, str_replace( '/', DS, $source ) );
	// 		$newSource[ count( $newSource ) -2 ] = $name;
	// 		$newSource = implode( DS, $newSource );
	// 		if( $newSource !== $source ) {
	// 			rename( $source, $newSource );
	// 			$source = $newSource;
	// 		}
	// 	}

	// 	return $source;
	// }
	
	private function _checkForcing() {
		if ( THEWPSETTINGS::_get_Setting( 'forceUpdates', THEBASE::$sTextID ) ) {
			set_site_transient( 'update_plugins', null );
			set_site_transient( 'update_themes', null );
		}
	}
	
	public function _checkConstants() {
		if ( !self::$s_constantsChecked ) {
			$const = get_defined_constants( true );
			foreach ( $const['user'] as $const => $name ) {
				if ( strstr( $const, 'THEUPDATES_UPDATABLE' )) {
					if ( count( ( $e = explode( '|', $name ) ) ) == 2 ) {
						self::updatable( THETOOLS::get_textID( $e[0] ), $e[1] );
					} else {
						self::updatable( THETOOLS::get_textID( $e[0] ) );
					}
				}
			}
			self::$s_constantsChecked = true;
		}
	}


	/* ----------------- */
	/*  PRIVATE METHODS  */
	/* ----------------- */


	private function _prepare_request( $action, $args, $textID ) {
		global $wp_version;
		
		try {
			$apiKey = THEWPSETTINGS::get_setting( 'updateApikey', $textID );
		} catch ( \Exception $e ) {
			if( function_exists( 'get_bloginfo' ) )
				$apiKey = md5( get_bloginfo('url') );
			else 
				$apiKey = '';
		}

		return array(
			'body' => array_merge( $args, array(
				'action' => $action, 
				'apikey' => $apiKey
			) ),
			'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo('url')
		);	
	}


	/* ---------------- */
	/*  PUBLIC METHODS  */
	/* ---------------- */


	public function updatable( $textID, $server = null ) {
		self::$s_updatables[$textID] = isset( $server ) ? $server : self::$s_defUpdateServer;
	}
	
	
	public function _check_for_project_update( $checked_data ) {
		self::_checkConstants();
		// THEDEBUG::debug( $checked_data, 'check' );
		if ( empty( $checked_data->checked ) )
			return $checked_data;
		
		// $this->debug( $checked_data );
		foreach ( self::$s_updatables as $textID => $server ) {

			$fullTextID = $textID;
			$isTheme = false;
			if( basename( $textID ) === 'style.css' ) {
				$textID = dirname( $textID );
				$isTheme = true;
			}

			if ( !isset( $checked_data->checked[$textID] ) ) {
				continue;
			}

			$pData = THEWPBUILDER::get_initArgs( ABSPATH . 'wp-content' . DS
				. ( $isTheme ? 'themes' : 'plugins' ) . DS . $fullTextID );

			$request_args = array(
				'slug' => $pData['textdomain'],
				'version' => $checked_data->checked[$textID],
			);
			if( isset( $pData['branch'] ) ) {
				$request_args['branch'] = $pData['branch'];
			}

			$request_string = self::_prepare_request( 'basic_check', $request_args, $fullTextID );
			// THEDEBUG::debug( $request_string, 'request_string' );
			
			// Start checking for an update


			$raw_response = wp_remote_post( $server, $request_string );
			// THEDEBUG::debug( $raw_response, 'raw_response' );
				

			if ( !is_wp_error( $raw_response ) && ( $raw_response['response']['code'] == 200 ) ) {
				$response = @unserialize( $raw_response['body'] );
			}

			// Feed the update data into WP updater
			if ( isset( $response )
			 && !empty( $response )
			 && ( is_object( $response ) || is_array( $response ) )
			 && THETOOLS::rget( $response, 'new_version' ) !== null
			) {
				$checked_data->response[$textID] = $response;
			}
		}
		// THEDEBUG::debug( $checked_data, 'checked_data' );

		return $checked_data;
	}

	public function _project_api_call( $def, $action, $args ) {
		if( !isset( $args->slug ) ) {
			return false;
		}

		$found = 0;
		foreach( self::$s_updatables as $textID => $server ) {
			if( dirname( $textID ) === $args->slug
			 || pathinfo( $textID , PATHINFO_FILENAME ) === $args->slug
			) {
				$found++;
				$current = array( 'fullTextID' => $textID, 'server' => $server );
			}
		}

		if ( $found === 1 ) {
			extract( $current );
		} elseif( $found > 1 ) {
			throw new Exception("slug collision for \"{$args->slug}\"", 1);
		} else {
			return false;
		}
		
		$pData = THEWPBUILDER::get_initArgs( ABSPATH . 'wp-content' . DS
				. 'plugins' . DS . $fullTextID );

		$request_args = array(
			'slug' => $pData['textdomain'],
		);
		if( isset( $pData['branch'] ) ) {
			$request_args['branch'] = $pData['branch'];
		}
		$request_string = self::_prepare_request( 'plugin_information', $request_args, $fullTextID );
		// THEDEBUG::debug( $request_string, 'request_string' );
		
		
		$raw_response = wp_remote_post($server, $request_string);

		// THEDEBUG::debug( $raw_response, 'raw_response' );

		if ( is_wp_error($raw_response) ) {
			$res = new WP_Error(
				'plugins_api_failed',
				__( 'An Unexpected HTTP Error occurred during the API request.</p>'
					. '<p><a href="?" onclick="document.location.reload(); return'
					. 'false;">Try again</a>'),
				$raw_response->get_error_message()
			);
		} else {
			$res = unserialize($raw_response['body']);
			
			if ($res === false)
				$res = new WP_Error('plugins_api_failed', __('An unknown error occurred'), $raw_response['body']);
		}
		
		return $res;
	}
	
} ?>