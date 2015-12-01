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
 * Class BixmailingModelAccount
 */
class BixmailingModelAccount extends JModelLegacy {
	/**
	 * @return array
	 */
	public function getActivationStatus () {
		$user_id = 0;
		$key = '';
		$statusClass = 'success';
		$statusIcon = 'check';
		try {
			if (JFactory::getUser()->id > 0) {
				throw new BixActivationWarning('ALREADY_LOGGED_IN');
			}
			$key = JFactory::getApplication()->input->getString('k', '');
			$db = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select("user_id")
				->from("#__user_profiles")
				->where("profile_key = 'profile.activationKey'")
				->where("profile_value = '\"$key\"'");
			$db->setQuery($query);
			$user_id = $db->loadResult();
			if (!$user_id) { //panic
				throw new BixActivationException('KEY_NOT_FOUND');
			}
			$user = JFactory::getUser($user_id);
			if ($user->requireReset == 0) { //user heeft wwreset al doorlopen
				throw new BixActivationWarning('PASSWORD_ALREADY_SET');
			}
			//valideer code
			$checkHash = sha1($user->email . '-' . JFactory::getConfig()->get('secret'));
			if ($checkHash !== $key) {
				throw new BixActivationException('KEY_NOT_VALID');
			}
			$status = 'KEY_VALID';

		} catch (BixActivationWarning $e) {
			$status = $e->getMessage();
			$statusClass = 'warning';
			$statusIcon = 'exclamation';
		} catch (BixActivationException $e) {
			$status = $e->getMessage();
			$statusClass = 'danger';
			$statusIcon = 'exclamation-triangle';
		}
		return array(
			'user_id' => $user_id,
			'key' => $key,
			'status' => $status,
			'statusClass' => $statusClass,
			'statusIcon' => $statusIcon
		);
	}
}

class BixActivationWarning extends BixException {}
class BixActivationException extends BixException {}
