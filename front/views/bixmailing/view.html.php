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

	function display ($tpl = null) {
		$app = JFactory::getApplication();
		$this->params = $app->getParams();
		// Get some data from the models
		$this->model = $this->getModel();
		$this->user = JFactory::getUser();
		$this->userProfile = BixmailingHelper::getUserProfile($this->user->id);

		$this->mailings = $this->get('MailingsByUser');
		$this->massas = $this->get('MassasByUser');
// pr($this->mailings);
		//doc opmaken
		BixTools::loadCSS();
		BixTools::uikit();
		BixTools::assetJs('com_bixmailing.front');
		$this->page_heading = $this->params->get('page_heading', JText::_('COM_BIXMAILING_DASHBOARD_PAGEHEADER'));
		//pathway
		// $pathway	= $app->getPathway();
		// $pathway->addItem($categorie, false);

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