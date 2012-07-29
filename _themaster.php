<?php
/*
Plugin Name: !THE MASTER
Plugin URI: http://plugins.red-thorn.de/libary/themaster/
Description: A Plugin to provide global access to the THEWPMASTER class. THEWPMASTER provides a lot of handy functions for plugins an themes.
Version: 3.0.0
Date: 2012-06-17 04:22:00
Author: Hannes Diercks
Author URI: http://red-thorn.de/
Update Server: http://plugins.red-thorn.de/api/
Branch: 3.0_Alpha
*/

// die( 'THEMASTER SYMLINKED' );

// xBP();

// $tmTextID = basename( dirname( __FILE__ ) ) . '/' . basename( __FILE__ );
// $tmSettingsID = 'THEMASTER_' . strtoupper( $tmTextID );
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
// define( $tmSettingsID . '_ERRORREPORTING', true );
// define( $tmSettingsID . '_DEBUG', true );
// define( $tmSettingsID . '_DEBUGMODE', 'FirePHP' );

// Define shorthand for DIRECTORY_SEPARATOR.
if( !defined( 'DS' ))
	define( 'DS', DIRECTORY_SEPARATOR );

// Register itself to be updatable.
define( 'THEUPDATES_UPDATABLE_THEMASTER', __FILE__ );

define( 'THEMASTER_PROJECTFILE', __FILE__ );

// Register activation hook.
if( function_exists( 'register_activation_hook' )) {
	register_activation_hook( __FILE__, '_masterActivate' );
}

function _masterActivate() {
	require_once( __DIR__ . DS . 'core' . DS . 'wpmaster.php' );
	THEWPMASTER::_masterActivate();
}

// Include core File - automaticaly includes required core files and instantiates a base instance.
if( !defined( 'WPMASTERAVAILABE' )) {
	require_once( __DIR__ . DS . 'core' . DS . 'wpmaster.php' );
}

/**
 * initiation for Plugins and Themes that want to use THE MASTER.
 * Automaticaly parses initiation args from main plugin file or 
 * theme style.
 *
 * @param	array	$initArgs	optional additional initiation arguments. 
 * 								Keys are overwriting parsed init args.
 *								Set Key "projectName" to prevent auto parsing.
 * @package	THEWPMASTER
 * @author	Hannes Diercks
 * @return	object				Instance of THEWPMASTER or false if error.
 */
function THEWPMASTERINIT( $initArgs = null, $file = null ) {
	// if( function_exists( 'xBP' ) ) xBP();
	
	// If init args or key projectName is not set.
	if( ( !is_array( $initArgs['projectName'] ) || !isset( $initArgs['projectName'] ) )
	 && ( null === $initArgs
	 || ( is_string( $initArgs ) && file_exists( $initArgs ) )
	 || ( is_string( $file ) && file_exists( $file ) )
	)) {
		// Start parsing the initiation arguments

		// Merge the passed arguments into the parsed.
		$initArgs = array_merge( 
			THEWPBUILDER::get_initArgs( $initArgs, $file ),
			( is_array( $initArgs ) ? $initArgs : array() )
		);

		$initArgs['isMaster'] = true;
	} 

	// Try to build a new Master with the initiation arguments.
	try {
		$r = THEWPMASTER::get_instance( 'Master', $initArgs );
	} catch( Exception $e ) {
		// Errors Occured -> write an admin notice.
		if( !isset( $GLOBALS['THEWPMASTERINITERRORS'] )) {
			$GLOBALS['THEWPMASTERINITERRORS'] = array();

			if( function_exists( 'add_action' ) ) {
				add_action( 'admin_notices', function() {
					foreach( $GLOBALS['THEWPMASTERINITERRORS'] as $e ) {
						echo '<div class="error"><p>' . $e->getMessage() . '<br />File:' . $e->getFile() . ' Line:' . $e->getLine() . '</p></div>';
					}
				});
			} else {
				echo '<div class="error"><p>' . $e->getMessage() . '<br />File:' . $e->getFile() . ' Line:' . $e->getLine() . '</p></div>';
			}
		}
		array_push( $GLOBALS['THEWPMASTERINITERRORS'], $e );
		// Do not return the object because it was not initated correctly.
		$r = false;
	}
	return $r;
}

function BUILDTHEMASTER( $extended = true, $template = 'def' ) {
	$args = THEWPBUILDER::get_initArgs();

	if( !isset( $args['projectName'] ) ) {
		throw new Exception( 'BUILDTHEWPMASTERPLUGIN called in invalid file.', 1 );
	} elseif( !isset( $args['date'] ) ) {
		THEWPBUILDER::sbuild( 'init', $args, $template, $extended );
	} elseif( !isset( $args['version'] ) ) {
		THEWPBUILDER::missing_initArgs( $args );
	} else {
		THEWPBUILDER::sbuild( 'full', $args, $template, $extended );
	}
}
?>