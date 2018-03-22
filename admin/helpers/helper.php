<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */

use \Michelf\MarkdownExtra;

/**
 * Class BixHelper
 */
abstract class BixHelper {

	/**
	 * @param string $view
	 * @param string $layout
	 * @param string $tpl
	 * @param array  $data
	 * @param bool   $forceFront
	 */
	public static function displayTemplate ($view, $layout, $tpl = '', $data = array(), $forceFront = false) {
		$context = new BixMailingViewContext();
		if (count($data)) {
			foreach ($data as $key => $var) {
				$context->$key = $var;
			}
		}
		echo $context->renderTemplate($view, $layout, $tpl, $forceFront);
	}

	/**
	 * @param string $view
	 * @param string $layout
	 * @param string $tpl
	 * @param array  $data
	 * @param bool   $forceFront
	 * @return string
	 */
	public static function renderTemplate ($view, $layout, $tpl = '', $data = array(), $forceFront = false) {
		$context = new BixMailingViewContext();
		if (count($data)) {
			foreach ($data as $key => $var) {
				$context->$key = $var;
			}
		}
		return $context->renderTemplate($view, $layout, $tpl, $forceFront);
	}

	/**
	 * @param $email
	 * @return array
	 */
	public static function getMailBase ($email) {
		$config = JFactory::getConfig();
		$maildata = array();
		$maildata['fromname'] = $config->get('fromname');
		$maildata['mailfrom'] = $config->get('mailfrom');
		$maildata['sitename'] = $config->get('sitename');
		$maildata['siteurl'] = JUri::root();
		$maildata['email'] = $email;
		$maildata['cc'] = array();
		$maildata['bcc'] = BixmailingHelper::getAdminEmails();
		$maildata['attachment'] = null;
		return $maildata;
	}

	/**
	 * mailen
	 * @param $maildata
	 * @return bool
	 */
	public static function sendMail ($maildata) {
		$maildata['tekst'] = str_replace('{site_url}', $maildata['siteurl'], $maildata['tekst']);
		$maildata['tekst'] = str_replace('{site_naam}', $maildata['sitename'], $maildata['tekst']);
		$maildata['bcc'] = JArrayHelper::getValue($maildata, 'bcc', []);
		$maildata['attachment'] = JArrayHelper::getValue($maildata, 'attachment', null);
		$body = MarkdownExtra::defaultTransform(nl2br($maildata['tekst']));
		//template
		if (file_exists(BIX_PATH_ADMIN_ASSETS . '/mailtemplate.html')) {
			$tmpl = file_get_contents(BIX_PATH_ADMIN_ASSETS . '/mailtemplate.html');
			$body = str_replace('{mail_tekst}', $body, $tmpl);
		}
		//from seperaten
		if (!is_array($maildata['email'])) {
			$maildata['email'] = explode(';', $maildata['email']);
		}
		//test mode
		$testmail = (count($maildata['email']) == 1 && in_array($maildata['email'][0], array('1000@home.nl', 'matthijs@bixie.nl', 'mallesbixie@gmail.com')));
		if (BixTools::config('testMail', true)) {
            if (!$testmail) {
                JFactory::getApplication()->enqueueMessage('mail blocked! :' . $maildata['email'][0]);
                return true;
            } else {
                $maildata['bcc'] = ['admin@bixie.nl'];
            }
		}
		//geen dubbele afzenders mogelijk in Joomla
        $maildata['cc'] = array_diff(array_unique($maildata['cc']), $maildata['email']);
        $maildata['bcc'] = array_diff(array_unique($maildata['bcc']), $maildata['email']);
        if ($doubles = array_intersect($maildata['cc'], $maildata['bcc'], $maildata['email'])) {
            $maildata['bcc'] = array_diff($maildata['bcc'], $doubles);
        }

		$result = JFactory::getMailer()->sendMail(
			$maildata['mailfrom'],
			$maildata['fromname'],
			$maildata['email'],
			$maildata['onderwerp'],
			$body,
			true,
			$maildata['cc'],
			$maildata['bcc'],
			$maildata['attachment']
		);

		return $result;
	}


