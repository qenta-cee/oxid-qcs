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

/**
 * Wirecard Checkout Seamless config class
 */
class wirecardCheckoutSeamlessConfig
{

    private static $_CUSTOMER_ID_DEMO_MODE = 'D200001';
    private static $_CUSTOMER_ID_TEST_MODE = 'D200411';
    private static $_SECRET_DEMO_MODE = 'B8AKTPWBRMNBV455FG6M2DANE99WU2';
    private static $_SECRET_TEST_MODE = 'DP4TMTPQQWFJW34647RM798E9A5X7E8ATP462Z4VGZK53YEJ3JWXS98B9P4F';
    private static $_SHOPID_DEMO_MODE = 'seamless';
    private static $_SHOPID_TEST_MODE = 'seamless3D';
    private static $_PASSWORD_DEMO_MODE = 'jcv45z';
    private static $_PASSWORD_TEST_MODE = '2g4f9q2m';
    private static $_financialInstitutionsLastModifiedTimer = '3600';
    private $_customerStatementLength = '25';

    /**
     * @var oxModule
     */
    protected $_oModule;

    /**
     * Return Wirecard Checkout Seamless module id.
     *
     * @return string
     */
    public function getModuleId()
    {
        return $this->_getModule()->getId();
    }

    public function getPluginVersion()
    {
        return $this->_getModule()->getInfo('version');
    }

    public function getCustomerId()
    {
        switch ($this->_getConfig()->getConfigParam('sPluginMode')) {
            case 'Demo':
                return self::$_CUSTOMER_ID_DEMO_MODE;
                break;
            case 'Test':
                return self::$_CUSTOMER_ID_TEST_MODE;
                break;
            case 'Live':
            default:
                return $this->_getConfig()->getConfigParam('sCustomerId');
                break;
        }
    }

    public function getShopId()
    {
        switch ($this->_getConfig()->getConfigParam('sPluginMode')) {
            case 'Demo':
                return self::$_SHOPID_DEMO_MODE;
                break;
            case 'Test':
                return self::$_SHOPID_TEST_MODE;
                break;
            case 'Live':
            default:
                return $this->_getConfig()->getConfigParam('sShopId');
                break;
        }
    }

    public function getSecret()
    {
        switch ($this->_getConfig()->getConfigParam('sPluginMode')) {
            case 'Demo':
                return self::$_SECRET_DEMO_MODE;
                break;
            case 'Test':
                return self::$_SECRET_TEST_MODE;
                break;
            case 'Live':
            default:
                return $this->_getConfig()->getConfigParam('sSecret');
                break;
        }
    }

    public function getPassword()
    {
        switch ($this->_getConfig()->getConfigParam('sPluginMode')) {
            case 'Demo':
                return self::$_PASSWORD_DEMO_MODE;
                break;
            case 'Test':
                return self::$_PASSWORD_TEST_MODE;
                break;
            case 'Live':
            default:
                return $this->_getConfig()->getConfigParam('sPassword');
                break;
        }
    }

    public function getDeleteFailedOrCanceledOrders(){
        return $this->_getConfig()->getConfigParam('sDeleteFailedOrCanceledOrders');
    }

    public function getServiceUrl()
    {
        return $this->_getConfig()->getConfigParam('sServiceUrl');
    }

    public function getAutoDeposit()
    {
        return $this->_getConfig()->getConfigParam('bAutoDeposit');
    }

    public function getConfirmMail()
    {
        return $this->_getConfig()->getConfigParam('sConfirmMail');
    }

    public function getDuplicateRequestCheck()
    {
        return ($this->_getConfig()->getConfigParam('bDuplicateRequestCheck'));
    }

    public function getUseIframe()
    {
        return $this->_getConfig()->getConfigParam('bUseIframe');
    }

    public function getLogConfirmations()
    {
        return $this->_getConfig()->getConfigParam('bLogConfirmations');
    }

    public function getMailShopOwner()
    {
        return $this->_getConfig()->getConfigParam('bMailShopOwner');
    }

    public function getDssSaqAEnable()
    {
        return $this->_getConfig()->getConfigParam('bDssSaqAEnable');
    }

    public function getIframeCssUrl()
    {
        return trim($this->_getConfig()->getConfigParam('sIframeCssUrl'));
    }

