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