	/*filehelpers*/
	/**
	 * @param string $hash
	 * @return stdClass
	 */
	public static function fileInfo ($hash) {
		$downloadObj = self::getFile($hash);
		$file = $downloadObj->filePath;
		$fileInfo = new stdClass;
		$fileInfo->fileName = basename($file);
		$fileInfo->fileNameShort = $fileInfo->fileName;
		if (strlen($fileInfo->fileNameShort) > 30) {
			$fileInfo->fileNameShort = substr($fileInfo->fileNameShort, 0, 16) . '...' . substr($fileInfo->fileNameShort, -10);
		}
		$fileInfo->fullPath = $file;
		$fileInfo->url = 'download?h=' . $hash;
		$nameArr = explode('.', $fileInfo->fileName);
		$fileInfo->fileExt = array_pop($nameArr);
		$fileInfo->fileNameRaw = implode('.', $nameArr);
		$fileInfo->isImage = false;
		if (in_array(strtolower($fileInfo->fileExt), array('png', 'jpg', 'gif', 'jpeg'))) {
			$fileInfo->isImage = true;
		}
		$fileInfo->file_size = @filesize($file);
		$fileInfo->format_size = BixHelper::getFileSize($fileInfo->file_size);
		$fileInfo->mimeType = BixHelper::getMimeType($file);

		return $fileInfo;
	}

	/**
	 * detect nasty IE
	 * @return int
	 */
	public static function bugIE (){
		return preg_match('/(?i)msie [3-8]/',$_SERVER['HTTP_USER_AGENT']);
	}

	/**
	 * @param string $path
	 * @return mixed|string
	 */
	public static function getMimeType ($path) {
		$mime_types = array(
			'txt' => 'text/plain',
			'htm' => 'text/html',
			'html' => 'text/html',
			'php' => 'text/html',
			'css' => 'text/css',
			'js' => 'application/javascript',
			'json' => 'application/json',
			'xml' => 'application/xml',
			'swf' => 'application/x-shockwave-flash',
			'flv' => 'video/x-flv',
			// images
			'png' => 'image/png',
			'jpe' => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'jpg' => 'image/jpeg',
			'gif' => 'image/gif',
			'bmp' => 'image/bmp',
			'ico' => 'image/vnd.microsoft.icon',
			'tiff' => 'image/tiff',
			'tif' => 'image/tiff',
			'svg' => 'image/svg+xml',
			'svgz' => 'image/svg+xml',
			// archives
			'zip' => 'application/zip',
			'rar' => 'application/x-rar-compressed',
			'exe' => 'application/x-msdownload',
			'msi' => 'application/x-msdownload',
			'cab' => 'application/vnd.ms-cab-compressed',
			// audio/video
			'mp3' => 'audio/mpeg',
			'qt' => 'video/quicktime',
			'mov' => 'video/quicktime',
			// adobe
			'pdf' => 'application/pdf',
			'psd' => 'image/vnd.adobe.photoshop',
			'ai' => 'application/postscript',
			'eps' => 'application/postscript',
			'ps' => 'application/postscript',
			// ms office
			'doc' => 'application/msword',
			'rtf' => 'application/rtf',
			'xls' => 'application/vnd.ms-excel',
			'ppt' => 'application/vnd.ms-powerpoint',
			'docx' => 'application/msword',
			'xlsx' => 'application/vnd.ms-excel',
			'pptx' => 'application/vnd.ms-powerpoint',
			// open office
			'odt' => 'application/vnd.oasis.opendocument.text',
			'ods' => 'application/vnd.oasis.opendocument.spreadsheet',
		);
		$nameArr = explode('.', $path);
		$ext = array_pop($nameArr);
		$realFile = file_exists($path);
		if ($realFile && function_exists('mime_content_type')) {
			$mimetype = mime_content_type($path);
			return $mimetype;

		} elseif ($realFile && function_exists('finfo_open')) {
			$finfo = finfo_open(FILEINFO_MIME);
			$mimetype = finfo_file($finfo, $path);
			finfo_close($finfo);
			return $mimetype;
		} elseif (array_key_exists($ext, $mime_types)) {
			return $mime_types[$ext];
		} else {
			return 'application/octet-stream';
		}
	}

