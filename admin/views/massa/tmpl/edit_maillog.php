<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

use \Michelf\Markdown;

?>
<div class="uk-panel uk-panel-box uk-form-stacked">
	<fieldset class="uk-form-horizontal">
		<legend><?php echo JText::_('COM_BIXPRINTSHOP_LEGEND_MAILLOGS'); ?></legend>
		<?php if (count($this->mailLogs)) : ?>
			<ul class="uk-list uk-list-striped">
				<?php foreach ($this->mailLogs as $mailLog) : ?>
					<li class="uk-clearfix">
						<a class="uk-button uk-float-right"
						   title="<?php echo JText::_('COM_BIXMAILING_MAILLOG_DETAILS'); ?>"
						   href="#details-maillog-<?php echo $mailLog->id; ?>" data-uk-modal><i
								class="uk-icon-search"></i></a>

						<div class="uk-text-truncate">
							<?php echo $mailLog->onderwerp; ?><br/>
							<strong><?php echo JText::_('COM_BIXMAILING_MAILLOG_VERZONDEN'); ?>
								:</strong> <?php echo JHtml::_('date', $mailLog->created, 'D d M Y H:i:s'); ?>
						</div>

						<div id="details-maillog-<?php echo $mailLog->id; ?>" class="uk-modal">
							<div class="uk-modal-dialog">
								<a class="uk-modal-close uk-close"></a>
								<dl class="uk-description-list">
									<dt><?php echo JText::_('COM_BIXMAILING_MAILLOG_ONTVANGERS'); ?></dt>
									<dd><?php echo $mailLog->ontvangers; ?></dd>
									<dt><?php echo JText::_('COM_BIXMAILING_MAILLOG_ONDERWERP'); ?></dt>
									<dd><span class=""><?php echo $mailLog->onderwerp; ?></span></dd>
									<dt><?php echo JText::_('COM_BIXMAILING_MAILLOG_TEKST'); ?></dt>
									<dd><?php echo Markdown::defaultTransform(nl2br($mailLog->tekst)); ?></dd>
								</dl>

							</div>
						</div>

					</li>
				<?php endforeach; ?>
			</ul>
		<?php else: ?>
			<em><?php echo JText::_('COM_BIXPRINTSHOP_MAILLOGS_NONE'); ?></em>
		<?php endif; ?>
	</fieldset>
</div>
