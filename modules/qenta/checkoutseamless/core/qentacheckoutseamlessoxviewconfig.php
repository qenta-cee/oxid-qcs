<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/oxid-qcs/blob/master/LICENSE
*/


class qentaCheckoutSeamlessOxViewConfig extends qentaCheckoutSeamlessOxViewConfig_parent
{
    /** @var qentaCheckoutSeamlessConfig */
    protected $_oQentaCheckoutSeamlesConfig = null;

    /**
     * array of country elv codes
     *
     * @var array
     */
    protected $_aElvCountries = null;

    public function getCustomerId()
    {
        return $this->_getQentaCheckoutSeamlesConfig()->getCustomerId();
    }

    public function getShopId()
    {
        return $this->_getQentaCheckoutSeamlesConfig()->getShopId();
    }

    public function getSecret()
    {
        return $this->_getQentaCheckoutSeamlesConfig()->getSecret();
    }

    public function getServiceUrl()
    {
        return $this->_getQentaCheckoutSeamlesConfig()->getServiceUrl();
    }

    public function getCustomerStatement()
    {
        return $this->_getQentaCheckoutSeamlesConfig()->getCustomerStatement();
    }

    public function getAutoDeposit()
    {
        return $this->_getQentaCheckoutSeamlesConfig()->getAutoDeposit();
    }

    public function getUseIframe()
    {
        return $this->_getQentaCheckoutSeamlesConfig()->getUseIframe();
    }

    public function getLogConfirmations()
    {
        return $this->_getQentaCheckoutSeamlesConfig()->getLogConfirmations();
    }

    public function getMailShopOwner()
    {
        return $this->_getQentaCheckoutSeamlesConfig()->getMailShopOwner();
    }

    public function getDssSaqAEnable()
    {
        return $this->_getQentaCheckoutSeamlesConfig()->getDssSaqAEnable();
    }

    public function getIframeCssUrl()
    {
        return $this->_getQentaCheckoutSeamlesConfig()->getIframeCssUrl();
    }

    public function getShowCreditcardCardholder()
    {
        return $this->_getQentaCheckoutSeamlesConfig()->getShowCreditcardCardholder();
    }

    public function getShowCreditcardCvc()
    {
        return $this->_getQentaCheckoutSeamlesConfig()->getShowCreditcardCvc();
    }

    public function getShowCreditcardIssueDate()
    {
        return $this->_getQentaCheckoutSeamlesConfig()->getShowCreditcardIssueDate();
    }

    public function getShowCreditcardIssueNumber()
    {
        return $this->_getQentaCheckoutSeamlesConfig()->getShowCreditcardIssueNumber();
    }

    public function getDirectDebitNoSepa()
    {
        return $this->_getQentaCheckoutSeamlesConfig()->getDirectDebitNoSepa();
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
     * @return qentaCheckoutSeamlessConfig
     */
    protected function _getQentaCheckoutSeamlesConfig()
    {

        if (is_null($this->_oQentaCheckoutSeamlesConfig)) {
            $this->_oQentaCheckoutSeamlesConfig = qentaCheckoutSeamlessConfig::getInstance();
        }

        return $this->_oQentaCheckoutSeamlesConfig;
    }


}
