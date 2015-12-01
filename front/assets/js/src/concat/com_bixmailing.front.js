/**
 *  com_bixmailing - Mailings for Joomla
 *  Copyright (C) 2014 Matthijs Alles
 *  Bixie.nl
 *
 */

(function (addon) {
    "use strict";

    var component;

    if (jQuery && UIkit) {
        component = addon(jQuery, UIkit);
    }

    if (typeof define === "function" && define.amd) {
        define("uikit-bixloadtemplate", ["uikit"], function () {
            return component || addon(jQuery, UIkit);
        });
    }

}(function ($, UI) {
    "use strict";

    UI.component('bixloadtemplate', {

        defaults: {
            view: '',
            layout: '',
            tpl: '',
            highlight: false,
            ajaxUrl: '/index.php?option=com_bixmailing&format=raw'
        },

        boot: function () {
            UI.ready(function (context) {
                $("[data-bix-loadtemplate]", context).each(function () {
                    var $ele = $(this);
                    if (!$ele.data("bixloadtemplate")) {
                        UI.bixloadtemplate($ele, UI.Utils.options($ele.attr('data-bix-loadtemplate')));
                    }
                });
            });
        },

        init: function () {
            var $this = this;
            this.on('loadTemplate', function () {
                $this.element.load($this.options.ajaxUrl, $this.options, function () {
                    if ($this.options.highlight) {
                        $this.find('li:first-child').addClass('uk-animation-scale-down');
                    }
                    $this.element.trigger('templateLoaded');
                });
            });

        }
    });

    return UI.bixloadtemplate;
}));
;/* *
 *  BixieMailing
 *  bestanden.js
 *  Created on 24-3-14 13:57
 *  
 *  @author Matthijs
 *  @copyright Copyright (C)2014 Bixie.nl
 *
 */

(function ($, UI) {
    "use strict";

    var bekijkUrl = '/index.php?option=com_bixmailing&format=raw&task=bixmailing.fileView';

    $.extend(UI.BixTools.prototype, {
        download: function (path, hash) {
            if (['csv', 'txt'].indexOf(path.slice(-3)) !== -1) {
                var modal = UI.modal("#file-download-modal", {bgclose: true});
                modal.show();
                modal.find('.bix-download').attr('href', '/download?h=' + hash).click(function () {
                    modal.hide();
                });
                modal.find('.bix-bekijk').click(function () {
                    modal.hide();
                    $('#file-contents').load(bekijkUrl + '&h=' + hash);
                });
                modal.find('.bix-mailings').click(function () {
                    modal.hide();
                    var patharr = path.split('/'), filename = patharr[patharr.length - 1];
                    window.location.href = $(this).data('base') + '?search=file:' + filename;
                });
            } else {
                window.location.href = '/download?h=' + hash;
            }
        }
    });

}(jQuery, UIkit));;/* *
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
                    var uploadInfoEl = $('[data-filehash="' + file.hash + '"]'), validFile = (['Gls', 'Postnl'].indexOf(file.type) !== -1),
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
                                if (data.success) {
                                    data.result.file.hash = data.result.file.url.slice(-32);
                                    uploadInfoEl.find('.bix-convert-text').hide();
                                    uploadInfoEl.find('.bix-filelist').append($this.createFileInfo(data.result.file));
                                    uploadInfoEl.find('.bix-filebuttons').append($this.createFileButtons(file.type));
                                    $this.buttonEvents(uploadInfoEl);
                                    $this.showDataTab(file.type);
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
;/* *
 *  BixieMailing
 *  cpanel.js
 *  Created on 9-3-14 1:14
 *
 *  @author Matthijs
 *  @copyright Copyright (C)2014 Bixie.nl
 *
 */

