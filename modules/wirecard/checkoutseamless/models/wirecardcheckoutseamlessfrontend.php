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

class wirecardCheckoutSeamlessFrontend
{
    /**
     * @var WirecardCEE_QMore_FrontendClient
     */
    protected $_client;

    public function __construct()
    {
        /** @var wirecardCheckoutSeamlessConfig $config */
        $config = wirecardCheckoutSeamlessConfig::getInstance();

        /** @var oxLang $oLang */
        $oLang = oxRegistry::get('oxLang');

        $this->_client = new WirecardCEE_QMore_FrontendClient(Array(
            'CUSTOMER_ID' => $config->getCustomerId(),
            'SHOP_ID' => $config->getShopId(),
            'LANGUAGE' => $oLang->getLanguageAbbr(),
            'SECRET' => $config->getSecret()
        ));

        $pluginVersion = WirecardCEE_QMore_FrontendClient::generatePluginVersion(
            'OXID ' . $config->getOxConfig()->getEdition(),
            $config->getOxConfig()->getVersion() . ' Revision: ' . $config->getOxConfig()->getRevision(),
            $config->getModuleId(),
            $config->getPluginVersion());

        $this->_client->setPluginVersion($pluginVersion);

        $oOrder = $this->_getOrder();

        $sHomeUrl = oxRegistry::getSession()->processUrl($config->getOxConfig()->getShopSecureHomeUrl());

        $sRtoken = oxRegistry::getSession()->getRemoteAccessToken(true);

        /** @var oxUtilsUrl $util */
        $util = oxRegistry::get("oxUtilsUrl");

        $this->_client->setConfirmUrl($util->cleanUrlParams($sHomeUrl . 'cl=order&fnc=wirecardConfirm&stoken=' . '&' . oxRegistry::getSession()->sid(true) . '&rtoken=' . $sRtoken,
            '&'));
        $this->_client->setSuccessUrl($util->cleanUrlParams($sHomeUrl . 'cl=order&fnc=wirecardSuccess', '&'));
        $this->_client->setPendingUrl($util->cleanUrlParams($sHomeUrl . 'cl=order&fnc=wirecardPending', '&'));
        $this->_client->setCancelUrl($util->cleanUrlParams($sHomeUrl . 'cl=order&fnc=wirecardCancel', '&'));
        $this->_client->setFailureUrl($util->cleanUrlParams($sHomeUrl . 'cl=order&fnc=wirecardFailure', '&'));

        $this->_client->setServiceUrl($config->getServiceUrl());

        $this->_client->setWindowName('wirecardCheckoutSeamlessIframe');
        $this->_client->setAutoDeposit($config->getAutoDeposit());
        $this->_client->setDuplicateRequestCheck($config->getDuplicateRequestCheck());
        $this->_client->setAutoDeposit($config->getAutoDeposit());
        $this->_client->setConfirmMail($config->getConfirmMail());
        $this->_client->createConsumerMerchantCrmId($oOrder->getFieldData('oxbillemail'));
	    if(isset($_SESSION['wcs-consumerDeviceId'])){
		    $this->_client->consumerDeviceId = $_SESSION['wcs-consumerDeviceId'];
		    unset($_SESSION['wcs-consumerDeviceId']);
	    }
    }

    public function initiate()
    {
        $this->_client->setStorageReference(oxRegistry::getSession()->getId(),
            wirecardCheckoutSeamlessDataStorage::getInstance()->getStorageId());

        return $this->_client->initiate();
    }

