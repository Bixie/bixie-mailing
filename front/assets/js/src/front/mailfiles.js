/* *
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
