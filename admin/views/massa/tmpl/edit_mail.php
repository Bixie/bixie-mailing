<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$currentMailtype = '';
?>
<div class="uk-panel uk-panel-box uk-form-stacked uk-margin-top">
	<input name="batch[task]" id="batch-task" type="hidden" value="mail"/>
	<input name="cid[]" type="hidden" value="<?php echo $this->item->id; ?>"/>

	<div class="uk-grid">
		<div class="uk-width-medium-1-3">
			<div class="uk-form-row">
				<label class="uk-form-label"><?php echo JText::_('COM_BIXMAILING_MAILINGS_MAILTEMPLATE'); ?></label>

				<div
					class="uk-form-controls"><?php echo JHtml::_('select.genericlist', $this->mailTemplatesOptions, 'batch[event]', 'class="uk-form-width-medium"'); ?>
					<button id="load-template" class="uk-button uk-button-small" type="button"><i
							class="uk-icon-refresh"></i></button>
				</div>
				<ul class="uk-list">
					<?php if ($this->mailTemplates) : ?>
						<?php foreach ($this->mailTemplates as $mailTemplate) :
							$hidden = $mailTemplate->type == $currentMailtype ? '' : ' class="uk-hidden"';
							?>
							<li data-template-type="<?php echo $mailTemplate->type; ?>"<?php echo $hidden; ?>>
								<button type="button"
										class="uk-button uk-width-1-1"><?php echo $mailTemplate->onderwerp; ?></button>
								<div class="content uk-hidden"><?php echo $mailTemplate->content; ?></div>
							</li>
						<?php endforeach; ?>
					<?php endif; ?>
				</ul>
			</div>
		</div>
		<div class="uk-width-medium-2-3">
			<div class="uk-text-right uk-margin-bottom">
				<button type="submit" class="uk-button uk-button-primary"
						onclick="Joomla.submitbutton('mailing.batch');" id="bix-sendmail">
					<i class="uk-icon-envelope-o uk-margin-small-right"></i><?php echo JText::_('COM_BIXMAILING_BATCH_SENDMAIL'); ?>
				</button>
				<button type="button" class="uk-button" id="bix-cancelmail">
					<i class="uk-icon-ban uk-margin-small-right"></i><?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
				</button>
			</div>
			<div id="template-editor" class="uk-hidden">
				<div class="uk-form-row">
					<label class="uk-form-label"><?php echo JText::_('BIX_MAILING_TEMPLATE_ONDERWERP'); ?></label>

					<div class="uk-form-controls"><input name="batch[subject]" type="text" value=""
														 class="uk-form-width-large uk-form-large"/></div>
				</div>
				<div class="uk-form-row">
					<textarea name="batch[template]" data-uk-markdownarea></textarea>
				</div>
			</div>
		</div>
	</div>
</div>
