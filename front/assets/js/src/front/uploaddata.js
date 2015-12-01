/* *
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
