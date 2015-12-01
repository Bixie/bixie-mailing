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
    <?php if ($this->params->get('show_page_heading')): ?>
        <div class="uk-grid">
            <div class="uk-width-1-1">
                <h1 class="uk-h3"><?php echo $this->page_heading; ?></h1>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!$this->user->authorise('core.admin')): ?>
        <div class="uk-alert uk-alert-warning">
            <i class="uk-icon-exclamation-triangle uk-margin-small-right"></i>
            <?php echo JText::_('COM_BIXMAILING_DASHBOARD_LOGIN_TOEGANG'); ?>
        </div>
    <?php else: ?>
        <div class="uk-grid">
            <div class="uk-width-medium-1-4">
                <?php echo BixHelper::renderTemplate('bixmailing', 'default', 'nav', array('params' => $this->params, 'user' => $this->user, 'userProfile' => $this->userProfile)) ?>
				<!--<div class="uk-panel">
					<div class="uk-alert">messages</div>

				</div>-->
			</div>
            <div class="uk-width-medium-3-4">
                <h3 class="uk-margin-small-bottom"><?php echo JText::_('COM_BIXMAILING_UPLOAD_FILE_TITLE'); ?></h3>

				<div id="bix-fileupload" data-bix-upload="{allowedExt:['txt','csv'],callback:'convertFiles'}" data-uk-observe>
				    <div class="bix-dropbox uk-text-center"><?php echo JText::_('COM_BIXMAILING_UPLOAD_DROPFILES'); ?></div>
                </div>
                <ul class="uk-grid uk-margin-top" id="import-switcher" data-uk-switcher="{connect:'#import-data'}">
                    <?php
                    $vervoerders = array('gls','postnl');
                    ?>
					<?php foreach ($vervoerders as $vervoerder) : ?>
						<li class="uk-width-1-2 bix-tab <?php echo $this->state->get('vervoerder', '') == $vervoerder ? 'uk-active' : ''; ?>"
                            data-bix-content="<?php echo $vervoerder; ?>">
                            <div>
								<h3 class="bix-file-icon-<?php echo $vervoerder; ?> uk-margin-remove">
									<?php echo JText::_('BIX_MAILING_VERVOERDER_' . strtoupper($vervoerder)); ?>
									<div class="uk-float-right">
                                        <div class="uk-badge uk-badge-success uk-badge-notification"
                                             data-bix-openmailings="<?php echo $vervoerder; ?>" data-uk-tooltip
                                             title="<?php echo JText::_('BIX_MAILING_AANTAL_MAILINGS_NIEUW'); ?>">
                                            <?php echo $this->openMailings[$vervoerder]->aantalNieuw; ?>
                                        </div>
                                        <div class="uk-badge uk-badge-danger uk-badge-notification"
											 data-bix-incompleet="<?php echo $vervoerder; ?>" data-uk-tooltip
											 title="<?php echo JText::_('BIX_MAILING_AANTAL_MAILINGS_ZONDER_KLANT'); ?>">
                                            <?php echo $this->openMailings[$vervoerder]->aantalIncompleet; ?>
                                        </div>
                                    </div>
                                </h3>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="uk-panel uk-panel-box">
                    <ul id="import-data" class="uk-switcher">
						<?php foreach ($vervoerders as $vervoerder) : ?>
							<li class="bix-uploaddata">
                                <div id="controls-<?php echo $vervoerder; ?>" class="uk-panel uk-panel-box"
                                     data-bix-uploadcontrols="{vervoerder:'<?php echo $vervoerder; ?>'}" data-uk-sticky="{top:55}">
                                    <div class="uk-grid">
                                        <div class="uk-width-1-3">
                                            <ul class="uk-list uk-clearfix">
                                                <li>
                                                    <div class="uk-float-right uk-badge uk-badge-notification"
                                                         data-bix-nrklanten="<?php echo $vervoerder; ?>">0</div>
                                                    <?php echo JText::_('BIX_MAILING_AANTAL_KLANTEN'); ?>
                                                </li>
                                                <li>
                                                    <div class="uk-float-right uk-badge uk-badge-notification"
                                                         data-bix-nrmails="<?php echo $vervoerder; ?>">0</div>
                                                    <?php echo JText::_('BIX_MAILING_AANTAL_MAILS'); ?>
                                                </li>

                                            </ul>
                                        </div>
                                        <div class="uk-width-1-3">
                                            <ul class="uk-list uk-clearfix">
                                                <li>
                                                    <div class="uk-float-right uk-badge uk-badge-notification"
                                                         data-bix-nrmailingmail="<?php echo $vervoerder; ?>">0</div>
                                                    <?php echo JText::_('BIX_MAILING_AANTAL_MAILINGS_IN_MAIL'); ?>
                                                </li>
                                                <li>
                                                    <div class="uk-float-right uk-badge uk-badge-danger uk-badge-notification"
                                                         data-bix-incompleet="<?php echo $vervoerder; ?>">
                                                        <?php echo $this->openMailings[$vervoerder]->aantalIncompleet; ?>
                                                    </div>
                                                    <?php echo JText::_('BIX_MAILING_AANTAL_MAILINGS_ZONDER_KLANT'); ?>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="uk-width-1-3">
                                            <button
                                                class="bix-sendmails uk-button uk-button-large uk-button-primary uk-width-1-1">
                                                <i class="uk-icon-envelope-o uk-margin-small-right"></i>
                                                <?php echo JText::_('BIX_MAILING_VERWERK_MAILS'); ?>
                                            </button>
                                        </div>
                                    </div>
                                </div>
								<div class="uk-margin-top" id="<?php echo $vervoerder; ?>-records" data-uk-observe
									 data-bix-loadtemplate="{view:'upload',layout:'default',tpl:'<?php echo $vervoerder; ?>',vervoerder:'<?php echo $vervoerder; ?>'}">
								<?php echo $this->loadTemplate($vervoerder); ?>
								</div>
							</li>
                        <?php endforeach; ?>
                    </ul>
                </div>


            </div>
        </div>

        <!-- This is the modal -->
		<div id="mail-modal" class="uk-modal" data-bix-mailfiles>
			<div class="uk-modal-dialog">
                <a href="" class="uk-modal-close uk-close uk-close-alt"></a>
                <fieldset class="uk-form uk-form-horizontal">
                    <legend><?php echo JText::_('COM_BIXMAILING_UPLOAD_SENDMAIL'); ?></legend>
                    <div class="uk-form-row">
						<label for="email" class="uk-form-label" data-uk-tooltip
							   title="<?php echo JText::_('COM_BIXMAILING_UPLOAD_MAIL_RECEIVERS_DESC'); ?>"><?php echo JText::_('COM_BIXMAILING_UPLOAD_MAIL_RECEIVERS'); ?></label>

                        <div class="uk-form-controls">
							<input type="text" id="email" name="email" class="uk-width-1-1"
								   value="<?php echo $this->defaultMailEmail; ?>"/>
						</div>
                    </div>
                    <div class="uk-form-row">
						<label for="onderwerp"
							   class="uk-form-label"><?php echo JText::_('COM_BIXMAILING_UPLOAD_MAIL_SUBJECT'); ?></label>

                        <div class="uk-form-controls">
							<input type="text" id="onderwerp" name="onderwerp"
								   value="<?php echo $this->defaultMailOnderwerp; ?>" class="uk-width-1-1"/>
						</div>
                    </div>
                    <div class="uk-form-row">
						<label for="tekst"
							   class="uk-form-label"><?php echo JText::_('COM_BIXMAILING_UPLOAD_MAIL_BODY'); ?></label>

						<div class="uk-form-controls">
							<textarea name="tekst" id="tekst" class="uk-width-1-1"
									  rows="4"><?php echo $this->defaultMailTekst; ?></textarea>
						</div>
                    </div>
                    <div class="uk-grid uk-margin-top">
                        <div class="uk-width-3-4">
							<ul class="bix-files uk-list">

							</ul>
						</div>
                        <div class="uk-width-1-4">
							<button class="bix-submit uk-button uk-button-primary uk-width-1-1">
							<i class="uk-icon-envelope-o uk-margin-small-right"></i><?php echo JText::_('COM_BIXMAILING_UPLOAD_BUTTON_SENDMAIL'); ?>
                            </button>

                        </div>
                    </div>
                </fieldset>
            </div>
        </div>
        <!-- This is the modal for userselect-->
        <div id="selectuser-modal" class="uk-modal">
            <div class="uk-modal-dialog" style="width:800px">
                <a href="" class="uk-modal-close uk-close uk-close-alt"></a>
                <fieldset class="uk-form uk-form-stacked" id="bix-selectuser-holder" data-bix-selectuser="{}">
                    <legend><?php echo JText::sprintf('BIX_MAILING_SELECT_USER_TITLE_SPR', '<em data-bix-referentie></em>'); ?></legend>
                    <div class="uk-grid">
                        <div class="uk-width-1-2">
                            <strong><?php echo JText::sprintf('BIX_MAILING_RELATED_MAILINGS_SPR', '<em data-bix-referentie></em>'); ?></strong><br/>

                            <div class="uk-scrollable-box uk-text-truncate" style="max-height: 350px">
                                <ul class="bix-related uk-nav uk-nav-side"></ul>
                            </div>
                        </div>
                        <div class="uk-width-1-2">
                            <div class="uk-form-row">
                                <strong><?php echo JText::_('BIX_MAILING_ZOEK_KLANT'); ?></strong><br/>
                                <input type="text" name="search" value="" class="uk-width-1-1 uk-form-large"
                                       placeholder="<?php echo JText::_('BIX_ZOEK_OP'); ?>"/>

                                <div class="uk-scrollable-box uk-text-truncate" style="max-height: 305px">
                                    <ul class="bix-klanten uk-nav uk-nav-side"></ul>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="uk-grid uk-margin-top">
                        <div class="uk-width-2-3">
                            <div class="bix-fileholder"><br/></div>
                        </div>
                        <div class="uk-width-1-3">
                            <button class="bix-attachuser uk-button uk-button-primary uk-width-1-1">
                                <i class="uk-icon-chain uk-margin-small-right"></i><?php echo JText::_('COM_BIXMAILING_LINK_USERS'); ?>
                            </button>

                        </div>
                    </div>
                </fieldset>
            </div>
        </div>

    <?php endif; ?>
</div>