(function (addon) {
    "use strict";

    var component;

    if (jQuery && UIkit) {
        component = addon(jQuery, UIkit);
    }

    if (typeof define === "function" && define.amd) {
        define("uikit-bixloadnext", ["uikit"], function () {
            return component || addon(jQuery, UIkit);
        });
    }

}(function ($, UI) {
    "use strict";

    UI.component('bixloadnext', {

        defaults: {
            ajaxUrl: '/index.php?option=com_bixmailing&format=raw',
            view: 'bixmailing',
            part: '',
            filter: '',
            totalCount: 0,
            extraStart: 5,
            limit: 5,
            lang: {
                meerLaden: 'Meer items inladen...',
                lastReached: 'Laatste item bereikt'
            }
        },

        boot: function () {
            UI.ready(function (context) {
                $("[data-bix-loadnext]", context).each(function () {
                    var $ele = $(this);
                    if (!$ele.data("bixloadnext")) {
                        UI.bixloadnext($ele, UI.Utils.options($ele.attr('data-bix-loadnext')));
                    }
                });
            });
        },

        init: function () {
            var $this = this;
            this.limitstart = this.options.extraStart;

            if (this.options.filter) {
                this.filter = $(this.options.filter);
                this.filter.find('input').keyup(function () {
                    $this.loadNew(true);
                });
            }

            this.setButton();

            this.on('click', '.bix-loadnext', function () {
                $this.loadNew(false);
            });

        },
        loadNew: function (reset) {
            var $this = this, req = {
                view: this.options.view,
                part: this.options.part,
                limit: this.options.limit,
                limitstart: 0
            };
            //set new limitstart
            this.limitstart = reset ? 0 : this.limitstart + this.options.limit;
            req.limitstart = this.limitstart;
            //filters
            if (this.filter) {
                this.filter.find('input').each(function () {
                    var $input = $(this);
                    req[$input.attr('name')] = $input.val();
                });
            }
            //go
            this.find('i.uk-icon-refresh').addClass('uk-icon-spin');
            $.post(this.options.ajaxUrl, req)
                .done(function (data) {
                    if (reset) {
                        $this.element.empty();
                    } else {
                        $this.find('.bix-loadnext').remove();
                    }
                    $this.element.append(data);
                    $this.setButton();
                })
                .fail(function () {
                    UI.notify({message: 'Fout in request', status: 'danger'});
                });
        },
        setButton: function () {
            if (this.options.totalCount - (this.limitstart + this.options.limit) > 0) {
                this.element.append(this.loadMoreTmpl());
            } else if (this.limitstart > this.options.extraStart) {
                this.element.append(this.lastReachedTmpl());
            }
        },
        loadMoreTmpl: function () {
            return '<li><a class="uk-button uk-button-small uk-text-center uk-width-1-1 bix-loadnext">' +
                UI.bixTools.icon('refresh') + this.options.lang.meerLaden +
                '</a></li>';
        },
        lastReachedTmpl: function () {
            return '<li><div class="uk-alert uk-text-small uk-text-center">' +
                UI.bixTools.icon('info') + this.options.lang.lastReached +
                '</div></li>';
        }

    });

    return UI.bixloadnext;
}));
;/* *
 *  DeVosDiensten
 *  klantlist.js
 *  Created on 30-1-2015 21:12
 *  
 *  @author Matthijs
 *  @copyright Copyright (C)2015 Bixie.nl
 *
 */

