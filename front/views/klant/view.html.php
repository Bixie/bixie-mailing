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
	protected $userFront;
	protected $params;
	protected $canDo;

	protected $user;
	protected $model;
	protected $form;
	protected $data;
	protected $userProfile;

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
		//doc opmaken
		BixTools::loadCSS();
		BixTools::uikit();
		BixTools::assetJs('com_bixmailing.front');
		$this->canDo = BixmailingHelper::getActions();

		//get com_users stuff
		require_once JPATH_ROOT. '/components/com_users/helpers/html/users.php';
		JFactory::getLanguage()->load('com_users', JPATH_ROOT, 'nl-NL', false, false);
		JFormHelper::addFormPath(array(JPATH_BASE . '/components/com_users/models/forms'));
		JModelLegacy::addIncludePath(JPATH_ROOT . '/components/com_users/models', 'UsersModel');
		switch ($this->_layout) {
			case 'add':
				$this->page_heading = $this->params->get('page_heading', JText::_('COM_BIXMAILING_ADD_KLANT_PAGEHEADER'));
				$this->model = JModelLegacy::getInstance('registration', 'UsersModel', array('ignore_request' => true));
				$this->form = $this->model->getForm();
				break;
			case 'edit':
				$this->page_heading = $this->params->get('page_heading', JText::_('COM_BIXMAILING_EDIT_KLANT_PAGEHEADER'));
				$this->model = JModelLegacy::getInstance('profile', 'UsersModel', array('ignore_request' => false));
				$this->form = $this->model->getForm();
				break;
			case 'list':
				$this->page_heading = $this->params->get('page_heading', JText::_('COM_BIXMAILING_EDIT_KLANTLIST_PAGEHEADER'));
				break;
			default:
				$this->page_heading = $this->params->get('page_heading', JText::_('COM_BIXMAILING_GEGEVENS_PAGEHEADER'));
				$this->model = JModelLegacy::getInstance('profile', 'UsersModel', array('ignore_request' => false));
				$this->data = $this->model->getData();
				$this->form = $this->model->getForm();
				break;
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