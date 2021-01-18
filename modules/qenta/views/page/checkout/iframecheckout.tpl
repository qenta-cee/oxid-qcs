[{capture append="oxidBlock_content"}]

    [{include file="page/checkout/inc/steps.tpl" active=3}]

    <div id="checkout_iframe" style="margin:auto;">
        <iframe src="[{$qmoreCheckoutIframeUrl}]" width="680" height="660" name="qmoreCheckoutSeamlessIframe"
                frameborder="0" style="margin: auto;">
        </iframe>
    </div>

    [{/capture}]

[{include file="layout/page.tpl"}]
