<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */
/**
 * @var BixmailingViewUpload $this
 * @var BixMailingMailing $bixMailing
 */

// no direct access
defined('_JEXEC') or die;

if ($this->state->get('vervoerder', '') != 'gls') {
	?>
	<div class="bix-placeholder uk-flex uk-flex-center uk-flex-middle" style="height: 150px;"><i class="uk-icon-spinner uk-icon-medium uk-icon-spin"></i></div>
	<?php
	return;
}

// pr($this->newMailings[0]);
$statusClass = array('incompleet' => 'danger', 'nieuw' => 'warning', 'verwerkt' => 'success');
?>
<?php if (count($this->newMailings)) : ?>
<div>
	<?php
	$currentKlantnummer = false;
	foreach ($this->newMailings as $bixMailing) : ?>
	<?php if ($currentKlantnummer !== $bixMailing->klantnummer) :

	//seperator shit
	$currentKlantnummer = $bixMailing->klantnummer;
	?>
	</div>
	<?php
	if ($currentKlantnummer === '0') : //geen klantnummer alert header ?>
	<div class=" uk-margin-bottom" data-bix-mailing-incompleet="{vervoerder:'gls'}">
		<div class="uk-alert uk-alert-warning uk-margin-small-bottom uk-clearfix">
			<div class="uk-float-right uk-margin-small-top">
				<i class="uk-icon-exclamation-triangle uk-icon-medium uk-margin-right uk-margin-small-top"></i>
			</div>
			<strong class="uk-display-block uk-margin-small-top"><?php echo JText::_('BIX_MAILING_GEENKLANT'); ?></strong>
			<small><?php echo JText::_('BIX_MAILING_GEENKLANT_DESC'); ?></small>
		</div>
	</div>
	<div data-bix-klantrecords="<?php echo $currentKlantnummer; ?>-gls" class="uk-margin-bottom">
	<?php else : //start klantheader ?>
	<div class="bix-controls uk-margin-bottom"
		 data-bix-klantcontrols="{klantnummer:'<?php echo $currentKlantnummer; ?>',user_id:<?php echo $bixMailing->user_id; ?>,vervoerder:'gls'}">
		<div class="uk-alert uk-margin-small-bottom uk-clearfix">
			<div class="uk-float-right">
				<div class="bix-toggler uk-margin-small-top uk-margin-small-right" data-uk-tooltip
					 style="color: #fff"
					 title="<?php echo JText::_('COM_BIXMAILING_TOGGLEKLANTSLIDE'); ?>">
					<i class="uk-icon-plus-square-o uk-icon-small"></i></div>
			</div>
			<strong><?php echo $currentKlantnummer; ?></strong><br/>
			<?php echo $bixMailing->user_company; ?>
		</div>
		<div class="uk-grid">
			<div class="uk-width-1-2 uk-form">
				<label class="">
					<input type="checkbox" class="uk-margin-small-right" name="klantmailer[]"
						   value="<?php echo $bixMailing->id; ?>">
					<i class="uk-icon-envelope-o uk-margin-small-right"></i><?php echo JText::_('BIX_MAILING_VERSTUUR_TRACE'); ?>
				</label><br/>
				<input type="text" name="email" class="uk-form-width-medium"
					   value="<?php echo $bixMailing->user_email; ?>"
					   placeholder="<?php echo JText::_('BIX_MAILING_FILL_MAIL'); ?>"/>
			</div>
			<div class="uk-width-1-2">
				<div class="uk-margin-small-top">
					<?php echo JText::_('COM_BIXMAILING_KLANT_NRMAILINGS'); ?>:
					<div class="bix-nrmailings uk-badge uk-badge-notification uk-float-right uk-margin-right"></div>
					<br/>
					<?php echo JText::_('COM_BIXMAILING_KLANT_NRMAILSSELECT'); ?>:
					<div
						class="bix-nractivemailings uk-badge uk-badge-notification uk-badge-success uk-float-right uk-margin-right"></div>
				</div>
			</div>
		</div>
	</div>
	<div data-bix-klantrecords="<?php echo $currentKlantnummer; ?>-gls" class="uk-margin-bottom" hidden>
		<?php endif; //end klantheader ?>
		<?php else: //reg. seperator ?>
			<hr/>
		<?php
		endif;
		?>
		<div class="uk-grid">
			<div class="uk-width-2-3 uk-text-truncate">
				<span class="uk-badge uk-badge-<?php echo $statusClass[$bixMailing->status]; ?>"
					  data-bix-mailing-status="<?php echo $bixMailing->status; ?>">
					<?php echo $bixMailing->status; ?>
				</span>
				<span class="uk-text-info"><?php echo $bixMailing->naam; ?></span><br/>
				<?php echo JText::_('BIX_MAILING_TYPE_' . strtoupper($bixMailing->type)); ?><br/>
				<i class="uk-icon-link uk-margin-small-right" data-uk-tooltip
				   title="<?php echo JText::_('COM_BIXMAILING_MAILING_TRACE'); ?>"></i><a
					href="<?php echo $bixMailing->trace_url; ?>" target="_blank">
					<?php echo $bixMailing->trace_url; ?>
				</a>
				<br/>
				<i class="uk-icon-file-o uk-margin-small-right" data-uk-tooltip
				   title="<?php echo JText::_('BIX_MAILING_IMPORTBESTAND'); ?>"></i><?php echo $bixMailing->importbestand; ?>
				<div class="uk-grid">
					<div class="uk-width-1-2">
						<i class="uk-icon-clock-o uk-margin-small-right" data-uk-tooltip
						   title="<?php echo JText::_('BIX_MAILING_AANGEMELD'); ?>"></i><?php echo JHtml::_('date', $bixMailing->aangemeld, 'd-m-Y H:i:s'); ?>
						<br/>
						<i class="uk-icon-circle uk-margin-small-right" data-uk-tooltip
						   title="<?php echo JText::_('BIX_MAILING_GEWICHT'); ?>"></i>
						<?php echo BixHelper::getWeigth($bixMailing->gewicht); ?>
						<br/>
						<?php if ($bixMailing->aang) : ?>
							<i class="uk-icon-check uk-margin-small-right uk-text-success"></i><?php echo JText::_('COM_BIXMAILING_MAILING_WEL_AANGETEKEND'); ?>
						<?php else : ?>
							<i class="uk-icon-ban uk-margin-small-right uk-text-danger"></i><?php echo JText::_('COM_BIXMAILING_MAILING_NIET_AANGETEKEND'); ?>
						<?php endif; ?>
						<br/>
						<?php if ($currentKlantnummer !== '0') :
							$checked = $bixMailing->defaultMail() ? ' checked="checked"' : '';
							?>
							<label class="uk-margin-small-top">
								<i class="uk-icon-envelope-o uk-margin-small-right"></i>
								<input type="checkbox" class="uk-margin-small-right" name="mailing[]"
									   value="<?php echo $bixMailing->id; ?>"<?php echo $checked; ?>/>
								<?php echo JText::_('BIX_MAILING_VERSTUUR_TRACE'); ?>
							</label>
							<input type="hidden" name="mailingIDs[]" value="<?php echo $bixMailing->id; ?>"/>
						<?php endif; ?>
					</div>
					<div class="uk-width-1-2">
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
					</div>
				</div>
			</div>
			<div class="uk-width-1-3 uk-text-truncate">
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
				<?php if ($bixMailing->user_company) : ?>
				<i class="uk-icon-user uk-margin-small-right" data-uk-tooltip
				   title="<?php echo JText::_('BIX_MAILING_KLANTNAAM'); ?>"></i><?php echo $bixMailing->user_company; ?>
					<br/>
				<?php else : ?>
					<button class="uk-button"
							data-bix-selectuser-button="{mailingID:<?php echo $bixMailing->id; ?>,vervoerder:'gls',referentie:'<?php echo $bixMailing->referentie; ?>'}">
						<i class="uk-icon-user uk-margin-small-right"></i><?php echo JText::_('BIX_MAILING_SELECT_USER'); ?>
					</button>
				<?php endif; ?>
			</div>
		</div>
		<?php endforeach; ?>
	</div>
	<?php endif; ?>
