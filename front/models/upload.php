<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */

defined('_JEXEC') or die;

/**
 * Class BixmailingModelUpload
 */
class BixmailingModelUpload extends JModelLegacy {

	/**
	 * @var JRegistry
	 */
	public $mailingsState;

	/**
	 * @var JRegistry
	 */
	public $uploadState;

	/**
	 * Constructor
	 * @param   array $config An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @since   11.1
	 */
	public function __construct ($config = array()) {
		parent::__construct($config);
		$app = JFactory::getApplication();

		$this->uploadState = new JRegistry();
		$vervoerder = $app->getUserStateFromRequest('com_bixmailing.upload.vervoerder', 'vervoerder', 'gls', 'word');
		$this->uploadState->set('vervoerder', $vervoerder);
	}

	/**
	 * @return JRegistry
	 */
	public function getUploadState () {
		return $this->uploadState;
	}

	/**
	 * @return array
	 */
	public function getNewMailings () {
		$states = array();
		$states['filter.vervoerder'] = $this->uploadState->get('vervoerder', 'gls');
		$states['filter.state'] = 0;

		return $this->_getMailings($states);
	}

	/**
	 * @return array
	 */
	public function getOpenMailings () {
		$mailingsModel = BixTools::getModel('mailings', array('ignore_request' => false));
		$vervoerders = array('gls', 'postnl');
		$openMailings = array('gls' => array(), 'postnl' => array());
		foreach ($vervoerders as $vervoerder) {
			$openMailings[$vervoerder] = $mailingsModel->getOpenMailings($vervoerder);
		}
		return $openMailings;

	}

	/**
	 * @param array $states
	 * @return array
	 */
	protected function _getMailings ($states) {
		$mailingsModel = BixTools::getModel('mailings', array('ignore_request' => false));
		$this->mailingsState = $mailingsModel->getState(); //init state
		foreach ($states as $name => $value) {
			$mailingsModel->setState($name, $value);
		}
		$mailingsModel->setState('list.ordering', 'm.klantnummer, m.referentie');
		$mailingsModel->setState('list.direction', 'ASC');
		$mailingsModel->setState('list.limit', 0);
		$mailingsModel->setState('list.start', 0);

		return $mailingsModel->getItems();
	}

}
