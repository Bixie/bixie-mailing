<?php
/**
 *	com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *	Bixie.nl
 *
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
jimport('joomla.application.component.view');
jimport('joomla.application.module.helper');

class BixmailingViewBixmailing extends JViewLegacy {

	function display($tpl = null) {
		jimport('joomla.html.pane');
		BixTools::loadCSS();

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}
		$this->modules = JModuleHelper::getModules('bm-cpanel');
		$this->panels = JModuleHelper::getModules('bm-mainpanel');
		$this->addToolbar($tpl);
		parent::display($tpl);
	}

	protected function addToolbar($tpl = null)	{

		//$state	= $this->get('State');$state->get('filter.category_id')
		$canDo	= BixmailingHelper::getActions();

		JToolBarHelper::title('<span class="color">'.JText::_('COM_BIXMAILING') . '</span> :: ' . JText::_('COM_BIXMAILING_TITLE_DASHBOARD'), 'bix-mailing');
		
		if ($canDo->get('core.admin')) {
			JToolBarHelper::preferences('com_bixmailing');
		}
	}
	
	/**
	 * This method creates a standard cpanel button
	 *
	 * @param unknown_type $link
	 * @param unknown_type $image
	 * @param unknown_type $text
	 */
	public function _quickiconButton( $link, $image, $text, $path='/administrator/images/', $target='', $onclick='' ) {
	 	if( $target != '' ) {
	 		$target = 'target="' .$target. '"';
	 	}
	 	if( $onclick != '' ) {
	 		$onclick = 'onclick="' .$onclick. '"';
	 	}
	 	if( $path === null || $path === '' ) {
	 		$path = '/administrator/images/';
	 	}
		?>
		<div class="uk-panel uk-panel-box uk-panel-box-secondary">
			<div>
				<a href="<?php echo $link; ?>" <?php echo $target;?>  <?php echo $onclick;?> class="uk-text-center">
					<img src="<?php echo $path.$image;?>" alt="<?php echo $text;?>" <?php echo $onclick;?> <?php echo $target;?> class="uk-align-center"/>
					<div><?php echo $text; ?></div>
				</a>
			</div>
		</div>
		<?php
	}

}

