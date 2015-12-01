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
class BixmailingModelBestanden extends JModelLegacy {


	/**
	 * @return array
	 */
	public function getFolders () {
		$mailingsModel = BixTools::getModel('mailings', array('ignore_request' => false));
		$vervoerders = array('gls', 'postnl');
		$openMailings = array('gls' => array(), 'postnl' => array());
		foreach ($vervoerders as $vervoerder) {
			$openMailings[$vervoerder] = $mailingsModel->getOpenMailings($vervoerder);
		}
		return $openMailings;

	}


}
