<?php
/**
 *	com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *	Bixie.nl
 *
 */

require_once JPATH_LIBRARIES . '/tcpdf/config/lang/nld.php';
require_once JPATH_LIBRARIES . '/tcpdf/tcpdf.php';


abstract class BixPdf {

	public static function createInvoice($filePath,BixBestel $bixBestel,$factuurData) {
		$jconfig = JFactory::getConfig();
		$pdf = self::createMailingPdf();
	
		$pdf->SetTitle(JText::sprintf('COM_BIXMAILING_FACTUUR_TITLE_SPR',$jconfig->getValue('config.sitename')));
		$pdf->SetSubject(JText::sprintf('COM_BIXMAILING_FACTUUR_SUBJECT_SPR',$bixBestel->get('bestelID'),$jconfig->getValue('config.sitename')));
		$pdf->AddPage();

		$html = BixHelper::renderTemplate('raw','factuur','',array('bixBestel'=>$bixBestel,'factuurData'=>$factuurData),true);
		
		$pdf->writeHTML($html, false, false, true, false, '');

		
		
		//Close and output PDF document
		$pdf->Output($filePath, 'F');
		return $pdf->Output($filePath, 'S');
		
	}
	protected static function createMailingPdf() {
		$jconfig = JFactory::getConfig();
		// create new PDF document
		$pdf = new BixTCPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
		// set document information
		$pdf->SetCreator(PDF_CREATOR);
		$pdf->SetAuthor('Bixie Mailing');
		$pdf->SetKeywords('');
		
		$logoPath = JPATH_ROOT.'/'.BixTools::config('mail.pdf.headerLogo','media/printshop/system/noimage-128.jpg');
		$logoWidth = BixTools::config('mail.pdf.headerLogoWidth',0);
		$imageInfo = self::testImage($logoPath);		
		$header_logo = self::toPdfPath($logoPath);
		$header_logo_width = $logoWidth;
		$header_title = BixTools::config('mail.pdf.headerTitle','');
		$header_string = BixTools::config('mail.pdf.headerSubtitle','');
		$header_text_color = self::hex2RGB(BixTools::config('mail.pdf.headerColor','#999999'));
		$header_line_color = self::hex2RGB(BixTools::config('mail.pdf.headerLineColor','#000000'));
		$seperator = BixTools::config('mail.pdf.footerSeperator','|');
		$pdf->SetHeaderData($header_logo, $header_logo_width, $header_title, $header_string, $header_text_color, $header_line_color);
		if (BixTools::config('mail.pdf.footerAdres',0)) {
			$footerData= array();
			if (BixTools::config('algemeen.gegevens.adres',0)) $footerData[] = BixTools::config('algemeen.gegevens.adres',0);
			if (BixTools::config('algemeen.gegevens.plaats',0)) $footerData[] = BixTools::config('algemeen.gegevens.postcode','').' '.BixTools::config('algemeen.gegevens.plaats',0);
			if (BixTools::config('algemeen.gegevens.telefoon',0)) $footerData[] = BixTools::config('algemeen.gegevens.telefoon',0);
			if (BixTools::config('algemeen.gegevens.email',0)) $footerData[] = BixTools::config('algemeen.gegevens.email',0);
			$footerData[] = JURI::getInstance()->toString(array('host'));
			$footerHtml = '<p>'.implode(' '.$seperator.' ',$footerData).'</p>';
			$pdf->footerHtml = $footerHtml;
		}
		if (BixTools::config('mail.pdf.footerBank',0)) {
			$footerData= array();
			if (BixTools::config('algemeen.gegevens.bank',0)) $footerData[] = BixTools::config('algemeen.gegevens.bank',0);
			if (BixTools::config('algemeen.gegevens.kvk',0)) $footerData[] = 'KvK: '.BixTools::config('algemeen.gegevens.kvk',0);
			if (BixTools::config('algemeen.gegevens.btw',0)) $footerData[] = BixTools::config('algemeen.gegevens.btw',0);
			$footerHtml = '<p>'.implode(' '.$seperator.' ',$footerData).'</p>';
			$pdf->footerHtml .= $footerHtml;
		}
		$pdf->setHeaderFont(array(BixTools::config('mail.pdf.headerFont','helvetica'), '', BixTools::config('mail.pdf.headerFontSize',14)));
		$pdf->setFont(BixTools::config('mail.pdf.font','helvetica'), '', BixTools::config('mail.pdf.fontSize',10));
		$pdf->setFooterFont(array(BixTools::config('mail.pdf.font','helvetica'), '', BixTools::config('mail.pdf.fontSize',10)));
		$pdf->setCellHeightRatio(1.7);
		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		//set margins
		$pdf->SetMargins(BixTools::config('mail.pdf.marginLeft',20), BixTools::config('mail.pdf.marginTop',10), BixTools::config('mail.pdf.marginRight',20));
		$pdf->SetHeaderMargin(BixTools::config('mail.pdf.marginHeader',25));
		$pdf->SetFooterMargin(BixTools::config('mail.pdf.marginFooter',25));

		//set auto page breaks 
		$pdf->SetAutoPageBreak(TRUE, BixTools::config('mail.pdf.marginBottom',10));

		//set image scale factor
		$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
		
		//set some language-dependent strings
		global $l;
		$pdf->setLanguageArray($l);

		return $pdf;		
	}
	/**
	 * Convert a hexa decimal color code to its RGB equivalent
	 *
	 * @param string $hexStr (hexadecimal color value)
	 * @param boolean $returnAsString (if set true, returns the value separated by the separator character. Otherwise returns associative array)
	 * @param string $seperator (to separate RGB values. Applicable only if second parameter is true.)
	 * @return array or string (depending on second parameter. Returns False if invalid hex color value)
	 */                                                                                                 
	public static function hex2RGB($hexStr, $returnAsString = false, $seperator = ',') {
		$hexStr = preg_replace("/[^0-9A-Fa-f]/", '', $hexStr); // Gets a proper hex string
		$rgbArray = array();
		if (strlen($hexStr) == 6) { //If a proper hex code, convert using bitwise operation. No overhead... faster
			$colorVal = hexdec($hexStr);
			$rgbArray['red'] = 0xFF & ($colorVal >> 0x10);
			$rgbArray['green'] = 0xFF & ($colorVal >> 0x8);
			$rgbArray['blue'] = 0xFF & $colorVal;
		} elseif (strlen($hexStr) == 3) { //if shorthand notation, need some string manipulations
			$rgbArray['red'] = hexdec(str_repeat(substr($hexStr, 0, 1), 2));
			$rgbArray['green'] = hexdec(str_repeat(substr($hexStr, 1, 1), 2));
			$rgbArray['blue'] = hexdec(str_repeat(substr($hexStr, 2, 1), 2));
		} else {
			return false; //Invalid hex color code
		}
		return $returnAsString ? implode($seperator, $rgbArray) : $rgbArray; // returns the rgb string or the associative array
	} 	
	
