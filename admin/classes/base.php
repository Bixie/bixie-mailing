<?php
/**
 *	base.php - Component base defines
 *  Copyright (C) 2011-2014 Matthijs Alles
 *	Bixie.org
 *
 */
//base extensions
function print_r_pre($var,$string='') {
	if (empty($var)) echo 'Var is leeg!';
	if ($string) $string = $string.'<br/>';
	echo '<pre>'.$string;
	print_r($var);
	echo '</pre>';
}
function pr($var,$string='') {
	print_r_pre($var,$string);
}

/*debugging*/
class BPSdebugMessage {
	public $message;
	public $path;
	public $priority;
	public $filename;
	public $linenr;
	
	private $_microtime;
	private $_elapsed;
	
	public function __construct ($message,$path='algemeen.debug',$priority=3,$filename='',$linenr=null) {
		$this->message = $message;
		$this->path = $path;
		$this->priority = $priority;
		$this->filename = $filename;
		$this->linenr = $linenr;
		
		$this->_microtime = microtime(true);
		$this->_elapsed = round($this->_microtime - BPSdebug::$_start_time,4);
	}
	
	public function __toString() {
		$aHtml = array();
		$aHtml[] = '<div class="debugmess prio'.$this->priority.'">';
		$aHtml[] = '<div class="time"><small>';
		$aHtml[] = JText::sprintf('COM_BIXPRINTSHOP_DEBUG_TIME_SPR',$this->_elapsed);
		$aHtml[] = '</small></div>';
		if (is_string($this->message)) {
			$aHtml[] = '<div class="message">';
			$aHtml[] = $this->message;
			$aHtml[] = '</div>';
		} else {
			$aHtml[] = '<pre>';
			$aHtml[] = var_export($this->message,true);
			$aHtml[] = '</pre>';
		}
		if ($this->filename) {
			$aHtml[] = '<div class="fileinfo"><small>';
			$aHtml[] = JText::sprintf('COM_BIXPRINTSHOP_DEBUG_FILELINE_SPR',str_replace(JPATH_ROOT,'',$this->filename),$this->linenr);
			$aHtml[] = '</small></div>';
		}
		$aHtml[] = '</div>';
		return implode($aHtml);
	}
}

abstract class BPSdebug {

	public static $_start_time;
	
	public static function debug($message,$path='algemeen.debug',$priority=3,$filename='',$linenr=null) {
		if (!BixTools::config('algemeen.debugging.genDebug',0) || BixTools::config('algemeen.debugging.priolevel',3) < $priority) return true;
		if (empty(self::$_start_time)) self::$_start_time = microtime(true);
		$BPSdebugMessage = new BPSdebugMessage($message,$path,$priority,$filename,$linenr);
		self::_addDebug($BPSdebugMessage);
	}
	
	private static function _addDebug(BPSdebugMessage $BPSdebugMessage) {
		$app = JFactory::getApplication();
		$path = $BPSdebugMessage->path;
		$pathArr = explode('.', $path);
		$nrCats = count($pathArr);
		$debugVar = unserialize($app->getUserState('com_bixprintshop.debug',''));
		$setVar = &$debugVar;
		for ($i=0;$i<$nrCats;$i++) {
			if ($i==($nrCats-1)) { //laatste pushen
				if (!isset($setVar[$pathArr[$i]])) {
					$setVar[$pathArr[$i]] = array();
				}
				$setVar[$pathArr[$i]][] = $BPSdebugMessage;
			} else { //path doorlopen
				if (!isset($setVar[$pathArr[$i]])) {
					$setVar[$pathArr[$i]] = array();
				}
				$setVar = &$setVar[$pathArr[$i]];
			}
		}
		$app->setUserState('com_bixprintshop.debug',serialize($debugVar));
	}
	
	public static function printDebug() {
		$app = JFactory::getApplication();
		$aDebug = unserialize($app->getUserState('com_bixprintshop.debug',''));
		$aHtml = array();
		if (is_array($aDebug)) {
			self::_printDebugCat($aHtml,$aDebug);
		} else {
			$aHtml[] = '<div class="box-info">'.JText::_('Geen debug info beschikbaar').'</div>';
		}
		echo implode($aHtml);
		$app->setUserState('com_bixprintshop.debug','');
// echo nl2br(htmlspecialchars(implode(_N_,$aHtml)));
// pr($aDebug);
	}
	
	private static function _printDebugCat(&$aHtml,$rootCat,$catKey=null,$level=0) {
		if ($catKey === null) {
			$aHtml[] = '<ul class="bps-debug">';
		}
		if (!empty($catKey) && !is_numeric($catKey)) {
			$aHtml[] = '<ul class="debug lev'.$level.'">';
			$aHtml[] = '<li><label>'.$catKey.'</label></li>';
		}
		if ($rootCat instanceof BPSdebugMessage) {
			$aHtml[] = (string) $rootCat;
			return true;
		}
		if (is_array($rootCat)) {
			$level++;
			foreach ($rootCat as $catKey=>$rootCat) {
				$aHtml[] = '<li>';
				self::_printDebugCat($aHtml,$rootCat,$catKey,$level);
				$aHtml[] = '</li>';
			}
		}
		$aHtml[] = '</ul>';
	}
}

/*base classes*/
class basicClass {
	public function get($key,$def_value='') {
		$value = $this->$key;
		if (empty($value)) $value = $def_value;
		return $value;
	}
	
	public function setValue($key,$value) {
		if (property_exists($this,$key))
			return $this->$key = $value;
		return false;
	}
}

