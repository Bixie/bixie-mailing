<?php
/**
 *	com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *	Bixie.nl
 *
 */
 
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');

class com_bixmailingInstallerScript
{
	protected $_messages = array();
	protected $_error = array();
	protected $_src;
	protected $_folders = array('/libraries/tcpdf','/uploads','/uploadparts','/thumbs');

	/**
	 * Called before any type of action
	 *
	 * @param   string  $type  Which action is happening (install|uninstall|discover_install)
	 * @param   object  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function preflight($type, $parent)
	{
		// init vars
		$type = strtolower($type);
		$this->_src = $parent->getParent()->getPath('source'); // tmp folder
		
	}

	/**
	 * Called on installation
	 *
	 * @param   object  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function install($parent)
	{
		//create folders
		$this->_checkFolders();
		//default content
		if (isset($parent->getParent()->getManifest()->defcontent->sql))
		{
			$utfresult = $parent->getParent()->parseSQLFiles($parent->getParent()->getManifest()->defcontent->sql);

			if ($utfresult === false)
			{
				// Install failed, rollback changes
				$parent->getParent()->abort(JText::sprintf('JLIB_INSTALLER_ABORT_COMP_INSTALL_SQL_ERROR', $db->stderr(true)));

				return false;
			}
		}
	}

	/**
	 * Called on update
	 *
	 * @param   object  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function update($parent)
	{
		//create folders
		$this->_checkFolders();
		
// echo '<pre>upd';
// print_r($parent);		
// echo '</pre>';		

//echo $this['kannie'];		
	}

	/**
	 * Called on uninstallation
	 *
	 * @param   object  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function uninstall($parent)
	{
		// delete files
		foreach ($this->_folders as $folder) {
			if (JFolder::exists(JPATH_ROOT.$folder)) {
				if (JFolder::delete(JPATH_ROOT.$folder)) {
					$this->_messages[] = '<li class="info">'.JText::sprintf('COM_BIXMAILING_INSTALL_FOLDER_DELETED_SPR',$folder).'</li>';
				} else {
					$this->_messages[] = '<li class="error">'.JText::sprintf('COM_BIXMAILING_INSTALL_FOLDER_DELETE_ERROR_SPR',$folder).'</li>';
				}
			}
		}
		$this->_showMessages();

	}

	/**
	 * Called after install
	 *
	 * @param   string  $type  Which action is happening (install|uninstall|discover_install)
	 * @param   object  $parent  The object responsible for running this script
	 *
	 * @return  boolean  True on success
	 */
	public function postflight($type, $parent)
	{
		echo '<p><strong>'.JText::_('COM_BIXMAILING_INSTALL_MESSAGE').'</strong></p>';
		$this->_showMessages();
		echo '<p>'.JText::_('COM_BIXMAILING_INSTALL_COMPLETE').'</p>';

	}
	
	protected function _checkFolders() {
		// copy files
		foreach ($this->_folders as $folder) {
			if (JFolder::exists($this->_src.$folder)) {
				if (!JFolder::exists(JPATH_ROOT.$folder)) {
					if (JFolder::copy($this->_src.$folder,JPATH_ROOT.$folder)) {
						$this->_messages[] = '<li class="info">'.JText::sprintf('COM_BIXMAILING_INSTALL_FOLDER_CREATED_SPR',$folder).'</li>';
					} else {
						$this->_messages[] = '<li class="error">'.JText::sprintf('COM_BIXMAILING_INSTALL_FOLDER_CREATE_ERROR_SPR',$folder).'</li>';
					}
				} else {
					$this->_messages[] = '<li class="info">'.JText::sprintf('COM_BIXMAILING_INSTALL_FOLDER_CREATE_EXIST_SPR',$folder).'</li>';
				}
			}
		}
	}
	
	protected function _showMessages() {
		if (count($this->_messages)) {
			echo '<style>
			ul.mess {list-style:none outside none;}
			ul.mess li.error {color:red;}
			ul.mess li.info {color:#0055BB;}
			</style>';
			echo '<ul class="mess">'.implode($this->_messages).'</ul>';
		}
	}
}