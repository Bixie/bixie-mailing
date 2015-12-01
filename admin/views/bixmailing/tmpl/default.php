<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */

defined('_JEXEC') or die('Restricted access'); ?>
<div class="uk-grid bps-cpanel">
	<div class="uk-width-1-2">
		<fieldset>
			<div class="uk-grid">
				<div class="uk-width-1-4">
					<?php
					$link = "index.php?option=" . COM_BIXMAILING . "&view=mailings";
					$this->_quickiconButton($link, "icon-48-mailing.png", JText::_('COM_BIXMAILING_MAILINGS'), BIX_ADMIN_ASSETS . "images/");
					?>
				</div>
				<div class="uk-width-1-4">
					<?php
					$link = "index.php?option=" . COM_BIXMAILING . "&view=massas";
					$this->_quickiconButton($link, "icon-48-massa.png", JText::_('COM_BIXMAILING_MASSAS'), BIX_ADMIN_ASSETS . "images/");
					?>
				</div>
				<div class="uk-width-1-4">
					<?php
					$link = "index.php?option=" . COM_BIXMAILING . "&view=templates";
					$this->_quickiconButton($link, "icon-48-template.png", JText::_('COM_BIXMAILING_TEMPLATES'), BIX_ADMIN_ASSETS . "images/");
					?>
				</div>
			</div>
		</fieldset>
		<div class="content">
			<?php
			echo JHtml::_('sliders.start', 'mainpanel-sliders', array('useCookie' => '1'));

			foreach ($this->panels as $module) {
				$output = JModuleHelper::renderModule($module);
				$params = new JRegistry;
				$params->loadString($module->params);
				if ($params->get('automatic_title', '0') == '0') {
					echo JHtml::_('sliders.panel', $module->title, 'mainpanel-panel-' . $module->name);
				} elseif (method_exists('mod' . $module->name . 'Helper', 'getTitle')) {
					echo JHtml::_('sliders.panel', call_user_func_array(array('mod' . $module->name . 'Helper', 'getTitle'), array($params)), 'mainpanel-panel-' . $module->name);
				} else {
					echo JHtml::_('sliders.panel', JText::_('MOD_' . $module->name . '_TITLE'), 'mainpanel-panel-' . $module->name);
				}
				echo $output;
			}

			echo JHtml::_('sliders.end');
			?>
		</div>
	</div>
	<div class="uk-width-1-2">
		<?php
		echo JHtml::_('sliders.start', 'panel-sliders', array('useCookie' => '1'));

		foreach ($this->modules as $module) {
			$output = JModuleHelper::renderModule($module);
			$params = new JRegistry;
			$params->loadString($module->params);
			if ($params->get('automatic_title', '0') == '0') {
				echo JHtml::_('sliders.panel', $module->title, 'cpanel-panel-' . $module->name);
			} elseif (method_exists('mod' . $module->name . 'Helper', 'getTitle')) {
				echo JHtml::_('sliders.panel', call_user_func_array(array('mod' . $module->name . 'Helper', 'getTitle'), array($params)), 'cpanel-panel-' . $module->name);
			} else {
				echo JHtml::_('sliders.panel', JText::_('MOD_' . $module->name . '_TITLE'), 'cpanel-panel-' . $module->name);
			}
			echo $output;
		}

		echo JHtml::_('sliders.end');
		?>
	</div>
</div>
 
