<?php
namespace Xiphe\THEMASTER\core;

use Xiphe as X;

/**
 * THESETTINGS is used to manage Master settings set by constants 
 *
 * @copyright Copyright (c) 2013, Hannes Diercks
 * @author    Hannes Diercks <xiphe@gmx.de>
 * @version   3.0.0
 * @link      https://github.com/Xiphe/-THE-MASTER/
 * @package   !THE MASTER
 */
class THESETTINGS extends THEBASE {

	// Turns true after first initiation.
	private static $s_initiated = false;


	private static $s_settings = array();
			
	private static  $s_forcedSettings = array();
	
	public function __construct($initArgs)
	{
		if (!isset( $this->constructing) || $this->constructing !== true) {
			throw new Exception("ERROR: THESETTINGS is not ment to be constructed directly.", 1);
			return false;
		}

		$this->add_requiredInitArgs_( array( 'textID' ) );

		if( !self::$s_initiated ) {
			self::$s_settings[X\THETOOLS::get_textID(THEMASTER_PROJECTFILE)] = array(
				'debug' => false,
				'debugMode' => 'inline',  // inline, mail, FirePHP, summed
				'debugGet' => false,
				'useHTML' => true,
				'debugEmail' => false,
				'debugEmailFrom' => false,
				'errorReporting' => false,
				'forceUpdates' => false
			);

			self::$s_initiated = true;
		}

		return parent::__construct($initArgs);
	}

	protected function _masterInit()
	{
		if (!isset($this)) {
			throw new Exception("_masterInit should not be called staticaly.", 1);
		}
		if ( isset( $this->_masterInitiated ) && $this->_masterInitiated === true) {
			return;
		}

		if( parent::_masterInit() ) {
			return true;
		}
	}

	public function get_setting( $key, $textID = null ) {
		if( $textID === null && isset( $this ) ) {
			$textID = $this->textID;
		}
		self::sGet_setting( $key, $textID );
	}


	public static function sGet_setting( $key, $textID = null ) {
		if (class_exists(THE::WPSETTINGS)) {
			return THEWPSETTINGS::sGet_setting( $key, $textID );
		} else {
			return self::_get_setting( $key, $textID );
		}
	}

	public static function _get_setting( $key, $textID = null, $noDefaults = false, $silent = false ) {
		if( !$silent && $textID === null ) {
			throw new Exception( 'Tried to get setting "' . $key . '" without textID.' );
			return;
		}

		if( isset( self::$s_settings[$textID][$key] ) ) {

			if( ( $setting = self::_get_forcedSetting( $key, $textID ) ) !== null ) {
				return $setting;
			} else {
				$const = get_defined_constants(true);
				$constKey = 'THEMASTER_' . strtoupper( $textID ) . '_' . strtoupper( $key );
				if( isset( $const['user'][$constKey] )) {
					return $const['user'][$constKey];
				} elseif( !$noDefaults ) {
					return self::$s_settings[$textID][$key];
				}
			}
		} elseif( !$silent ) {
			throw new \Exception( 'Tried to get non-existent setting "' . $textID . ': ' . $key . '".' );
		}
		return;
	}
	
	public static function _get_forcedSetting( $key, $textID ) {
		if( isset( self::$s_forcedSettings[$textID][$key] ) ) {
			return self::$s_forcedSettings[$textID][$key];
		}
		return;
	}

	public function sSet_setting( $key, $textID, $value ) {
		if (isset(self::$s_settings[$textID][$key])
			|| (class_exists(THE::WPSETTINGS) && THEWPSETTINGS::settingExists($key, $textID))
		) {
			self::$s_forcedSettings[$textID][$key] = $value;
		} else {
			throw new \Exception( 'Tried to set non-existent setting "' . $textID . ': ' . $key . '".' );
		}
	}
}
?>