	public static function toPdfPath($filePath) {
		if (!file_exists($filePath)) { 
			return K_BLANK_IMAGE;
		}
		$tcpdfPath = str_replace(JPATH_ROOT,'',$filePath);
		return '../../..'.$tcpdfPath;
	} 	
	
	public static function testImage($filePath) {
		$return = array('resolution'=>'Onbekend','filePath'=>$filePath);
		if (file_exists($filePath) && class_exists('imagick')) {
			$im = new imagick($filePath);
			$imgInfo = $im->identifyImage();
			if ($imgInfo['resolution']['x'] != $imgInfo['resolution']['y'] || !$imgInfo['resolution']['x']) {
				$return['resolution'] = 'Fout in resolutie: x!=y';
			} else {
				switch ($imgInfo['units']) {
					case 'PixelsPerCentimeter':
						$return['resolution'] = round($imgInfo['resolution']['x'] * 2.54);
						$return['realwidth'] = round(($imgInfo['geometry']['width'] / $imgInfo['resolution']['x'])*10);
						$return['realheight'] = round(($imgInfo['geometry']['height'] / $imgInfo['resolution']['x'])*10);
					break;
					case 'PixelsPerInch':
					case 'Undefined':
					default:
						$return['resolution'] = round($imgInfo['resolution']['x']);
						$return['realwidth'] = round((2540 / ($imgInfo['geometry']['width'] / $imgInfo['resolution']['x'])));
						$return['realheight'] = round((2540 / ($imgInfo['geometry']['height'] / $imgInfo['resolution']['x'])));
					break;
				}
			}
	//pr($imgInfo);
			if (stripos($imgInfo['format'],'JPEG') === false) {
				$nameArr = explode('.',$filePath);
				$ext = array_pop($nameArr);
				$return['filePath'] = implode('.',$nameArr).'.jpg';
				if (!file_exists($return['filePath'])) {
					if (stripos($imgInfo['format'],'PNG') !== false) {
						$newImage = new IMagick();
						$newImage->newImage($im->getImageWidth(), $im->getImageHeight(), new ImagickPixel("white"));
						$newImage->compositeImage($im, imagick::COMPOSITE_OVER, 0, 0);
					} else {
						$newImage = $im;
					}
					$im->setImageFormat( "jpg" );
					$im->writeImage($return['filePath']);
					$newImage->clear();
					$newImage->destroy();
				}
			}
			$im->clear();
			$im->destroy();
		} else {
			return false;
		}
		return $return;
	} 	
}



class BixTCPDF extends TCPDF {

	public $footerHtml;

	public function __construct($orientation='P', $unit='mm', $format='A4', $unicode=true, $encoding='UTF-8', $diskcache=false, $pdfa=false) {
		parent::__construct($orientation, $unit, $format, $unicode, $encoding, $diskcache, $pdfa);
	}
	
