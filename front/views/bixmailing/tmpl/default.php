<?php
/**
 *    com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *    Bixie.nl

 */

// no direct access
defined('_JEXEC') or die;
$testfilters = false;
?>
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
				<?php echo $this->loadTemplate('nav'); ?>
			</div>
			<div class="uk-width-medium-3-4">
				<ul class="uk-tab" data-uk-tab="{connect:'#dash-tabs'}">
					<li><a href="">
							<h4 class="uk-panel-title uk-margin-remove">
								<i class="uk-icon-truck uk-margin-right uk-text-primary"></i><?php echo JText::_('COM_BIXMAILING_ACTIEVE_MAILINGS'); ?>
							</h4>
						</a></li>
					<li><a href="">
						<h4 class="uk-panel-title uk-margin-remove">
								<i class="uk-icon-users uk-margin-right uk-text-primary"></i><?php echo JText::_('COM_BIXMAILING_ACTIEVE_MASSAMAILINGS'); ?>
							</h4>
						</a></li>
				</ul>

				<ul id="dash-tabs" class="uk-switcher uk-margin">
					<li>
						<div class="uk-panel">
							<?php if (count($this->mailings)) : ?>
								<div id="filter-mailings">
									<div class="uk-grid uk-form">
										<div class="uk-width-1-3">
											<?php if ($this->user->authorise('core.admin') && $testfilters) : ?>
												<div class="uk-button-group">
													<button class="uk-button">
														<i class="uk-icon-truck uk-margin-small-right"></i>
														<?php echo JText::_('BIX_MAILING_KIES_VERVOERDER'); ?>
													</button>
													<div data-uk-dropdown="{mode:'click'}">
														<button class="uk-button">
															<i class="uk-icon-caret-down"></i>
														</button>
														<div class="uk-dropdown uk-dropdown-small">
															<ul class="uk-nav uk-nav-dropdown">
																<li><a href="" data-filter="GLS"><i
																			class="bix-icon-gls"></i>GLS</a></li>
																<li><a href="" data-filter="Postnl"><i
																			class="bix-icon-postnl"></i>Post NL</a></li>
															</ul>
														</div>
													</div>
												</div>
												<input type="hidden" name="vervoerder"
													   value="<?php echo $this->model->mailingsState->get('filter.vervoerder'); ?>"/>
											<?php else: ?>
												<br/>
											<?php endif; ?>
										</div>
										<div class="uk-width-1-3">
											<?php if ($this->user->authorise('core.admin') && $testfilters) : ?>
												sel user
											<?php else: ?>
												<br/>
											<?php endif; ?>
										</div>
										<div class="uk-width-1-3 uk-text-right">
											<i class="uk-icon-search uk-margin-small-right"></i>
											<input type="text" name="search"
												   value="<?php echo $this->model->mailingsState->get('filter.search'); ?>"
												   placeholder="<?php echo JText::_('BIX_MAILING_SEARCH_PLCH'); ?>"/>
										</div>
									</div>
								</div>
								<ul class="uk-list uk-list-space uk-scrollable-text" style="height: 800px"
									data-bix-loadnext="{view:'bixmailing',part:'verzendingen','filter':'#filter-mailings',totalCount:<?php echo $this->model->mailingsState->get('list.total'); ?>}">
								<?php echo $this->loadTemplate('verzendingen'); ?>
								</ul>
							<?php else: ?>
								<div class="uk-alert">
									<i class="uk-icon-info uk-margin-small-right"></i><?php echo JText::_('COM_BIXMAILING_GEEN_ACTIEVE_MAILINGS'); ?>
								</div>
							<?php endif; ?>
						</div>
					</li>
					<li>
						<div class="uk-panel">
							<?php if (count($this->massas)) : ?>
								<div id="filter-massas">
									<div class="uk-grid uk-form">
										<div class="uk-width-1-1 uk-text-right">
											<i class="uk-icon-search uk-margin-small-right"></i>
											<input type="text" name="search" value=""
												   placeholder="<?php echo JText::_('BIX_MAILING_SEARCH_PLCH'); ?>"/>
										</div>
									</div>
								</div>
								<ul class="uk-list uk-list-space uk-scrollable-text" style="height: 800px"
									data-bix-loadnext="{view:'bixmailing',part:'massas','filter':'#filter-massas',totalCount:<?php echo $this->model->massasState->get('list.total'); ?>}">
								<?php echo $this->loadTemplate('massas'); ?>
								</ul>
							<?php else: ?>
								<div class="uk-alert">
									<i class="uk-icon-info uk-margin-small-right"></i><?php echo JText::_('COM_BIXMAILING_GEEN_ACTIEVE_MASSAMAILINGS'); ?>
								</div>
							<?php endif; ?>
						</div>
					</li>
				</ul>
			</div>
		</div>
	<?php endif; ?>
</div>