<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */

// No direct access
defined('_JEXEC') or die;
jimport('joomla.application.component.controllerform');

/**
 * Machine controller class.
 */
class BixmailingControllerMassa extends JControllerForm {

	function __construct () {
		$this->view_list = 'massas';
		parent::__construct();
	}

	/**
	 * Proxy for getModel.
	 * @since    1.6
	 */
	public function &getModel ($name = 'massa', $prefix = 'BixmailingModel', $config = array()) {
		$pset = parent::getModel($name, $prefix, $config);
		return $pset;
	}

	/**
	 * Method to run batch operations.
	 * @param   string $model The model
	 * @return    boolean  True on success.
	 * @since    2.5
	 */
	public function batch ($model = null) {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Set the model
		$model = $this->getModel('Massa');

		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_bixmailing&view=massas' . $this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}

}