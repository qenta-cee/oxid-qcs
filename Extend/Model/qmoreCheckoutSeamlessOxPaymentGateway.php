<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/oxid-qcs/blob/master/LICENSE
*/
namespace Qenta\Extend\Application\Model;

use OxidEsales\Eshop\Application\Model\Order;


/**
 * Payment gateway manager.
 * Checks and sets payment method data, executes payment.
 */
class qmoreCheckoutSeamlessOxPaymentGateway extends qmoreCheckoutSeamlessOxPaymentGateway_parent
{
    /**
     * Executes payment, returns true on success.
     *
     * @param double $dAmount Goods amount
     * @param Order &$oOrder User ordering object
     *
     * @return bool
     */
    public function executePayment($dAmount, &$oOrder)
    {
        return parent::executePayment($dAmount, $oOrder);
    }

}
