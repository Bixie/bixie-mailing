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
class BixmailingControllerMailing extends JControllerForm
{

    function __construct() {
        $this->view_list = 'mailings';
        parent::__construct();
    }
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function &getModel($name = 'mailing', $prefix = 'BixmailingModel',$config=array())
	{
		$pset = parent::getModel($name, $prefix, $config);
		return $pset;
	}
	
	/**
	 * Method to run batch operations.
	 *
	 * @param   string  $model  The model
	 *
	 * @return	boolean  True on success.
	 *
	 * @since	2.5
	 */
	public function batch($model = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Set the model
		$model	= $this->getModel('Mailing');

		// Preset the redirect
//		$this->setRedirect(JRoute::_('index.php?option=com_bixmailing&view=mailings' . $this->getRedirectToListAppend(), false));

		parent::batch($model);

		echo 'asdf';
        $messages = JFactory::getApplication()->getMessageQueue();
        var_dump($_POST);
        var_dump($messages);
        return false;
	}
	
}