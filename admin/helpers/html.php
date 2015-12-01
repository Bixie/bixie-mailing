<?php
/**
 *	com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *	Bixie.nl
 *
 */

abstract class BixHtml {

	public static function formatPrice($float,$class='',$nrDec=2,$priceclass='price') {
		if ($nrDec==3) {
			if (preg_match("/(?P<int>[0-9]*)\.(?P<dec>[0-9]*)$/",$float,$match)) {
				if (strlen($match['dec']) == 2 || (strlen($match['dec']) == 3 && substr($match['dec'],-1) == '0')) {
					$nrDec = 2;
				}
			}
		}
		$priceamount = number_format(round($float,$nrDec),$nrDec,',','.');
		if ($class == 'raw') return 'E '.$priceamount;
		if ($class != '') {
			$class = ' class="'.$class.'"';
		}
		$price = '<span class="'.$priceclass.'">&euro; <span'.$class.'>'.$priceamount.'</span></span>';
		return $price;
	}

	/**
	 * @param \Joomla\Registry\Registry $profile
	 * @param string $prefix
	 * @return string
	 */
	public static function formatAdres ($profile, $prefix = '') {
		$html = array();
		$html[] = $profile->get($prefix . 'address1', '') . ' ' . $profile->get($prefix . 'address2', '');
		$html[] = $profile->get($prefix . 'postal_code', '') . ' ' . $profile->get($prefix . 'city', '');
		return implode('<br/>', $html);

	}

 	public static function getMessageDiv($message,$type='info') {
		return '<div class="box-'.$type.'">'.$message.'</div>';
	}

 	public static function infobox($content,$title='',$options=array(),$event=array()) {
		$readmore = JArrayHelper::getValue($options,'readmore','')?$options['readmore']:'';
		$className = JArrayHelper::getValue($options,'className','')?$options['className']:'infobox';
		$boxContent = JArrayHelper::getValue($options,'boxContent','')?$options['boxContent']:'';
		$classArr = explode(' ',$className);
		$firstClass = array_shift($classArr);
		BixTools::infoBox($firstClass,$options,$event);
		$title = $title?'<strong class="title">'.$title.'</strong><br/>':'';
		$html = array();
		$html[] = '<strong class="'.$className.'"><span class="infoboxContent hidden">'.$title.''.$content.''.$readmore.'</span>'.$boxContent.'</strong>';
		return implode($html);
	}

}
