<?php

/**
 * Shop System Plugins
 * - Terms of use can be found under
 * https://guides.qenta.com/shop_plugins:info
 * - License can be found under:
 * https://github.com/qenta-cee/oxid-qcs/blob/master/LICENSE
 */

ini_set('include_path', ini_get('include_path') . PATH_SEPARATOR . dirname(__FILE__) . '/vendor');

require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance()->registerNamespace("WirecardCEE");
