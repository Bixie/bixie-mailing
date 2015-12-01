/* *
 *	BixieMailing
 *  batch
 *	Created on 9-3-14 1:14
 *
 *  @author Matthijs
 *  @copyright Copyright (C)2014 Bixie.nl
 *
 */

jQuery(function ($) {
    "use strict";

    //render mailingnaam
    (function () {
        $('[data-form-mailingnaam]').each(function () {
            renderMailingNaam($(this));
        });
    })();

    //render mailingnaam
    function renderMailingNaam(form) {
        //vars
        var ajaxUrl = '/index.php?option=com_bixmailing&format=raw&task=mailing.renderMailingNaam';

        //load dom
        $('[name*=user_id], [name*=type]').each(function () {
            $(this).change(function () {
                getMailingNaam();
            });
        });
        form.find('[name="jform[naam]"]').after($('<button class="uk-button uk-button-small" type="button"><i class="uk-icon-refresh"></i></button>').click(getMailingNaam));

        function getMailingNaam() {
            var req = getUserAndType();
            req.task = form.data('form-mailingnaam');
            $.post(ajaxUrl, req, '', "json")
                .done(function (data) {
                    if (data.success) {
                        $.UIkit.notify.closeAll();
                        form.find('[name="jform[naam]"]').val(data.result.naam);
                    }
                })
                .fail(function () {
                    UIkit.notify({message: 'Fout in request', status: 'danger'});
                })
                .always(function (data) {
                    if (data.messages) {
                        UIkit.bixTools.showNotifications(data.messages);
                    }
                });
        }

        function getUserAndType() {
            return {
                user_id: form.find('[name*=user_id]').val(),
                type: form.find('[name*=type]').val()
            }
        }
    }
});