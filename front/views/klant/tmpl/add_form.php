<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */

// no direct access
defined('_JEXEC') or die;
JHtml::_('behavior.formvalidation');
?>

<div class="uk-grid">
	<div class="uk-width-1-1">
		<form class="form-validate form-horizontal" id="bix-userform" name="bix-klant" method="post"
			  action="/index.php?option=com_bixmailing" data-bix-userform data-bix-ajax-submit>
			<?php foreach ($this->form->getFieldsets() as $fieldset): // Iterate through the form fieldsets and display each one.?>
				<?php $fields = $this->form->getFieldset($fieldset->name);?>
				<?php if (count($fields)):?>
					<fieldset>
						<?php if (isset($fieldset->label)):// If the fieldset has a label set, display it as the legend.?>
							<legend><?php echo JText::_($fieldset->label);?></legend>
						<?php endif;?>
						<?php foreach ($fields as $field) :// Iterate through the fields in the set and display them.?>
							<?php if ($field->hidden):// If the field is hidden, just display the input.?>
								<?php echo $field->input;?>
							<?php else:?>
								<div class="control-group">
									<div class="control-label">
										<?php echo $field->label; ?>
										<?php if (!$field->required && $field->type != 'Spacer') : ?>
											<span class="optional"><?php echo JText::_('COM_USERS_OPTIONAL');?></span>
										<?php endif; ?>
									</div>
									<div class="controls">
										<?php echo $field->input;?>
									</div>
								</div>
							<?php endif;?>
						<?php endforeach;?>
					</fieldset>
				<?php endif;?>
			<?php endforeach;?>

			<div class="uk-grid uk-margin-top">
					<div class="uk-width-2-3">
						<br/>
					</div>
					<div class="uk-width-1-3">
						<button class="uk-button uk-button-primary uk-width-1-1">
							<i class="uk-icon-check uk-margin-small-right"></i><?php echo JText::_('COM_BIXMAILING_KLANT_SUBMIT'); ?>
						</button>

					</div>
				</div>
			<?php echo JHtml::_('form.token'); ?>
			<input type="hidden" name="task" value="klant.addUser">
		</form>
	</div>
</div>
