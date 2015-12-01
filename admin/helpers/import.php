<?php

/**
 * com_bixmailing - Mailings for Joomla
 * @author    Matthijs Alles
 * @copyright 2014 Bixie.nl
 */
class BixImport extends JObject {

	protected $dataXrefPostnl = array(
		'fields' => array(
			'KO_KlantOrderDatumTijd' => 'aangemeld',
			'KOR_PG_GewichtPerStuk' => 'gewicht',
			'KOR_PG_Productnaam' => 'type',
			'ZEN_ADR_NaamVeld1' => 'adresnaam',
			'ZEN_ADR_Straatnaam' => 'straat',
			'ZEN_ADR_Huisnummer' => 'huisnummer',
			'ZEN_ADR_HuisnummerToevoeging' => 'huisnummer_toevoeging',
			'ZEN_ADR_PostCode' => 'postcode',
			'ZEN_ADR_Plaats' => 'plaats',
			'ZEN_ADR_LandCode' => 'land',
			'ZEN_COL_BarcodeNL' => 'trace_nl',
			'ZEN_COL_BarcodeBTL' => 'trace_btl',
			'KOR_PG_ORIK1Waarde' => 'referentie'
		),
		'params' => array(
			'KOR_PG_Zonecode' => 'zonecode',
			'KO_OrderNummer' => 'ordernummer',
			'KOR_KlantOrderRegelNummer' => 'klantordernummer'
		)
	);

	protected $dataXrefLabellite = array(
		'fields' => array(
			'Printdatum' => 'aangemeld',
			'Gewicht' => 'gewicht',
			'Verzendsys' => 'type',
			'Naam' => 'adresnaam',
			'Straat' => 'straat',
			'Nr.' => 'huisnummer',
			'Toev.' => 'huisnummer_toevoeging',
			'Postcode' => 'postcode',
			'Plaats' => 'plaats',
			'Lan' => 'land',
			'Track_&_Trace_link' => 'trace_url',
			'Pakketnummer' => 'trace_nl',
			'Track_ID' => 'trace_btl',
			'GP_nummer' => 'trace_gp',
			'Referentie' => 'referentie'
		),
		'params' => array(
			'Pakketnummer' => 'ordernummer',
			'GP_nummer' => 'klantordernummer',
			'vCashSeric' => 'vCashSeric',
			'Exp' => 'Exp',
			'SMS' => 'SMS'
		)
	);

