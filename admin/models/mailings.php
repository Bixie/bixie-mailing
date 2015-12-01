<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */

defined('_JEXEC') or die;

jimport('joomla.application.component.modellist');

/**
 * Methods supporting a list of Bixmailing records.
 */
class BixmailingModelmailings extends JModelList {

	protected static $_itemClass = 'Mailing';

	/**
	 * Constructor.
	 * @param    array    An optional associative array of configuration settings.
	 * @see        JControllerLegacy
	 * @since      1.6
	 */
	public function __construct ($config = array()) {
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'id', 'm.id',
				'naam', 'm.naam',
				'type', 'm.type',
				'company', 'vmi.company',
				'state', 'm.state',
				'status', 'm.status'

			);
		}
		parent::__construct($config);
	}

	protected function _getList ($query, $limitstart = 0, $limit = 0) {
		$this->_db->setQuery($query, $limitstart, $limit);
		$result = $this->_db->loadObjectList(null, 'BixMailing' . self::$_itemClass);

		return $result;
	}

	/**
	 * Method to auto-populate the model state.     - ORIG
	 * Note. Calling getState in this method will result in recursion.
	 */
	protected function populateState ($ordering = null, $direction = null) {
		// Initialise variables.
		$app = JFactory::getApplication('administrator');

		// Load the filter state.
		$search = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		if (is_numeric($search)) $search = 'id:' . $search;
		$this->setState('filter.search', $search);

		$published = $app->getUserStateFromRequest($this->context . '.filter.state', 'filter_published', '', 'string');
		$this->setState('filter.state', $published);

		$user_id = $app->getUserStateFromRequest($this->context . '.filter.user_id', 'filter_user_id', '', 'string');
		$this->setState('filter.user_id', $user_id);

		$vervoerder = $app->getUserStateFromRequest($this->context . '.filter.vervoerder', 'filter_vervoerder', '', 'string');
		$this->setState('filter.vervoerder', $vervoerder);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_bixmailing');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('m.id', 'desc');
	}

	/**
	 * Method to get a store id based on model configuration state.
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 * @param    string $id A prefix for the store id.
	 * @return    string        A store id.
	 * @since    1.6
	 */
	protected function getStoreId ($id = '') {
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');
		$id .= ':' . $this->getState('filter.user_id');
		$id .= ':' . $this->getState('filter.vervoerder');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 * @return    JDatabaseQuery
	 * @since    1.6
	 */
	protected function getListQuery () {
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			$this->getState(
				'list.select',
				'm.*'
			)
		);
		$query->from('`#__bm_mailing` AS m');

		// Join over the users for the checked out user.
		$query->select('uc.name AS editor');
		$query->join('LEFT', '#__users AS uc ON uc.id=m.checked_out');

		// Join over the users for email.
		$query->select('u.email AS user_email, u.name AS user_naam');
		$query->join('LEFT', '#__users AS u ON u.id=m.user_id');

		// Join over the profile tables
		$query->select("REPLACE(prc.profile_value,'\"','') AS user_company");
		$query->join('LEFT', '#__user_profiles AS prc ON m.user_id = prc.user_id AND prc.profile_key = \'profile.bedrijfsnaam\'');

		// Filter by published state
		$published = $this->getState('filter.state');
		if (is_numeric($published)) {
			$query->where('m.state = ' . (int)$published);
		} else if ($published === '') {
			$query->where('(m.state IN (0, 1))');
		}

		$user_id = $this->getState('filter.user_id');
		if (is_numeric($user_id)) {
			$query->where('m.user_id = ' . (int)$user_id);
		}

		$vervoerder = $this->getState('filter.vervoerder');
		if (!empty($vervoerder)) {
			$query->where("m.vervoerder = '$vervoerder'");
		}

		// Filter by search in title   - ORIG
		$search = $this->getState('filter.search');
		if (!empty($search)) {
			if (stripos($search, 'klant:') === 0) {
				$query->where("m.klantnummer = '" . (int)substr($search, 6) . "'");
			} elseif (stripos($search, 'file:') === 0) {
				$query->where("m.importbestand = '" . substr($search, 5) . "'");
			} else {
				$search = $db->escape($search, true);
				$searchLike = $db->Quote('%' . $search . '%');
				$query->where('(' . implode(' OR ', array(
						"prc.profile_value LIKE $searchLike",
						"m.naam LIKE $searchLike",
						"m.klantnummer LIKE '%$search'",
						"m.trace_nl = '$search'",
						"m.trace_btl = '$search'",
						"m.trace_gp = '$search'",
						"m.adresnaam LIKE $searchLike",
						"m.straat LIKE $searchLike",
						"m.postcode LIKE $searchLike",
						"m.plaats LIKE $searchLike"
					)) . ')');
			}
		}

		// Add the list ordering clause.   - ORIG
		$orderCol = $this->state->get('list.ordering');
		$orderDirn = $this->state->get('list.direction');
		if ($orderCol && $orderDirn) {
			$query->order($db->escape($orderCol . ' ' . $orderDirn));
		}
// echo $query->dump();
		return $query;
	}

	/**
	 * oke, dit kan vast veel beter...
	 * @param string $vervoerder
	 * @return \stdClass
	 */
	public function getOpenMailings ($vervoerder) {
		$db = $this->getDbo();
		$result = new stdClass;
		$query = $db->getQuery(true)
			->select("COUNT(id)")
			->from("#__bm_mailing")
			->where("state = 0 AND vervoerder = '$vervoerder' AND status = 'nieuw'");
		$db->setQuery($query);
		$result->aantalNieuw = $db->loadResult();
		$query = $db->getQuery(true)
			->select("COUNT(id)")
			->from("#__bm_mailing")
			->where("state = 0 AND vervoerder = '$vervoerder' AND status = 'incompleet'");
		$db->setQuery($query);
		$result->aantalIncompleet = $db->loadResult();
		return $result;
	}
}
