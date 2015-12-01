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
class BixmailingViewKlant extends JViewLegacy {
	protected $state;
	protected $params;
	protected $canDo;

	protected $klant;
	protected $klantProfile;

	protected $user;
	protected $userProfile;
	protected $access;


	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a JError object.
	 *
	 * @see     fetch()
	 * @since   11.1
	 */
	function display ($tpl = null) {
		$app = JFactory::getApplication();
		$this->params = $app->getParams();
		// Get some data from the models
		$this->user = JFactory::getUser();
		$this->userProfile = BixmailingHelper::getUserProfile($this->user->id);
		$this->canDo = BixmailingHelper::getActions();

// pr($this->mailings);
		$this->page_heading = $this->params->get('page_heading', JText::_('COM_BIXMAILING_KLANT_PAGEHEADER_PAGEHEADER'));
		$this->access = $this->canDo->get('core.admin');
		if ($this->_layout == 'confirm') {
			$user_id = $app->input->getInt('id', 0);
			$this->klant = JFactory::getUser($user_id);
			$this->klantProfile = BixmailingHelper::getUserProfile($user_id);

		}

		parent::display($tpl);

	}

}