<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/oxid-qcs/blob/master/LICENSE
*/

require_once getShopBasePath() . 'modules/qenta/checkoutseamless/autoloader.php';

class qentaCheckoutSeamlessDataStorage
{
    /**
     * @var WirecardCEE_QMore_DataStorageClient
     */
    protected $_client;

    public function __construct()
    {
        /** @var qentaCheckoutSeamlessConfig $config */
        $config = qentaCheckoutSeamlessConfig::getInstance();

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
        $config = qentaCheckoutSeamlessConfig::getInstance();

        $this->_client->setOrderIdent(oxRegistry::getSession()->getId());

        if ($config->getDssSaqAEnable()) {
            $this->_client->setJavascriptScriptVersion('pci3');

            if (strlen($config->getIframeCssUrl())) {
                $this->_client->setIframeCssUrl($config->getIframeCssUrl());
            }

            $this->_client->setCreditCardShowCardholderNameField($config->getShowCreditcardCardholder());
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
        oxRegistry::getSession()->setVariable('qentacheckoutseamlessStorageId', $storageId);
    }

    public function getStorageId()
    {
        return oxRegistry::getSession()->getVariable('qentacheckoutseamlessStorageId');
    }

    /**
     * @return qentaCheckoutSeamlessDataStorage
     */
    public static function getInstance()
    {
        if (is_object(oxRegistry::get('qentaCheckoutSeamlessDataStorage'))) {
            return oxRegistry::get('qentaCheckoutSeamlessDataStorage');
        }

        oxRegistry::set('qentaCheckoutSeamlessDataStorage', new self());
    }

}