(function (addon) {
    "use strict";

    var component;

    if (jQuery && UIkit) {
        component = addon(jQuery, UIkit);
    }

    if (typeof define === "function" && define.amd) {
        define("uikit-bixklantlist", ["uikit"], function () {
            return component || addon(jQuery, UIkit);
        });
    }

}(function ($, UI) {
    "use strict";

    UI.component('bixklantlist', {

        defaults: {
            ajaxUrl: '/index.php?option=com_bixmailing&format=raw',
            detailHeight: 434,
            detailTemplate: 'Maak template aan'
        },

        boot: function () {
            UI.ready(function (context) {
                $("[data-bix-klantlist]", context).each(function () {
                    var $ele = $(this);
                    if (!$ele.data("bixklantlist")) {
                        UI.bixklantlist($ele, UI.Utils.options($ele.attr('data-bix-klantlist')));
                    }
                });
            });
        },

        init: function () {
            var $this = this;
            this.zoekRequest = false;
            this.detailRequestId = false;
            this.inviteRequest = false;
            this.dom = {
                searchbox: this.find('input[name=search]'),
                klantenList: this.find('ul.bix-klanten'),
                klantDetails: this.find('.bix-klantdetails')
            };

            this.dom.searchbox.prop('autocomplete', 'off')
                .keyup(function (e) {
                    if ([38, 40].indexOf(e.keyCode) !== -1) { //up/down
                        UI.bixTools.navigateUl($this.dom.klantenList, e, false);
                        return;
                    }
                    if (e.keyCode === 13) { //return
                        $this.klantDetails();
                        return;
                    }
                    $this.zoekKlanten($(this).val());
                });

            this.klantdetailTemplate = $('script[type="text/klantdetailTemplate"]').text();
            this.klantdetailTemplate = UI.Utils.template(this.klantdetailTemplate || this.options.klantdetailTemplate);

            this.dom.klantenList.on('click', 'li', function () {
                $this.dom.klantenList.find('li').removeClass('uk-active');
                $(this).addClass('uk-active');
                $this.klantDetails();
            });
            this.dom.klantDetails.on('click', 'button.bix-invite', function () {
                $this.inviteKlant($(this));
            });
            this.zoekKlanten('');
        },
        inviteKlant: function ($button, force) {
            var $this = this, data = {};
            data.user_id = $button.data('bix-userid');
            data.force = force || 0;
            data.task = 'klant.inviteUser';
            if (this.inviteRequest !== false) {
                return;
            }
            $button.find('i').addClass('uk-icon-spin');
            this.inviteRequest = $.getJSON(this.options.ajaxUrl, data)
                .done(function (response) {
                    if (response.result.requireForce) {
                        $this.find('.bix-force-button').html(
                            $('<button type="button" data-bix-userid="' + data.user_id
                                + '" class="uk-button uk-button-primary uk-button-small uk-margin-small-top">'
                                + UI.bixTools.icon('refresh') +  response.result.forceText + '</button>')
                                .click(function () {
                                    $this.inviteKlant($button, 1);
                                })
                        );

                        console.log(response);
                    }
                    if (response.success) {
                        $this.klantDetails();
                    }
                })
                .always(function (response) {
                    $button.find('i').removeClass('uk-icon-spin');
                    $this.inviteRequest = false;
                    UI.bixTools.showNotifications(response.messages);
                });
        },
        zoekKlanten: function (string) {
            var $this = this, data = {};
            data.search = string;
            data.task = 'bixmailing.zoekKlanten';
            this.dom.klantenList.empty().append('<div class="uk-text-large uk-flex uk-flex-middle uk-flex-center" style="height:150px">'
                + UI.bixTools.icon('spinner uk-icon-spin' , true)
                + '</div>'
            );
            if (this.zoekRequest !== false) {
                this.zoekRequest.abort();
            }
            this.zoekRequest = $.getJSON(this.options.ajaxUrl, data)
                .done(function (response) {
                    if (response.success) {
                        if (response.result.klanten.length) {
                            $this.dom.klantenList.empty().append(response.result.klanten.map(function (obj) {
                                return '<li data-bix-userid="' + obj.user_id + '" data-bix-klantnummer="' + obj.klantnummer + '">' +
                                    '<a href="javascript:void(0)"><strong>' + obj.bedrijfsnaam + '</strong><br/>' +
                                    obj.klantnummer + ' - ' + obj.adres + '</a></li>';
                            }));
                            $this.klantDetails();
                        } else {
                            $this.dom.klantenList.empty().append('<li class="uk-alert">Geen resultaten</li>');
                        }
                    }
                })
                .always(function (response) {
                    $this.zoekRequest = false;
                    UI.bixTools.showNotifications(response.messages);
                });
        },
        klantDetails: function () {
            var $this = this, data = {
                task: 'bixmailing.klantDetails'
            };
            if (this.dom.klantenList.find("li.uk-active").length === 0) {
                this.dom.klantenList.find("li:first").addClass('uk-active');
            }
            data.userID = this.dom.klantenList.find("li.uk-active").data('bix-userid');
            if (this.detailRequestId !== false && data.userID === this.detailRequestId) {
                return;
            }
            this.detailRequestId = data.userID;
            this.dom.klantDetails.empty().append('<div class="uk-text-large uk-flex uk-flex-middle uk-flex-center" style="height:' + this.options.detailHeight + 'px">'
                + UI.bixTools.icon('spinner uk-icon-spin' , true)
                + '</div>'
            );
            $.getJSON(this.options.ajaxUrl, data)
                .done(function (response) {
                    if (response.success) {
                        $this.dom.klantDetails.html($this.klantdetailTemplate(response.result.profile));
                    } else {
                        $this.dom.klantDetails.empty().append('<li class="uk-alert">Geen resultaten</li>');
                    }
                })
                .always(function (response) {
                    $this.dom.klantDetails.find('div.uk-text-large.uk-flex').remove();
                    $this.detailRequestId = false;
                    UI.bixTools.showNotifications(response.messages);
                });
        }
    });

    return UI.bixklantlist;
}));
;/* *
 *  BixieMailing
 *  mailfiles.js
 *  Created on 22-3-14 19:01
 *  
 *  @author Matthijs
 *  @copyright Copyright (C)2014 Bixie.nl
 *
 */


