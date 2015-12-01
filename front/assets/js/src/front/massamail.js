/* *
 *  BixieMailing
 *  massamail.js
 *  Created on 14-3-14 15:31
 *  
 *  @author Matthijs
 *  @copyright Copyright (C)2014 Bixie.nl
 *
 */

(function ($, UI) {

    "use strict";

    /**
     * Extensions for massamail form
     * @type {{geenBestanden: string}}
     */
    var lang = {
            geenBestanden: 'Voeg eerst bestanden toe door ze in het venster te slepen'
        },
        formID = 'bix-massamailform',
        confirmUrl = '/index.php?option=com_bixmailing&view=massamail&layout=confirm&format=raw';

    if (UI.components.bixupload) {
        $.extend(UI.components.bixupload.prototype, {
            massamail: function (data, uploadedFiles) {
                this.progressNotification.close();
                this.progressNotification = false; //force new one
                var $this = this, $formFileList = $('#bix-formfiles').empty();
                $.each(uploadedFiles, function (index, file) {
                    $formFileList.append($this.createFileFormList(file));
                });
                UI.notify({message: UI.bixTools.icon('check') + this.options.lang.uploadDone, status: 'success', timeout: 4000});
            },
            createFileFormList: function (file) {
                return '<li class="bix-fileinfo" data-filehash="' + file.hash + '"><span class="uk-display-block uk-text-truncate uk-width-1-1" ' +
                    'title="' + file.name + '"><i class="uk-icon-paperclip uk-margin-small-right"></i>' + file.name + '</span>' +
                    '</li>';
            }

        });
    }
    //console.log($form);

    $(document).on("ready", function () {
        $("[data-bix-ajax-submit]").each(function () {
            var $form = $(this), bixAjaxSubmit = UI.bixajaxsubmit($form, UI.Utils.options($form.attr('data-bix-ajax-submit')));
            if (!$form.attr('data-checkset') && bixAjaxSubmit && $form.attr('id') === formID) {
                bixAjaxSubmit.validateForm = function () {
                    var $this = this, valid = true, $filelist = $('#bix-formfiles');
                    if (!$filelist.find('li').length) {
                        UI.notify({message: lang.geenBestanden, status: 'warning'});
                        valid = false;
                    } else {
                        $this.find("[name='jform[bestanden][]']").remove();
                        $filelist.find('li').each(function () {
                            var fileHash = $(this).data('filehash');
                            $this.element.append('<input type="hidden" name="jform[bestanden][]" value="' + fileHash + '" />');
                        });
                    }
                    return valid;
                };
                bixAjaxSubmit.onSuccess = function (result) {
                    $form.load(confirmUrl + '&id=' + result.id);
                };
                $form.attr('data-checkset', true);
            }
        });
});


}(jQuery, UIkit));

