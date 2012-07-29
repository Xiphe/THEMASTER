<?php
/************************************************************* 
 * This script is developed by Arturs Sosins aka ar2rsawseen, http://webcodingeasy.com 
 * Feel free to distribute and modify code, but keep reference to its creator 
 * 
 * CSSfix class can automatically apply vendor prefixes to specified CSS rules.
 * It also applies other fixes, for different CSS browser specifics.
 * 
 * For more information, examples and online documentation visit:  
 * http://webcodingeasy.com/PHP-classes/Automatically-add-CSS-vendor-prefixes
**************************************************************/
class CSSfix
{
	//provided css rules
	private $css = "";
	//store keyframes
	private $keyframes = array();
	//ignore properties
	private $ignore = array();
	//vendor prefixes
	private $prefixes = array(
		"Presto" => "o",
		"Gecko" => "moz",
		"Trident" => "ms",
		"AppleWebKit" => "webkit",
		"KDE" => "khtml"
	);
	//css rules
	private $rules = array(
		"@keyframes" => array("moz", "webkit", "o", "ms")
	);
	//css properties, that needs to be checked
	private $properties = array(
		"animation" => array("moz", "webkit", "o", "ms"),
		"background-clip" => array("moz", "webkit"),
		"background-origin" => array("moz", "webkit"),
		"background-position-x" => array("ms"),
		"background-position-y" => array("ms"),
		"background-size" => array("moz", "webkit", "o"),
		"background" => array("gradient"),
		"background-image" => array("gradient"),
		"border-bottom-color" => array("moz"),
		"border-bottom-left-radius" => array("webkit", "khtml", "mozborder"),
		"border-bottom-right-radius" => array("webkit", "khtml", "mozborder"),
		"border-image" => array("moz", "webkit", "o", "ms"),
		"border-left-color" => array("moz"),
		"border-radius" => array("moz", "webkit", "khtml"),
		"border-right-color" => array("moz"),
		"border-top-color" => array("moz"),
		"border-top-left-radius" => array("webkit", "khtml", "mozborder"),
		"border-top-right-radius" => array("webkit", "khtml", "mozborder"),
		"box-align" => array("moz", "webkit", "ms"),
		"box-direction" => array("moz", "webkit", "ms"),
		"box-flex" => array("moz", "webkit", "ms"),
		"box-flex-group" => array("moz", "webkit", "ms"),
		"box-lines" => array("moz", "webkit", "ms"),
		"box-ordinal-group" => array("moz", "webkit", "ms"),
		"box-orient" => array("moz", "webkit", "ms"),
		"box-pack" => array("moz", "webkit", "ms"),
		"box-shadow" => array("moz", "webkit"),
		"text-shadow" => array("moz", "webkit"),
		"box-sizing" => array("moz"),
		"column-count" => array("moz", "webkit"),
		"column-gap" => array("moz", "webkit"),
		"column-rule" => array("moz", "webkit"),
		"column-rule-color" => array("moz", "webkit"),
		"column-rule-style" => array("moz", "webkit"),
		"column-rule-width" => array("moz", "webkit"),
		"columns" => array("webkit"),
		"column-span" => array("webkit"),
		"column-width" => array("moz", "webkit"),
		"display" => array("box"),
		"filter" => array("ms"),
		"margin-end" => array("moz", "webkit"),
		"margin-start" => array("moz", "webkit"),
		"perspective" => array("moz", "webkit", "ms"),
		"opacity" => array("moz", "khtml", "msopacity"),
		"overflow-x" => array("ms"),
		"overflow-y" => array("ms"),
		"tab-size" => array("moz", "o"),
		"text-justify" => array("ms"),
		"text-overflow" => array("ms"),
		"transform" => array("moz", "webkit", "o", "ms"),
		"transform-origin" => array("moz", "webkit", "o", "ms"),
		"transform-style" => array("moz", "webkit", "o", "ms"),
		"transition" => array("moz", "webkit", "o", "ms"),
		"transition-delay" => array("moz", "webkit", "o", "ms"),
		"transition-duration" => array("moz", "webkit", "o", "ms"),
		"transition-property" => array("moz", "webkit", "o", "ms"),
		"transition-timing-function" => array("moz", "webkit", "o", "ms"),
		"user-modify" => array("moz", "webkit"),
		"user-select" => array("moz", "webkit"),
		"word-break" => array("ms"),
		"word-wrap" => array("ms")
	);
	
