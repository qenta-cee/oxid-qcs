<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/oxid-qcs/blob/master/LICENSE
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

                if ($config->getUseIframe() && $sWirecardPaymentType != QentaCEE\Qmore\PaymentType::SOFORTUEBERWEISUNG) {
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

        $out = QentaCEE\Qmore\ReturnFactory::generateConfirmResponseString();

        if (!isset($_POST['oxid_orderid'])) {
            print QentaCEE\Qmore\ReturnFactory::generateConfirmResponseString('Order Id is missing');

            return;
        }

        $sOXID = $_POST['oxid_orderid'];
        /** @var oxOrder $oOrder */
        $oOrder = $this->_getOrderById($sOXID);
        if ($oOrder === null) {
            print QentaCEE\Qmore\ReturnFactory::generateConfirmResponseString('Order not found.');

            return;
        }

        if(in_array($oOrder->oxorder__oxtransstatus, array('PAID'))) {
            wirecardCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':ORDER: can\'t update order state, since it is already in a final state: ' . $oOrder->oxorder__oxtransstatus);
            print QentaCEE\Qmore\ReturnFactory::generateConfirmResponseString('Can\'t update order state, since it is already in a final state.');

            return;
        }

        /** @var wirecardCheckoutSeamlessOrderDbGateway $oDbOrder */
        $oDbOrder = oxNew('wirecardCheckoutSeamlessOrderDbGateway');
        $aOrderData = $oDbOrder->loadByOrderId($sOXID);
        if (!count($aOrderData)) {
            print QentaCEE\Qmore\ReturnFactory::generateConfirmResponseString('Wirecard Order not found.');

            return;
        }

        try {
            /** @var $return QentaCEE\Stdlib\Return\ReturnAbstract */
            $return = QentaCEE\Qmore\ReturnFactory::getInstance($_POST, wirecardCheckoutSeamlessConfig::getInstance()->getSecret());
            if (!$return->validate()) {
                wirecardCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':Validation error: invalid response');
                print QentaCEE\Qmore\ReturnFactory::generateConfirmResponseString('Validation error: invalid response');
                return;
            }

            switch ($return->getPaymentState()) {
                case QentaCEE\Qmore\ReturnFactory::STATE_SUCCESS:
                    /** @var $return QentaCEE\Qmore\Return_Success */
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

                case QentaCEE\Qmore\ReturnFactory::STATE_PENDING:
                    $sendEmail = !in_array($oOrder->oxorder__oxtransstatus, array('PENDING'));

                    /** @var $return QentaCEE\Qmore\Return_Pending */
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

                case QentaCEE\Qmore\ReturnFactory::STATE_CANCEL:
                    /** @var $return QentaCEE\Qmore\Return_Cancel */
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

                case QentaCEE\Qmore\ReturnFactory::STATE_FAILURE:
                    /** @var $return QentaCEE\Qmore\Return_Failure */
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
                    /** var $e QentaCEE\Qmore\Error */
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
            $out = QentaCEE\Qmore\ReturnFactory::generateConfirmResponseString($e->getMessage());
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
