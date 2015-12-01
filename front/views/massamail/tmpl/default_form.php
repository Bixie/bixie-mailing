<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */


// no direct access
defined('_JEXEC') or die;

?>

<div class="uk-grid">
	<div class="uk-width-1-1">
		<form class="uk-form" id="bix-massamailform" name="bix-massamail" method="post"
			  action="/index.php?option=com_bixmailing" data-bix-ajax-submit>
			<h3 class="uk-panel-header"><?php echo JText::_('COM_BIXMAILING_MASSAMAIL_FORMTITLE'); ?></h3>
			<fieldset>
				<p><?php echo JText::_('COM_BIXMAILING_MASSAMAIL_FORMDESC'); ?></p>

				<div class="uk-grid">
					<div class="uk-width-2-3">
						<div class="uk-form-row">
							<label for="massa-type" class="uk-form-label" data-uk-tooltip
								   title="<?php echo JText::_('COM_BIXMAILING_MASSAMAIL_TYPE_DESC'); ?>"><?php echo JText::_('COM_BIXMAILING_MASSAMAIL_TYPE'); ?></label>

							<div class="uk-form-controls">
								<select id="massa-type" name="jform[type]" class="uk-width-1-1">
									<option
										value="massa_insteek_enkel"><?php echo JText::_('BIX_MAILING_TYPE_MASSA_INSTEEK_ENKEL'); ?></option>
									<option
										value="massa_insteek_dubbel"><?php echo JText::_('BIX_MAILING_TYPE_MASSA_INSTEEK_DUBBEL'); ?></option>
									<option
										value="massa_insteek_meer"><?php echo JText::_('BIX_MAILING_TYPE_MASSA_INSTEEK_MEER'); ?></option>
								</select>
							</div>
						</div>
						<div class="uk-form-row">
							<div class="uk-form-controls">
								<input type="hidden" name="jform[aang]" value="0">
								<input type="checkbox" id="massa-aang" name="jform[aang]" value="1"/>
								<label for="massa-aang" class="uk-form-label" data-uk-tooltip
									   title="<?php echo JText::_('COM_BIXMAILING_MASSAMAIL_AANGETEKEND_DESC'); ?>">
									<?php echo JText::_('COM_BIXMAILING_MASSAMAIL_AANGETEKEND'); ?></label>
							</div>
						</div>
						<div class="uk-form-row">
							<label
								class="uk-form-label"><?php echo JText::_('COM_BIXMAILING_MASSAMAIL_OPMERKINGEN'); ?></label>

							<div class="uk-form-controls">
								<textarea name="jform[opmerking]" class="uk-width-1-1" rows="4"></textarea>
							</div>
						</div>
						<div class="uk-grid uk-margin-top">
							<div class="uk-width-2-3">
								<br/>
							</div>
							<div class="uk-width-1-3">
								<button class="uk-button uk-button-primary uk-width-1-1">
									<i class="uk-icon-check uk-margin-small-right"></i><?php echo JText::_('COM_BIXMAILING_MASSAMAIL_SUBMIT'); ?>
								</button>

							</div>
						</div>
					</div>
					<div class="uk-width-1-3">
						<label><?php echo JText::_('COM_BIXMAILING_MASSAMAIL_BESTANDEN'); ?></label>
						<ul id="bix-formfiles" class="uk-list uk-list-striped">
						</ul>
					</div>
				</div>
			</fieldset>
			<?php echo JHtml::_('form.token'); ?>
			<input type="hidden" name="jform[user_id]" value="<?php echo $this->user->id; ?>">
			<input type="hidden" name="task" value="bixmailing.saveMassa">
		</form>
	</div>
</div>
