<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/oxid-qcs/blob/master/LICENSE
*/
namespace Qenta\Extend\Controller;
/**
 * Order class wrapper for QMORE Checkout Seamless
 *
 * @see order
 */
class qmoreCheckoutSeamlessOrder extends qmoreCheckoutSeamlessOrder_parent
{
    /**
     * Checks if order payment is a QENTA payment and redirect
     *
     * @param int $iSuccess order state
     *
     * @return string
     */
    protected function _getNextStep($iSuccess)
    {
        $sPaymentID = $this->getSession()->getVariable("paymentid");

        $isQenta = qmoreCheckoutSeamlessUtils::getInstance()->isOwnPayment($sPaymentID);

        if ($isQenta && is_numeric($iSuccess) && ($iSuccess == oxOrder::ORDER_STATE_OK || $iSuccess == oxOrder::ORDER_STATE_ORDEREXISTS)) {

            /** @var oxUtils $utils */
            $utils = oxRegistry::get('oxUtils');

            $oOrder = $this->_getOrder();

            /** @var qmoreCheckoutSeamlessOrderDbGateway $oDbOrder */
            $oDbOrder = oxNew('qmoreCheckoutSeamlessOrderDbGateway');
            $aOrderData = Array(
                'BASKET' => serialize(oxRegistry::getSession()->getBasket()),
                'OXORDERID' => $oOrder->getId()
            );
            $oDbOrder->insert($aOrderData);

            $sQentaPaymentType = qmoreCheckoutSeamlessUtils::getInstance()->convertPaymenttype($sPaymentID);

            $config = qmoreCheckoutSeamlessConfig::getInstance();

            $redirectErrorUrl = $config->getOxConfig()->getShopSecureHomeUrl() . 'cl=payment';

            try {
                $frontend = qmoreCheckoutSeamlessFrontend::getInstance();
                $frontend->setConsumerData($oOrder, $sQentaPaymentType);
                $frontend->setOrderData($oOrder, $sQentaPaymentType);
                $frontend->setBasket($oOrder, $sQentaPaymentType);

                $aValues = oxRegistry::getSession()->getVariable('qmoreCheckoutSeamlessValues');
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

                    qmoreCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':ERROR:' . print_r($aFormattedErrors, true));
                    return parent::_getNextStep(implode("<br/>\n", $aFormattedErrors));
                }

                if ($config->getUseIframe() && $sQentaPaymentType != QentaCEE\Qmore\PaymentType::SOFORTUEBERWEISUNG) {
                    $sStoken = oxRegistry::getSession()->getSessionChallengeToken();
                    $sHomeUrl = oxRegistry::getSession()->processUrl($config->getOxConfig()->getShopSecureHomeUrl());
                    oxRegistry::getSession()->setVariable('qmoreCheckoutIframeUrl', $oResponse->getRedirectUrl());
                    $utils->redirect($sHomeUrl . 'cl=order&fnc=qmoreCheckoutIframe&stoken=' . $sStoken);
                } else {
                    $utils->redirect($oResponse->getRedirectUrl());
                }

            } catch (Exception $e) {
                oxRegistry::getSession()->setVariable('payerror', -1);
                oxRegistry::getSession()->setVariable('payerrortext', $e->getMessage());
                qmoreCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':ERROR:' . $e->getMessage());
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
        $consumerMessage = oxRegistry::getSession()->getVariable('qmoreCheckoutSeamlessConsumerMessage');
        oxRegistry::getSession()->setVariable('qcs_payerrortext', $consumerMessage);

