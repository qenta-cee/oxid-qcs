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
 * Order class wrapper for Wirecard Checkout seamless
 *
 * @see order
 */
class wirecardCheckoutSeamlessOrder extends wirecardCheckoutSeamlessOrder_parent
{
    /**
     * Checks if order payment is a Wirecard payment and redirect
     *
     * @param int $iSuccess order state
     *
     * @return string
     */
    protected function _getNextStep($iSuccess)
    {
        $sPaymentID = $this->getSession()->getVariable("paymentid");

        $isWirecard = wirecardCheckoutSeamlessUtils::getInstance()->isOwnPayment($sPaymentID);

        if ($isWirecard && is_numeric($iSuccess) && ($iSuccess == oxOrder::ORDER_STATE_OK || $iSuccess == oxOrder::ORDER_STATE_ORDEREXISTS)) {

            /** @var oxUtils $utils */
            $utils = oxRegistry::get('oxUtils');

            $oOrder = $this->_getOrder();

            /** @var wirecardCheckoutSeamlessOrderDbGateway $oDbOrder */
            $oDbOrder = oxNew('wirecardCheckoutSeamlessOrderDbGateway');
            $aOrderData = Array(
                'BASKET' => serialize(oxRegistry::getSession()->getBasket()),
                'OXORDERID' => $oOrder->getId()
            );
            $oDbOrder->insert($aOrderData);

            $sWirecardPaymentType = wirecardCheckoutSeamlessUtils::getInstance()->convertPaymenttype($sPaymentID);

            $config = wirecardCheckoutSeamlessConfig::getInstance();

            $redirectErrorUrl = $config->getOxConfig()->getShopSecureHomeUrl() . 'cl=payment';

            try {
                $frontend = wirecardCheckoutSeamlessFrontend::getInstance();
                $frontend->setConsumerData($oOrder, $sWirecardPaymentType);
                $frontend->setOrderData($oOrder, $sWirecardPaymentType);
                $frontend->setBasket($oOrder, $sWirecardPaymentType);

                $aValues = oxRegistry::getSession()->getVariable('wirecardCheckoutSeamlessValues');
                if (isset($aValues['financialInstitution'])) {
                    $frontend->setFinancialInstitution($aValues['financialInstitution']);
                }

                $oResponse = $frontend->initiate();

                if ($oResponse->hasFailed()) {
                    $aFormattedErrors = Array();
                    foreach ($oResponse->getErrors() AS $error) {
                        $aFormattedErrors[] = $error->getConsumerMessage();
                    }

                    if($config->getDeleteFailedOrCanceledOrders()) {
                        $oOrder->delete();
                    }
                    else {
                        $oOrder->cancelOrder();
                        $oOrder->oxorder__oxtransstatus = new oxField('FAILED');
                        $oOrder->save();
                    }

                    wirecardCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':ERROR:' . print_r($aFormattedErrors, true));
                    return parent::_getNextStep(implode("<br/>\n", $aFormattedErrors));
                }

                if ($config->getUseIframe()) {
                    $sStoken = oxRegistry::getSession()->getSessionChallengeToken();
                    $sHomeUrl = oxRegistry::getSession()->processUrl($config->getOxConfig()->getShopSecureHomeUrl());
                    oxRegistry::getSession()->setVariable('wirecardCheckoutIframeUrl', $oResponse->getRedirectUrl());
                    $utils->redirect($sHomeUrl . 'cl=order&fnc=wirecardCheckoutIframe&stoken=' . $sStoken);
                } else {
                    $utils->redirect($oResponse->getRedirectUrl());
                }

            } catch (Exception $e) {
                oxRegistry::getSession()->setVariable('payerror', -1);
                oxRegistry::getSession()->setVariable('payerrortext', $e->getMessage());
                wirecardCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':ERROR:' . $e->getMessage());
                $utils->redirect($redirectErrorUrl);
            }

        } else {
            return parent::_getNextStep($iSuccess);
        }
    }

    public function wirecardPending()
    {
        $this->wirecardIframeBreakout();

        return parent::_getNextStep(oxOrder::ORDER_STATE_OK);
    }

    public function wirecardSuccess()
    {
        $this->wirecardIframeBreakout();

        return parent::_getNextStep(oxOrder::ORDER_STATE_OK);
    }

    public function wirecardCancel()
    {
        $this->wirecardIframeBreakout();
        $consumerMessage = oxRegistry::getSession()->getVariable('wirecardCheckoutSeamlessConsumerMessage');
        oxRegistry::getSession()->setVariable('wcs_payerrortext', $consumerMessage);

        return parent::_getNextStep(oxOrder::ORDER_STATE_PAYMENTERROR);
    }

    public function wirecardFailure()
    {
        $this->wirecardIframeBreakout();

        $consumerMessage = oxRegistry::getSession()->getVariable('wirecardCheckoutSeamlessConsumerMessage');
        oxRegistry::getSession()->setVariable('wcs_payerrortext', $consumerMessage);

        return parent::_getNextStep(oxOrder::ORDER_STATE_PAYMENTERROR);
    }

    public function wirecardIframeBreakout()
    {
        /** @var oxUtilsUrl $urlUtils */
        $urlUtils = oxRegistry::get('oxUtilsUrl');
        $sRedirectUrl = $urlUtils->getCurrentUrl();

        $redirected = (string)oxRegistry::getConfig()->getRequestParameter('iframebreakout');
        if (!$redirected && wirecardCheckoutSeamlessConfig::getInstance()->getUseIframe()) {
            $sRedirectUrl .= '&iframebreakout=1';
            $sRedirectUrl = json_encode($sRedirectUrl);
            /** @var oxUtils $utils */
            $utils = oxRegistry::get('oxUtils');
            $utils->showMessageAndExit(<<<EOT
<!DOCTYPE>
<html>
    <head>
        <script type="text/javascript">
            function iframeBreakout(redirectUrl)
            {
                top.location.href = redirectUrl;
            }
        </script>
    </head>
    <body onload='iframeBreakout($sRedirectUrl);'>
    </body>
</html>
EOT
            );
        }
    }

    public function wirecardCheckoutIframe()
    {
        $this->addGlobalParams();

        $this->_aViewData['wirecardCheckoutIframeUrl'] = oxRegistry::getSession()->getVariable('wirecardCheckoutIframeUrl');

        $this->_sThisTemplate = 'wirecardcheckoutseamlessiframecheckout.tpl';
    }

    public function wirecardConfirm()
    {
        wirecardCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':' . print_r($_POST, true));

        $config = wirecardCheckoutSeamlessConfig::getInstance();

        $out = WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString();

        if (!isset($_POST['oxid_orderid'])) {
            print WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString('Order Id is missing');

            return;
        }

        $sOXID = $_POST['oxid_orderid'];
        /** @var oxOrder $oOrder */
        $oOrder = $this->_getOrderById($sOXID);
        if ($oOrder === null) {
            print WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString('Order not found.');

            return;
        }

        if(in_array($oOrder->oxorder__oxtransstatus, array('PAID'))) {
            wirecardCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':ORDER: can\'t update order state, since it is already in a final state: ' . $oOrder->oxorder__oxtransstatus);
            print WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString('Can\'t update order state, since it is already in a final state.');

            return;
        }

        /** @var wirecardCheckoutSeamlessOrderDbGateway $oDbOrder */
        $oDbOrder = oxNew('wirecardCheckoutSeamlessOrderDbGateway');
        $aOrderData = $oDbOrder->loadByOrderId($sOXID);
        if (!count($aOrderData)) {
            print WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString('Wirecard Order not found.');

            return;
        }

        try {
            /** @var $return WirecardCEE_Stdlib_Return_ReturnAbstract */
            $return = WirecardCEE_QMore_ReturnFactory::getInstance($_POST, wirecardCheckoutSeamlessConfig::getInstance()->getSecret());
            if (!$return->validate()) {
                wirecardCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':Validation error: invalid response');
                print WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString('Validation error: invalid response');
                return;
            }

            switch ($return->getPaymentState()) {
                case WirecardCEE_QMore_ReturnFactory::STATE_SUCCESS:
                    /** @var $return WirecardCEE_QMore_Return_Success */
                    wirecardCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':SUCCESS:' . $return->getOrderNumber() . ':' . $return->getGatewayReferenceNumber());
                    $oOrder->oxorder__oxtransstatus = new oxField('PAID');
                    $oOrder->oxorder__oxpaid = new oxField(date('Y-m-d H:i:s'));
                    $oOrder->oxorder__oxtransid = new oxField($return->getOrderNumber());
                    $oOrder->oxorder__oxpayid = new oxField($return->getGatewayReferenceNumber());
                    $oOrder->oxorder__oxxid = new oxField($return->getGatewayContractNumber());
                    $oOrder->save();

                    //create info data
                    $prefix = 'WIRECARD_CHECKOUT_SEAMLESS_';
                    $returned = $return->getReturned();

                    unset($returned['paymentType']);
                    unset($returned['paymentTypeShop']);
                    unset($returned['oxid_orderid']);

                    foreach ($returned as $k => $v) {
                        $aInfo[$prefix . $k] = mysql_real_escape_string($v);
                    }

                    $oOxUserPayment = oxNew("oxUserPayment");
                    $oOxUserPayment->load($oOrder->oxorder__oxpaymentid->value);
                    $oOxUserPayment->oxuserpayments__oxvalue = new oxField(oxRegistry::getUtils()->assignValuesToText($aInfo), oxField::T_RAW);
                    $oOxUserPayment->setDynValues($aInfo);
                    $oOxUserPayment->save();

                    /** @var wirecardCheckoutSeamlessOxOrder $oOrder */
                    // cast oxBasket to wirecardCheckoutSeamlessOxBasket
                    $sClass = "wirecardCheckoutSeamlessBasket";
                    $oBasket = unserialize(preg_replace('/^O:\d+:"[^"]++"/', 'O:' . strlen($sClass) . ':"' . $sClass . '"', $aOrderData['BASKET']));
                    $oOrder->sendWirecardCheckoutSeamlessOrderByEmail($oBasket, $oOxUserPayment);
                    $oDbOrder->delete($aOrderData['OXID']);
                    break;

                case WirecardCEE_QMore_ReturnFactory::STATE_PENDING:
                    $sendEmail = !in_array($oOrder->oxorder__oxtransstatus, array('PENDING'));

                    /** @var $return WirecardCEE_QMore_Return_Pending */
                    wirecardCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':PENDING');
                    $oOrder->oxorder__oxtransstatus = new oxField('PENDING');
                    $oOrder->oxorder__oxtransid = new oxField($return->getOrderNumber());
                    $oOrder->save();

                    $oOxUserPayment = oxNew("oxUserPayment");
                    $oOxUserPayment->load($oOrder->oxorder__oxpaymentid->value);

                    /** @var wirecardCheckoutSeamlessOxOrder $oOrder */
                    // cast oxBasket to wirecardCheckoutSeamlessOxBasket
                    $sClass = "wirecardCheckoutSeamlessBasket";
                    $oBasket = unserialize(preg_replace('/^O:\d+:"[^"]++"/', 'O:' . strlen($sClass) . ':"' . $sClass . '"', $aOrderData['BASKET']));

                    if($sendEmail) {
                        $oOrder->sendWirecardCheckoutSeamlessOrderByEmail($oBasket, $oOxUserPayment);
                    }

                    break;

                case WirecardCEE_QMore_ReturnFactory::STATE_CANCEL:
                    /** @var $return WirecardCEE_QMore_Return_Cancel */
                    wirecardCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':CANCEL');

                    $oDbOrder->delete($aOrderData['OXID']);

                    if($config->getDeleteFailedOrCanceledOrders()) {
                        $oOrder->delete();
                    }
                    else {
                        $oOrder->cancelOrder();
                        $oOrder->oxorder__oxtransstatus = new oxField('CANCELED');
                        $oOrder->save();
                    }
                    break;

                case WirecardCEE_QMore_ReturnFactory::STATE_FAILURE:
                    /** @var $return WirecardCEE_QMore_Return_Failure */
                    wirecardCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':FAILURE:' . print_r($return->getErrors(),
                            true));

                    $oDbOrder->delete($aOrderData['OXID']);
                    if($config->getDeleteFailedOrCanceledOrders()) {
                        $oOrder->delete();
                    }
                    else {
                        $oOrder->cancelOrder();
                        $oOrder->oxorder__oxtransstatus = new oxField('CANCELED');
                        $oOrder->save();
                    }

                    $consumerMessage = '';
                    /** var $e WirecardCEE_QMore_Error */
                    foreach ($return->getErrors() as $e) {
                        $consumerMessage .= ' ' . $e->getConsumerMessage();
                    }
                    oxRegistry::getSession()->setVariable('wirecardCheckoutSeamlessConsumerMessage', $consumerMessage);
                    break;

                default:
                    break;
            }
        } catch (Exception $e) {
            wirecardCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':EXCEPTION:' . $e->getMessage() . $e->getTraceAsString());
            $out = WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString($e->getMessage());
        }

        wirecardCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':' . print_r($out, true));
        print $out;
        die;
    }

    /**
     * Returns current order object
     *
     * @return oxOrder
     */
    protected function _getOrder()
    {
        /** @var oxOrder $oOrder */
        $oOrder = oxNew("oxOrder");
        $bSuccess = $oOrder->load(oxRegistry::getSession()->getVariable('sess_challenge'));

        return $bSuccess ? $oOrder : null;
    }

    /**
     * @param $sOXID
     *
     * @return null|wirecardCheckoutSeamlessOxOrder
     */
    protected function _getOrderById($sOXID)
    {
        /** @var oxOrder $oOrder */
        $oOrder = oxNew("wirecardCheckoutSeamlessOxOrder");
        $bSuccess = $oOrder->load($sOXID);

        return $bSuccess ? $oOrder : null;
    }

    public function isWcsPaymethod($sPaymentID)
    {
        return wirecardCheckoutSeamlessPayment::isWcsPaymethod($sPaymentID);
    }

    public function getWcsRawPaymentDesc($paymethodNameWithPrefix)
    {
        return wirecardCheckoutSeamlessPayment::getWcsRawPaymentDesc($paymethodNameWithPrefix);
    }
}