	/**
	 * @param int $size
	 * @return string
	 */
	public static function getFileSize ($size) {
		if ((int)$size < 1024) {
			return (int)$size . ' bytes';
		} else if ((int)$size >= 1024 && (int)$size < 1048576) {
			return round(($size / 1024), 2) . ' kB';
		} else if ((int)$size >= 1048576) {
			return round(($size / 1048576), 2) . ' MB';
		}
		return '';
	}

	/**
	 * @param int $grams
	 * @return string
	 */
	public static function getWeigth ($grams) {
		if ((int)$grams < 1000) {
			return number_format($grams, 0, ',', '.') . ' gram';
		} else if ((int)$grams >= 1000 && (int)$grams < 1000000) {
			$dec = $grams % 1000 ? 2 : 0;
			return number_format(round(($grams / 1000), 2), $dec, ',', '.') . ' kg';
		} else if ((int)$grams >= 1000000) {
			return round(($grams / 1000000), 2) . ' t';
		}
		return '';
	}

	/**
	 * @param string $filePath
	 * @param int    $privateFile
	 * @param int    $hashOnly
	 * @return string
	 */
	public static function downloadLink ($filePath, $privateFile = 0, $hashOnly = 0) {
		if (stripos($filePath, BIX_BASEROOT) === false) {
			$filePath = JPATH_ROOT . '/' . $filePath;
		}
		$hash = md5('WeerEenNieuwGeheimWoordSssstt' . $filePath);
		$db = JFactory::getDbo();
		$db->setQuery("SELECT * FROM #__bm_download WHERE hash = '$hash'");
		$downloadObj = $db->loadObject();
		if (!isset($downloadObj->downloadID)) {
			$model = BixTools::getModel('download');
			//pr($model);
			$download = new stdClass;
			$download->id = 0;
			$download->hash = $hash;
			$download->filePath = $filePath;
			$download->user_id = JFactory::getUser()->id;
			$download->privateFile = $privateFile;
			$validData = $model->validate($model->getForm(), $download);
			if ($validData !== false) {
				$model->save($validData);
			}
		}
		if ($hashOnly) return $hash;
		$link = 'download?h=' . $hash;
		return $link;
	}

	/**
	 * @param string $hash
	 * @return bool|stdClass
	 */
	public static function getFile ($hash) {
		$db = JFactory::getDBO();
		$db->setQuery("SELECT * FROM #__bm_download WHERE hash = '$hash'");
		$downloadObj = $db->loadObject();
		if (!$downloadObj->id) {
//			JError::raiseWarning( 404, 'Bestand niet gevonden' );
			return false;
		}
		if (JFactory::getUser()->id != $downloadObj->user_id && $downloadObj->privateFile == 1) {
			JError::raiseWarning(403, 'Geen toegang tot bestand!');
			return false;
		}
		return $downloadObj;
	}

	/**
	 * @param $hash
	 */
	public static function downloadFile ($hash) {
		$downloadObj = self::getFile($hash);
		if (!$downloadObj) {
			jExit();
		}
		$absOrRelFile = $downloadObj->filePath;

		$fileWithoutPath = basename($absOrRelFile);
		$fileSize = filesize($absOrRelFile);
		$ctype = BixHelper::getMimeType($absOrRelFile);

		clearstatcache();
		if (ob_get_contents()) ob_end_clean();
		header('Content-Description: File Transfer');
		header('Content-Type: ' . $ctype);
		header('Content-Disposition: attachment; filename=' . $fileWithoutPath);
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
		header('Pragma: public');
		header('Content-Length: ' . $fileSize);
		if (ob_get_contents()) ob_clean();
		flush();
		readfile($absOrRelFile);

		jExit();
	}

	/**
	 * @param       $oldPath
	 * @param       $newPath
	 * @param int   $replace
	 * @param array $error
	 * @return bool
	 */
	public static function moveFile ($oldPath, $newPath, $replace = 0, &$error = array()) {
		$newFolder = dirname($newPath);
		if (!is_dir($newFolder)) {
			JFolder::create($newFolder, 0755);
		}
		//echo 'nf'.$newPath;
		if (is_dir($newFolder)) {
			if (is_file($oldPath)) {
				if (!is_file($newPath) || $replace) {
					if (@rename($oldPath, $newPath)) {
						if (is_file($newPath)) {
							return true;
						} else {
							$error[] = 'Bestand corrupt na verplaatsen!';
							return false;
						}
					} else {
						$error[] = 'Fout bij verplaatsen!';
						return false;
					}
				} else {
					$error[] = 'Bestand bestaat al!';
					return false;
				}
			} else {
				$error[] = 'Bronbestand bestaat niet!';
				return false;
			}
		} else {
			$error[] = 'Kan folder niet maken!';
			return false;
		}
	}

