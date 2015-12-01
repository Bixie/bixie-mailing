<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */


// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.controlleradmin');

/**
 * Machines list controller class.
 */
class BixmailingControllerMassas extends JControllerAdmin {
	/**
	 * Proxy for getModel.
	 * @since    1.6
	 */
	public function &getModel ($name = 'massa', $prefix = 'BixmailingModel') {
		$model = parent::getModel($name, $prefix, array('ignore_request' => true));
		return $model;
	}
}

