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
$sMetadataVersion = '1.1';

/**
 * Module information
 */
$aModule = array(
    'id'          => 'wirecardcheckoutseamless',
    'title'       => 'QMORE Checkout Seamless',
    'description' => array(
        'de' => 'Modul zur Bezahlung mit QMORE Checkout Seamless.<br /><br /><div id="helpPanel"><div class="bd"><strong>Support und Vertrieb:</strong><br /><a href="https://guides.qenta.com/support" target="_blank">Support</a><br /><a href="https://guides.qenta.com/sales" target="_blank">Sales</a></div></div>',
        'en' => 'Module for payment using QMORE Checkout Seamless.<br /><br /><div id="helpPanel"><div class="bd"><strong>Support and sales information</strong><br /><a href="https://guides.qenta.com/support" target="_blank">support</a><br /><a href="https://guides.qenta.com/sales" target="_blank">sales</a></div></div>',
    ),
    'thumbnail'   => 'picture.jpg',
    'version'     => '3.0.0',
    'author'      => 'QENTA Payment CEE GmbH',
    'url'         => 'http://www.qenta.com',
    'email'       => 'support@qenta.com',
    'extend'      => array(
        'order'            => 'qenta/checkoutseamless/controllers/wirecardcheckoutseamlessorder',
        'payment'          => 'qenta/checkoutseamless/controllers/wirecardcheckoutseamlesspayment',
        'thankyou'         => 'qenta/checkoutseamless/controllers/wirecardcheckoutseamlessthankyou',
        'oxviewconfig'     => 'qenta/checkoutseamless/core/wirecardcheckoutseamlessoxviewconfig',
        'oxpaymentgateway' => 'qenta/checkoutseamless/models/wirecardcheckoutseamlessoxpaymentgateway',
        'oxorder'          => 'qenta/checkoutseamless/models/wirecardcheckoutseamlessoxorder',
        'oxpaymentlist'    => 'qenta/checkoutseamless/models/wirecardcheckoutseamlessoxpaymentlist',
        'oxuserpayment'    => 'qenta/checkoutseamless/models/wirecardcheckoutseamlessoxuserpayment',
   ),
    'files'       => array(
        'wirecardCheckoutSeamlessConfig'         => 'qenta/checkoutseamless/core/wirecardcheckoutseamlessconfig.php',
        'wirecardCheckoutSeamlessEvents'         => 'qenta/checkoutseamless/core/wirecardcheckoutseamlessevents.php',
        'wirecardCheckoutSeamlessDataStorage'    => 'qenta/checkoutseamless/models/wirecardcheckoutseamlessdatastorage.php',
        'wirecardCheckoutSeamlessFrontend'       => 'qenta/checkoutseamless/models/wirecardcheckoutseamlessfrontend.php',
        'wirecardCheckoutSeamlessUtils'          => 'qenta/checkoutseamless/models/wirecardcheckoutseamlessutils.php',
        'wirecardCheckoutSeamlessOrderDbGateway' => 'qenta/checkoutseamless/models/dbgateways/wirecardcheckoutseamlessorderdbgateway.php',
		'wirecardCheckoutSeamlessSubmitConfig'   => 'qenta/checkoutseamless/controllers/admin/wirecardcheckoutseamlesssubmitconfig.php',
		'wirecardCheckoutSeamlessBasket'         => 'qenta/checkoutseamless/models/wirecardcheckoutseamlessoxbasket.php',

    ),
    'events'      => array(
        'onActivate'   => 'wirecardCheckoutSeamlessEvents::onActivate',
        'onDeactivate' => 'wirecardCheckoutSeamlessEvents::onDeactivate'
    ),
    'templates'   => array(
        'wirecardcheckoutseamlessiframecheckout.tpl' => 'qenta/checkoutseamless/views/page/checkout/iframecheckout.tpl',
        'wirecardcheckoutseamlesssubmitconfig.tpl'   => 'qenta/checkoutseamless/views/admin/tpl/wirecardcheckoutseamlesssubmitconfig.tpl',
        'wcs_payment_ccard.tpl'       =>  'qenta/checkoutseamless/views/blocks/paymethods/wcs_payment_ccard.tpl',
        'wcs_payment_eps.tpl'         =>  'qenta/checkoutseamless/views/blocks/paymethods/wcs_payment_eps.tpl',
        'wcs_payment_giropay.tpl'     =>  'qenta/checkoutseamless/views/blocks/paymethods/wcs_payment_giropay.tpl',
        'wcs_payment_idl.tpl'         =>  'qenta/checkoutseamless/views/blocks/paymethods/wcs_payment_idl.tpl',
        'wcs_payment_installment.tpl' =>  'qenta/checkoutseamless/views/blocks/paymethods/wcs_payment_installment.tpl',
        'wcs_payment_invoice.tpl'     =>  'qenta/checkoutseamless/views/blocks/paymethods/wcs_payment_invoice.tpl',
        'wcs_payment_pbx.tpl'         =>  'qenta/checkoutseamless/views/blocks/paymethods/wcs_payment_pbx.tpl',
        'wcs_payment_sepa_dd.tpl'     =>  'qenta/checkoutseamless/views/blocks/paymethods/wcs_payment_sepa_dd.tpl',
        'wcs_payment_trustpay.tpl'    =>  'qenta/checkoutseamless/views/blocks/paymethods/wcs_payment_trustpay.tpl',
        'wcs_payment_voucher.tpl'     =>  'qenta/checkoutseamless/views/blocks/paymethods/wcs_payment_voucher.tpl',
        'wcs_payment_other.tpl'       =>  'qenta/checkoutseamless/views/blocks/paymethods/wcs_payment_other.tpl',
   ),
    'blocks'      => array(
        array('template' => 'page/checkout/payment.tpl', 'block' => 'select_payment', 'file' => '/views/blocks/paymentselector.tpl'),
        array('template' => 'page/checkout/thankyou.tpl', 'block' => 'checkout_thankyou_info', 'file' => '/views/blocks/thankyou.tpl'),
        array('template' => 'page/checkout/payment.tpl','block' => 'checkout_payment_errors','file' => '/views/blocks/wirecardcheckoutseamlesserrors.tpl'),
        array('template' => 'page/checkout/order.tpl', 'block' => 'shippingAndPayment', 'file' => '/views/blocks/wirecardcheckoutseamlessorder.tpl'),
        array('template' => 'email/html/order_cust.tpl', 'block' => 'email_html_order_cust_paymentinfo_top', 'file' => '/views/blocks/email/html/order_cust.tpl'),
        array('template' => 'email/plain/order_cust.tpl', 'block' => 'email_plain_order_cust_paymentinfo', 'file' => '/views/blocks/email/plain/order_cust.tpl'),
    ),
    'settings'    => array(
        array('group' => 'wcs_params', 'name' => 'sPluginMode', 'type' => 'select', 'value' => 'Demo', 'constraints' => 'Demo|Test|Live'),
        array('group' => 'wcs_params', 'name' => 'sCustomerId', 'type' => 'str', 'value' => 'D200001'),
        array('group' => 'wcs_params', 'name' => 'sShopId', 'type' => 'str', 'value' => 'seamless'),
        array('group' => 'wcs_params', 'name' => 'sSecret', 'type'  => 'str', 'value' => 'B8AKTPWBRMNBV455FG6M2DANE99WU2'),
        array('group' => 'wcs_params', 'name' => 'sPassword', 'type' => 'str', 'value' => 'jcv45z'),
        array('group' => 'wcs_params', 'name' => 'sServiceUrl', 'type' => 'str', 'value' => ''),
        array('group' => 'wcs_params', 'name' => 'bAutoDeposit', 'type' => 'bool', 'value' => 'false'),
        array('group' => 'wcs_params', 'name' => 'sConfirmMail', 'type' => 'str', 'value' => ''),
        array('group' => 'wcs_params', 'name' => 'bDuplicateRequestCheck', 'type' => 'bool', 'value' => 'false'),
        array('group' => 'wcs_params', 'name' => 'sShopName', 'type' => 'str', 'value' => 'Web Shop'),

        array('group' => 'wcs_plugin', 'name' => 'bSendAdditionalCustomerBilling', 'type' => 'bool', 'value' => '1'),
	    array('group' => 'wcs_plugin', 'name' => 'bSendAdditionalCustomerShipping', 'type' => 'bool', 'value' => '1'),
        array('group' => 'wcs_plugin', 'name' => 'bSendAdditionalBasketData', 'type' => 'bool', 'value' => '1'),
        array('group' => 'wcs_plugin', 'name' => 'bUseIframe', 'type' => 'bool', 'value' => '1'),
        array('group' => 'wcs_plugin', 'name' => 'sDeleteFailedOrCanceledOrders', 'type' => 'bool', 'value' => '1'),

        array('group' => 'wcs_invoice_settings', 'name' => 'sInvoiceProvider', 'type' => 'select', 'value' => 'PAYOLUTION', 'constraints' => 'PAYOLUTION|RATEPAY|WIRECARD'),
        array('group' => 'wcs_invoice_settings', 'name' => 'sInvoicePayolutionMId', 'type' => 'str', 'value' => ''),
        array('group' => 'wcs_invoice_settings', 'name' => 'bInvoiceb2bTrustedShopsCheckbox', 'type' => 'bool', 'value' => ''),
        array('group' => 'wcs_invoice_settings', 'name' => 'bInvoiceb2cTrustedShopsCheckbox', 'type' => 'bool', 'value' => ''),
        array('group' => 'wcs_invoice_settings', 'name' => 'bInvoiceAllowDifferingAddresses', 'type' => 'bool', 'value' => ''),

	    array('group' => 'wcs_installment_settings', 'name' => 'sInstallmentProvider', 'type' => 'select', 'value' => 'PAYOLUTION', 'constraints' => 'PAYOLUTION|RATEPAY'),
	    array('group' => 'wcs_installment_settings', 'name' => 'sInstallmentPayolutionMId', 'type' => 'str', 'value' => ''),
	    array('group' => 'wcs_installment_settings', 'name' => 'bInstallmentTrustedShopsCheckbox', 'type' => 'bool', 'value' => ''),
	    array('group' => 'wcs_installment_settings', 'name' => 'bInstallmentAllowDifferingAddresses', 'type' => 'bool', 'value' => ''),

        array('group' => 'wcs_pci_dss', 'name' => 'bDssSaqAEnable', 'type' => 'bool', 'value' => 'false'),
        array('group' => 'wcs_pci_dss', 'name' => 'bShowCreditcardCardholder', 'type' => 'bool', 'value' => 'true'),
        array('group' => 'wcs_pci_dss', 'name' => 'bShowCreditcardCvc', 'type' => 'bool', 'value' => 'true'),
        array('group' => 'wcs_pci_dss', 'name' => 'bShowCreditcardIssueDate', 'type' => 'bool', 'value' => 'false'),
        array('group' => 'wcs_pci_dss', 'name' => 'bShowCreditcardIssueNumber', 'type' => 'bool', 'value' => 'false'),
        array('group' => 'wcs_pci_dss', 'name' => 'sIframeCssUrl', 'type' => 'str', 'value' => ''),

        array('group' => 'wcs_risk_settings', 'name' => 'sRiskConfigAlias', 'type' => 'str', 'value' => ''),
        array('group' => 'wcs_risk_settings', 'name' => 'bRiskSuppress', 'type' => 'bool', 'value' => 'false'),
    )
);