	/**
	 * This method is used to render the page header.
	 * It is automatically called by AddPage() and could be overwritten in your own inherited class.
	 * @public
	 */ 
	public function Header() {
		if ($this->header_xobjid < 0) {
			// start a new XObject Template
			$this->header_xobjid = $this->startTemplate($this->w, $this->tMargin);
			$headerfont = $this->getHeaderFont();
			$headerdata = $this->getHeaderData();
			$this->y = $this->header_margin;
			if ($this->rtl) {
				$this->x = $this->w - $this->original_rMargin;
			} else {
				$this->x = $this->original_lMargin;
			}
			if (($headerdata['logo']) AND ($headerdata['logo'] != K_BLANK_IMAGE)) {
				$imgtype = $this->getImageFileType(K_PATH_IMAGES.$headerdata['logo']);
				if (($imgtype == 'eps') OR ($imgtype == 'ai')) {
					$this->ImageEps(K_PATH_IMAGES.$headerdata['logo'], '', '', $headerdata['logo_width']);
				} elseif ($imgtype == 'svg') {
					$this->ImageSVG(K_PATH_IMAGES.$headerdata['logo'], '', '', $headerdata['logo_width']);
				} else {
					$this->Image(K_PATH_IMAGES.$headerdata['logo'], '', '', $headerdata['logo_width']);
				}
				$imgy = $this->getImageRBY();
			} else {
				$imgy = $this->y;
			}
			$cell_height = round(($this->cell_height_ratio * $headerfont[2]) / $this->k, 2);
			// set starting margin for text data cell
			if ($this->getRTL()) {
				$header_x = $this->original_rMargin + ($headerdata['logo_width'] * 1.1);
			} else {
				$header_x = $this->original_lMargin + ($headerdata['logo_width'] * 1.1);
			}
			$cw = $this->w - $this->original_lMargin - $this->original_rMargin - ($headerdata['logo_width'] * 1.1);
			$this->SetTextColorArray($this->header_text_color);
			// header title
			$this->SetFont($headerfont[0], 'B', $headerfont[2] + 1);
			$this->SetX($header_x);
			$this->Cell($cw, $cell_height, $headerdata['title'], 0, 1, '', 0, '', 0);
			// header string
			$this->SetFont($headerfont[0], $headerfont[1], $headerfont[2]);
			$this->SetX($header_x);
			$this->MultiCell($cw, $cell_height, $headerdata['string'], 0, '', 0, 1, '', '', true, 0, false, true, 0, 'T', false);
			// print an ending header line
			$this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $headerdata['line_color']));
			$this->SetY((2.835 / $this->k) + max($imgy, $this->y));
			if ($this->rtl) {
				$this->SetX($this->original_rMargin);
			} else {
				$this->SetX($this->original_lMargin);
			}
			$this->Cell(($this->w - $this->original_lMargin - $this->original_rMargin), 0, '', 'T', 0, 'C');
			$this->endTemplate();
		}
// pr($headerdata,$imgtype);
		// print header template
		$x = 0;
		$dx = 0;
		if (!$this->header_xobj_autoreset AND $this->booklet AND (($this->page % 2) == 0)) {
			// adjust margins for booklet mode
			$dx = ($this->original_lMargin - $this->original_rMargin);
		}
		if ($this->rtl) {
			$x = $this->w + $dx;
		} else {
			$x = 0 + $dx;
		}
		$this->printTemplate($this->header_xobjid, $x, 0, 0, 0, '', '', false);
		if ($this->header_xobj_autoreset) {
			// reset header xobject template at each page
			$this->header_xobjid = -1;
		}
	}
	// Page footer
	public function Footer() {
		$cur_y = $this->y;
		$this->SetTextColorArray($this->footer_text_color);
		// set style for cell border
		$line_width = 0;//(0.85 / $this->k);
		$this->SetLineStyle(array('width' => $line_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => $this->footer_line_color));
		$w_page = isset($this->l['w_page']) ? $this->l['w_page'].' ' : '';
		if (empty($this->pagegroups)) {
			$pagenumtxt = $w_page.$this->getAliasNumPage().' / '.$this->getAliasNbPages();
		} else {
			$pagenumtxt = $w_page.$this->getPageNumGroupAlias().' / '.$this->getPageGroupAlias();
		}
		$this->SetY($cur_y);
		if (!empty($this->footerHtml)) {
			$this->writeHTML($this->footerHtml, false, false, true, false, 'C');
		}
		$this->SetY($this->y+2);
		// Print page number
		if ($this->getRTL()) {
			$this->SetX($this->original_rMargin);
			$this->Cell(0, 0, $pagenumtxt, 'T', 0, 'L');
		} else {
			$this->SetX($this->original_lMargin);
			$this->Cell(0, 0, $this->getAliasRightShift().$pagenumtxt, 'T', 0, 'R');
		}
	}

}
