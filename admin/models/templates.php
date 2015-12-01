<?php
/**
 *	com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *	Bixie.nl
 *
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Bixmailing records.
 */
class BixmailingModeltemplates extends JModelList
{

	protected static $_itemClass = 'Template';
    /**
     * Constructor.
     *
     * @param    array    An optional associative array of configuration settings.
     * @see        JControllerLegacy
     * @since    1.6
     */
    public function __construct($config = array()) {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 't.id',
                'onderwerp', 't.onderwerp',
				'type', 't.type',
				'state', 't.state'

            );
        }
        parent::__construct($config);
    }

	protected function _getList($query, $limitstart = 0, $limit = 0) {
		$this->_db->setQuery($query, $limitstart, $limit);
		$result = $this->_db->loadObjectList(null,'BixMailing'.self::$_itemClass);

		return $result;
	}

	/**
	 * Method to auto-populate the model state.     - ORIG
	 *
	 * Note. Calling getState in this method will result in recursion.
	 */
	protected function populateState($ordering = null, $direction = null) {
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context.'.filter.search', 'filter_search');
		if (is_numeric($search)) $search = 'id:'.$search;
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context.'.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_bixmailing');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('t.type', 'desc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param	string		$id	A prefix for the store id.
	 * @return	string		A store id.
	 * @since	1.6
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id.= ':' . $this->getState('filter.search');
		$id.= ':' . $this->getState('filter.state');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return	JDatabaseQuery
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db		= $this->getDbo();
		$query	= $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				't.*'
			)
		);
		$query->from('`#__bm_template` AS t');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=t.checked_out');


		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published)) {
			$query->where('t.state = '.(int) $published);
		} else if ($published === '') {
			$query->where('(t.state IN (0, 1))');
		}
		
		// Filter by search in title   - ORIG
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'id:') === 0) {
				$query->where('t.id = '.(int) substr($search, 3));
			} else {
				$search = $db->Quote('%'.$db->escape($search, true).'%');
                $query->where("(t.onderwerp LIKE $search)");
			}
		}

		// Add the list ordering clause.   - ORIG
		$orderCol	= $this->state->get('list.ordering');
		$orderDirn	= $this->state->get('list.direction');
        if ($orderCol && $orderDirn) {
		    $query->order($db->escape($orderCol.' '.$orderDirn));
        }
// echo $query->dump();
		return $query;
	}
}