        return parent::_getNextStep(oxOrder::ORDER_STATE_PAYMENTERROR);
    }

    public function qentaFailure()
    {
        $this->qentaIframeBreakout();

        $consumerMessage = oxRegistry::getSession()->getVariable('qmoreCheckoutSeamlessConsumerMessage');
        oxRegistry::getSession()->setVariable('qcs_payerrortext', $consumerMessage);

        return parent::_getNextStep(oxOrder::ORDER_STATE_PAYMENTERROR);
    }

    public function qentaIframeBreakout()
    {
        /** @var oxUtilsUrl $urlUtils */
        $urlUtils = oxRegistry::get('oxUtilsUrl');
        $sRedirectUrl = $urlUtils->getCurrentUrl();

        $redirected = (string)oxRegistry::getConfig()->getRequestParameter('iframebreakout');
        if (!$redirected && qmoreCheckoutSeamlessConfig::getInstance()->getUseIframe()) {
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

    public function qmoreCheckoutIframe()
    {
        $this->addGlobalParams();

        $this->_aViewData['qmoreCheckoutIframeUrl'] = oxRegistry::getSession()->getVariable('qmoreCheckoutIframeUrl');

        $this->_sThisTemplate = 'qmorecheckoutseamlessiframecheckout.tpl';
    }

    public function qentaConfirm()
    {
        qmoreCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':' . print_r($_POST, true));

        $config = qmoreCheckoutSeamlessConfig::getInstance();

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
            qmoreCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':ORDER: can\'t update order state, since it is already in a final state: ' . $oOrder->oxorder__oxtransstatus);
            print QentaCEE\Qmore\ReturnFactory::generateConfirmResponseString('Can\'t update order state, since it is already in a final state.');

            return;
        }

        /** @var qmoreCheckoutSeamlessOrderDbGateway $oDbOrder */
        $oDbOrder = oxNew('qmoreCheckoutSeamlessOrderDbGateway');
        $aOrderData = $oDbOrder->loadByOrderId($sOXID);
        if (!count($aOrderData)) {
            print QentaCEE\Qmore\ReturnFactory::generateConfirmResponseString('QENTA Order not found.');

            return;
        }

        try {
            /** @var $return QentaCEE\Stdlib\Return\ReturnAbstract */
            $return = QentaCEE\Qmore\ReturnFactory::getInstance($_POST, qmoreCheckoutSeamlessConfig::getInstance()->getSecret());
            if (!$return->validate()) {
                qmoreCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':Validation error: invalid response');
                print QentaCEE\Qmore\ReturnFactory::generateConfirmResponseString('Validation error: invalid response');
                return;
            }

            switch ($return->getPaymentState()) {
                case QentaCEE\Qmore\ReturnFactory::STATE_SUCCESS:
                    /** @var $return QentaCEE\Qmore\Return_Success */
                    qmoreCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':SUCCESS:' . $return->getOrderNumber() . ':' . $return->getGatewayReferenceNumber());
                    $oOrder->oxorder__oxtransstatus = new oxField('PAID');
                    $oOrder->oxorder__oxpaid = new oxField(date('Y-m-d H:i:s'));
                    $oOrder->oxorder__oxtransid = new oxField($return->getOrderNumber());
                    $oOrder->oxorder__oxpayid = new oxField($return->getGatewayReferenceNumber());
                    $oOrder->oxorder__oxxid = new oxField($return->getGatewayContractNumber());
                    $oOrder->save();

                    //create info data
                    $prefix = 'QMORE_CHECKOUT_SEAMLESS_';
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

                    /** @var qmoreCheckoutSeamlessOxOrder $oOrder */
                    // cast oxBasket to qmoreCheckoutSeamlessOxBasket
                    $sClass = "qmoreCheckoutSeamlessBasket";
                    $oBasket = unserialize(preg_replace('/^O:\d+:"[^"]++"/', 'O:' . strlen($sClass) . ':"' . $sClass . '"', $aOrderData['BASKET']));
                    $oOrder->sendQMoreCheckoutSeamlessOrderByEmail($oBasket, $oOxUserPayment);
                    $oDbOrder->delete($aOrderData['OXID']);
                    break;

                case QentaCEE\Qmore\ReturnFactory::STATE_PENDING:
                    $sendEmail = !in_array($oOrder->oxorder__oxtransstatus, array('PENDING'));

                    /** @var $return QentaCEE\Qmore\Return_Pending */
                    qmoreCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':PENDING');
                    $oOrder->oxorder__oxtransstatus = new oxField('PENDING');
                    $oOrder->oxorder__oxtransid = new oxField($return->getOrderNumber());
                    $oOrder->save();

                    $oOxUserPayment = oxNew("oxUserPayment");
                    $oOxUserPayment->load($oOrder->oxorder__oxpaymentid->value);

                    /** @var qmoreCheckoutSeamlessOxOrder $oOrder */
                    // cast oxBasket to qmoreCheckoutSeamlessOxBasket
                    $sClass = "qmoreCheckoutSeamlessBasket";
                    $oBasket = unserialize(preg_replace('/^O:\d+:"[^"]++"/', 'O:' . strlen($sClass) . ':"' . $sClass . '"', $aOrderData['BASKET']));

                    if($sendEmail) {
                        $oOrder->sendQMoreCheckoutSeamlessOrderByEmail($oBasket, $oOxUserPayment);
                    }

                    break;

                case QentaCEE\Qmore\ReturnFactory::STATE_CANCEL:
                    /** @var $return QentaCEE\Qmore\Return_Cancel */
                    qmoreCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':CANCEL');

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
                    qmoreCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':FAILURE:' . print_r($return->getErrors(),
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
                    oxRegistry::getSession()->setVariable('qmoreCheckoutSeamlessConsumerMessage', $consumerMessage);
                    break;

                default:
                    break;
            }
        } catch (Exception $e) {
            qmoreCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':EXCEPTION:' . $e->getMessage() . $e->getTraceAsString());
            $out = QentaCEE\Qmore\ReturnFactory::generateConfirmResponseString($e->getMessage());
        }

        qmoreCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':' . print_r($out, true));
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
     * @return null|qmoreCheckoutSeamlessOxOrder
     */
    protected function _getOrderById($sOXID)
    {
        /** @var oxOrder $oOrder */
        $oOrder = oxNew("qmoreCheckoutSeamlessOxOrder");
        $bSuccess = $oOrder->load($sOXID);

        return $bSuccess ? $oOrder : null;
    }

    public function isQcsPaymethod($sPaymentID)
    {
        return qmoreCheckoutSeamlessPayment::isQcsPaymethod($sPaymentID);
    }

    public function getQcsRawPaymentDesc($paymethodNameWithPrefix)
    {
        return qmoreCheckoutSeamlessPayment::getQcsRawPaymentDesc($paymethodNameWithPrefix);
    }
}