    public function setOrderData(oxOrder $oOrder, $paymentType)
    {
        /** @var wirecardCheckoutSeamlessConfig $config */
        $config = wirecardCheckoutSeamlessConfig::getInstance();

        $paymentTypeShop = strtoupper(str_replace('wcs_', '', $oOrder->oxorder__oxpaymenttype->value));
        $paymentType = $paymentTypeShop;

        //change invoice and installment paymenttypes
        switch ($paymentTypeShop) {
            case 'INVOICE_B2B':
            case 'INVOICE_B2C':
                $paymentType = 'INVOICE';
                break;
        }
        $this->_client->setPaymentType($paymentType);

        $this->_client->setCustomerStatement($this->_getCustomerStatement($paymentType));
        $this->_client->__set('paymentTypeShop', $paymentTypeShop);

        $this->_client->setAmount($oOrder->getTotalOrderSum());
        $orderRef = sprintf('%010d', $oOrder->oxorder__oxordernr->value);
        $this->_client->setOrderReference($orderRef);
        $this->_client->setOrderDescription(sprintf('%s: #%s', $oOrder->getFieldData('oxbillemail'), $oOrder->getId()));
        $this->_client->setCurrency(oxRegistry::getConfig()->getActShopCurrencyObject()->name);
        $this->_client->__set('oxid_orderid', $oOrder->getId());
        $this->_client->__set('riskConfigAlias', $config->getRiskConfigAlias());

        if ($config->getRiskSuppress()) {
            $this->_client->__set('riskSuppress', 'TRUE');
        }

        return $this;
    }

    public function setFinancialInstitution($inst)
    {
        $this->_client->setFinancialInstitution($inst);
    }

