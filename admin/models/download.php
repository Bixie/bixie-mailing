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
 * Bixprintshop model.
 */
class BixmailingModeldownload extends JModelAdmin {
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'com_bixprintshop';

	protected $loadedForm = null;

	protected $jsonVars = array();
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
	public function getTable($type = 'download', $prefix = 'BixmailingTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 * @param    array   $data     An optional array of data for the form to interogate.
	 * @param    boolean $loadData True if the form is to load its own data (default case), false if not.
	 * @param string     $xmlName
	 * @param string     $control
	 * @return    JForm    A JForm object on success, false on failure
	 * @since    1.6
	 */
	public function getForm($data = array(), $loadData = true, $xmlName='download',$control='jform')
	{
		// Initialise variables.
		$app	= JFactory::getApplication();

		// Get the form.
		$form = $this->loadForm('com_bixmailing.download', $xmlName, array('control' => $control, 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		$this->loadedForm = $form;
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
		$data = JFactory::getApplication()->getUserState('com_bixmailing.edit.download.data', array());

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
//pr($this->jsonVars);
		if ($item = parent::getItem($pk)) {
			//Do any procesing on fields here if needed
			if (count($this->jsonVars)) {
				foreach ($this->jsonVars as $jsonVar) {
					if (property_exists($item, $jsonVar) && is_string($item->$jsonVar)) {
						$registry = new JRegistry;
						$registry->loadString($item->$jsonVar);
						$item->$jsonVar = $registry->toArray();
					}
				}
			}

		}

		return $item;
	}


}