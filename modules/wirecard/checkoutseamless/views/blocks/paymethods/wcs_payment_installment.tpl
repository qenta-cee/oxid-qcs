[{if $sPaymentID == "wcs_installment"}]

    [{if isset( $dobData.month ) }]
        [{assign var="iBirthdayMonth" value=$dobData.month }]
    [{else}]
        [{assign var="iBirthdayMonth" value=0}]
    [{/if}]
    [{if isset( $dobData.day ) }]
        [{assign var="iBirthdayDay" value=$dobData.day}]
    [{else}]
        [{assign var="iBirthdayDay" value=0}]
    [{/if}]
    [{if isset( $dobData.year ) }]
        [{assign var="iBirthdayYear" value=$dobData.year }]
    [{else}]
        [{assign var="iBirthdayYear" value=0}]
    [{/if}]

<dl>
    <dt>
        <input id="payment_[{$sPaymentID}]" type="radio" name="paymentid" value="[{$sPaymentID}]" [{if $oView->getCheckedPaymentId() == $paymentmethod->oxpayments__oxid->value}]checked[{/if}]>
        <label for="payment_[{$sPaymentID}]">[{$oView->getWcsPaymentLogo($sPaymentID)}]<b>[{ $oView->getWcsRawPaymentDesc($paymentmethod->oxpayments__oxdesc->value)}]
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

            </b></label>
    </dt>
    <dd class="[{if $oView->getCheckedPaymentId() == $paymentmethod->oxpayments__oxid->value}]activePayment[{/if}]">
        [{assign var="aDynValues" value=$paymentmethod->getDynValues()}]
        [{if $aDynValues}]
        <ul>
            [{foreach from=$aDynValues item=value name=PaymentDynValues}]
            <li>
                <label>[{$oView->getWcsPaymentLogo($sPaymentID)}] [{ $value->name}]</label>
                <input id="[{$sPaymentID}]_[{$smarty.foreach.PaymentDynValues.iteration}]" type="text" class="textbox" size="20" maxlength="64" name="dynvalue[[{$value->name}]]" autocomplete="off" value="[{ $value->value}]">
            </li>
            [{/foreach}]
        </ul>
        [{/if}]

        [{block name="checkout_payment_longdesc"}]
        [{if $paymentmethod->oxpayments__oxlongdesc->value|trim}]
            <div class="desc">
                [{ $paymentmethod->oxpayments__oxlongdesc->getRawValue()}]
            </div>
        [{/if}]
        [{/block}]

        [{if $bShowDobField && $oView->hasWcsDobField($sPaymentID)}]
        <div class="desc">
            <ul class="form clear" style="">
                <li class="oxDate[{if $aErrors.oxuser__oxbirthdate}] oxInValid[{/if}]">
                    <label class="req">[{ oxmultilang ident="BIRTHDATE" suffix="COLON" }]</label>
                    <select class='oxMonth js-oxValidate js-oxValidate_date js-oxValidate_notEmpty' name='[{$sPaymentID}]_iBirthdayMonth'>
                        <option value="" >-</option>
                        [{section name="month" start=1 loop=13 }]
                        <option value="[{$smarty.section.month.index}]" [{if $iBirthdayMonth == $smarty.section.month.index}] selected="selected" [{/if}]>
                            [{oxmultilang ident="MONTH_NAME_"|cat:$smarty.section.month.index}]
                        </option>
                        [{/section}]
                    </select>
                    <label class="innerLabel" for="[{$sPaymentID}]_oxDay" style="left: 250px; top: 5px;">[{ oxmultilang ident="DAY" }]</label>
                    <input id="[{$sPaymentID}]_oxDay" class='oxDay js-oxValidate' name='[{$sPaymentID}]_iBirthdayDay' type="text" data-fieldsize="xsmall" maxlength="2" autocomplete="off" value="[{if $iBirthdayDay > 0 }][{$iBirthdayDay }][{/if}]" />
                    [{oxscript include="js/widgets/oxinnerlabel.js" priority=10 }]
                    [{oxscript add="$( '#`$sPaymentID`_oxDay' ).oxInnerLabel({sReloadElement:'#payment'});"}]
                    <label class="innerLabel" for="[{$sPaymentID}]_oxYear" style="left: 287px; top: 5px;">[{ oxmultilang ident="YEAR" }]</label>
                    <input id="[{$sPaymentID}]_oxYear" class='oxYear js-oxValidate' name='[{$sPaymentID}]_iBirthdayYear' type="text" data-fieldsize="small" maxlength="4" autocomplete="off" value="[{if $iBirthdayYear }][{$iBirthdayYear }][{/if}]" />
                    [{oxscript include="js/widgets/oxinnerlabel.js" priority=10 }]
                    [{oxscript add="$( '#`$sPaymentID`_oxYear' ).oxInnerLabel({sReloadElement:'#payment'});"}]
                    <p class="oxValidateError">
                        <span class="js-oxError_notEmpty">[{ oxmultilang ident="ERROR_MESSAGE_INPUT_NOTALLFIELDS" }]</span>
                        <span class="js-oxError_incorrectDate">[{ oxmultilang ident="ERROR_MESSAGE_INCORRECT_DATE" }]</span>
                        [{include file="message/inputvalidation.tpl" aErrors=$aErrors.oxuser__oxbirthdate}]
                    </p>
                </li>
            </ul>
        </div>
        [{/if}]

        [{if $oView->showWcsTrustedShopsCheckbox($sPaymentID)}]
             <input id="payolutionTerms" class='js-oxValidate js-oxValidate_notEmpty' name='payolutionTerms' type="checkbox" value="1" autocomplete="off" />[{ $oView->getWcsPayolutionTerms() }]
        [{/if}]
    </dd>
</dl>
    [{/if}]