	/**
	 * inlezen postnl csv export
	 * @param $filepath
	 * @param $messages
	 * @return array|bool
	 */
	public function postnl ($filepath, &$messages) {
		// init vars
		$mailingModel = BixTools::getModel('mailing');
		$filename = basename($filepath);
		$dataRows = array();
		if (!file_exists($filepath)) {
			$this->setError('Exportbestand niet gevonden!');
		} else {
			$csvData = $this->_readCSVFile($filepath);
			if (!count($csvData)) {
				$this->setError('Fout bij inlezen CSV!');
			} else {
				//omzetten naar assoc
				$csvlabels = array_shift($csvData);
				$j = 0;
				foreach ($csvData as $csvrow) {
					$assocData = array();
					$bixMailing = new BixMailingMailing();
					$bixMailing->set('vervoerder', 'POSTNL');
					$bixMailing->set('importbestand', $filename);
					$params = array();

					$iNum = count($csvrow);
					for ($c = 0; $c < $iNum; $c++) {
						$key = trim($csvlabels[$c]);
						$value = trim($csvrow[$c]);
						$set = false;
						if (isset($this->dataXrefPostnl['fields'][$key])) {
							$bixMailing->set($this->dataXrefPostnl['fields'][$key], $value);
							$set = true;
						}
						if (isset($this->dataXrefPostnl['params'][$key])) {
							$params[$key] = $value;
							$set = true;
						}
						$bixMailing->set('params', $params);
						//rawdata
						if ($set) $assocData[$key] = $value;
					}
					//type
					switch ($bixMailing->type) {
						case 'Aangetekende Brief buitenland':
							$bixMailing->type = 'aangetekend_buitenland';
							break;
						case 'Aangetekende Brief':
							$bixMailing->type = 'aangetekend';
							break;
						case 'Aangetekend Pakket (0-10 kg)':
							$bixMailing->type = 'aangetekend_pakket_10';
							break;
						case 'Aangetekend Pakket(10-30kg)':
							$bixMailing->type = 'aangetekend_pakket_10_30';
							break;
						default:
							break;
					}
					//date
					$bixMailing->aangemeld = JFactory::getDate($bixMailing->aangemeld)->toSql();
					//url
					if (!empty($bixMailing->trace_btl)) {
						$bixMailing->trace_url = 'http://www.postnlpakketten.nl/klantenservice/tracktrace/basicsearch.aspx?lang=nl&B=' .
							$bixMailing->trace_btl . '&I=True';

					} else {
						$bixMailing->trace_url = 'http://www.postnlpakketten.nl/klantenservice/tracktrace/basicsearch.aspx?lang=nl&B=' .
							$bixMailing->trace_nl . '&P=' . strtoupper(str_replace(' ', '', $bixMailing->postcode));
					}
					$bixMailing->set('aang', (substr($bixMailing->type, 0, 11) == 'aangetekend'));
					$bixMailing->set('state', 0);
					$bixMailing->set('klantnummer', 0);
					$bixMailing->set('status', 'incompleet');
					$user_id = $bixMailing->findUser();
					if ($user_id) {
						$bixMailing->set('user_id', $user_id);
						$bixMailing->set('klantnummer', BixmailingHelper::getKlantnummer($user_id));
						$bixMailing->set('status', 'nieuw');
						$naam = $mailingModel->renderMailingNaam(0, $user_id, $bixMailing->type, $messages);
					} else {
						$naam = 'TEMP_' . strtoupper($bixMailing->type) . '-' . JFactory::getDate()->format('Ymd') . '-' . ($j + 1);
					}
					$bixMailing->set('naam', $naam);

					if (!$bixMailing->save(null, $messages)) {
						$messages['danger'][] = 'Opslaan trace ' . $bixMailing->trace_nl . ', referentie ' . $bixMailing->referentie . ' mislukt';
					}
					$dataRows[] = $assocData;
					$j++;
				}
				return $dataRows;
			}
		}
		return false;
	}

