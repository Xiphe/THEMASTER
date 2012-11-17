<?php
namespace Xiphe;

use Xiphe\THEMASTER\core as core;

/**
 * THEDEBUG is a standalone class for debugging purposes used by !THE MASTER
 *
 * @copyright Copyright (c) 2012, Hannes Diercks
 * @author    Hannes Diercks <xiphe@gmx.de>
 * @version   3.0.0
 * @link      https://github.com/Xiphe/-THE-MASTER/
 * @package   !THE MASTER
 */
class THEDEBUG {

	/* --------- *
	 *  SETTINGS *
	 * --------- */

	/**
	 * Fall-back Settings for Standalone usage.
	 *
	 * @access private
	 * @var array
	 */
	private static $s_settings = array(
		'debug' => true,
		'debugMode' => 'FirePHP',  // inline, mail, FirePHP, summed
		'debugGet' => false,
		'debugEmail' => false,
		'debugEmailFrom' => false,
	);

	private static $s_firePHPPath = '../FirePHPCore/fb.php';


	/* ------------------ *
	 *  STATIC VARIABLES  *
	 * ------------------ */

	/* PRIVATE */



	// Turns true after first initiation.
	private static $s_initiated = false;	

	private static $s_singleton;

	private static $s_enabled = false;
	private static $s_getMode = false;
	private static $s_mode;
	private static $s_debugEmail;
	private static $s_debugEmailFrom;

	private static $s_standardBtDeepth = 5;
	private static $s_internalStandardBtDeepth;
	private static $s_btDeepth;
	
	private static $s_debugs = array();
	
	private static $s_cDebug = array();

	private static $s_counts = array();
	
	private static $s_css = array(
		array(
			'b' => '#3a3',
			'bg' => '#aea',
			'f' => '#050'
		), array(
			'b' => '#ccc',
			'bg' => '#fff',
			'f' => '#000'
		), array(
			'b' => '#0af',
			'bg' => '#cef',
			'f' => '#058'
		), array(
			'b' => '#eb0',
			'bg' => '#fe8',
			'f' => '#740'
		), array(
			'b' => '#f31',
			'bg' => '#faa',
			'f' => '#500'
		)
	);


	
	// Holders for $this->_sortDebugs() function used by summed outputs
	private static $s_cSorting = 'type';
	private static $s_cDirection = 'asc';
	
	private static $s_names;
	

	public static function get_btDeepth() {
		return self::$s_btDeepth;
	}

	public function __construct() {
		if (isset(self::$s_singleton) && is_object(self::$s_singleton)) {
			return self::$s_singleton;
		} elseif (!self::$s_initiated) {
			if (!class_exists('Xiphe\THEMASTER\core\THE') || !class_exists(core\THE::BASE)) {
				self::sInit();
			}
		}
	}

