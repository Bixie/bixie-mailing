<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */

// No direct access
defined('_JEXEC') or die;

define('ADMIN_USERGROUPS', '8');

/**
 * Bixmailing helper.
 */
class BixmailingHelper {
	/**
	 * @var array
	 */
	protected static $_cache = array();

	/**
	 * Configure the Linkbar.
	 */
	public static function addSubmenu ($vName = '') {
		$aShowViews = array(
			'bixmailing', 'mailings', 'massas', 'templates'
		);
		if (!in_array($vName, $aShowViews)) return;
		JSubMenuHelper::addEntry(
			JText::_('COM_BIXMAILING_TITLE_DASHBOARD'),
			'index.php?option=com_bixmailing&view=bixmailing',
			$vName == 'bixmailing'
		);

		JSubMenuHelper::addEntry(
			JText::_('COM_BIXMAILING_MAILINGS'),
			'index.php?option=com_bixmailing&view=mailings',
			$vName == 'mailings'
		);

		JSubMenuHelper::addEntry(
			JText::_('COM_BIXMAILING_MASSAS'),
			'index.php?option=com_bixmailing&view=massas',
			$vName == 'massas'
		);

		JSubMenuHelper::addEntry(
			JText::_('COM_BIXMAILING_TEMPLATES'),
			'index.php?option=com_bixmailing&view=templates',
			$vName == 'templates'
		);
	}

	/**
	 * Gets a list of the actions that can be performed.
	 * @param null $pk
	 * @param null $userID
	 * @return    JObject
	 * @since    1.6
	 */
	public static function getActions ($pk = null, $userID = null) {
		$app = JFactory::getApplication();
		$user = JFactory::getUser($userID);
		$result = new JObject;

		$option = $app->input->getCmd('option', 'com_bixmailing');
		$view = $app->input->getCmd('view', '');
		$assetName = $option;
		if ($pk && in_array($view, array('config', 'drukkerij'))) {
			$assetName .= '.' . $view . '.' . $pk;
		}

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete', 'printshop.price', 'printshop.beheer', 'printshop.admin', 'config.plugin', 'config.betaling', 'config.admin'
		);

