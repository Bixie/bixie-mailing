/* *
 *  BixieMailing
 *  bestanden.js
 *  Created on 24-3-14 13:57
 *  
 *  @author Matthijs
 *  @copyright Copyright (C)2014 Bixie.nl
 *
 */

(function ($, UI) {
    "use strict";

    var bekijkUrl = '/index.php?option=com_bixmailing&format=raw&task=bixmailing.fileView';

    $.extend(UI.BixTools.prototype, {
        download: function (path, hash) {
            if (['csv', 'txt'].indexOf(path.slice(-3)) !== -1) {
                var modal = UI.modal("#file-download-modal", {bgclose: true});
                modal.show();
                modal.find('.bix-download').attr('href', '/download?h=' + hash).click(function () {
                    modal.hide();
                });
                modal.find('.bix-bekijk').click(function () {
                    modal.hide();
                    $('#file-contents').load(bekijkUrl + '&h=' + hash);
                });
                modal.find('.bix-mailings').click(function () {
                    modal.hide();
                    var patharr = path.split('/'), filename = patharr[patharr.length - 1];
                    window.location.href = $(this).data('base') + '?search=file:' + filename;
                });
            } else {
                window.location.href = '/download?h=' + hash;
            }
        }
    });

}(jQuery, UIkit));