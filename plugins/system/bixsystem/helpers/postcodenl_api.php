<?php
/*
Copyright (c) 2012, Postcode.nl B.V.
All rights reserved.

Redistribution and use in source and binary forms, with or without modification, are
permitted provided that the following conditions are met:

   1. Redistributions of source code must retain the above copyright notice, this list of
      conditions and the following disclaimer.

   2. Redistributions in binary form must reproduce the above copyright notice, this list
      of conditions and the following disclaimer in the documentation and/or other materials
      provided with the distribution.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS
OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR
CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL
DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY
WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
*/

class PostcodeNl_Api_Helper_Data {
	const API_TIMEOUT = 3;

	public function __construct($params) {
		$this->params = $params;
	}

	public function lookupAddress($postcode, $houseNumber, $houseNumberAddition)
	{
		$serviceUrl = trim($this->params->get('api_url'));
		$serviceKey = trim($this->params->get('api_key'));
		$serviceSecret = trim($this->params->get('api_secret'));
		$serviceShowcase = false;
		$serviceDebug = false;


		if (!$serviceUrl || !$serviceKey || !$serviceSecret)
		{
			return array('message' => JText::_('PLG_SYSTEM_BIXSYSTEM_NOT_CONFIGURED'));
		}

		// Check for SSL support in CURL, if connecting to `https`
		if (substr($serviceUrl, 0, 8) == 'https://')
		{
			$curlVersion = curl_version();
			if (!($curlVersion['features'] & CURL_VERSION_SSL))
			{
				return array('message' => 'Cannot connect to Postcode.nl API: Server is missing SSL (https) support for CURL.');
			}
		}

		$url = $serviceUrl . '/rest/addresses/' . urlencode($postcode). '/'. urlencode($houseNumber) . '/'. urlencode($houseNumberAddition);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::API_TIMEOUT);
		curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
		curl_setopt($ch, CURLOPT_USERPWD, $serviceKey .':'. $serviceSecret);
		curl_setopt($ch, CURLOPT_USERAGENT, 'BixiePrintshopPostcodenl_plugin');
		$jsonResponse = curl_exec($ch);
		$curlError = curl_error($ch);
		curl_close($ch);

		$response = json_decode($jsonResponse, true);

		$sendResponse = array();
		if ($serviceShowcase)
			$sendResponse['showcaseResponse'] = $response;

		if ($serviceDebug)
		{

			$sendResponse['debugInfo'] = array(
				'requestUrl' => $url,
				'rawResponse' => $jsonResponse,
				'parsedResponse' => $response,
				'curlError' => $curlError,
				'configuration' => array(
					'url' => $serviceUrl,
					'key' => $serviceKey,
					'secret' => substr($serviceSecret, 0, 6) .'[hidden]',
					'showcase' => $serviceShowcase,
					'debug' => $serviceDebug,
				)
			);
		}

		if (is_array($response) && isset($response['exceptionId']))
		{
			switch ($response['exceptionId'])
			{
				case 'PostcodeNl_Controller_Address_InvalidPostcodeException':
					$sendResponse['message'] = JText::_('PLG_SYSTEM_BIXSYSTEM_PC_INVALID');
					$sendResponse['messageTarget'] = 'postcode';
					break;
				case 'PostcodeNl_Service_PostcodeAddress_AddressNotFoundException':
					$sendResponse['message'] = JText::_('PLG_SYSTEM_BIXSYSTEM_PC_NBR_INVALID');
					$sendResponse['messageTarget'] = 'huisnummer';
					break;
				default:
					$sendResponse['message'] = JText::_('PLG_SYSTEM_BIXSYSTEM_NOT_FOUND');
					$sendResponse['messageTarget'] = 'huisnummer';
					break;
			}
		}
		else if (is_array($response) && isset($response['postcode']))
		{
			$sendResponse = array_merge($sendResponse, $response);
		}
		else
		{
			$sendResponse['message'] = JText::_('PLG_SYSTEM_BIXSYSTEM_POSTCODEFILL_NOT_AVAIL');
			$sendResponse['messageTarget'] = 'huisnummer';
		}
		return $sendResponse;
	}
}
