<?php
require_once( 'master.php' );
class THEWPBUILDER extends THEMASTER {

	private static $s_baseTemplatePath;
	private static $s_initiated = false;
	private static $s_access = false;

	private static $s_initArgsCache = array();

	private static $s_buildArgs = array();


	/**
	 * The Constructor method
	 *
	 * @param	array	$initArgs	the initiation arguments
	 */
	public function __construct( $initArgs ) {
		if( !self::$s_initiated ) {
			THEBASE::sRegister_callback( 'afterBaseS_init', array( 'THEWPBUILDER', 'sinit' ) );
		}

		// Pass ball to parent.
		parent::__construct( $initArgs );
	}

	/**
	 * One time initiaton.
	 */
	public static function sinit() {
		if( !self::$s_initiated ) {
			// Get all options from database.
			if( function_exists( 'get_option' ) ) {
				self::$s_initArgsCache = unserialize( get_option( 'tm-cachedInitArgs', 'a:0:{}' ) );
			}

			if( isset( $GLOBALS['pagenow'] ) 
			 && in_array( $GLOBALS['pagenow'], array( 'plugins.php', 'themes.php' ) )
			 && function_exists( 'is_admin' ) && is_admin()
			) {
				self::$s_access = true;
				add_action( 'init', array( 'THEWPBUILDER', 'sStartToBuild' ) );	
			}
			// Prevent this from beeing executed twice.
			self::$s_initiated = true;
		}
	}

