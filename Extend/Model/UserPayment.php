<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/oxid-qcs/blob/master/LICENSE
*/
namespace Qenta\Extend\Application\Model;

class qmorecheckoutseamlessoxuserpayment extends qmorecheckoutseamlessoxuserpayment_parent
{
    public function isQcsPaymethod($sPaymentID)
    {
        return qmoreCheckoutSeamlessPayment::isQcsPaymethod($sPaymentID);
    }

    public function getQcsRawPaymentDesc($paymethodNameWithPrefix)
    {
        return qmoreCheckoutSeamlessPayment::getQcsRawPaymentDesc($paymethodNameWithPrefix);
    }
}