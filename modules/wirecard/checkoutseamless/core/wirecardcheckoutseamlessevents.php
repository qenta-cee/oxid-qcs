<?php
/**
 * Shop System Plugins - Terms of Use
 *
 * The plugins offered are provided free of charge by Wirecard Central Eastern Europe GmbH
 * (abbreviated to Wirecard CEE) and are explicitly not part of the Wirecard CEE range of
 * products and services.
 *
 * They have been tested and approved for full functionality in the standard configuration
 * (status on delivery) of the corresponding shop system. They are under General Public
 * License Version 2 (GPLv2) and can be used, developed and passed on to third parties under
 * the same terms.
 *
 * However, Wirecard CEE does not provide any guarantee or accept any liability for any errors
 * occurring when used in an enhanced, customized shop system configuration.
 *
 * Operation in an enhanced, customized configuration is at your own risk and requires a
 * comprehensive test phase by the user of the plugin.
 *
 * Customers use the plugins at their own risk. Wirecard CEE does not guarantee their full
 * functionality neither does Wirecard CEE assume liability for any disadvantages related to
 * the use of the plugins. Additionally, Wirecard CEE does not guarantee the full functionality
 * for customized shop systems or installed plugins of other vendors of plugins within the same
 * shop system.
 *
 * Customers are responsible for testing the plugin's functionality before starting productive
 * operation.
 *
 * By installing the plugin into the shop system the customer agrees to these terms of use.
 * Please do not use the plugin if you do not agree to these terms of use!
 */

require_once getShopBasePath() . 'modules/wirecard/checkoutseamless/autoloader.php';

class wirecardCheckoutSeamlessEvents
{

