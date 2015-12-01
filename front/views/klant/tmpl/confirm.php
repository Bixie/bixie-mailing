<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */


// no direct access
defined('_JEXEC') or die;
?>

<div class="bix-mailing">
	<?php if ($this->params->get('show_page_heading')): ?>
		<div class="uk-grid">
			<div class="uk-width-1-1">
				<h1 class="uk-h3"><?php echo $this->page_heading; ?></h1>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($this->access == 0): ?>
		<div class="uk-alert uk-alert-warning">
			<i class="uk-icon-exclamation-triangle uk-margin-small-right"></i>
			<?php echo JText::_('COM_BIXMAILING_DASHBOARD_LOGIN_TOEGANG'); ?>
		</div>
	<?php else: ?>
		<div class="uk-alert"><?php echo JText::sprintf('COM_BIXMAILING_KLANT_CONFIRMED_SPR',$this->klantProfile->get('klantnummer', '-'), $this->klant->id); ?></div>
		<div class="uk-grid">
			<div class="uk-width-2-3">
				<a class="uk-button uk-float-right"
				   href="<?php echo JRoute::_('index.php?Itemid=' . $this->params->get('menuKlant', 101)) ?>">
					<i class="uk-icon-user uk-margin-small-right"></i><?php echo JText::_('COM_BIXMAILING_KLANT_NOGMAALS'); ?>
				</a>
			</div>
			<div class="uk-width-1-3">
				<a class="uk-button"
				   href="<?php echo JRoute::_('index.php?Itemid=' . $this->params->get('menuDashboard', 101)) ?>">
					<i class="uk-icon-dashboard uk-margin-small-right"></i><?php echo JText::_('COM_BIXMAILING_DASHBOARD_NAV'); ?>
				</a>
			</div>
		</div>

	<?php endif; ?>
</div>


