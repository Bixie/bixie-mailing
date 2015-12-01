<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */

// No direct access.
defined('_JEXEC') or die;

jimport('joomla.application.controller');

/**
 * Class BixmailingControllerBixmailing
 */
class BixmailingControllerBixmailing extends JControllerLegacy {

	/**
	 * @return void
	 */
	function download () {
		$hash = JFactory::getApplication()->input->getString('h', '');
		if (!$hash) {
			JError::raiseWarning(403, 'Geen geldige sleutel');
			jExit();
		}
		BixHelper::downloadFile($hash);
	}

	/**
	 * @return void
	 */
	public function upload () {
		//check token etc?
//		echo BixHelper::downloadLink('file');
		$app = JFactory::getApplication();
		$uploadTask = $app->input->get('uploadTask', '');
		$options = array();
		switch ($uploadTask) {
			case 'convertFiles':
				$options['upload_dir'] = BIX_PATH_UPLOADS . '/conversie/' . JFactory::getDate()->format('Y-m') . '/';
				if (!is_dir($options['upload_dir'] . 'xls/')) {
					mkdir($options['upload_dir'] . 'xls/', 0755, true);
				}
				break;
			case 'massamail':
				$options['upload_dir'] = BIX_PATH_UPLOADS . '/mailings/' . JFactory::getDate()->format('Y-m') . '/';
				break;
			default:
				break;
		}
		new BixUpload($options); //that's it

//pr($uploader);
	}

	/**
	 *
	 */
	public function saveMassa () {
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$return = array(
			'success' => false,
			'result' => array(),
			'messages' => array('success' => array(), 'warning' => array(), 'danger' => array(), 'info' => array())
		);
		$app = JFactory::getApplication();
		$model = BixTools::getModel('massa');
		$formData = $app->input->get('jform', array(), 'array');
		//add data
		$formData['naam'] = $model->renderMassaNaam(0, $formData['user_id'], $formData['type'], $return['messages']);
		$formData['status'] = 'nieuw';
		$formData['state'] = 1;
		$bixMassa = new BixMailingMassa();
		$return['success'] = $bixMassa->save($formData);
		if ($return['success']) {
			$return['messages']['success'][] = JText::_('COM_BIXMAILING_MASSAMAIL_SAVED');
			$return['result'] = $bixMassa->getProperties();
			//send mail
			if (!$model->sendConfirmMail($bixMassa->id)) {
				$return['messages']['warning'][] = JText::sprintf('COM_BIXMAILING_FOUT_VERZENDEN_MAIL_SPR', $model->getError());
			}
		}
		print json_encode($return);
	}

