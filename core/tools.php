<?php class THETOOLS {

	/**
	 * Deletes chars specified by $regex from $path
	 *
	 * Default regex allowes alphanumeric chars, "!", "_", "-", "/", "\" and " ".
	 * 
	 * @access public
	 * @param  string $path  the path to be cleaned.
	 * @param  string $regex the regex of valid chars.
	 * @return string        the clean path
	 */
	public function delete_invalidPathChars( $path, $regex = '!_-\w\/\\\ ' ) {
		return preg_replace( '/[^' . $regex . ']/', '', $path );
	}
	
	/**
	 * Checks if the path has invalid characters.
	 *
	 * @access public
	 * @param  string $path
	 * @param  bool   $clean set true to call self::get_directPath() on $path
	 * @param  string $regex the regex of valid chars
	 * @return void
	 */
	public function is_cleanPath( $path, $clean = false, $regex = '!_-\w\/\\\ ') {
		if( $clean === true ) {
			$path = self::get_directPath( $path );
		}
		if( preg_match( '/[^' . $regex . ']/', $path ) ) {
			return false;
		}
		return $path;
	}
	
	/**
	 * Converts to DIRECTORY_SEPERATOR, deletes ../ and invalid chars.
	 *
	 * valid chars are alphanumeric chars, "!", "_", "-", "/", "\" and " "
	 *
	 * @access public
	 * @param  string $path an unclean path.
	 * @return string       a clean path.
	 */
	public function get_verryCleanedDirectPath( $path ) {
		return self::delete_invalidPathChars( self::get_directPath( $path ) );
	}
	
	/**
	 * Deletes ../ in pathes and cleans it with self::get_cleanedPath()
	 *
	 * @access public
	 * @param  string $path input path.
	 * @return string
	 */
	public function get_directPath( $path ) {
		return str_replace( '..' . DS, '', self::get_cleanedPath( $path ) );
	}
	
	
	/** 
	 * Replaces / & \ to DIRECTORY_SEPERATOR in $path
	 *
	 * @access public
	 * @param string $path input path
	 * @return string
	 * @date Jan 22th 2012
	 */
	public function get_cleanedPath( $path ) {
		return preg_replace( "/[\/\\\]+/", DS, $path );
	}

	/**
	 * Getter for Static Variables of Named Classes
	 *
	 * @param  string $classname
	 * @param  string $key
	 * @return mixed
	 */
	function get_static_var( $classname, $key ) {
		$vars = get_class_vars( $classname );
		if( isset( $vars[$key] ) ) {
			return $vars[$key];
		}
	}

	/**
	 * returns an array of files in a specific folder, excluding files starting with . or _
	 *
	 * The default filter can be adjusted as a third parameter.
	 * Accepted is an array with the amount of chars that will be filtered at the beginning
	 * of the file or folder name on the first index. The second index have to be another
	 * array containing strings that are not accepted.
	 * 
	 * @access public
	 * @param  string $dir    the directory
	 * @param  mixed  $key    option for the array key of each file number or filename
	 *                        possible, default: the filename
	 * @param  array  $filter an array defining the. 
	 * @return array
	 */
	public function get_dirArray( $dir, $key = 'filename', $filter = null ) {
		if( !isset( $filter ) ) {
			$filter = array( 1, array( '.', '_' ) );
		}
		if ( $handle = opendir( $dir ) ) {
			$r = array();
			$i = 0;
			while ( false !== ( $file = readdir( $handle ) ) ) {
				if ( !in_array( substr( $file, 0, $filter[0] ), $filter[1] ) ) {
					$k = $key == 'filename' ? $file : $i;
					$r[$k] = $file;
					$i++;
				}
			}
			return $r;
		}
	}

	/**
	 * Looks if a session has been started, stars one if not and returns it.
	 * 
	 * @return array the session.
	 */
	public function session() {
		if( !isset( $_SESSION ) && !headers_sent() )
			session_start();

		if( isset( $_SESSION ) )
			return $_SESSION;
	}

	/**
	 * Checks if Array 1 has all required keys, specified by Array 2
	 * 
	 * @access public
	 * @param  array $args         the array to be checked.
	 * @param  array $requiredArgs the required keys.
	 * @return string|false        Error string or false if no Error found.
	 */
	public function get_requiredArgsError( $args, $requiredArgs ) {
		if( !is_array( $args ) )
			return __( '$args is not an array', 'themaster' );
		if( !is_array( $requiredArgs ) )
			return __( '$required is not an array', 'themaster' );

		$missing = array();
		foreach( $requiredArgs as $req ) {
			if( !isset( $args[$req] ) ) {
				$missing[] = $req;
			}
		}
		
		if( count( $missing ) == 0 ) {
			return false;
		} else {
			if( count( $missing ) == 1 ) {
				return sprintf( __( 'Missing "%s" as a key.', 'themaster' ), $missing[0] );
			} else {
				$and = $missing[ count( $missing ) - 1 ];
				unset( $missing[ count( $missing ) - 1 ] );
				$missing = implode( ', ', $missing ) . ' ' . __( 'and', 'themaster' ) . ' ' . $and;
				return sprintf( __( 'Missing "%s" as keys.', 'themaster' ), $missing );
			}
		}
	}

	/**
	 * Converts a given integer into a human readable file-size.
	 *
	 * from: http://codeaid.net/php/convert-size-in-bytes-to-a-human-readable-format-%28php%29
	 *
	 * @access public
	 * @param  integer  $bytes    the basic size to be recalculated.
	 * @param  integer $precision how much floating values?
	 * @return string             the readable size
	 */
	public function bytesToSize($bytes, $precision = 2) {  
		$kilobyte = 1024;
		$megabyte = $kilobyte * 1024;
		$gigabyte = $megabyte * 1024;
		$terabyte = $gigabyte * 1024;

		if (($bytes >= 0) && ($bytes < $kilobyte)) {
			return $bytes . ' B';
		} elseif (($bytes >= $kilobyte) && ($bytes < $megabyte)) {
			return round($bytes / $kilobyte, $precision) . ' KB';
		} elseif (($bytes >= $megabyte) && ($bytes < $gigabyte)) {
			return round($bytes / $megabyte, $precision) . ' MB';
		} elseif (($bytes >= $gigabyte) && ($bytes < $terabyte)) {
			return round($bytes / $gigabyte, $precision) . ' GB';
		} elseif ($bytes >= $terabyte) {
			return round($bytes / $terabyte, $precision) . ' TB';
		} else {
			return $bytes . ' B';
		}
	}

	/**
	 * Generates a identifier string based on file and folder name of given path.
	 * 
	 * @param  string $file the path
	 * @return string       the textID
	 */
	public function get_textID( $file ) {
		return basename( dirname( $file ) ) . '/' . basename( $file );
	}

		/**
	 * returns an array containing the keys of $data, starting with the given prefix.
	 * 
	 * $data = array( 'foo_bar1' => 'bar, 'bar_bar2' => 'foo' );
	 * filtered by $match = 'foo' will return array( 'foo_bar1' => 'bar );
	 *
	 * @access public
	 * @param  string      $match the beginning of the $data keys that should be returned
	 * @param  array|class $data  the data.
	 * @return array
	 */
	public function filter_data_by( $match, $data ) {
		$args = array();
		foreach ( $data as $key => $value ) {
			if ( substr( $key, 0, 1 ) == '_' ) {
				$key = substr( $key, 1, strlen( $key ) );			
			}
			if ( strlen( $key ) > strlen( $match )
			&&   substr( $key, 0, strlen( $match ) ) == $match ) {
				$args[str_replace( $match.'_', '', $key )] = $value;
			}
		}
		return $args;
	}
	
	/**
	 * returns an array containing the keys of $_POST, starting with the given prefix.
	 * 
	 * $_POST = array( 'foo_bar1' => 'bar, 'bar_bar2' => 'foo' );
	 * filtered by $match = 'foo' will return array( 'foo_bar1' => 'bar );
	 *
	 * @access public
	 * @param  string $match the beginning of the $_POST keys that should be returned
	 * @return array
	 */
	public function filter_postDataBy( $string ) {
		return $this->filter_data_by( $string, $_POST );
	}

	/**
	 * returns an array containing the keys of $_GET, starting with the given prefix.
	 * 
	 * $_GET = array( 'foo_bar1' => 'bar, 'bar_bar2' => 'foo' );
	 * filtered by $match = 'foo' will return array( 'foo_bar1' => 'bar );
	 *
	 * @access public
	 * @param  string $match the beginning of the $_GET keys that should be returned
	 * @return array
	 */
	public function filter_getDataBy( $string ) {
		return $this->filter_data_by( $string, $_GET );
	}

	/**
	 * returns an array containing the keys of $_REQUEST, starting with the given prefix.
	 * 
	 * $_REQUEST = array( 'foo_bar1' => 'bar, 'bar_bar2' => 'foo' );
	 * filtered by $match = 'foo' will return array( 'foo_bar1' => 'bar );
	 *
	 * @access public
	 * @param  string $match the beginning of the $_REQUEST keys that should be returned
	 * @return array
	 */
	public function filter_requestDataBy( $string ) {
		return $this->filter_data_by( $string, $_REQUEST );
	}
}

define( 'THETOOLSAVAILABLE', true );
$GLOBALS['THETOOLS'] = new THETOOLS;

?>