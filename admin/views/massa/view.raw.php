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
 * View to edit
 */
class BixmailingViewMassa extends JViewLegacy {
	protected $state;
	protected $item;
	protected $form;

	/**
	 * Display the view
	 */
	public function display ($tpl = null) {
		$this->state = $this->get('State');
		$this->item = $this->get('Item');
		$this->form = $this->get('Form');
		$this->model = $this->getModel();

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		$input = JFactory::getApplication()->input;
		$tpl = $input->post->get('tpl', '', 'word');

		$this->mailLogs = $this->get('MailLogs');

		$this->mailTemplatesOptions = array(JHtml::_('select.option', '', JText::_('COM_BIXMAILING_MAAK_KEUZE')));
		foreach (BixmailingHelper::getMailEvents() as $eventNaam => $eventTitle) {
			$this->mailTemplatesOptions[] = JHtml::_('select.option', $eventNaam, $eventTitle);
		}
		$templatesModel = BixTools::getModel('templates');
		$this->mailTemplates = $templatesModel->getItems();

		parent::display($tpl);
	}

}
