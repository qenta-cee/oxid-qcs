<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/oxid-qcs/blob/master/LICENSE
*/

class qmoreCheckoutSeamlessOxViewConfig extends qmoreCheckoutSeamlessOxViewConfig_parent
{
    /** @var qmoreCheckoutSeamlessConfig */
    protected $_oQMoreCheckoutSeamlessConfig = null;

    /**
     * array of country elv codes
     *
     * @var array
     */
    protected $_aElvCountries = null;

    public function getCustomerId()
    {
        return $this->_getQMoreCheckoutSeamlessConfig()->getCustomerId();
    }

    public function getShopId()
    {
        return $this->_getQMoreCheckoutSeamlessConfig()->getShopId();
    }

    public function getSecret()
    {
        return $this->_getQMoreCheckoutSeamlessConfig()->getSecret();
    }

    public function getServiceUrl()
    {
        return $this->_getQMoreCheckoutSeamlessConfig()->getServiceUrl();
    }

    public function getCustomerStatement()
    {
        return $this->_getQMoreCheckoutSeamlessConfig()->getCustomerStatement();
    }

    public function getAutoDeposit()
    {
        return $this->_getQMoreCheckoutSeamlessConfig()->getAutoDeposit();
    }

    public function getUseIframe()
    {
        return $this->_getQMoreCheckoutSeamlessConfig()->getUseIframe();
    }

    public function getLogConfirmations()
    {
        return $this->_getQMoreCheckoutSeamlessConfig()->getLogConfirmations();
    }

    public function getMailShopOwner()
    {
        return $this->_getQMoreCheckoutSeamlessConfig()->getMailShopOwner();
    }

    public function getDssSaqAEnable()
    {
        return $this->_getQMoreCheckoutSeamlessConfig()->getDssSaqAEnable();
    }

    public function getIframeCssUrl()
    {
        return $this->_getQMoreCheckoutSeamlessConfig()->getIframeCssUrl();
    }

    public function getShowCreditcardCardholder()
    {
        return $this->_getQMoreCheckoutSeamlessConfig()->getShowCreditcardCardholder();
    }

    public function getShowCreditcardCvc()
    {
        return $this->_getQMoreCheckoutSeamlessConfig()->getShowCreditcardCvc();
    }

    public function getShowCreditcardIssueDate()
    {
        return $this->_getQMoreCheckoutSeamlessConfig()->getShowCreditcardIssueDate();
    }

    public function getShowCreditcardIssueNumber()
    {
        return $this->_getQMoreCheckoutSeamlessConfig()->getShowCreditcardIssueNumber();
    }

    public function getDirectDebitNoSepa()
    {
        return $this->_getQMoreCheckoutSeamlessConfig()->getDirectDebitNoSepa();
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
     * Returns QMORE Checkout Seamless config.
     *
     * @return qmoreCheckoutSeamlessConfig
     */
    protected function _getQMoreCheckoutSeamlessConfig()
    {

        if (is_null($this->_oQMoreCheckoutSeamlessConfig)) {
            $this->_oQMoreCheckoutSeamlessConfig = qmoreCheckoutSeamlessConfig::getInstance();
        }

        return $this->_oQMoreCheckoutSeamlessConfig;
    }


}
