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

class wirecardCheckoutSeamlessOxViewConfig extends wirecardCheckoutSeamlessOxViewConfig_parent
{
    /** @var wirecardCheckoutSeamlessConfig */
    protected $_oWirecardCheckoutSeamlesConfig = null;

    /**
     * array of country elv codes
     *
     * @var array
     */
    protected $_aElvCountries = null;

    public function getCustomerId()
    {
        return $this->_getWirecardCheckoutSeamlesConfig()->getCustomerId();
    }

    public function getShopId()
    {
        return $this->_getWirecardCheckoutSeamlesConfig()->getShopId();
    }

    public function getSecret()
    {
        return $this->_getWirecardCheckoutSeamlesConfig()->getSecret();
    }

    public function getServiceUrl()
    {
        return $this->_getWirecardCheckoutSeamlesConfig()->getServiceUrl();
    }

    public function getCustomerStatement()
    {
        return $this->_getWirecardCheckoutSeamlesConfig()->getCustomerStatement();
    }

    public function getAutoDeposit()
    {
        return $this->_getWirecardCheckoutSeamlesConfig()->getAutoDeposit();
    }

    public function getUseIframe()
    {
        return $this->_getWirecardCheckoutSeamlesConfig()->getUseIframe();
    }

    public function getLogConfirmations()
    {
        return $this->_getWirecardCheckoutSeamlesConfig()->getLogConfirmations();
    }

    public function getMailShopOwner()
    {
        return $this->_getWirecardCheckoutSeamlesConfig()->getMailShopOwner();
    }

    public function getDssSaqAEnable()
    {
        return $this->_getWirecardCheckoutSeamlesConfig()->getDssSaqAEnable();
    }

    public function getIframeCssUrl()
    {
        return $this->_getWirecardCheckoutSeamlesConfig()->getIframeCssUrl();
    }

    public function getShowCreditcardCardholder()
    {
        return $this->_getWirecardCheckoutSeamlesConfig()->getShowCreditcardCardholder();
    }

    public function getShowCreditcardCvc()
    {
        return $this->_getWirecardCheckoutSeamlesConfig()->getShowCreditcardCvc();
    }

    public function getShowCreditcardIssueDate()
    {
        return $this->_getWirecardCheckoutSeamlesConfig()->getShowCreditcardIssueDate();
    }

    public function getShowCreditcardIssueNumber()
    {
        return $this->_getWirecardCheckoutSeamlesConfig()->getShowCreditcardIssueNumber();
    }

    public function getDirectDebitNoSepa()
    {
        return $this->_getWirecardCheckoutSeamlesConfig()->getDirectDebitNoSepa();
    }

    /**
     * Returns array of years for credit cards issue date
     *
     * @return array
     */
    public function getCreditCardIssueYears()
    {
        return range(date('Y') - 10, date('Y'));
    }

    /**
     * Returns array of years for credit cards expiry
     *
     * @return array
     */
    public function getCreditCardYears()
    {
        return range(date('Y'), date('Y') + 10);
    }

    /**
     * Template variable getter.
     * Returns array of countrycodes for directdebit banks
     *
     * @return array
     */
    public function getDirectDebitCountries()
    {
        if ($this->_aElvCountries === null) {
            // passing country list
            $oCountryList = oxNew('oxcountrylist');
            $oListObject = $oCountryList->getBaseObject();
            $sViewName = $oListObject->getViewName();
            $sQ = "select " . $sViewName . ".oxisoalpha2, " . $sViewName . ".oxtitle from " . $oListObject->getViewName();
            $sQ .= " where " . $sViewName . ".oxisoalpha2 IN ('DE', 'AT', 'NL') ";
            if ($sActiveSnippet = $oListObject->getSqlActiveSnippet()) {
                $sQ .= " AND " . $sActiveSnippet;
            }
            $oCountryList->selectString($sQ);
            $this->_aElvCountries = $oCountryList->getArray();
        }

        return $this->_aElvCountries;
    }

    /**
     * Returns Wirecard Checkout Seamles config.
     *
     * @return wirecardCheckoutSeamlessConfig
     */
    protected function _getWirecardCheckoutSeamlesConfig()
    {

        if (is_null($this->_oWirecardCheckoutSeamlesConfig)) {
            $this->_oWirecardCheckoutSeamlesConfig = wirecardCheckoutSeamlessConfig::getInstance();
        }

        return $this->_oWirecardCheckoutSeamlesConfig;
    }


}
