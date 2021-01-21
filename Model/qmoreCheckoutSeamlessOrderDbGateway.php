<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/oxid-qcs/blob/master/LICENSE
*/
namespace Qenta\Model;

use OxidEsales\Eshop\Core\Registry;

/**
 * Order db gateway class
 */
class qmoreCheckoutSeamlessOrderDbGateway
{
    /**
     * Save order to database
     *
     * @param array $aData
     *
     * @return bool
     */
    public function insert($aData)
    {
        $oDb = $this->_getDb();

        $aData['OXID'] = Registry::getUtilsObject()->generateUID();
        foreach ($aData as $sField => $sData) {
            $aSql[] = '`' . $sField . '` = ' . $oDb->quote($sData);
        }

        $sSql = 'INSERT INTO `qmorecheckoutseamless_order` SET ';
        $sSql .= implode(', ', $aSql);

        $oDb->execute($sSql);

        return $aData['OXID'];
    }

    /**
     *
     * @param string $sOrderId Order id.
     *
     * @return array
     */
    public function loadByOrderId($sOrderId)
    {
        $oDb = $this->_getDb();
        $aData = $oDb->getRow('SELECT * FROM `qmorecheckoutseamless_order` WHERE `OXORDERID` = ' . $oDb->quote($sOrderId));

        return $aData;
    }

    /**
     * @param string $sOxid .
     *
     * @return bool
     */
    public function delete($sOxid)
    {
        $oDb = $this->_getDb();

        $blResult = $oDb->execute('DELETE FROM `qmorecheckoutseamless_order` WHERE `OXID` = ' . $oDb->quote($sOxid));

        return $blResult;
    }

    /**
     * Returns data base resource.
     *
     * @return oxLegacyDb
     */
    protected function _getDb()
    {
        return DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);
    }

}
