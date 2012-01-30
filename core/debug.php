<?php
require_once('settings.php');

class THEDEBUG extends THESETTINGS {
	
	private $_debugs = array();
	
	private $_cDebug = array();
	
	private $_css = array(
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
	private $_cSorting = 'type';
	private $_cDirection = 'asc';
	
	private $_names = array('OK', 'Debug', 'Info', 'Warning', 'Error');
	
	protected function _masterInit($initArgs) {
		if($this->_get_setting('debugMode') == 'FirePHP') {
			try {
				require_once(dirname(dirname(__FILE__)).DS.'classes'.DS.'FirePHPCore'.DS.'fb.php');
				ob_start();
			} catch(exception $e) {
				echo $this->get_debug('Debug mode setted to FirePHP but FirePHP could not be found', 4);
			}
		}
		return parent::_masterInit($initArgs);
	}
	
	/** Generates the Debug Array and allowes mixing positions of nr & name
	 *
	 * @param array $args the called debug args
	 * @return void
	 * @access private
	 * @date Nov 10th 2011
	 */
	private function _gen_debug($args) {
		$this->_cDebug = array();
		$this->_cDebug['type'] = gettype($args[0]);
		$this->_cDebug['var'] = is_string($args[0]) ? htmlspecialchars($args[0]) : $args[0];
		for ($i=1; $i <= 2 ; $i++) { 
			if(isset($args[$i]) && is_int($args[$i])) {
				$this->_cDebug['nr'] = $args[$i];
			} elseif(isset($args[$i]) && is_string($args[$i])) {
				$this->_cDebug['name'] = $args[$i];
			}
		}
		if(!isset($this->_cDebug['nr']))
			$this->_cDebug['nr'] = 1;
		$bt = debug_backtrace();
		$this->_cDebug['btLine'] = isset($bt[3]['line']) ? $bt[3]['line'] : null;
		$this->_cDebug['btFile'] = isset($bt[3]['file']) ? $bt[3]['file'] : null;
		
		if(isset($this->_cDebug['name']))
			$this->_cDebug['name'] = substr($this->_cDebug['name'], 0, 1) == '$' ? $this->_cDebug['name'] : '$'.$this->_cDebug['name'];
		else
			$this->_cDebug['name'] = null;
	}
	
	private function callStack($depth = 3) {
		$offset = 2;
		$depth = $depth+$offset;
		$bt = debug_backtrace();
		$stack = array();
		for ($i = $offset; $i < $depth; $i++) {
			if(isset($bt[$i])) {
				$stack[$depth-$i] = array(
					'function' => $bt[$i]['function'],
					'called in File' => $bt[$i]['file'],
					'line' => $bt[$i]['line'],
				);
			}
			
		}
		$this->_inner_debug($stack, 'Call Info', 2);
	}
	
	public function debug($args = null) {
		$obj = isset($this) ? $this : self::inst();
		if(is_string($args) && strtolower($args) === 'callstack') $obj->callStack();
		elseif(is_string($args) && strtolower($args) === 'calledBy') $obj->callStack(1);
		else {
			call_user_func(array($obj, '_inner_debug'), func_get_args());
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
	public function _inner_debug($args = null) {
		if(empty($args)) return;
		if(!$this->_get_setting('debug')) return;
		elseif($this->_get_setting('debug') === 'get' && (!isset($_GET['debug']) || $_GET['debug'] != 'true')) return;
		
		$this->_gen_debug($args);
		
		// TODO: Summed Mails + Summed Output;
		switch ($this->_get_setting('debugMode')) {
			case 'mail':
				if(isset($this->projectName))
					$name = $this->projectName;
				else
					$name = 'THEDEBUG';
					$header  = "MIME-Version: 1.0\r\n";
					$header .= "Content-type: text/html; charset=iso-8859-1\r\n";
					$header .= "From: ".$this->_get_setting('debugEmailFrom')."\r\n";
					$header .= "Reply-To: ".$this->_get_setting('debugEmail')."\r\n";
					$header .= "X-Mailer: PHP ". phpversion();
					mail($this->_get_setting('debugEmail'), 'Debug from '.$name, $this->_get_debug($this->_cDebug), $header);
					break;
			case 'FirePHP':
				FB::setOptions(array('file' => $this->_cDebug['btFile'], 'line' => $this->_cDebug['btLine']));
				if($this->_cDebug['type'] == 'boolean') {
					$this->_cDebug['var'] = '(boolean) '.($this->_cDebug['var'] ? 'true' : 'false');
				} elseif($this->_cDebug['type'] == 'NULL') {
					$this->_cDebug['var'] = '(null) NULL';
				} elseif($this->_cDebug['type'] == 'string') {
					$this->_cDebug['var'] = '"'.$this->_cDebug['var'].'"';
				}
				switch ($this->_cDebug['nr']) {
					case 2:
						FB::info($this->_cDebug['var'], $this->_cDebug['name']);
						break;
					case 3:
						FB::warn($this->_cDebug['var'], $this->_cDebug['name']);
						break;
					case 4:
						FB::error($this->_cDebug['var'], $this->_cDebug['name']);
						break;
					default:
						FB::log($this->_cDebug['var'], $this->_cDebug['name']);
						break;
				}
				break;
			case 'summed':
				$this->_debugs[] = $this->_cDebug;
				break;
			default:
				echo $this->_get_debug($this->_cDebug);
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
	private function _get_debug($debugArr) {
		$r = '<div style="font-family: sans-serif; text-align: left; border: 1px solid '.$this->_css[$debugArr['nr']]['b'].';'.
		 'background: '.$this->_css[$debugArr['nr']]['bg'].'; color: '.$this->_css[$debugArr['nr']]['f'].';'.
		 ' padding: 10px; margin: 20px;"><h3 style="font-size: 30px;'.
		 'text-transform: uppercase; float: left; margin: 0 10px 0 0;">'.
		 $this->_names[$debugArr['nr']].'</h3><small style="position: relative; top: 4px; font-size: 11px;">'.
		 'File: '.$debugArr['btFile'].' - Line <strong>'.$debugArr['btLine'].'</strong>'.
		 '<br />Type: '.$debugArr['type'].'</small><br style="display: inline; clear: both;" />'.
		 '<pre style="text-align: left; color: #000; background: rgba(255,255,255,0.2); padding: 5px; margin: 5px 0 0;">';
		if(isset($debugArr['name']))
			$r .= $debugArr['name'].': ';
		$r .= var_export($debugArr['var'], true);
		$r .= '</pre></div>';
		return $r;
	}
	
	
	private function _sortDebugs($x, $y) {
		$c = $this->_cSorting;
		$c = $c == 'file' ? 'btFile' : $c == 'line' ? 'btLine' : $c;
		if(in_array($c, array('name', 'type', 'btFile'))) {
			$r = strcmp($x[$c], $y[$c]);
			
		} elseif(in_array($c, array('nr', 'btLine'))) {
			$r = 0;
			if($x[$c] > $y[$c])
				$r = 1;
			elseif($y[$c] > $x[$c])
				$r = -1;
		}
		if($this->_cDirection == 'asc')
			return $r;
		elseif($r > 0)
			return -1;
		elseif($r < 0)
			return 1;
		else
			return 0;
	}
	
	/** The output function for summed Debugs
	 *
	 * @param string $sorting null or name/nr/type/file/line
	 * @param string $dir null for asc or asc/desc
	 * @return void
	 * @access public
	 * @date Nov 10th 2011
	 */
	public function print_debug($sorting = null, $dir = 'asc') {
		
		if($sorting && count($this->_debugs) > 1) {
			$this->_cSorting = $sorting;
			$this->_cDirection = $dir;
			usort($this->_debugs, array($this, '_sortDebugs'));
		}
		foreach($this->_debugs as $debug) {
			echo $this->_get_debug($debug);
		}
	}
	
	/** Wrapper for $this->debug adds die() to the end.
	 *
	 * @param mixed $var the variable to be debugged
	 * @param string $name optional name of the variable
	 * @return void
	 * @access public
	 * @date Sep 22th 2011
	 */
	public function diebug() {
		$obj = isset($this) ? $this : self::inst();
		$args = func_get_args();
		call_user_func_array(array($obj, 'debug'), $args);
		die();
	}
	public function rebug($var) {
		$obj = isset($this) ? $this : self::inst();
		$args = func_get_args();
		call_user_func_array(array($obj, 'debug'), $args);
		return $var;
	}
}
?>