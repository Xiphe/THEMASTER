<?php
	require_once('base.php');
	class THESETTINGS extends THEBASE {
		public $updateApiUrl = 'http://plugins.red-thorn.de/api/index.php';
		private $_settings = array(
			'debug' => false,
			'debugMode' => 'inline',  // inline, mail, FirePHP, summed
			'useHTML' => true,
			'debugEmail' => 'hdiercks@uptoyou.de',
			'debugEmailFrom' => 'noreply@uptoyou.de',
			'errorReporting' => false,
			'forceUpdates' => false,
		);
				
		protected function _masterInit($initArgs) {
			return parent::_masterInit($initArgs);
		}
		
		protected function _get_setting($key) {
			
			if(isset($this->_settings[$key])) {
				$const = get_defined_constants(true);
				
				if(isset($const['user']['THEMASTER_'.strtoupper($key)])) {
					return $const['user']['THEMASTER_'.strtoupper($key)];
				} else {
					return $this->_settings[$key];
				}
			} else {
				throw new Exception('Tried to get non-existent Setting "'.$key.'".');
			}
		}
	}
?>