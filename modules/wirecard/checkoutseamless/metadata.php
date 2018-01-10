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

/**
 * Metadata version
 */
$sMetadataVersion = '1.1';

/**
 * Module information
 */
$aModule = array(
    'id'          => 'wirecardcheckoutseamless',
    'title'       => 'Wirecard Checkout Seamless',
    'description' => array(
        'de' => 'Modul zur Bezahlung mit Wirecard Checkout Seamless.<br /><br /><div id="helpPanel"><div class="bd"><strong>Support und Vertrieb:</strong><br /><a href="https://guides.wirecard.at/support" target="_blank">Support</a><br /><a href="https://guides.wirecard.at/sales" target="_blank">Sales</a></div></div>',
        'en' => 'Module for payment using Wirecard Checkout Seamless.<br /><br /><div id="helpPanel"><div class="bd"><strong>Support and sales information</strong><br /><a href="https://guides.wirecard.at/support" target="_blank">support</a><br /><a href="https://guides.wirecard.at/sales" target="_blank">sales</a></div></div>',
    ),
    'thumbnail'   => 'picture.jpg',
    'version'     => '2.2.5',
    'author'      => 'Wirecard',
    'url'         => 'http://www.wirecard.at',
    'email'       => 'support@wirecard.at',
    'extend'      => array(
        'order'            => 'wirecard/checkoutseamless/controllers/wirecardcheckoutseamlessorder',
        'payment'          => 'wirecard/checkoutseamless/controllers/wirecardcheckoutseamlesspayment',
        'thankyou'         => 'wirecard/checkoutseamless/controllers/wirecardcheckoutseamlessthankyou',
        'oxviewconfig'     => 'wirecard/checkoutseamless/core/wirecardcheckoutseamlessoxviewconfig',
        'oxpaymentgateway' => 'wirecard/checkoutseamless/models/wirecardcheckoutseamlessoxpaymentgateway',
        'oxorder'          => 'wirecard/checkoutseamless/models/wirecardcheckoutseamlessoxorder',
        'oxpaymentlist'    => 'wirecard/checkoutseamless/models/wirecardcheckoutseamlessoxpaymentlist',
        'oxuserpayment'    => 'wirecard/checkoutseamless/models/wirecardcheckoutseamlessoxuserpayment',
   ),
    'files'       => array(
        'wirecardCheckoutSeamlessConfig'         => 'wirecard/checkoutseamless/core/wirecardcheckoutseamlessconfig.php',
        'wirecardCheckoutSeamlessEvents'         => 'wirecard/checkoutseamless/core/wirecardcheckoutseamlessevents.php',
        'wirecardCheckoutSeamlessDataStorage'    => 'wirecard/checkoutseamless/models/wirecardcheckoutseamlessdatastorage.php',
        'wirecardCheckoutSeamlessFrontend'       => 'wirecard/checkoutseamless/models/wirecardcheckoutseamlessfrontend.php',
        'wirecardCheckoutSeamlessUtils'          => 'wirecard/checkoutseamless/models/wirecardcheckoutseamlessutils.php',
        'wirecardCheckoutSeamlessOrderDbGateway' => 'wirecard/checkoutseamless/models/dbgateways/wirecardcheckoutseamlessorderdbgateway.php',
		'wirecardCheckoutSeamlessSubmitConfig'   => 'wirecard/checkoutseamless/controllers/admin/wirecardcheckoutseamlesssubmitconfig.php',
		'wirecardCheckoutSeamlessBasket'         => 'wirecard/checkoutseamless/models/wirecardcheckoutseamlessoxbasket.php',

    ),
    'events'      => array(
        'onActivate'   => 'wirecardCheckoutSeamlessEvents::onActivate',
        'onDeactivate' => 'wirecardCheckoutSeamlessEvents::onDeactivate'
    ),
    'templates'   => array(
        'wirecardcheckoutseamlessiframecheckout.tpl' => 'wirecard/checkoutseamless/views/page/checkout/iframecheckout.tpl',
        'wirecardcheckoutseamlesssubmitconfig.tpl'   => 'wirecard/checkoutseamless/views/admin/tpl/wirecardcheckoutseamlesssubmitconfig.tpl',
        'wcs_payment_ccard.tpl'       =>  'wirecard/checkoutseamless/views/blocks/paymethods/wcs_payment_ccard.tpl',
        'wcs_payment_eps.tpl'         =>  'wirecard/checkoutseamless/views/blocks/paymethods/wcs_payment_eps.tpl',
        'wcs_payment_giropay.tpl'     =>  'wirecard/checkoutseamless/views/blocks/paymethods/wcs_payment_giropay.tpl',
        'wcs_payment_idl.tpl'         =>  'wirecard/checkoutseamless/views/blocks/paymethods/wcs_payment_idl.tpl',
        'wcs_payment_installment.tpl' =>  'wirecard/checkoutseamless/views/blocks/paymethods/wcs_payment_installment.tpl',
        'wcs_payment_invoice.tpl'     =>  'wirecard/checkoutseamless/views/blocks/paymethods/wcs_payment_invoice.tpl',
        'wcs_payment_pbx.tpl'         =>  'wirecard/checkoutseamless/views/blocks/paymethods/wcs_payment_pbx.tpl',
        'wcs_payment_sepa_dd.tpl'     =>  'wirecard/checkoutseamless/views/blocks/paymethods/wcs_payment_sepa_dd.tpl',
        'wcs_payment_trustpay.tpl'    =>  'wirecard/checkoutseamless/views/blocks/paymethods/wcs_payment_trustpay.tpl',
        'wcs_payment_voucher.tpl'     =>  'wirecard/checkoutseamless/views/blocks/paymethods/wcs_payment_voucher.tpl',
        'wcs_payment_other.tpl'       =>  'wirecard/checkoutseamless/views/blocks/paymethods/wcs_payment_other.tpl',
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
