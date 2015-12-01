<?php
/* *
 *	BixieMailing
 *  xls.php.php
 *	Created on 12-3-14 4:37
 *  
 *  @author Matthijs
 *  @copyright Copyright (C)2014 Bixie.nl
 *
 */

// No direct access
defined('_JEXEC') or die;

/**
 * Class BixXls
 */
class BixXls extends JObject {
	/**
	 * @var XlsFile
	 */
	protected $file;

	/**
	 * @param XlsFile $file
	 */
	function __construct (XlsFile $file) {
		$this->file = $file;
	}

	/**
	 * @param null  $value
	 * @param array $params
	 * @return XlsCell
	 */
	public static function newCell ($value = null, $params = array()) {
		return new XlsCell($value, $params);
	}

	/**
	 * @param array $cells
	 * @param array $params
	 * @return XlsRow
	 */
	public static function newRow ($cells = array(), $params = array()) {
		return new XlsRow($cells, $params);
	}

	/**
	 * @param string $name
	 * @param array  $params
	 * @internal param array $rows
	 * @return XlsTab
	 */
	public static function newTab ($name, $params = array()) {
		return new XlsTab($name, $params);
	}

	/**
	 * @param $meta array
	 * @return XlsFile
	 */
	public static function newFile ($meta) {
		return new XlsFile($meta);
	}

	/**
	 * @param  string $destinationPath
	 * @return bool
	 */
	public function createXls ($destinationPath) {

		/** PHPExcel */
		include BIX_PATH_ADMIN_HELPERS . '/phpexcel/PHPExcel.php';
		include BIX_PATH_ADMIN_HELPERS . '/phpexcel/PHPExcel/Writer/Excel2007.php';

		try {
			// Create new PHPExcel object
			$objPHPExcel = new PHPExcel();

			// Set properties
			$objPHPExcel->getProperties()->setCreator("Bixie Mailings");
			$objPHPExcel->getProperties()->setLastModifiedBy("Bixie Mailings");
			$objPHPExcel->getProperties()->setTitle($this->file->meta['title']);
			$objPHPExcel->getProperties()->setSubject($this->file->meta['subject']);
			$objPHPExcel->getProperties()->setDescription($this->file->meta['description']);
			$i=0;
			foreach ($this->file->getTabs() as $tab) {
				$objPHPExcel->setActiveSheetIndex($i);
				$nr = 1;
				foreach ($tab->getRows() as $row) {
					$l = 'A';
					foreach ($row->getCells() as $cell) {
						$objPHPExcel->getActiveSheet()->SetCellValue($l.$nr, $cell->getValue());
						if (!empty($cell->params['numberFormat'])) {
							$objPHPExcel->getActiveSheet()->getStyle($l . $nr)->getNumberFormat()
								->setFormatCode($this->_getNumberFormat($cell->params['numberFormat']));
						}
						// edit sheet
						$objPHPExcel->getActiveSheet()->setTitle($tab->getName());
						if (!empty($cell->params['colwidth'])) {
							$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth($cell->params['colwidth']);
						} else {
							$objPHPExcel->getActiveSheet()->getColumnDimension('B')->setAutoSize(true);
						}
						$l++;//++!!
					}
					$nr++;
				}

				$i++;
			}



			// Add some data
			$i=0;
// Save Excel 2007 file
			$objWriter = new PHPExcel_Writer_Excel2007($objPHPExcel);
			$objWriter->save($destinationPath);

		} catch (PHPExcel_Writer_Exception $e) {
			$this->setError($e->getMessage());
		}

		if (!file_exists($destinationPath)) {
			return false;
		}
		return true;
	}

	/**
	 * @param $format
	 * @return string
	 */
	private function _getNumberFormat ($format) {
		$constants = array(
			'general' => PHPExcel_Style_NumberFormat::FORMAT_GENERAL,
			'integer' => PHPExcel_Style_NumberFormat::FORMAT_NUMBER,
			'float' => PHPExcel_Style_NumberFormat::FORMAT_NUMBER_00,
			'date' => PHPExcel_Style_NumberFormat::FORMAT_DATE_DMYMINUS,
			'datetime' => PHPExcel_Style_NumberFormat::FORMAT_DATE_XLSX22,
			'euro' => PHPExcel_Style_NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE
		);
		return isset($constants[$format]) ? $constants[$format] : $constants['general'];
	}
}

/**
 * Class XlsCell
 */
class XlsCell {
	/**
	 * @var
	 */
	public $value;
	/**
	 * @var
	 */
	public $params;

	/**
	 * @param $value
	 * @param $params
	 */
	function __construct ($value = null, $params = array()) {
		$this->value = $value;
		$this->params = $params;
	}

	/**
	 * @return mixed
	 */
	public function getValue () {
		return $this->value;
	}

}

/**
 * Class XlsRow
 */
class XlsRow {
	/**
	 * @var array
	 */
	protected $cells;
	/**
	 * @var array
	 */
	public $params;

	/**
	 * @param array $params
	 */
	function __construct ($params = array()) {
		$this->params = $params;
	}

	/**
	 * @return array
	 */
	public function getCells () {
		return $this->cells;
	}

	/**
	 * @param XlsCell $cell
	 */
	public function cell (XlsCell $cell) {
		$this->cells[] = $cell;
	}
}

/**
 * Class XlsTab
 */
class XlsTab {
	/**
	 * @var array
	 */
	public $name;
	/**
	 * @var array
	 */
	protected $rows;

	/**
	 * @var
	 */
	public $params;

	/**
	 * @param string $name
	 * @param array  $params
	 */
	function __construct ($name, $params = array()) {
		$this->name = $name;
		$this->params = $params;
	}

	/**
	 * @return array
	 */
	public function getRows () {
		return $this->rows;
	}

	/**
	 * @return array
	 */
	public function getName () {
		//todo make string safe
		return $this->name;
	}

	/**
	 * @param XlsRow $row
	 */
	public function row (XlsRow $row) {
		$this->rows[] = $row;
	}

}


/**
 * Class XlsFile
 */
class XlsFile {
	/**
	 * @var array
	 */
	public $meta;
	/**
	 * @var array
	 */
	protected $tabs;

	/**
	 * @param $meta array
	 */
	function __construct ($meta) {
		$this->meta = $meta;
	}

	/**
	 * @return array
	 */
	public function getTabs () {
		return $this->tabs;
	}

	/**
	 * @param XlsTab $tab
	 */
	public function tab (XlsTab $tab) {
		$this->tabs[] = $tab;
	}
}