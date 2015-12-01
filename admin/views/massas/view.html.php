<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');
// require_once BIX_PATH_ADMIN_CLASS . '/bixprijs.php';

/**
 * View class for a list of Bixmailing.
 */
class BixmailingViewMassas extends JViewLegacy {
	protected $items;
	protected $pagination;
	protected $state;

	/**
	 * Display the view
	 */
	public function display ($tpl = null) {
		BixTools::loadCSS();
		BixTools::markdown();
		BixTools::assetJs('com_bixmailing.admin');

		$this->state = $this->get('State');
		$this->items = $this->get('Items');
		$this->pagination = $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		$this->mailTemplatesOptions = array(JHtml::_('select.option', '', JText::_('COM_BIXMAILING_MAAK_KEUZE')));
		foreach (BixmailingHelper::getMailEvents() as $eventNaam => $eventTitle) {
			$this->mailTemplatesOptions[] = JHtml::_('select.option', $eventNaam, $eventTitle);
		}
		$templatesModel = BixTools::getModel('templates');
		$this->mailTemplates = $templatesModel->getItems();
		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 * @since    1.6      - ORIG
	 */
	protected function addToolbar () {
		$canDo = BixmailingHelper::getActions();

		JToolBarHelper::title('<span class="color">' . JText::_('COM_BIXMAILING') . '</span> :: ' . JText::_('COM_BIXMAILING_MASSAS'), 'massa');
		JFactory::getDocument()->setTitle(JText::_('COM_BIXMAILING') . ':: ' . JText::_('COM_BIXMAILING_MASSAS'));

		//Check if the form exists before showing the add/edit buttons
		$formPath = JPATH_COMPONENT_ADMINISTRATOR . '/views/massa';
		if (file_exists($formPath)) {

			if ($canDo->get('core.create')) {
				JToolBarHelper::addNew('massa.add', 'JTOOLBAR_NEW');
			}

			if ($canDo->get('core.edit')) {
				JToolBarHelper::editList('massa.edit', 'JTOOLBAR_EDIT');
			}

		}

		if ($canDo->get('core.edit.state')) {

			if (isset($this->items[0]->state)) {
				JToolBarHelper::divider();
				JToolBarHelper::custom('massas.publish', 'publish.png', 'publish_f2.png', 'JTOOLBAR_PUBLISH', true);
				JToolBarHelper::custom('massas.unpublish', 'unpublish.png', 'unpublish_f2.png', 'JTOOLBAR_UNPUBLISH', true);
			} else {
				//If this component does not use state then show a direct delete button as we can not trash
				JToolBarHelper::deleteList('', 'massas.delete', 'JTOOLBAR_DELETE');
			}

			if (isset($this->items[0]->state)) {
				JToolBarHelper::divider();
				JToolBarHelper::archiveList('massas.archive', 'JTOOLBAR_ARCHIVE');
			}
			if (isset($this->items[0]->checked_out)) {
				JToolBarHelper::custom('massas.checkin', 'checkin.png', 'checkin_f2.png', 'JTOOLBAR_CHECKIN', true);
			}
		}

		//Show trash and delete for components that uses the state field
		if (isset($this->items[0]->state)) {
			if ($this->state->get('filter.state') == -2 && $canDo->get('core.delete')) {
				JToolBarHelper::deleteList('', 'massas.delete', 'JTOOLBAR_EMPTY_TRASH');
				JToolBarHelper::divider();
			} else if ($canDo->get('core.edit.state')) {
				JToolBarHelper::trash('massas.trash', 'JTOOLBAR_TRASH');
				JToolBarHelper::divider();
			}
		}

		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_bixmailing');
		}
	}
}
