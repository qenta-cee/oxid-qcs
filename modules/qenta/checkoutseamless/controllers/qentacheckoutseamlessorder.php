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
 * Order class wrapper for Wirecard Checkout seamless
 *
 * @see order
 */
class qentaCheckoutSeamlessOrder extends qentaCheckoutSeamlessOrder_parent
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

        $isQenta = qentaCheckoutSeamlessUtils::getInstance()->isOwnPayment($sPaymentID);

        if ($isQenta && is_numeric($iSuccess) && ($iSuccess == oxOrder::ORDER_STATE_OK || $iSuccess == oxOrder::ORDER_STATE_ORDEREXISTS)) {

            /** @var oxUtils $utils */
            $utils = oxRegistry::get('oxUtils');

            $oOrder = $this->_getOrder();

            /** @var qentaCheckoutSeamlessOrderDbGateway $oDbOrder */
            $oDbOrder = oxNew('qentaCheckoutSeamlessOrderDbGateway');
            $aOrderData = Array(
                'BASKET' => serialize(oxRegistry::getSession()->getBasket()),
                'OXORDERID' => $oOrder->getId()
            );
            $oDbOrder->insert($aOrderData);

            $sQentaPaymentType = qentaCheckoutSeamlessUtils::getInstance()->convertPaymenttype($sPaymentID);

            $config = qentaCheckoutSeamlessConfig::getInstance();

            $redirectErrorUrl = $config->getOxConfig()->getShopSecureHomeUrl() . 'cl=payment';

            try {
                $frontend = qentaCheckoutSeamlessFrontend::getInstance();
                $frontend->setConsumerData($oOrder, $sQentaPaymentType);
                $frontend->setOrderData($oOrder, $sQentaPaymentType);
                $frontend->setBasket($oOrder, $sQentaPaymentType);

                $aValues = oxRegistry::getSession()->getVariable('qentaCheckoutSeamlessValues');
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

                    qentaCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':ERROR:' . print_r($aFormattedErrors, true));
                    return parent::_getNextStep(implode("<br/>\n", $aFormattedErrors));
                }

                if ($config->getUseIframe() && $sQentaPaymentType != WirecardCEE_QMore_PaymentType::SOFORTUEBERWEISUNG) {
                    $sStoken = oxRegistry::getSession()->getSessionChallengeToken();
                    $sHomeUrl = oxRegistry::getSession()->processUrl($config->getOxConfig()->getShopSecureHomeUrl());
                    oxRegistry::getSession()->setVariable('qentaCheckoutIframeUrl', $oResponse->getRedirectUrl());
                    $utils->redirect($sHomeUrl . 'cl=order&fnc=qentaCheckoutIframe&stoken=' . $sStoken);
                } else {
                    $utils->redirect($oResponse->getRedirectUrl());
                }

            } catch (Exception $e) {
                oxRegistry::getSession()->setVariable('payerror', -1);
                oxRegistry::getSession()->setVariable('payerrortext', $e->getMessage());
                qentaCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':ERROR:' . $e->getMessage());
                $utils->redirect($redirectErrorUrl);
            }

        } else {
            return parent::_getNextStep($iSuccess);
        }
    }

    public function qentaPending()
    {
        $this->qentaIframeBreakout();

        return parent::_getNextStep(oxOrder::ORDER_STATE_OK);
    }

    public function qentaSuccess()
    {
        $this->qentaIframeBreakout();

        return parent::_getNextStep(oxOrder::ORDER_STATE_OK);
    }

    public function qentaCancel()
    {
        $this->qentaIframeBreakout();
        $consumerMessage = oxRegistry::getSession()->getVariable('qentaCheckoutSeamlessConsumerMessage');
        oxRegistry::getSession()->setVariable('qcs_payerrortext', $consumerMessage);

        return parent::_getNextStep(oxOrder::ORDER_STATE_PAYMENTERROR);
    }

    public function qentaFailure()
    {
        $this->qentaIframeBreakout();

        $consumerMessage = oxRegistry::getSession()->getVariable('qentaCheckoutSeamlessConsumerMessage');
        oxRegistry::getSession()->setVariable('qcs_payerrortext', $consumerMessage);

        return parent::_getNextStep(oxOrder::ORDER_STATE_PAYMENTERROR);
    }

    public function qentaIframeBreakout()
    {
        /** @var oxUtilsUrl $urlUtils */
        $urlUtils = oxRegistry::get('oxUtilsUrl');
        $sRedirectUrl = $urlUtils->getCurrentUrl();

        $redirected = (string)oxRegistry::getConfig()->getRequestParameter('iframebreakout');
        if (!$redirected && qentaCheckoutSeamlessConfig::getInstance()->getUseIframe()) {
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

    public function qentaCheckoutIframe()
    {
        $this->addGlobalParams();

        $this->_aViewData['qentaCheckoutIframeUrl'] = oxRegistry::getSession()->getVariable('qentaCheckoutIframeUrl');

        $this->_sThisTemplate = 'qentacheckoutseamlessiframecheckout.tpl';
    }

    public function qentaConfirm()
    {
        qentaCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':' . print_r($_POST, true));

        $config = qentaCheckoutSeamlessConfig::getInstance();

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
            qentaCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':ORDER: can\'t update order state, since it is already in a final state: ' . $oOrder->oxorder__oxtransstatus);
            print WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString('Can\'t update order state, since it is already in a final state.');

            return;
        }

        /** @var qentaCheckoutSeamlessOrderDbGateway $oDbOrder */
        $oDbOrder = oxNew('qentaCheckoutSeamlessOrderDbGateway');
        $aOrderData = $oDbOrder->loadByOrderId($sOXID);
        if (!count($aOrderData)) {
            print WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString('QENTA Order not found.');

            return;
        }

        try {
            /** @var $return WirecardCEE_Stdlib_Return_ReturnAbstract */
            $return = WirecardCEE_QMore_ReturnFactory::getInstance($_POST, qentaCheckoutSeamlessConfig::getInstance()->getSecret());
            if (!$return->validate()) {
                qentaCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':Validation error: invalid response');
                print WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString('Validation error: invalid response');
                return;
            }

            switch ($return->getPaymentState()) {
                case WirecardCEE_QMore_ReturnFactory::STATE_SUCCESS:
                    /** @var $return WirecardCEE_QMore_Return_Success */
                    qentaCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':SUCCESS:' . $return->getOrderNumber() . ':' . $return->getGatewayReferenceNumber());
                    $oOrder->oxorder__oxtransstatus = new oxField('PAID');
                    $oOrder->oxorder__oxpaid = new oxField(date('Y-m-d H:i:s'));
                    $oOrder->oxorder__oxtransid = new oxField($return->getOrderNumber());
                    $oOrder->oxorder__oxpayid = new oxField($return->getGatewayReferenceNumber());
                    $oOrder->oxorder__oxxid = new oxField($return->getGatewayContractNumber());
                    $oOrder->save();

                    //create info data
                    $prefix = 'QENTA_CHECKOUT_SEAMLESS_';
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

                    /** @var qentaCheckoutSeamlessOxOrder $oOrder */
                    // cast oxBasket to qentaCheckoutSeamlessOxBasket
                    $sClass = "qentaCheckoutSeamlessBasket";
                    $oBasket = unserialize(preg_replace('/^O:\d+:"[^"]++"/', 'O:' . strlen($sClass) . ':"' . $sClass . '"', $aOrderData['BASKET']));
                    $oOrder->sendQentaCheckoutSeamlessOrderByEmail($oBasket, $oOxUserPayment);
                    $oDbOrder->delete($aOrderData['OXID']);
                    break;

                case WirecardCEE_QMore_ReturnFactory::STATE_PENDING:
                    $sendEmail = !in_array($oOrder->oxorder__oxtransstatus, array('PENDING'));

                    /** @var $return WirecardCEE_QMore_Return_Pending */
                    qentaCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':PENDING');
                    $oOrder->oxorder__oxtransstatus = new oxField('PENDING');
                    $oOrder->oxorder__oxtransid = new oxField($return->getOrderNumber());
                    $oOrder->save();

                    $oOxUserPayment = oxNew("oxUserPayment");
                    $oOxUserPayment->load($oOrder->oxorder__oxpaymentid->value);

                    /** @var qentaCheckoutSeamlessOxOrder $oOrder */
                    // cast oxBasket to qentaCheckoutSeamlessOxBasket
                    $sClass = "qentaCheckoutSeamlessBasket";
                    $oBasket = unserialize(preg_replace('/^O:\d+:"[^"]++"/', 'O:' . strlen($sClass) . ':"' . $sClass . '"', $aOrderData['BASKET']));

                    if($sendEmail) {
                        $oOrder->sendQentaCheckoutSeamlessOrderByEmail($oBasket, $oOxUserPayment);
                    }

                    break;

                case WirecardCEE_QMore_ReturnFactory::STATE_CANCEL:
                    /** @var $return WirecardCEE_QMore_Return_Cancel */
                    qentaCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':CANCEL');

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
                    qentaCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':FAILURE:' . print_r($return->getErrors(),
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
                    oxRegistry::getSession()->setVariable('qentaCheckoutSeamlessConsumerMessage', $consumerMessage);
                    break;

                default:
                    break;
            }
        } catch (Exception $e) {
            qentaCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':EXCEPTION:' . $e->getMessage() . $e->getTraceAsString());
            $out = WirecardCEE_QMore_ReturnFactory::generateConfirmResponseString($e->getMessage());
        }

        qentaCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':' . print_r($out, true));
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
     * @return null|qentaCheckoutSeamlessOxOrder
     */
    protected function _getOrderById($sOXID)
    {
        /** @var oxOrder $oOrder */
        $oOrder = oxNew("qentaCheckoutSeamlessOxOrder");
        $bSuccess = $oOrder->load($sOXID);

        return $bSuccess ? $oOrder : null;
    }

    public function isQcsPaymethod($sPaymentID)
    {
        return qentaCheckoutSeamlessPayment::isQcsPaymethod($sPaymentID);
    }

    public function getQcsRawPaymentDesc($paymethodNameWithPrefix)
    {
        return qentaCheckoutSeamlessPayment::getQcsRawPaymentDesc($paymethodNameWithPrefix);
    }
}
