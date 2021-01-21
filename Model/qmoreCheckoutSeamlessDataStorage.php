<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/oxid-qcs/blob/master/LICENSE
*/
namespace Qenta\Model;

use OxidEsales\Eshop\Core\Registry;

use Qenta\Core\qmoreCheckoutSeamlessConfig;


class qmoreCheckoutSeamlessDataStorage
{
    /**
     * @var \QentaCEE\Qmore\DataStorageClient
     */
    protected $_client;

    public function __construct()
    {
        /** @var qmoreCheckoutSeamlessConfig $config */
        $config = qmoreCheckoutSeamlessConfig::getInstance();

        /** @var oxLang $oLang */
        $oLang = Registry::get('oxLang');

        /** @var oxUtilsUrl $util */
        $util = Registry::get("oxUtilsUrl");
        $sHomeUrl = Registry::getSession()->processUrl($config->getOxConfig()->getShopSecureHomeUrl());

        $sReturnUrl = $util->cleanUrlParams($sHomeUrl . 'cl=payment&fnc=datastorageReturn', '&');
        $this->_client = new \QentaCEE\Qmore\DataStorageClient(Array(
            'CUSTOMER_ID' => $config->getCustomerId(),
            'SHOP_ID' => $config->getShopId(),
            'LANGUAGE' => $oLang->getLanguageAbbr(),
            'SECRET' => $config->getSecret()
        ));
        $this->_client->setReturnUrl($sReturnUrl);
    }

    public function initiate()
    {
        $config = qmoreCheckoutSeamlessConfig::getInstance();

        $this->_client->setOrderIdent(Registry::getSession()->getId());

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
        Registry::getSession()->setVariable('qmorecheckoutseamlessStorageId', $storageId);
    }

    public function getStorageId()
    {
        return Registry::getSession()->getVariable('qmorecheckoutseamlessStorageId');
    }

    /**
     * @return qmoreCheckoutSeamlessDataStorage
     */
    public static function getInstance()
    {
        if (is_object(Registry::get('qmoreCheckoutSeamlessDataStorage'))) {
            return Registry::get('qmoreCheckoutSeamlessDataStorage');
        }

        Registry::set('qmoreCheckoutSeamlessDataStorage', new self());
    }

}
