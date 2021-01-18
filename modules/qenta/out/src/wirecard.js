/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/oxid-qcs/blob/master/LICENSE
*/

(function ($) {

    var ddWirecardPayment = {
        //field mapping. define which input field belongs to which ds parametername
        paymentTypes: {
            wcs_ccard: {
                paymentType: 'CCARD',
                iframe: null,
                fields: {
                    ccard_cardholdername: 'cardholdername',
                    ccard_pan: 'pan',
                    ccard_expirationMonth: 'expirationMonth',
                    ccard_expirationYear: 'expirationYear',
                    ccard_cardVerifyCode: 'cardVerifyCode',
                    ccard_issueMonth: 'issueMonth',
                    ccard_issueYear: 'issueYear',
                    ccard_issueNumber: 'issueNumber'
                }
            },
            "wcs_ccard-moto": {
                paymentType: 'CCARD',
                iframe: null,
                fields: {
                    "ccard-moto_cardholdername": 'cardholdername',
                    "ccard-moto_pan": 'pan',
                    "ccard-moto_expirationMonth": 'expirationMonth',
                    "ccard-moto_expirationYear": 'expirationYear',
                    "ccard-moto_cardVerifyCode": 'cardVerifyCode',
                    "ccard-moto_issueMonth": 'issueMonth',
                    "ccard-moto_issueYear": 'issueYear',
                    "ccard-moto_issueNumber": 'issueNumber'
                }
            },
            "wcs_sepa-dd": {
                paymentType: 'SEPA-DD',
                iframe: null,
                fields: {
                    sepadd_bankBic: 'bankBic',
                    sepadd_bankAccountIban: 'bankAccountIban',
                    sepadd_accountOwner: 'accountOwner'
                }
            },
            "wcs_elv": {
                paymentType: 'ELV',
                iframe: null,
                fields: {
                    elv_accountOwner: 'accountOwner',
                    elv_bankName: 'bankName',
                    elv_bankCountry: 'bankCountry',
                    elv_bankNumber: 'bankNumber',
                    elv_bankAccount: 'bankAccount'
                }
            },
            wcs_giropay: {
                paymentType: 'GIROPAY',
                iframe: null,
                fields: {
                    giropay_bankNumber: 'bankNumber',
                    giropay_bankAccount: 'bankAccount',
                    giropay_accountOwner: 'accountOwner'
                }
            },
            wcs_pbx: {
                paymentType: 'PBX',
                iframe: null,
                fields: {pbx_payerPayboxNumber: 'payerPayboxNumber'}
            },
            wcs_voucher: {
                paymentType: 'VOUCHER',
                iframe: null,
                fields: {voucher_voucherId: 'voucherId'}
            }
        },

        actPaymentType: null,

        /**
         * constructor: create instance
         * look for old sumbit event binded to form.
         * bind new submit event and trigger old one for validation
         */
        _create: function () {
            var self = this,
                el = self.element;

            var wdcee = new WirecardCEE_DataStorage();
            if ($('#wirecardCheckoutSeamless_ccardIframeContainer').length > 0) {
                this.paymentTypes['wcs_ccard'].iframe = wdcee.buildIframeCreditCard('wirecardCheckoutSeamless_ccardIframeContainer', '100%', '160px');
            }
            if ($('#wirecardCheckoutSeamless_ccard-motoIframeContainer').length > 0) {
                this.paymentTypes['wcs_ccard-moto'].iframe = wdcee.buildIframeCreditCard('wirecardCheckoutSeamless_ccard-motoIframeContainer', '100%', '160px');
            }

            var elEvents = $(el).data('events');

            // jquery 1.8+
            if (!elEvents)
                elEvents = jQuery._data(el[0], 'events');
            var oldSubmit = false;
            if (elEvents.submit) {
                oldSubmit = jQuery.extend(true, {}, elEvents.submit);
                if (oldSubmit) {
                    $.each(oldSubmit, function (i, handler) {
                        $(el).bind('wirecardOldsubmit', handler['handler']);
                    });
                }
                el.unbind("submit");
            }
            el.bind("submit", function () {
                var parentReturn = true;

                if (oldSubmit) {
                    parentReturn = $(el).triggerHandler('wirecardOldsubmit');
                    if (parentReturn === false) return false;
                }

                return self.submitPayment(this);
            });
            $("dl dd ul li input[type=text]", el).change([self], self.resetStored);

            /** hack to get things working in the basic layout */
            $("tr td ul li input[type=text]", el).change([self], self.resetStored);
        },

        /**
         * On submit get Paymentid and use seemless if necessary,
         * return bool to maybe stop paymentforms submit
         * or execute callback if seemless was successfull
         *
         * @return bool
         */
        submitPayment: function (oForm) {

            var sPaymentType = $('input[name=paymentid]:checked', oForm).val();
            this.actPaymentType = sPaymentType;

            // hidden payment data is already set
            var blStoredvalue = $('#' + sPaymentType + '_stored').val();
            if (blStoredvalue != "0" && typeof this.paymentTypes[sPaymentType] != "undefined" && this.paymentTypes[sPaymentType].iframe === null) {
                return true;
            }

            var oGatewayState = this.usePaymentGateway(sPaymentType, oForm);
            if (oGatewayState === 0) {
                // if not using seamless usePaymentGateway returns 0, return true and continue with next step
                return true;
            } else if (oGatewayState === null) {
                // if using iframe for paymentdata (pan) and browser has no postMessage support
                return true;
            } else {

                // request error, got dummy return object
                if (oGatewayState.getErrors)
                {
                    for (var x in oGatewayState) {
                        if (oGatewayState[x].consumerMessage)
                            this.showError(oGatewayState[x].consumerMessage);
                        else
                            this.showError(oGatewayState[x].message);
                    }
                }

                // if using seamless don't continue to next step, its done within the callback function (async)
                return false;
            }
        },

        /**
         * Check if  Paymentid is a wirecard payment and use seamless if necessary,
         * return bool to maybe stop paymentforms submit
         *
         * @return bool
         */
        usePaymentGateway: function (paymentType, oForm) {

            var self = this;
            if(this.paymentTypes[paymentType] == undefined)
            {
                //not using seamless for this paymentType
                return 0;
            }

            var paymentTypeObject = this.paymentTypes[paymentType];
            var paymentInformation = {};

            paymentInformation.paymentType = paymentTypeObject.paymentType;

            for(var fieldName in paymentTypeObject.fields)
            {
                var formField = $(oForm).find(":input[name=" + fieldName + "]");
                if(formField.length == 1) {
                    paymentInformation[paymentTypeObject.fields[fieldName]] = formField[0].value;
                }
            }

            if($('#' + paymentType + '_stored').val() == 0  || paymentTypeObject.iframe !== null)
            {
                return this.qpaySeamlessRequest(paymentInformation, function(oResponse) {

                    if(oResponse.getStatus() == 0)
                    {
                        //remove values from fields
                        for(var fieldName in paymentTypeObject.fields)
                        {
                            $(":input[name=" + fieldName + "]", oForm).val("");
                        }

                        var anonymizedPaymentInformation = oResponse.getAnonymizedPaymentInformation();
                        for(var fieldName in anonymizedPaymentInformation) {
                            $(this.element).empty();
                            var elem = $("<input>").attr('type', 'hidden');
                            elem.attr('name', fieldName);
                            elem.attr('value', anonymizedPaymentInformation[fieldName]);
                            $(this.element).append(elem);
                        }
                        self.setStored(paymentType, true);
                        oForm.submit();
                    }
                    else
                    {
                        if (paymentTypeObject.iframe === null)
                        {
                            self.showError('', 'CLEAR');
                            var errors = oResponse.getErrors();
                            for (var x in errors) {
                                if (errors[x].consumerMessage) self.showError(errors[x].consumerMessage);
                                else self.showError(errors[x].message);
                            }
                        }
                    }
                });
            }
            else
            {
                oForm.submit();
            }

        },

        /**
         * uses a asynchronous Request to store paymentinfo
         * on successful request it uses callback function to set new form params and resubmit the form
         *
         * @return
         */
        qpaySeamlessRequest: function (paymentInformation, callback) {

            try {
                var DataStorage = new WirecardCEE_DataStorage();
                return DataStorage.storePaymentInformation(paymentInformation, function (responseObject) {
                    callback(responseObject);
                });
            }
            catch (e) {
                //store failed. In this case we do not have a consumerMessage.
                var oReturn = {
                    getStatus: function () {
                        return 4;
                    },
                    getErrors: function () {
                        return [
                            {
                                message: 'Store operation failed with error: ' + e.message,
                                errorCode: e.errorCode
                            }
                        ];
                    }
                };
                return oReturn;
            }
        },

        /**
         * Set _stored hidden field to new Value if Paymendata was successfully stored,
         */
        setStored: function (paymentType, newValue) {
            $('#' + paymentType + '_stored').val(Number(newValue));
        },

        /**
         * Reset _stored hidden field if a textinput was changed,
         */
        resetStored: function (changedEl) {
            // ddWirecardPayment object is transmitted in data Map
            var self = changedEl.data[0];
            var sPaymentType = $('input[name=paymentid]:checked', self.element).val();
            self.setStored(sPaymentType, false);
        },

        /**
         * Add debug Message to Error List
         */
        debugLog: function (message) {
            //if jsDebugList exists debugging is enabled
            if (document.getElementById('debugList') != undefined) {
                var jsDebugList = document.getElementById('jsDebugList');
                var debugMsg = document.createElement('li');
                debugMsg.innerHTML = (new Date()) + ' ' + message;
                jsDebugList.appendChild(debugMsg);
            }
        },

        /**
         * Add Message to Error List
         */
        showError: function (message, type) {
            var actPaymentRadio = $('input[name=paymentid]:checked', self.element);
            var actPaymentErrorList = $('#wirecardErrorList', actPaymentRadio.parents('dl'));

            if (actPaymentErrorList.length) {
                var errorList = actPaymentErrorList.get(0);
            }
            else {
                var errorList = document.createElement('div');
                errorList.id = 'wirecardErrorList';
                var errorDiv = $('dd', actPaymentRadio.parents('dl'));
                errorDiv.prepend(errorList);
            }

            switch (type) {
                case 'CLEAR':
                    $(errorList).empty();
                    return;
                    break;
                case 'NEW':
                    errorList.innerHTML = '';
                    break;
                case 'APPEND':
                default:
                    break;
            }
            var errorMessage = document.createElement('p');
            errorMessage.innerHTML = message;
            errorList.appendChild(errorMessage)
            $("#" + errorList.id).addClass("status error corners");
            this.debugLog(message);
        }

    }

    /**
     * Form crosssite submit
     */
    $.widget("ui.ddWirecardPayment", ddWirecardPayment);

    $('form#payment').ddWirecardPayment();

})(jQuery);