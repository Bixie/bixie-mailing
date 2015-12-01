/* *
 *  BixieMailing
 *  converteren.js
 *  Created on 18-3-14 22:53
 *  
 *  @author Matthijs
 *  @copyright Copyright (C)2014 Bixie.nl
 *
 */

jQuery(function ($) {
    "use strict";

    $('#import-switcher').on('show.uk.switcher', function (event, area) {
        var vervoerder = area.data('bix-content'), contentEl = $('#' + vervoerder + '-records');
        if (!contentEl.find('> div:not(.bix-placeholder)').length) {
            contentEl.trigger('loadTemplate');
        }
    });
});


(function ($, UI) {

    "use strict";

    //extend uploader
    if (UI.components.bixupload) {
        $.extend(UI.components.bixupload.prototype, {
            //handler convertfiles
            convertFiles: function (data, uploadedFiles) {
                //algemene progressmess aanpassen
                this.progressNotification.element.find('.bix-progress-text').text(this.options.lang.bezigConverteren);
                this.progressNotification.element.find('.bix-progress-stats').text(this.options.lang.tekstConverteren)
                    .prepend($(UI.bixTools.icon('cog')).addClass('uk-icon-spin'));
                this.progressNotification.element.find('.uk-progress').hide();
                //files klaarzetten
                var $this = this, filesFound = false, conversions = 0;
                $.each(uploadedFiles, function (index, file) {
                    var uploadInfoEl = $('[data-filehash="' + file.hash + '"]'), validFile = (['Gls', 'Postnl', 'Parcelware'].indexOf(file.type) !== -1),
                        req = {
                            hash: file.hash
                        };
                    filesFound = validFile;
                    if (validFile) {
                        uploadInfoEl.append($this.createConvertMessage(file.type));
                        req.task = 'bixmailing.import' + file.type;
                        conversions++;
                        $.post($this.options.ajaxUrl, req, '', "json")
                            .done(function (data) {
                                var fileType = file.type;
                                if (data.success) {
                                    data.result.file.hash = data.result.file.url.slice(-32);
                                    uploadInfoEl.find('.bix-convert-text').hide();
                                    uploadInfoEl.find('.bix-filelist').append($this.createFileInfo(data.result.file));
                                    if (file.type === 'Parcelware') {
                                        fileType = 'Postnl'
                                    }
                                    uploadInfoEl.find('.bix-filebuttons').append($this.createFileButtons(fileType));
                                    $this.buttonEvents(uploadInfoEl);
                                    $this.showDataTab(fileType);
                                    conversions--;
                                }
                                if (conversions === 0) {
                                    UI.notify.closeAll();
                                    $this.progressNotification = false; //force new one
                                }
                            })
                            .fail(function () {
                                UI.notify({message: $this.options.lang.foutConversieRequest, status: 'danger'});
                            })
                            .always(function (data) {
                                if (data.messages) {
                                    UI.bixTools.showNotifications(data.messages);
                                }
                            });
                    }
                });
                if (!filesFound) {
                    UI.notify.closeAll();
                    UI.notify({message: this.options.lang.noValidFiles, status: 'danger'});
                }
            },

            //helpers
            buttonEvents: function (uploadInfoEl) {
                var $this = this;
                uploadInfoEl.find('.bix-mailfiles').click(function () {
                    var files = uploadInfoEl.find('[data-bix-fileinfo]').map(function () {
                        return UI.Utils.options($(this).data('bix-fileinfo'));
                    }).get();
                    $("#mail-modal").data('bixmailfiles').sendFiles(files);
                });
                uploadInfoEl.find('.bix-filedetails').click(function () {
                    var $element = $(this);
                    if ($element.hasClass('uk-active')) {
                        return;
                    }
                    $this.element.find('button').removeClass('uk-active');
                    $element.addClass('uk-active');
                    $this.showDataTab($element.data('type'));
                });
            },
            showDataTab: function (vervoerder) {
                $('#' + vervoerder.toLowerCase() + '-records').empty(); //force refresh
                var tab = vervoerder === 'Postnl' ? 1 : 0;
                $('#import-switcher').data('switcher').show(tab);
            },
            createFileButtons: function (filetype) {
                var $this = this;
                return '<div class="bix-filebuttons">' +
                    '<button class="bix-mailfiles uk-button uk-width-1-1" type="button">' +
                    '<i class="uk-icon-paperclip uk-margin-small-right"></i>' + $this.options.lang.mailfiles + '</button>' +
                    '<button class="bix-filedetails uk-button uk-button-primary uk-margin-small-top uk-width-1-1" type="button" data-type="' + filetype + '">' +
                    '<i class="uk-icon-search uk-margin-small-right"></i>' + $this.options.lang.bekijkdata + '</button>' +
                    '</div>';
            },
            createConvertMessage: function (type) {
                var $this = this;
                return  '<div class="bix-convert-text uk-width-1-1 uk-margin-small-top uk-margin-small-bottom"><em>' + $this.options.lang.converting[type] + '</em></div>';
            }
        });
    }

}(jQuery, UIkit));

