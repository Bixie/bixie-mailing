<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */

// No direct access
defined('_JEXEC') or die;

/**
 * drukkerij Table class
 */
class BixmailingTablemailing extends JTable {
	//   - ORIG
	protected $_jsonVars = array('params');

	/**
	 * Constructor
	 * @param JDatabase $db
	 * @internal param \A $JDatabase database connector object
	 */
	public function __construct (&$db) {
		parent::__construct('#__bm_mailing', 'id', $db);
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 * @param        array Named  $array
	 * @param string $ignore
	 * @internal   param \Named $array array
	 * @return    null|string    null is operation was satisfactory, otherwise returns an error
	 * @see        JTable:bind
	 * @since      1.5
	 */
	public function bind ($array, $ignore = '') {
		if (count($this->_jsonVars)) {
			foreach ($this->_jsonVars as $jsonVar) {
				if (isset($array[$jsonVar]) && is_array($array[$jsonVar])) {
					$registry = new JRegistry;
					$registry->loadArray($array[$jsonVar]);
					$array[$jsonVar] = (string)$registry;
				}
			}
		}
		// Bind the rules. 
		if (isset($array['rules']) && is_array($array['rules'])) {
			$rules = new JRules($array['rules']);
			$this->setRules($rules);
		}
		return parent::bind($array, $ignore);
	}

	/**
	 * Overloaded check function
	 */
	public function check () {
		$this->_db->setQuery($this->_db->getQuery(true)
            ->select("id")
            ->from("#__bm_mailing")
            ->where("trace_nl = '$this->trace_nl'")
            ->where("aangemeld = '$this->aangemeld'")
        );
		$result = $this->_db->loadResult();
		if ($result && $result != $this->id) {
			$this->setError('Tracecode bestaat al!');
			return false;
		}
		return parent::check();
	}


	/**
	 * Overrides JTable::store to set modified data and user id.
	 * @param   boolean $updateNulls True to update fields even if they are null.
	 * @return  boolean  True on success.
	 * @since   11.1      - ORIG
	 */
	public function store ($updateNulls = false) {
		$date = JFactory::getDate();
		$user = JFactory::getUser();
		$k = $this->_tbl_key;
		if ($this->$k) {
			$this->modified = $date->toSql();
			$this->modified_by = $user->get('id');
		} else {
			$this->created = $date->toSql();
			$this->created_by = $user->get('id');
		}
		return parent::store($updateNulls);
	}

	/**
	 * Method to set the publishing state for a row or list of rows in the database
	 * table.  The method respects checked out rows by other users and will attempt
	 * to checkin rows that it can after adjustments are made.
	 * @param    mixed   $pks    An optional array of primary key values to update.  If not
	 *                           set the instance property value is used.
	 * @param    integer $state  The publishing state. eg. [0 = unpublished, 1 = published]
	 * @param    integer $userId The user id of the user performing the operation.
	 * @return    boolean    True on success.
	 * @since    1.0.4       - ORIG
	 */
	public function publish ($pks = null, $state = 1, $userId = 0) {
		// Initialise variables.
		$k = $this->_tbl_key;

		// Sanitize input.
		JArrayHelper::toInteger($pks);
		$userId = (int)$userId;
		$state = (int)$state;

		// If there are no primary keys set check to see if the instance key is set.
		if (empty($pks)) {
			if ($this->$k) {
				$pks = array($this->$k);
			} // Nothing to set publishing state on, return false.
			else {
				$this->setError(JText::_('JLIB_DATABASE_ERROR_NO_ROWS_SELECTED'));
				return false;
			}
		}

		// Build the WHERE clause for the primary keys.
		$where = $k . '=' . implode(' OR ' . $k . '=', $pks);

		// Determine if there is checkin support for the table.
		if (!property_exists($this, 'checked_out') || !property_exists($this, 'checked_out_time')) {
			$checkin = '';
		} else {
			$checkin = ' AND (checked_out = 0 OR checked_out = ' . (int)$userId . ')';
		}

		// Update the publishing state for rows with the given primary keys.
		$this->_db->setQuery(
			'UPDATE `' . $this->_tbl . '`' .
			' SET `state` = ' . (int)$state .
			' WHERE (' . $where . ')' .
			$checkin
		);
		$this->_db->query();

		// Check for a database error.
		if ($this->_db->getErrorNum()) {
			$this->setError($this->_db->getErrorMsg());
			return false;
		}

		// If checkin is supported and all rows were adjusted, check them in.
		if ($checkin && (count($pks) == $this->_db->getAffectedRows())) {
			// Checkin the rows.
			foreach ($pks as $pk) {
				$this->checkin($pk);
			}
		}

		// If the JTable instance value is in the list of primary keys that were set, set the instance.
		if (in_array($this->$k, $pks)) {
			$this->state = $state;
		}

		$this->setError('');
		return true;
	}

	/**
	 * Define a namespaced asset name for inclusion in the #__assets table
	 * @return string The asset name
	 * @see JTable::_getAssetName
	 */
	protected function _getAssetName () {
		$k = $this->_tbl_key;
		return 'com_bixmailing.mailing.' . (int)$this->$k;
	}
}
