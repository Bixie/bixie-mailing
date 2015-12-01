<?php
/**
 *	com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *	Bixie.nl
 *
 */


// no direct access
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_bixmailing')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}
include_once(JPATH_COMPONENT_ADMINISTRATOR . '/classes/bixtools.php');

// Include dependancies
jimport('joomla.application.component.controller');
//controllers forcen
$app = JFactory::getApplication();
$view = $app->input->getCmd('view', 'bixmailing');
$command = $app->input->getCmd('task', 'display');

$controller	= JControllerLegacy::getInstance('Bixmailing');
$controller->execute($app->input->getCmd('task', ''));
$controller->redirect();
