<?php

/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/oxid-qcs/blob/master/LICENSE
 */

/**
 * Metadata version.
 */
$sMetadataVersion = '2.0';

/**
 * Module information.
 */
$aModule = [
    'id' => 'qmorecheckoutseamless',
    'title' => 'QMORE Checkout Seamless',
    'description' => [
        'de' => 'Modul zur Bezahlung mit QMORE Checkout Seamless.<br /><br /><div id="helpPanel"><div class="bd"><strong>Support und Vertrieb:</strong><br /><a href="https://guides.qenta.com/support" target="_blank">Support</a><br /><a href="https://guides.qenta.com/sales" target="_blank">Sales</a></div></div>',
        'en' => 'Module for payment using QMORE Checkout Seamless.<br /><br /><div id="helpPanel"><div class="bd"><strong>Support and sales information</strong><br /><a href="https://guides.qenta.com/support" target="_blank">support</a><br /><a href="https://guides.qenta.com/sales" target="_blank">sales</a></div></div>',
    ],
    'thumbnail' => 'picture.jpg',
    'version' => '3.0.0',
    'author' => 'QENTA Payment CEE GmbH',
    'url' => 'https://www.qenta.com',
    'email' => 'support@qenta.com',
    'extend' => [
        \OxidEsales\Eshop\Application\Controller\OrderController::class => \Qenta\Extend\Controller\qmoreCheckoutSeamlessOrder::class,
        \OxidEsales\Eshop\Application\Controller\PaymentController::class => \Qenta\Extend\Controller\qmoreCheckoutSeamlessPayment::class,
        \OxidEsales\Eshop\Application\Controller\ThankYouController::class => \Qenta\Extend\Controller\qmoreCheckoutSeamlessThankyou::class,
        // \OxidEsales\Eshop\Core\ViewConfig::class => \Qenta\Extend\Core\qmoreCheckoutSeamlessOxViewConfig::class,
        \OxidEsales\Eshop\Application\Model\PaymentGateway::class => \Qenta\Extend\Application\Model\qmoreCheckoutSeamlessOxPaymentGateway::class,
        \OxidEsales\Eshop\Application\Model\Order::class => \Qenta\Extend\Application\Model\qmoreCheckoutSeamlessOxOrder::class,
        \OxidEsales\Eshop\Application\Model\PaymentList::class => \Qenta\Extend\Application\Model\qmorecheckoutseamlessoxpaymentlist::class,
        \OxidEsales\Eshop\Application\Model\UserPayment::class => \Qenta\Extend\Application\Model\qmorecheckoutseamlessoxuserpayment::class,
    ],
    'controllers' => [
        'qmoreCheckoutSeamlessConfig' => \Qenta\Core\qmoreCheckoutSeamlessConfig::class,
        'qmoreCheckoutSeamlessEvents' => \Qenta\Core\qmoreCheckoutSeamlessEvents::class,
        'qmoreCheckoutSeamlessDataStorage' => \Qenta\Model\qmoreCheckoutSeamlessDataStorage::class,
        'qmoreCheckoutSeamlessFrontend' => \Qenta\Model\qmoreCheckoutSeamlessFrontend::class,
        'qmoreCheckoutSeamlessUtils' => \Qenta\Model\qmoreCheckoutSeamlessUtils::class,
        'qmoreCheckoutSeamlessOrderDbGateway' => \Qenta\Model\qmoreCheckoutSeamlessOrderDbGateway::class,
        'qmoreCheckoutSeamlessSubmitConfig' => \Qenta\Controller\Admin\qmorecheckoutseamlessSubmitConfig::class,
        'qmoreCheckoutSeamlessBasket' => \Qenta\Model\qmoreCheckoutSeamlessBasket::class,
    ],
    'events' => [
        'onActivate' => '\Qenta\Core\qmoreCheckoutSeamlessEvents::onActivate',
        'onDeactivate' => '\Qenta\Core\qmoreCheckoutSeamlessEvents::onDeactivate',
    ],
    'templates' => [
        'qmorecheckoutseamlessiframecheckout.tpl' => 'qenta/checkoutseamless/views/page/checkout/iframecheckout.tpl',
        'qmorecheckoutseamlesssubmitconfig.tpl' => 'qenta/checkoutseamless/views/admin/tpl/qmorecheckoutseamlesssubmitconfig.tpl',
        'qcs_payment_ccard.tpl' => 'qenta/checkoutseamless/views/blocks/paymethods/qcs_payment_ccard.tpl',
        'qcs_payment_eps.tpl' => 'qenta/checkoutseamless/views/blocks/paymethods/qcs_payment_eps.tpl',
        'qcs_payment_giropay.tpl' => 'qenta/checkoutseamless/views/blocks/paymethods/qcs_payment_giropay.tpl',
        'qcs_payment_idl.tpl' => 'qenta/checkoutseamless/views/blocks/paymethods/qcs_payment_idl.tpl',
        'qcs_payment_installment.tpl' => 'qenta/checkoutseamless/views/blocks/paymethods/qcs_payment_installment.tpl',
        'qcs_payment_invoice.tpl' => 'qenta/checkoutseamless/views/blocks/paymethods/qcs_payment_invoice.tpl',
        'qcs_payment_pbx.tpl' => 'qenta/checkoutseamless/views/blocks/paymethods/qcs_payment_pbx.tpl',
        'qcs_payment_sepa_dd.tpl' => 'qenta/checkoutseamless/views/blocks/paymethods/qcs_payment_sepa_dd.tpl',
        'qcs_payment_trustpay.tpl' => 'qenta/checkoutseamless/views/blocks/paymethods/qcs_payment_trustpay.tpl',
        'qcs_payment_voucher.tpl' => 'qenta/checkoutseamless/views/blocks/paymethods/qcs_payment_voucher.tpl',
        'qcs_payment_other.tpl' => 'qenta/checkoutseamless/views/blocks/paymethods/qcs_payment_other.tpl',
    ],
    'blocks' => [
        ['template' => 'page/checkout/payment.tpl', 'block' => 'select_payment', 'file' => '/views/blocks/paymentselector.tpl'],
        ['template' => 'page/checkout/thankyou.tpl', 'block' => 'checkout_thankyou_info', 'file' => '/views/blocks/thankyou.tpl'],
        ['template' => 'page/checkout/payment.tpl', 'block' => 'checkout_payment_errors', 'file' => '/views/blocks/qmorecheckoutseamlesserrors.tpl'],
        ['template' => 'page/checkout/order.tpl', 'block' => 'shippingAndPayment', 'file' => '/views/blocks/qmorecheckoutseamlessorder.tpl'],
        ['template' => 'email/html/order_cust.tpl', 'block' => 'email_html_order_cust_paymentinfo_top', 'file' => '/views/blocks/email/html/order_cust.tpl'],
        ['template' => 'email/plain/order_cust.tpl', 'block' => 'email_plain_order_cust_paymentinfo', 'file' => '/views/blocks/email/plain/order_cust.tpl'],
    ],
    'settings' => [
        ['group' => 'qcs_params', 'name' => 'sPluginMode', 'type' => 'select', 'value' => 'Demo', 'constraints' => 'Demo|Test|Live'],
        ['group' => 'qcs_params', 'name' => 'sCustomerId', 'type' => 'str', 'value' => 'D200001'],
        ['group' => 'qcs_params', 'name' => 'sShopId', 'type' => 'str', 'value' => 'seamless'],
        ['group' => 'qcs_params', 'name' => 'sSecret', 'type' => 'str', 'value' => 'B8AKTPWBRMNBV455FG6M2DANE99WU2'],
        ['group' => 'qcs_params', 'name' => 'sPassword', 'type' => 'str', 'value' => 'jcv45z'],
        ['group' => 'qcs_params', 'name' => 'sServiceUrl', 'type' => 'str', 'value' => ''],
        ['group' => 'qcs_params', 'name' => 'bAutoDeposit', 'type' => 'bool', 'value' => 'false'],
        ['group' => 'qcs_params', 'name' => 'sConfirmMail', 'type' => 'str', 'value' => ''],
        ['group' => 'qcs_params', 'name' => 'bDuplicateRequestCheck', 'type' => 'bool', 'value' => 'false'],
        ['group' => 'qcs_params', 'name' => 'sShopName', 'type' => 'str', 'value' => 'Web Shop'],

        ['group' => 'qcs_plugin', 'name' => 'bSendAdditionalCustomerBilling', 'type' => 'bool', 'value' => '1'],
        ['group' => 'qcs_plugin', 'name' => 'bSendAdditionalCustomerShipping', 'type' => 'bool', 'value' => '1'],
        ['group' => 'qcs_plugin', 'name' => 'bSendAdditionalBasketData', 'type' => 'bool', 'value' => '1'],
        ['group' => 'qcs_plugin', 'name' => 'bUseIframe', 'type' => 'bool', 'value' => '1'],
        ['group' => 'qcs_plugin', 'name' => 'sDeleteFailedOrCanceledOrders', 'type' => 'bool', 'value' => '1'],

        ['group' => 'qcs_invoice_settings', 'name' => 'sInvoiceProvider', 'type' => 'select', 'value' => 'PAYOLUTION', 'constraints' => 'PAYOLUTION|RATEPAY|WIRECARD'],
        ['group' => 'qcs_invoice_settings', 'name' => 'sInvoicePayolutionMId', 'type' => 'str', 'value' => ''],
        ['group' => 'qcs_invoice_settings', 'name' => 'bInvoiceb2bTrustedShopsCheckbox', 'type' => 'bool', 'value' => ''],
        ['group' => 'qcs_invoice_settings', 'name' => 'bInvoiceb2cTrustedShopsCheckbox', 'type' => 'bool', 'value' => ''],
        ['group' => 'qcs_invoice_settings', 'name' => 'bInvoiceAllowDifferingAddresses', 'type' => 'bool', 'value' => ''],

        ['group' => 'qcs_installment_settings', 'name' => 'sInstallmentProvider', 'type' => 'select', 'value' => 'PAYOLUTION', 'constraints' => 'PAYOLUTION|RATEPAY'],
        ['group' => 'qcs_installment_settings', 'name' => 'sInstallmentPayolutionMId', 'type' => 'str', 'value' => ''],
        ['group' => 'qcs_installment_settings', 'name' => 'bInstallmentTrustedShopsCheckbox', 'type' => 'bool', 'value' => ''],
        ['group' => 'qcs_installment_settings', 'name' => 'bInstallmentAllowDifferingAddresses', 'type' => 'bool', 'value' => ''],

        ['group' => 'qcs_pci_dss', 'name' => 'bDssSaqAEnable', 'type' => 'bool', 'value' => 'false'],
        ['group' => 'qcs_pci_dss', 'name' => 'bShowCreditcardCardholder', 'type' => 'bool', 'value' => 'true'],
        ['group' => 'qcs_pci_dss', 'name' => 'bShowCreditcardCvc', 'type' => 'bool', 'value' => 'true'],
        ['group' => 'qcs_pci_dss', 'name' => 'bShowCreditcardIssueDate', 'type' => 'bool', 'value' => 'false'],
        ['group' => 'qcs_pci_dss', 'name' => 'bShowCreditcardIssueNumber', 'type' => 'bool', 'value' => 'false'],
        ['group' => 'qcs_pci_dss', 'name' => 'sIframeCssUrl', 'type' => 'str', 'value' => ''],

        ['group' => 'qcs_risk_settings', 'name' => 'sRiskConfigAlias', 'type' => 'str', 'value' => ''],
        ['group' => 'qcs_risk_settings', 'name' => 'bRiskSuppress', 'type' => 'bool', 'value' => 'false'],
    ],
    'settings' => []
];
