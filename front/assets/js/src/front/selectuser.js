/* *
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
