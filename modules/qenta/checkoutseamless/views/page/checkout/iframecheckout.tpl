[{capture append="oxidBlock_content"}]

    [{include file="page/checkout/inc/steps.tpl" active=3}]

    <div id="checkout_iframe" style="margin:auto;">
        <iframe src="[{$qentaCheckoutIframeUrl}]" width="680" height="660" name="qentaCheckoutSeamlessIframe"
                frameborder="0" style="margin: auto;">
        </iframe>
    </div>

    [{/capture}]

[{include file="layout/page.tpl"}]
