<?php
/**
 *	com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *	Bixie.nl
 *
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class BixFieldFilter {
	public static function kvk($value) {
		$validValue = (string)$value;
		if (preg_match("/[a-zA-Z]/",$value,$match) || strlen($value) > 12) {
			$validValue = JText::_('COM_BIXMAILING_INVALID_KVK');
		}
		return $validValue;
	}
	
	public static function btw($value) {
		$value = trim(str_replace(array('-','.'),'',strtoupper($value)));
		return $value;
	}
	
	public static function prefix($value) {
		$value = trim(strtolower($value));
		if (substr($value,-1) != '_') {
			$value .= '_';
		}
		return $value;
	}
	
	public static function upperkey($value) {
		return trim(str_replace(array('-','.'),'',strtoupper($value)));
	}
	
	public static function filepath($value) {
		if (substr($value,0,1) == '/') {
			$value = substr($value,1);
		}
		if (substr($value,-1) == '/') {
			$value = substr($value,0,-1);
		}
		return trim(str_replace(array('-','.'),'',strtolower($value)));
	}
	
}

class BixFormRuleInteger extends JFormRule {
	protected $regex = '^([0-9]*)$';
}

class BixFormRuleTime extends JFormRule {
	protected $regex = '^([0-9]|0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$';
}


