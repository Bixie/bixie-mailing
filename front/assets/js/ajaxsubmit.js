/* *
 *	BixieMailing
 *  bixtools.js
 *	Created on 22-3-14 14:55
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
        define("uikit-bixajaxsubmit", ["uikit"], function () {
            return component || addon(jQuery, UIkit);
        });
    }

}(function ($, UI) {
    "use strict";

    UI.component('bixajaxsubmit', {

        defaults: {
            lang: {
                requestFail: 'Fout in request'
            },
            spinSel: 'button i'
        },

        boot: function () {
            UI.ready(function (context) {
                $("[data-bix-ajax-submit]", context).each(function () {
                    var $ele = $(this);
                    if (!$ele.data("bixajaxsubmit")) {
                        UI.bixajaxsubmit($ele, UI.Utils.options($ele.attr('data-bix-ajax-submit')));
                    }
                });
            });
        },

        init: function () {
            var $this = this;
            this.element.submit(function (e) {
                if ($this.validateForm()) {
                    $this.startSpin();
                    $.ajax({
                        type: "POST",
                        dataType: 'json',
                        url: $this.element.attr('action') + '&format=raw',
                        data: $this.element.serialize() // serializes the form's elements.
                    })
                        .done(function (data) {
                            if (data.success) {
                                $this.onSuccess(data.result);
                            } else {
                                $this.onError(data.result);
                            }
                        })
                        .fail(function () {
                            UI.notify({message: $this.options.lang.requestFail, status: 'danger'});
                        })
                        .always(function (data) {
                            $this.stopSpin();
                            if (data.messages) {
                                UI.bixTools.showNotifications(data.messages);
                            }
                        });
                }
                e.preventDefault();
            });

        },
        startSpin: function () {
            this.element.find(this.options.spinSel).addClass('uk-icon-spinner uk-icon-spin');
        },
        stopSpin: function () {
            this.element.find(this.options.spinSel).removeClass('uk-icon-spinner uk-icon-spin');
        },
        validateForm: function () {
            return true;
        },
        onSuccess: function (result) {
        },
        onError: function (result) {
        }

    });

    return UI.bixajaxsubmit;
}));