    public static function getAvailablePaymenttypes()
    {
        return Array(
            WirecardCEE_QMore_PaymentType::CCARD => array(
                'weight' => 1,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 1
            ),
            WirecardCEE_QMore_PaymentType::CCARD_MOTO => array(
                'weight' => 2,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            WirecardCEE_QMore_PaymentType::EPS => array(
                'weight' => 4,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            WirecardCEE_QMore_PaymentType::IDL => array(
                'weight' => 5,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            WirecardCEE_QMore_PaymentType::GIROPAY => array(
                'weight' => 6,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            WirecardCEE_QMore_PaymentType::TATRAPAY => array(
                'weight' => 7,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            WirecardCEE_QMore_PaymentType::TRUSTPAY => array(
                'weight' => 8,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            WirecardCEE_QMore_PaymentType::SOFORTUEBERWEISUNG => array(
                'weight' => 9,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 1
            ),
            WirecardCEE_QMore_PaymentType::SKRILLDIRECT => array(
                'weight' => 10,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            WirecardCEE_QMore_PaymentType::SKRILLWALLET => array(
                'weight' => 11,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            WirecardCEE_QMore_PaymentType::MPASS => array(
                'weight' => 12,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            WirecardCEE_QMore_PaymentType::BMC => array(
                'weight' => 13,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            WirecardCEE_QMore_PaymentType::P24 => array(
                'weight' => 14,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            WirecardCEE_QMore_PaymentType::MONETA => array(
                'weight' => 15,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            WirecardCEE_QMore_PaymentType::POLI => array(
                'weight' => 16,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            WirecardCEE_QMore_PaymentType::EKONTO => array(
                'weight' => 17,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            WirecardCEE_QMore_PaymentType::TRUSTLY => array(
                'weight' => 18,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            WirecardCEE_QMore_PaymentType::PBX => array(
                'weight' => 19,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            WirecardCEE_QMore_PaymentType::PSC => array(
                'weight' => 20,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            WirecardCEE_QMore_PaymentType::QUICK => array(
                'weight' => 21,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            WirecardCEE_QMore_PaymentType::PAYPAL => array(
                'weight' => 22,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 1
            ),
            WirecardCEE_QMore_PaymentType::EPAYBG => array(
                'weight' => 23,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            WirecardCEE_QMore_PaymentType::SEPADD => array(
                'weight' => 24,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 1
            ),
            WirecardCEE_QMore_PaymentType::INVOICE . '_B2C' => array(
                'weight' => 25,
                'fromamount' => 10,
                'toamount' => 3500,
                'activatePaymethod' => 1
            ),
            WirecardCEE_QMore_PaymentType::INVOICE . '_B2B' => array(
                'weight' => 26,
                'fromamount' => 25,
                'toamount' => 3500,
                'activatePaymethod' => 1
            ),
            WirecardCEE_QMore_PaymentType::INSTALLMENT => array(
                'weight' => 27,
                'fromamount' => 150,
                'toamount' => 3500,
                'activatePaymethod' => 0
            ),
            WirecardCEE_QMore_PaymentType::VOUCHER => array(
                'weight' => 28,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
        );
    }

    public static function addPaymentTypes()
    {
        /** @var oxLang $oLang */
        $oLang = oxRegistry::get('oxLang');
        $aLanguages = $oLang->getLanguageIds();

        foreach (self::getAvailablePaymenttypes() as $wpt => $configValues) {
            $trkey = sprintf('WIRECARD_CHECKOUT_SEAMLESS_%s', strtoupper($wpt));
            $pt = sprintf('%s_%s', 'wcs', strtolower($wpt));

            /** @var oxPayment $oPayment */
            $oPayment = oxNew('oxPayment');
            $oPayment->setId($pt);
            $oPayment->oxpayments__oxactive = new oxField($configValues['activatePaymethod']);
            $oPayment->oxpayments__oxaddsum = new oxField(0);
            $oPayment->oxpayments__oxaddsumtype = new oxField('abs');
            $oPayment->oxpayments__oxfromboni = new oxField(0);
            $oPayment->oxpayments__oxsort = new oxField($configValues['weight']);
            $oPayment->oxpayments__oxfromamount = new oxField($configValues['fromamount']);
            $oPayment->oxpayments__oxtoamount = new oxField($configValues['toamount']);

            foreach ($aLanguages as $iLanguageId => $sLangCode) {
                $oPayment->setLanguage($iLanguageId);
                $oPayment->oxpayments__oxlongdesc = new oxField($oLang->translateString($trkey . '_DESC',
                    $iLanguageId));
                $paymethodName = $oLang->translateString($trkey . '_LABEL', $iLanguageId);
                $oPayment->oxpayments__oxdesc = new oxField('WCS ' . $paymethodName);
                $oPayment->save();
            }
        }
    }

    /**
     * Disables payment methods
     */
    public static function disablePaymenttypes()
    {
        foreach (self::getAvailablePaymenttypes() as $pt => $configData) {
            $pt = sprintf('%s_%s', 'wcs', strtolower($pt));
            /** @var oxPayment $oPayment */
            $oPayment = oxNew('oxpayment');
            $oPayment->load($pt);
            $oPayment->oxpayments__oxactive = new oxField(0);
            $oPayment->save();
        }
    }

    /**
     * Execute action on activate event
     */
    public static function onActivate()
    {
        self::addWirecardCheckoutSeamlessOrderTable();
        self::addPaymentTypes();
    }

    /**
     * Execute action on deactivate event
     *
     * @return null
     */
    public static function onDeactivate()
    {
        self::disablePaymenttypes();
    }

    public static function addWirecardCheckoutSeamlessOrderTable()
    {
        $sSql = "CREATE TABLE IF NOT EXISTS `wirecardcheckoutseamless_order` (
              `OXID` char(32) NOT NULL,
              `OXORDERID` char(32) NOT NULL,
              `BASKET` TEXT NULL,
              `TIMESTAMP` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
              PRIMARY KEY (`OXID`),
              KEY `WIRECARDCHECKOUTSEAMLESS_ORDER_OXORDERID` (`OXORDERID`)
            );";

        oxDb::getDb()->execute($sSql);
    }
}
