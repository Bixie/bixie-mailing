<?php
/**
 *	com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *	Bixie.nl
 *
 */

// No direct access
defined('_JEXEC') or die;
jimport('joomla.application.component.controllerform');

	/**
	 * Class BixmailingControllerMailing
	 */
	class BixmailingControllerMailing extends JControllerForm {
	
	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */
	public function &getModel($name = 'mailing', $prefix = 'BixmailingModel',$config=array()) {
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	/**
	 * Creeer mailingnaam obv mailinggegevens
	 * @return void
	 */
	public function renderMailingNaam () {
		$return = array(
			'success' => false,
			'result' => array('mailingNaam' => ''),
			'messages' => array('success' => array(), 'warning' => array(), 'danger' => array(), 'info' => array())
		);
		$model = $this->getModel();
		$input = JFactory::getApplication()->input;
		$mailing_id = $input->get('mailing_id', 0, 'int');
		$user_id = $input->get('user_id', 0, 'int');
		$type = $input->get('type', '', 'string');

		$return['result']['naam'] = $model->renderMailingNaam($mailing_id, $user_id, $type, $return['messages']);

		$return['success'] = $return['result']['naam'] != '';

		print json_encode($return);
	}

	/**
	 * Method to run batch operations.
	 * @param   string $model The model
	 * @return void
	 */
	public function batch ($model = null) {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$return = array(
			'success'=>false,
			'result'=>array(),
			'messages'=>array('success'=>array(),'warning'=>array(),'danger'=>array(),'info'=>array())
		);
		// Set the model
		$model	= $this->getModel('Mailing');

		$return['success'] = parent::batch($model);

		$messages = JFactory::getApplication()->getMessageQueue();
		if (!empty($this->message) && !$return['success']) { //alleen fout uit controller
			$messages[] = array('message'=>$this->message,'type'=>'danger');
		}
// pr($messages,$this->message);
		// Build the sorted message list
		if (is_array($messages) && !empty($messages)) {
			foreach ($messages as $msg) {
				if (isset($msg['message'])) {
					if ($msg['type'] == 'message' || empty($msg['type'])) $msg['type'] = 'info';
					if ($msg['type'] == 'error') $msg['type'] = 'danger';
					$return['messages'][$msg['type']][] = $msg['message'];
				}
			}
		}
		print json_encode($return);
	}


	/**
	 * test
	 * @return void
	 */
	public function fetchGLS() {
		// JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		// 75470057
		$reference = array(
			'Credentials' => array('UserName' => 'DEVOS', 'Password' => '1221DV'),
			'RefValue' => '18235206982'
		);
		$client = new SoapClient("http://www.gls-group.eu/276-I-PORTAL-WEBSERVICE/services/Tracking/wsdl/Tracking.wsdl");
		// $result = $client->GetTuDetail($reference);
		$result = $client->GetTuList($reference);
		echo '<pre>';
		print_r($client);	
		
		// $reference = array(
			// 'Credentials' => array('UserName' => 'devos', 'Password' => 'knk547'),
			// 'RefValue' => '18235206982'
		// );
		// $client = new SoapClient("http://services.gls-netherlands.com/webservices/webuniboxproxy.asmx?WSDL");
		// $result = $client->GetRandomUniBoxSoapOut();

		print_r($result);	
		echo '</pre>';
	}
}