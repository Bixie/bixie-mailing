<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */

/**
 * defines
 */
define('COM_BIXMAILING', 'com_bixmailing');
define('BIX_PATH_COMP', JPATH_ROOT . '/components' . '/' . COM_BIXMAILING);
define('BIX_PATH_ADMIN', JPATH_ROOT . '/administrator/components' . '/' . COM_BIXMAILING);

define('_N_', "\n");
define('_T_', "\t");
define('BIX_ISADMIN', JFactory::getApplication()->isAdmin());
define('BIX_PATH_ADMIN_CLASS', BIX_PATH_ADMIN . '/classes');
define('BIX_PATH_ADMIN_HELPERS', BIX_PATH_ADMIN . '/helpers');
define('BIX_PATH_ADMIN_MODELS', BIX_PATH_ADMIN . '/models');
define('BIX_PATH_ADMIN_FORMS', BIX_PATH_ADMIN_MODELS . '/forms');
define('BIX_PATH_ADMIN_FIELDS', BIX_PATH_ADMIN_MODELS . '/fields');
define('BIX_PATH_ADMIN_ASSETS', BIX_PATH_ADMIN . '/assets');
define('BIX_PATH_CSS', BIX_PATH_COMP . '/assets/css');
define('BIX_PATH_IMAGE', BIX_PATH_COMP . '/assets/images');
define('BIX_PATH_JS', BIX_PATH_COMP . '/assets/js');
define('BIX_PATH_PHP', BIX_PATH_COMP . '/assets/php');
if (BIX_ISADMIN) define('BIX_COMP', JURI::root() . 'components/' . COM_BIXMAILING . '/');
else define('BIX_COMP', '/components/' . COM_BIXMAILING . '/');
define('BIX_COMP_ADMIN', '/administrator/components/' . COM_BIXMAILING . '/');
define('BIX_ADMIN_ASSETS', BIX_COMP_ADMIN . 'assets/');
define('BIX_JS', BIX_COMP . 'assets/js/');
define('BIX_IMAGES', BIX_COMP . 'assets/images/');
define('BIX_CSS', BIX_COMP . 'assets/css/');
define('BIX_JQUERY_PATH', BIX_ADMIN_ASSETS . 'js/');
define('BIX_JQUERY_VERSION', '2.1.0');

$baseArr = explode('/', JPATH_ROOT);
$rootFolder = array_pop($baseArr);
$baseroot = str_replace('/' . $rootFolder, '', JPATH_ROOT);
define('BIX_BASEROOT', $baseroot);
define('BIX_PATH_EXPORTS', BIX_BASEROOT . '/exports');
define('BIX_PATH_FILES', BIX_BASEROOT . '/bestanden');
define('BIX_PATH_UPLOADS', BIX_BASEROOT . '/uploads');
define('BIX_THUMBS', '/thumbs');
define('BIX_ICONS', '/thumbs/icons');
define('BIX_PATH_THUMBS', JPATH_ROOT . BIX_THUMBS);
define('BIX_PATH_ICONS', JPATH_ROOT . BIX_ICONS);


/**
 * load dependencies
 */
//JLoader!
// JLoader::register('Markdown',BIX_PATH_ADMIN_HELPERS.'/Michelf/Markdown.inc.php'); //grrrr....
require_once BIX_PATH_ADMIN_HELPERS . '/Michelf/Markdown.inc.php';
require_once BIX_PATH_ADMIN_HELPERS . '/Michelf/MarkdownExtra.inc.php';
JLoader::discover('Bix', BIX_PATH_ADMIN_HELPERS);
JLoader::discover('BixMailing', BIX_PATH_ADMIN_CLASS);
require_once BIX_PATH_ADMIN_HELPERS . '/upload.php'; //grr
// require_once BIX_PATH_ADMIN_HELPERS . '/bixhtml.php';

/*classes laden*/
require_once BIX_PATH_ADMIN_CLASS . '/base.php';
require_once BIX_PATH_ADMIN_FIELDS . '/fieldval.php';
require_once BIX_PATH_ADMIN_HELPERS . '/bixmailing.php';

//lang
JFactory::getLanguage()->load('com_bixmailing', JPATH_ROOT, 'nl-NL', false, false);

