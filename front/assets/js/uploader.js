/* *
 *	BixieMailing
 *  uploader.js
 *	Created on 11-3-14 1:51
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
        define("uikit-bixupload", ["uikit"], function () {
            return component || addon(jQuery, UIkit);
        });
    }

}(function ($, UI) {
    "use strict";

    UI.component('bixupload', {

        defaults: {
            ajaxUrl: '/index.php?option=com_bixmailing&format=raw',
            allowedExt: ['txt', 'csv'],
            callback: 'convertFiles',
            lang: {
                dropFile: 'Drop het bestand ergens in dit venster',
                extNotAllowed: 'Extensie niet toegestaan! Toegestaan zijn : ',
                bezigOpladen: 'Bezig met uploaden...',
                uploadDone: 'Uploaden voltooid',
                foutConversieRequest: 'Fout in request conversie',
                bezigConverteren: 'Bezig met converteren...',
                tekstConverteren: 'Converteren naar Excel',
                noValidFiles: 'Geen geldig exportbestand gevonden',
                bekijkdata: 'Bekijk data',
                mailfiles: 'Mailen',
                converting: {
                    Gls: 'GLS exportbestand wordt geconverteerd...',
                    Postnl: 'Post NL exportbestand wordt geconverteerd...',
                    Parcelware: 'Parcelware exportbestand wordt geconverteerd...'
                },
                downloaden: 'Downloaden'
            }
        },

        boot: function () {
            UI.ready(function (context) {
                $("[data-bix-upload]", context).each(function () {
                    var $ele = $(this);
                    if (!$ele.data("bixupload")) {
                        UI.bixupload($ele, UI.Utils.options($ele.attr('data-bix-upload')));
                    }
                });
            });
        },

        init: function () {
            this.activeUploads = 0;
            this.totalUploads = 0;
            this.uploadedFiles = [];
            this.dropNotification = false;
            this.progressNotification = false;

            this.setupUpload();

        },
        //set up the bitch
        setupUpload: function () {
            var $this = this;
            if (typeof this.element.fileupload === 'function') {
                this.element.fileupload({
                    dataType: 'json',
                    url: '/index.php?option=com_bixmailing&format=raw&task=bixmailing.upload',
                    formData: {uploadTask: this.options.callback},
                    dragover: function () {
                        if (!$this.dropNotification) {
                            $this.dropNotification = UI.notify({message: UI.bixTools.icon('upload') + $this.options.lang.dropFile, status: 'info', timeout: 6000 });
                        }
                        $this.element.find('.bix-dropbox').addClass('uk-active');
                    },
                    add: function (e, data) {
                        //validate
                        var file = data.files[0],
                            ext = file.name.substr((file.name.lastIndexOf('.') + 1));
                        if ($this.options.allowedExt.indexOf(ext) === -1) {
                            UI.notify({message: UI.bixTools.icon('ban') + $this.options.lang.extNotAllowed + $this.options.allowedExt.join(', '), status: 'danger'});
                            return;
                        }
                        //add
                        $this.activeUploads++;
                        $this.totalUploads++;
                        if (!$this.progressNotification) {
                            $this.dropNotification.close();
                            $this.progressNotification = UI.notify({message: $this.createProgress(), status: 'success', pos: 'bottom-center', timeout: 0});
                        }
                        //attach notify en submit
                        data.notification = UI.notify({message: $this.createUpload(data), status: 'info', timeout: 0});
                        data.submit();
                    },
                    progress: function (e, data) {
                        var progress = parseInt(data.loaded / data.total * 100, 10);
                        data.notification.element.find('.uk-progress-bar').css('width', progress + '%');
                    },
                    progressall: function (e, data) {
                        var progress = parseInt(data.loaded / data.total * 100, 10),
                            progressTxt = UI.bixTools.formatFileSize(data.loaded) + ' / ' + UI.bixTools.formatFileSize(data.total) + ' (' + UI.bixTools.formatBitrate(data.bitrate) + ')';
                        $this.progressNotification.element.find('.uk-progress-bar').css('width', progress + '%');
                        $this.progressNotification.element.find('.bix-progress-stats').text(progressTxt);
                    },
                    done: function (e, data) {
                        data.notification.close();
                        $this.element.find('.bix-dropbox').remove();
                        $.each(data.result.files, function (index, file) {
                            $this.activeUploads--;
                            file.hash = file.url.slice(-32);
                            file.type = $this.getFileType(file.name);
                            $this.uploadedFiles.push(file);
                            $this.element.append('<div class="bix-fileinfo uk-panel uk-panel-box uk-margin-small-bottom" data-filehash="' + file.hash + '">' +
                            '<div class="uk-grid"><div class="bix-filelist uk-width-3-4">' +
                            $this.createFileInfo(file) +
                            '</div><div class="bix-filebuttons uk-width-1-4"></div></div></div>');
                        });
                        //no running uploads
                        if ($this.activeUploads === 0) {
                            if (typeof $this[$this.options.callback] === 'function') {
                                $this[$this.options.callback](data, $this.uploadedFiles);
                            } else {
                                UI.notify.closeAll();
                            }
                            $this.uploadedFiles = [];
                            $this.totalUploads = 0;
                        }
                    }
                });
            }
        },
        //internal helpers
        getFileType: function (filename) {
            var type = false;
            if (filename.match(/\.xlsx$/)) {
                return 'Excel';
            }
            if (filename.match(/\.pdf$/)) {
                return 'Pdf';
            }
            if (filename.match(/^csvExport/i)) {
                type = 'Postnl';
            }
            if (filename.match(/^Export\s/i)) {
                type = 'Parcelware';
            }
            if (filename.match(/^LLShipment/i)) {
                type = 'Gls';
            }
            return type;
        },

        createUpload: function (data) {
            var file = data.files[0];
            return '<div class="bix-uploadinfo uk-text-center">' +
                '<h4 class="bix-filename uk-margin-remove uk-text-truncate" title="' + file.name + '">' + file.name + '</h4>' +
                '<span class="bix-filesize uk-text-small">' + UI.bixTools.formatFileSize(file.size) + '</span>' +
                '<div class="uk-progress uk-progress-info uk-progress-mini uk-margin-remove"><div class="uk-progress-bar"></div></div>' +
                '</div>';
        },

        createFileInfo: function (file) {
            var $this = this, type = $this.getFileType(file.name),
                typeclass = type ? type.toLowerCase() : '';
            return '<div class="bix-fileinfo" data-bix-fileinfo="{hash:\'' + file.hash + '\',name:\'' + file.name + '\',size:' + file.size + '}">' +
                '<strong class="uk-display-block uk-text-truncate uk-width-1-1 bix-file-icon-' + typeclass +
                '" title="' + file.name + '">' + file.name + '</strong>' +
                '<a class="uk-button uk-button-mini uk-float-right uk-margin-bottom" href="' + file.url + '" download>' +
                '<i class="uk-icon-download uk-margin-small-right"></i>' + $this.options.lang.downloaden + '</a>' +
                '<span class="bix-filesize uk-text-small">' + UI.bixTools.formatFileSize(file.size) + '</span></div>';
        },
        createProgress: function () {
            var $this = this;
            return '<div class="bix-uploadinfo"><h3 class="bix-progress-text uk-margin-remove">' + $this.options.lang.bezigOpladen + '</h3>' +
                '<span class="bix-progress-stats"></span>' +
                '<div class="uk-progress uk-progress-success uk-progress-striped uk-active"><div class="uk-progress-bar"></div></div>' +
                '</div>';
        }

    });

    return UI.bixupload;
}));
