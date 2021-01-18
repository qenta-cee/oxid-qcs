[{if $sPaymentID == "wcs_ccard" || $sPaymentID == "wcs_ccard-moto"}]
[{if $sPaymentID == "wcs_ccard"}]
    [{ assign var="prefix" value="ccard"}]
    [{else}]
    [{ assign var="prefix" value="ccard-moto"}]
    [{/if}]
[{ assign var="qmoreCheckoutSeamless_paymentdata_stored" value=$oView->hasWirecardCheckoutSeamlessPaymentData($sPaymentID) }]
[{ assign var="qmoreCheckoutSeamless_paymentdata" value=$oView->getWirecardCheckoutSeamlessPaymentData($sPaymentID) }]
[{oxscript include=$oView->getWirecardStorageJsUrl() priority=1)}]
[{oxscript include=$oViewConf->getModuleUrl('qmorecheckoutseamless','out/src/wirecard.js') priority=10}]
<dl>
    <dt>
        <input type="hidden" id="[{$sPaymentID}]_stored" value="[{ $qmoreCheckoutSeamless_paymentdata_stored|intval }]" />
        <input id="payment_[{$sPaymentID}]" type="radio" name="paymentid" value="[{$sPaymentID}]"
               [{if $oView->getCheckedPaymentId() == $paymentmethod->oxpayments__oxid->value}]checked[{/if}]>
        <label for="payment_[{$sPaymentID}]">[{$oView->getWcsPaymentLogo($sPaymentID)}]<b>[{$oView->getWcsRawPaymentDesc($paymentmethod->oxpayments__oxdesc->value)}]</b></label>
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
        [{if $qmoreCheckoutSeamless_paymentdata_stored}]
        <div class="desc">
            [{ oxmultilang ident="WIRECARDCHECKOUTSEAMLESS_PAYMENTDATA_ALREADY_STORED" }]
        </div>
        [{/if}]

        [{if !$oViewConf->getDssSaqAEnable()}]
        <ul class="form">
            <li>
                <label>[{ oxmultilang ident="WIRECARDCHECKOUTSEAMLESS_CARDNUMBER" }]</label>
                <input type="text" class="js-oxValidate js-oxValidate_notEmpty" size="20" maxlength="64" name="[{ $prefix }]_pan" autocomplete="off" value="[{ $qmoreCheckoutSeamless_paymentdata.ccard_number }]">
                <p class="oxValidateError">
                    <span class="js-oxError_notEmpty">[{ oxmultilang ident="ERROR_MESSAGE_INPUT_NOTALLFIELDS" }]</span>
                </p>
            </li>
            [{if $oViewConf->getShowCreditCardCardholder() }]
            <li>
                <label>[{ oxmultilang ident="WIRECARDCHECKOUTSEAMLESS_CARDHOLDER" }]</label>
                <input type="text" size="20" class="js-oxValidate js-oxValidate_notEmpty" maxlength="64" name="[{ $prefix }]_cardholdername" autocomplete="off" value="[{ if $qmoreCheckoutSeamless_paymentdata.ccard_name }][{ $qmoreCheckoutSeamless_paymentdata.ccard_name }][{else}][{$oxcmp_user->oxuser__oxfname->value}] [{$oxcmp_user->oxuser__oxlname->value}][{/if}]">
                <p class="oxValidateError">
                    <span class="js-oxError_notEmpty">[{ oxmultilang ident="ERROR_MESSAGE_INPUT_NOTALLFIELDS" }]</span>
                </p>
                <br>
                <div class="note">[{ oxmultilang ident="IF_DIFFERENT_FROM_BILLING_ADDRESS" }]</div>
            </li>
            [{/if}]
            <li>
                <label>[{ oxmultilang ident="WIRECARDCHECKOUTSEAMLESS_VALID_UNTIL" }]</label>
                <select name="[{ $prefix }]_expirationMonth">
                    <option [{ if $qmoreCheckoutSeamless_paymentdata.ccard_month == "01"}]selected[{/if}]>01</option>
                    <option [{ if $qmoreCheckoutSeamless_paymentdata.ccard_month == "02"}]selected[{/if}]>02</option>
                    <option [{ if $qmoreCheckoutSeamless_paymentdata.ccard_month == "03"}]selected[{/if}]>03</option>
                    <option [{ if $qmoreCheckoutSeamless_paymentdata.ccard_month == "04"}]selected[{/if}]>04</option>
                    <option [{ if $qmoreCheckoutSeamless_paymentdata.ccard_month == "05"}]selected[{/if}]>05</option>
                    <option [{ if $qmoreCheckoutSeamless_paymentdata.ccard_month == "06"}]selected[{/if}]>06</option>
                    <option [{ if $qmoreCheckoutSeamless_paymentdata.ccard_month == "07"}]selected[{/if}]>07</option>
                    <option [{ if $qmoreCheckoutSeamless_paymentdata.ccard_month == "08"}]selected[{/if}]>08</option>
                    <option [{ if $qmoreCheckoutSeamless_paymentdata.ccard_month == "09"}]selected[{/if}]>09</option>
                    <option [{ if $qmoreCheckoutSeamless_paymentdata.ccard_month == "10"}]selected[{/if}]>10</option>
                    <option [{ if $qmoreCheckoutSeamless_paymentdata.ccard_month == "11"}]selected[{/if}]>11</option>
                    <option [{ if $qmoreCheckoutSeamless_paymentdata.ccard_month == "12"}]selected[{/if}]>12</option>
                </select>

                &nbsp;/&nbsp;

                <select name="[{ $prefix }]_expirationYear">
                    [{foreach from=$oViewConf->getCreditCardYears() item=year}]
                    <option [{ if $qmoreCheckoutSeamless_paymentdata.ccard_year == $year}]selected[{/if}]>[{$year}]</option>
                    [{/foreach}]
                </select>
            </li>
            [{if $oViewConf->getShowCreditCardCvc() }]
            <li>
                <label>[{ oxmultilang ident="WIRECARDCHECKOUTSEAMLESS_CVC" }]</label>
                <input type="text" class="" size="20" maxlength="64" name="[{ $prefix }]_cardVerifyCode" autocomplete="off" value="[{ $qmoreCheckoutSeamless_paymentdata.ccard_cvc }]">
                <p class="oxValidateError">
                    <span class="js-oxError_notEmpty">[{ oxmultilang ident="ERROR_MESSAGE_INPUT_NOTALLFIELDS" }]</span>
                </p>
                <div class="note">
                    [{ oxmultilang ident="WIRECARDCHECKOUTSEAMLESS_CVC_DESCRIPTION" }]
                    <a href="#" onclick="javascript:window.open('[{$oViewConf->getModuleUrl('qmorecheckoutseamless','out/img/cvc_help.jpg')}]','ccard_cvc','toolbar=no, location=no, directories=no, status=no, menubar=no, scrollbars=yes, resizable=yes, width=190, height=790');return false;">
                        [{ oxmultilang ident="WIRECARDCHECKOUTSEAMLESS_CVC_EXAMPLE" }]
                    </a>
                </div>
            </li>
            [{/if}]
            [{if $oViewConf->getShowCreditCardIssueDate() }]
            <li>
                <label>[{ oxmultilang ident="WIRECARDCHECKOUTSEAMLESS_ISSUE_DATE" }]</label>
                <select name="[{ $prefix }]_issueMonth">
                    <option [{ if $qmoreCheckoutSeamless_paymentdata.ccard_issuemonth == "01"}]selected[{/if}]>01</option>
                    <option [{ if $qmoreCheckoutSeamless_paymentdata.ccard_issuemonth == "02"}]selected[{/if}]>02</option>
                    <option [{ if $qmoreCheckoutSeamless_paymentdata.ccard_issuemonth == "03"}]selected[{/if}]>03</option>
                    <option [{ if $qmoreCheckoutSeamless_paymentdata.ccard_issuemonth == "04"}]selected[{/if}]>04</option>
                    <option [{ if $qmoreCheckoutSeamless_paymentdata.ccard_issuemonth == "05"}]selected[{/if}]>05</option>
                    <option [{ if $qmoreCheckoutSeamless_paymentdata.ccard_issuemonth == "06"}]selected[{/if}]>06</option>
                    <option [{ if $qmoreCheckoutSeamless_paymentdata.ccard_issuemonth == "07"}]selected[{/if}]>07</option>
                    <option [{ if $qmoreCheckoutSeamless_paymentdata.ccard_issuemonth == "08"}]selected[{/if}]>08</option>
                    <option [{ if $qmoreCheckoutSeamless_paymentdata.ccard_issuemonth == "09"}]selected[{/if}]>09</option>
                    <option [{ if $qmoreCheckoutSeamless_paymentdata.ccard_issuemonth == "10"}]selected[{/if}]>10</option>
                    <option [{ if $qmoreCheckoutSeamless_paymentdata.ccard_issuemonth == "11"}]selected[{/if}]>11</option>
                    <option [{ if $qmoreCheckoutSeamless_paymentdata.ccard_issuemonth == "12"}]selected[{/if}]>12</option>
                </select>

                &nbsp;/&nbsp;

                <select name="[{ $prefix }]_issueYear">
                    [{foreach from=$oViewConf->getCreditCardIssueYears() item=year}]
                        <option [{ if $qmoreCheckoutSeamless_paymentdata.ccard_issueyear == $year}]selected[{/if}]>[{$year}]</option>
                    [{/foreach}]
                </select>
            </li>
            [{/if}]

            [{if $oViewConf->getShowCreditCardIssueNumber() }]
            <li>
                <label>[{ oxmultilang ident="WIRECARDCHECKOUTSEAMLESS_ISSUE_NUMBER" }]</label>
                <input type="text" class="" size="20" maxlength="64" name="[{ $prefix }]_issueNumber" autocomplete="off" value="[{ $qmoreCheckoutSeamless_paymentdata.ccard_issuenumber }]">
                <p class="oxValidateError">
                    <span class="js-oxError_notEmpty">[{ oxmultilang ident="ERROR_MESSAGE_INPUT_NOTALLFIELDS" }]</span>
                </p>
            </li>
            [{/if}]
        </ul>
    </dd>
    [{else}]
        <div id="qmoreCheckoutSeamless_[{ $prefix }]IframeContainer"></div>
    [{/if}]
</dl>
[{/if}]