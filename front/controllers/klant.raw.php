<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */

// No direct access.
defined('_JEXEC') or die;



/**
 * Class BixmailingControllerBixmailing
 */
class BixmailingControllerKlant extends JControllerLegacy {

	public function postodecheck () {
		$data = JFactory::getApplication()->input->get('adres',array(),'array');

		$return = BixPostcodecheck::lookup($data['pcode'],$data['huisnr'],$data['toev']);

		print json_encode($return);

	}

	/**
	 * @return bool
	 */
	public function addUser () {
		$return = array(
			'success' => false,
			'result' => array(),
			'messages' => array('success' => array(), 'warning' => array(), 'danger' => array(), 'info' => array())
		);
		if (!JSession::checkToken()) {
			$return['messages']['error'] = JText::_('JINVALID_TOKEN');
			print json_encode($return);
		}
		$user = JFactory::getUser();
		if ($user->authorise('core.admin')) {
			$data = JFactory::getApplication()->input->get('jform',array(),'array');

			$data['id'] = 0;

			try {
				if (!strlen($data['username'])) {
					throw new BixException("Gebruikersnaam verplicht");
				}
				if (!strlen($data['password1'])){
					throw new BixException("Wachtwoord is verplicht!");
				}
				if (!strlen($data['email1'])) {
					throw new BixException("E-mail mag niet leeg zijn");
				}
				if ($data['password1'] != $data['password2']) {
					throw new BixException("Wachtwoorden moeten overeen komen.");
				}
				if ($data['email1'] != $data['email2']) {
					throw new BixException("Mailadressen moeten overeen komen.");
				}
				$data['email'] = $data['email1'];
				$data['password'] = $data['password1'];
				//email oke?
				$db = JFactory::getDbo();
				$db->setQuery(
					$db->getQuery(true)
						->select("id")
						->from("#__users")
						->where("username = '{$data['username']}'")
						->where("email = '{$data['email']}'")
					, 0, 1
				);
				$user_id = $db->loadResult();

				if ($user_id) {
					throw new BixException('Gebruiker '.$data['username'].' met '.$data['email'].' bestaat al!');
				}


				$this->_saveUser($data, $return['messages']);
				$return['success'] = true;
				$return['result'] = $data;
				$return['result']['confirm'] = true;
				$return['messages']['success'][] = 'Gebruiker succesvol aangemaakt!';
			} catch (BixException $e) {
				$return['messages']['danger'][] = $e->getMessage();
				$return['result'] = $data;
			}

		} else {
			$return['messages']['danger'][] = 'Geen adminrechten!';
		}
		print json_encode($return);

	}

	/**
	 * @return bool
	 */
	public function saveUser () {
		$return = array(
			'success' => false,
			'result' => array(),
			'messages' => array('success' => array(), 'warning' => array(), 'danger' => array(), 'info' => array())
		);
		if (!JSession::checkToken()) {
			$return['messages']['error'] = JText::_('JINVALID_TOKEN');
			print json_encode($return);
		}
		$user = JFactory::getUser();
		$data = JFactory::getApplication()->input->get('jform',array(),'array');
		if ($user->authorise('core.admin') || $user->id == $data['id']) {

			try {
				if (!strlen($data['username'])) {
					throw new BixException("Gebruikersnaam verplicht");
				}
				if (!strlen($data['email1'])) {
					throw new BixException("E-mail mag niet leeg zijn");
				}
				if ($data['email1'] != $data['email2']) {
					throw new BixException("Mailadressen moeten overeen komen.");
				}

				$data['email'] = $data['email1'];
				$data['password'] = $data['password1'];

				$this->_saveUser($data, $return['messages']);
				$return['success'] = true;
				$return['result'] = $data;
				$return['result']['confirm'] = false;
				$return['messages']['success'][] = 'Gegevens succesvol opgeslagen!';
			} catch (BixException $e) {
				$return['messages']['danger'][] = $e->getMessage();
				$return['result'] = $data;
			}

		} else {
			$return['messages']['danger'][] = 'Geen adminrechten!';
		}
		print json_encode($return);

	}

	/**
	 * @param array $data
	 * @return bool
	 * @throws BixException
	 */
	protected function _saveUser (&$data) {

		if ($data['id'] > 0) {
			$user = JFactory::getUser($data['id']);
			$user->bind($data);
		} else {
			$user = new JUser();
			$user->id = 0;
			$user->groups = array(JComponentHelper::getParams('com_users')->get('new_usertype', 2));
			$user->bind($data);
		}

		if(!$user->save()) {
			throw new BixException(implode(', ', $user->getErrors()));
		}

		$data['id'] = $user->id;
		return true;
	}

