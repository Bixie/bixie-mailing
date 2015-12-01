/*plugin postcodefill
 Postcode.nl lookup
 (c)2013 Bixie
 */


var BixPostcode = new Class({
    Implements: [Options],
    options: {
        reqUrl: '/index.php?option=com_virtuemart&controller=plugin&task=display&type=vmcustom&name=bixsystem&validateFormat=0',
        spinnerclass: 'uk-icon-spinner uk-icon-spin',
        iconclass: 'uk-icon-sign-in',
        userForm: false,
        bekendePostcode: '',
        formEls: {
            postcode: false,
            huisnummer: false,
            straat: false,
            plaats: false
        }
    },
    userForm: {
        postcode: false,
        huisnummer: false,
        straat: false,
        plaats: false
    },
    spinnerHolders: [],
    spinning: false,
    buttonEl: false,
    registerEl: false,
    initialize: function (input, button, register, options) {
        this.setOptions(options);
        this.inputEl = document.id(input);
        this.buttonEl = document.id(button);
        this.registerEl = document.id(register);
        var self = this;
        this.request = new Request.JSON({
            url: self.options.reqUrl,
            link: 'cancel',
            onRequest: function () {
                if (self.buttonEl) self.buttonEl.getElement('i').removeClass(self.options.iconclass).addClass(self.options.spinnerclass);
            },
            onFailure: function (error) {
                self.showMessage(error, 'danger', 'exclamation')
            },
            onError: function (text, error) {
                self.showMessage(text, 'danger', 'exclamation')
            },
            onSuccess: function (result) {
                if (self.buttonEl) self.buttonEl.getElement('i').removeClass(self.options.spinnerclass).addClass(self.options.iconclass);
                self.showResult(result);
            }
        });
        if (this.buttonEl) {
            this.buttonEl.addEvent('click', function () {
                if (self.inputEl.value) self.request.get(Object.toQueryString({postcode: self.inputEl.value}));
            });
            this.inputEl.addEvent('keydown', function (event) {
                if (event.key == "enter") {
                    if (self.inputEl.value) self.request.get(Object.toQueryString({postcode: self.inputEl.value}));
                }
            });
        }
        if (this.options.userForm) {
            this.userForm['postcode'] = this.inputEl;
            Object.each(this.options.formEls, function (elId, name) {
                self.userForm[name] = document.id(elId);
                if (name == 'postcode' || name == 'huisnummer') {
                    self.userForm[name].addEvent('keyup', function () {
                        self.lookup();
                    });
                }
                if (name == 'straat' || name == 'plaats') {
                    self.spinnerHolders.push(self.userForm[name].getParent('.uk-form-controls'));
                }
            });
            this.inputEl.value = this.options.bekendePostcode;
        }
        console.log(this.spinnerHolders);
    },
    spinInputs: function (state) {
        var self = this, spinner = new Element('i.uk-icon-spinner.uk-icon-spin', {style: 'margin-left:-25px'});
        this.spinnerHolders.each(function (spinnerHolder) {
            if (state == 1 && !self.spinning) {
                spinnerHolder.grab(spinner.clone());
            } else if (state == 0) {
                spinnerHolder.getElement('i.uk-icon-spin').destroy();
            }
        });
        this.spinning = state;
    },
    lookup: function (result) {
        var postcode = this.userForm.postcode.get('value').replace(/\s+/, '').toUpperCase();
        if (postcode.length != 6) postcode = false;
        var huisnummerRaw = this.userForm.huisnummer.get('value');
        var huisnummer_toevoeging = '';
        if (huisnummerRaw.match(/\-/)) {
            var split = huisnummerRaw.split('-')
            huisnummerRaw = split[0];
            huisnummer_toevoeging = split[1];
        }
        var huisnummer = huisnummerRaw.toInt();
        if (!postcode || !huisnummer) return;
        var req = {
            postcode: postcode,
            huisnummer: huisnummer,
            huisnummer_toevoeging: huisnummer_toevoeging
        };
        this.spinInputs(1);
        this.request.get(Object.toQueryString(req))
    },
    showResult: function (result) {
        var style = result.valid ? 'success' : 'danger';
        var icon = result.valid ? 'check' : 'exclamation';
        if (this.registerEl) {
            if (result.valid) this.registerEl.removeClass('uk-hidden').addClass('uk-animation-fade');
            else this.registerEl.addClass('uk-hidden').removeClass('uk-animation-fade');
        }
        this.showMessage(result.message, style, icon);
// console.log(result.info);
        if (this.options.userForm) {
            this.spinInputs(0);
            if (result.valid) {
                var huisnummer = result.info.houseNumber;
                if (result.info.houseNumberAddition != '') huisnummer += '-' + result.info.houseNumberAddition;
                this.userForm.postcode.set('value', result.info.postcode)
                this.userForm.huisnummer.set('value', huisnummer)
                this.userForm.straat.set('value', result.info.street)
                this.userForm.plaats.set('value', result.info.city)
            }
        }
    },
    showMessage: function (message, style, icon) {
        var iconHtml = icon ? '<i class="uk-icon-' + icon + ' uk-margin-small-right"></i>' : '';
        jQuery.UIkit.notify({
            message: iconHtml + message,
            status: style,
            timeout: 5000,
            pos: 'top-center'
        });
    }
});