class BaseClass {
	protected $_params;
	protected $_error = array();
	protected $_debug = array();
	protected $_message = array();
	protected $_getForms = array();
	protected $_getObjects = array();
	
	/*classfuncties*/
	public function get($key,$def_value='',$group=null,$skipForms=false) {
		$value = null;
		if (property_exists($this,$key)) {
			$value = $this->$key; 
		}
		if ($value == null && !$skipForms) { //eerst forms ivm overrides in attribforms
			foreach ($this->_getForms as $formProperty) {
				$value = $this->$formProperty->getValue($key,$group,$def_value); 
			}
		}
		if ($value == null) {
			foreach ($this->_getObjects as $property) {
				if (!empty($group) && property_exists($this->$property,$group)) {
					if (is_array($this->$property->$group)) {
						$array = $this->$property->$group;
						if (isset($array[$key])) {
							$value = $array[$key];
						}
					} else {
						if (property_exists($this->$property->$group,$key)) {
							$value = $this->$property->$group->$key; 
						}
					}
				} else {
					if (property_exists($this->$property,$key)) {
						$value = $this->$property->$key; 
					}
				}
			}
		}
		if ($value == null) $value = $def_value;
		return $value;
	}
	public function set($key,$value,$group=null) {
		$valueSet = false;
		if ($group) {
			if (isset($this->$group)) {
				if (is_object($this->$group)) {
					if (property_exists($this->$group,$key)) { 
						$this->$group->$key = $value;
						$valueSet = true;
					}
				} elseif (is_array($this->$group)) {
					if (array_key_exists($key,$this->$group)) { 
						$this->$group[$key] = $value; 
						$valueSet = true;
					}
				}
			}
		} else {
			if (property_exists($this,$key)) { 
				$this->$key = $value;
				$valueSet = true;
			}
		}
		foreach ($this->_getForms as $formProperty) {
			if ($this->$formProperty->setValue($key,$group,$value)) {
				$valueSet = true;
			}
		}
		if ($group) {
			foreach ($this->_getObjects as $property) {
				if (is_object($this->$property->$group)) {
					if (property_exists($this->$property->$group,$key)) { 
						$this->$property->$group->$key = $value; 
						$valueSet = true;
					}
				} elseif (is_array($this->$property->$group)) {
					if (array_key_exists($key,$this->$property->$group)) { 
						$arr = $this->$property->$group;
						$arr[$key] = $value; 
						$this->$property->$group = $arr; //grrr
						$valueSet = true;
					}
				}
			}
		} else {
			foreach ($this->_getObjects as $property) {
				if (property_exists($this->$property,$key)) { 
				// pr( $value,$property.$key) ;
					return $this->$property->$key = $value; 
				}
			}
		}
		return $valueSet;
	}
	protected function setFormObject($propertyName) {
		$this->_getForms[] = $propertyName;
	}
	protected function setGetObject($propertyName) {
		$this->_getObjects[] = $propertyName;
	}
	protected function setParams($params=array()) {
		if (!isset($this->_params)) $this->_params = new stdClass;
		$this->_params->debug = false;
		foreach ($params as $key=>$value) {
			if (property_exists($this->_params,$key)) {
				$this->_params->$key = $value;
			}
		}
	}
	public function getParam($key,$def_value='') {
		$value = $this->_params->$key;
		if (empty($value)) $value = $def_value;
		return $value;
	}
	public function setParam($key,$value) {
		if (property_exists($this->_params,$key))
			return $this->_params->$key = $value;
		return false;
	}
	protected function setError($error,$cat='function') {
		$this->setDebug($error,get_class($this).'::'.$cat);
		$this->_error[] = JText::_($error);
		return false;
	}
	public function hasError() {
		return count($this->_error);
	}
	public function resetErrors() {
		$this->setDebug('Errors reset','resetErrors');
		$this->_error = array();
		return;
	}
	public function getError() {
		return implode('<br/>',$this->_error);
	}
	protected function setDebug($debug, $cat='') {
		if ($this->getParam('debug',false)) {
			if ($cat == '') $cat = get_class($this);
			$this->_debug[$cat][] = $debug;
		}
	}
	public function getDebug() {
		$html = array();
		foreach ($this->_debug as $debugcat=>$messages) {
			$html[] = '<strong>'.$debugcat.'</strong><ul>';
			foreach ($messages as $message) 
				if (is_string($message)) $html[]= '<li>'.$message.'</li>'; else	$html[]= '<li><pre>'.print_r($message,true).'</pre></li>';
			$html[] = '</ul>';
		}
		return implode ("\n",$html);
	}
}

class bixRegistry extends JRegistry {
	protected $_error = array();
	protected function setError($error,$cat='function') {
		$this->_error[] = $error;
		return false;
	}
	public function hasError() {
		return count($this->_error);
	}
	public function getError() {
		return implode('<br/>',$this->_error);
	}
}

class strictObj extends JObject {
	public function __construct($data=null) {
		if ($data) {
			foreach ($data as $k=>$v) {
				if (is_string($v) && preg_match('/^\{.*\}$/',$v) == true) {
					$registry = new JRegistry;
					$registry->loadString($v);
					$v = $registry->toArray();
				}
				$this->set($k,$v);
			}
		}
		unset($this->_errors);
	}
	public function get($key,$def_value='') {
		$value = $this->$key;
		if (empty($value)) $value = $def_value;
		return $value;
	}
	
	public function set($key,$value=null) {
		if (property_exists($this,$key))
			return parent::set($key,$value);
		return false;
	}
}


class BixException extends Exception {}
