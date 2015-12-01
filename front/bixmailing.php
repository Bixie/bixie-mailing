<?php
/**
 *	com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *	Bixie.nl
 *
 */

defined('_JEXEC') or die;

// Include dependancies
jimport('joomla.application.component.controller');
include_once(JPATH_COMPONENT_ADMINISTRATOR . '/classes/bixtools.php');

// Execute the task.
$controller	= JControllerLegacy::getInstance('Bixmailing');
$controller->execute(JFactory::getApplication()->input->getCmd('task',''));
$controller->redirect();