/*standaard JS*/
// JHTML::_('behavior.mootools'); Ha!
$document = JFactory::getDocument();
if (BIX_ISADMIN) { //UI-kit FTW!
	BixTools::assetJs('jquery');
	$document->addScript(BIX_ADMIN_ASSETS . 'js/uikit.min.js');
	$document->addScript(BIX_ADMIN_ASSETS . 'js/notify.min.js');
	$document->addStylesheet(BIX_ADMIN_ASSETS . 'css/uikit.almost-flat.min.css');
	$document->addStylesheet(BIX_ADMIN_ASSETS . 'css/notify.almost-flat.min.css');
}
/*generator*/
$document->setGenerator('Bixie mailing on Joomla!');

/*tables toevoegen*/
JTable::addIncludePath(BIX_PATH_ADMIN . '/tables');
JFormHelper::addFormPath(array(BIX_PATH_ADMIN_MODELS . '/forms'));
JFormHelper::addFieldPath(array(BIX_PATH_ADMIN_MODELS . '/fields'));

/**
 * Class BixTools
 */
abstract class BixTools {

	/**
	 * @var array
	 */
	protected static $loaded = array();
	/**
	 * @var array
	 */
	protected static $_cache = array();

	/**
	 * @param null $path
	 * @param null $default
	 * @return JRegistry|mixed
	 */
	public static function config ($path = null, $default = null) {
		jimport('joomla.component.helper');
		$params = JComponentHelper::getParams(COM_BIXMAILING);
		if ($path) {
			return $params->get($path, $default);
		} else {
			return $params;
		}
	}

	/**
	 * @param        $type
	 * @param array  $config
	 * @param string $name
	 * @return mixed
	 */
	public static function getModel ($type, $config = array('ignore_request' => true), $name = 'BixmailingModel') {
		jimport('joomla.application.component.model');
		JModelLegacy::addIncludePath(BIX_PATH_ADMIN_MODELS, 'BixmailingModel');
		$model = JModelLegacy::getInstance($type, $name, $config);
		return $model;
	}

	/**
	 * @param      $type
	 * @param null $xmlName
	 * @param null $control
	 * @return mixed
	 */
	public static function getForm ($type, $xmlName = null, $control = null) {
		$model = self::getModel($type);
		if ($control) {
			$model->setState('form.control', $control);
		}
		return $model->getForm(array(), false, $xmlName);
	}

	/**
	 * @param $type string
	 * @param $pk   int
	 * @return mixed
	 */
	public static function getItem ($type, $pk) {
		$model = self::getModel($type);
		if ($pk != '') {
			return $model->getItem($pk);
		} else {
			return false;
		}
	}

	/**
	 * @param $type string
	 * @param $name string
	 * @return mixed
	 */
	public static function getField ($type, $name) {
		$form = self::getForm($type);
		$group = null;
		if (stripos($name, '.') !== false) {
			$arr = explode('.', $name);
			$name = array_shift($arr);
			$group = implode('.', $arr);
		}
		return $form->getField($name, $group);
	}

	/**
	 * Opties uit XML-form halen
	 * @param string $type   name of form
	 * @param string $name   name of field
	 * @param string $format raw of xml-options
	 * @return mixed
	 */
	public static function getFieldOptions ($type, $name, $format = 'options') {
		$field = self::getField($type, $name);
		if (method_exists($field, 'getOptions')) {
			$options = $field->getOptions();
			if ($format == 'array') {
				$array = array();
				foreach ($options as $option) {
					$array[$option->value] = JText::_($option->text);
				}
				return $array;
			}
			return $options;
		}
		return false;
	}

	/**
	 * @param string $name
	 * @return mixed
	 */
	public static function getMenuModel ($name = 'main') {
		jimport('joomla.application.component.model');
		JModelLegacy::addIncludePath(JPATH_ROOT . '/administrator/components/com_menus/models/', 'MenusModel');
		$model = JModelLegacy::getInstance($name, 'MenusModel', array('name' => 'menu', 'ignore_request' => true));
		return $model;
	}

	/**
	 *    load uikit markdown addon
	 * @return void
	 */
	public static function markdown () {
		self::asset('js', 'codemirror', BIX_ADMIN_ASSETS . 'js/markdown/');
		self::asset('js', 'markdown', BIX_ADMIN_ASSETS . 'js/markdown/');
		self::asset('js', 'overlay', BIX_ADMIN_ASSETS . 'js/markdown/');
		self::asset('js', 'xml', BIX_ADMIN_ASSETS . 'js/markdown/');
		self::asset('js', 'gfm', BIX_ADMIN_ASSETS . 'js/markdown/');
		self::asset('js', 'marked', BIX_ADMIN_ASSETS . 'js/markdown/');
		self::asset('js', 'markdownarea.min', BIX_ADMIN_ASSETS . 'js/markdown/');
		self::asset('css', 'codemirror', BIX_ADMIN_ASSETS . 'css/');
		self::asset('css', 'markdownarea.almost-flat.min', BIX_ADMIN_ASSETS . 'css/');
	}

