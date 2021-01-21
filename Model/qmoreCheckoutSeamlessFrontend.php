<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/oxid-qcs/blob/master/LICENSE
*/
namespace Qenta\Model;

use DateTime;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Application\Model\Order;

use Qenta\Core\qmoreCheckoutSeamlessConfig;

class qmoreCheckoutSeamlessFrontend
{
    /**
     * @var \QentaCEE\Qmore\FrontendClient
     */
    protected $_client;

    public function __construct()
    {
        /** @var qmoreCheckoutSeamlessConfig $config */
        $config = qmoreCheckoutSeamlessConfig::getInstance();

        /** @var oxLang $oLang */
        $oLang = Registry::get('oxLang');

        $this->_client = new \QentaCEE\Qmore\FrontendClient(Array(
            'CUSTOMER_ID' => $config->getCustomerId(),
            'SHOP_ID' => $config->getShopId(),
            'LANGUAGE' => $oLang->getLanguageAbbr(),
            'SECRET' => $config->getSecret()
        ));

        $pluginVersion = \QentaCEE\Qmore\FrontendClient::generatePluginVersion(
            'OXID ' . $config->getOxConfig()->getEdition(),
            $config->getOxConfig()->getVersion() . ' Revision: ' . $config->getOxConfig()->getRevision(),
            $config->getModuleId(),
            $config->getPluginVersion());

        $this->_client->setPluginVersion($pluginVersion);

        $oOrder = $this->_getOrder();

        $sHomeUrl = Registry::getSession()->processUrl($config->getOxConfig()->getShopSecureHomeUrl());

        $sRtoken = Registry::getSession()->getRemoteAccessToken(true);

        /** @var oxUtilsUrl $util */
        $util = Registry::get("oxUtilsUrl");

        $this->_client->setConfirmUrl($util->cleanUrlParams($sHomeUrl . 'cl=order&fnc=qentaConfirm&stoken=' . '&' . Registry::getSession()->sid(true) . '&rtoken=' . $sRtoken,
            '&'));
        $this->_client->setSuccessUrl($util->cleanUrlParams($sHomeUrl . 'cl=order&fnc=qentaSuccess', '&'));
        $this->_client->setPendingUrl($util->cleanUrlParams($sHomeUrl . 'cl=order&fnc=qentaPending', '&'));
        $this->_client->setCancelUrl($util->cleanUrlParams($sHomeUrl . 'cl=order&fnc=qentaCancel', '&'));
        $this->_client->setFailureUrl($util->cleanUrlParams($sHomeUrl . 'cl=order&fnc=qentaFailure', '&'));

        $this->_client->setServiceUrl($config->getServiceUrl());

        $this->_client->setWindowName('qmoreCheckoutSeamlessIframe');
        $this->_client->setAutoDeposit($config->getAutoDeposit());
        $this->_client->setDuplicateRequestCheck($config->getDuplicateRequestCheck());
        $this->_client->setAutoDeposit($config->getAutoDeposit());
        $this->_client->setConfirmMail($config->getConfirmMail());
        $this->_client->createConsumerMerchantCrmId($oOrder->getFieldData('oxbillemail'));
	    if(isset($_SESSION['qcs-consumerDeviceId'])){
		    $this->_client->consumerDeviceId = $_SESSION['qcs-consumerDeviceId'];
		    unset($_SESSION['qcs-consumerDeviceId']);
	    }
    }

    public function initiate()
    {
        $this->_client->setStorageReference(Registry::getSession()->getId(),
            qmoreCheckoutSeamlessDataStorage::getInstance()->getStorageId());

        return $this->_client->initiate();
    }

