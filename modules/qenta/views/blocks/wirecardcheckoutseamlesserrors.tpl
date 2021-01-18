[{if $oView->isWcsPaymentError() === TRUE}]
    <div class="status error">[{ $oView->getWcsPaymentError() }]</div>
    [{else}]
    [{$smarty.block.parent}]
    [{/if}]