	/**
	 * @param       $oldPath
	 * @param       $newPath
	 * @param int   $replace
	 * @param array $error
	 * @return bool
	 */
	public static function copyFile ($oldPath, $newPath, $replace = 0, &$error = array()) {
		$newFolder = dirname($newPath);
		if (!is_dir($newFolder)) {
			JFolder::create($newFolder, 0755);
		}
		if (is_dir($newFolder)) {
			if (is_file($oldPath)) {
				if (!is_file($newPath) || $replace) {
					if (@copy($oldPath, $newPath)) {
						if (is_file($newPath)) {
							return true;
						} else {
							$error[] = 'copyFile: Bestand corrupt na verplaatsen!';
							return false;
						}
					} else {
						$error[] = 'copyFile: Fout bij verplaatsen!';
						return false;
					}
				} else {
					$error[] = 'copyFile: Bestand bestaat al!';
					return false;
				}
			} else {
				$error[] = 'copyFile: Bronbestand bestaat niet!';
				return false;
			}
		} else {
			$error[] = 'copyFile: Kan folder niet maken!';
			return false;
		}
	}

	/*js*/
	/**
	 * @param array $array
	 * @return string
	 */
	public static function getJSObject ($array = array()) {
		// Initialise variables.
		$object = '{';

		// Iterate over array to build objects
		foreach ((array)$array as $k => $v) {
			if (is_null($v)) {
				continue;
			}

			if (is_bool($v)) {
				if ($k === 'fullScreen') {
					$object .= 'size: { ';
					$object .= 'x: ';
					$object .= 'window.getSize().x-80';
					$object .= ',';
					$object .= 'y: ';
					$object .= 'window.getSize().y-80';
					$object .= ' }';
					$object .= ',';
				} else {
					$object .= ' ' . $k . ': ';
					$object .= ($v) ? 'true' : 'false';
					$object .= ',';
				}
			} elseif (!is_array($v) && !is_object($v)) {
				$object .= ' ' . $k . ': ';
				$object .= (is_numeric($v) || strpos($v, '\\') === 0) ? (is_numeric($v)) ? $v : substr($v, 1) : "'" . $v . "'";
				$object .= ',';
			} else {
				$object .= "'" . $k . "': " . BixHelper::getJSObject($v) . ',';
			}
		}

		if (substr($object, -1) == ',') {
			$object = substr($object, 0, -1);
		}

		$object .= '}';

		return $object;
	}

	/**
	 * @param $aFiles
	 * @param $destination
	 * @return bool
	 */
	public static function zipFiles ($aFiles, $destination) {
		if (!extension_loaded('zip')) {
			return false;
		}
		$zip = new ZipArchive();
		if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
			return false;
		}
		foreach ($aFiles as $fileName => $filePath) {
			$filePath = str_replace('\\', '/', $filePath);

			// Ignore "." and ".." folders
			if (in_array(substr($filePath, strrpos($filePath, '/') + 1), array('.', '..')))
				continue;

			$filePath = realpath($filePath);

			if (is_dir($filePath) === true) {
				continue;
			} else if (is_file($filePath) === true) {
				$zip->addFromString($fileName, file_get_contents($filePath));
			}
		}
		return $zip->close();
	}

}

/**
 * Class csvObject
 */
class csvObject {
	/**
	 * @var array|null
	 */
	private $_vars = null;
	/**
	 * @var null|string
	 */
	private $_delimiter = null;

	/**
	 *
	 */
	public function __construct () {
		$this->_vars = get_class_vars(get_class($this));
		$this->_encloser = '"';
		$this->_delimiter = ';';
	}

	/**
	 * @param        $key
	 * @param string $def_value
	 * @return string
	 */
	public function get ($key, $def_value = '') {
		$value = $this->$key;
		if (empty($value)) $value = $def_value;
		return $value;
	}

