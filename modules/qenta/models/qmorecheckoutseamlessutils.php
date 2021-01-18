<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/oxid-qcs/blob/master/LICENSE
*/

class qmoreCheckoutSeamlessUtils
{
    protected $_logFilename = 'qmorecheckoutseamless.log';

    public function convertPaymenttype($sPaymentID)
    {
        $sWirecardPaymentType = str_replace('qcs_', '', $sPaymentID);

        return strtoupper($sWirecardPaymentType);
    }


    public function isOwnPayment($sPaymentID)
    {
        return preg_match('/^qcs_/', $sPaymentID);
    }

    public function log($str)
    {
        $str = sprintf("%s %s\n", date('Y-m-d H:i:s'), $str);
        oxRegistry::getUtils()->writeToLog($str, $this->_logFilename);
    }

    /**
     * @return qmoreCheckoutSeamlessUtils
     */
    public static function getInstance()
    {
        if (is_object(oxRegistry::get('qmoreCheckoutSeamlessUtils'))) {
            return oxRegistry::get('qmoreCheckoutSeamlessUtils');
        }

        oxRegistry::set('qmoreCheckoutSeamlessUtils', new self());
    }

}
