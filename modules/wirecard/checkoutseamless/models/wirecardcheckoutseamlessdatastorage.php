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

class wirecardCheckoutSeamlessDataStorage
{
    /**
     * @var WirecardCEE_QMore_DataStorageClient
     */
    protected $_client;

    public function __construct()
    {
        /** @var wirecardCheckoutSeamlessConfig $config */
        $config = wirecardCheckoutSeamlessConfig::getInstance();

        /** @var oxLang $oLang */
        $oLang = oxRegistry::get('oxLang');

        /** @var oxUtilsUrl $util */
        $util = oxRegistry::get("oxUtilsUrl");
        $sHomeUrl = oxRegistry::getSession()->processUrl($config->getOxConfig()->getShopSecureHomeUrl());

        $sReturnUrl = $util->cleanUrlParams($sHomeUrl . 'cl=payment&fnc=datastorageReturn', '&');
        $this->_client = new WirecardCEE_QMore_DataStorageClient(Array(
            'CUSTOMER_ID' => $config->getCustomerId(),
            'SHOP_ID' => $config->getShopId(),
            'LANGUAGE' => $oLang->getLanguageAbbr(),
            'SECRET' => $config->getSecret()
        ));
        $this->_client->setReturnUrl($sReturnUrl);
    }

    public function initiate()
    {
        $config = wirecardCheckoutSeamlessConfig::getInstance();

        $this->_client->setOrderIdent(oxRegistry::getSession()->getId());

        if ($config->getDssSaqAEnable()) {
            $this->_client->setJavascriptScriptVersion('pci3');

            if (strlen($config->getIframeCssUrl())) {
                $this->_client->setIframeCssUrl($config->getIframeCssUrl());
            }

            $this->_client->setCreditCardCardholderNameField($config->getShowCreditcardCardholder());
            $this->_client->setCreditCardShowCvcField($config->getShowCreditcardCvc());
            $this->_client->setCreditCardShowIssueDateField($config->getShowCreditcardIssueDate());
            $this->_client->setCreditCardShowIssueNumberField($config->getShowCreditcardIssueNumber());
        }

        return $this->_client->initiate();
    }

    public function read($storageId = null)
    {
        if ($storageId !== null) {
            $this->_client->setStorageId($storageId);
        }

        return $this->_client->read();
    }

    public function getClient()
    {
        return $this->_client;
    }

    public function setStorageId($storageId)
    {
        oxRegistry::getSession()->setVariable('wirecardcheckoutseamlessStorageId', $storageId);
    }

    public function getStorageId()
    {
        return oxRegistry::getSession()->getVariable('wirecardcheckoutseamlessStorageId');
    }

    /**
     * @return wirecardCheckoutSeamlessDataStorage
     */
    public static function getInstance()
    {
        if (is_object(oxRegistry::get('wirecardCheckoutSeamlessDataStorage'))) {
            return oxRegistry::get('wirecardCheckoutSeamlessDataStorage');
        }

        oxRegistry::set('wirecardCheckoutSeamlessDataStorage', new self());
    }

}
