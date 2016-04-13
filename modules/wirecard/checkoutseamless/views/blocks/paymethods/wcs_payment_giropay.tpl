[{if $sPaymentID == "wcs_giropay"}]
[{ assign var="wirecardCheckoutSeamless_paymentdata_stored" value=$oView->hasWirecardCheckoutSeamlessPaymentData($sPaymentID) }]
[{ assign var="wirecardCheckoutSeamless_paymentdata" value=$oView->getWirecardCheckoutSeamlessPaymentData($sPaymentID) }]
[{oxscript include=$oView->getWirecardStorageJsUrl() priority=1)}]
[{oxscript include=$oViewConf->getModuleUrl('wirecardcheckoutseamless','out/src/wirecard.js') priority=10}]
<dl>
    <dt>
        <input type="hidden" id="[{$sPaymentID}]_stored" value="[{ $wirecardCheckoutSeamless_paymentdata_stored|intval }]" />
        <input id="payment_[{$sPaymentID}]" type="radio" name="paymentid" value="[{$sPaymentID}]" [{if $oView->getCheckedPaymentId() == $paymentmethod->oxpayments__oxid->value}]checked[{/if}]>
        <label for="payment_[{$sPaymentID}]">[{$oView->getWcsPaymentLogo($sPaymentID)}]<b>[{ $oView->getWcsRawPaymentDesc($paymentmethod->oxpayments__oxdesc->value)}]</b></label>
        [{if $paymentmethod->getPrice()}]
            [{assign var="oPaymentPrice" value=$paymentmethod->getPrice() }]
            [{if $oViewConf->isFunctionalityEnabled('blShowVATForPayCharge') }]
                ( [{oxprice price=$oPaymentPrice->getNettoPrice() currency=$currency}]
                [{if $oPaymentPrice->getVatValue() > 0}]
                    [{ oxmultilang ident="PLUS_VAT" }] [{oxprice price=$oPaymentPrice->getVatValue() currency=$currency }]
                [{/if}])
                [{else}]
                    ([{oxprice price=$oPaymentPrice->getBruttoPrice() currency=$currency}])
                [{/if}]
            [{/if}]

    </dt>
    <dd class="[{if $oView->getCheckedPaymentId() == $paymentmethod->oxpayments__oxid->value}]activePayment[{/if}]">
        <ul class="form">
            <li>
                <label>[{ oxmultilang ident="WIRECARDCHECKOUTSEAMLESS_BANK_CODE" }]</label>
                <input type="text" class="js-oxValidate js-oxValidate_notEmpty" size="20" maxlength="64" name="giropay_bankNumber" autocomplete="off" value="[{ $wirecardCheckoutSeamless_paymentdata.giropay_banknumber }]">
                <p class="oxValidateError">
                    <span class="js-oxError_notEmpty">[{ oxmultilang ident="ERROR_MESSAGE_INPUT_NOTALLFIELDS" }]</span>
                </p>
            </li>
            <li>
                <label>[{ oxmultilang ident="WIRECARDCHECKOUTSEAMLESS_BANK_ACCOUNT_NUMBER" }]</label>
                <input type="text" class="js-oxValidate js-oxValidate_notEmpty" size="20" maxlength="64" name="giropay_bankAccount" autocomplete="off" value="[{ $wirecardCheckoutSeamless_paymentdata.giropay_bankaccount }]">
                <p class="oxValidateError">
                    <span class="js-oxError_notEmpty">[{ oxmultilang ident="ERROR_MESSAGE_INPUT_NOTALLFIELDS" }]</span>
                </p>
            </li>
            <li>
                <label>[{ oxmultilang ident="WIRECARDCHECKOUTSEAMLESS_BANK_ACCOUNT_HOLDER" }]</label>
                <input type="text" class="" size="20" maxlength="64" name="giropay_accountOwner" autocomplete="off" value="[{ if $wirecardCheckoutSeamless_paymentdata.giropay_accountowner }][{ $wirecardCheckoutSeamless_paymentdata.giropay_accountowner }][{else}][{$oxcmp_user->oxuser__oxfname->value}] [{$oxcmp_user->oxuser__oxlname->value}][{/if}]">
                <p class="oxValidateError">
                    <span class="js-oxError_notEmpty">[{ oxmultilang ident="ERROR_MESSAGE_INPUT_NOTALLFIELDS" }]</span>
                </p>
            </li>
        </ul>
        
        [{block name="checkout_payment_longdesc"}]
        [{if $paymentmethod->oxpayments__oxlongdesc->value|trim}]
            <div class="desc">
                [{ $paymentmethod->oxpayments__oxlongdesc->getRawValue()}]
            </div>
        [{/if}]
        [{/block}]
    </dd>
</dl>
[{/if}]