	/**
	 *    load uploader blueimp
	 *    https://github.com/blueimp/jQuery-File-Upload/wiki/Basic-plugin
	 * @return void
	 */
	public static function upload () {
		self::assetJs('jquery');
		self::uikit();
		if (!self::config('minifyJs')) {
			self::asset('js', 'jquery.ui.widget', BIX_JS . 'uploader/');
			self::asset('js', 'jquery.iframe-transport', BIX_JS . 'uploader/');
			self::asset('js', 'jquery.fileupload', BIX_JS . 'uploader/');
		}
		self::assetJS('uploader');
	}

	/**
	 *   load filetree
	 *   http://www.abeautifulsite.net/blog/2008/03/jquery-file-tree/#download
	 * @return void
	 */
	public static function filetree () {
		self::assetJs('jquery');
		self::uikit();
		self::asset('js', 'jqueryFileTree', BIX_JS . 'jqueryFileTree/');
		self::asset('css', 'jqueryFileTree', BIX_JS . 'jqueryFileTree/');
	}

	/**
	 *    load uikit
	 * @return void
	 */
	public static function uikit () {
		self::assetJs('jquery');
		if (JFactory::getApplication()->get('uikit', false)) {
			self::assetJs('bixtools');
			self::assetJs('ajaxsubmit');
			return;
		}
		self::asset('js', 'uikit.min', BIX_ADMIN_ASSETS . 'js/');
		self::asset('js', 'notify.min', BIX_ADMIN_ASSETS . 'js/');
		self::asset('js', 'jquery.hotkeys', BIX_JS . 'vendor/');
		self::assetJs('bixtools');
		self::assetJs('ajaxsubmit');
		JFactory::getApplication()->set('uikit', true);
	}

	/**
	 * @param        $filename
	 * @param string $path
	 * @return void
	 */
	public static function assetJs ($filename, $path = '') {
		//gecombineerde js-files
		if (stripos($filename, 'com_bixmailing') === 0) {
			if (self::config('minifyJs')) {
				self::asset('js', $filename . '.min', BIX_JS);
			} else {
				jimport('joomla.filesystem.folder');
				jimport('joomla.filesystem.file');
				$parts = explode('.', $filename);
				//algemene files
				$files = JFolder::files(BIX_PATH_JS . '/src/', '.js');
				foreach ($files as $file) {
					self::asset('js', basename($file, '.js'), BIX_JS . 'src/');
				}
				//admin/front files
				$files = JFolder::files(BIX_PATH_JS . '/src/' . $parts[1] . '/', '.js');
				foreach ($files as $file) {
					self::asset('js', basename($file, '.js'), BIX_JS . 'src/' . $parts[1] . '/');
				}
			}
		} else {
			if ($filename == 'jquery') {
				if (JFactory::getApplication()->get('jquery', false)) return;
				$path = BIX_JQUERY_PATH;
				$filename .= '-' . BIX_JQUERY_VERSION;
				JFactory::getApplication()->set('jquery', true);
			}
			if (self::config('minifyJs')) {
				$filename .= '.min';
			}
			//add to doc
			if ($path == '') $path = BIX_JS;
			self::asset('js', $filename, $path);
		}
	}

	/**
	 * @param        $filename
	 * @param string $path
	 * @return void
	 */
	public static function assetCss ($filename, $path = '') {
		if ($path == '') $path = BIX_CSS;
		self::asset('css', $filename, $path);
	}

	/**
	 * @param        $type
	 * @param        $filename
	 * @param string $path
	 * @return void
	 */
	protected static function asset ($type, $filename, $path = '') {
		if (substr($path, -1) != '/') $path .= '/';
		$file = $path . $filename;
		switch ($type) {
			case 'js':
				JFactory::getDocument()->addScript($file . '.js');
				break;
			case 'css':
				JFactory::getDocument()->addStylesheet($file . '.css');
				break;
		}
	}

	/**
	 * laden juiste css class
	 * @return void
	 */
	public static function loadCSS () {
		if (BIX_ISADMIN) {
			self::asset('css', 'bixmailing', BIX_ADMIN_ASSETS . 'css/');
		} else {
			self::asset('css', 'bixmailing', BIX_CSS);
		}
	}

}