	public static function sInit() {
		if (!self::$s_initiated) {
			self::$s_initiated = true;

			if (self::_get_setting('debug')) {
				self::$s_mode = self::_get_setting('debugMode');
				self::$s_getMode = self::_get_setting('debugGet');

				if (!function_exists('__') && !function_exists('Xiphe\__')) {
					function __($str) {
						return $str;
					}
				}
				self::$s_names = array(
					__('OK', 'themaster'),
					__('Debug', 'themaster'),
					__('Info', 'themaster'),
					__('Warning', 'themaster'),
					__('Error', 'themaster')
				);

				if (self::$s_mode === 'FirePHP') {
					try {
						if( !class_exists( '\FirePHP' ) ) {
							if (class_exists('Xiphe\THEMASTER\core\THE') && class_exists(core\THE::BASE)) {
								$firePHP = core\THEBASE::$sBasePath.'classes'.DS.'FirePHPCore'.DS.'fb.php';
							} else {
								$firePHP = dirname(__FILE__).DIRECTORY_SEPARATOR.self::$s_firePHPPath;
							}

							require_once $firePHP;
							ob_start();
							$FB = \FirePHP::getInstance( true );
						}
					} catch(\Exception $e) {
						if (class_exists('Xiphe\THEMASTER\core\THE') && class_exists(core\THE::SETTINGS)) {
							core\THESETTINGS::_set_setting('debugMode', 'themaster', 'inline');
						}
						self::$s_mode = 'inline';
						echo self::_get_debug('Debug mode reset to inline because FirePHP could not be initiated', 4);
					}
				} elseif (self::$s_mode === 'mail') {

					self::$s_debugEmail = self::_get_setting('debugEmail');

					if ((
						 class_exists('Xiphe\THETOOLS')
					 	 && !THETOOLS::isValidEmail(self::$s_debugEmail)
					   ) || (
					   	 !class_exists('Xiphe\THETOOLS')
					   	 && !preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/", self::$s_debugEmail)
					   )
					) {
						throw new \Exception( __( 'THEMASTER ERROR: THEDEBUG Mode is set to Mail but no valid reciver is found.', 'themaster' ), 1);
					}
					

					if (function_exists('get_bloginfo')) {
						$baseFrom = get_bloginfo('siteurl');
					} else {
						$baseFrom = $_SERVER['SERVER_NAME'];
					}

					$baseFrom = parse_url($baseFrom, PHP_URL_HOST);
					if (count(($e = explode('.', $baseFrom))) > 2) {
						unset($e[0]);
						$baseFrom = implode('.', $e);
					}
					self::$s_debugEmailFrom = 'debug@' . $baseFrom;
				}

				self::$s_internalStandardBtDeepth = self::$s_standardBtDeepth;
				self::_reset_btDeepth();

			
				self::$s_enabled = true;
				if ((!defined('DOING_AJAX') || !DOING_AJAX)) {
					self::debug(sprintf(__('Debug is on and Mode is set to %s.', 'themaster'), self::$s_mode), 2);
				}
			} // ENDIF ( THESETTINGS::get_setting( 'debug', '_themaster' ) )
		} // ENDIF ( !self::$s_initiated )
	} // ENDMETHOD sInit
	
	public static function print_debugcounts() {
		if( !self::$s_enabled ) return;

		foreach( self::$s_counts as $k => $count ) {

			self::$s_cDebug = $count['debug'];
			self::$s_cDebug['name'] = $count['key'];
			self::$s_cDebug['var'] = $count['nr'] . ' times.';
			self::$s_cDebug['nr'] = 2;
			self::$s_cDebug['type'] = 'msg';

			self::_inner_debug( 'pregenerated_Z3PnWVieHx7qKdnFnteL' );
			unset( self::$s_counts[$k] );
		}
	}

	private static function _get_setting($key)
	{
		if(class_exists('Xiphe\THEMASTER\core\THE') && class_exists(core\THE::SETTINGS)) {
			return core\THESETTINGS::sGet_setting($key, core\THEBASE::$sTextID);
		} else {
			return self::$s_settings[$key];
		}
	}

	/** Generates the Debug Array and allowes mixing positions of nr & name
	 *
	 * @param array $args the called debug args
	 * @return void
	 * @access private
	 * @date Nov 10th 2011
	 */
	private static function _gen_debug( $args ) {
		self::$s_cDebug = array();
		self::$s_cDebug['type'] = gettype( $args[0] );
		self::$s_cDebug['var'] = $args[0];

		for( $i=1; $i <= 2 ; $i++ ) { 
			if( isset( $args[$i] ) && is_int( $args[$i] ) ) {
				self::$s_cDebug['nr'] = $args[$i];
			} elseif( isset( $args[$i] ) && is_string( $args[$i] ) ) {
				self::$s_cDebug['name'] = $args[$i];
			}
		}
		
		if( !isset( self::$s_cDebug['nr'] ) )
			self::$s_cDebug['nr'] = 1;

		$bt = debug_backtrace();

		self::$s_cDebug['btLine'] = isset( $bt[self::$s_btDeepth]['line'] ) ? $bt[self::$s_btDeepth]['line'] : null;
		self::$s_cDebug['btFile'] = isset( $bt[self::$s_btDeepth]['file'] ) ? $bt[self::$s_btDeepth]['file'] : null;
		
		if( isset( self::$s_cDebug['name'] ) )
			self::$s_cDebug['name'] = substr( self::$s_cDebug['name'], 0, 1 ) == '$' ? self::$s_cDebug['name'] : '$' . self::$s_cDebug['name'];
		else
			self::$s_cDebug['name'] = null;
	}
	
