<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */


// no direct access
defined('_JEXEC') or die;
($this->item->bestanden);
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
		<div class="uk-alert"><?php echo JText::_('COM_BIXMAILING_MASSAMAIL_CONFIRMED'); ?></div>
		<div class="uk-grid">
			<div class="uk-width-2-3">
				<a class="uk-button uk-float-right"
				   href="<?php echo JRoute::_('index.php?Itemid=' . $this->params->get('menuMassamails', 101)) ?>">
					<i class="uk-icon-users uk-margin-small-right"></i><?php echo JText::_('COM_BIXMAILING_MASSAMAIL_NOGMAALS'); ?>
				</a>
			</div>
			<div class="uk-width-1-3">
				<a class="uk-button"
				   href="<?php echo JRoute::_('index.php?Itemid=' . $this->params->get('menuDashboard', 101)) ?>">
					<i class="uk-icon-dashboard uk-margin-small-right"></i><?php echo JText::_('COM_BIXMAILING_DASHBOARD_NAV'); ?>
				</a>
			</div>
		</div>
		<h3><?php echo $this->item->naam; ?></h3>
		<div class="uk-grid">
			<div class="uk-width-1-2">
				<em><?php echo JText::_('BIX_MAILING_TYPE_' . strtoupper($this->item->type)); ?></em><br/>
				<?php echo JText::_('COM_BIXMAILING_MASSAMAIL_AANGETEKEND'); ?>:
				<?php if ($this->item->aang) : ?>
					<i class="uk-icon-check uk-margin-small-right uk-text-success"></i><?php echo JText::_('JYES'); ?>
				<?php else : ?>
					<i class="uk-icon-ban uk-margin-small-right uk-text-danger"></i><?php echo JText::_('JNO'); ?>
				<?php endif; ?>
				<?php if ($this->item->opmerking) : ?>
					<p><?php echo nl2br($this->item->opmerking); ?></p>
				<?php endif; ?>
			</div>
			<div class="uk-width-1-2">
				<strong><?php echo JText::_('COM_BIXMAILING_MASSAMAIL_BESTANDEN'); ?>:</strong><br/>
				<?php if (count($this->item->bestanden)): ?>
					<ul class="uk-list uk-list-striped">
						<?php foreach ($this->item->bestanden as $hash) :
							$fileInfo = BixHelper::fileInfo($hash);
							?>
							<li>
								<i class="uk-icon-paperclip uk-margin-small-right"></i><?php echo $fileInfo->fileName; ?>
							</li>

						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>
		</div>

	<?php endif; ?>
</div>


