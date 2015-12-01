<?php
/**
 *	com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *	Bixie.nl
 *
 */

// No direct access
defined('_JEXEC') or die;
jimport('joomla.application.component.controllerform');
/**
 * Machine controller class.
 */
class BixmailingControllerTemplate extends JControllerForm
{

    function __construct() {
        $this->view_list = 'templates';
        parent::__construct();
    }
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function &getModel($name = 'template', $prefix = 'BixmailingModel',$config=array())
	{
		$pset = parent::getModel($name, $prefix, $config);
		return $pset;
	}
	
}