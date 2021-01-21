<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/oxid-qcs/blob/master/LICENSE
*/
namespace Qenta\Extend\Controller;

use OxidEsales\Eshop\Application\Model\Order;
use OxidEsales\Eshop\Core\Registry;

/**
 * Order class wrapper for QMORE Checkout Seamless
 */
class qmoreCheckoutSeamlessThankyou extends qmoreCheckoutSeamlessThankyou_parent
{
    /**
     * @var Order
     */
    protected $_oOrder = null;

    public function init()
    {
        $this->_oOrder = oxNew(Order::class);
        $this->_oOrder->load(Registry::getSession()->getVariable('sess_challenge'));
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
