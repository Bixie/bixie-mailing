<?php
/**
 *	com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *	Bixie.nl
 *
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * View to edit
 */
class BixmailingViewTemplate extends JViewLegacy
{
	protected $state;
	protected $item;
	protected $form;

	/**
	 * Display the view
	 */
	public function display($tpl = null) {
		$this->state	= $this->get('State');
		$this->item		= $this->get('Item');
		$this->form		= $this->get('Form');
		$this->model	= $this->getModel();

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		
	// echo $this->item->id;
		//js
		BixTools::loadCSS();
		// BixTools::assetJs('bixmailing');
		

		$this->canDo		= BixmailingHelper::getActions($this->item->id);
		$this->addToolbar();
		parent::display($tpl);
	}

	/**
	 * Add the page title and toolbar.
	 */
	protected function addToolbar() {
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$user		= JFactory::getUser();
		$isNew		= ($this->item->id == 0);
        if (isset($this->item->checked_out)) {
		    $checkedOut	= !($this->item->checked_out == 0 || $this->item->checked_out == $user->get('id'));
        } else {
            $checkedOut = false;
        }
		$title = $isNew? ' - <small>['.JText::_('BIX_NEW').']</small>': ' - '.$this->item->onderwerp.' <small>['.JText::_('BIX_EDIT').']</small>';
		JToolBarHelper::title('<span class="color">'.JText::_('COM_BIXMAILING') . '</span> :: ' .JText::_('COM_BIXMAILING_TEMPLATE').$title, 'template');
		$this->document->setTitle(JText::_('COM_BIXMAILING') . ':: ' .$this->item->onderwerp);
		// If not checked out, can save the item.
		if (!$checkedOut && ($this->canDo->get('core.edit')||($this->canDo->get('core.create')))) {
			JToolBarHelper::apply('template.apply', 'JTOOLBAR_APPLY');
			JToolBarHelper::save('template.save', 'JTOOLBAR_SAVE');
		}
		if (!$checkedOut && ($this->canDo->get('core.create'))){
			// JToolBarHelper::custom('template.save2new', 'save-new.png', 'save-new_f2.png', 'JTOOLBAR_SAVE_AND_NEW', false);
		}
		// If an existing item, can save to a copy.
		if (!$isNew && $this->canDo->get('core.create') ) {
			// JToolBarHelper::custom('template.save2copy', 'save-copy.png', 'save-copy_f2.png', 'JTOOLBAR_SAVE_AS_COPY', false);
		}
		if (empty($this->item->id)) {
			JToolBarHelper::cancel('template.cancel', 'JTOOLBAR_CANCEL');
		} else {
			JToolBarHelper::cancel('template.cancel', 'JTOOLBAR_CLOSE');
		}

	}
}
