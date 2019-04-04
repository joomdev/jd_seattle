<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php
if (version_compare(PHP_VERSION, '5.4.0', '<')) {
    echo '<p style="color:red">This version of AcyMailing requires at least PHP 5.4.0, it is time to update the PHP version of your server!</p>';
    exit;
}

if (!include_once(rtrim(JPATH_ADMINISTRATOR, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acym'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php')) {
    echo "Could not load Acy helper file";

    return;
}

if (acym_isDebug()) {
    acym_displayErrors();
}

global $Itemid;
if (empty($Itemid)) {
    $urlItemid = acym_getVar('int', 'Itemid');
    if (!empty($urlItemid)) {
        $Itemid = $urlItemid;
    }
}

$ctrl = acym_getVar('cmd', 'ctrl', acym_getVar('cmd', 'view', ''));

if (!include_once(ACYM_CONTROLLER_FRONT.$ctrl.'.php')) {
    acym_redirect(acym_rootURI());

    return;
}
acym_setVar('ctrl', $ctrl);

$className = ucfirst($ctrl).'Controller';
$controller = new $className();

$task = acym_getVar('cmd', 'task', acym_getVar('cmd', 'layout', ''));
if (empty($task)) {
    $task = acym_getVar('cmd', 'defaulttask', $controller->defaulttask);
}
acym_setVar('task', $task);

acym_addScript(true, 'var ACYM_JS_TXT = '.acym_getJSMessages().';');

acym_addScript(false, ACYM_JS.'global.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'global.min.js'));
acym_addStyle(false, ACYM_CSS.'front_global.min.css?v='.filemtime(ACYM_MEDIA.'css'.DS.'front_global.min.css'));
acym_addScript(false, ACYM_JS.'front_global.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'front_global.min.js'));
if (file_exists(ACYM_MEDIA.'js'.DS.'front'.DS.$ctrl.'.min.js')) {
    acym_addScript(false, ACYM_JS.'front/'.$ctrl.'.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'front'.DS.$ctrl.'.min.js'));
}

$controller->loadScripts($task);
$controller->$task();
