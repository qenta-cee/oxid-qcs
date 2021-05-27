<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/oxid-qcs/blob/master/LICENSE
*/


require_once getShopBasePath() . 'modules/qenta/checkoutseamless/autoloader.php';


/**
 * Payment class wrapper for PayPal module
 *
 * @see oxPayment
 */
class qentaCheckoutSeamlessPayment extends qentaCheckoutSeamlessPayment_parent
{
    /**
     * @var qentaCheckoutSeamlessDataStorage
     */
    protected $_oQentaDataStorage;


    /**
     * @var WirecardCEE_QMore_DataStorage_Response_Read
     */
    protected $_oQentaDataStorageReadResponse;

    /**
     * url to qenta JS Library for cross domain request.
     * Should be returned by WirecardCEE_Client_DataStorage_Request_Initiation
     *
     * @var string
     */
    protected $_sQentaDataStorageJsUrl = null;

    public function render()
    {
        $sReturn = parent::render();

        $this->_initQentaDatastorage();

        print_r($sReturn);

        return $sReturn;
    }

    public function validatePayment()
    {
        $parentResult = parent::validatePayment();

        $aValues = Array();
        $sPaymentId = (string )oxRegistry::getConfig()->getRequestParameter('paymentid');
        $sPaymenttype = qentaCheckoutSeamlessUtils::getInstance()->convertPaymenttype($sPaymentId);
        $config = qentaCheckoutSeamlessConfig::getInstance();
        $oUser = $this->getUser();
        $oLang = oxRegistry::get('oxLang');

        switch ($sPaymenttype) {
            case WirecardCEE_QMore_PaymentType::IDL:
                $aValues['financialInstitution'] = (string)oxRegistry::getConfig()->getRequestParameter('ideal_financialInstitution');
                break;

            case WirecardCEE_QMore_PaymentType::EPS:
                $aValues['financialInstitution'] = (string)oxRegistry::getConfig()->getRequestParameter('eps_financialInstitution');
                break;

            case WirecardCEE_QMore_PaymentType::TRUSTPAY:
                $aValues['financialInstitution'] = (string)oxRegistry::getConfig()->getRequestParameter('trustpay_financialInstitution');
                break;

            case WirecardCEE_QMore_PaymentType::INVOICE . '_B2B':
                if ($config->getInvoiceProvider() == 'PAYOLUTION') {
                    $vatId = $oUser->oxuser__oxustid->value;
                    if ($this->hasQcsVatIdField($sPaymentId) && empty($vatId)) {
                        $sVatId = oxRegistry::getConfig()->getRequestParameter('sVatId');

                        if (!empty($sVatId)) {
                            $oUser->oxuser__oxustid = new oxField($sVatId, oxField::T_RAW);
                            $oUser->save();
                        }
                    }

                    if ($this->showQcsTrustedShopsCheckbox($sPaymentId)) {
                        if (!oxRegistry::getConfig()->getRequestParameter('payolutionTerms')) {
                            oxRegistry::getSession()->setVariable('qcs_payerrortext',
                                $oLang->translateString('WIRECARD_CHECKOUT_SEAMLESS_CONFIRM_PAYOLUTION_TERMS',
                                    $oLang->getBaseLanguage()));
                            $oSmarty = oxRegistry::get("oxUtilsView")->getSmarty();
                            $oSmarty->assign("aErrors", array('payolutionTerms' => 1));

                            return;
                        }
                    }
                }
                break;

            case WirecardCEE_QMore_PaymentType::INVOICE . '_B2C':

            case WirecardCEE_QMore_PaymentType::INSTALLMENT:
                if ($config->getInstallmentProvider() == 'PAYOLUTION') {
                    if ($this->hasQcsDobField($sPaymentId) && $oUser->oxuser__oxbirthdate == '0000-00-00') {
                        $iBirthdayYear = oxRegistry::getConfig()->getRequestParameter($sPaymentId . '_iBirthdayYear');
                        $iBirthdayDay = oxRegistry::getConfig()->getRequestParameter($sPaymentId . '_iBirthdayDay');
                        $iBirthdayMonth = oxRegistry::getConfig()->getRequestParameter($sPaymentId . '_iBirthdayMonth');

                        if (empty($iBirthdayYear) || empty($iBirthdayDay) || empty($iBirthdayMonth)) {
                            oxRegistry::getSession()->setVariable('qcs_payerrortext',
                                $oLang->translateString('WIRECARD_CHECKOUT_SEAMLESS_PLEASE_FILL_IN_DOB',
                                    $oLang->getBaseLanguage()));

                            return;
                        }

                        $dateData = array('day' => $iBirthdayDay, 'month' => $iBirthdayMonth, 'year' => $iBirthdayYear);
                        $aValues['dobData'] = $dateData;
                        oxRegistry::getSession()->setVariable('qcs_dobData', $dateData);

                        if (is_array($dateData)) {
                            $oUser->oxuser__oxbirthdate = new oxField($oUser->convertBirthday($dateData),
                                oxField::T_RAW);
                            $oUser->save();
                        }
                    }

                    //validate paymethod
                    if (!$this->qcsValidateCustomerAge($oUser, 18)) {
                        oxRegistry::getSession()->setVariable('qcs_payerrortext',
                            sprintf($oLang->translateString('WIRECARD_CHECKOUT_SEAMLESS_DOB_TOO_YOUNG',
                                $oLang->getBaseLanguage()), 18));

                        return;
                    }

                    if ($this->showQcsInstallmentTrustedShopsCheckbox($sPaymentId)) {
                        if (!oxRegistry::getConfig()->getRequestParameter('payolutionTerms')) {
                            oxRegistry::getSession()->setVariable('qcs_payerrortext',
                                $oLang->translateString('WIRECARD_CHECKOUT_SEAMLESS_CONFIRM_PAYOLUTION_TERMS',
                                    $oLang->getBaseLanguage()));
                            $oSmarty = oxRegistry::get("oxUtilsView")->getSmarty();
                            $oSmarty->assign("aErrors", array('payolutionTerms' => 1));

                            return;
                        }
                    }
                }
                break;
        }

        oxRegistry::getSession()->setVariable('qentaCheckoutSeamlessValues', $aValues);

        return $parentResult;
    }

