<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/oxid-qcs/blob/master/LICENSE
*/

require_once getShopBasePath() . 'modules/qenta/checkoutseamless/autoloader.php';

class qmoreCheckoutSeamlessEvents
{

    public static function getAvailablePaymenttypes()
    {
        return Array(
            QentaCEE\Qmore\PaymentType::CCARD => array(
                'weight' => 1,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 1
            ),
            QentaCEE\Qmore\PaymentType::CCARD_MOTO => array(
                'weight' => 2,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            QentaCEE\Qmore\PaymentType::EPS => array(
                'weight' => 4,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            QentaCEE\Qmore\PaymentType::IDL => array(
                'weight' => 5,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            QentaCEE\Qmore\PaymentType::GIROPAY => array(
                'weight' => 6,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            QentaCEE\Qmore\PaymentType::TATRAPAY => array(
                'weight' => 7,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            QentaCEE\Qmore\PaymentType::TRUSTPAY => array(
                'weight' => 8,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            QentaCEE\Qmore\PaymentType::SOFORTUEBERWEISUNG => array(
                'weight' => 9,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 1
            ),
            QentaCEE\Qmore\PaymentType::SKRILLDIRECT => array(
                'weight' => 10,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            QentaCEE\Qmore\PaymentType::SKRILLWALLET => array(
                'weight' => 11,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            QentaCEE\Qmore\PaymentType::MPASS => array(
                'weight' => 12,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            QentaCEE\Qmore\PaymentType::BMC => array(
                'weight' => 13,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            QentaCEE\Qmore\PaymentType::P24 => array(
                'weight' => 14,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            QentaCEE\Qmore\PaymentType::MONETA => array(
                'weight' => 15,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            QentaCEE\Qmore\PaymentType::POLI => array(
                'weight' => 16,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            QentaCEE\Qmore\PaymentType::EKONTO => array(
                'weight' => 17,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            QentaCEE\Qmore\PaymentType::TRUSTLY => array(
                'weight' => 18,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            QentaCEE\Qmore\PaymentType::PBX => array(
                'weight' => 19,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            QentaCEE\Qmore\PaymentType::PSC => array(
                'weight' => 20,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            QentaCEE\Qmore\PaymentType::PAYPAL => array(
                'weight' => 22,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 1
            ),
            QentaCEE\Qmore\PaymentType::EPAYBG => array(
                'weight' => 23,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 0
            ),
            QentaCEE\Qmore\PaymentType::SEPADD => array(
                'weight' => 24,
                'fromamount' => 0,
                'toamount' => 100000,
                'activatePaymethod' => 1
            ),
            QentaCEE\Qmore\PaymentType::INVOICE . '_B2C' => array(
                'weight' => 25,
                'fromamount' => 10,
                'toamount' => 3500,
                'activatePaymethod' => 1
            ),
            QentaCEE\Qmore\PaymentType::INVOICE . '_B2B' => array(
                'weight' => 26,
                'fromamount' => 25,
                'toamount' => 3500,
                'activatePaymethod' => 1
            ),
            QentaCEE\Qmore\PaymentType::INSTALLMENT => array(
                'weight' => 27,
                'fromamount' => 150,
                'toamount' => 3500,
                'activatePaymethod' => 0
            ),
            QentaCEE\Qmore\PaymentType::VOUCHER => array(
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
        $sSql = "CREATE TABLE IF NOT EXISTS `qmorecheckoutseamless_order` (
              `OXID` char(32) NOT NULL COLLATE 'latin1_general_ci',
              `OXORDERID` char(32) NOT NULL COLLATE 'latin1_general_ci',
              `BASKET` TEXT NULL,
              `TIMESTAMP` timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
              PRIMARY KEY (`OXID`),
              KEY `WIRECARDCHECKOUTSEAMLESS_ORDER_OXORDERID` (`OXORDERID`)
            ) COLLATE='utf8_general_ci'";

        oxDb::getDb()->execute($sSql);
    }
}
