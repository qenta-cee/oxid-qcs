[{if $oView->isQcsPaymentError() === TRUE}]
    <div class="status error">[{ $oView->getQcsPaymentError() }]</div>
    [{else}]
    [{$smarty.block.parent}]
    [{/if}]