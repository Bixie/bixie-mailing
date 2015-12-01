<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */

// No direct access
defined('_JEXEC') or die;

/**
 * Class BixMailingMailing
 */
class BixMailingMailing extends JObject {

	/*db fields*/
	public $id;

	public $user_id;

	public $type;

	public $naam;

	public $vervoerder;

	public $gewicht;

	public $aangemeld;

	public $aang;

	public $trace_url;

	public $trace_nl;

	public $trace_btl;

	public $trace_gp;

	public $adresnaam;

	public $straat;

	public $huisnummer;

	public $huisnummer_toevoeging;

	public $postcode;

	public $plaats;

	public $land;

	public $referentie;

	public $klantnummer;

	public $importbestand;

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
	public $user_naam;

	public $user_email;

	public $user_company;

	/*statics*/
	protected static $_jsonVars = array('params');

	/**
	 * @param null|JObject $item
	 */
	public function __construct ($item = null) {
		$properties = $item ? $item->getProperties() : null;
		$this->id = (int)$this->id; //avoid null
		parent::__construct($properties);
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
	 * @return int
	 */
	public function findUser () {
		$user_id = 0;
		if (empty($this->referentie)) return $user_id;
		$db = JFactory::getDbo();
		//13 voor klantnummer in referentie geeft alleen aangeteknd aan, en kan gestript worden in de match
		$klantnrRef = (strlen($this->referentie) > 3 && substr($this->referentie, 0, 2) == '13') ? substr($this->referentie, 2) : $this->referentie;
		$db->setQuery(
			$db->getQuery(true)
				->select("user_id")
				->from("#__user_profiles")
				->where("profile_key = 'profile.klantnummer'")
				->where("profile_value = '\"13$klantnrRef\"'")
				->order("user_id ASC")
			, 0, 1
		);
		//geen match op 'kaal' nummer. Alpha formaat proberen, en klantnummer in eerste 3 pos van langere referentie
		$user_id = $db->loadResult();
		if (!$user_id) {
			$db->setQuery(
				$db->getQuery(true)
					->select("user_id")
					->from("#__user_profiles")
					->where("profile_key = 'profile.klantnummer'")
					->where("(".
						"profile_value = '\"$this->referentie\"'". " OR " .
						"profile_value = '\"13" . substr($this->referentie, 0, 3) . "\"'".
					")")
					->order("user_id ASC")
				, 0, 1
			);
			$user_id = $db->loadResult();
		}
		return $user_id ? $user_id : 0;
	}

	/**
	 * @param array|null $formData
	 * @param array      $messages
	 * @return bool
	 */
	public function save ($formData = null, &$messages = array()) {
		if (!$formData) $formData = $this->getProperties();
		//init vars
		$model = BixTools::getModel('mailing');
		// Validate the posted data.
		// Sometimes the form needs some posted data, such as for plugins and modules.
		$form = $model->getForm($formData, false);

		if (!$form) {
			$messages['danger'][] = $model->getError();
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
			$messages['warning'][] = JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError());
			return false;
		}

		$validData['id'] = $model->getState($model->getName() . '.id');
		$this->setProperties($validData);
		return true;
	}

	/**
	 * @return bool
	 */
	public function defaultMail () {
		$mailtype = BixmailingHelper::getUserProfile($this->user_id)->get('mailtype', 'aang');
		$doMail = $mailtype != 'nooit';
		if ($mailtype == 'aang') {
			$doMail = $this->aang;
		}
		return $doMail;
	}

}