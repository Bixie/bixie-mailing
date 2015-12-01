<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */

// no direct access
defined('_JEXEC') or die;
$app = JFactory::getApplication();
$view = $app->input->getCmd('view', '');
$Itemid = $app->input->getInt('Itemid', 0);
?>

<div class="uk-grid">
	<div class="uk-width-1-1">
		<div class="uk-panel uk-panel-box uk-panel-box-primary">
			<?php if ($this->user->authorise('core.admin')): ?>
				<div
					class="uk-panel-badge uk-badge uk-badge-warning"><?php echo JText::_('COM_BIXMAILING_ADMIN'); ?></div>
			<?php endif; ?>
			<h4 class="uk-panel-title"><i
					class="uk-icon-user uk-margin-right"></i><?php echo JText::_('COM_BIXMAILING_MIJN_GEGEVENS'); ?>
			</h4>
			<strong><?php echo $this->user->name; ?></strong><br/>
			<dl class="uk-description-list">
				<dt><?php echo JText::_('COM_BIXMAILING_DASHBOARD_LABEL_USERNAAM'); ?></dt>
				<dd><?php echo $this->user->username; ?></dd>
				<dt><?php echo JText::_('COM_BIXMAILING_DASHBOARD_LABEL_EMAIL'); ?></dt>
				<dd><?php echo $this->user->email; ?></dd>
				<dt><?php echo JText::_('COM_BIXMAILING_DASHBOARD_LABEL_BEDRIJF'); ?></dt>
				<dd><?php echo $this->userProfile->get('bedrijfsnaam', '-'); ?></dd>
			</dl>
		</div>
	</div>
	<div class="uk-width-1-1">
		<div class="uk-panel uk-panel-box">
			<ul class="uk-nav uk-nav-side">
				<li<?php echo $view == 'bixmailing' ? ' class="uk-active"' : ''; ?>>
					<a href="<?php echo JRoute::_('index.php?Itemid=' . $this->params->get('menuDashboard', 101)) ?>">
						<i class="uk-icon-dashboard uk-margin-small-right"></i><?php echo JText::_('COM_BIXMAILING_DASHBOARD_NAV'); ?>
					</a></li>
				<li<?php echo $view == 'massamail' ? ' class="uk-active"' : ''; ?>>
					<a href="<?php echo JRoute::_('index.php?Itemid=' . $this->params->get('menuMassamails', 101)) ?>">
						<i class="uk-icon-users uk-margin-small-right"></i><?php echo JText::_('COM_BIXMAILING_MASSAMAIL_NAV'); ?>
					</a></li>
				<?php if ($this->user->authorise('core.admin')): ?>
					<li<?php echo $view == 'upload' ? ' class="uk-active"' : ''; ?>>
						<a href="<?php echo JRoute::_('index.php?Itemid=' . $this->params->get('menuUploads', 101)) ?>">
							<i class="uk-icon-upload uk-margin-small-right"></i><?php echo JText::_('COM_BIXMAILING_UPLOAD_NAV'); ?>
						</a></li>
					<li<?php echo $view == 'bestanden' ? ' class="uk-active"' : ''; ?>>
						<a href="<?php echo JRoute::_('index.php?Itemid=' . $this->params->get('menuBestanden', 101)) ?>">
							<i class="uk-icon-files-o uk-margin-small-right"></i><?php echo JText::_('COM_BIXMAILING_BESTANDEN_NAV'); ?>
						</a></li>
					<li<?php echo $Itemid == $this->params->get('menuKlantlist', 101) || $Itemid == $this->params->get('menuKlant', 101) ? ' class="uk-active"' : ''; ?>>
						<a href="<?php echo JRoute::_('index.php?Itemid=' . $this->params->get('menuKlantlist', 101)) ?>">
							<i class="uk-icon-building uk-margin-small-right"></i><?php echo JText::_('COM_BIXMAILING_KLANTLIST_NAV'); ?>
						</a>
						<ul class="uk-nav uk-subnav">
							<li class="uk-active">
								<a href="<?php echo JRoute::_('index.php?Itemid=' . $this->params->get('menuKlant', 101)) ?>">
									<i class="uk-icon-plus uk-margin-small-right"></i><?php echo JText::_('COM_BIXMAILING_KLANT_NAV'); ?>
								</a>
							</li>
						</ul>
					</li>
				<?php endif; ?>
				<li<?php echo $Itemid == $this->params->get('menuGegevens', 101) || $Itemid == $this->params->get('menuEditklant', 101) ? ' class="uk-active"' : ''; ?>>
					<a href="<?php echo JRoute::_('index.php?Itemid=' . $this->params->get('menuGegevens', 101)) ?>">
						<i class="uk-icon-user uk-margin-small-right"></i><?php echo JText::_('COM_BIXMAILING_GEGEVENS_NAV'); ?>
					</a>
					<?php if ($Itemid == $this->params->get('menuEditklant', 101)) : ?>
					<ul class="uk-nav uk-subnav">
						<li class="uk-active"><a href="<?php echo JRoute::_('index.php?Itemid=' . $this->params->get('menuEditklant', 101)) ?>"><i
									class="uk-icon-long-arrow-right uk-margin-small-right"></i><?php echo JText::_('COM_BIXMAILING_EDIT_GEGEVENS_NAV'); ?>
								</a></li>
					</ul>
					<?php endif; ?>
				</li>
			</ul>
		</div>
	</div>
</div>
