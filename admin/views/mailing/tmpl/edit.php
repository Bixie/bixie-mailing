<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.formvalidation');

// pr($this->item);
?>
<script type="text/javascript">
	Joomla.submitbutton = function (task) {
		if (task == 'mailing.cancel' || document.formvalidator.isValid(document.id('mailing-form'))) {
			Joomla.submitform(task, document.id('mailing-form'));
		}
		else {
			jQuery.UIkit.notify('<?php echo $this->escape(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));?>', 'warning');
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_bixmailing&layout=edit&id=' . (int)$this->item->id); ?>"
	  method="post" name="adminForm" id="mailing-form" class="uk-form form-validate"
	  data-form-mailingnaam="massa.renderMailingNaam">
	<div class="uk-grid uk-grid-preserve">
		<div class="uk-width-7-10">
			<div class="uk-panel uk-panel-box">
				<fieldset class="uk-form-horizontal">
					<legend><?php echo JText::_('COM_BIXPRINTSHOP_LEGEND_ALGEMEEN'); ?></legend>
					<?php foreach ($this->form->getFieldset('basic') as $field): ; ?>
						<div class="uk-form-row">
							<?php echo $field->label; ?>
							<div class="uk-form-controls"><?php echo $field->input; ?></div>
						</div>
					<?php endforeach; ?>
				</fieldset>
				<fieldset class="uk-form-horizontal">
					<legend><?php echo JText::_('COM_BIXPRINTSHOP_LEGEND_ADRES'); ?></legend>
					<?php foreach ($this->form->getFieldset('adres') as $field): ; ?>
						<div class="uk-form-row">
							<?php echo $field->label; ?>
							<div class="uk-form-controls"><?php echo $field->input; ?></div>
						</div>
					<?php endforeach; ?>
				</fieldset>
				<fieldset class="uk-form-horizontal">
					<legend><?php echo JText::_('COM_BIXPRINTSHOP_LEGEND_ADMINISTRATIE'); ?></legend>
					<?php foreach ($this->form->getFieldset('administratie') as $field): ; ?>
						<div class="uk-form-row">
							<?php echo $field->label; ?>
							<div class="uk-form-controls"><?php echo $field->input; ?></div>
						</div>
					<?php endforeach; ?>
				</fieldset>
			</div>
			<?php if ($this->item->id) : ?>
				<?php echo $this->loadTemplate('mail'); ?>
			<?php endif; ?>

		</div>
		<div class="uk-width-3-10">
			<div class="uk-panel uk-panel-box">
				<fieldset class="uk-form-stacked">
					<legend><?php echo JText::_('COM_BIXPRINTSHOP_LEGEND_PARAMS'); ?></legend>
					<?php foreach ($this->form->getFieldset('params') as $field): ; ?>
						<div class="uk-form-row">
							<?php echo $field->label; ?>
							<div class="uk-form-controls"><?php echo $field->input; ?></div>
						</div>
					<?php endforeach; ?>
				</fieldset>
			</div>
			<div class="uk-margin-top" id="mailhistorie"
				 data-bix-loadtemplate="{view:'mailing',layout:'edit',tpl:'maillog',id:<?php echo $this->item->id; ?>,highlight:true}">
			<?php echo $this->loadTemplate('maillog'); ?>
			</div>
		</div>
	</div>
	<input type="hidden" name="task" value=""/>
	<?php echo JHtml::_('form.token'); ?>
</form>
