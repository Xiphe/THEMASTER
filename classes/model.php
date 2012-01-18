<?php class THEMODEL {
	public $ID;
	
	public $table;
	
	
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
	
	public function get($name) {
		return $this->$name;
	}
	
	public function set($name, $var) {
		if($var == '++' && is_int($this->$name))
			$this->$name = $this->$name+1;
		elseif($var == '--' && is_int($this->$name))
			$this->$name--;
		else
			$this->$name = $var;
		return $this;
	}
	
} ?>