	private static function s_cacheInitArgs() {
		if( function_exists( 'update_option' ) ) {
			update_option( 'tm-cachedInitArgs', serialize( self::$s_initArgsCache ) );
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

	private function _check_folderStructure($structure, $basePath) {
		if(!isset(self::$_ftp_conn_id) && defined('FTP_HOST') && defined('FTP_USER') && defined('FTP_PASS')) {
			self::$_ftp_conn_id = ftp_connect(FTP_HOST);
			$login_result = ftp_login(self::$_ftp_conn_id, FTP_USER, FTP_PASS);
			$basePath = str_replace(FTP_WORKING_PATH, '', $basePath);
		}
		self::_check_folderStructureWalker($structure, '', $basePath);
		if(isset(self::$_ftp_conn_id)) {
			ftp_close(self::$_ftp_conn_id);
		}
	}
	
	private static function _check_folderStructureWalker($structure, $basedir, $root) {
		foreach($structure as $folder => $chmod) {
			$subfolders = null;
			if(is_array($chmod)) {
				$t = $chmod['chmod'];
				unset($chmod['chmod']);
				$subfolders = $chmod;
				$chmod = $t;
			}
			$dir = $root.$basedir.DS.$folder.DS;
			if(is_dir($dir)) {
				if(!isset(self::$_ftp_conn_id)) {
					chmod($dir, $chmod);
				} else {
					ftp_chmod(self::$_ftp_conn_id, $chmod, $dir);
				}
			} else {
				if(!isset(self::$_ftp_conn_id)) {
					mkdir($dir, $chmod);
				} else {
					ftp_mkdir(self::$_ftp_conn_id, $dir);
					ftp_chmod(self::$_ftp_conn_id, $chmod, $dir);
				}
			}
			
			if(is_array($subfolders)) {
				self::_check_folderStructureWalker($subfolders, $basedir.DS.$folder, $root);
			}
		}
	}

	public static function get_initArgs( $file = null, $file2 = null, $deepth = 1 ) {
		// THEDEBUG::debug( 'callstack' );
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
		
		$configFile = strstr( $file, 'wp-content' . DS . 'themes' ) ?
			dirname( $file ) . DS . 'style.css' : $file;

		$textID = THEBASE::get_textID( $configFile );

		if( isset( self::$s_initArgsCache[ $textID ] )
		 && self::$s_initArgsCache[ $textID ]['time'] >= ( filemtime( $configFile ) + filemtime( $file ) ) / 2
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
		$iA['projectType'] = ( strstr( $iA['basePath'], 'wp-content' . DS . 'themes' ) ) 
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
					case 'prefix':
						$iA['prefix'] = trim( $p[1] );
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
		if( !isset( $iA['prefix'] ) && isset( $iA['projectName'] ) && isset( $iA['author'] ) ) {
			$px = preg_replace( '/[^A-Z]/', '', $iA['author'] );
			$px .= preg_replace( '/[^A-Z]/', '', $iA['projectName'] );
			if( strlen( $px ) >= 4 ) {
				$iA['prefix'] = $px . '_';
			}
		}

		if( !isset( $iA['updatable'] ) ) {
			$iA['updatable'] = false;
		}

		if( !isset( $iA['textID'] ) ) {
			$iA['textID'] =  THEBASE::get_textID( $iA['configFile'] );
		}

		self::$s_initArgsCache[ $iA['textID'] ] = array(
			'time' => ( filemtime( $iA['configFile'] ) + filemtime( $iA['projectFile'] ) ) / 2,
			'args' => $iA
		);
		self::s_cacheInitArgs();

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
				$pF = preg_replace( '/(\t)*BUILDTHEMASTER( )*\((.)*\)( )*;( )*\n/', "\tTHEWPMASTERINIT( __FILE__ );\n", $pF);
				$pF = str_replace( array(
						"// *optional*\n",
						"// Update Server: \n",
						"// Required Plugins: \n",
						"// Please fill in additional Plugin information.\n"
					),
					'',
					$pF
				);
				$pF = str_replace( 'BUILDTHEMASTER', 'THEWPMASTERINIT', $pF );
				file_put_contents( $args['projectFile'], $pF );
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
			$string = preg_replace( '/(\t)*(\/\/ \*\*EXTENDED\*\* \/\/)+(.*?)(\*\*EXTENDED_END\*\* \/\/\n)+/ims', '', $string );
		} else {
			$string = preg_replace( '/(\t)*(\/\/ \*\*EXTENDED\*\* \/\/\n)+/', '', $string );
			$string = preg_replace( '/(\t)*(\/\/ \*\*EXTENDED_END\*\* \/\/\n)+/', '', $string );
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
		switch( $key ) {
			case 'tmminimal':
				$r = $extended === 'mini' ? ' false' : '';
				return $r . ( $baseTemplateName === 'def' ? ' ' : '' );
				break;
			case 'tmtemplate':
				$r = $extended === 'mini' ? ', ' : ' ';
				return $baseTemplateName === 'def' ? '' : $r . '\'' . $baseTemplateName . '\' ';
				break;
			case 'PREFIX':
				return strtoupper( $args['prefix'] );
				break;
			case 'lcprefix':
				return strtolower( $args['prefix'] );
				break;
			case 'currentTime':
				$t = get_option('gmt_offset');
				if( $t !== 0 ) {
					$z = ( $t > 0 ? '+0' : '-0' ) . $t . ':00';
				} else {
					$z = '';
				}
				return date( sprintf( __( 'd.m.Y H:i:s %s', 'themaster' ), $z ) );
				break;
			case 'currentUser':
				return THEWPMASTER::get_user( 'data|display_name' );
				break;
			case 'FILE':
				return '__FILE__';
				beak;
			case 'DIR':
				return '__DIR__';
				break;
			case 'CLASS':
				return '__CLASS__';
				break;
			case 'baseConfigFile';
				return basename( $args['configFile'] );
				break;
			default:
				throw new Exception('No fill value found for template variable: ' . $key, 1);
				break;
		}
	}

	private static function s_get_template( $path, $relpath = '', $extended ) {
		$r = array();

		if( file_exists( ( $dir = self::s_getBaseTemplatePath() . $path ) ) 
		 && is_dir( $dir )
		) {
			foreach( THEBASE::get_dirArray( $dir, null, array( 9, array( '.', '..', '.DS_Store' ) ) ) as $file ) {
				$target = $file;
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

} ?>