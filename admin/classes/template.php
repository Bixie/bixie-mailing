<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */

// No direct access
defined('_JEXEC') or die;

/**
 * Class BixMailingTemplate
 */
class BixMailingTemplate extends JObject {

	/*db fields*/
	public $id;

	public $type;

	public $onderwerp;

	public $content;

	public $state;

	public $params;

	public $checked_out;

	public $checked_out_time;

	public $created;

	public $created_by;

	public $modified;

	public $modified_by;

	/*statics*/
	protected static $_jsonVars = array('params');

	/**
	 * @param null|JObject $item
	 */
	public function __construct ($item = null) {
		$properties = $item ? $item->getProperties() : null;
		parent::__construct($properties);
		$this->id = (int)$this->id; //avoid null
		//jsonvars
		if (count(self::$_jsonVars)) {
			foreach (self::$_jsonVars as $jsonVar) {
				if (property_exists($this, $jsonVar) && is_string($this->$jsonVar)) {
					$registry = new JRegistry;
					$registry->loadString($this->$jsonVar);
					$this->$jsonVar = $registry->toArray();
				}
			}
		}

	}

}