/* *
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