	/**
	 * @param $key
	 * @param $value
	 * @return bool
	 */
	public function set ($key, $value) {
		if (property_exists($this, $key)) {
			return $this->$key = $value;
		}
		return false;
	}

	/**
	 * @return string
	 */
	public function getHeader () {
		$csvRow = array();
		foreach ($this->_vars as $var => $val) {
			if (substr($var, 0, 1) == '_') continue;
			$csvRow[] = $var;
		}
		return $this->_encloser . implode($this->_encloser . $this->_delimiter . $this->_encloser, $csvRow) . $this->_encloser;
	}

	/**
	 * @return string
	 */
	public function getRow () {
		$csvRow = array();
		foreach ($this->_vars as $var => $val) {
			if (substr($var, 0, 1) == '_') continue;
			$csvRow[] = htmlspecialchars($this->$var);
		}
		return $this->_encloser . implode($this->_encloser . $this->_delimiter . $this->_encloser, $csvRow) . $this->_encloser;
	}

	/**
	 *
	 */
	public function reset () {
		foreach ($this->_vars as $var => $val) {
			if (substr($var, 0, 1) == '_') continue;
			$this->$var = null;
		}
	}
}

/**
 * Class xmlObject
 */
class xmlObject {

	/**
	 * @var array
	 */
	public $_attribs = array();
	/**
	 * @var array
	 */
	public $_cdata = array();

	/**
	 * @param        $key
	 * @param string $def_value
	 * @return string
	 */
	public function get ($key, $def_value = '') {
		$value = $this->$key;
		if (empty($value)) $value = $def_value;
		return $value;
	}

	/**
	 * @param       $key
	 * @param       $value
	 * @param array $attribs
	 * @return bool
	 */
	public function set ($key, $value, $attribs = array()) {
		if (property_exists($this, $key)) {
			if (count($attribs)) {
				$this->_attribs[$key] = $attribs;
			}
			if (is_array($this->$key)) {
				$arr = $this->$key;
				$arr[] = $value;
				$this->$key = $arr;
				return true;
			} else {
				return $this->$key = $value;
			}
		}
		return false;
	}

	/**
	 * @param        $attribs
	 * @param string $field
	 * @return bool
	 */
	public function setAttribs ($attribs, $field = 'base') {
		if (count($attribs)) {
			$this->_attribs[$field] = $attribs;
		}
		return true;
	}
}

/**
 * Class BixXMLHelper
 */
class BixXMLHelper {
	//yep, this is a beauty :)
	/**
	 * @param     $xmlObjs
	 * @param int $level
	 * @return string
	 */
	public static function parseXMLobjs ($xmlObjs, $level = 0) {
		$xml = array();
		$values = array();
		$level++;
		$attribs = $xmlObjs->_attribs;
		$cdata = $xmlObjs->_cdata;
		$nodeName = $xmlObjs->_nodeName;
		foreach ($xmlObjs as $xmlKey => $xmlValue) {
			if (substr($xmlKey, 0, 1) == '_') continue;
			if ($xmlValue == 'hideNode') continue;
			if (!is_array($xmlValue) && (is_string($xmlValue) || is_int($xmlValue) || !$xmlValue)) {
				if (!isset($attribs[$xmlKey])) $attribs[$xmlKey] = array();
				if (!isset($cdata[$xmlKey])) $cdata[$xmlKey] = false;
				$values[] = BixXMLHelper::XMLtag($xmlKey, $xmlValue, $attribs[$xmlKey], $cdata[$xmlKey], $level);
			} elseif (is_array($xmlValue)) {
				$childValues = array();
				foreach ($xmlValue as $childXmlObjs) {
					$childValues[] = BixXMLHelper::parseXMLobjs($childXmlObjs, $level + 1);
				}
				$childValue = count($childValues) ? implode(_N_, $childValues) : '';
				if (!isset($attribs[$xmlKey])) $attribs[$xmlKey] = array();
				if (!isset($cdata[$xmlKey])) $cdata[$xmlKey] = false;
				$values[] = BixXMLHelper::XMLtag($xmlKey, $childValue, $attribs[$xmlKey], $cdata[$xmlKey], $level);
			} elseif ($xmlValue) {
				$values[] = BixXMLHelper::parseXMLobjs($xmlValue, $level); //recursive, cool!
			}
		}
		$value = count($values) ? implode(_N_, $values) : '';
		if (!isset($attribs['base'])) $attribs['base'] = array();
		$xml[] = BixXMLHelper::XMLtag($nodeName, $value, $attribs['base'], false, $level - 1);
		return implode(_N_, $xml);
	}

