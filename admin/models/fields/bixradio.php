<?php
/**
 *	com_bix_printshop - Online-PrintStore for Joomla
 *  Copyright (C) 2010-2012 Matthijs Alles
 *	Bixie.nl
 *
 */

defined('JPATH_PLATFORM') or die;
include_once(JPATH_SITE."/libraries/joomla/form/fields/radio.php");


class JFormFieldBixradio extends JFormFieldRadio {

	protected $type = 'Bixradio';

	public function getFormatValue() {
		// Get the field options.
		$options = (array) $this->getOptions();
		foreach ($options as $option) {
			if ($option->value == $this->value) break;
		}
		return JText::_($option->text);
	}
	
	public function getOptions() {
		return parent::getOptions();
	}
	
}