	private static function _callStack( $depth ) {
		$offset = 4;
		$depth = $depth + $offset;
		$bt = debug_backtrace();
		$stack = array();
		for( $i = $offset; $i < $depth; $i++ ) {
			if( isset( $bt[$i] ) ) {
				$stack[$depth-$i] = array(
					'function' => $bt[$i]['function'],
					'called in File' => ( isset( $bt[$i]['file'] ) ? $bt[$i]['file'] : 'UNKNOWN' ),
					'line' => ( isset( $bt[$i]['line'] ) ? $bt[$i]['line'] : 'UNKNOWN' ),
					// 'class' => ( isset( $bt[$i]['class'] ) ? $bt[$i]['class'] : 'UNKNOWN' ),
				);
			}
			
		}
		call_user_func( array( 
				isset( $this ) ? $this : 'Xiphe\THEDEBUG',
				'_inner_debug' 
			),
			array( $stack, 'Call Info', 2, 0 )
		);
	}
	
	public static function _debug( $args = null, $deepth = null ) {
		if( !self::$s_enabled ) return;

		self::s_softReset_btDeepth();

		if( is_string( $args ) && strtolower( $args ) === 'callstack' ) {
			$deepth = !is_int( $deepth ) ? 3 : $deepth;
			if( isset( $this ) ) {
				$this->_callStack( $deepth );
			} else {
				self::_callStack( $deepth );
			}
		} elseif( is_string( $args ) && strtolower( $args ) === 'calledby' ) {
			if( isset( $this ) ) {
				$this->_callStack( 1 );
			} else {
				self::_callStack( 1 );
			}
		} else {
			call_user_func( array(
					isset( $this ) ? $this : 'Xiphe\THEDEBUG',
					'_inner_debug' 
				),
				func_get_args()
			);
		}
	}
	
	/** Debug function, switches settings and handles the returning of $this->get_debug
	 *
	 * @param mixed $var the variable to be debugged
	 * @param string $name optional name of the variable
	 * @return void
	 * @access public
	 * @date Nov 10th 2011 
	 */
	public static function _inner_debug( $args = null ) {
		if( !self::$s_enabled ) return;
		if( empty( $args ) ) return;

		elseif( self::$s_getMode && ( !isset( $_GET['debug'] ) || $_GET['debug'] != 'true' )) return;
		
		if( isset( $args[3] ) && is_int( $args[3] ) ) {
			self::$s_btDeepth = self::$s_btDeepth + $args[3];
		}
		if( $args !== 'pregenerated_Z3PnWVieHx7qKdnFnteL' ) {
			self::_gen_debug( $args );
		}
		
		// TODO: Summed Mails + Summed Output;
		switch( self::$s_mode ) {
			case 'mail':
				if( isset( $this ) && isset( $this->projectName ) )
					$name = $this->projectName;
				else
					$name = 'THEDEBUG';

				$header  = "MIME-Version: 1.0\r\n";
				$header .= "Content-type: text/html; charset=iso-8859-1\r\n";
				$header .= "From: " . self::$s_debugEmailFrom . "\r\n";
				$header .= "Reply-To: " . self::$s_debugEmail ."\r\n";
				$header .= "X-Mailer: PHP ". phpversion();

				mail(
					self::$s_debugEmail,
					'Debug from '.$name,
					self::_get_debug( self::$s_cDebug ),
					$header
				);
				break;

			case 'FirePHP':
				ob_start();

				\FB::setOptions( array(
					'file' => self::$s_cDebug['btFile'],
					'line' => self::$s_cDebug['btLine']
				));
				$FB = \FirePHP::getInstance( true );

				if( self::$s_cDebug['type'] == 'boolean' ) {
					self::$s_cDebug['var'] = '(boolean) ' . ( self::$s_cDebug['var'] ? 'true' : 'false' );
				} elseif( self::$s_cDebug['type'] == 'NULL' ) {
					self::$s_cDebug['var'] = '(null) NULL';
				} elseif( self::$s_cDebug['type'] == 'string' ) {
					self::$s_cDebug['var'] = '"' . self::$s_cDebug['var'] . '"';
				}

				switch( self::$s_cDebug['nr'] ) {
					case 2:
						$FB->info( self::$s_cDebug['var'], self::$s_cDebug['name'] );
						break;
					case 3:
						$FB->warn( self::$s_cDebug['var'], self::$s_cDebug['name'] );
						break;
					case 4:
						$FB->error( self::$s_cDebug['var'], self::$s_cDebug['name'] );
						break;
					default:
						$FB->log( self::$s_cDebug['var'], self::$s_cDebug['name'] );
						break;
				}
				break;
			case 'summed':
				self::$s_debugs[] = self::$s_cDebug;
				break;
			default:
				echo self::_get_debug( self::$s_cDebug );
				break;
		}
		
	}

