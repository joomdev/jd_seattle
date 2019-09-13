<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.2.2
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

$ctrl = acym_getVar('cmd', 'ctrl', 'dashboard');
$task = acym_getVar('cmd', 'task');

$config = acym_config();

if ((($config->get('migration') == 0 && acym_existsAcyMailing59() && acym_getVar('string', 'task') != 'migrationDone') || $config->get('walk_through') == 1) && !acym_isNoTemplate()) {
    $ctrl = 'dashboard';
}


if (!include_once(ACYM_CONTROLLER.$ctrl.'.php')) {
    acym_redirect(acym_completeLink('dashboard'));

    return;
}

$className = ucfirst($ctrl).'Controller';
$controller = new $className();

if (empty($task)) {
    $task = acym_getVar('cmd', 'defaulttask', $controller->defaulttask);
    acym_setVar('task', $task);
}

if (file_exists(ACYM_BACK.'extensions')) {
    $updateHelper = acym_get('helper.update');
    $updateHelper->installExtensions();
}

acym_addScript(
    true,
    'var TOGGLE_URL_ACYM = "index.php?option='.ACYM_COMPONENT.'&'.acym_noTemplate().'&ctrl=toggle&'.acym_getFormToken().'";
    var AJAX_URL_ACYM = "index.php?option='.ACYM_COMPONENT.'&'.acym_noTemplate().'&'.acym_getFormToken().'";
    var AJAX_URL_ACYBA = "'.ACYM_ACYWEBSITE.'";
    var MEDIA_URL_ACYM = "'.ACYM_MEDIA_URL.'";
    var CMS_ACYM = "'.ACYM_CMS.'";
    var FOUNDATION_FOR_EMAIL = "'.ACYM_CSS.'libraries/foundation_email.min.css?v='.filemtime(ACYM_MEDIA.'css'.DS.'libraries'.DS.'foundation_email.min.css').'";
    var ACYM_FIXES_FOR_EMAIL = "'.ACYM_CSS.'email.min.css?v='.filemtime(ACYM_MEDIA.'css'.DS.'email.min.css').'";
    var ACYM_REGEX_EMAIL = /^'.acym_getEmailRegex(true).'$/i;
    var ACYM_JS_TXT = '.acym_getJSMessages().';
    var ACYM_JOOMLA_MEDIA_IMAGE = "'.ACYM_LIVE.'";'
);

JHtml::_('jquery.framework');
acym_addScript(false, 'https://code.jquery.com/ui/1.11.4/jquery-ui.min.js');

acym_addScript(false, ACYM_JS.'libraries/foundation.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'libraries'.DS.'foundation.min.js'));
acym_addScript(false, ACYM_JS.'libraries/select2.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'libraries'.DS.'select2.min.js'));

acym_addStyle(false, 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');

acym_addStyle(false, ACYM_CSS.'libraries/introjs.min.css?v='.filemtime(ACYM_MEDIA.'css'.DS.'libraries'.DS.'introjs.min.css'));
acym_addScript(false, ACYM_JS.'libraries/intro.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'libraries'.DS.'intro.min.js'));

acym_addScript(false, ACYM_JS.'global.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'global.min.js'));
acym_addScript(false, ACYM_JS.'back_global.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'back_global.min.js'));
acym_addStyle(false, ACYM_CSS.'back_global.min.css?v='.filemtime(ACYM_MEDIA.'css'.DS.'back_global.min.css'));
if (file_exists(ACYM_MEDIA.'js'.DS.'back'.DS.$ctrl.'.min.js')) {
    acym_addScript(false, ACYM_JS.'back/'.$ctrl.'.min.js?v='.filemtime(ACYM_MEDIA.'js'.DS.'back'.DS.$ctrl.'.min.js'));
}

$controller->loadScripts($task);
$controller->$task();
