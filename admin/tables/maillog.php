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
 * drukkerij Table class
 */
class BixmailingTablemaillog extends JTable
{
    //   - ORIG
	protected $_jsonVars = array();
	
	/**
	 * Constructor
	 *
	 * @param JDatabase A database connector object
	 */
	public function __construct(&$db)
	{
		parent::__construct('#__bm_maillog', 'id', $db);
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 *
	 * @param	array		Named array
	 * @return	null|string	null is operation was satisfactory, otherwise returns an error
	 * @see		JTable:bind
	 * @since	1.5
	 */
	public function bind($array, $ignore = '')
	{
		if (count($this->_jsonVars)) {
			foreach ($this->_jsonVars as $jsonVar) {
				if (isset($array[$jsonVar]) && is_array($array[$jsonVar])) {
					$registry = new JRegistry;
					$registry->loadArray($array[$jsonVar]);
					$array[$jsonVar] = (string)$registry;
				}
			}
		}
		return parent::bind($array, $ignore);
	}

	/**
	 * Overrides JTable::store to set modified data and user id.
	 *
	 * @param   boolean  $updateNulls  True to update fields even if they are null.
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   11.1      - ORIG
	 */
	public function store($updateNulls = false)
	{
        $k = $this->_tbl_key;
		if (!$this->$k) {
			$date = JFactory::getDate();
			$user = JFactory::getUser();
			$this->created = $date->toSql();
			$this->created_by = $user->get('id');
		}
		return parent::store($updateNulls);
	}

}
