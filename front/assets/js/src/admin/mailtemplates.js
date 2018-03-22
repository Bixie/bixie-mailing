/* *
 *	BixieMailing
 *  mailtemplates
 *	Created on 9-3-14 1:15
 *  
 *  @author Matthijs
 *  @copyright Copyright (C)2014 Bixie.nl
 *
 */

jQuery(function ($) {
    "use strict";

    //selecteren mailtemplates
    (function () {
        //vars
        var currentContent, currentSubject,
            ajaxUrl = 'index.php?option=com_bixmailing&format=raw',
            batchtaskInput = $('#batch-task'),
            taskInput = $('[name=task]'),
            subjectInput = $('[name*=subject]'),
            templateTextarea = $('[name*=template]'),
            editorHolder = $('#template-editor');

        //template selector
        $('[name*=event]').change(function () {
            $('[data-template-type]').addClass('uk-hidden');
            $('[data-template-type="' + $(this).val() + '"]').removeClass('uk-hidden');
        });
        $('[data-template-type]').each(function () {
            var el = $(this), content = el.find('div.content').html();
            el.find('button').click(function () {
                var subject = $(this).html();
                currentSubject = subject;
                currentContent = content;
                loadContent(subject, content);
            });
        });
        $('#load-template').click(function () {
            if (currentContent || currentSubject)
                loadContent(currentSubject, currentContent);
        });
        //actionbuttons
        $('#bix-cancelmail').click(resetMailform);
        $('#bix-sendmail').attr('onclick', '').click(function (event) {
            event.preventDefault();
            batchtaskInput.val('mail');
            taskInput.val('mailing.batch');
            var formData = $('.uk-form').serialize();
            $.post(ajaxUrl, formData, '', "json")
                .done(function (data) {
                    if (data.success) {
                        $('[name="cid[]"], [name=checkall-toggle]').attr('checked', false);
                        resetMailform()
                    }
                })
                .fail(function () {
                    $.UIkit.notify('Fout in request', 'danger');
                })
                .always(function (data) {
                    if (data.messages) {
                        $.each(data.messages, function (type) {
                            $.each(this, function () {
                                $.UIkit.notify(this, type);
                            });
                        });
                    }
                });
        });
        //functions
        function loadContent(subject, content) {
            editorHolder.removeClass('uk-hidden');
            subjectInput.val(subject);
            templateTextarea.val(content);
            templateTextarea.data('markdownarea').editor.setValue(content);
        }

        function resetMailform() {
            currentContent = '';
            currentSubject = '';
            $('[name^=batch]').val('');
            $('[data-template-type]').addClass('uk-hidden');
            editorHolder.addClass('uk-hidden');
            templateTextarea.data('markdownarea').editor.setValue('');
            //refresh templates
            $('#mailhistorie').trigger('loadTemplate');
        }
    })();


});