    public function getShowCreditcardCardholder()
    {
        return $this->_getConfig()->getConfigParam('bShowCreditcardCardholder');
    }

    public function getShowCreditcardCvc()
    {
        return $this->_getConfig()->getConfigParam('bShowCreditcardCvc');
    }

    public function getShowCreditcardIssueDate()
    {
        return $this->_getConfig()->getConfigParam('bShowCreditcardIssueDate');
    }

    public function getShowCreditcardIssueNumber()
    {
        return $this->_getConfig()->getConfigParam('bShowCreditcardIssueNumber');
    }

    public function getDirectDebitNoSepa()
    {
        return $this->_getConfig()->getConfigParam('bDirectDebitNoSepa');
    }

    public function getShopName()
    {
        return $this->_getConfig()->getConfigParam('sShopName');
    }

    public function getSendAdditionalBasketData()
    {
        return $this->_getConfig()->getConfigParam('bSendAdditionalBasketData');
    }

    public function getSendShippingData()
    {
        return $this->_getConfig()->getConfigParam('bSendAdditionalCustomerShipping');
    }

    public function getSendBillingData()
    {
    	return $this->_getConfig()->getConfigParam('bSendAdditionalCustomerBilling');
    }

    public function getInvoiceProvider()
    {
        return $this->_getConfig()->getConfigParam('sInvoiceProvider');
    }

	public function getInstallmentProvider()
	{
		return $this->_getConfig()->getConfigParam('sInstallmentProvider');
	}

    public function getInstallmentPayolutionMId()
    {
        return $this->_getConfig()->getConfigParam('sInstallmentPayolutionMId');
    }

	public function getInvoicePayolutionMId()
	{
		return $this->_getConfig()->getConfigParam('sInvoicePayolutionMId');
	}

    public function getInstallmentTrustedShopsCheckbox()
    {
        return $this->_getConfig()->getConfigParam('bInstallmentTrustedShopsCheckbox');
    }

    public function getInvoiceb2bTrustedShopsCheckbox()
    {
        return $this->_getConfig()->getConfigParam('bInvoiceb2bTrustedShopsCheckbox');
    }

    public function getInvoiceb2cTrustedShopsCheckbox()
    {
        return $this->_getConfig()->getConfigParam('bInvoiceb2cTrustedShopsCheckbox');
    }

    public function getRiskSuppress()
    {
        return $this->_getConfig()->getConfigParam('bRiskSuppress');
    }

    public function getRiskConfigAlias()
    {
        return $this->_getConfig()->getConfigParam('sRiskConfigAlias');
    }

    public function getInvoiceAllowDifferingAddresses()
    {
        return $this->_getConfig()->getConfigParam('bInvoiceAllowDifferingAddresses');
    }

	public function getInstallmentAllowDifferingAddresses()
	{
		return $this->_getConfig()->getConfigParam('bInstallmentAllowDifferingAddresses');
	}

    public function getFinancialInstitutionsLastModifiedTimer()
    {
        return self::$_financialInstitutionsLastModifiedTimer;
    }

    /**
     * @return oxConfig
     */
    public function getOxConfig()
    {
        return $this->_getConfig();
    }

    /**
     * Returns active shop id
     *
     * @return string
     */
    protected function _getShopId()
    {
        return $this->_getConfig()->getShopId();
    }

    /**
     * Returns customer statement max lenght
     *
     * @return string
     */
    public function getCustomerStatementLength()
    {
        return $this->_customerStatementLength;
    }

    /**
     * Returns oxConfig instance
     *
     * @return oxConfig
     */
    protected function _getConfig()
    {
        return oxRegistry::getConfig();
    }

    /**
     * @return oxModule
     */
    protected function _getModule()
    {
        if ($this->_oModule === null) {
            /** @var oxModule $module */
            $this->_oModule = oxNew('oxModule');
            $this->_oModule->load('wirecardcheckoutseamless');
        }

        return $this->_oModule;
    }


    /**
     * @return wirecardCheckoutSeamlessConfig
     */
    public static function getInstance()
    {
        if (is_object(oxRegistry::get('wirecardCheckoutSeamlessConfig'))) {
            return oxRegistry::get('wirecardCheckoutSeamlessConfig');
        }

        oxRegistry::set('wirecardCheckoutSeamlessConfig', new self());
    }
}
