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
 * BixmailingViewAccount
 */
class BixmailingViewAccount extends JViewLegacy {
	protected $params;
	protected $canDo;
	protected $userProfile;

	protected $user;
	protected $activatieData;

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
		$this->user = JFactory::getUser();
		$this->userProfile = BixmailingHelper::getUserProfile($this->user->id);

		//key opzoeken
		$this->activatieData = $this->get('ActivationStatus');

		//doc opmaken
		BixTools::loadCSS();
		BixTools::uikit();
		BixTools::assetJs('com_bixmailing.front');
		$this->canDo = BixmailingHelper::getActions();
		$this->page_heading = $this->params->get('page_heading', JText::_('COM_BIXMAILING_ACCOUNT_PAGEHEADER'));

		//site title
		$title = $this->page_heading;
		if (empty($title)) {
			$title = $app->getCfg('sitename');
		} elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		} elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		$this->document->setTitle($title);


		parent::display($tpl);

	}

}