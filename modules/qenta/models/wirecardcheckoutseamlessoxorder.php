<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/oxid-qcs/blob/master/LICENSE
*/


/**
 * Wirecard Checkout Seamless oxOrder class
 *
 * @see oxOrder
 */
class wirecardCheckoutSeamlessOxOrder extends wirecardCheckoutSeamlessOxOrder_parent
{
    // if wirecard paymenttype suppress oder email
    protected function _sendOrderByEmail($oUser = null, $oBasket = null, $oPayment = null)
    {
        if (wirecardCheckoutSeamlessUtils::getInstance()->isOwnPayment($this->oxorder__oxpaymenttype)) {
            return 1;
        } else {
            return parent::_sendOrderByEmail($oUser, $oBasket, $oPayment);
        }
    }

    // will be send by confirm
    public function sendWirecardCheckoutSeamlessOrderByEmail($oBasket, $oUserPayment = null)
    {
        $sUserId = $this->oxorder__oxuserid;
        /** @var oxUser $oUser */
        $oUser = oxNew('oxUser');
        $oUser->load($sUserId);

        $this->_setUser($oUser);

        /** @var oxUserPayment $oUserPayment */
        if ($oUserPayment === null) {
            $oUserPayment = $this->_setPayment($oBasket->getPaymentId());
        }

        return parent::_sendOrderByEmail($oUser, $oBasket, $oUserPayment);
    }

}
