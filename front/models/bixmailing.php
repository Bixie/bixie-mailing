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
 * Class BixmailingModelBixmailing
 */
class BixmailingModelBixmailing extends JModelLegacy {

	public $mailingsState;
	public $massasState;

	/**
	 * @return array
	 */
	public function getMailingsByUser () {
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		if (!$user->id) return array();
		$mailingsModel = BixTools::getModel('mailings', array('ignore_request' => true));
		$this->mailingsState = $mailingsModel->getState(); //init state
		$mailingsModel->setState('list.ordering', 'm.aangemeld');
		$mailingsModel->setState('list.direction', 'DESC');
		if (!$user->authorise('core.admin')) {
			$mailingsModel->setState('filter.user_id', $user->id);
		}
		//filter
		$search = $app->getUserStateFromRequest('com_bixmailing.bixmailing.mailings.search', 'search', '', 'string');
		$mailingsModel->setState('filter.search', $search);
		$mailingsModel->setState('filter.state', 1);
		//paginatie
		$mailingsModel->setState('list.total', $mailingsModel->getTotal());
		$start = $app->input->getInt('limitstart', 0);
		if ($app->input->getString('part', '') == 'verzendingen' && $start) {
			$mailingsModel->setState('list.limit', $app->input->getInt('limit', 5));
			$mailingsModel->setState('list.start', $start);
		} else {
			$mailingsModel->setState('list.limit', 10);
			$mailingsModel->setState('list.start', 0);
		}
		return $mailingsModel->getItems();
	}

	/**
	 * @return array
	 */
	public function getMassasByUser () {
		$user = JFactory::getUser();
		$app = JFactory::getApplication();
		if (!$user->id) return array();
		$massasModel = BixTools::getModel('massas', array('ignore_request' => true));
		$this->massasState = $massasModel->getState(); //init state
		$massasModel->setState('list.ordering', 'm.created');
		$massasModel->setState('list.direction', 'DESC');
		$massasModel->setState('filter.user_id', $user->id);
		$massasModel->setState('list.total', $massasModel->getTotal());
		$start = $app->input->getInt('limitstart', 0);
		if ($app->input->getString('part', '') == 'massas' && $start) {
			$massasModel->setState('list.limit', $app->input->getInt('limit', 5));
			$massasModel->setState('list.start', $start);
		} else {
			$massasModel->setState('list.limit', 10);
			$massasModel->setState('list.start', 0);
		}
		return $massasModel->getItems();
	}

}