	/**
	 * @param       $name
	 * @param       $value
	 * @param array $attribs
	 * @param int   $cdata
	 * @param int   $level
	 * @return string
	 */
	public function XMLtag ($name, $value, $attribs = array(), $cdata = 0, $level = 0) {
		$attribString = '';
		$inspring = '';
		for ($i = 0; $i < $level; $inspring .= _T_, $i++) ;
		if (count($attribs)) foreach ($attribs as $attrname => $attrvalue) $attribString .= ' ' . $attrname . '="' . $attrvalue . '"';
		if (trim($value) == '') return $inspring . '<' . $name . $attribString . '/>';
		if ($cdata) return $inspring . '<' . $name . $attribString . '><![CDATA[' . htmlspecialchars($value) . ']]></' . $name . '>';
		if (stripos($value, "\n") !== false || stripos($value, "\t") !== false)
			return $inspring . '<' . $name . $attribString . '>' . _N_ . $value . _N_ . $inspring . '</' . $name . '>';
		return $inspring . '<' . $name . $attribString . '>' . htmlspecialchars($value) . '</' . $name . '>';
	}

	/**
	 * @param $xmlFilePath
	 * @param $xmlString
	 * @return mixed
	 */
	public function createXMLfile ($xmlFilePath, $xmlString) {
		$newFolder = dirname($xmlFilePath);
		if (!is_dir(JPATH_ROOT . '/' . $newFolder)) {
			JFolder::create(JPATH_ROOT . '/' . $newFolder, 0755);
		}
		$file = fopen(JPATH_ROOT . '/' . $xmlFilePath, 'w');
		fwrite($file, $xmlString);
		fclose($file);
		return $xmlFilePath;
	}

}

jimport('joomla.application.component.view');

/**
 * Class BixMailingViewContext
 */
class BixMailingViewContext extends JViewLegacy {

	/**
	 * @param        $view
	 * @param        $layout
	 * @param string $tpl
	 * @param bool   $forceFront
	 * @return string
	 */
	public function renderTemplate ($view, $layout, $tpl = '', $forceFront = false) {
		$tpl = $tpl != '' ? '_' . $tpl : '';
		$templateNaam = $this->_frontendTemplate();
		$basePath = BIX_PATH_COMP;
		if (!BIX_ISADMIN || $forceFront == true) {
			$tplfile = JPATH_ROOT . '/templates/' . $templateNaam . '/html/com_bixmailing/' . $view . '/' . $layout . $tpl . '.php';
			JFactory::getLanguage()->load('com_bixmailing', JPATH_ROOT);
			if (file_exists($tplfile)) {
				$html = $this->_getTemplateContents($tplfile);
				return $html;
			}
		} else {
			$basePath = BIX_PATH_ADMIN;
			JFactory::getLanguage()->load('com_bixmailing', JPATH_ADMINISTRATOR);
		}
		$tplfile = $basePath . '/views/' . $view . '/tmpl/' . $layout . $tpl . '.php';
		if (file_exists($tplfile)) {
			$html = $this->_getTemplateContents($tplfile);
		} else {
			$html = 'Template "' . $view . '/tmpl/' . $layout . $tpl . '.php" niet gevonden!';
		}
		return $html;
	}

	/**
	 * @return mixed|string
	 */
	protected function _frontendTemplate () {
		if (BIX_ISADMIN) {
			$db = JFactory::getDbo();
			$db->setQuery("SELECT template FROM #__template_styles WHERE home = 1 AND client_id = 0");
			$templateNaam = $db->loadResult();
		} else {
			$app = JFactory::getApplication();
			$templateNaam = $app->getTemplate();
		}
		return $templateNaam;
	}

	/**
	 * @param $file
	 * @return string
	 */
	protected function _getTemplateContents ($file) {
		$contents = '';
		ob_start();
		include($file);
//pr($file);
		$contents = ob_get_clean();
		return $contents;
	}
}