(function (addon) {
    "use strict";

    var component;

    if (jQuery && UIkit) {
        component = addon(jQuery, UIkit);
    }

    if (typeof define === "function" && define.amd) {
        define("uikit-bixuploadcontrols", ["uikit"], function () {
            return component || addon(jQuery, UIkit);
        });
    }

}(function ($, UI) {
    "use strict";

    UI.component('bixuploadcontrols', {

        defaults: {
            vervoerder: '',
            ajaxUrl: '/index.php?option=com_bixmailing&format=raw&task=bixmailing.sendMail'
        },

        boot: function () {
            UI.ready(function (context) {
                $("[data-bix-uploadcontrols]", context).each(function () {
                    var $ele = $(this);
                    if (!$ele.data("bixuploadcontrols")) {
                        UI.bixuploadcontrols($ele, UI.Utils.options($ele.attr('data-bix-uploadcontrols')));
                    }
                });
            });
        },

        init: function () {
            var $this = this;
            this.dataContainer = $('#' + this.options.vervoerder + '-records');


            this.dom = {
                button: this.find('button.bix-sendmails'),
                //weergaves
                nrOpenMailings: $('[data-bix-openmailings="' + this.options.vervoerder + '"]'),
                nrIncomplMailings: $('[data-bix-incompleet="' + this.options.vervoerder + '"]'),
                nrKlanten: this.find('[data-bix-nrklanten="' + this.options.vervoerder + '"]'),
                nrMails: this.find('[data-bix-nrmails="' + this.options.vervoerder + '"]'),
                nrMailingMails: this.find('[data-bix-nrmailingmail="' + this.options.vervoerder + '"]')
            };
            this.dom.button.click(function () {
                $this.sendMails();
            });

            if (this.dataContainer.find('> div:not(.bix-placeholder)').length) {
                this.process();
            }

        },
        //general processing
        process: function () {
            this.nrOpenMailings = this.dataContainer ? this.dataContainer.find('[data-bix-mailing-status="nieuw"]').length : 0;
            this.nrIncomplMailings = this.dataContainer ? this.dataContainer.find('[data-bix-mailing-status="incompleet"]').length : 0;
            this.nrKlanten = this.dataContainer ? this.dataContainer.find('[data-bix-klantcontrols]').length : 0;
            this.nrMails = this.dataContainer ? this.dataContainer.find('[name="klantmailer[]"]:checked').length : 0;
            this.nrMailingMails = this.dataContainer ? this.dataContainer.find('[name="mailing[]"]:checked').length : 0;
            this.updateDom();
        },
        updateDom: function () {
            //totalen
            this.dom.nrOpenMailings.text(this.nrOpenMailings);
            this.dom.nrIncomplMailings.text(this.nrIncomplMailings);
            this.dom.nrKlanten.text(this.nrKlanten);
            this.dom.nrMails.text(this.nrMails);
            this.dom.nrMailingMails.text(this.nrMailingMails);
        },
        sendMails: function () {
            var $this = this,
                recordsEl = $('#' + $this.options.vervoerder.toLowerCase() + '-records'),
                data = {
                    vervoerder: this.options.vervoerder,
                    maildata: $('[data-bix-klantcontrols*=' + this.options.vervoerder + ']').map(function () {
                        return $(this).data('bixklantcontrols').getData();
                    }).get()
                };
            //recount after refresh
            recordsEl.bind('templateLoaded', function () {
                console.log($this);
                $this.process();
            });
            this.dom.button.find('i').addClass('uk-icon-spin');
            $.post(this.options.ajaxUrl, data, '', "json")
                .done(function (data) {
                    console.log(data);
                    if (data.success) {
                        UI.notify({message: data.result.length + ' e-mails verzonden', status: 'success'});
                    }
                    recordsEl.trigger('loadTemplate');
                })
                .fail(function () {
                    UI.notify({message: 'Fout in request', status: 'danger'});
                })
                .always(function (data) {
                    $this.dom.button.find('i').removeClass('uk-icon-spin');
                    UI.bixTools.showNotifications(data.messages);
                });
        }
    });

    return UI.bixuploadcontrols;
}));
