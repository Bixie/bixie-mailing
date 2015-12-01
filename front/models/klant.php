<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.model');


/**
 * Class BixmailingModelKlant
 */
class BixmailingModelKlant extends JModelLegacy {

	/**
	 * @param JUser  $user
	 * @param string $subject
	 * @param        $tekst
	 * @return array
	 */
	public function prepareMail ($user, $subject, $tekst) {
		$profile = BixmailingHelper::getUserProfile($user->id);
		$maildata = BixHelper::getMailBase($user->email);
		$onderwerp = $subject;
		$fields = array_keys($profile->toArray());
		foreach ($fields as $key) {
			switch ($key) {
				case 'modified':
					$format = JHtml::_('date', $profile->get($key, ''), 'd-m-Y H:i:s');
					$onderwerp = str_replace('{user_' . $key . '}', $format, $onderwerp);
					$tekst = str_replace('{user_' . $key . '}', $format, $tekst);
					break;
				default:
					$onderwerp = str_replace('{user_' . $key . '}', $profile->get($key, ''), $onderwerp);
					$tekst = str_replace('{user_' . $key . '}', $profile->get($key, ''), $tekst);
					break;
			}
		}
		foreach (array('name', 'username', 'email') as $key) {
			$onderwerp = str_replace('{' . $key . '}', $user->$key, $onderwerp);
			$tekst = str_replace('{' . $key . '}', $user->$key, $tekst);
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
	 * @param $maildata
	 * @return bool|JTable
	 */
	public function createMaillog ($event, $user_id, $maildata) {
		$logTable = BixTools::getModel('maillog')->getTable();
		$logTable->reset();
		$logTable->id = 0;
		$logTable->user_id = $user_id;
		$logTable->mailing_id = 0;
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

}

