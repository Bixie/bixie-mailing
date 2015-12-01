<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');


/**
 * Bixmailing model.
 */
class BixmailingModelmailing extends JModelAdmin {
	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'com_bixmailing';

	/**
	 * @var array
	 */
	protected $_jsonVars = array();

	/**
	 * @var string
	 */
	protected static $_itemClass = 'Mailing';

	/**
	 * Constructor.
	 * @param    array $config An optional associative array of configuration settings.
	 * @see        JControllerLegacy
	 * @since      1.6
	 */
	public function __construct ($config = array()) {
		parent::__construct($config);
	}


	/**
	 * Returns a reference to the a Table object, always creating it.
	 * @param string $type
	 * @param string $prefix A prefix for the table class name. Optional.
	 * @param array  $config Configuration array for model. Optional.
	 * @internal param \The $type table type to instantiate
	 * @return    JTable    A database object
	 * @since    1.6
	 */
	public function getTable ($type = 'Mailing', $prefix = 'BixmailingTable', $config = array()) {
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 * @param    array   $data     An optional array of data for the form to interogate.
	 * @param    boolean $loadData True if the form is to load its own data (default case), false if not.
	 * @return    JForm    A JForm object on success, false on failure
	 * @since    1.6
	 */
	public function getForm ($data = array(), $loadData = true) {

		// Get the form.
		$form = $this->loadForm('com_bixmailing.mailing', 'mailing', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 * @return    mixed    The data for the form.
	 * @since    1.6
	 */
	protected function loadFormData () {
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_bixmailing.edit.mailing.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 * @param    integer $pk The id of the primary key.
	 * @return    mixed    Object on success, false on failure.
	 * @since    1.6
	 */
	public function getItem ($pk = null) {
		if ($item = parent::getItem($pk)) {
			//Do any procesing on fields here if needed
			$classname = 'BixMailing' . self::$_itemClass;
			$item = new $classname($item);
		}

		return $item;
	}

	/**
	 * @param null $pk
	 * @return array|mixed
	 */
	public function getMailLogs ($pk = null) {
		$mailLogs = array();
		if ($item = self::getItem($pk)) {
			if ($item->id > 0) {
				$query = $this->_db->getQuery(true);
				$query->select('ml.*, u.name')
					->from('#__bm_maillog AS ml')
					->join('INNER', '#__users AS u ON ml.user_id = u.id')
					->where("ml.mailing_id = $item->id")
					->order("ml.created DESC");
				$this->_db->setQuery($query);
				$mailLogs = $this->_db->loadObjectList();
			}
		}
		return $mailLogs;
	}

	/**
	 * Method to test whether a record can have its state edited.
	 * @param    object $record A record object.
	 * @return    boolean    True if allowed to change the state of the record. Defaults to the permission set in the component.
	 * @since    1.6
	 */
	protected function canEditState ($record) {
		if ($record->user_id == 0 && $record->state == 0) return false;
		return parent::canEditState($record);
	}

	/**
	 * Method to perform batch operations on an item or a set of items.
	 * @param   array $commands An array of commands to perform.
	 * @param   array $pks      An array of item ids.
	 * @param   array $contexts An array of item contexts.
	 * @return    boolean     Returns true on success, false on failure.
	 * @since    2.5
	 */
	public function batch ($commands, $pks, $contexts) {
		// Sanitize user ids.
		$pks = array_unique($pks);
		JArrayHelper::toInteger($pks);

		// Remove any values of zero.
		if (array_search(0, $pks, true)) {
			unset($pks[array_search(0, $pks, true)]);
		}

		if (empty($pks)) {
			$this->setError(JText::_('JGLOBAL_NO_ITEM_SELECTED'));
			return false;
		}

		$done = false;
		switch ($commands['task']) {
			case 'mail':
				$event = JArrayHelper::getValue($commands, 'event', '');
				$subject = JArrayHelper::getValue($commands, 'subject', '');
				$template = JArrayHelper::getValue($commands, 'template', '');

				$result = $this->batchMail($event, $subject, $template, $pks);
				if (is_array($result)) {
					//all's fine
					if (count($result) == 1)
						JFactory::getApplication()->enqueueMessage(JText::_('COM_BIXMAILING_MAILING_MAIL_VERZONDEN'), 'success');
					else
						JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_BIXMAILING_MAILING_NR_MAILS_VERZONDEN_SPR', count($result)), 'success');
				} else {
					return false;
				}
				$done = true;
				break;
		}

		if (!$done) {
			$this->setError(JText::_('JLIB_APPLICATION_ERROR_INSUFFICIENT_BATCH_INFORMATION'));
			return false;
		}

		// Clear the cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Batch copy items to a new category or current.
	 * @param         $event
	 * @param         $subject
	 * @param         $template
	 * @param   array $pks An array of row IDs.
	 * @internal param int $value The new category.
	 * @return  mixed  An array of new IDs on success, boolean false on failure.
	 * @since    2.5
	 */
	protected function batchMail ($event, $subject, $template, $pks) {

		$mailingTable = $this->getTable('mailing');
		$i = 0;

		if (empty($event) || empty($subject) || empty($template)) {
			$this->setError(JText::_('COM_BIXMAILING_MAILING_MAILINFO_INCOMPLETE'));
			return false;
		}

		// go
		$newIds = array();
		while (!empty($pks)) {
			// Pop the first ID off the stack
			$pk = array_shift($pks);

			$mailingTable->reset();

			// Check that the row actually exists
			if (!$mailingTable->load($pk)) {
				if ($error = $mailingTable->getError()) {
					// Fatal error
					$this->setError($error);
					return false;
				} else {
					// Not fatal error
					$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			// Alter the title & alias
			$maildata = $this->prepareMail($mailingTable, $subject, $template);

			//send mail
			if (!Bixhelper::sendMail($maildata)) {
				$this->setError(JText::_('COM_BIXMAILING_MAILING_FOUT_BIJ_MAILEN'));
				return false;
			}

			// Add the new ID to the array
			$newIds[$i] = $this->createMaillog($event, $mailingTable->user_id, $pk, $maildata);
			$i++;
		}

		// Clean the cache
		$this->cleanCache();

		return $newIds;
	}

	/**
	 * Batch copy items to a new category or current.
	 * @param         $user_id
	 * @param         $klantnummer
	 * @param   array $pks An array of row IDs.
	 * @param null    $status
	 * @internal param $event
	 * @internal param int $value The new category.
	 * @return  mixed  An array of new IDs on success, boolean false on failure.
	 * @since    2.5
	 */
	public function batchUser ($user_id, $klantnummer, $pks, $status = null) {

		$mailingTable = $this->getTable('mailing');
		$i = 0;

		if (empty($user_id) || empty($klantnummer)) {
			$this->setError(JText::_('COM_BIXMAILING_MAILING_MAILINFO_INCOMPLETE'));
			return false;
		}

		// go
		$newIds = array();
		while (!empty($pks)) {
			// Pop the first ID off the stack
			$pk = array_shift($pks);

			$mailingTable->reset();

			// Check that the row actually exists
			if (!$mailingTable->load($pk)) {
				if ($error = $mailingTable->getError()) {
					// Fatal error
					$this->setError($error);
					return false;
				} else {
					// Not fatal error
					$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $pk));
					continue;
				}
			}

			// Alter the title & alias
			$messages = array();
			$newNaam = $this->renderMailingNaam($mailingTable->id, $user_id, $mailingTable->type, $messages);
			if ($newNaam) {
				$mailingTable->naam = $newNaam;
			} else {
				$this->setError($messages['danger'][0]);
				continue;
			}
			$mailingTable->user_id = $user_id;
			$mailingTable->klantnummer = $klantnummer;
			if ($status) {
				$mailingTable->status = $status;
			}

			// Check the row.
			if (!$mailingTable->check()) {
				$this->setError($mailingTable->getError());
				return false;
			}

			// Store the row.
			if (!$mailingTable->store()) {
				$this->setError($mailingTable->getError());
				return false;
			}

			// Get the new item ID
			$newId = $mailingTable->get('id');

			// Add the new ID to the array
			$newIds[$i] = $newId;
			$i++;
		}

		// Clean the cache
		$this->cleanCache();

		return $newIds;
	}

	/**
	 * @param JTable $mailingTable
	 * @param string $subject
	 * @param string $body
	 * @return array
	 */
	public function prepareMail ($mailingTable, $subject, $body) {
		$user = JFactory::getUser($mailingTable->user_id);
		$maildata = BixHelper::getMailBase($user->email);
		$onderwerp = $subject;
		$tekst = $body;
		$fields = array_keys($mailingTable->getFields());
		foreach ($fields as $name) {
			switch ($name) {
				case 'modified':
				case 'created':
				case 'aangemeld':
					$format = JHtml::_('date', $mailingTable->$name, 'd-m-Y H:i:s');
					$onderwerp = str_replace('{mailing_' . $name . '}', $format, $onderwerp);
					$tekst = str_replace('{mailing_' . $name . '}', $format, $tekst);
					break;
				default:
					$onderwerp = str_replace('{mailing_' . $name . '}', $mailingTable->$name, $onderwerp);
					$tekst = str_replace('{mailing_' . $name . '}', $mailingTable->$name, $tekst);
					break;
			}
		}
		$tekst = str_replace('{aanhef}', BixmailingHelper::getAanhef($user->id), $tekst);

		$maildata['onderwerp'] = $onderwerp;
		$maildata['tekst'] = $tekst;

		// pr($maildata);
		return $maildata;
	}
	//stuur mail naar klant

	/**
	 * @param $event
	 * @param $user_id
	 * @param $mailing_id
	 * @param $maildata
	 * @return bool|JTable
	 */
	public function createMaillog ($event, $user_id, $mailing_id, $maildata) {
		$logTable = $this->getTable('maillog');
		$logTable->reset();
		$logTable->id = 0;
		$logTable->user_id = $user_id;
		$logTable->mailing_id = $mailing_id;
		$logTable->event = $event;
		$logTable->ontvangers = $maildata['email'] . ',' . implode(',', $maildata['cc'] + $maildata['bcc']);
		$logTable->onderwerp = $maildata['onderwerp'];
		$logTable->tekst = $maildata['tekst'];
		// Check the row.
		if (!$logTable->check()) {
			$this->setError($logTable->getError());
			return false;
		}

		// Store the row.
		if (!$logTable->store()) {
			$this->setError($logTable->getError());
			return false;
		}
		return $logTable->id;
	}

	/**
	 * @param array $mailingIDs
	 * @param array $newData
	 * @return bool
	 */
	public function updateMailings ($mailingIDs, $newData) {
		$query = $this->_db->getQuery(true)->update("#__bm_mailing")
			->where("id IN (" . implode(',', $mailingIDs) . ")");
		foreach ($newData as $key => $value) {
			$query->set("$key = '$value'");
		}
		$query->set("modified = '" . JFactory::getDate()->toSql() . "'");
		$query->set("modified_by = '" . JFactory::getUser()->id . "'");
		$this->_db->setQuery($query);
//		echo $query->dump();
		$this->_db->execute();
		if (!$this->_db->getAffectedRows()) {
			$this->setError('Fout bij wijzigen mailing: ' . $this->_db->stderror());
			return false;
		}
		return true;
	}

	//render mailingnaam obv gegevens
	/**
	 * @param       $mailing_id
	 * @param       $user_id
	 * @param       $type
	 * @param array $messages
	 * @return string
	 */
	public function renderMailingNaam ($mailing_id, $user_id, $type, &$messages = array()) {
		if (empty($messages)) $messages = array('success' => array(), 'warning' => array(), 'danger' => array(), 'info' => array());
		if (!$user_id) {
			$messages['warning'][] = 'Klant niet geselecteerd';
			return;
		}
		if (!$type) {
			$messages['warning'][] = 'Type niet geselecteerd';
			return;
		}
		$mailingNaam = '';
		$suffix = '';
		$user = JFactory::getUser($user_id);
		if ($user->id) {
			$klantNummer = BixmailingHelper::getKlantnummer($user_id);
			if ($klantNummer) {
				$date = JFactory::getDate()->format('Ymd');
				$mailingNaam = strtoupper($klantNummer . '-' . $type . '-' . $date);
				$suffix = '-001';
				$existing = $this->existingMailingNaam($mailing_id, $mailingNaam);
				if ($existing) {
					$knullen = array(0 => '', 1 => '0', 2 => '00');
					$parts = explode('-', $existing);
					$lastSfx = array_pop($parts);
					$nextSfx = intval($lastSfx) + 1;
					$suffix = '-' . $knullen[(3 - strlen($nextSfx))] . $nextSfx;
				}
			} else {
				$messages['danger'][] = 'Klantnummer niet gevonden';
			}
		} else {
			$messages['danger'][] = 'Klant niet gevonden';
		}
		return $mailingNaam . $suffix;
	}

	/**
	 * @param $mailing_id
	 * @param $mailingNaam
	 * @return mixed
	 */
	public function existingMailingNaam ($mailing_id, $mailingNaam) {
		$this->_db->setQuery($this->_db->getQuery(true)->select("naam")->from("#__bm_mailing")->where("naam LIKE '$mailingNaam%' AND id NOT IN ($mailing_id)")->order("naam DESC"), 0, 1);
		return $this->_db->loadResult();
	}

}