[{if $sPaymentID == "qcs_idl"}]
[{ assign var="qentaCheckoutSeamless_paymentdata_stored" value=$oView->hasQentaCheckoutSeamlessPaymentData($sPaymentID) }]
[{ assign var="qentaCheckoutSeamless_paymentdata" value=$oView->getQentaCheckoutSeamlessPaymentData($sPaymentID) }]
[{oxscript include=$oView->getQentaStorageJsUrl() priority=1)}]
[{oxscript include=$oViewConf->getModuleUrl('qentacheckoutseamless','out/src/wirecard.js') priority=10}]
<dl>
    <dt>
        <input type="hidden" id="[{$sPaymentID}]_stored" value="[{ $qentaCheckoutSeamless_paymentdata_stored|intval }]" />
        <input id="payment_[{$sPaymentID}]" type="radio" name="paymentid" value="[{$sPaymentID}]" [{if $oView->getCheckedPaymentId() == $paymentmethod->oxpayments__oxid->value}]checked[{/if}]>
        <label for="payment_[{$sPaymentID}]">[{$oView->getQcsPaymentLogo($sPaymentID)}]<b>[{ $oView->getQcsRawPaymentDesc($paymentmethod->oxpayments__oxdesc->value)}]</b></label>
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
                <span>[{ oxmultilang ident="WIRECARDCHECKOUTSEAMLESS_CHOOSE_FINANCIAL_INSTITUTION" }]</span>
                [{html_options name=ideal_financialInstitution options=$oView->getQentaCheckoutSeamlessFinancialInstitutions($sPaymentID)}]
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