    /**
     * Set Wirecard Consumer Data Objects
     *
     * @param oxOrder $oOrder
     *
     * @return wirecardCheckoutSeamlessFrontend
     */
    public function setConsumerData(oxOrder $oOrder, $paymentType)
    {
        /** @var wirecardCheckoutSeamlessConfig $config */
        $config = wirecardCheckoutSeamlessConfig::getInstance();
        $consumerData = new WirecardCEE_Stdlib_ConsumerData();

        if ($config->getSendAdditionalCustomerData() || in_array($paymentType, array('INVOICE_B2B', 'INVOICE_B2C', 'INSTALLMENT'))) {

            $consumerData->setEmail($oOrder->getFieldData('oxbillemail'));
            $oUser = $oOrder->getOrderUser();
            $oUser->oxuser__oxustid->value;

            if (!empty($oUser->oxuser__oxustid->value)) {
                $consumerData->setCompanyVatId($oUser->oxuser__oxustid->value);
            }

            if (!empty($oUser->oxuser__oxcompany->value)) {
                $consumerData->setCompanyName($oUser->oxuser__oxcompany->value);
            }


            // processing birth date which came from output as array
            $consumerBirthDate = is_array($oUser->oxuser__oxbirthdate->value) ? $oUser->convertBirthday($oUser->oxuser__oxbirthdate->value) : $oUser->oxuser__oxbirthdate->value;

            if ($consumerBirthDate != '0000-00-00') {
                $consumerData->setBirthDate(new DateTime($consumerBirthDate));
            }

            // billing Address
            $billingAddressObj = new WirecardCEE_Stdlib_ConsumerData_Address(WirecardCEE_Stdlib_ConsumerData_Address::TYPE_BILLING);
            $billingAddressObj->setFirstname($oOrder->getFieldData('oxbillfname'));
            $billingAddressObj->setLastname($oOrder->getFieldData('oxbilllname'));
            $billingAddressObj->setAddress1($oOrder->getFieldData('oxbillstreet'));
            $billingAddressObj->setAddress2($oOrder->getFieldData('oxbillstreetnr'));
            $billingAddressObj->setCity($oOrder->getFieldData('oxbillcity'));

            $sBillingCountryId = $oOrder->getFieldData('oxbillcountryid');
            $oDB = oxDb::GetDB();
            $sBillingCountry = $oDB->getOne("select oxisoalpha2 from oxcountry where oxid = '$sBillingCountryId'");

            $billingAddressObj->setCountry($sBillingCountry);
            $billingAddressObj->setState($oOrder->getFieldData('oxbillstateid'));
            $billingAddressObj->setZipCode($oOrder->getFieldData('oxbillzip'));
            $billingAddressObj->setFax($oOrder->getFieldData('oxbillfax'));
            $billingAddressObj->setPhone($oOrder->getFieldData('oxbillfon'));
            $consumerData->addAddressInformation($billingAddressObj);

            // shipping address
            $shippingAddressObj = new WirecardCEE_Stdlib_ConsumerData_Address(WirecardCEE_Stdlib_ConsumerData_Address::TYPE_SHIPPING);

            $oShippingData = $oOrder->getDelAddressInfo();
            if ($oShippingData) {
                $shippingAddressObj->setFirstname($oShippingData->getFieldData('oxfname'));
                $shippingAddressObj->setLastname($oShippingData->getFieldData('oxlname'));
                $shippingAddressObj->setAddress1($oShippingData->getFieldData('oxstreet'));
                $shippingAddressObj->setAddress2($oShippingData->getFieldData('oxstreetnr'));
                $shippingAddressObj->setCity($oShippingData->getFieldData('oxcity'));

                $sShippingCountryId = $oShippingData->getFieldData('oxcountryid');
                $oDB = oxDb::GetDB();
                $sShippingCountry = $oDB->getOne("select oxisoalpha2 from oxcountry where oxid = '$sShippingCountryId'");

                $shippingAddressObj->setCountry($sShippingCountry);
                $shippingAddressObj->setState($oShippingData->getFieldData('oxstateid'));
                $shippingAddressObj->setZipCode($oShippingData->getFieldData('oxzip'));
                $shippingAddressObj->setFax($oShippingData->getFieldData('oxfax'));
                $shippingAddressObj->setPhone($oShippingData->getFieldData('oxfon'));
            } else {
                $shippingAddressObj->setFirstname($oOrder->getFieldData('oxbillfname'));
                $shippingAddressObj->setLastname($oOrder->getFieldData('oxbilllname'));
                $shippingAddressObj->setAddress1($oOrder->getFieldData('oxbillstreet'));
                $shippingAddressObj->setAddress2($oOrder->getFieldData('oxbillstreetnr'));
                $shippingAddressObj->setCity($oOrder->getFieldData('oxbillcity'));
                $shippingAddressObj->setCountry($sBillingCountry);
                $shippingAddressObj->setState($oOrder->getFieldData('oxbillstateid'));
                $shippingAddressObj->setZipCode($oOrder->getFieldData('oxbillzip'));
                $shippingAddressObj->setFax($oOrder->getFieldData('oxbillfax'));
                $shippingAddressObj->setPhone($oOrder->getFieldData('oxbillfon'));
            }
            $consumerData->addAddressInformation($shippingAddressObj);
        } elseif ((in_array($paymentType, array('INVOICE_B2B', 'INVOICE_B2C'))
            && $config->getInvoiceProvider() == 'PAYOLUTION') || ($paymentType == 'INSTALLMENT' && $config->getInstallmentProvider() == 'PAYOLUTION')
        ) {
            $oUser = $oOrder->getOrderUser();

            if (!empty($oUser->oxuser__oxustid->value) && $paymentType == 'INVOICE_B2B') {
                $consumerData->setCompanyVatId($oUser->oxuser__oxustid->value);
            }

            if (!empty($oUser->oxuser__oxcompany->value) && $paymentType == 'INVOICE_B2B') {
                $consumerData->setCompanyName($oUser->oxuser__oxcompany->value);
            }

            // processing birth date which came from output as array
            $consumerBirthDate = is_array($oUser->oxuser__oxbirthdate->value) ? $oUser->convertBirthday($oUser->oxuser__oxbirthdate->value) : $oUser->oxuser__oxbirthdate->value;

            if ($consumerBirthDate != '0000-00-00' && ($paymentType == 'INVOICE_B2C' || $paymentType == 'INSTALLMENT')) {
                $consumerData->setBirthDate(new DateTime($consumerBirthDate));
            }

            // billing Address
            $billingAddressObj = new WirecardCEE_Stdlib_ConsumerData_Address(WirecardCEE_Stdlib_ConsumerData_Address::TYPE_BILLING);
            $billingAddressObj->setFirstname($oOrder->getFieldData('oxbillfname'));
            $billingAddressObj->setLastname($oOrder->getFieldData('oxbilllname'));
            $billingAddressObj->setAddress1($oOrder->getFieldData('oxbillstreet'));
            $billingAddressObj->setAddress2($oOrder->getFieldData('oxbillstreetnr'));
            $billingAddressObj->setCity($oOrder->getFieldData('oxbillcity'));

            $sBillingCountryId = $oOrder->getFieldData('oxbillcountryid');
            $oDB = oxDb::GetDB();
            $sBillingCountry = $oDB->getOne("select oxisoalpha2 from oxcountry where oxid = '$sBillingCountryId'");

            $billingAddressObj->setCountry($sBillingCountry);
            $billingAddressObj->setState($oOrder->getFieldData('oxbillstateid'));
            $billingAddressObj->setZipCode($oOrder->getFieldData('oxbillzip'));
            $billingAddressObj->setFax($oOrder->getFieldData('oxbillfax'));
            $billingAddressObj->setPhone($oOrder->getFieldData('oxbillfon'));
            $consumerData->addAddressInformation($billingAddressObj);
        } elseif ((in_array($paymentType, array('INVOICE_B2B', 'INVOICE_B2C'))
            && ($config->getInvoiceProvider() == 'RATEPAY' || $config->getInvoiceProvider() == 'WIRECARD')
                   || ($paymentType == 'INSTALLMENT' && $config->getInstallmentProvider() == 'RATEPAY'))
        ) {

            $oUser = $oOrder->getOrderUser();
            // processing birth date which came from output as array
            $consumerBirthDate = is_array($oUser->oxuser__oxbirthdate->value) ? $oUser->convertBirthday($oUser->oxuser__oxbirthdate->value) : $oUser->oxuser__oxbirthdate->value;

            if ($consumerBirthDate != '0000-00-00') {
                $consumerData->setBirthDate(new DateTime($consumerBirthDate));
            }
        }

        if (isset($_SERVER['REMOTE_ADDR'])) {
            $consumerData->setIpAddress($_SERVER['REMOTE_ADDR']);
        }
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $consumerData->setUserAgent($_SERVER['HTTP_USER_AGENT']);
        }

