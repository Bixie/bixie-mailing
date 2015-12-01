<?php

/**
 * com_bixmailing - Mailings for Joomla
 * @author    Matthijs Alles
 * @copyright 2014 Bixie.nl
 */
class BixExport extends JObject {

	/**
	 * @param $data
	 * @param $filename
	 * @return bool
	 */
	public function labellite ($data, $filename) {
		if (!count($data)) return false; //are ya'foolin' with me??
		$labelMap = array();
		$date = JHtml::_('date', 'now', 'd-m-Y H:i:s');
		$file = BixXls::newFile(array(
			'title' => JText::sprintf('COM_BIXMAILING_MAILING_EXPORT_XLS_TITLE_SPR', 'Labellite', $date),
			'subject' => JText::sprintf('COM_BIXMAILING_MAILING_EXPORT_XLS_SUBJECT_SPR', 'Labellite'),
			'description' => JText::_('COM_BIXMAILING_MAILING_EXPORT_XLS_DESCR')
		));
		//init tab
		$tab = BixXls::newTab('export Labellite');

		//first row
		$row = BixXls::newRow();
		$rawLabels = array_keys($data[0]);
		$col = 0;
		foreach ($rawLabels as $rawLabel) {
			$params = array();
			if (in_array($rawLabel, array('Pakketnummer', 'GP_nummer'))) {
				$params['colwidth'] = 500;
			}
			$cellParams = array();
			if (in_array($rawLabel, array('Pakketnummer', 'GP_nummer', 'Track_ID'))) {
				$cellParams['numberFormat'] = 'integer';
			}
			$labelMap[$col] = $cellParams;
			$row->cell(BixXls::newCell($rawLabel, $params));
			$col++;
		}
		$tab->row($row);
		foreach ($data as $rawRow) {
			$row = BixXls::newRow();
			$col = 0;
			foreach ($rawRow as $value) {
				$params = $labelMap[$col];
				$row->cell(BixXls::newCell($value, $params));
				$col++;
			}
			$tab->row($row);
		}

		$file->tab($tab);

		return $this->_createXls($file, $filename);
	}

	/**
	 * @param $data
	 * @param $filename
	 * @return bool
	 */
	public function postnl ($data, $filename) {
		if (!count($data)) return false; //are ya'foolin' with me??
		$labelMap = array();
		$date = JHtml::_('date', 'now', 'd-m-Y H:i:s');
		$file = BixXls::newFile(array(
			'title' => JText::sprintf('COM_BIXMAILING_MAILING_EXPORT_XLS_TITLE_SPR', 'PostNL', $date),
			'subject' => JText::sprintf('COM_BIXMAILING_MAILING_EXPORT_XLS_SUBJECT_SPR', 'PostNL'),
			'description' => JText::_('COM_BIXMAILING_MAILING_EXPORT_XLS_DESCR')
		));
		//init tab
		$tab = BixXls::newTab('export PostNL');

		//first row
		$row = BixXls::newRow();
		$rawLabels = array_keys($data[0]);
		$col = 0;
		foreach ($rawLabels as $rawLabel) {
			$params = array();
			if (in_array($rawLabel, array('KO_OrderNummer', 'KOR_KlantOrderRegelNummer', 'KOR_PG_KlantAanbiederId'))) {
				$params['colwidth'] = 500;
			}
			$cellParams = array();
			if (in_array($rawLabel, array('KO_OrderNummer', 'KOR_KlantOrderRegelNummer', 'KOR_PG_KlantAanbiederId'))) {
				$cellParams['numberFormat'] = 'integer';
			}
			$labelMap[$col] = $cellParams;
			$row->cell(BixXls::newCell($rawLabel, $params));
			$col++;
		}
		$tab->row($row);
		foreach ($data as $rawRow) {
			$row = BixXls::newRow();
			$col = 0;
			foreach ($rawRow as $value) {
				$params = $labelMap[$col];
				$row->cell(BixXls::newCell($value, $params));
				$col++;
			}
			$tab->row($row);
		}

		$file->tab($tab);

		return $this->_createXls($file, $filename);
	}

	/**
	 * @param XlsFile $file
	 * @param string  $destinationPath string path of file to create
	 * @return bool
	 */
	protected function _createXls (XlsFile $file, $destinationPath) {
		$xlsHelper = new BixXls($file);
		return $xlsHelper->createXls($destinationPath);
	}

}
