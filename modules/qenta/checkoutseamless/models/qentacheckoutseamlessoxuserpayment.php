<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/oxid-qcs/blob/master/LICENSE
*/

class qentacheckoutseamlessoxuserpayment extends qentacheckoutseamlessoxuserpayment_parent
{
    public function isQcsPaymethod($sPaymentID)
    {
        return qentaCheckoutSeamlessPayment::isQcsPaymethod($sPaymentID);
    }

    public function getQcsRawPaymentDesc($paymethodNameWithPrefix)
    {
        return qentaCheckoutSeamlessPayment::getQcsRawPaymentDesc($paymethodNameWithPrefix);
    }
}