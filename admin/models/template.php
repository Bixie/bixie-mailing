<?php
/**
 *	com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *	Bixie.nl
 *
 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.component.modeladmin');

/**
 * Bixmailing model.
 */
class BixmailingModeltemplate extends JModelAdmin {
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'com_bixmailing';

	protected $_jsonVars = array();
 
 	protected static $_itemClass = 'Template';

   /**
     * Constructor.
     *
     * @param    array    An optional associative array of configuration settings.
     * @see        JControllerLegacy
     * @since    1.6
     */
    public function __construct($config = array()) {
        parent::__construct($config);
    }


	/**
	 * Returns a reference to the a Table object, always creating it.
	 * @param string|The $type
	 * @param string     $prefix A prefix for the table class name. Optional.
	 * @param array      $config Configuration array for model. Optional.
	 * @internal param \The $type table type to instantiate
	 * @return    JTable    A database object
	 * @since    1.6
	 */
	public function getTable($type = 'Template', $prefix = 'BixmailingTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param	array	$data		An optional array of data for the form to interogate.
	 * @param	boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return	JForm	A JForm object on success, false on failure
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_bixmailing.template', 'template', array('control' => 'jform', 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return	mixed	The data for the form.
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_bixmailing.edit.template.data', array());

		if (empty($data)) {
			$data = $this->getItem();
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param	integer	The id of the primary key.
	 *
	 * @return	mixed	Object on success, false on failure.
	 * @since	1.6
	 */
	public function getItem($pk = null)	{
		if ($item = parent::getItem($pk)) {
			//Do any procesing on fields here if needed
			$classname = 'BixMailing'.self::$_itemClass;
			$item = new $classname($item);
		}

		return $item;
	}


	/**
	 * Method to test whether a record can have its state edited.
	 *
	 * @param	object	$record	A record object.
	 *
	 * @return	boolean	True if allowed to change the state of the record. Defaults to the permission set in the component.
	 * @since	1.6
	 */
	protected function canEditState($record) {
		$user = JFactory::getUser();

		return parent::canEditState('com_bixmailing');
	}


}