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

	public function activate () {
		$accountModel = BixTools::getModel('account');
		$activatieData = $accountModel->getActivationStatus();
		if (!empty($activatieData['status'])) {
			$app = JFactory::getApplication();
			$params = $app->getParams('com_bixmailing');
			switch ($activatieData['status']) {
				case 'KEY_VALID':
					$user = JFactory::getUser($activatieData['user_id']);
					//make credentials
					$credentials = array(
						'username' => $user->username,
						'password' => uniqid('temp')
					);
					$options = array(
						'return' => JRoute::_('index.php?Itemid=' . $params->get('menuProfile', 101)),
						'remember' => 1
					);
					$user->set('password', JUserHelper::hashPassword($credentials['password']));
					if (!$user->save()) {
						$this->setRedirect('/', JText::_('BIX_MAILING_KLANT_ACTIVATE_FATAL_ERROR'), 'error');
						break;
					}
					// Perform the log in.
					if (true === $app->login($credentials, $options)) {
						// Success
						$app->redirect(JRoute::_($options['return'], false));
					} else {
						// Login failed !
						$this->setRedirect('/', JText::_('BIX_MAILING_KLANT_ACTIVATE_FATAL_ERROR'), 'error');
					}

					break;
				default:
					$activatieData['statusClass'] = $activatieData['statusClass'] == 'danger'? 'error' : $activatieData['statusClass'];
					$this->setRedirect('/', JText::_('BIX_MAILING_KLANT_ACTIVATE_' . $activatieData['status']), $activatieData['statusClass']);
					break;
			}
		}
	}


}