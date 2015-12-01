<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */


// no direct access
defined('_JEXEC') or die;

?>

<div class="bix-dashboard">
	<div class="uk-grid">
		<div class="uk-width-medium-1-4">
			<?php echo BixHelper::renderTemplate('bixmailing', 'default', 'nav', array('params' => $this->params, 'user' => $this->user, 'userProfile' => $this->userProfile)) ?>
			<div class="uk-panel">
				<div class="uk-alert"><?php echo JText::_('COM_BIXMAILING_GEGEVENS_DESCR'); ?></div>

			</div>
		</div>
		<div class="uk-width-medium-3-4">
			<div class="uk-panel uk-panel-box uk-margin-small-bottom" id="bix-klantform">
				<h3 class="uk-panel-header"><?php echo $this->page_heading; ?></h3>
				<div class="uk-grid">
					<div class="uk-width-1-2">
						<?php echo $this->loadTemplate('custom'); ?>
					</div>
					<div class="uk-width-1-2">
						<fieldset>
							<legend><?php echo JText::_('COM_BIXMAILING_GEGEVENS_SITEGEGEVENS');?></legend>
							<dl class="dl-horizontal">
								<dt>
									<?php echo JText::_('COM_USERS_PROFILE_NAME_LABEL'); ?>
								</dt>
								<dd>
									<?php echo $this->data->name; ?>
								</dd>
								<dt>
									<?php echo JText::_('COM_USERS_PROFILE_USERNAME_LABEL'); ?>
								</dt>
								<dd>
									<?php echo htmlspecialchars($this->data->username); ?>
								</dd>
								<dt>
									<?php echo JText::_('COM_BIXMAILING_GEGEVENS_EMAIL'); ?>
								</dt>
								<dd>
									<?php echo $this->data->email; ?>
								</dd>
								<dt>
									<?php echo JText::_('COM_BIXMAILING_GEGEVENS_PASS'); ?>
								</dt>
								<dd>
									***<br/>
									<small>Bewerk profiel om het wachtwoord te wijzigen</small>
								</dd>
							</dl>

						</fieldset>

						<?php if (JFactory::getUser()->id == $this->data->id) : ?>
							<div class="uk-text-right">
									<a class="uk-button uk-button-primary" href="<?php echo JRoute::_('index.php?Itemid=' . $this->params->get('menuEditklant', 101)) ?>">
										<i class="uk-icon-edit uk-margin-small-right"></i><?php echo JText::_('COM_USERS_EDIT_PROFILE'); ?></a>
							</div>
						<?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>


