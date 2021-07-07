<?php

/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/oxid-qcs/blob/master/LICENSE
 */

require_once getShopBasePath() . 'modules/qenta/checkoutseamless/autoloader.php';

/**
 * QENTA Checkout Seamless oxBasket class
 *
 * @see oxBasket
 */
class qentaCheckoutSeamlessBasket extends oxBasket
{
    /**
     * Returns array of basket oxarticle objects
     *
     * @return array
     */
    public function getBasketArticles()
    {
        qentaCheckoutSeamlessUtils::getInstance()->log(__METHOD__ . ':has been called');
        $aBasketArticles = array();


        foreach ($this->_aBasketContents as $sItemKey => $oBasketItem) {
            try {
                $oProduct = $oBasketItem->getArticle();

                if ($this->getConfig()->getConfigParam('bl_perfLoadSelectLists')) {
                    // marking chosen select list
                    $aSelList = $oBasketItem->getSelList();
                    if (is_array($aSelList) && ($aSelectlist = $oProduct->getSelectLists($sItemKey))) {
                        reset($aSelList);
                        while (list($conkey, $iSel) = each($aSelList)) {
                            $aSelectlist[$conkey][$iSel] = $aSelectlist[$conkey][$iSel];
                            $aSelectlist[$conkey][$iSel]->selected = 1;
                        }
                        $oProduct->setSelectlist($aSelectlist);
                    }
                }
            } catch (oxNoArticleException $oEx) {
                oxRegistry::get("oxUtilsView")->addErrorToDisplay($oEx);
                $this->removeItem($sItemKey);
                $this->calculateBasket(true);
                continue;
            } catch (oxArticleInputException $oEx) {
                oxRegistry::get("oxUtilsView")->addErrorToDisplay($oEx);
                $this->removeItem($sItemKey);
                $this->calculateBasket(true);
                continue;
            }

            $aBasketArticles[$sItemKey] = $oProduct;
        }

        return $aBasketArticles;
    }
}