	//set prefixes to use
	public function set_prefixes($prefixes){
		$this->prefixes = $prefixes;
	}
	
	//get array of prefixes
	public function get_prefixes(){
		return $this->prefixes;
	}
	
	//get array of prefixes
	public function get_properties(){
		return array_keys($this->properties);
	}
	
	//set css properties to ignore
	public function set_ignore($properties){
		$this->ignore = $properties;
	}
	
	//read css properties from file
	public function from_file($css_file){
		if($css_file != "" && file_exists($css_file))
		{
			$this->css .= file_get_contents($css_file);
		}
	}
	
	//load css properties from string
	public function from_string($css_rules){
		$this->css .= $css_rules;
	}
	
	//generate modified css
	public function generate($min = true){
		$data = $this->parse();
		$data = $this->check_props($data);
		return $this->build($data, $min);
	}
	
	//store modified css to file
	public function to_file($file, $min = true){
		$css = $this->generate($min);
		file_get_contents($file, $css);
	}
	
	/*********************************
	* PRIVATE FUNCTIONS
	**********************************/
	//parse css
	private function parse(){
		$data = array();
		try {
			$css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $this->css);
			$css = preg_replace('/\s+/', ' ', $css);
			preg_match_all('/@([^{]*)\s*\{\s*(.*)\s*}/', $css, $result);
			foreach($result[0] as $key => $val)
			{
				$css = str_replace($val, '', $css);
				$this->keyframes[] = array("key" => $result[1][$key], "val" => $result[2][$key]);
			}
			preg_match_all('/([^{]*)\s*\{\s*([^}]*)\s*}/', $css, $result);
			foreach($result[1] as $key => $selector)
			{
				$ndata = array();
				$selector = trim($selector);
				$ndata["key"] = $selector;
				$ndata["val"] = array();
				$rules = explode(";", $result[2][$key]);
				foreach($rules as $rule)
				{
					if(trim($rule) != "")
					{
						$parts = explode(":", $rule);
						$nprop = array();
						$nprop["key"] = trim($parts[0]);
						$nprop["val"] = trim(implode(":", array_slice($parts, 1)));
						$ndata["val"][] = $nprop;
					}
				}
				$data[] = $ndata;
			}
		}
		catch(Exception $e)
		{
			echo 'Error parsing CSS rules: ',  $e->getMessage(), "\n";
		}
		return $data;
	}
	
	//apply provided prefix
	private function apply_prefix($property, $prefix){
		if(in_array($prefix, $this->prefixes))
		{
			return "-".$prefix."-".$property;
		}
		return "";
	}
	
	//apply opera prefix
	private function o($property, $val){
		return array(array("key" => $this->apply_prefix($property, "o"), "val" => $val));
	}
	
	//apply mozilla prefix
	private function moz($property, $val){
		return array(array("key" => $this->apply_prefix($property, "moz"), "val" => $val));
	}
	
	//apply webkit prefix
	private function webkit($property, $val){
		return array(array("key" => $this->apply_prefix($property, "webkit"), "val" => $val));
	}
	
	//apply KDE prefix
	private function khtml($property, $val){
		return array(array("key" => $this->apply_prefix($property, "khtml"), "val" => $val));
	}
	
	//apply IE prefix
	private function ms($property, $val){
		return array(array("key" => $this->apply_prefix($property, "ms"), "val" => $val));
	}
	
	//apply mozilla borders
	private function mozborder($property, $val){
		$property = str_replace('top-left-radius', 'radius-topleft', $property);
		$property = str_replace('top-right-radius', 'radius-topright', $property);
		$property = str_replace('bottom-right-radius', 'radius-bottomright', $property);
		$property = str_replace('bottom-left-radius', 'radius-bottomleft', $property);
		return array(array("key" => "-moz-".$property, "val" => $val));
	}

	//apply gradient modification
	private function gradient($property, $val){
		$props = array();
		$prefs = array("ms", "moz", "o", "webkit");
		if(strpos($val, "linear-gradient") !== false || strpos($val, "radial-gradient") !== false)
		{
			foreach($prefs as $pref)
			{
				$newprop = array();
				$newprop["key"] = $property;
				$newprop["val"] = $this->apply_prefix($val, $pref);
				if($newprop["val"] != "")
				{
					$props[] = $newprop;
				}
			}
		}
		return $props;
	}
	
	//apply opacity modification
	private function msopacity($property, $val){
		$props = array();
		if(in_array("ms", $this->prefixes))
		{
			$val = (double)$val*100;
			$newprop = array();
			$newprop["key"] = "filter";
			$newprop["val"] = '"progid:DXImageTransform.Microsoft.Alpha(Opacity='.$val.')"';
			$props[] = $newprop;
			
			$newprop = array();
			$newprop["key"] = "-ms-filter";
			$newprop["val"] = '"progid:DXImageTransform.Microsoft.Alpha(Opacity='.$val.')"';
			$props[] = $newprop;
		}
		return $props;
	}
	
	//apply display box modification
	private function box($property, $val){
		$props = array();
		$prefs = array("moz", "webkit");
		if(strpos($val, "box") !== false)
		{
			foreach($prefs as $pref)
			{
				$newprop = array();
				$newprop["key"] = $property;
				$newprop["val"] = $this->apply_prefix($val, $pref);
				if($newprop["val"] != "")
				{
					$props[] = $newprop;
				}
			}
		}
		return $props;
	}
	
	//check key frames
	private function check_keyframes(){
		$data = array();
		foreach($this->keyframes as $frame)
		{
			$data[] = array("key" => "@".$frame["key"], "val" => $frame["val"]);
			foreach($this->rules["@keyframes"] as $prefix)
			{
				$newkey = call_user_func(array($this,$prefix), $frame["key"], $frame["val"]);
				foreach($newkey as $newval)
				{
					if($newval["key"] != "" && !in_array($newval["key"], $this->ignore))
					{
						$data[] = array("key" => "@".$newval["key"], "val" => $newval["val"]);
					}
				}
			}
		}
		$this->keyframes = $data;
	}
	
	//check properties
	private function check_props($data){
		$this->check_keyframes();
		$newdata = array();
		foreach($data as $rule)
		{
			$selector = $rule["key"];
			if(!isset($newdata[$selector]))
			{
				$newdata[$selector] = array();
			}
			foreach($rule["val"] as $val)
			{
				$key = $val["key"];
				$val = $val["val"];
				if(isset($this->properties[$key]))
				{
					foreach($this->properties[$key] as $prefix)
					{
						$newkey = call_user_func(array($this,$prefix), $key, $val);
						foreach($newkey as $newval)
						{
							if($newval["key"] != "" && !in_array($newval["key"], $this->ignore))
							{
								$newdata[$selector][] = array("key" => $newval["key"], "val" => $newval["val"]);
							}
						}
					}
				}
				$newdata[$selector][] = array("key" => $key, "val" => $val);
			}
		}
		return $newdata;
	}
	
	//build new css string
	private function build($data, $min){
		$str = "";
		foreach($this->keyframes as $rule)
		{
			$selector = $rule["key"];
			$str .= ($min) ? $selector."{" : $selector."{\n";
			$str .= ($min) ? $rule["val"] : "\t".$rule["val"]."\n";
			$str .= ($min) ? "}" : "}\n";
		}
		foreach($data as $selector => $rule)
		{
			$str .= ($min) ? $selector."{" : $selector." {\n";
			foreach($rule as $val)
			{
				$key = $val["key"];
				$val = $val["val"];
				$str .= ($min) ? $key.":".$val.";" : "\t".$key.": ".$val.";\n";
			}
			$str .= ($min) ? "}" : "}\n";
		}
		return $str;
	}
}
?>