	/**
	 * importeren gls informatie
	 * @return void
	 */
	public function importGls () {
		// JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$return = array(
			'success' => false,
			'result' => array(),
			'messages' => array('success' => array(), 'warning' => array(), 'danger' => array(), 'info' => array())
		);
		$importType = 'labellite';
		$hash = JFactory::getApplication()->input->getString('hash', '');
		$file = $this->_getFileByHash($hash);
		$data = $this->_importRawData($importType, $file, $return['messages']);
		if ($data) {
			$return['messages']['info'][] = JText::sprintf('COM_BIXMAILING_MAILING_IMPORT_RIJEN_IMPORTED_SPR', count($data), ucfirst($importType), array('jsSafe' => true));
			$newfile = dirname($file) . '/xls/' . basename($file, '.txt') . '.xlsx';
			$result = $this->_exportData($importType, $data, $newfile, $return);
			if ($result) {
				$return['result']['file'] = array(
					'name' => basename($newfile),
					'size' => filesize($newfile),
					'url' => BixHelper::downloadLink($newfile)
				);
				$return['success'] = true;
			}
		}
// pr($data);
		print json_encode($return);
	}

	/**
	 * importeren postnl informatie
	 * @return void
	 */
	public function importPostnl () {
		// JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$return = array(
			'success' => false,
			'result' => array(),
			'messages' => array('success' => array(), 'warning' => array(), 'danger' => array(), 'info' => array())
		);
		$importType = 'postnl';
		$hash = JFactory::getApplication()->input->getString('hash', '');
		$file = $this->_getFileByHash($hash);
		$data = $this->_importRawData($importType, $file, $return['messages']);
		if ($data) {
			$return['messages']['info'][] = JText::sprintf('COM_BIXMAILING_MAILING_IMPORT_RIJEN_IMPORTED_SPR', count($data), ucfirst($importType), array('jsSafe' => true));
			$newfile = dirname($file) . '/xls/' . basename($file, '.csv') . '.xlsx';
			$result = $this->_exportData($importType, $data, $newfile, $return);
			if ($result) {
				$return['result']['file'] = array(
					'name' => basename($newfile),
					'size' => filesize($newfile),
					'url' => JURI::root() . BixHelper::downloadLink($newfile)
				);
				$return['success'] = true;
			}
		}
		print json_encode($return);
	}

	/**
	 * importeren postnl informatie
	 * @return void
	 */
	public function importParcelware () {
		// JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$return = array(
			'success' => false,
			'result' => array(),
			'messages' => array('success' => array(), 'warning' => array(), 'danger' => array(), 'info' => array())
		);
		$importType = 'postnl';
		$hash = JFactory::getApplication()->input->getString('hash', '');
		$file = $this->_getFileByHash($hash);
		$data = $this->_importRawData($importType, $file, $return['messages']);
		if ($data) {
			$return['messages']['info'][] = JText::sprintf('COM_BIXMAILING_MAILING_IMPORT_RIJEN_IMPORTED_SPR', count($data), ucfirst($importType), array('jsSafe' => true));
			$newfile = dirname($file) . '/xls/' . basename($file, '.csv') . '.xlsx';
			$result = $this->_exportData($importType, $data, $newfile, $return);
			if ($result) {
				$return['result']['file'] = array(
					'name' => basename($newfile),
					'size' => filesize($newfile),
					'url' => JURI::root() . BixHelper::downloadLink($newfile)
				);
				$return['success'] = true;
			}
		}
		print json_encode($return);
	}

	/**
	 * @param $hash
	 * @return bool
	 */
	protected function _getFileByHash ($hash) {
		$db = JFactory::getDBO();
		$db->setQuery("SELECT * FROM #__bm_download WHERE hash = '$hash'");
		$downloadObj = $db->loadObject();
		if ($downloadObj->id && file_exists($downloadObj->filePath)) {
			return $downloadObj->filePath;
		}
		return false;
	}


	/**
	 * @param $importType
	 * @param $file
	 * @param $return
	 * @return bool
	 */
	protected function _importRawData ($importType, $file, &$return) {
		if ($file) {
			$bixImport = new BixImport();
			$data = $bixImport->$importType($file, $return);
			if (!$data) {
				$return['messages']['danger'][] = $bixImport->getError();
				return false;
			} else {
				return $data;
			}
		} else {
			$return['messages']['danger'][] = JText::_('COM_BIXMAILING_MAILING_IMPORT_FILE_NOT_FOUND');
			return false;
		}
	}

	/**
	 * @param string $importType
	 * @param array  $data
	 * @param string $filename
	 * @param  array $return
	 * @return bool
	 */
	protected function _exportData ($importType, $data, $filename, &$return) {
		$bixExport = new BixExport();
		$result = $bixExport->$importType($data, $filename);
		if (!$result) {
			$return['messages']['danger'][] = $bixExport->getError();
			return false;
		} else {
			return true;
		}
	}

	/**
	 *
	 */
	public function relatedMailings () {
		$return = array(
			'success' => false,
			'result' => array(),
			'messages' => array('success' => array(), 'warning' => array(), 'danger' => array(), 'info' => array())
		);
		$user = JFactory::getUser();
		if ($user->authorise('core.admin')) {
			$app = JFactory::getApplication();
			$return['result']['mailingID'] = $app->input->getString('mailingID', '');
			$referentie = $app->input->getString('referentie', '');
			$db = JFactory::getDbo();
			$db->setQuery($db->getQuery(true)
					->select("id, naam, type, referentie")
					->from("#__bm_mailing")
					->where("referentie = '$referentie' AND status = 'incompleet'")
					->order("id DESC")
			);
			$return['result']['related'] = $db->loadObjectList();
			foreach ($return['result']['related'] as &$related) {
				$related->type = JText::_('BIX_MAILING_TYPE_' . strtoupper($related->type));
			}

			$return['success'] = !$db->getErrorNum();
		} else {
			$return['messages']['danger'][] = 'Geen adminrechten!';
		}
		print json_encode($return);

	}

	/**
	 *
	 */
	public function zoekKlanten () {
		$return = array(
			'success' => false,
			'result' => array(),
			'messages' => array('success' => array(), 'warning' => array(), 'danger' => array(), 'info' => array())
		);
		$user = JFactory::getUser();
		if ($user->authorise('core.admin')) {
			$app = JFactory::getApplication();
			$db = JFactory::getDbo();
			$search = $db->escape($app->input->getString('search', ''));
			$db->setQuery($db->getQuery(true)
				->select("u.id AS user_id")
				->from("#__users AS u")
				->select("REPLACE(prk.profile_value,'\"','') AS klantnummer")
				->select("REPLACE(prb.profile_value,'\"','') AS bedrijfsnaam")
				->select("CONCAT(REPLACE(pra1.profile_value,'\"',''),' ',REPLACE(pra2.profile_value,'\"',''),' ',REPLACE(prc.profile_value,'\"','')) AS adres")
				->join('LEFT', '#__user_profiles AS prb ON u.id = prb.user_id AND prb.profile_key = \'profile.bedrijfsnaam\'')
				->join('LEFT', '#__user_profiles AS prk ON u.id = prk.user_id AND prk.profile_key = \'profile.klantnummer\'')
				->join('LEFT', '#__user_profiles AS pra1 ON u.id = pra1.user_id AND pra1.profile_key = \'profile.address1\'')
				->join('LEFT', '#__user_profiles AS pra2 ON u.id = pra2.user_id AND pra2.profile_key = \'profile.address2\'')
				->join('LEFT', '#__user_profiles AS prc ON u.id = prc.user_id AND prc.profile_key = \'profile.city\'')
				->where("prk.profile_value NOT IN('\"\"', '')")
				->where("(u.name LIKE '%$search%' OR u.username LIKE '%$search%' OR u.email LIKE '%$search%' OR "
					. "prb.profile_value LIKE '%$search%' OR "
					. "prk.profile_value LIKE '%$search%' OR "
					. "pra1.profile_value LIKE '%$search%' OR "
					. "pra2.profile_value LIKE '%$search%' OR "
					. "prc.profile_value LIKE '%$search%')")
//					->group("klantnummer")
				->order("prb.profile_value ASC")
			);
			$return['result']['klanten'] = $db->loadObjectList();
			$return['success'] = !$db->getErrorNum();
		} else {
			$return['messages']['danger'][] = 'Geen adminrechten!';
		}
		print json_encode($return);

	}

	/**
	 *
	 */
	public function klantDetails () {
		$return = array(
			'success' => false,
			'result' => array(),
			'messages' => array('success' => array(), 'warning' => array(), 'danger' => array(), 'info' => array())
		);
		if (JFactory::getUser()->authorise('core.admin')) {
			$app = JFactory::getApplication();
			$userID = $app->input->getInt('userID', 0);
			$user = JFactory::getUser($userID);
			$return['result']['profile'] = BixmailingHelper::getUserProfile($userID, true)->toArray();
			$return['success'] = $user->id > 0;
		} else {
			$return['messages']['danger'][] = 'Geen adminrechten!';
		}
		print json_encode($return);
	}

	/**
	 *
	 */
	public function attachUser () {
		$return = array(
			'success' => false,
			'result' => array(),
			'messages' => array('success' => array(), 'warning' => array(), 'danger' => array(), 'info' => array())
		);
		$user = JFactory::getUser();
		if ($user->authorise('core.admin')) {
			$app = JFactory::getApplication();
			$klantnummer = $app->input->getString('klantnummer', '');
			$user_id = $app->input->getString('user_id', '');
			$mailingIDs = $app->input->get('mailingIDs', array(), 'array');
			if (!empty($mailingIDs)) {
				$mailingModel = BixTools::getModel('mailing');
				if (!$mailingModel->batchUser($user_id, $klantnummer, $mailingIDs, 'nieuw')) {
					$return['messages']['danger'][] = $mailingModel->getError();
				}
				$return['result'] = 'hoi';
				$return['messages']['success'][] = JText::sprintf('BIX_MAILING_KLANT_GEKOPPELD_SPR', count($mailingIDs));
				$return['success'] = true;
			} else {
				$return['messages']['warning'][] = 'Geen verzendingen geselecteerd!';
			}
		} else {
			$return['messages']['danger'][] = 'Geen adminrechten!';
		}
		print json_encode($return);

	}

	/**
	 *
	 */
	public function mailFiles () {
		$return = array(
			'success' => false,
			'result' => array(),
			'messages' => array('success' => array(), 'warning' => array(), 'danger' => array(), 'info' => array())
		);
		$user = JFactory::getUser();
		if ($user->authorise('core.admin')) {
			$app = JFactory::getApplication();
			$maildataReq = $app->input->get('maildata', array(), 'array');
			$maildata = BixHelper::getMailBase($maildataReq['email']);
			$maildata = array_merge($maildata, $maildataReq);
			$files = $app->input->get('files', array(), 'array');
			if (!empty($maildata['email']) && !empty($maildata['onderwerp']) && !empty($maildata['tekst']) && count($files)) {
				$maildata['attachment'] = array();
				foreach ($files as $hash) {
					$fileInfo = BixHelper::getFile($hash);
					if (!$fileInfo) {
						$return['messages']['warning'][] = 'Bestand niet gevonden!';
						continue;
					}
					$maildata['attachment'][] = $fileInfo->filePath;
				}
				$return['result'] = $maildata;
				$return['success'] = BixHelper::sendMail($maildata);
				if ($return['success'] == true) {
					$return['messages']['success'][] = JText::_('BIX_MAILING_FILES_SENT_SUCCESS');
				} else {
					$return['messages']['danger'][] = 'Fout bij verzenden mail';
				}
			} else {
				$return['messages']['warning'][] = 'Data niet compleet!';
			}
		} else {
			$return['messages']['danger'][] = 'Geen adminrechten!';
		}
		print json_encode($return);

	}

	/**
	 *
	 */
	public function sendMail () {
		// JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		$return = array(
			'success' => false,
			'result' => array(),
			'messages' => array('success' => array(), 'warning' => array(), 'danger' => array(), 'info' => array())
		);
		$user = JFactory::getUser();
		$db = JFactory::getDbo();
		if ($user->authorise('core.admin')) {
			$maildata = JFactory::getApplication()->input->get('maildata', array(), 'array');
			if ($maildata) {
				$statusChanges = array('opgeslagen' => array(), 'gemaild' => array());
				$mailingModel = BixTools::getModel('mailing');
				$mailingTable = $mailingModel->getTable();
				$db->setQuery($db->getQuery(true)
						->select("onderwerp, content")
						->from("#__bm_template")
						->where("type = 'TRACECOMBI' AND state = 1")
					, 0, 1);
				$template = $db->loadObject();
				$subjectTemplate = $template->onderwerp;
				$templParts = explode('{{[[|split|]]}}', $template->content);
				foreach ($maildata as $aKlantenMails) {
					$profile = BixmailingHelper::getUserProfile($aKlantenMails['user_id']);
					$totalTemplate = $templParts[0];
					$mailingTemplate = $templParts[1];
					//mieuwe statussen voorbereiden
					$allMailingIDs = array_flip($aKlantenMails['mailingIDs']);
					$mailedMailingIDs = array();
					// mailing info verzamelen
					$mailInfos = array();
					if (!empty($aKlantenMails['sendMailingIDs'])) {
						foreach ($aKlantenMails['sendMailingIDs'] as $mailingID) {
							$mailingTable->reset();
							// Check that the row actually exists
							if (!$mailingTable->load($mailingID)) {
								if ($error = $mailingTable->getError()) {
									// Fatal error
									$return['messages']['danger'][] = $error;
									break;
								} else {
									// Not fatal error
									$return['messages']['warning'][] = JText::sprintf('JLIB_APPLICATION_ERROR_BATCH_MOVE_ROW_NOT_FOUND', $mailingID);
									continue;
								}
							}
							$emaildatamailing = $mailingModel->prepareMail($mailingTable, '', $mailingTemplate);
							$mailInfos[] = $emaildatamailing['tekst'];
							unset($allMailingIDs[$mailingID]);
							$mailedMailingIDs[] = $mailingID;
						}
						//userinfo
						$fields = array_keys($profile->toArray());
						foreach ($fields as $name) {
							$subjectTemplate = str_replace('{user_' . $name . '}', $profile->get($name. ''), $subjectTemplate);
							$totalTemplate = str_replace('{user_' . $name . '}', $profile->get($name, ''), $totalTemplate);
						}
						//template compleet
						$totalTemplate = str_replace('{aanhef}', BixmailingHelper::getAanhef($aKlantenMails['user_id']), $totalTemplate);
						$totalTemplate = str_replace('{mailing_aantal}', count($mailInfos), $totalTemplate);
						$totalTemplate = str_replace('{mailing_infos}', implode('', $mailInfos), $totalTemplate);

						$emaildata = BixHelper::getMailBase($user->email);
						$emaildata['email'] = $aKlantenMails['email'];
						$emaildata['onderwerp'] = $subjectTemplate;
						$emaildata['tekst'] = $totalTemplate;

						//stuur mail en log mails/mailings
						if (BixHelper::sendMail($emaildata)) {
							foreach ($aKlantenMails['sendMailingIDs'] as $mailingID) {
								$mailingModel->createMaillog('TRACECOMBI', $aKlantenMails['user_id'], $mailingID, $emaildata);
							}
						} else {
							$return['messages']['warning'][] = 'Fout bij verzenden mail ' . $emaildata['email'];
						}
					}
					//statussen/state bijhouden
					$statusChanges['opgeslagen'] = array_merge($statusChanges['opgeslagen'], array_keys($allMailingIDs));
					$statusChanges['gemaild'] = array_merge($statusChanges['gemaild'], $mailedMailingIDs);

					$return['result'][] = $aKlantenMails['email'];

				}
				//statussen/states bijwerken
				foreach ($statusChanges as $newStatus => $mailingIDs) {
					if (count($mailingIDs)) {
						if (!$mailingModel->updateMailings($mailingIDs, array('status' => $newStatus, 'state' => 1))) {
							$return['messages']['danger'][] = $mailingModel->getError();
						}
						$return['messages']['success'][] = sprintf('Status van %d mailings aangepast naar %s.', count($mailingIDs), $newStatus);
					}
				}


				$return['success'] = true;
			} else {
				$return['messages']['warning'][] = 'Geen data!';
			}

		} else {
			$return['messages']['danger'][] = 'Geen adminrechten!';
		}
		print json_encode($return);

	}

	public function fileTree () {
		$user = JFactory::getUser();
		$html = '';
		if ($user->authorise('core.admin')) {
			$dir = JFactory::getApplication()->input->getString('dir', '');
			$root = BIX_PATH_UPLOADS;
			if (file_exists($root . $dir)) {
				$files = scandir($root . $dir);
				natcasesort($files);
				if (count($files) > 2) { /* The 2 accounts for . and .. */
					$html = "<ul class=\"jqueryFileTree\" style=\"display: none;\">";
					// All dirs
					foreach ($files as $file) {
						if (file_exists($root . $dir . $file) && $file != '.' && $file != '..' && is_dir($root . $dir . $file)) {
							$html .= "<li class=\"directory collapsed\"><a href=\"#\" rel=\"" . htmlentities($dir . $file) . "/\">" . htmlentities($file) . "</a></li>";
						}
					}
					// All files
					foreach ($files as $file) {
						if (file_exists($root . $dir . $file) && $file != '.' && $file != '..' && !is_dir($root . $dir . $file)) {
							$ext = preg_replace('/^.*\./', '', $file);
							$hash = BixHelper::downloadLink($root . $dir . $file, false, true);
							echo "<li class=\"file ext_$ext\"><a href=\"#\" rel=\"" . htmlentities($dir . $file) . "\" data-hash=\"$hash\">" . htmlentities($file) . "</a></li>";
						}
					}
					$html .= "</ul>";
				}
			}
		} else {
			$html = 'Geen adminrechten!';
		}
		echo $html;
	}

	public function fileView () {
		$user = JFactory::getUser();
		if ($user->authorise('core.admin')) {
			$hash = JFactory::getApplication()->input->getString('h', '');
			$fileInfo = BixHelper::getFile($hash);
			if (file_exists($fileInfo->filePath)) {
				$html = '<pre>';
				$html .= file_get_contents($fileInfo->filePath);
				$html .= '</pre>';
			} else {
				$html = 'Bestand niet gevonden!';
			}
		} else {
			$html = 'Geen adminrechten!';
		}
		echo $html;
	}


	public function postcodecheck () {
		$data = JFactory::getApplication()->input->getArray(array('pcode'=>'string','huisnr'=>'string','toev'=>'string'));
		$return = BixPostcodecheck::lookup($data['pcode'],$data['huisnr'],$data['toev']);
		print json_encode($return);

	}


}