	/**
	 * Labellite spatiedingesfile fatsoeneren
	 * This is a mess!!
	 * @param $filepath
	 * @param $messages
	 * @return array|bool
	 */
	public function labellite ($filepath, &$messages) {
		// init vars
		$mailingModel = BixTools::getModel('mailing');
		$filename = basename($filepath);
		$dataRows = array();
		$labelData = array();
		$labels = array('Pakketnummer', 'Printdatum', 'Referentie', 'Gewicht', 'Verzendsys', 'Verpa', 'GP nummer', 'Track ID'
		, 'Uni-Ship', 'EOD', 'Track & Trace link', 'Naam', 'Naam 2', 'Naam 3', 'Straat', 'Nr.', 'Toev.', 'Postcode', 'Plaats', 'Lan'
		, 'Contactpersoon', 'Telefoon nr', 'Naam', 'Naam 2', 'Straat', 'Nr.', 'Toev.', 'Postcode', 'Plaats', 'Lan'
		, 'Telefoon nr', 'E-mail', 'Afzender', 'vCashServic', 'Exp', 'SMS');

		if (!file_exists($filepath)) {
			$messages['danger'][] = 'Exportbestand niet gevonden!';
		} else {
			$lines = explode("\n", file_get_contents($filepath));
			$return['messages']['danger'][] = 'Exportbestand Label Lite is leeg!';
			if (count($lines) < 2) {
				$messages['danger'][] = 'Exportbestand Label Lite is leeg!';
			} else {
				//labelpos en legth uitzoeken
				$labelLine = array_shift($lines);
				for ($i = 0; $i < count($labels); $i++) {
					$startPos = $i > 0 ? stripos($labelLine, $labels[$i], $labelData[$i - 1]['start']) : 0;
					$label = array(
						'naam' => str_replace(' ', '_', $labels[$i]),
						'start' => $startPos,
						'length' => 0
					);
					if ($i > 0) $labelData[$i - 1]['length'] = $startPos - $labelData[$i - 1]['start'];
					$labelData[$i] = $label;
				}
				//laatste lengte
				$labelData[$i - 1]['length'] = strlen($labelLine) - $labelData[$i - 1]['start'];
//pr($labelData);
				//data vullen
				$nrLines = count($lines);
				for ($j = 0; $j < $nrLines; $j++) {
					if (trim($lines[$j]) == '') continue; //skip lege regels
					$assocData = array();
					$setFields = array();
					$bixMailing = new BixMailingMailing();
					$bixMailing->set('vervoerder', 'GLS');
					$bixMailing->set('importbestand', $filename);
					$params = array();
					foreach ($labelData as $label) {
						$key = (isset($setFields[$label['naam']])) ? 'afzender_' . $label['naam'] : $label['naam'];
						$value = trim(substr($lines[$j], $label['start'], $label['length']));
						$set = false;
						if (isset($this->dataXrefLabellite['fields'][$key])) {
							$bixMailing->set($this->dataXrefLabellite['fields'][$key], $value);
							$set = true;
						}
						if (isset($this->dataXrefLabellite['params'][$key])) {
							$params[$key] = $value;
							$set = true;
						}
						$bixMailing->set('params', $params);
						//rawdata
						if ($set) $assocData[$key] = $value;
						$setFields[$key] = $value; //mark key for doubles
					}
					//type
					switch ($bixMailing->type) {
						case 'Freight':
							$bixMailing->type = 'gls_freight';
							break;
						case 'Parcel':
							$bixMailing->type = 'gls';
							break;
						default:
							break;
					}
					//date
					$bixMailing->aangemeld = JFactory::getDate($bixMailing->aangemeld)->toSql();
					//weight to gram
					$bixMailing->gewicht *= 1000;

					$bixMailing->set('aang', (strlen($bixMailing->referentie) > 3 && substr($bixMailing->referentie, 0, 2) == '13'));
					$bixMailing->set('state', 0);
					$bixMailing->set('klantnummer', 0);
					$bixMailing->set('status', 'incompleet');
					$user_id = $bixMailing->findUser();
					if ($user_id) {
						$bixMailing->set('user_id', $user_id);
						$bixMailing->set('status', 'nieuw');
						$bixMailing->set('klantnummer', BixmailingHelper::getKlantnummer($user_id));
						$naam = $mailingModel->renderMailingNaam(0, $user_id, $bixMailing->type, $messages);
					} else {
						$naam = 'TEMP_' . strtoupper($bixMailing->type) . '-' . JFactory::getDate()->format('Ymd') . '-' . ($j + 1);
					}
					$bixMailing->set('naam', $naam);

					if (!$bixMailing->save(null, $messages)) {
						$messages['danger'][] = 'Opslaan trace ' . $bixMailing->trace_nl . ', referentie ' . $bixMailing->referentie . ' mislukt';
					}
					$dataRows[] = $assocData;
				}
			}
			return $dataRows;
		}
		return false;
	}

	/**
	 * Inlezen csv bestand
	 * @param $file
	 * @return array
	 */
	protected function _readCSVFile ($file) {
		//niks meer, niks minder
		ini_set("auto_detect_line_endings", true);
		$dataRows = array();
		$del = ';';
		if (($handle = fopen($file, "r")) !== FALSE) {
			while (($data = fgetcsv($handle, 10000, $del)) !== FALSE) {
				if ($data[0] == '') continue; //lege regel
				$dataRows[] = $data;
			}
			fclose($handle);
		}
		return $dataRows;
	}


}
