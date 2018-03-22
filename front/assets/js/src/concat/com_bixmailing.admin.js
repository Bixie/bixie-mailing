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
;/* *
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

    //batch verwerken
    (function () {
        var taskInput = $('#batch-task');
        $('[data-uk-switcher][data-batch-type]').on('uk.switcher.show', function (event, area) {
            taskInput.val(area.data('batch-type'));
        });
    })();

    //batch selected
    (function () {
        $('[name="cid[]"], [name=checkall-toggle]').click(setNrSelected);
        function setNrSelected() {
            $('[bix-nr-selected]').html($('[name="cid[]"]:checked').length);
        }

        setNrSelected();
    })();
});;/* *
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
});;/* *
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