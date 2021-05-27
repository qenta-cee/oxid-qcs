[{if $sPaymentID == "qcs_sepa-dd"}]
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
                <label>[{ oxmultilang ident="WIRECARDCHECKOUTSEAMLESS_BIC" }]</label>
                <input type="text" class="js-oxValidate js-oxValidate_notEmpty" size="20" maxlength="64" name="sepadd_bankBic" autocomplete="off" value="[{ $wirecardCheckoutSeamless_paymentdata.sepa_bankBic }]">
                <p class="oxValidateError">
                    <span class="js-oxError_notEmpty">[{ oxmultilang ident="ERROR_MESSAGE_INPUT_NOTALLFIELDS" }]</span>
                </p>
            </li>
            <li>
                <label>[{ oxmultilang ident="WIRECARDCHECKOUTSEAMLESS_IBAN" }]</label>
                <input type="text" class="js-oxValidate js-oxValidate_notEmpty" size="20" maxlength="64" name="sepadd_bankAccountIban" autocomplete="off" value="[{ $wirecardCheckoutSeamless_paymentdata.sepa_bankAccountIban }]">
                <p class="oxValidateError">
                    <span class="js-oxError_notEmpty">[{ oxmultilang ident="ERROR_MESSAGE_INPUT_NOTALLFIELDS" }]</span>
                </p>
            </li>
            <li>
                <label>[{ oxmultilang ident="WIRECARDCHECKOUTSEAMLESS_ACCOUNTHOLDER" }]</label>
                <input type="text" class="" size="20" maxlength="64" name="sepadd_accountOwner" autocomplete="off" value="[{ if $wirecardCheckoutSeamless_paymentdata.lsktoinhaber }][{ $wirecardCheckoutSeamless_paymentdata.sepa_accountOwner }][{else}][{$oxcmp_user->oxuser__oxfname->value}] [{$oxcmp_user->oxuser__oxlname->value}][{/if}]">
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