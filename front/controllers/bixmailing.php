<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.controller');

/**
 * Class BixmailingControllerBixmailing
 */
class BixmailingControllerBixmailing extends JControllerLegacy {

	/**
	 * sla massamail aanvraag op
	 * @return void
	 */
	public function saveMassa () {
		// JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

	}
}