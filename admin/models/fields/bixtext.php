<?php
/**
 *	com_bix_printshop - Online-PrintStore for Joomla
 *  Copyright (C) 2010-2012 Matthijs Alles
 *	Bixie.nl
 *
 */

defined('JPATH_PLATFORM') or die;
include_once(JPATH_SITE."/libraries/joomla/form/fields/text.php");


class JFormFieldBixtext extends JFormFieldText
{

	protected $type = 'Bixtext';

	protected function getInput() {
		// Initialize some field attributes.
		$size = $this->element['size'] ? ' size="' . (int) $this->element['size'] . '"' : '';
		$maxLength = $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '';
		$class = $this->element['class'] ? ' class="' . (string) $this->element['class'] . '"' : '';
		$readonly = ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';

		// Initialize JavaScript field attributes.
		$onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : '';
		$placeholder = $this->element['placeholder'] ? ' placeholder="' . JText::_((string) $this->element['placeholder']) . '"' : '';

		$input = '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="'
			. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $class . $size . $disabled . $readonly . $onchange . $maxLength . $placeholder. '/>';
			
		$sImageHtml = '';
		if ((int)$this->element['imagesize'] > 0 && file_exists(JPATH_ROOT.$this->value)) {
			$sImageHtml = '<div class="fieldImage"><img src="'.htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8').'" alt="prodimage" style="max-width:'.(int)$this->element['imagesize'].'px;max-height:'.(int)$this->element['imagesize'].'px;"/></div>';
		}

		$suffix = $this->element['suffix'] ? '<div class="postfield">'.(string) $this->element['suffix'].'</div>': false;
		if ($class && preg_match("/validate-bix-(?P<validate>[a-z]*)\s?/i",$class,$match)) {
			$function = 'validate'.ucfirst($match['validate']);
			$this->$function();
		}
		$html = array();
		if ($suffix || $sImageHtml) {
			$html[] = '<fieldset id="fs' . $this->id . '" class="radio">';
			$html[] = _T_.$input;
			$html[] = _T_.$suffix.$sImageHtml;
			$html[] = '</fieldset>';
		} else {
			$html[] = $input;
		}
		return implode(_N_,$html);
	}

	public function getFieldNameRaw() {
		return $this->fieldname;
	}

	public function getFormatValue($params=array()) {
		$translatevalue = ((string) $this->element['translatevalue'] == 'true') ? true : false;
		if ($translatevalue) {
			return JText::_($this->value);
		}
		return $this->value;
	}

	protected function validateInteger() {
		global $bixCache;
		if (!isset($bixCache['jsvalidation']['Integer'])) {
			$js = array();
			$js[] = _T_."window.addEvent('domready', function(){";
			$js[] = _T_._T_."document.formvalidator.setHandler('bix-integer',";
			$js[] = _T_._T_._T_."function (value) {";
			$js[] = _T_._T_._T_._T_."var regex = /^[0-9]*$/;";
			$js[] = _T_._T_._T_._T_."return regex.test(value);";
			$js[] = _T_._T_._T_."}";
			$js[] = _T_._T_.");";
			$js[] = _T_."});";
			JFactory::getDocument()->addScriptDeclaration(implode(_N_,$js));
			$bixCache['jsvalidation']['Integer'] = true;
		}
	}
	protected function validateFloat() {
		global $bixCache;
		if (!isset($bixCache['jsvalidation']['Float'])) {
			$js = array();
			$js[] = _T_."window.addEvent('domready', function(){";
			$js[] = _T_._T_."document.formvalidator.setHandler('bix-float',";
			$js[] = _T_._T_._T_."function (value) {";
			$js[] = _T_._T_._T_._T_."var regex = /^[0-9]*\.[0-9]*$/;";
			$js[] = _T_._T_._T_._T_."if (regex.test(value)) return value > 0;";
			$js[] = _T_._T_._T_._T_."else return false;";
			$js[] = _T_._T_._T_."}";
			$js[] = _T_._T_.");";
			$js[] = _T_."});";
			JFactory::getDocument()->addScriptDeclaration(implode(_N_,$js));
			$bixCache['jsvalidation']['Float'] = true;
		}
	}
	protected function validateUrl() {
		global $bixCache;
		if (!isset($bixCache['jsvalidation']['Integer'])) {
			$js = array();
			$js[] = _T_."window.addEvent('domready', function(){";
			$js[] = _T_._T_."document.formvalidator.setHandler('bix-url',";
			$js[] = _T_._T_._T_."function (value) {";
			$js[] = _T_._T_._T_._T_."var regex = /^https?:\/\/([-\w\.]+)+(:\d+)?(\/([\w/_\.]*(\?\S+)?)?)?$/;";
			$js[] = _T_._T_._T_._T_."return regex.test(value);";
			$js[] = _T_._T_._T_."}";
			$js[] = _T_._T_.");";
			$js[] = _T_."});";
			JFactory::getDocument()->addScriptDeclaration(implode(_N_,$js));
			$bixCache['jsvalidation']['Integer'] = true;
		}
	}
	
}