        $this->_client->setConsumerData($consumerData);

        return $this;
    }


    /**
     * Set Wirecard Basket Data to Frontend Client
     *
     * @param oxOrder $oOrder
     *
     * @return wirecardCheckoutSeamlessFrontend
     */
	public function setBasket(oxOrder $oOrder, $paymentType)
	{
		/** @var wirecardCheckoutSeamlessConfig $config */
		$config = wirecardCheckoutSeamlessConfig::getInstance();

		if ($config->getSendAdditionalBasketData()
		    || ((in_array($paymentType, array('INVOICE_B2B', 'INVOICE_B2C'))
		         && $config->getInvoiceProvider() != 'PAYOLUTION')
		        || ($paymentType == 'INSTALLMENT' && $config->getInstallmentProvider() != 'PAYOLUTION'))
		) {
			$oOrderArticles = $oOrder->getOrderArticles();
			$oLang = oxRegistry::get('oxLang');
			$iLangId = $oLang->getBaseLanguage();

			$basketItemsCount = 0;
			$basket = new WirecardCEE_Stdlib_Basket();

			foreach ($oOrderArticles as $oOrderArticle) {
				$netPrice = number_format($oOrderArticle->oxorderarticles__oxnprice->rawValue, 2);
				$netTax = number_format($oOrderArticle->oxorderarticles__oxbprice->rawValue - $oOrderArticle->oxorderarticles__oxnprice->rawValue,
					2);
				$amount = $oOrderArticle->oxorderarticles__oxamount->rawValue;
				$item = new WirecardCEE_Stdlib_Basket_Item($oOrderArticle->oxorderarticles__oxartnum->rawValue);

				$item->setUnitGrossAmount(number_format($oOrderArticle->oxorderarticles__oxbprice->rawValue, 2, '.', ''))
				     ->setUnitNetAmount(number_format($netPrice, 2, '.', ''))
				     ->setUnitTaxAmount(number_format($netTax, 2, '.', ''))
				     ->setUnitTaxRate(number_format($oOrderArticle->oxarticles__oxvat->rawValue, 3, '.', ''))
				     ->setDescription(strip_tags($oOrderArticle->oxarticles__oxshortdesc->rawValue))
				     ->setName($oOrderArticle->oxarticles__oxtitle->rawValue);

				if (strlen($oOrderArticle->oxorderarticles__oxurlimg)) {
					$item->setImageUrl($oOrderArticle->oxorderarticles__oxurlimg);
				}

				$basket->addItem($item, $amount);
			}
			//add possible additional costs as articles to basket
			$aAdditionalCosts = array(
				'shipping cost' => array(
					'description' => $oLang->translateString('SHIPPING_COST', $iLangId),
					'vat' => $oOrder->oxorder__oxdelvat->rawValue,
					'price' => $oOrder->oxorder__oxdelcost->rawValue
				),
				'paymethod cost' => array(
					'description' => $oLang->translateString('SURCHARGE',
							$iLangId) . ' ' . $oLang->translateString('PAYMENT_METHOD', $iLangId),
					'vat' => $oOrder->oxorder__oxpayvat->rawValue,
					'price' => $oOrder->oxorder__oxpaycost->rawValue
				),
				'wrapping cost' => array(
					'description' => $oLang->translateString('GIFT_WRAPPING', $iLangId),
					'vat' => $oOrder->oxorder__oxwrapvat->rawValue,
					'price' => $oOrder->oxorder__oxwrapcost->rawValue
				),
				'gift card cost' => array(
					'description' => $oLang->translateString('GREETING_CARD', $iLangId),
					'vat' => $oOrder->oxorder__oxgiftcardvat->rawValue,
					'price' => $oOrder->oxorder__oxgiftcardcost->rawValue
				),
				'discount' => array(
					'description' => $oLang->translateString('DISCOUNT', $iLangId),
					'vat' => 0,
					'price' => $oOrder->oxorder__oxdiscount->rawValue * -1
				),
			);

			foreach ($aAdditionalCosts as $type => $data) {
				if ($data['price'] != 0) {
					$basketItemsCount++;
					$netTaxAdditional = number_format($data['price'] * ($data['vat'] / 100), 2);
					$netPriceAdditional = number_format($data['price'] - $netTaxAdditional, 2);
					$item = new WirecardCEE_Stdlib_Basket_Item($type);

					$item->setUnitGrossAmount(number_format($data['price'], 2, '.', ''))
					     ->setUnitNetAmount(number_format($netPriceAdditional, 2, '.', ''))
					     ->setUnitTaxAmount(number_format($netTaxAdditional, 2, '.', ''))
					     ->setUnitTaxRate(number_format($data['vat'], 3, '.', ''))
					     ->setDescription(strip_tags($data['description']))
					     ->setName(strip_tags($data['description']));

					$basket->addItem($item, 1);
				}
			}
			$this->_client->setBasket($basket);
		}

		return $this;
	}

    /**
     * @return wirecardCheckoutSeamlessFrontend
     */
    public static function getInstance()
    {
        if (is_object(oxRegistry::get('wirecardCheckoutSeamlessFrontend'))) {
            return oxRegistry::get('wirecardCheckoutSeamlessFrontend');
        }

        oxRegistry::set('wirecardCheckoutSeamlessFrontend', new self());
    }

    private function _getCustomerStatement($paymenttype)
    {
        $oOrder = $this->_getOrder();
        /** @var wirecardCheckoutSeamlessConfig $config */
        $oConfig = wirecardCheckoutSeamlessConfig::getInstance();

        $orderReference = sprintf('%010d', $oOrder->oxorder__oxordernr->value);
        $customerStatementString = sprintf('%s id:%s', $oConfig->getShopName(), $orderReference);
        $customerStatementLength = ($paymenttype != 'POLI') ? $oConfig->getCustomerStatementLength() : 9;

        if ($paymenttype == 'POLI') {
            $customerStatementString = substr($oConfig->getShopName(), 0, 9);
        } elseif (strlen($orderReference) > $customerStatementLength) {
            $customerStatementString = substr($orderReference, -$customerStatementLength);
        } elseif (strlen($customerStatementString) > $customerStatementLength) {
            $customerStatementString = substr($oConfig->getShopName(), 0,
                    $customerStatementLength - 14) . ' id:' . $orderReference;
        }

        return $customerStatementString;
    }

    protected function _getOrder()
    {
        if ($this->_oOrder === null) {
            $oOrder = oxNew('oxorder');
            $oOrder->load(oxRegistry::getSession()->getVariable('sess_challenge'));
            $this->_oOrder = $oOrder;
        }

        return $this->_oOrder;
    }
}
