<?php
/**
 *	com_bix_printshop - Online-PrintStore for Joomla
 *  Copyright (C) 2010-2012 Matthijs Alles
 *	Bixie.nl
 *
 */

defined('JPATH_PLATFORM') or die;
include_once(JPATH_SITE."/libraries/joomla/form/fields/list.php");


class JFormFieldBixlist extends JFormFieldList
{

	protected $type = 'Bixlist';

	protected function getInput() {
		if ($this->element['message']) {
			$this->element['onchange'] = "document.id('alert$this->id').setStyle('display','block')";
		}
		if (is_array($this->value) && $this->element['autooptions'] == true) {
			if (count($this->value) < (int)$this->element['size']) {
				$this->element['size'] = count($this->value);
			}
		}
		$select = parent::getInput();
		$html = array($select);
		if ($this->element['message']) {
			$alertclass = $this->element['alertclass'] ? ' ' . (string) $this->element['alertclass'] . '' : 'box-warning';
			$html[] = '<fieldset class="radio">';
			$html[] = '<div id="alert' . $this->id . '" class="fieldAlert'.$alertclass.'" style="display:none;">'.JText::_((string) $this->element['message']).'</div>';
			$html[] = '</fieldset>';
		}
		if ((string)$this->element['hidden'] == 'true') {
			array_unshift($html,'<div class="hidden">');
			$html[] = '</div>';
		}
		return implode($html);
	}

	public function getFormatValue($params=array()) {
		$valueFormatDef = $this->element['valueFormat'] ? (string) $this->element['valueFormat']: '%s';
		$labelfunction = $this->element['labelfunction'] ? (string) $this->element['labelfunction']: false;
		$valueFormat = JArrayHelper::getValue($params,'valueFormat',$valueFormatDef);
		$seperator = JArrayHelper::getValue($params,'seperator',', ');
		if ($this->element['autooptions'] == true) {
			$suffix = $this->element['suffix'] ? (string) $this->element['suffix']: '';
			if ($this->element['multiple'] == true) {
				$values = array();
				foreach ($this->value as $value) {
					if ($labelfunction && method_exists('BixprintshopHelper',$labelfunction)) {
						$format = BixprintshopHelper::$labelfunction($value);
					} else {
						$format = sprintf($valueFormat,$value);
					}
					$values[] = $format;
				}
				return implode($seperator,$values);
			} else {
				return sprintf($valueFormat,$this->value);
			}
		} else {
			// Get the field options.
			$options = (array) $this->getOptions();
			if ($this->element['multiple'] == true) {
				$values = array();
				foreach ($options as $option) {
					if (in_array($option->value,(array)$this->value)) {
						$values[] = sprintf($valueFormat,JText::_($option->text));
					}
				}
				return implode($seperator,$values);
			} else {
				foreach ($options as $option) {
					if ($option->value == $this->value) break;
				}
				return JText::_($option->text);
			}
		}
	}
	
	public function getOptions() {
		if ($this->element['autooptions'] == true) {
//pr($this->value);
			$valueFormat = $this->element['valueFormat'] ? (string) $this->element['valueFormat']: '%s';
			$options = array();
			if (is_array($this->value)) {
				foreach ($this->value as $value) {
					$options[] = JHtml::_('select.option', $value, sprintf($valueFormat,$value));
				}
				return $options;
			}
		}
		return parent::getOptions();
	}
	
}