	/** Debug function, returns the given variable with additional informations
	 *
	 * @param mixed $var the variable to be debugged
	 * @param string $name optional name of the variable
	 * @return string
	 * @access private
	 * @date Jul 28th 2011
	 */
	private static function _get_debug( $debugArr ) {
		$r = '<div style="font-family: sans-serif; text-align: left; border: 1px solid '
				. self::$s_css[$debugArr['nr']]['b'] . ';'
				. 'background: ' . self::$s_css[$debugArr['nr']]['bg'] . '; color: '
				. self::$s_css[$debugArr['nr']]['f'] . ';'
				. ' padding: 10px; margin: 20px;"><h3 style="font-size: 30px;'
				. 'text-transform: uppercase; float: left; margin: 0 10px 0 0;">'
			. self::$s_names[$debugArr['nr']]
			. '</h3><small style="position: relative; top: 4px; font-size: 11px;">'
			. 'File: ' . $debugArr['btFile'] . ' - Line <strong>' . $debugArr['btLine'] . '</strong>'
			. '<br />Type: ' . $debugArr['type'] . '</small><br style="display: inline; clear: both;" />'
			. '<pre style="text-align: left; color: #000; background: '
			. 'rgba(255,255,255,0.2); padding: 5px; margin: 5px 0 0;">';

		if( isset( $debugArr['name'] ) )
			$r .= $debugArr['name'] . ': ';
		$r .= var_export( $debugArr['var'], true );
		$r .= '</pre></div>';
		return $r;
	}
	
	private static function _sortDebugs( $x, $y ) {
		$c = self::$s_cSorting;
		$c = $c == 'file' ? 'btFile' : $c == 'line' ? 'btLine' : $c;
		if( in_array( $c, array( 'name', 'type', 'btFile' ) ) ) {
			$r = strcmp( $x[$c], $y[$c] );
			
		} elseif( in_array( $c, array( 'nr', 'btLine' ) ) ) {
			$r = 0;
			if( $x[$c] > $y[$c] )
				$r = 1;
			elseif( $y[$c] > $x[$c] )
				$r = -1;
		}
		if( self::$s_cDirection == 'asc' )
			return $r;
		elseif( $r > 0 )
			return -1;
		elseif( $r < 0 )
			return 1;
		else
			return 0;
	}
	
	public static function _reset_btDeepth() {
		self::$s_standardBtDeepth = self::$s_internalStandardBtDeepth;
		self::$s_btDeepth = self::$s_internalStandardBtDeepth;
	}
	public static function _set_btDeepth( $deepth ) {
		if( $deepth === '--' ) {
			self::$s_standardBtDeepth--;
			self::$s_btDeepth--;
		} elseif( $deepth === '++' ) {
			self::$s_standardBtDeepth++;
			self::$s_btDeepth++;
		} else {
			self::$s_standardBtDeepth = $deepth;
			self::$s_btDeepth = $deepth;
		}
	}
	private static function s_softReset_btDeepth() {
		self::$s_btDeepth = self::$s_standardBtDeepth;
	}
	