(function (addon) {
    "use strict";

    var component;

    if (jQuery && UIkit) {
        component = addon(jQuery, UIkit);
    }

    if (typeof define === "function" && define.amd) {
        define("uikit-bixmailfiles", ["uikit"], function () {
            return component || addon(jQuery, UIkit);
        });
    }

}(function ($, UI) {
    "use strict";

    UI.component('bixmailfiles', {

        defaults: {
            ajaxUrl: '/index.php?option=com_bixmailing&format=raw&task=bixmailing.mailFiles'
        },

        boot: function () {
            UI.ready(function (context) {
                $("[data-bix-mailfiles]", context).each(function () {
                    var $ele = $(this);
                    if (!$ele.data("bixmailfiles")) {
                        UI.bixmailfiles($ele, UI.Utils.options($ele.attr('data-bix-mailfiles')));
                    }
                });
            });
        },

        init: function () {
            var $this = this;
            this.dom = {
                email: this.find('[name=email]'),
                onderwerp: this.find('[name=onderwerp]'),
                tekst: this.find('[name=tekst]'),
                submit: this.find('.bix-submit'),
                fileList: this.find('ul.bix-files')
            };

            this.UImodal = UI.modal(this.element);

            this.dom.submit.click(function () {
                $this.submitform();
            });

            this.files = {};

        },
        sendFiles: function (files) {
            var $this = this;
            this.dom.fileList.empty();
            $.each(files, function (key, file) {
                $this.files[file.hash] = file;
                $this.dom.fileList.append(
                    '<li>' + UI.bixTools.icon('paperclip') + file.name + '<br/>' +
                    '<small>' + UI.bixTools.formatFileSize(file.size) + '</small></li>');
            });
            if (this.UImodal.isActive()) {
                this.UImodal.hide();
            }
            this.UImodal.show();
        },

        submitform: function () {
            var $this = this, data = {
                maildata: {
                    email: this.dom.email.val(),
                    onderwerp: this.dom.onderwerp.val(),
                    tekst: this.dom.tekst.val()
                },
                files: []
            };
            $.each(this.files, function (hash, file) {
                data.files.push(file.hash);
            });
            $this.dom.submit.find('i').addClass('uk-icon-spin');
            $.post(this.options.ajaxUrl, data, '', "json")
                .done(function (data) {
                    console.log($this.UImodal);
                    if (data.success) {
                        $this.UImodal.hide();
                    }
                })
                .fail(function () {
                    UI.notify({message: 'Fout in request', status: 'danger'});
                })
                .always(function (data) {
                    $this.dom.submit.find('i').removeClass('uk-icon-spin');
                    UI.bixTools.showNotifications(data.messages);
                });

        }

    });

    return UI.bixmailfiles;
}));
;/* *
 *	BixieMailing
 *  selectuser.js
 *	Created on 20-3-14 17:17
 *  
 *  @author Matthijs
 *  @copyright Copyright (C)2014 Bixie.nl
 *
 */

