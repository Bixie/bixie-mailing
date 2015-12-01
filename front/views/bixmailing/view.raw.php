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
class BixmailingViewBixmailing extends JViewLegacy {

	protected $model;
	protected $user;
	protected $userProfile;

	protected $mailings;
	protected $massas;

	/**
	 * Execute and display a template script.
	 * @param   string $tpl The name of the template file to parse; automatically searches through the template paths.
	 * @return  mixed  A string if successful, otherwise a JError object.
	 * @see     fetch()
	 * @since   11.1
	 */
	function display ($tpl = null) {
		$app = JFactory::getApplication();
		$this->params = $app->getParams();

		// Get some data from the models
		$this->model = $this->getModel();
		$this->user = JFactory::getUser();
		$this->userProfile = BixmailingHelper::getUserProfile($this->user->id);

		$tpl = $app->input->getCmd('part', null);
		if ($tpl == 'verzendingen') {
			$this->mailings = $this->get('MailingsByUser');
		}
		if ($tpl == 'massas') {
			$this->massas = $this->get('MassasByUser');
		}

		parent::display($tpl);
	}

}