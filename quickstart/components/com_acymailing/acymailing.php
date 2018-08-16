<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php
jimport('joomla.application.component.controller');
jimport('joomla.application.component.view');

include_once(rtrim(JPATH_ADMINISTRATOR, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acymailing'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php');

if(acymailing_isDebug()) acymailing_displayErrors();

$view = acymailing_getVar('cmd', 'view');
if(!empty($view) AND !acymailing_getVar('cmd', 'ctrl')){
	acymailing_setVar('ctrl', $view);
	$layout = acymailing_getVar('cmd', 'layout');
	if(!empty($layout)){
		acymailing_setVar('task', $layout);
	}
}
$taskGroup = acymailing_getVar('cmd', 'ctrl', acymailing_getVar('cmd', 'gtask', 'lists'));

global $Itemid;
if(empty($Itemid)){
	$urlItemid = acymailing_getVar('int', 'Itemid');
	if(!empty($urlItemid)) $Itemid = $urlItemid;
}


$config = acymailing_config();

acymailing_addScript(false, ACYMAILING_JS.'acymailing.js?v='.str_replace('.', '', $config->get('version')));

if(ACYMAILING_J16 && file_exists(ACYMAILING_ROOT.'media'.DS.'system'.DS.'js'.DS.'core.js')){
	$url = rtrim(acymailing_rootURI(), '/').'/media/system/js/core.js?v='.filemtime(ACYMAILING_ROOT.'media'.DS.'system'.DS.'js'.DS.'core.js');
	$js = 'document.addEventListener("DOMContentLoaded", function(){
		if(typeof Joomla == "undefined" && typeof window.Joomla == "undefined"){
			var script = document.createElement("script");
			script.type = "text/javascript";
			script.src = "'.$url.'";
			document.head.appendChild(script);
		}
	});';
	acymailing_addScript(true, $js);
}

$cssFrontend = $config->get('css_frontend', 'default');
if(!empty($cssFrontend)){
	acymailing_addStyle(false, ACYMAILING_CSS.'component_'.$cssFrontend.'.css?v='.filemtime(ACYMAILING_MEDIA.'css'.DS.'component_'.$cssFrontend.'.css'));
}

if($taskGroup == 'newsletter') $taskGroup = 'frontnewsletter';

if(!file_exists(ACYMAILING_CONTROLLER_FRONT.$taskGroup.'.php') || !include(ACYMAILING_CONTROLLER_FRONT.$taskGroup.'.php')){
	return acymailing_raiseError(E_ERROR, 404, 'Page not found : '.$taskGroup);
}

$className = ucfirst($taskGroup).'Controller';
$classGroup = new $className();
acymailing_setVar('view', $classGroup->getName());

$action = acymailing_getVar('cmd', 'task');
if(empty($action)){
	$action = acymailing_getVar('cmd', 'defaulttask');
	acymailing_setVar('task', $action);
}

$classGroup->execute($action);
$classGroup->redirect();
if(acymailing_getVar('string', 'tmpl') !== 'component' && !in_array(acymailing_getVar('cmd', 'task'), array('unsub', 'saveunsub', 'optout', 'out', 'view'))){
	echo acymailing_footer();
}