    public function setOrderData(Order $oOrder, $paymentType)
    {
        /** @var qmoreCheckoutSeamlessConfig $config */
        $config = qmoreCheckoutSeamlessConfig::getInstance();

        $paymentTypeShop = strtoupper(str_replace('qcs_', '', $oOrder->oxorder__oxpaymenttype->value));
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
        $this->_client->setCurrency(Registry::getConfig()->getActShopCurrencyObject()->name);
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
     * Set QENTA Consumer Data Objects
     *
     * @param Order $oOrder
     *
     * @return qmoreCheckoutSeamlessFrontend
     */
    public function setConsumerData(Order $oOrder, $paymentType)
    {
        /** @var qmoreCheckoutSeamlessConfig $config */
        $config = qmoreCheckoutSeamlessConfig::getInstance();
        $consumerData = new \QentaCEE\Stdlib\ConsumerData();

        if ($config->getSendBillingData() || in_array($paymentType, array('INVOICE_B2B', 'INVOICE_B2C', 'INSTALLMENT'))) {

	        $consumerData->setEmail( $oOrder->getFieldData( 'oxbillemail' ) );
	        $oUser = $oOrder->getOrderUser();
	        $oUser->oxuser__oxustid->value;

	        if ( ! empty( $oUser->oxuser__oxustid->value ) ) {
		        $consumerData->setCompanyVatId( $oUser->oxuser__oxustid->value );
	        }

	        if ( ! empty( $oUser->oxuser__oxcompany->value ) ) {
		        $consumerData->setCompanyName( $oUser->oxuser__oxcompany->value );
	        }


	        // processing birth date which came from output as array
	        $consumerBirthDate = is_array( $oUser->oxuser__oxbirthdate->value ) ? $oUser->convertBirthday( $oUser->oxuser__oxbirthdate->value ) : $oUser->oxuser__oxbirthdate->value;

	        if ( $consumerBirthDate != '0000-00-00' ) {
		        $consumerData->setBirthDate( new DateTime( $consumerBirthDate ) );
	        }

	        // billing Address
	        $billingAddressObj = new \QentaCEE\Stdlib\ConsumerData\Address( \QentaCEE\Stdlib\ConsumerData\Address::TYPE_BILLING );
	        $billingAddressObj->setFirstname( $oOrder->getFieldData( 'oxbillfname' ) );
	        $billingAddressObj->setLastname( $oOrder->getFieldData( 'oxbilllname' ) );
	        $billingAddressObj->setAddress1( $oOrder->getFieldData( 'oxbillstreet' ) );
	        $billingAddressObj->setAddress2( $oOrder->getFieldData( 'oxbillstreetnr' ) );
	        $billingAddressObj->setCity( $oOrder->getFieldData( 'oxbillcity' ) );

	        $sBillingCountryId = $oOrder->getFieldData( 'oxbillcountryid' );
	        $oDB               = DatabaseProvider::GetDB();
	        $sBillingCountry   = $oDB->getOne( "select oxisoalpha2 from oxcountry where oxid = '$sBillingCountryId'" );

	        $billingAddressObj->setCountry( $sBillingCountry );
	        $billingAddressObj->setState( $oOrder->getFieldData( 'oxbillstateid' ) );
	        $billingAddressObj->setZipCode( $oOrder->getFieldData( 'oxbillzip' ) );
	        $billingAddressObj->setFax( $oOrder->getFieldData( 'oxbillfax' ) );
	        $billingAddressObj->setPhone( $oOrder->getFieldData( 'oxbillfon' ) );
	        $consumerData->addAddressInformation( $billingAddressObj );
        }
            // shipping address
	    if ($config->getSendShippingData()
	        || (in_array($paymentType, array('INVOICE_B2B', 'INVOICE_B2C')) && $config->getInvoiceProvider() != 'PAYOLUTION')
	        || ($paymentType == 'INSTALLMENT' && $config->getInstallmentProvider() != 'PAYOLUTION')) {
            $shippingAddressObj = new \QentaCEE\Stdlib\ConsumerData\Address(\QentaCEE\Stdlib\ConsumerData\Address::TYPE_SHIPPING);

            $oShippingData = $oOrder->getDelAddressInfo();
            if ($oShippingData) {
                $shippingAddressObj->setFirstname($oShippingData->getFieldData('oxfname'));
                $shippingAddressObj->setLastname($oShippingData->getFieldData('oxlname'));
                $shippingAddressObj->setAddress1($oShippingData->getFieldData('oxstreet'));
                $shippingAddressObj->setAddress2($oShippingData->getFieldData('oxstreetnr'));
                $shippingAddressObj->setCity($oShippingData->getFieldData('oxcity'));

                $sShippingCountryId = $oShippingData->getFieldData('oxcountryid');
                $oDB = DatabaseProvider::GetDB();
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
     * Set QENTA Basket Data to Frontend Client
     *
     * @param Order $oOrder
     *
     * @return qmoreCheckoutSeamlessFrontend
     */
	public function setBasket(Order $oOrder, $paymentType)
	{
		/** @var qmoreCheckoutSeamlessConfig $config */
		$config = qmoreCheckoutSeamlessConfig::getInstance();

		if ($config->getSendAdditionalBasketData()
		    || ((in_array($paymentType, array('INVOICE_B2B', 'INVOICE_B2C'))
		         && $config->getInvoiceProvider() != 'PAYOLUTION')
		        || ($paymentType == 'INSTALLMENT' && $config->getInstallmentProvider() != 'PAYOLUTION'))
		) {
			$oOrderArticles = $oOrder->getOrderArticles();
			$oLang = Registry::get('oxLang');
			$iLangId = $oLang->getBaseLanguage();

			$basketItemsCount = 0;
			$basket = new \QentaCEE\Stdlib\Basket();

			foreach ($oOrderArticles as $oOrderArticle) {
				$netPrice = number_format($oOrderArticle->oxorderarticles__oxnprice->rawValue, 2);
				$netTax = number_format($oOrderArticle->oxorderarticles__oxbprice->rawValue - $oOrderArticle->oxorderarticles__oxnprice->rawValue,
					2);
				$amount = $oOrderArticle->oxorderarticles__oxamount->rawValue;
				$item = new \QentaCEE\Stdlib\Basket\Item($oOrderArticle->oxorderarticles__oxartnum->rawValue);

				$item->setUnitGrossAmount(number_format($oOrderArticle->oxorderarticles__oxbprice->rawValue, 2, '.', ''))
				     ->setUnitNetAmount(number_format($netPrice, 2, '.', ''))
				     ->setUnitTaxAmount(number_format($netTax, 2, '.', ''))
				     ->setUnitTaxRate(number_format($oOrderArticle->oxarticles__oxvat->rawValue, 3, '.', ''))
				     ->setDescription(strip_tags($oOrderArticle->oxarticles__oxshortdesc->rawValue))
				     ->setName($oOrderArticle->oxarticles__oxtitle->rawValue);

				if (strlen($oOrderArticle->oxorderarticles__oxurlimg->rawValue)) {
					$item->setImageUrl($oOrderArticle->oxorderarticles__oxurlimg->rawValue);
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
					$item = new \QentaCEE\Stdlib\Basket\Item($type);

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
     * @return qmoreCheckoutSeamlessFrontend
     */
    public static function getInstance()
    {
        if (is_object(Registry::get('qmoreCheckoutSeamlessFrontend'))) {
            return Registry::get('qmoreCheckoutSeamlessFrontend');
        }

        Registry::set('qmoreCheckoutSeamlessFrontend', new self());
    }

    private function _getCustomerStatement($paymenttype)
    {
        $oOrder = $this->_getOrder();
        /** @var qmoreCheckoutSeamlessConfig $config */
        $oConfig = qmoreCheckoutSeamlessConfig::getInstance();

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
            $oOrder = oxNew(\OxidEsales\Eshop\Application\Model\Order::class);
            $oOrder->load(Registry::getSession()->getVariable('sess_challenge'));
            $this->_oOrder = $oOrder;
        }

        return $this->_oOrder;
    }
}
