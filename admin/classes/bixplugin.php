<?php
/**
 *	com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *	Bixie.nl
 *
 */

 
class BixPlugin extends BaseClass {
	
	protected $_dispatcher;
		
	public function __construct($groups=array('bixmailing')) {
		$this->_dispatcher = JDispatcher::getInstance();
		if (count($groups)) {
			foreach ($groups as $group) {
				$this->importPlugin($group);
			}
		}
		$this->setParams(array());
	}
	public function importPlugin($group) {
		JPluginHelper::importPlugin($group);
	}

	public function trigger($event,$args=array()) {
		$validResults = array();
		$results = $this->_dispatcher->trigger($event,$args);
		if (count($results) && in_array(false, $results, true)) {
 //pr($this->_dispatcher->getError(),$event);//TODO
			$this->setError($this->_dispatcher->getError());
		}
		foreach ($results as $result) {
			if ($result == '') continue;
			$validResults[] = $result;
		}
		return $validResults;
	}

	
}


?>