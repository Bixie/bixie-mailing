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
