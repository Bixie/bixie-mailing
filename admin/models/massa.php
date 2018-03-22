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
class BixmailingModelmassa extends JModelAdmin {
	/**
	 * @var        string    The prefix to use with controller messages.
	 * @since    1.6
	 */
	protected $text_prefix = 'com_bixmailing';

	/**
	 * @var string
	 */
	protected static $_itemClass = 'Massa';

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
	 * @param string $type   The $type
	 * @param string $prefix A prefix for the table class name. Optional.
	 * @param array  $config Configuration array for model. Optional.
	 * @internal param \The $type table type to instantiate
	 * @return    JTable    A database object
	 * @since    1.6
	 */
	public function getTable ($type = 'Massa', $prefix = 'BixmailingTable', $config = array()) {
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
		$form = $this->loadForm('com_bixmailing.massa', 'massa', array('control' => 'jform', 'load_data' => $loadData));
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
		$data = JFactory::getApplication()->getUserState('com_bixmailing.edit.massa.data', array());

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
					->where("ml.massa_id = $item->id")
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
//		$user = JFactory::getUser();

		return parent::canEditState($record, 'com_bixmailing');
	}


	/**
	 * @param null|integer $pk
	 * @return bool|array
	 */
	public function sendConfirmMail ($pk = null) {
		if ($item = $this->getItem($pk)) {
			$this->_db->setQuery($this->_db->getQuery(true)
					->select("onderwerp, content")
					->from("#__bm_template")
					->where("type = 'MASSAMAIL' AND state = 1")
				, 0, 1);
			$template = $this->_db->loadObject();
			return $this->batchMail('MASSAMAIL', $template->onderwerp, $template->content, array($item->id));
		}
		return false;

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
	 * @param  string $event
	 * @param  string $subject
	 * @param  string $template
	 * @param   array $pks An array of row IDs.
	 * @internal param int $value The new category.
	 * @return  mixed  An array of new IDs on success, boolean false on failure.
	 * @since    2.5
	 */
	protected function batchMail ($event, $subject, $template, $pks) {

		$massaTable = $this->getTable('massa');
		$logTable = $this->getTable('maillog');
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

			$logTable->reset();
			$massaTable->reset();

			// Check that the row actually exists
			if (!$massaTable->load($pk)) {
				if ($error = $massaTable->getError()) {
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
			$maildata = $this->prepareMail($massaTable, $subject, $template);
			$logTable->id = 0;
			$logTable->user_id = $massaTable->user_id;
			$logTable->massa_id = $pk;
			$logTable->event = $event;
			$logTable->ontvangers = $maildata['email'] . ',' . implode(',', $maildata['cc']);
			$logTable->onderwerp = $maildata['onderwerp'];
			$logTable->tekst = $maildata['tekst'];

			//send mail
			if (!BixHelper::sendMail($maildata)) {
				$this->setError(JText::_('COM_BIXMAILING_MAILING_FOUT_BIJ_MAILEN'));
				return false;
			}

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

			// Get the new item ID
			$newId = $logTable->get('id');

			// Add the new ID to the array
			$newIds[$i] = $newId;
			$i++;
		}

		// Clean the cache
		$this->cleanCache();

		return $newIds;
	}

	//voorbereiden mail
	/**
	 * @param JTable $massaTable
	 * @param string $subject
	 * @param string $body
	 * @return array
	 */
	public function prepareMail ($massaTable, $subject, $body) {
		$config = JFactory::getConfig();
		$user = JFactory::getUser($massaTable->user_id);
		$maildata = array();
		$maildata['fromname'] = $config->get('fromname');
		$maildata['mailfrom'] = $config->get('mailfrom');
		$maildata['sitename'] = $config->get('sitename');
		$maildata['siteurl'] = JUri::root();
		$maildata['email'] = $user->email;
		$maildata['cc'] = [];
		$maildata['bcc'] = BixmailingHelper::getModeratorEmails();
		$onderwerp = $subject;
		$tekst = $body;
		$fields = array_keys($massaTable->getFields());
		foreach ($fields as $name) {
			switch ($name) {
				case 'bestanden':
					$bestandsnamen = array();
					$registry = new JRegistry;
					$registry->loadString($massaTable->bestanden);
					$bestanden = $registry->toArray();
					foreach ($bestanden as $hash) {
						$bestandsnamen[] = '<a href="' . JURI::root() . '/download?h=' . $hash . '" download>' . BixHelper::fileInfo($hash)->fileName . '</a>';
					}
					$format = implode('<br/>', $bestandsnamen);
					break;
				case 'type':
					$format = JText::_('BIX_MAILING_TYPE_' . $massaTable->type);
					break;
				case 'aang':
					$format = $massaTable->aang ? JText::_('JYES') : JText::_('JNO');
					break;
				case 'modified':
				case 'created':
					$format = JHtml::_('date', $massaTable->$name, 'm-d-Y H:i:s');
					break;
				default:
					$format = $massaTable->$name;
					break;
			}
			$onderwerp = str_replace('{massa_' . $name . '}', $format, $onderwerp);
			$tekst = str_replace('{massa_' . $name . '}', $format, $tekst);
		}
		$tekst = str_replace('{aanhef}', BixmailingHelper::getAanhef($user->id), $tekst);

		$maildata['onderwerp'] = $onderwerp;
		$maildata['tekst'] = $tekst;

		return $maildata;
	}


	//render mailingnaam obv gegevens
	/**
	 * @param  integer $mailing_id
	 * @param  integer $user_id
	 * @param  string  $type
	 * @param array    $messages
	 * @return string
	 */
	public function renderMassaNaam ($mailing_id, $user_id, $type, &$messages = array()) {
		if (empty($messages)) $messages = array('success' => array(), 'warning' => array(), 'danger' => array(), 'info' => array());
		if (!$user_id) {
			$messages['warning'][] = 'Klant niet geselecteerd';
			return '';
		}
		if (!$type) {
			$messages['warning'][] = 'Type niet geselecteerd';
			return '';
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
				$existing = $this->existingMassaNaam($mailing_id, $mailingNaam);
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
	 * @param $massa_id
	 * @param $massaNaam
	 * @return mixed
	 */
	public function existingMassaNaam ($massa_id, $massaNaam) {
		$this->_db->setQuery($this->_db->getQuery(true)->select("naam")->from("#__bm_massa")->where("naam LIKE '$massaNaam%' AND id NOT IN ($massa_id)")->order("naam DESC"), 0, 1);
		return $this->_db->loadResult();
	}

}