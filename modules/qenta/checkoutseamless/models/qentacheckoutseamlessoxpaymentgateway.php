<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/oxid-qcs/blob/master/LICENSE
*/

/**
 * Payment gateway manager.
 * Checks and sets payment method data, executes payment.
 */
class qentaCheckoutSeamlessOxPaymentGateway extends qentaCheckoutSeamlessOxPaymentGateway_parent
{
    /**
     * Executes payment, returns true on success.
     *
     * @param double $dAmount Goods amount
     * @param oxOrder &$oOrder User ordering object
     *
     * @return bool
     */
    public function executePayment($dAmount, &$oOrder)
    {
        return parent::executePayment($dAmount, $oOrder);
    }

}
