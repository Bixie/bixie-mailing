<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  User.profile
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 *
 * @package     Joomla.Plugin
 * @subpackage  User.profile
 * @since       2.5.5
 */
class JFormFieldGlscustomernumber extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  2.5.5
	 */
	protected $type = 'Glscustomernumber';

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$options = [(object) [
			'value'    => '',
			'text'     => 'Standaard nummer'
		]];

		if (!$app = @include(JPATH_ADMINISTRATOR . '/components/com_bix_devos/bix_devos-app.php')) {
			return $options;
		}

		if (!empty($app['config']['gls_customer_numbers'])) {
			foreach ($app['config']['gls_customer_numbers'] as $number) {
				$options[] = (object)[
					'value' => $number,
					'text' => $number
				];
			}
		}

		reset($options);

		return $options;
	}

}
