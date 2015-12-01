<?php
/**
 *  com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *  Bixie.nl

 */


// no direct access
defined('_JEXEC') or die;

?>
<script>
	jQuery(function ($) {
		$('#file-tree-uploads').fileTree({ root: '/conversie/'}, function (path, hash) {
			UIkit.bixTools.download(path, hash);
		});
		$('#file-tree-massas').fileTree({ root: '/mailings/'}, function (path, hash) {
			UIkit.bixTools.download(path, hash);
		});
	});
</script>

<div class="bix-bestanden">
	<?php if ($this->params->get('show_page_heading')): ?>
		<div class="uk-grid">
			<div class="uk-width-1-1">
				<h1 class="uk-h3"><?php echo $this->page_heading; ?></h1>
			</div>
		</div>
	<?php endif; ?>
	<?php if (!$this->user->authorise('core.admin')): ?>
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
					<div class="uk-alert"><?php echo JText::_('COM_BIXMAILING_BESTANDEN_DESCR'); ?></div>

				</div>
			</div>
			<div class="uk-width-medium-3-4">
				<div class="uk-grid" data-uk-grid-match="{target:'.uk-panel'}">
					<div class="uk-width-1-2">
						<div class="uk-panel uk-panel-box">
							<h3 class="uk-panel-header"><?php echo JText::_('BIX_MAILING_CONVERSIEBESTANDEN'); ?></h3>

							<div id="file-tree-uploads"></div>
						</div>
					</div>
					<div class="uk-width-1-2">
						<div class="uk-panel uk-panel-box">
							<h3 class="uk-panel-header"><?php echo JText::_('BIX_MAILING_MASSAMAILINGBESTANDEN'); ?></h3>

							<div id="file-tree-massas"></div>
						</div>
					</div>
				</div>
				<div class="uk-panel uk-panel-box uk-margin-top">
					<div id="file-contents"></div>
				</div>
			</div>
		</div>

		<!-- This is the modal -->
		<div id="file-download-modal" class="uk-modal">
			<div class="uk-modal-dialog">
				<a class="uk-modal-close uk-close"></a>

				<p><?php echo JText::_('BIX_MAILING_BESTAND_DOWNLOADEN'); ?></p>

				<div class="uk-grid">
					<div class="uk-width-1-3">
						<a class="bix-download uk-button uk-width-1-1" href="">
							<i class="uk-icon-download uk-margin-small-right"></i>
							<?php echo JText::_('BIX_MAILING_DOWNLOADEN'); ?>
						</a>
					</div>
					<div class="uk-width-1-3">
						<button class="bix-bekijk uk-button uk-width-1-1">
							<i class="uk-icon-eye uk-margin-small-right"></i>
							<?php echo JText::_('BIX_MAILING_BEKIJKEN'); ?>
						</button>
					</div>
					<div class="uk-width-1-3">
						<button class="bix-mailings uk-button uk-width-1-1"
								data-base="<?php echo JRoute::_('index.php?Itemid=' . $this->params->get('menuDashboard', 101)) ?>">
							<i class="uk-icon-tag uk-margin-small-right"></i>
							<?php echo JText::_('BIX_MAILING_MAILINGS'); ?>
						</button>
					</div>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>


