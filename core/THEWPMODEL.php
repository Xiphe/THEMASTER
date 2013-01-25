<?php
namespace Xiphe\THEMASTER\core;

use Xiphe as X;

/**
 * THEWPMODEL extends THEMODEL and adds a connection to the Wordpress database.
 *
 * @copyright Copyright (c) 2013, Hannes Diercks
 * @author    Hannes Diercks <xiphe@gmx.de>
 * @version   3.0.0
 * @link      https://github.com/Xiphe/-THE-MASTER/
 * @package   !THE MASTER
 */
class THEWPMODEL extends THEMODEL {
	private static $_structures = array();
	
	private $_possibleReadKey = array();
	
	private static $_WPTypes = array(
		'int' => '%d',
		'tinyint' => '%d',
		'smallint' => '%d',
		'mediumint' => '%d',
		'bigint' => '%d',
		'boolean' => '%d',
		'date' => '%d',
		'datetime' => '%d',
		
		'decimal' => '%f',
		'float' => '%f',
		'double' => '%f',
		'real' => '%f',
		
		'default' => '%s',
	);
	
	public function get_wpType($type) {
		if(isset(self::$_WPTypes[$type]))
			return self::$_WPTypes[$type];
		else
			return self::$_WPTypes['default'];
	}
	
	public function pre_read() {
		return true;
	}
	public function after_read() {}
	public function read_empty() {
		return true;
	}
	final public function read( $keys = '*', $args = null ) {
		if( $this->pre_read( $args ) ) {
			if(!self::$table || !$this->_checkReadKeys()) {
				throw new Exception('tryed to get DB entry without table or enough key information.', 1);
				return false;
			}
	
			global $wpdb;
			$where = $this->_whereKey();
			if(($result = $wpdb->get_row($wpdb->prepare('
				SELECT '.$keys.'
				FROM '.self::$table.'
				'.$where['where'],
				$where['values']
			)))) {
				$structure = $this->_get_structure();
				foreach($result as $k => $v) {
					if($this->get_wpType($structure[$k]) == '%d')
						$v = intval($v);
					if($this->get_wpType($structure[$k]) == '%f')
						$v = floatval($v);
					
					$this->$k = $v;
				}
			} else if(!$this->read_empty( $args )) {
				return $this;
			}
			$this->after_read( $args );
		}
		return $this;
	}
	
	private function _checkReadKeys() {
		$structure = $this->_get_structure();
		foreach($structure['___keys___'] as $keyName => $keyPair) {
			$ok = true;
			foreach($keyPair as $key) {
				if(!$this->$key) {
					$ok = false;
					break;
				}
			}
			if($ok) {
				$this->_possibleReadKey = $structure['___keys___'][$keyName];
				return true;
			}
		}
		return false;
	}
	
	public function pre_save() {
		return true;
	}
	public function after_save() {}
	final public function save() {
		if($this->pre_save()) {
			if(!self::$table) {
				throw new Exception('tryed to Save a Model without table', 1);
				return false;
			}
			global $wpdb;
			$values = array();
			$format = array();
			foreach($this->_get_structure() as $name => $type) {
				if(isset($this->$name)) {
					$values[$name] = $this->$name;
					$format[] = $this->get_wpType($type);
				}
			}
			if(isset($values['ID'])) {
				$wpdb->update(self::$table, $values, array('ID' => $values['ID']), $format);
			} elseif(count($values) > 0) {
				$wpdb->insert(self::$table, $values, $format);
			}
			
			$this->after_save();
		}
		return $this;
	}
	
	private function _whereKey() {
		$structure = $this->_get_structure();
		$where = 'WHERE 1=1 ';
		$values = array();
		foreach($this->_possibleReadKey as $k => $key) {
			$where .= 'AND '.$key.' = \''.$this->get_wpType($structure[$key]).'\'
';
			$values[] = $this->$key;
		}
		return array(
			'where' => $where,
			'values' => $values
		);
	}
	
	public function pre_delete() {
		return true;
	}
	public function after_delete() {}
	public function deleteError() { return true; }
	final public function delete() {
		if( call_user_func_array( array( $this, 'pre_delete' ), func_get_args() ) ) {
			if(!self::$table) {
				throw new \Exception('tryed to Delete a Model without table', 1);
				return false;
			}
			
			global $wpdb;
			
			$where = $this->_whereKey();
			$query = $wpdb->prepare(
				'DELETE FROM ' . self::$table . ' ' .
				$where['where'],

				$where['values']
			);

			if( empty($where['values'])
			 &&	$this->deleteError( 
			 		'eraseAll',
			 		'You will delete all entrys... return "ok" to continue.',
			 		$query
			 	) != 'ok'
			) {
				throw new \Exception('Error on Delete.', 1);
				return false;
			}

			if( ( $r = $wpdb->query( $query ) ) ) {
				$this->deleted = true;
				$this->after_delete();
				return $this;
			} else {
				if( $this->deleteError( 'delete went wrong.', $query, $r ) ) {
					throw new \Exception('Error on Delete.', 1);
					return false;
				}
			}
		}
		return $this;
	}
	
	public function _get_structure() {
		if(!isset(self::$_structures[self::$table])) {
			global $wpdb;
			foreach($wpdb->get_results('SHOW INDEX FROM '.self::$table) as $pk) {
				self::$_structures[self::$table]['___keys___'][$pk->Key_name][] = $pk->Column_name;
			}
			
			foreach($wpdb->get_results('
				SELECT DATA_TYPE, COLUMN_NAME
				FROM INFORMATION_SCHEMA.Columns
				WHERE TABLE_NAME = "'.self::$table.'";')
			as $column) {
				self::$_structures[self::$table][$column->COLUMN_NAME] = $column->DATA_TYPE;
			}
		}
		return self::$_structures[self::$table];
	}
} ?>