	/** 
	 * The output function for summed Debugs
	 *
	 * @access public
	 * @param  string $sorting null or name/nr/type/file/line
	 * @param  string $dir     null for asc or asc/desc
	 * @return void
	 */
	public static function print_debug( $sorting = null, $dir = 'asc' ) {
		if( !self::$s_enabled ) return;
		
		if( $sorting && count( self::$s_debugs ) > 1 ) {
			if( !$sorting !== null ) {
				self::$s_cSorting = $sorting;
			}
			self::$s_cDirection = $dir;
			usort( self::$s_debugs, array('Xiphe\THEDEBUG', '_sortDebugs' ) );
		}
		foreach( self::$s_debugs as $k => $debug ) {
			echo self::_get_debug( $debug );
			unset( self::$s_debugs[$k] );
		}
	}
	
	/** 
	 * Wrapper for $this->debug adds die() to the end.
	 *
	 * @access public
	 * @param  mixed  $var  the variable to be debugged
	 * @param  string $name optional name of the variable
	 * @return void
	 */
	public static function diebug( $var = null ) {
		if( !self::$s_enabled ) return;
		
		$args = func_get_args();
		call_user_func_array(
			array( 'Xiphe\THEDEBUG', '_debug' ),
			$args
		);
		
		$d = 0;
		if (count($args) >= 4) {
			$d += $args[3];
		}
		call_user_func_array(
			array( 'Xiphe\THEDEBUG', '_debug' ),
			array( 'Script got murdered by diebug.', 2, null, $d )
		);
		die();
	}

	public static function rebug( $var ) {
		if( !self::$s_enabled ) return;
		
		call_user_func_array(
			array( 'Xiphe\THEDEBUG', '_debug' ),
			func_get_args()
		);
		return $var;
	}

	public static function debug( $var ) {
		if( !self::$s_enabled ) return;
		
		call_user_func_array(
			array( 'Xiphe\THEDEBUG', '_debug' ),
			func_get_args()
		);
	}

	public static function countbug($key) {
		if( !self::$s_enabled ) return;

		self::$s_btDeepth -= 4;
		if( isset( $this )) {
			$this->_gen_debug( $key );
		} else {
			self::_gen_debug( $key );
		}
		$debug = self::$s_cDebug;
		self::$s_btDeepth += 4;


		$interkey = $debug['btFile'] . $debug['btLine'];

		if( !isset( self::$s_counts[$interkey] ) ) {
			self::$s_counts[$interkey] = array(
				'nr' => 1,
				'key' => $key,
				'debug' => $debug
			);
		} else {
			self::$s_counts[$interkey]['nr']++;
		}
	}

	public static function deprecated( $alternative = null, $continue = true, $bto = 0, $bto2 = 0 ) {
		if( !self::$s_enabled ) return;
		
		self::_set_btDeepth( '++' );
		$bto++;
		
		$bt = debug_backtrace();

		$ac = self::$s_mode === 'FirePHP' ? '´' : '&acute;';

		$func = $ac;
		if (isset($bt[$bto]['class'])) {
			$func .= $bt[$bto]['class'].'::';
		}
		$func .= $bt[$bto]['function'].'()'.$ac;

		if (isset($alternative) && $alternative != false && $alternative != '') {
			$alt = ' '.sprintf(
				__('Please use %s insted.', 'themaster'),
				$ac.$alternative.$ac
			);
		} else {
			$alt = '';
		}

		$msg = sprintf(
			__('Deprecated use of %s.%s', 'themaster'),
			$func,
			$alt
		);

		$args = array(
			$msg, 3, null, $bto2+2
		);
		$method = $continue ? 'debug' : 'diebug';

		call_user_func_array(
			array( 'Xiphe\THEDEBUG', $method ),
			$args
		);
		self::_set_btDeepth( '--' );
	}

	public static function get_mode() {
		return self::$s_mode;
	}
}