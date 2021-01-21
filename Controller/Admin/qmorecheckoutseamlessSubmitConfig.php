<?php
/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/oxid-qcs/blob/master/LICENSE
*/
namespace Qenta\Controller\Admin;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Controller\Admin\AdminController;
class qmorecheckoutseamlessSubmitConfig extends AdminController
{
    protected $_sThisTemplate = 'qmorecheckoutseamlesssubmitconfig.tpl';

    protected $_aSupportMails = array('support@qenta.com');

    /**
     * Executes parent method parent::render() and returns name of template
     * file "qmorecheckoutseamlesssubmitconfig.tpl".
     *
     * @return string
     */

    public function render()
    {
        parent::render();

        $sCurrentAdminShop = Registry::getSession()->getVariable("currentadminshop");

        if (!$sCurrentAdminShop) {
            if (Registry::getSession()->getVariable("malladmin")) {
                $sCurrentAdminShop = "oxbaseshop";
            } else {
                $sCurrentAdminShop = Registry::getSession()->getVariable("actshop");
            }
        }

        $this->_aViewData["currentadminshop"] = $sCurrentAdminShop;
        Registry::getSession()->setVariable("currentadminshop", $sCurrentAdminShop);

        $recipient = Registry::getConfig()->getRequestParameter('qcs_config_export_recipient');
        $comment = Registry::getConfig()->getRequestParameter('qcs_config_export_description_text');
        $replyTo = Registry::getConfig()->getRequestParameter('qcs_config_export_reply_to_mail');

        $oSmarty = Registry::get("oxUtilsView")->getSmarty();
        $oSmarty->assign("aSupportMails", $this->_aSupportMails);
        if (!empty($recipient)) {
            $oSmarty->assign("sSupportMailActive", $recipient);
        }
        if (!empty($comment)) {
            $oSmarty->assign("sDescriptionText", $comment);
        }
        if (!empty($replyTo)) {
            $oSmarty->assign("sReplyTo", $replyTo);
        }

        return $this->_sThisTemplate;
    }

    public function getModuleConfig()
    {
        $oConfig = Registry::getConfig();
        $aModules = $oConfig->getConfigParam('aModulePaths');

        include('../modules/' . $aModules['qmorecheckoutseamless'] . '/metadata.php');

        foreach ($aModule['settings'] as $k => $aParams) {
            if ($aParams['name'] !== 'sWcpSecret') {
                $params[$aParams['name']] = $oConfig->getConfigParam($aParams['name']);
            } else {
                $params[$aParams['name']] = str_pad('', strlen($oConfig->getConfigParam($aParams['name'])), 'X');
            }
        }

        $moduleConfigString = "module extending classes\n";
        $moduleConfigString .= "------------------------\n";
        $moduleConfigString .= print_r($oConfig->getModulesWithExtendedClass(), 1) . "\n";
        $moduleConfigString .= "\n\nmodule config\n";
        $moduleConfigString .= "------------------------\n";
        $moduleConfigString .= 'id: ' . print_r($aModule['id'], 1) . "\n";
        $moduleConfigString .= 'title: ' . print_r($aModule['title'], 1) . "\n";
        $moduleConfigString .= 'version: ' . print_r($aModule['version'], 1) . "\n";
        $moduleConfigString .= print_r($params, 1) . "\n";

        return $moduleConfigString;
    }

    public function submit()
    {
        $recipient = Registry::getConfig()->getRequestParameter('qcs_config_export_recipient');
        $confString = $this->getModuleConfig();
        $comment = Registry::getConfig()->getRequestParameter('qcs_config_export_description_text');
        $replyTo = Registry::getConfig()->getRequestParameter('qcs_config_export_reply_to_mail');
        $oSmarty = Registry::get("oxUtilsView")->getSmarty();

        if (empty($recipient) || !in_array($recipient, $this->_aSupportMails)) {
            $oSmarty->assign("sErrorMessage", 'recipient invalid.');

            return;
        }

        $Mail = Registry::get('oxemail');
        $Mail->setFrom(Registry::getConfig()->getActiveShop()->oxshops__oxowneremail->rawValue,
            Registry::getConfig()->getActiveShop()->oxshops__oxname->rawValue);
        $Mail->setRecipient($recipient);
        $Mail->setBody('<p>' . $confString . '</p><p>' . $comment . '</p>');
        $Mail->setAltBody($confString . "\n\n" . $comment);
        $Mail->setSubject('OXID WCS Plugin Configuration from ' . Registry::getConfig()->getActiveShop()->oxshops__oxname->rawValue);
        if ($replyTo) {
            $Mail->setReplyTo($replyTo, "");
        }

        if ($Mail->send()) {
            $oSmarty->assign("sSuccessMessage", 'SUCCESS');
        }
    }
}