(function (addon) {
    "use strict";

    var component;

    if (jQuery && UIkit) {
        component = addon(jQuery, UIkit);
    }

    if (typeof define === "function" && define.amd) {
        define("uikit-bixmailingincompleet", ["uikit"], function () {
            return component || addon(jQuery, UIkit);
        });
    }

}(function ($, UI) {
    "use strict";

    UI.component('bixmailingincompleet', {

        defaults: {
            vervoerder: ''
        },

        boot: function () {
            UI.ready(function (context) {
                $("[data-bix-mailing-incompleet]", context).each(function () {
                    var $ele = $(this);
                    if (!$ele.data("bixmailingincompleet")) {
                        UI.bixmailingincompleet($ele, UI.Utils.options($ele.attr('data-bix-mailing-incompleet')));
                    }
                });
            });
        },

        init: function () {
            var $this = this;
            this.klantrecords = $('[data-bix-klantrecords=0-' + this.options.vervoerder + ']');
            //koppelen selector
            var userSelector = $("#bix-selectuser-holder");
            this.selUser = UI.bixselectuser(userSelector, UI.Utils.options(userSelector.attr("data-bix-selectuser")));

            this.klantrecords.find("[data-bix-selectuser-button]").click(function () {
                $this.selUser.showForm(UI.Utils.options($(this).attr("data-bix-selectuser-button")));
            });

        }
    });

    return UI.bixmailingincompleet;
}));
;/* *
 *  BixieMailing
 *  massamail.js
 *  Created on 14-3-14 15:31
 *  
 *  @author Matthijs
 *  @copyright Copyright (C)2014 Bixie.nl
 *
 */

(function ($, UI) {

    "use strict";

    /**
     * Extensions for massamail form
     * @type {{geenBestanden: string}}
     */
    var lang = {
            geenBestanden: 'Voeg eerst bestanden toe door ze in het venster te slepen'
        },
        formID = 'bix-massamailform',
        confirmUrl = '/index.php?option=com_bixmailing&view=massamail&layout=confirm&format=raw';

    if (UI.components.bixupload) {
        $.extend(UI.components.bixupload.prototype, {
            massamail: function (data, uploadedFiles) {
                this.progressNotification.close();
                this.progressNotification = false; //force new one
                var $this = this, $formFileList = $('#bix-formfiles').empty();
                $.each(uploadedFiles, function (index, file) {
                    $formFileList.append($this.createFileFormList(file));
                });
                UI.notify({message: UI.bixTools.icon('check') + this.options.lang.uploadDone, status: 'success', timeout: 4000});
            },
            createFileFormList: function (file) {
                return '<li class="bix-fileinfo" data-filehash="' + file.hash + '"><span class="uk-display-block uk-text-truncate uk-width-1-1" ' +
                    'title="' + file.name + '"><i class="uk-icon-paperclip uk-margin-small-right"></i>' + file.name + '</span>' +
                    '</li>';
            }

        });
    }
    //console.log($form);

    $(document).on("ready", function () {
        $("[data-bix-ajax-submit]").each(function () {
            var $form = $(this), bixAjaxSubmit = UI.bixajaxsubmit($form, UI.Utils.options($form.attr('data-bix-ajax-submit')));
            if (!$form.attr('data-checkset') && bixAjaxSubmit && $form.attr('id') === formID) {
                bixAjaxSubmit.validateForm = function () {
                    var $this = this, valid = true, $filelist = $('#bix-formfiles');
                    if (!$filelist.find('li').length) {
                        UI.notify({message: lang.geenBestanden, status: 'warning'});
                        valid = false;
                    } else {
                        $this.find("[name='jform[bestanden][]']").remove();
                        $filelist.find('li').each(function () {
                            var fileHash = $(this).data('filehash');
                            $this.element.append('<input type="hidden" name="jform[bestanden][]" value="' + fileHash + '" />');
                        });
                    }
                    return valid;
                };
                bixAjaxSubmit.onSuccess = function (result) {
                    $form.load(confirmUrl + '&id=' + result.id);
                };
                $form.attr('data-checkset', true);
            }
        });
});


}(jQuery, UIkit));

