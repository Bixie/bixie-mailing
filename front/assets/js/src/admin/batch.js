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
});