    protected function _initQentaDatastorage()
    {
        $this->_oQentaDataStorage = qentaCheckoutSeamlessDataStorage::getInstance();

        try {
            $oResponse = $this->_oQentaDataStorage->initiate();
            /** @var WirecardCEE_QMore_DataStorage_Response_Initiation $oResponse */

            if ($oResponse->hasFailed()) {
                $dsErrors = $oResponse->getErrors();
                $sErrorMessages = '';
                if (!empty($dsErrors)) {
                    foreach ($dsErrors as $error) {
                        $sErrorMessages .= $error->getConsumerMessage();
                    }
                }
                oxRegistry::getSession()->setVariable('payerror', -1);
                oxRegistry::getSession()->setVariable('payerrortext', $sErrorMessages);
                $this->_aViewData['qentacheckoutseamless_errors'] = $sErrorMessages;
                qentaCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':ERROR:' . $sErrorMessages);
            } else {
                $this->_sQentaDataStorageJsUrl = $oResponse->getJavascriptUrl();
                $this->_oQentaDataStorage->setStorageId($oResponse->getStorageId());

                $this->_oQentaDataStorageReadResponse = $this->_oQentaDataStorage->read();
            }
        } catch (Exception $e) {

            oxRegistry::getSession()->setVariable('payerror', -1);
            oxRegistry::getSession()->setVariable('payerrortext', $e->getMessage());

            return;
        }

    }

    public function getQentaStorageJsUrl()
    {
        return $this->_sQentaDataStorageJsUrl;
    }

    public function getQentaDataStorageReadResponse()
    {
        return $this->_oQentaDataStorageReadResponse;
    }

    /**
     * get stored paymentData for selected payment
     *
     * @param string $sPaymenttype
     *
     * @return array $aResponse
     */
    protected function getQentaPaymentData($sPaymenttype = null)
    {
        $aResponse = array();
        if (!$sPaymenttype) {
            return $aResponse;
        } else {
            if ($sPaymenttype == 'qcs_ccard-moto') {
                //CCARD-MOTO is stored in the same store as CCARD, so we have to use sPaymenttype CCARD for reading here
                $sPaymenttype = 'qcs_ccard';
            }
        }
        if (!is_object($this->_oQentaDataStorageReadResponse)) {
            return $aResponse;
        }

        $sQentaPaymentType = qentaCheckoutSeamlessUtils::getInstance()->convertPaymenttype($sPaymenttype);
        if ($this->_oQentaDataStorageReadResponse->hasPaymentInformation($sQentaPaymentType)) {
            $aResponse = $this->_oQentaDataStorageReadResponse->getPaymentInformation($sQentaPaymentType);
        }

        return $aResponse;
    }

    public function getQentaCheckoutSeamlessPaymentData($sPaymenttype)
    {
        $aResponse = array();

        $aPaymentInformation = $this->getQentaPaymentData($sPaymenttype);

        if (is_array($aPaymentInformation) && !empty($aPaymentInformation)) {

            switch ($sPaymenttype) {
                case 'qcs_ccard':
                case 'qcs_ccard-moto':
                    $sExpiry = $aPaymentInformation['expiry'];
                    $aExpiry = explode('/', $sExpiry);
                    if (!empty($aExpiry)) {
                        if (isset($aExpiry[0])) {
                            $aResponse['ccard_month'] = (string)$aExpiry[0];
                        }
                        if (isset($aExpiry[1])) {
                            $aResponse['ccard_year'] = (string)$aExpiry[1];
                        }
                    }
                    $aResponse['ccard_name'] = (string)$aPaymentInformation['cardholdername'];
                    $aResponse['ccard_number'] = (string)$aPaymentInformation['maskedPan'];
                    $aResponse['ccard_brand'] = (string)$aPaymentInformation['brand'];
                    $aResponse['ccard_type'] = (string)$aPaymentInformation['financialInstitution'];
                    $aResponse['ccard_cvc'] = ($aPaymentInformation['cardVerifyCode']) ? (string)$aPaymentInformation['cardVerifyCode'] : '****';
                    break;

                case 'qcs_sepa-dd':
                    $aResponse['sepa_accountOwner'] = (string)$aPaymentInformation['accountOwner'];
                    $aResponse['sepa_bankBic'] = (string)$aPaymentInformation['bankBic'];
                    $aResponse['sepa_bankAccountIban'] = (string)$aPaymentInformation['bankAccountIban'];
                    break;

                case 'qcs_giropay':
                    $aResponse['giropay_banknumber'] = (string)$aPaymentInformation['bankNumber'];
                    $aResponse['giropay_bankaccount'] = (string)$aPaymentInformation['bankAccount'];
                    $aResponse['giropay_accountowner'] = (string)$aPaymentInformation['accountOwner'];
                    break;

                case 'qcs_pbx':
                    $aResponse['paybox_payerPayboxNumber'] = (string)$aPaymentInformation['payerPayboxNumber'];
                    break;

                case 'qcs_voucher':
                    $aResponse['voucher_voucherId'] = (string)$aPaymentInformation['voucherId'];
                    break;

                default:
                    break;
            }
        }

        return $aResponse;
    }

    public function datastorageReturn()
    {
        $sFallbackResponse = oxRegistry::getConfig()->getRequestParameter('response');
        echo '<!DOCTYPE>
<html>
    <head>
        <script type="text/javascript">
            function setResponse(response)
            {
                if(typeof parent.WirecardCEE_Fallback_Request_Object == "object")
                {
                    parent.WirecardCEE_Fallback_Request_Object.setResponseText(response);
                }
                else
                {
                    console.log("Not a valid seamless fallback call.");
                }
            }
        </script>
    </head>
    <body onload=\'setResponse("' . addslashes(html_entity_decode($sFallbackResponse)) . '");\'>
    </body>
</html>';
        exit();
    }

    /**
     * Checks if the given payment type has any financial intitutions
     * and if so returns them in an array (for paymentSelector.tpl)
     *
     * @param string $paymentType
     *
     * @return Array
     */
    public function getQentaCheckoutSeamlessFinancialInstitutions($sPaymentID)
    {
        $sPaymentType = qentaCheckoutSeamlessUtils::getInstance()->convertPaymenttype($sPaymentID);

        if (WirecardCEE_QMore_PaymentType::hasFinancialInstitutions($sPaymentType)) {
            return WirecardCEE_QMore_PaymentType::getFinancialInstitutions($sPaymentType);
        } elseif ($sPaymentType == WirecardCEE_QMore_PaymentType::TRUSTPAY) {

            $financialInstitutions = $this->getSession()->getVariable('qentaCheckoutSeamlessTrustPayFinancialInstitutions');
            $financialInstitutionsLastModified = $this->getSession()->getVariable('qentaCheckoutSeamlessTrustPayFinancialInstitutionsLastModified');

            /** @var qentaCheckoutSeamlessConfig $config */
            $config = qentaCheckoutSeamlessConfig::getInstance();

            if (empty($financialInstitutions) || $financialInstitutionsLastModified < (time() - $config->getFinancialInstitutionsLastModifiedTimer())) {

                /** @var oxLang $oLang */
                $oLang = oxRegistry::get('oxLang');

                $financialInstitutions = array();
                try {
                    $_client = new WirecardCEE_QMore_BackendClient(Array(
                        'CUSTOMER_ID' => $config->getCustomerId(),
                        'SHOP_ID' => $config->getShopId(),
                        'LANGUAGE' => $oLang->getLanguageAbbr(),
                        'SECRET' => $config->getSecret(),
                        'PASSWORD' => $config->getPassword(),
                    ));

                    $response = $_client->getFinancialInstitutions(WirecardCEE_QMore_PaymentType::TRUSTPAY)->getResponse();

                    foreach ($response['financialInstitution'] as $institution) {
                        $financialInstitutions[$institution["id"]] = $institution["name"];
                    }

                    $this->getSession()->setVariable('qentaCheckoutSeamlessTrustPayFinancialInstitutions',
                        $financialInstitutions);
                    $this->getSession()->setVariable('qentaCheckoutSeamlessTrustPayFinancialInstitutionsLastModified',
                        time());
                } catch (Exception $e) {
                    $financialInstitutions = array();
                    $this->getSession()->deleteVariable('qentaCheckoutSeamlessTrustPayFinancialInstitutions');
                    $this->getSession()->deleteVariable('qentaCheckoutSeamlessTrustPayFinancialInstitutionsLastModified');
                }
            }

            return $financialInstitutions;
        } else {
            return Array();
        }
    }

    /**
     * check if selected payment has stored Data
     *
     * @param string $sPaymenttype
     *
     * @return string $bResponse
     */
    public function hasQentaCheckoutSeamlessPaymentData($sPaymenttype = null)
    {
        if (!$sPaymenttype) {
            return false;
        }
        if (!is_object($this->_oQentaDataStorageReadResponse)) {
            return false;
        }
        $sQentaPaymentType = qentaCheckoutSeamlessUtils::getInstance()->convertPaymenttype($sPaymenttype);

        return $this->_oQentaDataStorageReadResponse->hasPaymentInformation($sQentaPaymentType);
    }

    /**
     * check if user is older than the given age
     * @param oxUser $oUser
     * @param integer $iMinAge
     * @return boolean
     */
    public function qcsValidateCustomerAge($oUser, $iMinAge = 18)
    {
        $dob = $oUser->oxuser__oxbirthdate->value;
        if ($dob && $dob != '0000-00-00') {
            $iAgeChecker = $iMinAge--;
            $dobObject = new DateTime($dob);
            $currentYear = date('Y');
            $currentMonth = date('m');
            $currentDay = date('d');
            $ageCheckDate = ($currentYear - $iAgeChecker) . '-' . $currentMonth . '-' . $currentDay;
            $ageCheckObject = new DateTime($ageCheckDate);
            if ($ageCheckObject < $dobObject) {
                //customer is younger than given age. PaymentType not available
                return false;
            }
        }

        return true;
    }

    public function qcsValidateAddresses($oUser, $oOrder)
    {
        //if delivery Address is not set it's the same as billing
        $oDelAddress = $oOrder->getDelAddressInfo();
        if ($oDelAddress) {
            if ($oDelAddress->oxaddress__oxcompany->value != $oUser->oxuser__oxcompany->value ||
                $oDelAddress->oxaddress__oxfname->value != $oUser->oxuser__oxfname->value ||
                $oDelAddress->oxaddress__oxlname->value != $oUser->oxuser__oxlname->value ||
                $oDelAddress->oxaddress__oxstreet->value != $oUser->oxuser__oxstreet->value ||
                $oDelAddress->oxaddress__oxstreetnr->value != $oUser->oxuser__oxstreetnr->value ||
                $oDelAddress->oxaddress__oxaddinfo->value != $oUser->oxuser__oxaddinfo->value ||
                $oDelAddress->oxaddress__oxcity->value != $oUser->oxuser__oxcity->value ||
                $oDelAddress->oxaddress__oxcountry->value != $oUser->oxuser__oxcountry->value ||
                $oDelAddress->oxaddress__oxstateid->value != $oUser->oxuser__oxstateid->value ||
                $oDelAddress->oxaddress__oxzip->value != $oUser->oxuser__oxzip->value ||
                $oDelAddress->oxaddress__oxfon->value != $oUser->oxuser__oxfon->value ||
                $oDelAddress->oxaddress__oxfax->value != $oUser->oxuser__oxfax->value ||
                $oDelAddress->oxaddress__oxsal->value != $oUser->oxuser__oxsal->value
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * check if basket currency is an allowed currency
     * @param oxBasket $oBasket
     * @param Array $aAllowedCurrencies
     * @return boolean
     */
    public function qcsValidateCurrency($oBasket, $aAllowedCurrencies = Array('EUR'))
    {
        $currency = $oBasket->getBasketCurrency();
        if (!in_array($currency->name, $aAllowedCurrencies)) {
            return false;
        }

        return true;
    }


    /**
     * strips "Qcs " prefix from paymethod description
     *
     * @param String paymethod description with prefix
     * @return String paymethod description without prefix
     **/
    public static function getQcsRawPaymentDesc($paymethodNameWithPrefix)
    {
        return str_replace('QCS ', '', $paymethodNameWithPrefix);
    }

    public static function isQcsPaymethod($sPaymentId)
    {
        $sPaymenttype = qentaCheckoutSeamlessUtils::getInstance()->convertPaymenttype($sPaymentId);

        switch ($sPaymenttype) {
            case WirecardCEE_QMore_PaymentType::BMC:
            case WirecardCEE_QMore_PaymentType::CCARD:
            case WirecardCEE_QMore_PaymentType::CCARD_MOTO:
            case WirecardCEE_QMore_PaymentType::EKONTO:
            case WirecardCEE_QMore_PaymentType::EPAYBG:
            case WirecardCEE_QMore_PaymentType::EPS:
            case WirecardCEE_QMore_PaymentType::GIROPAY:
            case WirecardCEE_QMore_PaymentType::IDL:
            case WirecardCEE_QMore_PaymentType::INSTALLMENT:
            case WirecardCEE_QMore_PaymentType::INVOICE . '_B2B':
            case WirecardCEE_QMore_PaymentType::INVOICE . '_B2C':
            case WirecardCEE_QMore_PaymentType::MONETA:
            case WirecardCEE_QMore_PaymentType::P24:
            case WirecardCEE_QMore_PaymentType::PAYPAL:
            case WirecardCEE_QMore_PaymentType::PBX:
            case WirecardCEE_QMore_PaymentType::POLI:
            case WirecardCEE_QMore_PaymentType::PSC:
            case WirecardCEE_QMore_PaymentType::SEPADD:
            case WirecardCEE_QMore_PaymentType::SKRILLWALLET:
            case WirecardCEE_QMore_PaymentType::SOFORTUEBERWEISUNG:
            case WirecardCEE_QMore_PaymentType::TATRAPAY:
            case WirecardCEE_QMore_PaymentType::TRUSTLY:
            case WirecardCEE_QMore_PaymentType::TRUSTPAY:
            case WirecardCEE_QMore_PaymentType::VOUCHER:
                return true;
        }

        return false;
    }

    public function getQcsPaymentLogo($sPaymentId)
    {
        $sPaymenttype = qentaCheckoutSeamlessUtils::getInstance()->convertPaymenttype($sPaymentId);

        $conf = oxRegistry::getConfig();
        $modulePaths = $conf->getConfigParam('aModulePaths');
        $imgPath = $conf->getConfigParam('sShopURL') . '/modules/' . $modulePaths['qentacheckoutseamless'] . '/out/img/';

        switch ($sPaymenttype) {
            case WirecardCEE_QMore_PaymentType::BMC:
                return '<img src="' . $imgPath . 'bancontact_mistercash.png" />';
            case WirecardCEE_QMore_PaymentType::CCARD:
                return '<img src="' . $imgPath . 'ccard.png" />';
            case WirecardCEE_QMore_PaymentType::CCARD_MOTO:
                return '<img src="' . $imgPath . 'ccard_moto.png" />';
            case WirecardCEE_QMore_PaymentType::EKONTO:
                return '<img src="' . $imgPath . 'ekonto.png" />';
            case WirecardCEE_QMore_PaymentType::EPAYBG:
                return '<img src="' . $imgPath . 'epay_bg.png" />';
            case WirecardCEE_QMore_PaymentType::EPS:
                return '<img src="' . $imgPath . 'eps.png" />';
            case WirecardCEE_QMore_PaymentType::GIROPAY:
                return '<img src="' . $imgPath . 'giropay.png" />';
            case WirecardCEE_QMore_PaymentType::IDL:
                return '<img src="' . $imgPath . 'idl.png" />';
            case WirecardCEE_QMore_PaymentType::INSTALLMENT:
                return '<img src="' . $imgPath . 'installment.png" />';
            case WirecardCEE_QMore_PaymentType::INVOICE . '_B2B':
                return '<img src="' . $imgPath . 'invoice.png" />';
            case WirecardCEE_QMore_PaymentType::INVOICE . '_B2C':
                return '<img src="' . $imgPath . 'invoice.png" />';
            case WirecardCEE_QMore_PaymentType::MONETA:
                return '<img src="' . $imgPath . 'moneta.png" />';
            case WirecardCEE_QMore_PaymentType::PAYPAL:
                return '<img src="' . $imgPath . 'paypal.png" />';
            case WirecardCEE_QMore_PaymentType::PBX:
                return '<img src="' . $imgPath . 'pbx.png" />';
            case WirecardCEE_QMore_PaymentType::POLI:
                return '<img src="' . $imgPath . 'poli.png" />';
            case WirecardCEE_QMore_PaymentType::P24:
                return '<img src="' . $imgPath . 'przelewy24.png" />';
            case WirecardCEE_QMore_PaymentType::PSC:
                return '<img src="' . $imgPath . 'psc.png" />';
            case WirecardCEE_QMore_PaymentType::SEPADD:
                return '<img src="' . $imgPath . 'sepa-dd.png" />';
            case WirecardCEE_QMore_PaymentType::SKRILLWALLET:
                return '<img src="' . $imgPath . 'skrillwallet.png" />';
            case WirecardCEE_QMore_PaymentType::SOFORTUEBERWEISUNG:
                return '<img src="' . $imgPath . 'sofortueberweisung.png" />';
            case WirecardCEE_QMore_PaymentType::TATRAPAY:
                return '<img src="' . $imgPath . 'tatrapay.png" />';
            case WirecardCEE_QMore_PaymentType::TRUSTLY:
                return '<img src="' . $imgPath . 'trustly.png" />';
            case WirecardCEE_QMore_PaymentType::VOUCHER:
                return '<img src="' . $imgPath . 'voucher.png" />';
            case WirecardCEE_QMore_PaymentType::TRUSTPAY:
                return '<img src="' . $imgPath . 'trustpay.png" />';
            default:
                return null;
        }
    }

    public function hasQcsDobField($sPaymentId)
    {
        if (in_array($sPaymentId, array('qcs_invoice_b2c', 'qcs_installment'))) {
            return true;
        }

        return false;
    }

    public function hasQcsVatIdField($sPaymentId)
    {
        $config = qentaCheckoutSeamlessConfig::getInstance();

        if ($config->getInvoiceProvider() == 'PAYOLUTION') {
            if ($sPaymentId == 'qcs_invoice_b2b') {
                return true;
            }
        }

        return false;
    }


    function showQcsTrustedShopsCheckbox($sPaymentId)
    {
        $config = qentaCheckoutSeamlessConfig::getInstance();

        if ($config->getInvoiceProvider() == 'PAYOLUTION') {
            switch ($sPaymentId) {
                case 'qcs_invoice_b2b':
                    return $config->getInvoiceb2bTrustedShopsCheckbox();
                case 'qcs_invoice_b2c':
                    return $config->getInvoiceb2cTrustedShopsCheckbox();
                default:
                    return false;
            }
        }
    }

	function showQcsInstallmentTrustedShopsCheckbox($sPaymentId)
	{
		$config = qentaCheckoutSeamlessConfig::getInstance();

		if ($config->getInstallmentProvider() == 'PAYOLUTION') {
			return $config->getInstallmentTrustedShopsCheckbox();
		}
		return false;
	}

    function getQcsInvoicePayolutionTerms()
    {
        $oLang = oxRegistry::get('oxLang');
        $config = qentaCheckoutSeamlessConfig::getInstance();

        return sprintf($oLang->translateString('WIRECARD_CHECKOUT_SEAMLESS_PAYOLUTION_TERMS',
            $oLang->getBaseLanguage()),
            'https://payment.payolution.com/payolution-payment/infoport/dataprivacyconsent?mId=' . $config->getInvoicePayolutionMId());
    }

	function getQcsInstallmentPayolutionTerms()
	{
		$oLang = oxRegistry::get('oxLang');
		$config = qentaCheckoutSeamlessConfig::getInstance();

		return sprintf($oLang->translateString('WIRECARD_CHECKOUT_SEAMLESS_PAYOLUTION_TERMS',
			$oLang->getBaseLanguage()),
			'https://payment.payolution.com/payolution-payment/infoport/dataprivacyconsent?mId=' . $config->getInstallmentPayolutionMId());
	}

	function getQcsRatePayConsumerDeviceId()
	{
		$config = qentaCheckoutSeamlessConfig::getInstance();

		if(isset($_SESSION['qcs-consumerDeviceId'])) {
			$consumerDeviceId = $_SESSION['qcs-consumerDeviceId'];
		} else {
			$timestamp = microtime();
			$customerId = $config->getCustomerId();
			$consumerDeviceId = md5($customerId . "_" . $timestamp);
			$_SESSION['qcs-consumerDeviceId'] = $consumerDeviceId;
		}

		if($config->getInvoiceProvider() == 'RATEPAY' || $config->getInstallmentProvider() == 'RATEPAY')
        {
            $ratepay = '<script language="JavaScript">var di = {t:"'.$consumerDeviceId.'",v:"WDWL",l:"Checkout"};</script>';
            $ratepay .= '<script type="text/javascript" src="//d.ratepay.com/'.$consumerDeviceId.'/di.js"></script>';
            $ratepay .= '<noscript><link rel="stylesheet" type="text/css" href="//d.ratepay.com/di.css?t='.$consumerDeviceId.'&v=WDWL&l=Checkout"></noscript>';
            $ratepay .= '<object type="application/x-shockwave-flash" data="//d.ratepay.com/WDWL/c.swf" width="0" height="0"><param name="movie" value="//d.ratepay.com/WDWL/c.swf" /><param name="flashvars" value="t='.$consumerDeviceId.'&v=WDWL"/><param name="AllowScriptAccess" value="always"/></object>';

            return $ratepay;
        }

	}

    /**
     * @return mixed
     */
    public function getQcsPaymentError()
    {
        $qcs_payment_error = '';

        if (oxRegistry::getSession()->hasVariable('qcs_payerrortext')) {
            $qcs_payment_error = oxRegistry::getSession()->getVariable('qcs_payerrortext');
            oxRegistry::getSession()->deleteVariable('qcs_payerrortext');
            oxRegistry::getSession()->deleteVariable('sess_challenge');
            oxRegistry::getSession()->deleteVariable('qcpPaymentState');
        }

        return $qcs_payment_error;
    }

    /**
     * @return bool
     */
    public function isQcsPaymentError()
    {
        if (oxRegistry::getSession()->hasVariable('qcs_payerrortext')) {
            return true;
        }

        return false;
    }
}