;/* *
 *	BixieMailing
 *  selectuser.js
 *	Created on 20-3-14 17:17
 *  
 *  @author Matthijs
 *  @copyright Copyright (C)2014 Bixie.nl
 *
 */

(function (addon) {
    "use strict";

    var component;

    if (jQuery && UIkit) {
        component = addon(jQuery, UIkit);
    }

    if (typeof define === "function" && define.amd) {
        define("uikit-bixselectuser", ["uikit"], function () {
            return component || addon(jQuery, UIkit);
        });
    }

}(function ($, UI) {
    "use strict";

    UI.component('bixselectuser', {

        defaults: {
            ajaxUrl: '/index.php?option=com_bixmailing&format=raw'
        },

        boot: function () {
            UI.ready(function (context) {
                $("[data-bix-selectuser-list]", context).each(function () {
                    var $ele = $(this);
                    if (!$ele.data("bixselectuser")) {
                        UI.bixselectuser($ele, UI.Utils.options($ele.attr('data-bix-selectuser-list')));
                    }
                });
            });
        },

        init: function () {
            var $this = this;
            this.state = {
                mailingID: 0,
                vervoerder: '',
                referentie: '',
                data: {}
            };

            this.activeRequest = false;

            this.dom = {
                modal: $('#selectuser-modal'),
                searchbox: this.find('input[name=search]'),
                button: this.find('button.bix-attachuser'),
                relatedList: this.find('ul.bix-related'),
                klantenList: this.find('ul.bix-klanten'),
                referentie: this.find('[data-bix-referentie]')
            };

            this.selUserModal = UI.modal(this.dom.modal);

            //events
            this.dom.relatedList.on('click', 'li', function () {
                $(this).toggleClass('uk-active');
                $this.checkData();
            });
            this.dom.searchbox.prop('autocomplete', 'off')
                .keyup(function (e) {
                    if ([38, 40].indexOf(e.keyCode) !== -1) { //up/down
                        UI.bixTools.navigateUl($this.dom.klantenList, e, false);
                        return;
                    }
                    if (e.keyCode === 13) { //return
                        $this.checkData();
                        if (!$this.dom.button.attr('disabled')) {
                            $this.attachUser();
                        }
                        return;
                    }
                    $this.zoekKlanten($(this).val());
                });
            $(document).bind('keyup', 'return', function (e) {
                if ($this.selUserModal.isActive()) {
                    e.preventDefault();
                    $this.checkData();
                    if (!$this.dom.button.attr('disabled')) {
                        $this.attachUser();
                    }
                }
            });
            this.dom.klantenList.on('click', 'li', function () {
                $this.dom.klantenList.find('li').removeClass('uk-active');
                $(this).addClass('uk-active');
                $this.checkData();
            });
            this.dom.button.click(function () {
                $this.attachUser();
            });

            this.zoekKlanten('');

        },
        //called from incompleet buttons
        showForm: function (options) {
            var oldState = this.state.referentie;
            this.state.mailingID = options.mailingID;
            this.state.vervoerder = options.vervoerder;
            this.state.referentie = options.referentie;
            if (this.state.referentie !== oldState) {
                this.relatedMailings(this.state);
            }
            this.dom.referentie.text(this.state.referentie);
            this.dom.searchbox.val('');
            this.zoekKlanten('');
            if (this.selUserModal.isActive()) {
                this.selUserModal.hide();
            }
            this.selUserModal.show();
            this.dom.searchbox.focus();
        },

        checkData: function () {
            if (this.dom.klantenList.find("li.uk-active").length === 0) {
                this.dom.klantenList.find("li:first").addClass('uk-active');
            }
            this.state.data = {
                referentie: this.state.referentie,
                vervoerder: this.state.vervoerder,
                mailingID: this.state.mailingID,
                mailingIDs: this.dom.relatedList.find("li.uk-active").map(function () {
                    return $(this).data('bix-mailingid');
                }).get(),
                klantnummer: this.dom.klantenList.find("li.uk-active").map(function () {
                    return $(this).data('bix-klantnummer');
                }).get().pop(),
                user_id: this.dom.klantenList.find("li.uk-active").map(function () {
                    return $(this).data('bix-userid');
                }).get().pop()
            };
            this.dom.button.prop('disabled', (!this.state.data.user_id || !this.state.data.mailingIDs.length));
            return this.state.data;
        },

        relatedMailings: function (data) {
            var $this = this;
            data.task = 'bixmailing.relatedMailings';
            this.dom.relatedList.empty();
            $.getJSON(this.options.ajaxUrl, data)
                .done(function (data) {
                    if (data.success && data.result.related.length) {
                        $this.dom.relatedList.append(data.result.related.map(function (obj) {
                            return '<li class="uk-active" data-bix-mailingid="' + obj.id + '">' +
                                '<a href="javascript:void(0)">' + obj.naam + '<br/>' +
                                obj.type + '</a></li>';
                        }));
                    }
                })
                .fail(function () {
                    UI.notify({message: 'Fout in request mailings', status: 'danger'});
                })
                .always(function (data) {
                    UI.bixTools.showNotifications(data.messages);
                });
        },

        zoekKlanten: function (string) {
            var $this = this, data = {};
            data.search = string;
            data.task = 'bixmailing.zoekKlanten';
            this.dom.klantenList.empty().append('<i class="uk-icon-spinner uk-icon-spin"></i>');
            if (this.activeRequest !== false) {
                this.activeRequest.abort();
            }
            this.activeRequest = $.getJSON(this.options.ajaxUrl, data)
                .done(function (data) {
                    if (data.success) {
                        if (data.result.klanten.length) {
                            $this.dom.klantenList.empty().append(data.result.klanten.map(function (obj) {
                                return '<li data-bix-userid="' + obj.user_id + '" data-bix-klantnummer="' + obj.klantnummer + '">' +
                                    '<a href="javascript:void(0)"><strong>' + obj.bedrijfsnaam + '</strong><br/>' +
                                    obj.klantnummer + ' - ' + obj.adres + '</a></li>';
                            }));
                            $this.checkData();
                        } else {
                            $this.dom.klantenList.empty().append('<li class="uk-alert">Geen resultaten</li>');
                        }
                    }
                })
                .always(function (data) {
                    UI.bixTools.showNotifications(data.messages);
                });
        },

        attachUser: function () {
            var $this = this, data = this.checkData();
            data.task = 'bixmailing.attachUser';
            $.post(this.options.ajaxUrl, data, '', 'json')
                .done(function (data) {
                    if (data.success) {
                        $this.selUserModal.hide();
                        $('#' + $this.state.vervoerder.toLowerCase() + '-records').trigger('loadTemplate');
                    }
                })
                .fail(function () {
                    UI.notify({message: 'Fout in request klant koppelen', status: 'danger'});
                })
                .always(function (data) {
                    UI.bixTools.showNotifications(data.messages);
                });

        }

    });

    return UI.bixselectuser;
}));
;/* *
 *	BixieMailing
 *  uploaddata.js.js
 *	Created on 19-3-14 11:39
 *  
 *  @author Matthijs
 *  @copyright Copyright (C)2014 Bixie.nl
 *
 */

