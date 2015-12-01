<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */


// no direct access
defined('_JEXEC') or die;

?>

<div class="bix-account">
	<?php if ($this->params->get('show_page_heading')): ?>
	<div class="uk-width-1-1">
		<h1 class="uk-article-title"><?php echo $this->page_heading; ?></h1>
	</div>
	<?php endif; ?>
	<p>
		<?php
		jimport('joomla.application.module.helper');
		$renderer = $this->document->loadRenderer('module');
		$contents = '';
		foreach (JModuleHelper::getModules('account-activeren') as $mod) {
			$contents .= $renderer->render($mod, array('style' => 'raw'));
		}
		echo $contents;
		?>
	</p>
	<?php if (!empty($this->activatieData['status'])) : ?>
	    <div class="uk-alert uk-alert-<?php echo $this->activatieData['statusClass']; ?>">
			<i class="uk-icon-<?php echo $this->activatieData['statusIcon']; ?> uk-margin-small-right"></i>
			<?php echo JText::_('BIX_MAILING_KLANT_ACTIVATE_' . $this->activatieData['status']); ?>
		</div>
		<?php if ($this->activatieData['status'] == 'PASSWORD_ALREADY_SET') : ?>
			<?php
				$contents = '';
				foreach (JModuleHelper::getModules('login') as $mod) {
				$contents .= $renderer->render($mod, array('style' => 'raw'));
				}
				echo $contents;
				  ?>
			<?php elseif ($this->activatieData['status'] == 'KEY_VALID') : ?>
			<div class="uk-text-center uk-margin-top">
				<a href="/index.php?option=com_bixmailing&task=klant.activate&k=<?php echo $this->activatieData['key']; ?>"
				   class="uk-button uk-button-primary uk-button-large"><i
						class="uk-icon-user uk-margin-right"></i><?php echo JText::_('BIX_MAILING_KLANT_ACTIVATE_LOGIN_BUTTON'); ?>
					<i class="uk-icon-arrow-right uk-margin-left"></i></a>
			</div>
			<?php endif; ?>
	<?php endif; ?>
</div>


