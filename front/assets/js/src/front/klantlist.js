/* *
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
