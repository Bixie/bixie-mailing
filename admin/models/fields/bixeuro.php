<?php
/**
 *	com_bix_printshop - Online-PrintStore for Joomla
 *  Copyright (C) 2010-2012 Matthijs Alles
 *	Bixie.nl
 *
 */

defined('JPATH_BASE') or die;

jimport('joomla.html.html');
require_once JPATH_ADMINISTRATOR . '/components/com_bixprintshop/classes/bixtools.php';
require_once BIX_PATH_ADMIN_FIELDS . '/bixtext.php';

/**
 * Supports an HTML select list of categories
 */
class JFormFieldBixeuro extends JFormFieldBixtext
{
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'bixeuro';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput()
	{
		// Initialize some field attributes.
		$dec = $this->element['dec'] ? $this->element['dec']: 2;
		$size = $this->element['size'] ? ' size="' . (int) ($this->element['size'] - 2) . '"' : ' size="18"';
		$maxLength = $this->element['maxlength'] ? ' maxlength="' . (int) $this->element['maxlength'] . '"' : '';
		$class = $this->element['class'] ? ' class="bix-euro ' . (string) $this->element['class'] . '"' : ' class="bix-euro"';
		$readonly = ((string) $this->element['readonly'] == 'true') ? ' readonly="readonly"' : '';
		$disabled = ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		//validatie
		if ($class && preg_match("/validate-bix-(?P<validate>[a-z]*)\s?/i",$class,$match)) {
			$function = 'validate'.ucfirst($match['validate']);
			$this->$function();
		}

		// Initialize JavaScript field attributes.
		$onchange = $this->element['onchange'] ? ' onchange="' . (string) $this->element['onchange'] . '"' : ' onchange="try{this.value = makePriceFloat(this.value,'.$dec.')}catch(e){}"';
		$html[] = '<fieldset id="fs' . $this->id . '" class="radio">';
		$html[] = '<div class="prefield">&euro; </div>';
		$html[] = '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="'
			. htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8') . '"' . $class . $size . $disabled . $readonly . $onchange . $maxLength . '/>';
		$html[] = '</fieldset>';

		return implode($html);
	}
	
	public function getFormatValue($params=array()) {
		$dec = $this->element['dec'] ? (int)$this->element['dec']: 2;
		$class = JArrayHelper::getValue($params,'class','');
		return BixHtml::formatPrice($this->value,$class,$dec);
	}
}