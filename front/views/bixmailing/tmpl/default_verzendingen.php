<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */

// no direct access
defined('_JEXEC') or die;
$statusClass = array('incompleet' => 'danger', 'nieuw' => 'warning', 'verwerkt' => 'success', 'opgeslagen' => 'success', 'gemaild' => 'success');

?>
<?php foreach ($this->mailings as $bixMailing) : ?>
	<li>
		<div class="uk-grid uk-panel uk-panel-box">
			<div class="uk-width-medium-2-3 uk-text-truncate">
				<span
					class="uk-badge uk-badge-<?php echo $statusClass[$bixMailing->status]; ?>"><?php echo $bixMailing->status; ?></span>
				<span class="uk-text-info"><?php echo $bixMailing->naam; ?></span><br/>

				<div class="uk-grid uk-margin-top">
					<div class="uk-width-1-2">
						<span
							class="uk-text-danger"><?php echo JText::_('BIX_MAILING_TYPE_' . strtoupper($bixMailing->type)); ?></span><br/>
					</div>
					<div class="uk-width-1-2">
						<i class="uk-icon-clock-o uk-margin-small-right" data-uk-tooltip
						   title="<?php echo JText::_('BIX_MAILING_AANGEMELD'); ?>"></i>
						<?php echo JHtml::_('date', $bixMailing->aangemeld, 'd-m-Y H:i:s'); ?><br/>
						<i class="uk-icon-circle uk-margin-small-right" data-uk-tooltip
						   title="<?php echo JText::_('BIX_MAILING_GEWICHT'); ?>"></i>
						<?php echo BixHelper::getWeigth($bixMailing->gewicht); ?>
					</div>
				</div>
				<div class="uk-grid uk-margin-top">
					<div class="uk-width-1-2">
						<a class="uk-button uk-button-small uk-button-primary"
						   href="<?php echo $bixMailing->trace_url; ?>" target="_blank">
							<i class="uk-icon-link uk-margin-small-right" data-uk-tooltip
							   title="<?php echo JText::_('COM_BIXMAILING_MAILING_TRACE'); ?>"></i>
							<?php echo JText::_('BIX_MAILING_TRACK_TRACE'); ?>
						</a>
					</div>
					<div class="uk-width-1-2">
						<?php if ($bixMailing->vervoerder == 'GLS') : ?>
						<i class="uk-icon-ticket uk-margin-small-right" data-uk-tooltip
						   title="<?php echo JText::_('BIX_MAILING_GLS_PAKKETNUMMER'); ?>"></i><?php echo $bixMailing->trace_nl; ?>
							<br/>
							<?php if ($bixMailing->trace_btl) : ?>
							<i class="uk-icon-barcode uk-margin-small-right" data-uk-tooltip
							   title="<?php echo JText::_('BIX_MAILING_GLS_TRACEID'); ?>"></i><?php echo $bixMailing->trace_btl; ?>
								<br/>
							<?php endif; ?>
							<?php if ($bixMailing->trace_gp) : ?>
							<i class="uk-icon-barcode uk-margin-small-right" data-uk-tooltip
							   title="<?php echo JText::_('BIX_MAILING_GLS_GPNUMMER'); ?>"></i><?php echo $bixMailing->trace_gp; ?>
								<br/>
							<?php endif; ?>
						<?php else : ?>
						<i class="uk-icon-barcode uk-margin-small-right" data-uk-tooltip
						   title="<?php echo JText::_('BIX_MAILING_POSTNL_BARC_NL'); ?>"></i><?php echo $bixMailing->trace_nl; ?>
							<br/>
							<?php if ($bixMailing->trace_btl) : ?>
							<i class="uk-icon-globe uk-margin-small-right" data-uk-tooltip
							   title="<?php echo JText::_('BIX_MAILING_POSTNL_BARC_BTL'); ?>"></i><?php echo $bixMailing->trace_btl; ?>
								<br/>
							<?php endif; ?>
						<?php endif; ?>
					</div>
				</div>

			</div>
			<div class="uk-width-medium-1-3 uk-text-truncate">
				<div class="uk-thumbnail uk-width-1-1" style="padding: 10px">
					<?php echo $bixMailing->adresnaam; ?><br/>
					<?php echo $bixMailing->straat; ?> <?php echo $bixMailing->huisnummer; ?> <?php echo $bixMailing->huisnummer_toevoeging; ?>
					<br/>
					<?php echo $bixMailing->postcode; ?> <?php echo $bixMailing->plaats; ?>
					, <?php echo $bixMailing->land; ?>
				</div>
				<br/>
				<i class="uk-icon-tag uk-margin-small-right" data-uk-tooltip
				   title="<?php echo JText::_('BIX_MAILING_REFERENTIE'); ?>"></i><?php echo $bixMailing->referentie; ?>
				<br/>

				<?php if ($bixMailing->aang) : ?>
					<i class="uk-icon-check uk-margin-small-right uk-text-success"></i><?php echo JText::_('COM_BIXMAILING_MAILING_WEL_AANGETEKEND'); ?>
				<?php else : ?>
					<i class="uk-icon-ban uk-margin-small-right uk-text-danger"></i><?php echo JText::_('COM_BIXMAILING_MAILING_NIET_AANGETEKEND'); ?>
				<?php endif; ?>


				<br/>
				<i class="uk-icon-user uk-margin-small-right" data-uk-tooltip
				   title="<?php echo JText::_('BIX_MAILING_KLANTNAAM'); ?>"></i><?php echo $bixMailing->user_company; ?>
			</div>
		</div>
	</li>
<?php endforeach; ?>
