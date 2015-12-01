<?php
/**
 *	com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *	Bixie.nl
 *
 */


// No direct access
defined('_JEXEC') or die;

/**
 * Class BixmailingController
 */
class BixmailingController extends JControllerLegacy
{
    function __construct() {
		parent::__construct();
    }

	/**
	 * Method to display a view.
	 * @param    boolean $cachable  If true, the view output will be cached
	 * @param array|bool $urlparams An array of safe url parameters and their variable types, for valid values see {@link JFilterInput::clean()}
	 *                              .
	 * @return    JControllerLegacy        This object to support chaining.
	 * @since    1.5
	 */
	public function display($cachable = false, $urlparams = false) {
		require_once JPATH_COMPONENT.'/helpers/bixmailing.php';

		// Load the submenu.
		BixmailingHelper::addSubmenu(JFactory::getApplication()->input->getCmd('view', 'bixmailing'));
		$app = JFactory::getApplication();
		$view = $app->input->getCmd('view', 'bixmailing');
		$app->input->set('view', $view);

		parent::display();

		return $this;
	}
}
