/* *
 *  DeVosDiensten
 *  bixuser.js
 *  Created on 10-1-2015 21:54
 *  
 *  @author Matthijs
 *  @copyright Copyright (C)2015 Bixie.nl
 *
 */

(function (addon) {
    "use strict";

    var component;

    if (jQuery && UIkit) {
        component = addon(jQuery, UIkit);
    }

    if (typeof define === "function" && define.amd) {
        define("uikit-bixuserform", ["uikit"], function () {
            return component || addon(jQuery, UIkit);
        });
    }

}(function ($, UI) {
    "use strict";

    var pcUrl = '/index.php?option=com_bixmailing&format=raw&task=bixmailing.postcodecheck',
        formID = 'bix-userform',
        confirmUrl = '/index.php?option=com_bixmailing&view=klant&layout=confirm&format=raw';

    function Postcodecheck ($context) {
        var $this = this;
        this.ajaxReq = false;
        this.spinFields = $context.find('[name*=address1], [name*=city]');
        this.spinFields.each(function () {
            var $input = $(this);
            $input.wrap($('<div></div>').css('position', 'relative').width($input.outerWidth()));
            $input.after($('<i class="uk-icon-spinner uk-icon-spin"></i>').css({
                position: 'absolute',
                right: '10px',
                top: '10px'
            }).hide());
        });
        $context.find("[name*=postal_code], [name*=address2]").keyup(function () {
            var split,
                adres = {},
                huisnummer, huisnummer_toevoeging = '',
                postcode = $context.find('[name*=postal_code]').val().replace(/\s+/, '').toUpperCase(),
                huisnummerRaw = $context.find('[name*=address2]').val();

            if (postcode.length !== 6) {
                postcode = false;
            }
            if (huisnummerRaw.match(/\-/)) {
                split = huisnummerRaw.split('-');
                huisnummerRaw = split[0];
                huisnummer_toevoeging = split[1];
            }
            huisnummer = huisnummerRaw.toInt();

            if (!postcode || !huisnummer) {
                return;
            }

            adres.pcode = postcode;
            adres.huisnr = huisnummer;
            adres.toev = huisnummer_toevoeging;

            $this.lookup(adres);
        });

        this.lookup = function (adres) {
            var $this = this;
            if (this.ajaxReq !== false){ //al aan het zoeken
                this.ajaxReq.abort();
            } else {
                this.spin();
            }
            UI.notify.closeAll(); //event.foutmeldingen weg
            this.ajaxReq = $.ajax({
                type: "POST",
                dataType: 'json',
                url: pcUrl,
                data: adres
            })
                .done(function (data) {
                    if (data.success && data.result.street) {
                        //console.log(data.result);
                        $context.find('[name*=address1]').val(data.result.street);
                        $context.find('[name*=city]').val(data.result.city);
                        $context.find('[name*=postal_code]').val(data.result.postcode);
                    }
                    $this.stopSpin();
                })
                .always(function (data) {
                    $this.ajaxReq = false;
                    if (data.messages) {
                        UI.bixTools.showNotifications(data.messages);
                    }
                });

        };
        this.spin = function () {
            this.spinFields.each(function () {
                $(this).next().show();
            });
        };
        this.stopSpin = function () {
            this.spinFields.each(function () {
                $(this).next().hide();
            });
        };
    }

    UI.component('bixuserform', {

        defaults: {},

        boot: function () {
            UI.ready(function (context) {
                $("[data-bix-userform]", context).each(function () {
                    var $ele = $(this);
                    if (!$ele.data("bixuserform")) {
                        UI.bixuserform($ele, UI.Utils.options($ele.attr('data-bix-userform')));
                    }
                });
                //set ajax submit
                $("[data-bix-ajax-submit]", context).each(function () {
                    var $form = $(this), bixAjaxSubmit = UI.bixajaxsubmit($form, UI.Utils.options($form.attr('data-bix-ajax-submit')));
                    if (!$form.attr('data-checkset') && bixAjaxSubmit && $form.attr('id') === formID) {
                        bixAjaxSubmit.validateForm = function () {
                            return document.formvalidator.isValid($form.get(0));
                        };
                        bixAjaxSubmit.onSuccess = function (result) {
                            if (result.confirm) {
                                $form.load(confirmUrl + '&id=' + result.id);
                            }
                            UI.bixTools.scrollToEl($form, {offset: 100});
                        };
                        $form.attr('data-checkset', true);
                    }
                });
            });
        },

        init: function () {
            var $this = this,
                //koppel naamvelden
                naamVeld = this.find('[name="jform[name]"]').prop('readonly', true).addClass('readonly'),
                voornaamVeld = this.find('[name="jform[profile][voornaam]"]').change(function () {
                    $this.setNaam(naamVeld, voornaamVeld, achternaamVeld);
                }),
                achternaamVeld = this.find('[name="jform[profile][achternaam]"]').change(function () {
                    $this.setNaam(naamVeld, voornaamVeld, achternaamVeld);
                });

            //swap fieldsets
            this.fieldsets = this.find('fieldset');
            this.element.prepend(this.fieldsets.get(1));

            //postcodes
            this.find('[name*=postal_code]').closest('fieldset').each(function () {
                new Postcodecheck($(this));
            });

        },
        setNaam: function (naamVeld, voornaamVeld, achternaamVeld) {
            var voornaam = voornaamVeld.val() ? voornaamVeld.val() + ' ': '';
            naamVeld.val(voornaam + achternaamVeld.val());
        }
    });

    return UI.bixuserform;
}));