(function (addon) {
    "use strict";

    var component;

    if (jQuery && UIkit) {
        component = addon(jQuery, UIkit);
    }

    if (typeof define === "function" && define.amd) {
        define("uikit-bixklantcontrols", ["uikit"], function () {
            return component || addon(jQuery, UIkit);
        });
    }

}(function ($, UI) {
    "use strict";

    UI.component('bixklantcontrols', {

        defaults: {
            klantnummer: 0,
            vervoerder: '',
            togglerSel: '.bix-toggler',
            openToggleIcon: 'uk-icon-minus-square-o uk-icon-small',
            closedToggleIcon: 'uk-icon-plus-square-o uk-icon-small',
            nrMailingsSel: '.bix-nrmailings',
            nrActiveMailingsSel: '.bix-nractivemailings'
        },

        boot: function () {
            UI.ready(function (context) {
                $("[data-bix-klantcontrols]", context).each(function () {
                    var $ele = $(this);
                    if (!$ele.data("bixklantcontrols")) {
                        UI.bixklantcontrols($ele, UI.Utils.options($ele.attr('data-bix-klantcontrols')));
                    }
                });
            });
        },

        init: function () {
            var $this = this, vervoerderControls;
            this.klantcontrol = this.element;
            this.klantrecords = $('[data-bix-klantrecords=' + this.options.klantnummer + '-' + this.options.vervoerder + ']');
            this.dom = {
                //inputs
                klantmailer: this.find("input[name='klantmailer[]']"),
                mailingChecks: this.klantrecords.find("input[name='mailing[]']"),
                email: this.find("input[name='email']"),
                //weergaves
                nrMailings: this.find(this.options.nrMailingsSel),
                nrActiveMailings: this.find(this.options.nrActiveMailingsSel)
            };
            //slide toggler
            this.find(this.options.togglerSel).click(function () {
                $(this).toggleClass('uk-active');
                if ($this.klantrecords.is(":hidden")) {
                    $(this).find('i').attr('class', $this.options.openToggleIcon);
                    $this.klantrecords.slideDown();
                } else {
                    $(this).find('i').attr('class', $this.options.closedToggleIcon);
                    $this.klantrecords.slideUp();
                }
            });
            //(de)select all
            this.dom.klantmailer.click(function () {
                $this.toggle($(this));
            });
            //mailselects
            this.dom.mailingChecks.click(function () {
                $this.process();
            });
            //uploadController
            vervoerderControls = $('#controls-' + this.options.vervoerder);
            this.uploadController = UI.bixuploadcontrols(vervoerderControls, UI.Utils.options(vervoerderControls.attr('data-bix-uploadcontrols')));

            this.process();

        },
        toggle: function ($check) {
            this.dom.mailingChecks.prop('checked', $check.is(':checked'));
            this.process();
        },

        //general processing
        process: function () {
            this.nrMailings = this.dom.mailingChecks.length;
            this.nrActiveMailings = this.dom.mailingChecks.filter(":checked").length;
            this.updateDom();
        },
        getData: function () {
            return {
                klantnummer: this.options.klantnummer,
                user_id: this.options.user_id,
                email: this.dom.email.val(),
                sendMailingIDs: this.dom.mailingChecks.filter(":checked").map(function () {
                    return $(this).val();
                }).get(),
                mailingIDs: this.klantrecords.find("[name='mailingIDs[]']").map(function () {
                    return $(this).val();
                }).get()
            };
        },
        updateDom: function () {
            if (this.dom.klantmailer.not(':checked') && this.nrActiveMailings > 0) {
                this.dom.klantmailer.prop('checked', true);
            }
            //totalen
            this.dom.nrMailings.text(this.nrMailings);
            this.dom.nrActiveMailings.text(this.nrActiveMailings);
            this.uploadController.process();
        }

    });

    return UI.bixklantcontrols;
}));
