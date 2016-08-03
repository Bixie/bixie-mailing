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

	public static function loadJsAssets ($assets = [], $vue = true, $devos = null) {
		JHtml::_('jquery.framework');
		// get devos
		if ($devos or $devos = require(JPATH_ADMINISTRATOR.'/components/com_bix_devos/bix_devos-app.php')) {

			if ($vue) {
				$devos['scripts']->add('vue', 'assets/js/vue.js', ['uikit-tooltip', 'uikit-notify', 'uikit-pagination', 'uikit-upload']);
			} else {
				$devos['scripts']->add('uikit', 'vendor/assets/uikit/js/uikit.min.js');
				$devos['scripts']->add('uikit-tooltip');
				$devos['scripts']->add('uikit-notify');
				$devos['scripts']->add('uikit-pagination');
				$devos['scripts']->add('uikit-upload');
			}
			$devos['scripts']->add('uikit-sticky');
			$devos['scripts']->add('uikit-form-select');
			$devos['scripts']->add('uikit-lightbox');
			$devos['scripts']->add('uikit-slideset');
			$devos['scripts']->add('uikit-slider');
			$devos['scripts']->add('uikit-slideshow');

		}
		foreach ((array) $assets as $asset) {
			$devos['scripts']->add(basename($asset, '.js'), $asset);
		}

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

	public function onAfterRoute () {
		//trigger devas app for assets
		require(JPATH_ADMINISTRATOR.'/components/com_bix_devos/bix_devos-app.php');
	}

}
