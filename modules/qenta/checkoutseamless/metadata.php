<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/oxid-qcs/blob/master/LICENSE
*/


/**
 * Metadata version
 */
$sMetadataVersion = '2.1';

/**
 * Module information
 */
$aModule = array(
    'id'          => 'qentacheckoutseamless',
    'title'       => 'QMORE Checkout Seamless',
    'description' => array(
        'de' => 'Modul zur Bezahlung mit QENTA Checkout Seamless.<br /><br /><div id="helpPanel"><div class="bd"><strong>Support und Vertrieb:</strong><br /><a href="https://guides.qenta.at/support" target="_blank">Support</a><br /><a href="https://guides.qenta.at/sales" target="_blank">Sales</a></div></div>',
        'en' => 'Module for payment using QENTA Checkout Seamless.<br /><br /><div id="helpPanel"><div class="bd"><strong>Support and sales information</strong><br /><a href="https://guides.qenta.at/support" target="_blank">support</a><br /><a href="https://guides.qenta.at/sales" target="_blank">sales</a></div></div>',
    ),
    'thumbnail'   => 'qenta.svg',
    'version'     => '3.0.0',
    'author'      => 'QENTA Payment CEE GmbH',
    'url'         => 'http://www.qenta.com',
    'email'       => 'support@qenta.com',
    'extend'      => array(
        'order'            => 'qenta/checkoutseamless/controllers/qentacheckoutseamlessorder',
        'payment'          => 'qenta/checkoutseamless/controllers/qentacheckoutseamlesspayment',
        'thankyou'         => 'qenta/checkoutseamless/controllers/qentacheckoutseamlessthankyou',
        'oxviewconfig'     => 'qenta/checkoutseamless/core/qentacheckoutseamlessoxviewconfig',
        'oxpaymentgateway' => 'qenta/checkoutseamless/models/qentacheckoutseamlessoxpaymentgateway',
        'oxorder'          => 'qenta/checkoutseamless/models/qentacheckoutseamlessoxorder',
        'oxpaymentlist'    => 'qenta/checkoutseamless/models/qentacheckoutseamlessoxpaymentlist',
        'oxuserpayment'    => 'qenta/checkoutseamless/models/qentacheckoutseamlessoxuserpayment',
   ),
    'files'       => array(
        'qentaCheckoutSeamlessConfig'         => 'qenta/checkoutseamless/core/qentacheckoutseamlessconfig.php',
        'qentaCheckoutSeamlessEvents'         => 'qenta/checkoutseamless/core/qentacheckoutseamlessevents.php',
        'qentaCheckoutSeamlessDataStorage'    => 'qenta/checkoutseamless/models/qentacheckoutseamlessdatastorage.php',
        'qentaCheckoutSeamlessFrontend'       => 'qenta/checkoutseamless/models/qentacheckoutseamlessfrontend.php',
        'qentaCheckoutSeamlessUtils'          => 'qenta/checkoutseamless/models/qentacheckoutseamlessutils.php',
        'qentaCheckoutSeamlessOrderDbGateway' => 'qenta/checkoutseamless/models/dbgateways/qentacheckoutseamlessorderdbgateway.php',
		'qentaCheckoutSeamlessSubmitConfig'   => 'qenta/checkoutseamless/controllers/admin/qentacheckoutseamlesssubmitconfig.php',
		'qentaCheckoutSeamlessBasket'         => 'qenta/checkoutseamless/models/qentacheckoutseamlessoxbasket.php',

    ),
    'events'      => array(
        'onActivate'   => 'qentaCheckoutSeamlessEvents::onActivate',
        'onDeactivate' => 'qentaCheckoutSeamlessEvents::onDeactivate'
    ),
    'templates'   => array(
        'qentacheckoutseamlessiframecheckout.tpl' => 'qenta/checkoutseamless/views/page/checkout/iframecheckout.tpl',
        'qentacheckoutseamlesssubmitconfig.tpl'   => 'qenta/checkoutseamless/views/admin/tpl/qentacheckoutseamlesssubmitconfig.tpl',
        'qcs_payment_ccard.tpl'       =>  'qenta/checkoutseamless/views/blocks/paymethods/qcs_payment_ccard.tpl',
        'qcs_payment_eps.tpl'         =>  'qenta/checkoutseamless/views/blocks/paymethods/qcs_payment_eps.tpl',
        'qcs_payment_giropay.tpl'     =>  'qenta/checkoutseamless/views/blocks/paymethods/qcs_payment_giropay.tpl',
        'qcs_payment_idl.tpl'         =>  'qenta/checkoutseamless/views/blocks/paymethods/qcs_payment_idl.tpl',
        'qcs_payment_installment.tpl' =>  'qenta/checkoutseamless/views/blocks/paymethods/qcs_payment_installment.tpl',
        'qcs_payment_invoice.tpl'     =>  'qenta/checkoutseamless/views/blocks/paymethods/qcs_payment_invoice.tpl',
        'qcs_payment_pbx.tpl'         =>  'qenta/checkoutseamless/views/blocks/paymethods/qcs_payment_pbx.tpl',
        'qcs_payment_sepa_dd.tpl'     =>  'qenta/checkoutseamless/views/blocks/paymethods/qcs_payment_sepa_dd.tpl',
        'qcs_payment_trustpay.tpl'    =>  'qenta/checkoutseamless/views/blocks/paymethods/qcs_payment_trustpay.tpl',
        'qcs_payment_voucher.tpl'     =>  'qenta/checkoutseamless/views/blocks/paymethods/qcs_payment_voucher.tpl',
        'qcs_payment_other.tpl'       =>  'qenta/checkoutseamless/views/blocks/paymethods/qcs_payment_other.tpl',
   ),
    'blocks'      => array(
        array('template' => 'page/checkout/payment.tpl', 'block' => 'select_payment', 'file' => '/views/blocks/paymentselector.tpl'),
        array('template' => 'page/checkout/thankyou.tpl', 'block' => 'checkout_thankyou_info', 'file' => '/views/blocks/thankyou.tpl'),
        array('template' => 'page/checkout/payment.tpl','block' => 'checkout_payment_errors','file' => '/views/blocks/qentacheckoutseamlesserrors.tpl'),
        array('template' => 'page/checkout/order.tpl', 'block' => 'shippingAndPayment', 'file' => '/views/blocks/qentacheckoutseamlessorder.tpl'),
        array('template' => 'email/html/order_cust.tpl', 'block' => 'email_html_order_cust_paymentinfo_top', 'file' => '/views/blocks/email/html/order_cust.tpl'),
        array('template' => 'email/plain/order_cust.tpl', 'block' => 'email_plain_order_cust_paymentinfo', 'file' => '/views/blocks/email/plain/order_cust.tpl'),
    ),
    'settings'    => array(
        array('group' => 'qcs_params', 'name' => 'sPluginMode', 'type' => 'select', 'value' => 'Demo', 'constraints' => 'Demo|Test|Live'),
        array('group' => 'qcs_params', 'name' => 'sCustomerId', 'type' => 'str', 'value' => 'D200001'),
        array('group' => 'qcs_params', 'name' => 'sShopId', 'type' => 'str', 'value' => 'seamless'),
        array('group' => 'qcs_params', 'name' => 'sSecret', 'type'  => 'str', 'value' => 'B8AKTPWBRMNBV455FG6M2DANE99WU2'),
        array('group' => 'qcs_params', 'name' => 'sPassword', 'type' => 'str', 'value' => 'jcv45z'),
        array('group' => 'qcs_params', 'name' => 'sServiceUrl', 'type' => 'str', 'value' => ''),
        array('group' => 'qcs_params', 'name' => 'bAutoDeposit', 'type' => 'bool', 'value' => 'false'),
        array('group' => 'qcs_params', 'name' => 'sConfirmMail', 'type' => 'str', 'value' => ''),
        array('group' => 'qcs_params', 'name' => 'bDuplicateRequestCheck', 'type' => 'bool', 'value' => 'false'),
        array('group' => 'qcs_params', 'name' => 'sShopName', 'type' => 'str', 'value' => 'Web Shop'),

        array('group' => 'qcs_plugin', 'name' => 'bSendAdditionalCustomerBilling', 'type' => 'bool', 'value' => '1'),
	    array('group' => 'qcs_plugin', 'name' => 'bSendAdditionalCustomerShipping', 'type' => 'bool', 'value' => '1'),
        array('group' => 'qcs_plugin', 'name' => 'bSendAdditionalBasketData', 'type' => 'bool', 'value' => '1'),
        array('group' => 'qcs_plugin', 'name' => 'bUseIframe', 'type' => 'bool', 'value' => '1'),
        array('group' => 'qcs_plugin', 'name' => 'sDeleteFailedOrCanceledOrders', 'type' => 'bool', 'value' => '1'),

        array('group' => 'qcs_invoice_settings', 'name' => 'sInvoiceProvider', 'type' => 'select', 'value' => 'PAYOLUTION', 'constraints' => 'PAYOLUTION|RATEPAY|QENTA'),
        array('group' => 'qcs_invoice_settings', 'name' => 'sInvoicePayolutionMId', 'type' => 'str', 'value' => ''),
        array('group' => 'qcs_invoice_settings', 'name' => 'bInvoiceb2bTrustedShopsCheckbox', 'type' => 'bool', 'value' => ''),
        array('group' => 'qcs_invoice_settings', 'name' => 'bInvoiceb2cTrustedShopsCheckbox', 'type' => 'bool', 'value' => ''),
        array('group' => 'qcs_invoice_settings', 'name' => 'bInvoiceAllowDifferingAddresses', 'type' => 'bool', 'value' => ''),

	    array('group' => 'qcs_installment_settings', 'name' => 'sInstallmentProvider', 'type' => 'select', 'value' => 'PAYOLUTION', 'constraints' => 'PAYOLUTION|RATEPAY'),
	    array('group' => 'qcs_installment_settings', 'name' => 'sInstallmentPayolutionMId', 'type' => 'str', 'value' => ''),
	    array('group' => 'qcs_installment_settings', 'name' => 'bInstallmentTrustedShopsCheckbox', 'type' => 'bool', 'value' => ''),
	    array('group' => 'qcs_installment_settings', 'name' => 'bInstallmentAllowDifferingAddresses', 'type' => 'bool', 'value' => ''),

        array('group' => 'qcs_pci_dss', 'name' => 'bDssSaqAEnable', 'type' => 'bool', 'value' => 'false'),
        array('group' => 'qcs_pci_dss', 'name' => 'bShowCreditcardCardholder', 'type' => 'bool', 'value' => 'true'),
        array('group' => 'qcs_pci_dss', 'name' => 'bShowCreditcardCvc', 'type' => 'bool', 'value' => 'true'),
        array('group' => 'qcs_pci_dss', 'name' => 'bShowCreditcardIssueDate', 'type' => 'bool', 'value' => 'false'),
        array('group' => 'qcs_pci_dss', 'name' => 'bShowCreditcardIssueNumber', 'type' => 'bool', 'value' => 'false'),
        array('group' => 'qcs_pci_dss', 'name' => 'sIframeCssUrl', 'type' => 'str', 'value' => ''),

        array('group' => 'qcs_risk_settings', 'name' => 'sRiskConfigAlias', 'type' => 'str', 'value' => ''),
        array('group' => 'qcs_risk_settings', 'name' => 'bRiskSuppress', 'type' => 'bool', 'value' => 'false'),
    )
);
