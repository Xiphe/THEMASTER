<?php
namespace Xiphe\THEMASTER;

/*
 * Include parent class.
 */
require_once(THEMASTER_COREFOLDER.'master.php');

/**
 * THEWPBUILDER can extract Masters initiation arguments from Plug-in and Theme files
 * and build new skeletons from the template folder.
 *
 * @copyright Copyright (c) 2012, Hannes Diercks
 * @author    Hannes Diercks <xiphe@gmx.de>
 * @version   3.0.0
 * @link      https://github.com/Xiphe/-THE-MASTER/
 * @package   !THE MASTER
 */
class THEWPBUILDER extends THEMASTER {

	private static $s_baseTemplatePath;
	private static $s_initiated = false;
	private static $s_access = false;

	private static $s_initArgsCache = array();

	private static $s_buildArgs = array();
	private static $s_storeNewInitArgs = false;

	private static $_ftp_conn_id;

	public static $sDefaultStructure = array(
		'classes' => 0775,
		'res' => array(
			'chmod' => 0775,
			'css' => 0777,
			'less' => 0775,
			'js' => 0775
		)
	);

	public static $sFullStructure = array(
		'classes' => 0775,
		'res' => array(
			'chmod' => 0775,
			'css' => 0777,
			'less' => 0775,
			'js' => 0775,
			'includes' => 0775
		),
		'models' => 0775,
		'views' => 0775
	);

	/**
	 * The Constructor method
	 *
	 * @param	array	$initArgs	the initiation arguments
	 */
	public function __construct( $initArgs ) {
		if( !self::$s_initiated ) {
			THEBASE::sRegister_callback( 'afterBaseS_init', array( 'Xiphe\THEMASTER\THEWPBUILDER', 'sinit' ) );
		}

		// Pass ball to parent.
		return parent::__construct( $initArgs );
	}

	/**
	 * One time initiaton.
	 */
	public static function sinit() {
		if( !self::$s_initiated ) {
			// Get all options from database.
			if( function_exists( 'get_option' ) ) {
				self::$s_initArgsCache = get_option( 'Xiphe\THEMASTER\cachedInitArgs', array() );
			}

			if( isset( $GLOBALS['pagenow'] ) 
			 && in_array( $GLOBALS['pagenow'], array( 'plugins.php', 'themes.php' ) )
			 && function_exists( 'is_admin' ) && is_admin()
			) {
				self::$s_access = true;
				add_action( 'init', array( 'Xiphe\THEMASTER\THEWPBUILDER', 'sStartToBuild' ) );	
			}

			if (function_exists('add_action')) {
				add_action('shutdown', array('Xiphe\THEMASTER\THEWPBUILDER','sCacheInitArgs'));
			}

			// Prevent this from beeing executed twice.
			self::$s_initiated = true;
		}
	}

	public static function sCacheInitArgs() {
		if( self::$s_storeNewInitArgs && function_exists( 'update_option' ) ) {
			update_option( 'Xiphe\THEMASTER\cachedInitArgs', self::$s_initArgsCache );
			self::$s_storeNewInitArgs = false;
		}
	}

