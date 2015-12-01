<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */

// no direct access
defined('_JEXEC') or die;
//pr($this->model->massasState);

?>
<?php foreach ($this->massas as $massa) : ?>
	<li>
		<div class="uk-grid uk-panel-box">
			<div class="uk-width-medium-2-3">
				<span class="uk-text-info"><?php echo $massa->naam; ?></span>
			</div>
			<div class="uk-width-medium-1-3">
				<ol class="uk-list uk-margin-remove">
					<li><i class="uk-icon-clock-o uk-margin-small-right"
						   title="<?php echo JText::_('COM_BIXMAILING_MASSAMAILING_CREATED'); ?>"
						   data-uk-tooltip></i>
						<?php echo JHtml::_('date', $massa->created, JText::_('COM_BIXMAILING_DATETIME')); ?>
					</li>
					<?php if ($massa->modified != '0000-00-00 00:00:00') : ?>
						<li><i class="uk-icon-arrow-circle-o-up uk-margin-small-right"
							   title="<?php echo JText::_('COM_BIXMAILING_MASSAMAILING_MODIFIED'); ?>"
							   data-uk-tooltip></i>
							<?php echo JHtml::_('date', $massa->modified, JText::_('COM_BIXMAILING_DATETIME')); ?>
						</li>
					<?php endif; ?>
				</ol>
				<div class="uk-margin-small-top uk-margin-small-bottom">
					<?php echo JText::_('COM_BIXMAILING_MASSAMAIL_AANGETEKEND'); ?>:
					<?php if ($massa->aang) : ?>
						<i class="uk-icon-check uk-margin-small-right uk-text-success"></i><?php echo JText::_('JYES'); ?>
					<?php else : ?>
						<i class="uk-icon-ban uk-margin-small-right uk-text-danger"></i><?php echo JText::_('JNO'); ?>
					<?php endif; ?>
				</div>
			</div>
			<div class="uk-width-medium-1-2">
				<div class="uk-margin-top">
					<strong><?php echo JText::_('COM_BIXMAILING_MASSAMAIL_BESTANDEN'); ?>:</strong><br/>
					<?php if (count($massa->bestanden)): ?>
						<ol class="uk-list uk-list-striped uk-margin-remove">
							<?php foreach ($massa->bestanden as $hash) :
								$fileInfo = BixHelper::fileInfo($hash);
								?>
								<li title="<?php echo $fileInfo->fileName; ?>"><i
										class="uk-icon-paperclip uk-margin-small-right"></i>
									<a href="<?php echo $fileInfo->url; ?>"
									   download><?php echo $fileInfo->fileNameShort; ?></a></li>
							<?php endforeach; ?>
						</ol>
					<?php endif; ?>
				</div>
			</div>
			<div class="uk-width-medium-1-2">
				<div class="uk-margin-top">
					<strong><?php echo JText::_('COM_BIXMAILING_MASSAMAIL_OPMERKINGEN'); ?>:</strong><br/>
					<?php if ($massa->opmerking) : ?>
						<?php echo nl2br($massa->opmerking); ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</li>
<?php endforeach; ?>
