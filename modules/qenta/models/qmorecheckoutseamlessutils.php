<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/oxid-qcs/blob/master/LICENSE
*/

class wirecardCheckoutSeamlessUtils
{
    protected $_logFilename = 'wirecardcheckoutseamless.log';

    public function convertPaymenttype($sPaymentID)
    {
        $sWirecardPaymentType = str_replace('wcs_', '', $sPaymentID);

        return strtoupper($sWirecardPaymentType);
    }


    public function isOwnPayment($sPaymentID)
    {
        return preg_match('/^wcs_/', $sPaymentID);
    }

    public function log($str)
    {
        $str = sprintf("%s %s\n", date('Y-m-d H:i:s'), $str);
        oxRegistry::getUtils()->writeToLog($str, $this->_logFilename);
    }

    /**
     * @return wirecardCheckoutSeamlessUtils
     */
    public static function getInstance()
    {
        if (is_object(oxRegistry::get('wirecardCheckoutSeamlessUtils'))) {
            return oxRegistry::get('wirecardCheckoutSeamlessUtils');
        }

        oxRegistry::set('wirecardCheckoutSeamlessUtils', new self());
    }

}
