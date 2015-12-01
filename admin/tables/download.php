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
 * attrib Table class
 */
class BixmailingTabledownload extends JTable {
	/**
	 * Constructor
	 * @param string $db
	 * @internal param \A $JDatabase database connector object
	 */
	public function __construct (&$db) {
		parent::__construct('#__bm_download', 'id', $db);
	}

	/**
	 * Overloaded bind function to pre-process the params.
	 * @param Named  $array
	 * @param string $ignore
	 * @internal   param \Named $array array
	 * @return    null|string    null is operation was satisfactory, otherwise returns an error
	 * @see        JTable:bind
	 * @since      1.5
	 */
	public function bind ($array, $ignore = '') {

		return parent::bind($array, $ignore);
	}

	/**
	 * Overloaded check function
	 */
	public function check () {


		return parent::check();
	}


}
