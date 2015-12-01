<?php
/**
 *	com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *	Bixie.nl
 *
 */

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.formvalidation');

// pr($this->item);
?>
<script type="text/javascript">
    Joomla.submitbutton = function(task)
	{
		if (task == 'template.cancel' || document.formvalidator.isValid(document.id('template-form'))) {
			Joomla.submitform(task, document.id('template-form'));
		}
		else {
			jQuery.UIkit.notify('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>','warning');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_bixmailing&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="template-form" class="uk-form form-validate">
	<div class="uk-grid">
		<div class="uk-width-7-10">
			<div class="uk-panel uk-panel-box">
				<fieldset class="uk-form-horizontal">
					<legend><?php echo JText::_('COM_BIXPRINTSHOP_LEGEND_ALGEMEEN'); ?></legend>
					<?php foreach($this->form->getFieldset('basic') as $field): ;?>
						<div class="uk-form-row">
							<?php echo $field->label; ?>
							<div class="uk-form-controls"><?php echo $field->input; ?></div>
						</div>
					<?php endforeach; ?>
				</fieldset>
			</div>
		</div>
		<div class="uk-width-3-10">
			<div class="uk-panel uk-panel-box">
				<fieldset class="uk-form-stacked">
					<legend><?php echo JText::_('COM_BIXPRINTSHOP_LEGEND_PARAMS'); ?></legend>
					<?php foreach($this->form->getFieldset('params') as $field): ;?>
						<div class="uk-form-row">
							<?php echo $field->label; ?>
							<div class="uk-form-controls"><?php echo $field->input; ?></div>
						</div>
					<?php endforeach; ?>
				</fieldset>
			</div>
		</div>
	</div>
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="delID" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
