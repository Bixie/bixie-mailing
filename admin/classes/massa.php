<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */

// No direct access
defined('_JEXEC') or die;

/**
 * Class BixMailingMassa
 */
class BixMailingMassa extends JObject {

	/*db fields*/
	public $id;

	public $user_id;

	public $type;

	public $naam;

	public $aang;

	public $opmerking;

	public $bestanden;

	public $status;

	public $state;

	public $params;

	public $asset_id;

	public $checked_out;

	public $checked_out_time;

	public $created;

	public $created_by;

	public $modified;

	public $modified_by;

	/*joins*/
	public $editor;

	/*vminfo*/
	public $klantnummer;

	public $user_naam;

	public $user_company;

	/*statics*/
	protected static $_jsonVars = array('bestanden', 'params');

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

	/**
	 * @param array $formData
	 * @param array $messages
	 * @return bool
	 */
	public function save ($formData, &$messages = array()) {
		//init vars
		$model = BixTools::getModel('massa');
		// Validate the posted data.
		// Sometimes the form needs some posted data, such as for plugins and modules.
		$form = $model->getForm($formData, false);

		if (!$form) {
			$messages['error'][] = $model->getError();
			return false;
		}

		// Test whether the data is valid.
		$validData = $model->validate($form, $formData);

		// Check for validation errors.
		if ($validData === false) {
			// Get the validation messages.
			$errors = $model->getErrors();
			// Push up to five validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 5; $i++) {
				if ($errors[$i] instanceof Exception) {
					$messages['warning'][] = $errors[$i]->getMessage();
				} else {
					$messages['warning'][] = $errors[$i];
				}
			}
			return false;
		}

		// Attempt to save the data.
		if (!$model->save($validData)) {
			$messages['error'][] = JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError());
			return false;
		}

		$validData['id'] = $model->getState($model->getName() . '.id');
		$this->setProperties($validData);
		return true;
	}
}