	/**
	 *
	 */
	public function inviteUser () {
		$return = array(
			'success' => false,
			'result' => array('requireForce'=>false),
			'messages' => array('success' => array(), 'warning' => array(), 'danger' => array(), 'info' => array())
		);
		try {
			if (!JFactory::getUser()->authorise('core.admin')) {
				throw new BixException("Geen adminrechten!");
			}

			$app = JFactory::getApplication();
			$params = $app->getParams('com_bixmailing');
			$user_id = $app->input->getInt('user_id', 0);
			$user = JFactory::getUser($user_id);
			if (empty($user->id)) {
				throw new BixException("Geen geldige gebruiker geselecteerd!");
			}
			$profile = BixmailingHelper::getUserProfile($user_id);
			if ($profile->get('activationKey', '') != '' && !$app->input->getInt('force', 0)) {
				//al gedaan
				$return['result']['requireForce'] = true;
				$return['result']['forceText'] = JText::sprintf('BIX_MAILING_KLANT_AL_INVITED_FORCE_SPR', $user->name, $user->email);
				$return['messages']['info'][] = JText::sprintf('BIX_MAILING_KLANT_AL_INVITED_SPR', $user->name, $user->email);
				print json_encode($return);
				return;
			}

			$activationKey = sha1($user->email.'-'.JFactory::getConfig()->get('secret'));
			$profile->set('activationKey', $activationKey);
			$user->set('requireReset', 1);
			if (!$user->save()) {
				throw new BixException($user->getError());
			}
			BixmailingHelper::saveUserProfile($user_id, $profile);

			//mail verzenden
			$klantModel = BixTools::getModel('klant');
			$db = JFactory::getDbo();
			$db->setQuery($db->getQuery(true)
				->select("onderwerp, content")
				->from("#__bm_template")
				->where("type = 'UITNODIGEN' AND state = 1")
				, 0, 1);
			$template = $db->loadObject();

			$activatieLink = JRoute::_('index.php?Itemid=' . $params->get('menuActivatie', 101) . '&k=' . $activationKey);
			$activatieLink = JUri::root() . substr($activatieLink, 1);
			$emaildata = $klantModel->prepareMail($user, $template->onderwerp, $template->content);
			$emaildata['email'] = $user->email;
			$emaildata['tekst'] = str_replace('{activatielink}', $activatieLink, $emaildata['tekst']);

			//stuur mail en log mails/mailings
			if (BixHelper::sendMail($emaildata)) {
				$klantModel->createMaillog('UITNODIGEN', $user->id, $emaildata);
			} else {
				$return['messages']['warning'][] = 'Fout bij verzenden mail ' . $emaildata['email'];
			}


			$return['result']['activatieLink'] = $activatieLink;
			$return['result']['activationKey'] = $activationKey;
			$return['messages']['success'][] = JText::sprintf('BIX_MAILING_KLANT_INVITED_SPR', $user->name, $user->email);
			$return['success'] = true;

		} catch (BixException $e) {
			$return['messages']['danger'][] = $e->getMessage();
		}
		print json_encode($return);

	}

	public function convertUsers () {
		return true;
		$db = JFactory::getDbo();
		$db->setQuery(
			$db->getQuery(true)
				->select("id")
				->from("#__users")
		);
		$userids = $db->loadColumn();

		foreach ($userids as $user_id) {
			try {
				$profile = BixmailingHelper::getUserProfile($user_id);
				if ($profile->exists('ST_bedrijfsnaam')) {
					echo "User $user_id al done<br/>";
					continue;
				}
				$vmUser = BixmailingHelper::getVMuser($user_id);
				$vmUserShipto = BixmailingHelper::getVMuserShipTo($user_id);
				if (!$vmUser) {
					echo "User $user_id geen vm user<br/>";
					continue;
				}
				// Sanitize the date
				$data['profile']['aanhef'] = $vmUser->title;
				$data['profile']['bedrijfsnaam'] = $vmUser->company;
				$data['profile']['voornaam'] = $vmUser->first_name;
				$data['profile']['achternaam'] = $vmUser->last_name;
				$data['profile']['address1'] = $vmUser->address_1;
				$data['profile']['address2'] = $vmUser->address_2;
				$data['profile']['postal_code'] = $vmUser->zip;
				$data['profile']['city'] = $vmUser->city;

				$data['profile']['ST_bedrijfsnaam'] = $vmUserShipto ? $vmUserShipto->company : '';
				$data['profile']['ST_address1'] = $vmUserShipto ? $vmUserShipto->address_1 : '';
				$data['profile']['ST_address2'] = $vmUserShipto ? $vmUserShipto->address_2 : '';
				$data['profile']['ST_postal_code'] = $vmUserShipto ? $vmUserShipto->zip : '';
				$data['profile']['ST_city'] = $vmUserShipto ? $vmUserShipto->city : '';

				$data['profile']['phone'] = $vmUser->phone_1;
				$data['profile']['mobile'] = $vmUser->phone_2;
				$data['profile']['klantnummer'] = $vmUser->klantnummer;
				$data['profile']['mailtype'] = $vmUser->mailtype;

				$db = JFactory::getDbo();
				$query = $db->getQuery(true)
					->delete($db->quoteName('#__user_profiles'))
					->where($db->quoteName('user_id') . ' = ' . (int)$user_id)
					->where($db->quoteName('profile_key') . ' LIKE ' . $db->quote('profile.%'));
				$db->setQuery($query);
//				$db->execute();

				$tuples = array();
				$order = 1;

				foreach ($data['profile'] as $k => $v) {
					$tuples[] = '(' . $user_id . ', ' . $db->quote('profile.' . $k) . ', ' . $db->quote(json_encode($v)) . ', ' . ($order++) . ')';
				}

				$db->setQuery('INSERT INTO #__user_profiles VALUES ' . implode(', ', $tuples));
//				$db->execute();
			} catch (RuntimeException $e) {
				echo "User $user_id error:<br/>";
				echo $e->getMessage();
				echo "<br/>";

				continue;
			}
		}

	}




}