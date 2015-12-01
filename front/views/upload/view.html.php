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
	protected $userFront;
	protected $params;

	protected $model;
	protected $user;
	protected $userProfile;
	/**
	 * @var BixMailingMailing[]
	 */
	protected $newMailings;
	protected $openMailings;

	function display ($tpl = null) {
		$app = JFactory::getApplication();
		$this->params = $app->getParams();
		// Get some data from the models
		$this->model = $this->getModel();
		$this->state = $this->get('UploadState');
		$this->user = JFactory::getUser();
		$this->userProfile = BixmailingHelper::getUserProfile($this->user->id);

		$this->newMailings = $this->get('NewMailings');

		$this->openMailings = $this->get('OpenMailings');

		$this->defaultMailEmail = BixTools::config('defaultFileMail', '');
		$this->defaultMailOnderwerp = BixTools::config('defaultFileSubject', '');
		$this->defaultMailTekst = BixTools::config('defaultFileBody', '');
		//doc opmaken
		BixTools::loadCSS();
		BixTools::upload();
		BixTools::assetJs('com_bixmailing.front');
		$this->page_heading = $this->params->get('page_heading', JText::_('COM_BIXMAILING_UPLOAD_PAGEHEADER'));

		//joostpeventie
		if (BixHelper::bugIE()) {
			$app->enqueueMessage('Gebruik aub een moderne browser!','warning');
		}

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