		foreach ($actions as $action) {
			$result->set($action, $user->authorise($action, $assetName));
		}
//pr($result,$assetName.$user->name);
		return $result;
	}

	/**
	 * @return mixed
	 */
	public static function getAdminEmails () {
		// getAll SuperAdmin users: thanks ZOOlanders
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('u.email')
			->from('#__users AS u')
			->join('INNER', '#__user_usergroup_map AS ugm ON ugm.user_id=u.id')
			->where(array(
				'ugm.group_id IN(' . ADMIN_USERGROUPS . ')',
				'u.sendEmail=1',
				'u.block=0'
			));

		$db->setQuery($query);
		return $db->loadColumn();
	}

	/*
	*  mailhelpers
	*/
	/**
	 * @return array
	 */
	public static function getMailEvents () {
		return array(
			'BEVESTIG' => JText::_('COM_BIXMAILING_MAIL_BEVESTIG'),
			'TRACKTRACE' => JText::_('COM_BIXMAILING_MAIL_TRACKTRACE'),
			'AFMELDEN' => JText::_('COM_BIXMAILING_MAIL_AFMELDEN')
		);
	}

	/*
	*  adreshelpers
	*/
	/**
	 * @param $postcodeRaw
	 * @return array
	 */
	public static function formatPostcode ($postcodeRaw) {
		$regEx = '/^(?P<num>[0-9]{4}).?(?P<alph>[a-z|A-Z]{2})$/';
		$postcodeArr = array();
		if (preg_match($regEx, trim($postcodeRaw), $match)) {
			$postcodeArr['format'] = $match['num'] . ' ' . strtoupper($match['alph']);
			$postcodeArr['num'] = $match['num'];
			$postcodeArr['alph'] = strtoupper($match['alph']);
			$postcodeArr['raw'] = $postcodeRaw;
		} else {
//print_r_pre($match);
			$postcodeArr['raw'] = $postcodeRaw;
		}
		return $postcodeArr;
	}

	/**
	 * @param $user_id
	 * @return string
	 */
	public static function getAanhef ($user_id) {
		$profile = self::getUserProfile($user_id);
		if ($profile->get('bedrijfsnaam') == $profile->get('achternaam')) {
			return JText::_('COM_BIXMAILING_MAILING_AANHEF_ALGEMEEN');
		} else {
			return JText::sprintf('COM_BIXMAILING_MAILING_AANHEF_' . strtoupper($profile->get('aanhef', 'Dhr')) . '_SPR', $profile->get('achternaam'));
		}
	}

	/**
	 * @param $user_id
	 * @return mixed
	 */
	public static function getKlantnummer ($user_id) {
		return self::getUserProfile($user_id)->get('klantnummer', '');
	}

	/**
	 * @param $user_id
	 * @return mixed
	 * @deprecated
	 */
	public static function getVMuser ($user_id) {
		if (!$user_id) return new JObject;
		if (!isset(self::$_cache['vmuser' . $user_id])) {
			$db = JFactory::getDbo();
			$db->setQuery($db->getQuery(true)
					->select("vmi.*, vmu.*")
					->select("CASE WHEN vmi.middle_name = '' THEN CONCAT(vmi.first_name,' ',vmi.last_name) ELSE CONCAT(vmi.first_name,' ',vmi.middle_name,' ',vmi.last_name) END AS format_name")
					->select("CASE WHEN vmi.middle_name = '' THEN vmi.last_name ELSE CONCAT(vmi.middle_name,' ',vmi.last_name) END AS full_last_name")
					->from("#__virtuemart_vmusers AS vmu")
					->leftjoin("#__virtuemart_userinfos AS vmi ON vmu.virtuemart_user_id = vmi.virtuemart_user_id AND vmi.address_type = 'BT'")
					->where("vmu.virtuemart_user_id = $user_id")
				, 0, 1);
			self::$_cache['vmuser' . $user_id] = $db->loadObject();
		}
		return self::$_cache['vmuser' . $user_id];
	}

	/**
	 * @param $user_id
	 * @return mixed
	 * @deprecated
	 */
	public static function getVMuserShipTo ($user_id) {
		if (!$user_id) return new JObject;
		if (!isset(self::$_cache['vmusershipTo' . $user_id])) {
			$db = JFactory::getDbo();
			$db->setQuery($db->getQuery(true)
					->select("vmi.*, vmu.*")
					->from("#__virtuemart_vmusers AS vmu")
					->innerjoin("#__virtuemart_userinfos AS vmi ON vmu.virtuemart_user_id = vmi.virtuemart_user_id AND vmi.address_type = 'ST'")
					->where("vmu.virtuemart_user_id = $user_id")
				, 0, 1);
			self::$_cache['vmusershipTo' . $user_id] = $db->loadObject();
		}
		return self::$_cache['vmusershipTo' . $user_id];
	}

	/**
	 * @param int $user_id
	 * @param bool $format
	 * @return \Joomla\Registry\Registry
	 */
	public static function getUserProfile ($user_id, $format = false) {
		$profile = new \Joomla\Registry\Registry();
		if (!$user_id) return $profile;
		if (!isset(self::$_cache['profile' . $user_id . $format])) {
			$profileObj = JUserHelper::getProfile($user_id);
			$profile->loadArray($profileObj->profile);
			if ($format) {
				//niet geimporteerd
				$profile->def('website', '');
				$profile->def('mobile', '');
				//import user
				$user = JFactory::getUser($user_id);
				foreach (array('id', 'name', 'username', 'email') as $key) {
					$profile->set($key, $user->$key);
				}
				//acountactivatie
				$activationKey = $profile->def('activationKey', '');
				$notVisited = $user->lastvisitDate == '0000-00-00 00:00:00';
				$profile->set('requireReset', (bool)$user->requireReset);
				$profile->set('isInvited', (bool)$activationKey != '');
				$accountStatus = 'BIX_MAILING_KLANT_ACTIVATE_STATUS_ACTIEF';
				if ($notVisited) {
					if ($activationKey == '') {
						$accountStatus = 'BIX_MAILING_KLANT_ACTIVATE_STATUS_NOT_INVITED';
					} else { //al key gehad
						if ($user->requireReset) {
							$accountStatus = 'BIX_MAILING_KLANT_ACTIVATE_STATUS_RESET_SPR';
						} else {
							$accountStatus = 'BIX_MAILING_KLANT_ACTIVATE_STATUS_INVITED_SPR';
						}
					}
				} else {
					if ($user->requireReset) {
						$accountStatus = 'BIX_MAILING_KLANT_ACTIVATE_STATUS_RESET_SPR';
					}
				}
				$profile->set('accountStatus', JText::sprintf($accountStatus, $user->email));
				//format
				$profile->set('factuurAdres', BixHtml::formatAdres($profile));
				$profile->set('bezoekAdres', BixHtml::formatAdres($profile), 'ST_');
				$volledigeNaam = $profile->get('aanhef', '') . ' ' . $profile->get('voornaam', '') . ' ' . $profile->get('achternaam', '');
				if ($profile->get('achternaam', '') == $profile->get('bedrijfsnaam', '')) {
					$volledigeNaam = JText::_('COM_BIXMAILING_NAAM_ALGEMEEN');
				}
				$profile->set('volledigeNaam', $volledigeNaam);
				$lastvisitDate = $notVisited ? '-' : JHtml::_('date', $user->lastvisitDate, JText::_('COM_BIXMAILING_DATETIME'));
				$profile->set('lastvisitDate', $lastvisitDate);
				$profile->set('mailtype', JText::_('PLG_USER_BIXMAILINGSETTINGS_MAILTYPE_' . strtoupper($profile->get('mailtype', 'nooit'))));
			}
			self::$_cache['profile' . $user_id . $format] = $profile;
		}
		return self::$_cache['profile' . $user_id . $format];
	}


	/**
	 * @param int $user_id
	 * @param \Joomla\Registry\Registry $newProfile
	 * @return bool
	 * @throws BixException
	 */
	public static function saveUserProfile ($user_id, \Joomla\Registry\Registry $newProfile) {
		if (!$user_id) {
			throw new BixException("Geen userid!");
		}
		$profile = static::getUserProfile($user_id);
		$profile->merge($newProfile);
		$profile->set('modified', JFactory::getDate()->toSql());
		$aUpdateData = $profile->toArray();
		$db = JFactory::getDbo();
		$db->setQuery(
			'DELETE FROM #__user_profiles WHERE user_id = ' . $user_id .
			" AND profile_key LIKE 'profile.%'"
		);
		if (!$db->execute()) {
			throw new BixException($db->getErrorMsg());
		}
		$tuples = array();
		$order = 1;
		foreach ($aUpdateData as $k => $v) {
			$tuples[] = '(' . $user_id . ', ' . $db->quote('profile.' . $k) . ', ' . $db->quote(json_encode($v)) . ', ' . $order++ . ')';
		}
		$db->setQuery('INSERT INTO #__user_profiles VALUES ' . implode(', ', $tuples));
		if (!$db->execute()) {
			throw new BixException($db->getErrorMsg());
		}
		return true;
	}

}


