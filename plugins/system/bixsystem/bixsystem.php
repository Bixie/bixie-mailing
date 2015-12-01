<?php
/**
 *    COM_BIXPRINTSHOP - Online-PrintStore for Joomla
 *  Copyright (C) 2010-2012 Matthijs Alles
 *    Bixie.nl

 */

// No direct access
defined('_JEXEC') or die;

/**
 * Class plgSystemBixsystem
 */
class plgSystemBixsystem extends JPlugin {

	/*Statics
	* must be called from within VM!
	*/
	/**
	 * @return string
	 */
	public static function noProducts () {
		jimport('joomla.application.module.helper');
		//init vars
		$html = '';
		$position = false;
		if (!self::validUser()) { //geen valid user
			$position = 'noproducts';
		}
		//modulehelper
		if ($position) {
			$renderer = JFactory::getDocument()->loadRenderer('module');
			foreach (JModuleHelper::getModules($position) as $mod) {
				$html .= $renderer->render($mod, array('style' => 'blank'));
			}
		}
// echo '<pre style="margin-top:100px;">';
// print_r($vmUser);
// echo '</pre>';
		return $html;
	}

	/**
	 * @return string
	 */
	public static function notValidUser () {
		jimport('joomla.application.module.helper');
		//init vars
		$html = '';
		$position = 'notvaliduser';
		//modulehelper
		if ($position) {
			$renderer = JFactory::getDocument()->loadRenderer('module');
			foreach (JModuleHelper::getModules($position) as $mod) {
				$html .= $renderer->render($mod, array('style' => 'blank'));
			}
		}
// echo '<pre style="margin-top:100px;">';
// print_r($vmUser);
// echo '</pre>';
		return $html;
	}

	/**
	 * @return bool
	 */
	public static function validUser () {
		jimport('joomla.plugin.helper');
		//plugin params
		$bixPlugin = JPluginHelper::getPlugin('system', 'bixsystem');
		$bixParams = new JRegistry();
		$bixParams->loadString($bixPlugin->params);
		//vmuser
		$usermodel = VmModel::getModel('user');
		$vmUser = $usermodel->getUser();

		return in_array($bixParams->get('allowedGroup'), $vmUser->shopper_groups);
	}

	/**
	 * @param $fieldsArr
	 * @return string
	 */
	public static function formatVMadres ($fieldsArr) {
		$index = array();
		foreach ($fieldsArr as $field) {
			$index[$field['name']] = $field;
		}
		$html = '<div class="uk-grid uk-address">';
		$html .= '<div class="uk-width-1-5 uk-width-large-1-10">';
		$html .= '<i class="uk-icon-user"></i><br/>';
		$html .= '</div>';
		$html .= '<div class="uk-width-4-5 uk-width-large-9-10">';
		$html .= $index['email']['value'] . '<br/>';
		$html .= $index['title']['value'] . ' ';
		$html .= $index['first_name']['value'] . ' ';
		if ($index['middle_name']['value']) $html .= $index['middle_name']['value'] . ' ';
		$html .= $index['last_name']['value'] . '<br/>';
		$html .= '</div>';
		$html .= '<div class="uk-width-1-5 uk-width-large-1-10">';
		$html .= '<i class="uk-icon-home"></i><br/>';
		$html .= '</div>';
		$html .= '<div class="uk-width-4-5 uk-width-large-9-10">';
		$html .= $index['address_1']['value'] . ' ';
		$html .= $index['address_2']['value'] . '<br/>';
		$html .= $index['city']['value'] . '<br/>';
		$html .= $index['country']['value'] . '<br/>';
		$html .= '</div>';
		$html .= '</div>';
		return $html;
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
//print_r_pre($match);
			$postcodeArr['raw'] = $postcodeRaw;
		}
		$postcodeArr['valid'] = !empty($postcodeArr['num']) && !empty($postcodeArr['alph']);
		return $postcodeArr;
	}

	/*Events*/
	/**
	 * @return bool
	 */
	public function onAfterInitialise () {
//		$app = JFactory::getApplication();
//		if ($app->isSite()) {
//			$template = $app->getTemplate();
//			if (!class_exists('JDocumentRendererMessage') && file_exists(JPATH_THEMES . '/' . $template . '/html/message.php')) {
//				require_once JPATH_THEMES . '/' . $template . '/html/message.php';
//			}
//		}
		return true;
	}

	/**
	 * @return bool
	 */
	public function onAfterDispatch () {
		$app = JFactory::getApplication();
		if ($app->isAdmin()) {
			return true;
		}
		$option = $app->input->getCmd('option', '');
		$view = $app->input->getCmd('view', '');
		$user = JFactory::getUser();
		if ($option == 'com_users' && !$user->requireReset && (in_array($view, array('profile')))) {
			$app = JFactory::getApplication();
			$link = JRoute::_('index.php?Itemid=' . $this->params->get('profileItemid'));
			$app->redirect($link);
		}
		return true;
	}

	/**
	 * @return bool
	 */
	public function onBeforeRender () {
		$app = JFactory::getApplication();

		if ($app->input->getCmd('option', '') == 'com_bixmailing') { //loose joomla jquery
			$document = JFactory::getDocument();
			$newOrder = array();
			foreach ($document->_scripts as $url => $docInfo) {
				if ( preg_match('#jui/js/jquery#', $url)) {
					//kill it with fire!
				} else {
					$newOrder[$url] = $docInfo;
				}
			}
			$document->_scripts = $newOrder;
		}
		return true;
	}


}
