/* *
 *	BixieMailing
 *  bixtools.js
 *	Created on 22-3-14 14:55
 *  
 *  @author Matthijs
 *  @copyright Copyright (C)2014 Bixie.nl
 *
 */

(function ($, UI) {
    "use strict";

    var BixTools;

    /**
     * BixTools!
     * @constructor
     */
    BixTools = function () {

        this.options = $.extend({}, BixTools.defaults);

    };

    $.extend(BixTools.prototype, {

        /**
         * show array of notifications with UIkit.notify
         * @param messages
         */
        showNotifications: function (messages) {
            if (messages) {
                $.each(messages, function (type) {
                    $.each(this, function () {
                        UI.notify({message: this, status: type});
                    });
                });
            }
        },

        /**
         * UIkit icon markup
         * @param name
         * @param nomargin
         * @returns {string}
         */
        icon: function (name, nomargin) {
            var margin = nomargin ? '' : ' uk-margin-small-right';
            return '<i class="uk-icon-' + name + margin + '"></i>';
        },

        /**
         * Format human readeble Filesize
         * @param bytes
         * @returns {string}
         */
        formatFileSize: function (bytes) {
            if (typeof bytes !== 'number') {
                return '';
            }
            if (bytes >= 1000000000) {
                return (bytes / 1000000000).toFixed(2) + ' GB';
            }
            if (bytes >= 1000000) {
                return (bytes / 1000000).toFixed(2) + ' MB';
            }
            return (bytes / 1000).toFixed(2) + ' KB';
        },

        /**
         * Format human readeble Bitrate
         * @param bits
         * @returns {string}
         */
        formatBitrate: function (bits) {
            if (typeof bits !== 'number') {
                return '';
            }
            if (bits >= 1000000000) {
                return (bits / 1000000000).toFixed(2) + ' Gbit/s';
            }
            if (bits >= 1000000) {
                return (bits / 1000000).toFixed(2) + ' Mbit/s';
            }
            if (bits >= 1000) {
                return (bits / 1000).toFixed(2) + ' kbit/s';
            }
            return bits.toFixed(2) + ' bit/s';
        },

        /**
         * navigate Ulist with arrows
         * @param e
         * @param wrap
         */
        navigateUl: function (el, e, wrap) {
            var selected = el.find(".uk-active");
            e.preventDefault();
            if ([38, 40].indexOf(e.keyCode) !== -1) {
                if (selected.prev().length === 0) { //staat boven
                    if (e.keyCode === 40) { //down
                        el.find("li").removeClass("uk-active");
                        selected.next().addClass("uk-active");
                    } else if (wrap) {//onderste selecteren
                        selected.siblings().last().addClass("uk-active");
                    }
                } else if (selected.next().length === 0) { //staat onder
                    if (e.keyCode === 38) { //up
                        el.find("li").removeClass("uk-active");
                        selected.prev().addClass("uk-active");
                    } else if (wrap) { //bovenste selecteren
                        selected.siblings().first().addClass("uk-active");
                    }
                } else { // otherwise we just select the next one
                    el.find("li").removeClass("uk-active");
                    if (e.keyCode === 38) { //up
                        selected.prev().addClass("uk-active");
                    } else {
                        selected.next().addClass("uk-active");
                    }
                }
            }
        },

        /**
         * UIkit smoothscroller ripoff
         * @param targetEl
         * @param options
         */
        scrollToEl: function (targetEl, options) {
            options = $.extend({
                duration: 1000,
                transition: 'easeOutExpo',
                offset: 0,
                complete: function (ignore) {return ignore; }
            }, options);

            var ele = $(targetEl),
                target = ele.offset().top - options.offset,
                docheight = UI.$doc.height(),
                winheight = UI.$win.height();

            if ((target + winheight) > docheight) {
                target = docheight - winheight;
            }

            $("html,body").stop().animate({scrollTop: target}, options.duration, options.transition).promise().done(options.complete);
        }

    });

    BixTools.defaults = {
    };


    UI.BixTools = BixTools;

    UI.bixTools = new BixTools();

}(jQuery, jQuery.UIkit));