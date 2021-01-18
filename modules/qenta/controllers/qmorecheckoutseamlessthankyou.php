<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/oxid-qcs/blob/master/LICENSE
*/


/**
 * Order class wrapper for QMORE Checkout Seamless
 */
class qmoreCheckoutSeamlessThankyou extends qmoreCheckoutSeamlessThankyou_parent
{
    /**
     * @var oxOrder
     */
    protected $_oOrder = null;

    public function init()
    {
        $this->_oOrder = oxNew("oxOrder");
        $this->_oOrder->load($this->getSession()->getVariable('sess_challenge'));
        parent::init();
    }

    /**
     * @return bool
     */
    public function getPendingStatus()
    {
        return $this->_oOrder->oxorder__oxtransstatus == 'PENDING';
    }

}
