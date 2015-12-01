<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */


// no direct access
defined('_JEXEC') or die;

?>
<script>
</script>

<div class="bix-dashboard">
	<?php if ($this->params->get('show_page_heading')): ?>
		<div class="uk-grid">
			<div class="uk-width-1-1">
				<h1 class="uk-h3"><?php echo $this->page_heading; ?></h1>
			</div>
		</div>
	<?php endif; ?>
	<?php if ($this->user->id == 0): ?>
		<div class="uk-grid">
			<div class="uk-width-2-3">
				<div class="uk-alert uk-alert-warning">
					<i class="uk-icon-exclamation-triangle uk-margin-small-right"></i>
					<?php echo JText::_('COM_BIXMAILING_DASHBOARD_LOGIN_TOEGANG'); ?>
				</div>
			</div>
			<div class="uk-width-1-3">
				<div class="uk-panel uk-panel-box">
					<?php
					jimport('joomla.application.module.helper');
					$renderer = $this->document->loadRenderer('module');
					$contents = '';
					foreach (JModuleHelper::getModules('login') as $mod) {
						$contents .= $renderer->render($mod, array('style' => 'blank'));
					}
					echo $contents;
					?>
				</div>
			</div>
		</div>
	<?php else: ?>
		<div class="uk-grid">
			<div class="uk-width-medium-1-4">
				<?php echo BixHelper::renderTemplate('bixmailing', 'default', 'nav', array('params' => $this->params, 'user' => $this->user, 'userProfile' => $this->userProfile)) ?>
				<div class="uk-panel">
					<div class="uk-alert"><?php echo JText::_('COM_BIXMAILING_MASSAMAIL_DESCR'); ?></div>

				</div>
			</div>
			<div class="uk-width-medium-3-4">
				<div class="uk-panel uk-panel-box uk-margin-small-bottom" id="bix-massaform">
					<?php echo $this->loadTemplate('form'); ?>
				</div>
				<div id="bix-fileupload" data-bix-upload="{allowedExt:['pdf'],callback:'massamail'}"></div>
			</div>
		</div>
	<?php endif; ?>
</div>


