<?php
class THEMODEL {
	public $ID;
	
	public static $table;
	
	
	public function __construct($initArgs = null) {
		if(is_int($initArgs)) {
			$this->ID = $initArgs;
		} elseif(is_array($initArgs) || is_object($initArgs)) {
			foreach($initArgs as $k => $v) {
				$this->$k = $v;
			}
		}
		$this->init();
	}
	
	public function init() {
	}
	
	public function get( $name ) {
		if( method_exists( $this, 'get_' . $name ) ) {
			$args = func_get_args();
			unset( $args[0] );
			return call_user_func_array( array( $this, 'get_' . $name ), $args );
		}
		return $this->$name;
	}
	
	public function set($name, $var) {
		if( method_exists( $this, 'set_' . $name ) ) {
			return call_user_func( $f );
		}
		if($var == '++' && is_int($this->$name))
			$this->$name = $this->$name+1;
		elseif($var == '--' && is_int($this->$name))
			$this->$name--;
		else
			$this->$name = $var;
		return $this;
	}
	
	private function _debug( $args ) {
		if( !isset( $args[3] ) ) {
			for( $i=0; $i <= 3; $i++ ) {
				if($i != 3 && !isset( $args[$i] ) ) {
					$args[$i] = null;
				} elseif( $i == 3 ) {
					$args[$i] = 7;
				}
			}
		} else {
			$args[3] = $args[3]+7;
		}
		return $args;
	}
	
	public function debug() {
		THEDEBUG::_set_btDeepth( 7 );
		call_user_func_array( array( 'THEDEBUG', 'debug' ), func_get_args() );
		THEDEBUG::_reset_btDeepth();
	}
	
	public function diebug() {
		THEDEBUG::_set_btDeepth( 7 );
		call_user_func_array( array( 'THEDEBUG', 'diebug' ), func_get_args() );
	}
	
	public function rebug() {
		THEDEBUG::_set_btDeepth( 7 );
		$r = call_user_func_array( array( 'THEDEBUG', 'rebug' ), func_get_args() );
		THEDEBUG::_reset_btDeepth();
		return $r;
	}

	public function countbug() {
		THEDEBUG::_set_btDeepth( 7 );
		call_user_func_array(array('THEDEBUG', 'countbug'), func_get_args() );
		THEDEBUG::_reset_btDeepth();
	}
	
	public function deprecated( $alternative, $contunue = true, $bto = 0 ) {
		THEDEBUG::_set_btDeepth( 7 );
		$bto = $bto+2;
		return call_user_func_array(
			array('THEDEBUG', 'deprecated'),
			array( $alternative, $contunue, $bto )
		);
		THEDEBUG::_reset_btDeepth();
	}
	
} ?>
