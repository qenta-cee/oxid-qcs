<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/oxid-qcs/blob/master/LICENSE
*/

class wirecardcheckoutseamlessoxuserpayment extends wirecardcheckoutseamlessoxuserpayment_parent
{
    public function isWcsPaymethod($sPaymentID)
    {
        return wirecardCheckoutSeamlessPayment::isWcsPaymethod($sPaymentID);
    }

    public function getWcsRawPaymentDesc($paymethodNameWithPrefix)
    {
        return wirecardCheckoutSeamlessPayment::getWcsRawPaymentDesc($paymethodNameWithPrefix);
    }
}