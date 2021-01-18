<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/oxid-qcs/blob/master/LICENSE
*/

class qmorecheckoutseamlessoxuserpayment extends qmorecheckoutseamlessoxuserpayment_parent
{
    public function isWcsPaymethod($sPaymentID)
    {
        return qmoreCheckoutSeamlessPayment::isWcsPaymethod($sPaymentID);
    }

    public function getWcsRawPaymentDesc($paymethodNameWithPrefix)
    {
        return qmoreCheckoutSeamlessPayment::getWcsRawPaymentDesc($paymethodNameWithPrefix);
    }
}