	private static function s_getBaseTemplatePath() {
		if( !isset( self::$s_baseTemplatePath ) ) {
			self::$s_baseTemplatePath = dirname( dirname( __FILE__ ) ) . DS . 'templates' . DS;
		}
		return self::$s_baseTemplatePath;
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

	protected function check_folderStructure_($basePath, $structure = null) {
		if (!isset(self::$_ftp_conn_id) && defined('FTP_HOST') && defined('FTP_USER') && defined('FTP_PASS')) {
			self::$_ftp_conn_id = ftp_connect(FTP_HOST);
			$login_result = ftp_login(self::$_ftp_conn_id, FTP_USER, FTP_PASS);
			$relBasePath = str_replace(FTP_WORKING_PATH, '', $basePath);
		} else {
			$relBasePath = $basePath;
		}
		if (empty($structure)) {
			$structure = self::$sDefaultStructure;
		}
		self::_check_folderStructureWalker($structure, $relBasePath, $basePath);
		if(isset(self::$_ftp_conn_id)) {
			ftp_close(self::$_ftp_conn_id);
		}
	}
	
	private static function _check_folderStructureWalker($structure, $ftpRoot, $root, $basedir = '') {
		foreach($structure as $folder => $chmod) {
			$subfolders = null;
			if(is_array($chmod)) {
				$t = $chmod['chmod'];
				unset($chmod['chmod']);
				$subfolders = $chmod;
				$chmod = $t;
			}
			$relDir = THETOOLS::DS($ftpRoot.$basedir.$folder);
			$dir = THETOOLS::DS($root.$basedir.$folder);
			if(is_dir($dir)) {
				if(!isset(self::$_ftp_conn_id)) {
					chmod($dir, $chmod);
				} else {
					ftp_chmod(self::$_ftp_conn_id, $chmod, $relDir);
				}
			} else {
				if(!isset(self::$_ftp_conn_id)) {
					mkdir($dir, $chmod);
				} else {
					ftp_mkdir(self::$_ftp_conn_id, $relDir);
					ftp_chmod(self::$_ftp_conn_id, $chmod, $relDir);
				}
			}
			
			if(is_array($subfolders)) {
				self::_check_folderStructureWalker($subfolders, $ftpRoot, $root, $basedir.$folder.DS);
			}
		}
	}

	public static function get_initArgs( $file = null, $file2 = null, $deepth = 1 ) {
		// THEDEBUG::debug( $file );
		// THEDEBUG::debug( $file, 'file' );
		// THEDEBUG::debug( $file2, 'file2' );

		if( !is_string( $file ) || !file_exists( $file ) ) {
			$file = $file2;
		}

		if( $file === null || !file_exists( $file ) ) {
			$bt = debug_backtrace();
			$file = $bt[$deepth]['file'];
			unset($bt);
		}
		
		$configFile = strstr( $file, 'wp-content' . DS . 'themes' ) || strstr( $file, 'htdocs' . DS . '__THEMES' ) ?
			dirname( $file ) . DS . 'style.css' : $file;

		$textID = THETOOLS::get_textID( $configFile );

		if( isset( self::$s_initArgsCache[ $textID ] )
		 && self::$s_initArgsCache[ $textID ]['time'] >= ( filemtime( $configFile ) + filemtime( $file ) )
		) {
			// THEDEBUG::debug( self::$s_initArgsCache[ $file ]['args'], 'cachedArgs' );
			return self::$s_initArgsCache[ $textID ]['args'];
		} else {
			return self::s_get_initArgsFromFile( $file );
		}
	}

	private static function s_get_initArgsFromFile( $file ) {
		$iA = array(); // Target Array (initArgs)

		$iA['projectFile'] = $file;

		// Generate basePath and folderName from projectFile.
		$iA['basePath'] = dirname( $iA['projectFile'] ) . DS;
		$iA['folderName'] = basename( $iA['basePath'] );

		// If path contains wp-content/themes project seems to be a theme.
		$iA['projectType'] = ( strstr( $iA['basePath'], 'wp-content' . DS . 'themes' ) || strstr( $iA['basePath'], 'htdocs' . DS . '__THEMES' ) ) 
			? 'theme' : 'plugin';

		// Set textdomain to foldername for themes and to filebasename for plugins.
		if( $iA['projectType'] === 'theme' ) {
			$iA['textdomain'] = $iA['folderName'];
		} else {
			$iA['textdomain'] = pathinfo( $iA['projectFile'], PATHINFO_FILENAME );
		}
		
		// Set baseUrl.
		if( $iA['projectType'] === 'plugin' && function_exists( 'plugins_url' ) ) {
			$iA['baseUrl'] = plugins_url( $iA['textdomain'] ) . '/';
		} elseif( function_exists( 'get_bloginfo' ) ) {
			$iA['baseUrl'] = get_bloginfo( 'template_url' ) . '/';
		} else {
			$iA['baseUrl'] = '/';
		}

		// Get the file that contains additional information.
		$iA['configFile'] = $iA['projectType'] === 'plugin'
			? $iA['projectFile'] : $iA['basePath'] . 'style.css';

		// Read the file and fill additional initiation arguments with information.
		foreach( file( $iA['configFile'] ) as $l ) {
			if( count( ( $p = explode(':', $l, 2 ) ) ) > 1 ) {
				if( trim( $p[1] ) === '' ) continue;

				switch( preg_replace('/[^a-z0-9]/', '', strtolower( $p[0] ))) {
					case 'date' :
						$iA['date'] = trim( $p[1] );
						break;
					case 'pluginname':
						$iA['projectName'] = trim( $p[1] );
						break;
					case 'themename':
						$iA['projectName'] = trim( $p[1] );
						break;
					case 'description':
						$iA['projectDesc'] = trim( $p[1] );
						break;
					case 'version':
						$iA['version'] = trim( $p[1] );
						break;
					case 'branch':
						$iA['branch'] = trim( $p[1] );
						break;
					case 'updateserver':
						$iA['updatable'] = true;
						$iA['updateServer'] = trim( $p[1] );
						break;
					case 'author':
						$iA['author'] = trim( $p[1] );
						break;
					case 'updatable':
						if( trim( $p[1] ) != 'false' )
							$iA['updatable'] = true;
						break;
					case 'requiredplugins':
						$iA['requiredPlugins'] = array();
						foreach( explode(',', trim( $p[1] ) ) as $rqrd ) {
							array_push( $iA['requiredPlugins'], trim($rqrd) );
						}
						break;
					case 'namespace':
						$iA['namespace'] = trim( $p[1] );
						break;
					default:
						break;
				}
			}
			// Stop reading when the first comment is closed.
			if( trim($l) == '*/' )
				break;
		}

		// Check if a prefix is set or generate it from the first two uppercase chars of the projectName.
		if( !isset( $iA['namespace'] ) && isset( $iA['projectName'] ) && isset( $iA['author'] ) ) {
			$ns = preg_replace( '/[^A-Za-z]/', '', $iA['author'] ) . '\\';
			$ns .= preg_replace( '/[^A-Za-z]/', '', $iA['projectName'] );
			
			$iA['namespace'] = $ns;
		}

		if( !isset( $iA['updatable'] ) ) {
			$iA['updatable'] = false;
		}

		if( !isset( $iA['textID'] ) ) {
			$iA['textID'] =  THETOOLS::get_textID( $iA['configFile'] );
		}

		self::$s_initArgsCache[ $iA['textID'] ] = array(
			'time' => ( filemtime( $iA['configFile'] ) + filemtime( $iA['projectFile'] ) ),
			'args' => $iA
		);

		self::$s_storeNewInitArgs = true;
		return $iA;
	}

	public static function sBuild( $mode, $args, $template, $extended ) {
		if( self::$s_access !== true ) { return false; }

		$extended = ( $extended === true ? 'extended' : 'mini' );
		self::$s_buildArgs[$mode][$template][$extended][] = $args;
	}

	public static function sStartToBuild() {
		// THEDEBUG::debug( self::$s_buildArgs, 'buildArgs' );
		if( self::$s_access !== true ) { return false; }
		foreach( self::$s_buildArgs as $type => $templates ) {
		foreach( $templates as $template => $extOrNot ) {
		foreach( $extOrNot as $extended => $argss ) {
		foreach( $argss as $args ) {
			$baseTemplateName = $template;
			$templateName = $args['projectType'] . DS . $type . DS . $template;
			if( !is_dir( self::s_getBaseTemplatePath() . $templateName ) ) {
				$msg = sprintf(
					__( '**Error:** Template //%1$s// for %2$s "%3$s" does not exist.', 'themaster' ),
					$templateName,
					ucfirst( $args['projectType'] ),
					$args['projectName']
				);
				THEWPMASTER::set_adminMessage( $msg, 'error' );
				continue;
			}

			$template = self::s_get_template( $templateName, '', $extended );
			$template = self::s_fillTemplate( $template, $args, $extended, $baseTemplateName );
			self::s_write_template( $template, $args );

			if( $type === 'full' ) {

				$pF = file_get_contents( $args['projectFile'] );
				// THEDEBUG::diebug(preg_match('/(\t)*TM\\\BUILD( )*\((.)*\)( )*;( )*(\n|\r|\r\n)/', $pF));
				$pF = preg_replace('/(\t)*TM\\\BUILD( )*\((.)*\)( )*;( )*/', "\tTM\INIT(__FILE__);", $pF);
				$pF = preg_replace('/(\t)*\/\/ \*optional\*( )*(\r\n|\r|\n)/', '', $pF);
				$pF = preg_replace('/(\t)*\/\/ Update Server:( )*(\r\n|\r|\n)/', '', $pF);
				$pF = preg_replace('/(\t)*\/\/ Required Plugins:( )*(\r\n|\r|\n)/', '', $pF);
				$pF = preg_replace('/(\t)*\/\/ Branch:( )*(\r\n|\r|\n)/', '', $pF);
				$pF = preg_replace('/(\t)*\/\/ Please fill in additional Plugin information.( )*(\r\n|\r|\n)/', '', $pF);
				$pF = str_replace('BUILD', 'INIT', $pF);

				file_put_contents($args['projectFile'], $pF);
			}

			$msg = sprintf(
				__( '**Successfull** completed %5$s %1$s build for %2$s "%3$s". Used Template //%4$s//.', 'themaster' ),
				$type,
				ucfirst( $args['projectType'] ),
				$args['projectName'],
				$templateName,
				$extended === 'extended' ? __( 'extended', 'themaster' ) : __( 'minimal', 'themaster' )
			);
			THEWPMASTER::set_adminMessage( $msg, 'success' );
		}}}}
	}

	private static function s_write_template( $template, $args ) {
		$dir = dirname( $args['basePath'] );
		foreach( $template as $tmp => $c ) {
			$e = substr( $tmp, strlen( $tmp ) -1, strlen( $tmp ) );
			$file = $dir . $tmp;
			if( $e === DS && !is_dir( $file ) ) {
				mkdir( $file );
			} elseif( $e !== DS ) {
				if( !file_exists( $file ) ) {
					fclose( fopen( $file, 'x' ) );
				}
				file_put_contents( $file, $c );
			}
		}
	}

	public static function missing_initArgs( $args ) {
		if( self::$s_access !== true ) { return false; }

		$msg = sprintf(
			__( 'Missing build information for %1$s **%2$s**. Please have a look at:/||//%3$s//.'),
			ucfirst( $args['projectType'] ),
			$args['projectName'],
			$args['projectFile']
		);
		THEWPMASTER::set_adminMessage( $msg, 'info' );
	}

	public static function sBuildClass( $className, $args, $Master ) {
		$template = $Master->projectType . DS . 'full' . DS;
		$extended = true;
		if( $Master->buildMissingClasses == true ) {
			$template .= 'def' . DS;
		} elseif( is_string( $Master->buildMissingClasses ) ) {
			if( count( ( $e = explode( '|', $Master->buildMissingClasses ) ) ) == 2 ) {
				if( $e[1] == 'false' || strtolower( $e[1] ) == 'mini' ) {
					$extended = 'mini';
				}
				unset( $e[1] );
				$template .= $e[0] . DS;
			} elseif( count( $e ) == 1 ) {
				$template .= $e[0] . DS;
			} else {
				throw new Exception( 'To much variables in buildMissingClasses', 1 );
			}
		} else {
			return false;
		}

		$template = array( DS . $Master->folderName . DS . 'classes' . DS . '__classname__.php' => file_get_contents( self::s_getBaseTemplatePath() . $template . '__foldername__'
			. DS . 'classes' . DS . '-n- __classname__.php' ) );

		$template = self::s_fillTemplate(
			$template,
			array_merge(
				$Master->_mastersInitArgs,
				array( 'ClassName' => $className )
			),
			$extended,
			''
		);

		self::s_write_template( $template, $Master->_mastersInitArgs );
		THEWPMASTER::set_adminMessage(
			sprintf(
				__( 'Successfully written Class **"%s"** for "%s".', 'themaster' ),
				$className,
				$Master->projectName
			),
			'success'
		);
		return $Master->get_instance( $className, $args );
	}

	private static function s_fillTemplate( $template, $args, $extended, $baseTemplateName ) {
		$r = array();
		foreach( $template as $k => $v ) {
			$r[ self::s_replaceTemplateTags( $k, $args, $extended, $baseTemplateName ) ] = 
				self::s_replaceTemplateTags( $v, $args, $extended, $baseTemplateName );
		}
		return $r;
	}

	private static function s_replaceTemplateTags( $string, $args, $extended, $baseTemplateName ) {
		if( $extended === 'mini' ) {
			$string = preg_replace( '/(\t)*(\/\/ \*\*EXTENDED\*\* \/\/)+(.*?)(\*\*EXTENDED_END\*\* \/\/)+(\t )*(\r\n|\n|\r)/ims', '', $string );
		} else {
			$string = preg_replace( '/(\t)*(\/\/ \*\*EXTENDED\*\* \/\/)+(\t )*(\r\n|\n|\r)/', '', $string );
			$string = preg_replace( '/(\t)*(\/\/ \*\*EXTENDED_END\*\* \/\/)+(\t )*(\r\n|\n|\r)/', '', $string );
		}
		preg_match_all( '/__(\w[^_]*)__/', $string, $m );

		foreach( $m[1] as $var ) {
			$val = isset( $args[$var] ) ? $args[$var] : self::s_additionalArg( $var, $args, $extended, $baseTemplateName );
			$string = str_replace( '__' . $var . '__', $val, $string );
		}
		return $string;
	}

	private static function s_additionalArg( $key, $args, $extended, $baseTemplateName ) {
		// THEDEBUG::diebug( get_option('gmt_offset') );
		if( !isset( $args['ClassName'] ) ) {
			$args['ClassName'] = 'Foo';
		}
		switch( $key ) {
			case 'classname':
				return strtolower( $args['ClassName'] );
			case 'ClassName':
				return $args['ClassName'];
			case 'tmminimal':
				$r = $extended === 'mini' ? ' false' : '';
				return $r . ( $baseTemplateName === 'def' ? ' ' : '' );
			case 'tmtemplate':
				$r = $extended === 'mini' ? ', ' : ' ';
				return $baseTemplateName === 'def' ? '' : $r . '\'' . $baseTemplateName . '\' ';
			case 'currentTime':
				$t = get_option('gmt_offset');
				if( $t !== 0 ) {
					$z = ( $t > 0 ? '+0' : '-0' ) . $t . ':00';
				} else {
					$z = '';
				}
				return date( sprintf( __( 'd.m.Y H:i:s %s', 'themaster' ), $z ) );
			case 'currentUser':
				return THEWPMASTER::get_user( 'data|display_name' );
			case 'namespace':
				return preg_replace('/[^A-Za-z]/', '', THEWPMASTER::get_user('data|display_name'))
					.DS.preg_replace('/[^A-Za-z]/', '', $args['projectName']);
			case 'mdh1projectName':
				return str_repeat('=', strlen($args['projectName']));
			case 'FILE':
				return '__FILE__';
			case 'DIR':
				return '__DIR__';
			case 'CLASS':
				return '__CLASS__';
			case 'baseConfigFile';
				return basename( $args['configFile'] );
			default:
				throw new \Exception('No fill value found for template variable: ' . $key, 1);
		}
	}

	private static function s_get_template( $path, $relpath = '', $extended, $n = false ) {
		$r = array();

		if( file_exists( ( $dir = self::s_getBaseTemplatePath() . $path ) ) 
		 && is_dir( $dir )
		) {
			foreach( THETOOLS::get_dirArray( $dir, null, array( 9, array( '.', '..', '.DS_Store' ) ) ) as $file ) {
				$target = $file;
				if( substr( $file, 0, 4 ) === '-n- ' ) {
					if( $n == false ) { continue; }
					$target = substr( $file, 4, strlen( $file ) );
				}
				if( substr( $file, 0, 4 ) === '-e- ' ) {
					if( $extended === 'mini' ) { continue; }
					$target = substr( $file, 4, strlen( $file ) );
				}
				if( is_dir( $dir . DS . $file ) ) {
					$r[$relpath . DS . $target . DS] = null;
					$r = array_merge( $r, self::s_get_template( $path . DS . $file, $relpath . DS . $target, $extended ) );
				} else {
					$r[$relpath . DS . $target] = file_get_contents( $dir . DS . $file );
				}
			}

		}
		return $r;
	}

	/**
     * Deactivation method for !THE MASTER
     *
     * @access private
     * @return void
     */
    public static function _masterDeactivate() {
        delete_option('Xiphe\THEMASTER\cachedInitArgs');
        self::$s_storeNewInitArgs = false;
        return parent::_masterDeactivate();
    }

} ?>