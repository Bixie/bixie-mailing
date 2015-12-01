<?php
/* *
 *	BixieMailing
 *  postcodecheck.php.php
 *	Created on 1-4-14 13:04
 *  
 *  @author Matthijs
 *  @copyright Copyright (C)2014 Bixie.nl
 *
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Class BixPostcodecheck
 */
class BixPostcodecheck {


	/**
	 * @param $postcode
	 * @param $huisnr
	 * @param $toevoeging
	 * @return array
	 */
	public static function lookup ($postcode, $huisnr, $toevoeging) {
		$return = array(
			'success' => false,
			'result' => array(),
			'messages' => array('success' => array(), 'warning' => array(), 'danger' => array(), 'info' => array())
		);
		//format postcode
		$postcodeArr = self::formatPostcode($postcode);
		if (empty($postcodeArr['valid']) || empty($postcodeArr['num'])) { //invalid postcode
			$return['messages']['danger'][] = 'Postcode niet in geldig formaat';
			return $return;
		}
		if (!empty($huisnr)) {
			//validatie postcode.nl
			$helper = new PostcodeNl_Api_Helper_Bix();
			$return['result'] = $helper->lookupAddress($postcodeArr['num'] . $postcodeArr['alph'], $huisnr, $toevoeging, $return['messages']);
			if (!isset($return['result']['street'])) {
				$return['messages']['warning'][] = $return['result']['message'];
			}
		}
		//valid pc
		$return['success'] = true;
		return $return;
	}
	/**
	 * @param $postcodeRaw
	 * @return array
	 */
	public static function formatPostcode ($postcodeRaw) {
		$regEx = '/^(?P<num>[0-9]{4}).?(?P<alph>[a-z|A-Z]{2})?$/';
		$postcodeArr = array();
		if (preg_match($regEx, trim($postcodeRaw), $match)) {
			$postcodeArr['format'] = $match['num'] . ' ' . strtoupper($match['alph']);
			$postcodeArr['num'] = $match['num'];
			$postcodeArr['alph'] = strtoupper($match['alph']);
			$postcodeArr['raw'] = $postcodeRaw;
		} else {
			$postcodeArr['raw'] = $postcodeRaw;
		}
		$postcodeArr['valid'] = !empty($postcodeArr['num']) && !empty($postcodeArr['alph']);
		return $postcodeArr;
	}
}


/**
 * Class PostcodeNl_Api_Helper_Data
 */
class PostcodeNl_Api_Helper_Bix {
	/**
	 *
	 */
	const API_TIMEOUT = 3;
	/**
	 *
	 */
	const API_URL = 'https://api.postcode.nl';
	/**
	 *
	 */
	const API_KEY = '6sweluzZcaevmcWEFUGW7qYWE1wD9XPdjZLtoRvtVoO';
	/**
	 *
	 */
	const API_SECRET = 'fABWL3t88Et8QysjAUrBrfsYG1nZVjAoKEIdOkiiaMw';

	/**
	 * @param $postcode
	 * @param $houseNumber
	 * @param $houseNumberAddition
	 * @param $messages
	 * @return array
	 */
	public function lookupAddress ($postcode, $houseNumber, $houseNumberAddition, &$messages) {
		$sendResponse = array();
		$serviceUrl = self::API_URL;
		$serviceKey = self::API_KEY;
		$serviceSecret = self::API_SECRET;
		$serviceShowcase = false;
		$serviceDebug = false;


		if (!$serviceUrl || !$serviceKey || !$serviceSecret) {
			$messages['danger'][] = 'Error in config';
			return $sendResponse;
		}

		// Check for SSL support in CURL, if connecting to `https`
		if (substr($serviceUrl, 0, 8) == 'https://') {
			$curlVersion = curl_version();
			if (!($curlVersion['features'] & CURL_VERSION_SSL)) {
				$messages['danger'][] = 'annot connect to Postcode.nl API: Server is missing SSL (https) support for CURL.';
				return $sendResponse;
			}
		}

		$url = $serviceUrl . '/rest/addresses/' . urlencode($postcode) . '/' . urlencode($houseNumber) . '/' . urlencode($houseNumberAddition);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::API_TIMEOUT);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($ch, CURLOPT_USERPWD, $serviceKey . ':' . $serviceSecret);
		curl_setopt($ch, CURLOPT_USERAGENT, 'BixiePrintshopPostcodenl_plugin');
		$jsonResponse = curl_exec($ch);
		$curlError = curl_error($ch);
		curl_close($ch);

		$response = json_decode($jsonResponse, true);

		if ($serviceShowcase)
			$sendResponse['showcaseResponse'] = $response;

		if ($serviceDebug) {

			$sendResponse['debugInfo'] = array(
				'requestUrl' => $url,
				'rawResponse' => $jsonResponse,
				'parsedResponse' => $response,
				'curlError' => $curlError,
				'configuration' => array(
					'url' => $serviceUrl,
					'key' => $serviceKey,
					'secret' => substr($serviceSecret, 0, 6) . '[hidden]',
					'showcase' => $serviceShowcase,
					'debug' => $serviceDebug,
				)
			);
		}

		if (is_array($response) && isset($response['exceptionId'])) {
			switch ($response['exceptionId']) {
				case 'PostcodeNl_Controller_Address_InvalidPostcodeException':
					$sendResponse['message'] = JText::_('Postcode niet geldig');
					$sendResponse['messageTarget'] = 'postcode';
					break;
				case 'PostcodeNl_Service_PostcodeAddress_AddressNotFoundException':
					$sendResponse['message'] = JText::_('Combinatie postcode en huisnummer bestaat niet');
					$sendResponse['messageTarget'] = 'huisnummer';
					break;
				default:
					$sendResponse['message'] = JText::_('Postcode niet gevonden');
					$sendResponse['messageTarget'] = 'huisnummer';
					break;
			}
		} else if (is_array($response) && isset($response['postcode'])) {
			$sendResponse = array_merge($sendResponse, $response);
		} else {
			$sendResponse['message'] = JText::_('Systeem niet beschikbaar');
			$sendResponse['messageTarget'] = 'huisnummer';
		}
		return $sendResponse;
	}
}
