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
	<?php if (!$this->user->id): ?>
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
					<div class="uk-alert"><?php echo JText::_('COM_BIXMAILING_LIST_KLANT_DESCR'); ?></div>
				</div>
			</div>
			<div class="uk-width-medium-3-4">
				<div class="uk-panel uk-panel-box uk-margin-small-bottom" id="bix-klantform">
					<h3 class="uk-panel-header"><?php echo $this->page_heading; ?></h3>

					<div class="uk-margin" data-bix-klantlist="{detailHeight: 434}">
						<strong><?php echo JText::_('BIX_MAILING_ZOEK_KLANT'); ?></strong><br/>

						<input type="text" name="search" value="" class="uk-width-1-1 uk-form-large"
							   placeholder="<?php echo JText::_('BIX_ZOEK_OP'); ?>"/>

						<div class="uk-scrollable-box uk-text-truncate">
							<ul class="bix-klanten uk-nav uk-nav-side"></ul>
						</div>

						<div class="bix-klantdetails uk-margin">
							<div class="uk-text-large uk-flex uk-flex-middle uk-flex-center" style="height:434px"><i
									class="uk-icon-spinner uk-icon-spin uk-margin-small-right"></i></div>
						</div>
						<script type="text/klantdetailTemplate">
							<div class="uk-grid uk-grid-small">
							    <div class="uk-width-2-3">
									<h3 class="uk-margin-remove">{{bedrijfsnaam}}</h3>
									<em><?php echo JText::_('PLG_USER_BIXMAILINGSETTINGS_KLANTNUMMER_LABEL'); ?>: {{klantnummer}}</em>
							    </div>
							    <div class="uk-width-1-3 uk-text-right">
									<a href="administrator/index.php?option=com_users&task=user.edit&id={{id}}" target="_blank" class="uk-button uk-button-primary uk-button-small"><i
											class="uk-icon-edit uk-margin-small-right"></i><?php echo JText::_('COM_BIXMAILING_LIST_KLANT_EDIT'); ?></a>
							    </div>
							</div>
							<div class="uk-grid">
							    <div class="uk-width-medium-1-2">
							        <dl class="uk-description-list">
										<dt><?php echo JText::_('PLG_USER_VOLLNAAM_LABEL'); ?></dt>
										<dd>{{volledigeNaam}}</dd>
										<dt><?php echo JText::_('PLG_USER_EMAIL_LABEL'); ?></dt>
										<dd>{{email}}</dd>
										<dt><?php echo JText::_('PLG_USER_PROFILE_FIELD_PHONE_LABEL'); ?></dt>
										<dd>{{phone}}</dd>
										<dt><?php echo JText::_('PLG_USER_PROFILE_FIELD_MOBILE_LABEL'); ?></dt>
										<dd>{{mobile}}</dd>
										<dt><?php echo JText::_('PLG_USER_PROFILE_FIELD_WEB_SITE_LABEL'); ?></dt>
										<dd>{{website}}</dd>
									</dl>
							    </div>
							    <div class="uk-width-medium-1-2">
									<strong><?php echo JText::_('PLG_USER_BT_ADDRESS_LABEL'); ?></strong><br/>
									<div>
										{{!factuurAdres}}
									</div>
									<strong><?php echo JText::_('PLG_USER_VISITADDRESS_SLIDER_LABEL'); ?></strong><br/>
									<div>
										{{!bezoekAdres}}
									</div>
							    </div>
							</div>
							<hr/>
							<div class="uk-margin-top">
								<div class="uk-grid">
								    <div class="uk-width-medium-1-2">
										<small>
											<?php echo JText::_('PLG_USER_LASTVISITDATE_LABEL'); ?>: {{lastvisitDate}}.<br/>
											<?php echo JText::_('PLG_USER_ISINVITED_LABEL'); ?>: {{#isInvited}}<i
												class="uk-icon-check uk-text-success"></i>{{/isInvited}}{{^isInvited}}<i
												class="uk-icon-ban uk-text-danger"></i>{{/isInvited}}<br/>
											<?php echo JText::_('PLG_USER_REQUIRERESET_LABEL'); ?>: {{#requireReset}}<i
												class="uk-icon-check uk-text-success"></i>{{/requireReset}}{{^requireReset}}<i
												class="uk-icon-ban uk-text-danger"></i>{{/requireReset}}<br/>
										</small>
										<button type="button" data-bix-userid="{{id}}"
												class="bix-invite uk-button uk-button-small uk-margin-small-top"><i
												class="uk-icon-paper-plane-o uk-margin-small-right"></i>
											<?php echo JText::_('COM_BIXMAILING_LIST_KLANT_INVITE'); ?></button>
										<div class="bix-force-button">
										</div>
								    </div>
								    <div class="uk-width-medium-1-2">
										<small>
											<?php echo JText::_('PLG_USER_MAILTYPE_LABEL'); ?>: {{mailtype}}.<br/>
										</small>
										<div class="uk-alert uk-alert-success uk-text-small">
											{{accountStatus}}
										</div>
									</div>
								</div>
							</div>
						</script>
					</div>

				</div>
			</div>
		</div>
	<?php endif; ?>
</div>


