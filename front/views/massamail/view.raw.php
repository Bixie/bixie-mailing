<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the Bixmailing component
 */
class BixmailingViewMassamail extends JViewLegacy {
	protected $state;
	protected $userFront;
	protected $params;
	protected $canDo;

	protected $model;
	protected $item;
	protected $user;
	protected $userProfile;
	protected $access;

	function display ($tpl = null) {
		$app = JFactory::getApplication();
		$this->params = $app->getParams();
		// Get some data from the models
		$this->model = BixTools::getModel('massa');
		$this->user = JFactory::getUser();
		$this->userProfile = BixmailingHelper::getUserProfile($this->user->id);
		$this->canDo = BixmailingHelper::getActions();

// pr($this->mailings);
		$this->page_heading = $this->params->get('page_heading', JText::_('COM_BIXMAILING_MASSAMAIL_PAGEHEADER'));
		$this->access = 1;
		if ($this->_layout == 'confirm') {
			$massa_id = $app->input->getInt('id', 0);
			$this->item = $this->model->getItem($massa_id);
			$this->access = $this->canDo->get('core.edit.own');

		}

		parent::display($tpl);

	}

}