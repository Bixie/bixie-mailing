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
class BixmailingViewUpload extends JViewLegacy {
	protected $state;
	protected $params;

	protected $user;
	protected $userProfile;

	protected $newMailings;

	function display ($tpl = null) {
		$app = JFactory::getApplication();
		$this->params = $app->getParams();
		// Get some data from the models
		$this->state = $this->get('UploadState');
		$this->user = JFactory::getUser();
		$this->userProfile = BixmailingHelper::getUserProfile($this->user->id);

		$this->newMailings = $this->get('NewMailings');

		$tpl = $app->input->getCmd('tpl', null);
		parent::display($tpl);

	}

}