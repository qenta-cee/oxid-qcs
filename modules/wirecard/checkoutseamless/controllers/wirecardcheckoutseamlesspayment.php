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


/**
 * Payment class wrapper for PayPal module
 *
 * @see oxPayment
 */
class wirecardCheckoutSeamlessPayment extends wirecardCheckoutSeamlessPayment_parent
{
    /**
     * @var wirecardCheckoutSeamlessDataStorage
     */
    protected $_oWirecardDataStorage;


    /**
     * @var WirecardCEE_QMore_DataStorage_Response_Read
     */
    protected $_oWirecardDataStorageReadResponse;

    /**
     * url to wirecard JS Library for cross domain request.
     * Should be returned by WirecardCEE_Client_DataStorage_Request_Initiation
     *
     * @var string
     */
    protected $_sWirecardDataStorageJsUrl = null;

    public function render()
    {
        $sReturn = parent::render();

        $this->_initWirecardDatastorage();

        return $sReturn;
    }

    public function validatePayment()
    {
        $parentResult = parent::validatePayment();

        $aValues = Array();
        $sPaymentId = (string )oxRegistry::getConfig()->getRequestParameter('paymentid');
        $sPaymenttype = wirecardCheckoutSeamlessUtils::getInstance()->convertPaymenttype($sPaymentId);
        $config = wirecardCheckoutSeamlessConfig::getInstance();
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
                if ($config->getInvoiceInstallmentProvider() == 'PAYOLUTION') {
                    $vatId = $oUser->oxuser__oxustid->value;
                    if ($this->hasWcsVatIdField($sPaymentId) && empty($vatId)) {
                        $sVatId = oxRegistry::getConfig()->getRequestParameter('sVatId');

                        if (!empty($sVatId)) {
                            $oUser->oxuser__oxustid = new oxField($sVatId, oxField::T_RAW);
                            $oUser->save();
                        }
                    }

                    if ($this->showWcsTrustedShopsCheckbox($sPaymentId)) {
                        if (!oxRegistry::getConfig()->getRequestParameter('payolutionTerms')) {
                            oxRegistry::getSession()->setVariable('wcs_payerrortext',
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
                if ($config->getInvoiceInstallmentProvider() == 'PAYOLUTION') {
                    if ($this->hasWcsDobField($sPaymentId) && $oUser->oxuser__oxbirthdate == '0000-00-00') {
                        $iBirthdayYear = oxRegistry::getConfig()->getRequestParameter($sPaymentId . '_iBirthdayYear');
                        $iBirthdayDay = oxRegistry::getConfig()->getRequestParameter($sPaymentId . '_iBirthdayDay');
                        $iBirthdayMonth = oxRegistry::getConfig()->getRequestParameter($sPaymentId . '_iBirthdayMonth');

                        if (empty($iBirthdayYear) || empty($iBirthdayDay) || empty($iBirthdayMonth)) {
                            oxRegistry::getSession()->setVariable('wcs_payerrortext',
                                $oLang->translateString('WIRECARD_CHECKOUT_SEAMLESS_PLEASE_FILL_IN_DOB',
                                    $oLang->getBaseLanguage()));

                            return;
                        }

                        $dateData = array('day' => $iBirthdayDay, 'month' => $iBirthdayMonth, 'year' => $iBirthdayYear);
                        $aValues['dobData'] = $dateData;
                        oxRegistry::getSession()->setVariable('wcs_dobData', $dateData);

                        if (is_array($dateData)) {
                            $oUser->oxuser__oxbirthdate = new oxField($oUser->convertBirthday($dateData),
                                oxField::T_RAW);
                            $oUser->save();
                        }
                    }

                    //validate paymethod
                    if (!$this->wcsValidateCustomerAge($oUser, 18)) {
                        oxRegistry::getSession()->setVariable('wcs_payerrortext',
                            sprintf($oLang->translateString('WIRECARD_CHECKOUT_SEAMLESS_DOB_TOO_YOUNG',
                                $oLang->getBaseLanguage()), 18));

                        return;
                    }

                    if ($this->showWcsTrustedShopsCheckbox($sPaymentId)) {
                        if (!oxRegistry::getConfig()->getRequestParameter('payolutionTerms')) {
                            oxRegistry::getSession()->setVariable('wcs_payerrortext',
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

        oxRegistry::getSession()->setVariable('wirecardCheckoutSeamlessValues', $aValues);

        return $parentResult;
    }

    protected function _initWirecardDatastorage()
    {
        $this->_oWirecardDataStorage = wirecardCheckoutSeamlessDataStorage::getInstance();

        try {
            $oResponse = $this->_oWirecardDataStorage->initiate();
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
                $this->_aViewData['wirecardcheckoutseamless_errors'] = $sErrorMessages;
                wirecardCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':ERROR:' . $sErrorMessages);
            } else {
                $this->_sWirecardDataStorageJsUrl = $oResponse->getJavascriptUrl();
                $this->_oWirecardDataStorage->setStorageId($oResponse->getStorageId());

                $this->_oWirecardDataStorageReadResponse = $this->_oWirecardDataStorage->read();
            }
        } catch (Exception $e) {

            oxRegistry::getSession()->setVariable('payerror', -1);
            oxRegistry::getSession()->setVariable('payerrortext', $e->getMessage());

            return;
        }

    }

    public function getWirecardStorageJsUrl()
    {
        return $this->_sWirecardDataStorageJsUrl;
    }

    public function getWirecardDataStorageReadResponse()
    {
        return $this->_oWirecardDataStorageReadResponse;
    }

    /**
     * get stored paymentData for selected payment
     *
     * @param string $sPaymenttype
     *
     * @return array $aResponse
     */
    protected function getWirecardPaymentData($sPaymenttype = null)
    {
        $aResponse = array();
        if (!$sPaymenttype) {
            return $aResponse;
        } else {
            if ($sPaymenttype == 'wcs_ccard-moto') {
                //CCARD-MOTO is stored in the same store as CCARD, so we have to use sPaymenttype CCARD for reading here
                $sPaymenttype = 'wcs_ccard';
            }
        }
        if (!is_object($this->_oWirecardDataStorageReadResponse)) {
            return $aResponse;
        }

        $sWirecardPaymentType = wirecardCheckoutSeamlessUtils::getInstance()->convertPaymenttype($sPaymenttype);
        if ($this->_oWirecardDataStorageReadResponse->hasPaymentInformation($sWirecardPaymentType)) {
            $aResponse = $this->_oWirecardDataStorageReadResponse->getPaymentInformation($sWirecardPaymentType);
        }

        return $aResponse;
    }

    public function getWirecardCheckoutSeamlessPaymentData($sPaymenttype)
    {
        $aResponse = array();

        $aPaymentInformation = $this->getWirecardPaymentData($sPaymenttype);

        if (is_array($aPaymentInformation) && !empty($aPaymentInformation)) {

            switch ($sPaymenttype) {
                case 'wcs_ccard':
                case 'wcs_ccard-moto':
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

                case 'wcs_sepa-dd':
                    $aResponse['sepa_accountOwner'] = (string)$aPaymentInformation['accountOwner'];
                    $aResponse['sepa_bankBic'] = (string)$aPaymentInformation['bankBic'];
                    $aResponse['sepa_bankAccountIban'] = (string)$aPaymentInformation['bankAccountIban'];
                    break;

                case 'wcs_giropay':
                    $aResponse['giropay_banknumber'] = (string)$aPaymentInformation['bankNumber'];
                    $aResponse['giropay_bankaccount'] = (string)$aPaymentInformation['bankAccount'];
                    $aResponse['giropay_accountowner'] = (string)$aPaymentInformation['accountOwner'];
                    break;

                case 'wcs_pbx':
                    $aResponse['paybox_payerPayboxNumber'] = (string)$aPaymentInformation['payerPayboxNumber'];
                    break;

                case 'wcs_voucher':
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
    public function getWirecardCheckoutSeamlessFinancialInstitutions($sPaymentID)
    {
        $sPaymentType = wirecardCheckoutSeamlessUtils::getInstance()->convertPaymenttype($sPaymentID);

        if (WirecardCEE_QMore_PaymentType::hasFinancialInstitutions($sPaymentType)) {
            return WirecardCEE_QMore_PaymentType::getFinancialInstitutions($sPaymentType);
        } elseif ($sPaymentType == WirecardCEE_QMore_PaymentType::TRUSTPAY) {

            $financialInstitutions = $this->getSession()->getVariable('wirecardCheckoutSeamlessTrustPayFinancialInstitutions');
            $financialInstitutionsLastModified = $this->getSession()->getVariable('wirecardCheckoutSeamlessTrustPayFinancialInstitutionsLastModified');

            /** @var wirecardCheckoutSeamlessConfig $config */
            $config = wirecardCheckoutSeamlessConfig::getInstance();

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

                    $this->getSession()->setVariable('wirecardCheckoutSeamlessTrustPayFinancialInstitutions',
                        $financialInstitutions);
                    $this->getSession()->setVariable('wirecardCheckoutSeamlessTrustPayFinancialInstitutionsLastModified',
                        time());
                } catch (Exception $e) {
                    $financialInstitutions = array();
                    $this->getSession()->deleteVariable('wirecardCheckoutSeamlessTrustPayFinancialInstitutions');
                    $this->getSession()->deleteVariable('wirecardCheckoutSeamlessTrustPayFinancialInstitutionsLastModified');
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
    public function hasWirecardCheckoutSeamlessPaymentData($sPaymenttype = null)
    {
        if (!$sPaymenttype) {
            return false;
        }
        if (!is_object($this->_oWirecardDataStorageReadResponse)) {
            return false;
        }
        $sWirecardPaymentType = wirecardCheckoutSeamlessUtils::getInstance()->convertPaymenttype($sPaymenttype);

        return $this->_oWirecardDataStorageReadResponse->hasPaymentInformation($sWirecardPaymentType);
    }

    /**
     * check if user is older than the given age
     * @param oxUser $oUser
     * @param integer $iMinAge
     * @return boolean
     */
    public function wcsValidateCustomerAge($oUser, $iMinAge = 18)
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

    public function wcsValidateAddresses($oUser, $oOrder)
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
    public function wcsValidateCurrency($oBasket, $aAllowedCurrencies = Array('EUR'))
    {
        $currency = $oBasket->getBasketCurrency();
        if (!in_array($currency->name, $aAllowedCurrencies)) {
            return false;
        }

        return true;
    }


    /**
     * strips "WCS " prefix from paymethod description
     *
     * @param String paymethod description with prefix
     * @return String paymethod description without prefix
     **/
    public static function getWcsRawPaymentDesc($paymethodNameWithPrefix)
    {
        return str_replace('WCS ', '', $paymethodNameWithPrefix);
    }

    public static function isWcsPaymethod($sPaymentId)
    {
        $sPaymenttype = wirecardCheckoutSeamlessUtils::getInstance()->convertPaymenttype($sPaymentId);

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
            case WirecardCEE_QMore_PaymentType::MPASS:
            case WirecardCEE_QMore_PaymentType::P24:
            case WirecardCEE_QMore_PaymentType::PAYPAL:
            case WirecardCEE_QMore_PaymentType::PBX:
            case WirecardCEE_QMore_PaymentType::POLI:
            case WirecardCEE_QMore_PaymentType::PSC:
            case WirecardCEE_QMore_PaymentType::QUICK:
            case WirecardCEE_QMore_PaymentType::SEPADD:
            case WirecardCEE_QMore_PaymentType::SKRILLDIRECT:
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

    public function getWcsPaymentLogo($sPaymentId)
    {
        $sPaymenttype = wirecardCheckoutSeamlessUtils::getInstance()->convertPaymenttype($sPaymentId);

        $conf = oxRegistry::getConfig();
        $modulePaths = $conf->getConfigParam('aModulePaths');
        $imgPath = $conf->getConfigParam('sShopURL') . '/modules/' . $modulePaths['wirecardcheckoutseamless'] . '/out/img/';

        switch ($sPaymenttype) {
            case WirecardCEE_QMore_PaymentType::BMC:
                return '<img src="' . $imgPath . 'bancontact_mistercash.jpg" />';
            case WirecardCEE_QMore_PaymentType::CCARD:
                return '<img src="' . $imgPath . 'ccard.jpg" />';
            case WirecardCEE_QMore_PaymentType::CCARD_MOTO:
                return '<img src="' . $imgPath . 'ccard_moto.jpg" />';
            case WirecardCEE_QMore_PaymentType::EKONTO:
                return '<img src="' . $imgPath . 'ekonto.jpg" />';
            case WirecardCEE_QMore_PaymentType::EPAYBG:
                return '<img src="' . $imgPath . 'epay_bg.jpg" />';
            case WirecardCEE_QMore_PaymentType::EPS:
                return '<img src="' . $imgPath . 'eps.jpg" />';
            case WirecardCEE_QMore_PaymentType::GIROPAY:
                return '<img src="' . $imgPath . 'giropay.jpg" />';
            case WirecardCEE_QMore_PaymentType::IDL:
                return '<img src="' . $imgPath . 'idl.jpg" />';
            case WirecardCEE_QMore_PaymentType::INSTALLMENT:
                return '<img src="' . $imgPath . 'installment.jpg" />';
            case WirecardCEE_QMore_PaymentType::INVOICE . '_B2B':
                return '<img src="' . $imgPath . 'invoice.jpg" />';
            case WirecardCEE_QMore_PaymentType::INVOICE . '_B2C':
                return '<img src="' . $imgPath . 'invoice.jpg" />';
            case WirecardCEE_QMore_PaymentType::MONETA:
                return '<img src="' . $imgPath . 'moneta.jpg" />';
            case WirecardCEE_QMore_PaymentType::MPASS:
                return '<img src="' . $imgPath . 'mpass.jpg" />';
            case WirecardCEE_QMore_PaymentType::PAYPAL:
                return '<img src="' . $imgPath . 'paypal.jpg" />';
            case WirecardCEE_QMore_PaymentType::PBX:
                return '<img src="' . $imgPath . 'pbx.jpg" />';
            case WirecardCEE_QMore_PaymentType::POLI:
                return '<img src="' . $imgPath . 'poli.jpg" />';
            case WirecardCEE_QMore_PaymentType::P24:
                return '<img src="' . $imgPath . 'przelewy24.jpg" />';
            case WirecardCEE_QMore_PaymentType::PSC:
                return '<img src="' . $imgPath . 'psc.jpg" />';
            case WirecardCEE_QMore_PaymentType::QUICK:
                return '<img src="' . $imgPath . 'quick.jpg" />';
            case WirecardCEE_QMore_PaymentType::SEPADD:
                return '<img src="' . $imgPath . 'sepa-dd.jpg" />';
            case WirecardCEE_QMore_PaymentType::SKRILLDIRECT:
                return '<img src="' . $imgPath . 'skrilldirect.jpg" />';
            case WirecardCEE_QMore_PaymentType::SKRILLWALLET:
                return '<img src="' . $imgPath . 'skrillwallet.jpg" />';
            case WirecardCEE_QMore_PaymentType::SOFORTUEBERWEISUNG:
                return '<img src="' . $imgPath . 'sofortueberweisung.jpg" />';
            case WirecardCEE_QMore_PaymentType::TATRAPAY:
                return '<img src="' . $imgPath . 'tatrapay.jpg" />';
            case WirecardCEE_QMore_PaymentType::TRUSTLY:
                return '<img src="' . $imgPath . 'trustly.jpg" />';
            case WirecardCEE_QMore_PaymentType::VOUCHER:
                return '<img src="' . $imgPath . 'voucher.jpg" />';
            case WirecardCEE_QMore_PaymentType::TRUSTPAY:
                return '<img src="' . $imgPath . 'trustpay.jpg" />';
            default:
                return null;
        }
    }

    public function hasWcsDobField($sPaymentId)
    {
        if (in_array($sPaymentId, array('wcs_invoice_b2c', 'wcs_installment'))) {
            return true;
        }

        return false;
    }

    public function hasWcsVatIdField($sPaymentId)
    {
        $config = wirecardCheckoutSeamlessConfig::getInstance();

        if ($config->getInvoiceInstallmentProvider() == 'PAYOLUTION') {
            if ($sPaymentId == 'wcs_invoice_b2b') {
                return true;
            }
        }

        return false;
    }


    function showWcsTrustedShopsCheckbox($sPaymentId)
    {
        $config = wirecardCheckoutSeamlessConfig::getInstance();

        if ($config->getInvoiceInstallmentProvider() == 'PAYOLUTION') {
            switch ($sPaymentId) {
                case 'wcs_installment':
                    return $config->getInstallmentTrustedShopsCheckbox();
                case 'wcs_invoice_b2b':
                    return $config->getInvoiceb2bTrustedShopsCheckbox();
                case 'wcs_invoice_b2c':
                    return $config->getInvoiceb2cTrustedShopsCheckbox();
                default:
                    return false;
            }
        }
    }

    function getWcsPayolutionTerms()
    {
        $oLang = oxRegistry::get('oxLang');
        $config = wirecardCheckoutSeamlessConfig::getInstance();

        return sprintf($oLang->translateString('WIRECARD_CHECKOUT_SEAMLESS_PAYOLUTION_TERMS',
            $oLang->getBaseLanguage()),
            'https://payment.payolution.com/payolution-payment/infoport/dataprivacyconsent?mId=' . $config->getPayolutionMId());
    }

    /**
     * @return mixed
     */
    public function getWcsPaymentError()
    {
        $wcs_payment_error = '';

        if (oxRegistry::getSession()->hasVariable('wcs_payerrortext')) {
            $wcs_payment_error = oxRegistry::getSession()->getVariable('wcs_payerrortext');
            oxRegistry::getSession()->deleteVariable('wcs_payerrortext');
        }

        return $wcs_payment_error;
    }

    /**
     * @return bool
     */
    public function isWcsPaymentError()
    {
        if (oxRegistry::getSession()->hasVariable('wcs_payerrortext')) {
            return true;
        }

        return false;
    }
}
