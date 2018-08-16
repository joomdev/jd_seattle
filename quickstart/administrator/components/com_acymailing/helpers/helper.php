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

define('ACYMAILING_NAME', 'AcyMailing');
define('ACYMAILING_DBPREFIX', '#__acymailing_');
define('ACYMAILING_UPDATEURL', 'https://www.acyba.com/index.php?option=com_updateme&ctrl=update&task=');
define('ACYMAILING_SPAMURL', 'https://www.acyba.com/index.php?option=com_updateme&ctrl=spamsystem&task=');
define('ACYMAILING_HELPURL', 'https://www.acyba.com/index.php?option=com_updateme&ctrl=doc&component='.ACYMAILING_NAME.'&page=');
define('ACYMAILING_REDIRECT', 'https://www.acyba.com/index.php?option=com_updateme&ctrl=redirect&page=');
define('ACYMAILING_PLUGINURL', 'https://www.acyba.com/media/com_updateme/xmlfiles/');

if(!defined('DS')) define('DS', DIRECTORY_SEPARATOR);
include_once(rtrim(dirname(__DIR__),DS).DS.'compat'.DS.'joomla.php');

if(is_callable("date_default_timezone_set")) date_default_timezone_set(@date_default_timezone_get());

function acymailing_getEmailRegex($secureJS = false, $forceRegex = false){
	$config = acymailing_config();
	if($forceRegex || $config->get('special_chars', 0) == 0){
		$regex = '[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*\@([a-z0-9-]+\.)+[a-z0-9]{2,10}';
	}else{
		$regex = '.+\@(.+\.)+.{2,10}';
	}

	if($secureJS) $regex = str_replace(array('"', "'"), array('\"', "\'"), $regex);

	return $regex;
}

function acymailing_level($level){
    $config = acymailing_config();
    if($config->get($config->get('level'), 0) >= $level) return true;
    return false;
}

function acymailing_navigationTabs(){
    if(acymailing_isNoTemplate() || !acymailing_isAdmin() || !ACYMAILING_J40) return;

    $pages = array(
        'list' => array(
            'LISTS' => array('ctrl' => 'list', 'task' => ''),
            'ACY_DISTRIBUTION' => array('ctrl' => 'action', 'task' => '')
        ),
        'subscriber' => array(
            'ACY_SUBSCRIBER' => array('ctrl' => 'subscriber', 'task' => ''),
            'IMPORT' => array('ctrl' => 'data', 'task' => 'import'),
            'ACY_EXPORT' => array('ctrl' => 'data', 'task' => 'export'),
            'ACY_MASS_ACTIONS' => array('ctrl' => 'filter', 'task' => '')
        ),
        'newsletter' => array(
            'NEWSLETTERS' => array('ctrl' => 'newsletter', 'task' => ''),
            'AUTONEWSLETTERS' => array('ctrl' => 'autonews', 'task' => ''),
            'ACY_CAMPAIGNS' => array('ctrl' => 'campaign', 'task' => ''),
            'QUEUE' => array('ctrl' => 'queue', 'task' => ''),
            'SIMPLE_SENDING' => array('ctrl' => 'simplemail', 'task' => 'edit'),
            'ACY_TEMPLATES' => array('ctrl' => 'template', 'task' => '')
        ),
        'stats' => array(
            'STATISTICS' => array('ctrl' => 'stats', 'task' => 'listing'),
            'DETAILED_STATISTICS' => array('ctrl' => 'stats', 'task' => 'detaillisting'),
            'CLICK_STATISTICS' => array('ctrl' => 'statsurl', 'task' => ''),
            'CHARTS' => array('ctrl' => 'diagram', 'task' => '')
        ),
        'cpanel' => array(
            'ACY_CONFIGURATION' => array('ctrl' => 'cpanel', 'task' => ''),
            'EXTRA_FIELDS' => array('ctrl' => 'fields', 'task' => ''),
            'BOUNCE_HANDLING' => array('ctrl' => 'bounces', 'task' => '')
        )
    );

    $ctrl = acymailing_getVar('cmd', 'ctrl');
    $task = acymailing_getVar('cmd', 'task');

    $page = str_replace('acymailing_', '', acymailing_getVar('cmd', 'page', ''));

    if(empty($page)){
        foreach($pages as $mainCtrl => $siblings){
            foreach($siblings as $oneSibling){
                if($oneSibling['ctrl'] == $ctrl){
                    $page = $mainCtrl;
                    break;
                }
            }

            if(!empty($page)) break;
        }
    }
    if(empty($pages[$page])) return;

	$config = acymailing_config();
    $navigationTabs = array();
    foreach($pages[$page] as $text => $oneCtrl){
        $active = false;

        if($oneCtrl['ctrl'] == $ctrl && (empty($oneCtrl['task']) || $oneCtrl['task'] == $task || (empty($task) && $oneCtrl['task'] == 'listing'))) $active = true;

		if($text == 'DETAILED_STATISTICS' && $config->get('anonymous_tracking', 0) == 1) continue;

        $navigationTabs[] = '<li'.($active ? ' class="active"' : '').'><a href="' . acymailing_completeLink($oneCtrl['ctrl']). (empty($oneCtrl['task']) ? '' : '&task='.$oneCtrl['task']) . '">' . acymailing_translation($text) . '</a></li>';
    }

    echo '<div class="acytabsystem"><ul class="acynavigationtabs nav nav-tabs">'.implode('', $navigationTabs).'</ul></div>';
}

function acymailing_getDate($time = 0, $format = '%d %B %Y %H:%M'){
	if(empty($time)) return '';

	if(is_numeric($format)) $format = acymailing_translation('DATE_FORMAT_LC'.$format);
	if(ACYMAILING_J16){
		$format = str_replace(array('%A', '%d', '%B', '%m', '%Y', '%y', '%H', '%M', '%S', '%a', '%I', '%p', '%w'), array('l', 'd', 'F', 'm', 'Y', 'y', 'H', 'i', 's', 'D', 'h', 'a', 'w'), $format);
		try{
			return acymailing_date($time, $format, false);
		}catch(Exception $e){
			return date($format, $time);
		}
	}else{
		static $timeoffset = null;
		if($timeoffset === null){
			$timeoffset = acymailing_getCMSConfig('offset');
		}
		return acymailing_date($time - date('Z'), $format, $timeoffset);
	}
}

function acymailing_isRobot(){
	if(empty($_SERVER)) return false;
	if(!empty($_SERVER['HTTP_USER_AGENT']) && strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'spambayes') !== false) return true;
	if(!empty($_SERVER['REMOTE_ADDR']) && version_compare($_SERVER['REMOTE_ADDR'], '64.235.144.0', '>=') && version_compare($_SERVER['REMOTE_ADDR'], '64.235.159.255', '<=')) return true;

	return false;
}

function acymailing_isAllowed($allowedGroups, $groups = null){
	if($allowedGroups == 'all') return true;
	if($allowedGroups == 'none') return false;
	if(!is_array($allowedGroups)) $allowedGroups = explode(',', trim($allowedGroups, ','));

	$currentUserid = acymailing_currentUserId();
	if(empty($currentUserid) && empty($groups) && in_array('nonloggedin', $allowedGroups)) return true;

	if(empty($groups) && empty($currentUserid)) return false;
	if(empty($groups)) $groups = acymailing_getGroupsByUser($currentUserid, false);

	if(!is_array($groups)) $groups = array($groups);
	$inter = array_intersect($groups, $allowedGroups);
	if(empty($inter)) return false;
	return true;
}

function acymailing_getFunctionsEmailCheck($controllButtons = array(), $bounce = false){
	$addressCheck = '!emailAddress.match(/^'.acymailing_getEmailRegex(true).'((,|;)'.acymailing_getEmailRegex(true).')*$/i)';

	$return = '<script language="javascript" type="text/javascript">
				function validateEmail(emailAddress, fieldName){
					if(emailAddress.length > 0 && emailAddress.indexOf("{") == -1 && '.$addressCheck.'){
						alert("Wrong email address supplied for the " + fieldName + " field: " + emailAddress);
						return false;
					}
					return true;
				}';

	if(!empty($controllButtons)){
		foreach($controllButtons as &$oneField){
			$oneField = 'pressbutton == \''.$oneField.'\'';
		}

		$return .= '
		document.addEventListener("DOMContentLoaded", function(){
			acymailing.submitbutton = function(pressbutton){
				if('.implode(' || ', $controllButtons).'){
					var emailVars = ["fromemail","replyemail"'.($bounce ? ',"bounceemail"' : '').'];
					var val = "";
					for(var key in emailVars){
						if(isNaN(key)) continue;
						val = document.getElementById(emailVars[key]).value;
						if(!validateEmail(val, emailVars[key])){
							return;
						}
					}
				}
				acymailing.submitform(pressbutton,document.adminForm);
			};
		});';
	}

	$return .= '
				</script>';

	return $return;
}

function acymailing_loadLanguage(){
	acymailing_loadLanguageFile(ACYMAILING_COMPONENT, ACYMAILING_ROOT, null, true);
	acymailing_loadLanguageFile(ACYMAILING_COMPONENT.'_custom', ACYMAILING_ROOT, null, true);
}

function acymailing_createDir($dir, $report = true, $secured = false){
	if(is_dir($dir)) return true;

	$indexhtml = '<html><body bgcolor="#FFFFFF"></body></html>';

	try{
		$status = acymailing_createFolder($dir);
	}catch(Exception $e){
		$status = false;
	}

	if(!$status){
		if($report) acymailing_display('Could not create the directory '.$dir, 'error');
		return false;
	}

	try{
		$status = acymailing_writeFile($dir.DS.'index.html', $indexhtml);
	}catch(Exception $e){
		$status = false;
	}

	if(!$status){
		if($report) acymailing_display('Could not create the file '.$dir.DS.'index.html', 'error');
	}

	if($secured){
		try{
			$htaccess = 'Order deny,allow'."\r\n".'Deny from all';
			$status = acymailing_writeFile($dir.DS.'.htaccess', $htaccess);
		}catch(Exception $e){
			$status = false;
		}

		if(!$status){
			if($report) acymailing_display('Could not create the file '.$dir.DS.'.htaccess', 'error');
		}
	}

	return $status;
}

function acymailing_getUpgradeLink($tolevel){
	$config = acymailing_config();
	return ' <a class="acyupgradelink" href="'.ACYMAILING_REDIRECT.'upgrade-acymailing-'.$config->get('level').'-to-'.$tolevel.'" target="_blank">'.acymailing_translation('ONLY_FROM_'.strtoupper($tolevel)).'</a>';
}

function acymailing_replaceDate($mydate){

	if(strpos($mydate, '{time}') === false) return $mydate;

	$mydate = str_replace('{time}', time(), $mydate);
	$operators = array('+', '-');
	foreach($operators as $oneOperator){
		if(!strpos($mydate, $oneOperator)) continue;
		list($part1, $part2) = explode($oneOperator, $mydate);
		if($oneOperator == '+'){
			$mydate = trim($part1) + trim($part2);
		}elseif($oneOperator == '-'){
			$mydate = trim($part1) - trim($part2);
		}
	}

	return $mydate;
}

function acymailing_initJSStrings($includejs = 'header', $params = null){
	static $alreadyThere = false;
	if($alreadyThere && $includejs == 'header') return;
	$alreadyThere = true;

	if(method_exists($params, 'get')){
		$nameCaption = $params->get('nametext');
		$emailCaption = $params->get('emailtext');
	}
	if(empty($nameCaption)) $nameCaption = acymailing_translation('NAMECAPTION');
	if(empty($emailCaption)) $emailCaption = acymailing_translation('EMAILCAPTION');

	$js = "	if(typeof acymailingModule == 'undefined'){
				var acymailingModule = Array();
			}
			
			acymailingModule['emailRegex'] = /^".acymailing_getEmailRegex(true)."$/i;

			acymailingModule['NAMECAPTION'] = '".str_replace("'", "\'", $nameCaption)."';
			acymailingModule['NAME_MISSING'] = '".str_replace("'", "\'", acymailing_translation('NAME_MISSING'))."';
			acymailingModule['EMAILCAPTION'] = '".str_replace("'", "\'", $emailCaption)."';
			acymailingModule['VALID_EMAIL'] = '".str_replace("'", "\'", acymailing_translation('VALID_EMAIL'))."';
			acymailingModule['ACCEPT_TERMS'] = '".str_replace("'", "\'", acymailing_translation('ACCEPT_TERMS'))."';
			acymailingModule['CAPTCHA_MISSING'] = '".str_replace("'", "\'", acymailing_translation('ERROR_CAPTCHA'))."';
			acymailingModule['NO_LIST_SELECTED'] = '".str_replace("'", "\'", acymailing_translation('NO_LIST_SELECTED'))."';
		";
	if($includejs == 'header'){
		acymailing_addScript(true, $js);
	}else{
		echo "<script type=\"text/javascript\">
					<!--
					$js
					//-->
				</script>";
	}
}

function acymailing_generateKey($length){
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$randstring = '';
	$max = strlen($characters) - 1;
	for($i = 0; $i < $length; $i++){
		$randstring .= $characters[mt_rand(0, $max)];
	}
	return $randstring;
}

function acymailing_absoluteURL($text){
	static $mainurl = '';
	if(empty($mainurl)){
		$urls = parse_url(ACYMAILING_LIVE);
		if(!empty($urls['path'])){
			$mainurl = substr(ACYMAILING_LIVE, 0, strrpos(ACYMAILING_LIVE, $urls['path'])).'/';
		}else{
			$mainurl = ACYMAILING_LIVE;
		}
	}

	$text = str_replace(array('href="../undefined/', 'href="../../undefined/', 'href="../../../undefined//', 'href="undefined/', ACYMAILING_LIVE.'http://', ACYMAILING_LIVE.'https://'), array('href="'.$mainurl, 'href="'.$mainurl, 'href="'.$mainurl, 'href="'.ACYMAILING_LIVE, 'http://', 'https://'), $text);
	$text = preg_replace('#href="(/?administrator)?/({|%7B)#Ui', 'href="$2', $text);

	$text = preg_replace('#href="http:/([^/])#Ui', 'href="http://$1', $text);

	$text = preg_replace('#href="'.preg_quote(str_replace(array('http://', 'https://'), '', $mainurl), '#').'#Ui', 'href="'.$mainurl, $text);

	$replace = array();
	$replaceBy = array();
	if($mainurl !== ACYMAILING_LIVE){

		$replace[] = '#(href|src|action|background)[ ]*=[ ]*\"(?!(\{|%7B|\[|\#|\\\\|[a-z]{3,15}:|/))(?:\.\./)#i';
		$replaceBy[] = '$1="'.substr(ACYMAILING_LIVE, 0, strrpos(rtrim(ACYMAILING_LIVE, '/'), '/') + 1);


		$subfolder = substr(ACYMAILING_LIVE, strrpos(rtrim(ACYMAILING_LIVE, '/'), '/'));
		$replace[] = '#(href|src|action|background)[ ]*=[ ]*\"'.preg_quote($subfolder, '#').'(\{|%7B)#i';
		$replaceBy[] = '$1="$2';
	}
	$replace[] = '#(href|src|action|background)[ ]*=[ ]*\"(?!(\{|%7B|\[|\#|\\\\|[a-z]{3,15}:|/))(?:\.\./|\./)?#i';
	$replaceBy[] = '$1="'.ACYMAILING_LIVE;
	$replace[] = '#(href|src|action|background)[ ]*=[ ]*\"(?!(\{|%7B|\[|\#|\\\\|[a-z]{3,15}:))/#i';
	$replaceBy[] = '$1="'.$mainurl;

	$replace[] = '#((background-image|background)[ ]*:[ ]*url\(\'?"?(?!(\\\\|[a-z]{3,15}:|/|\'|"))(?:\.\./|\./)?)#i';
	$replaceBy[] = '$1'.ACYMAILING_LIVE;

	return preg_replace($replace, $replaceBy, $text);
}

function acymailing_mainURL(&$link){
    static $mainurl = '';
    static $otherarguments = false;
    if(empty($mainurl)){
        $urls = parse_url(ACYMAILING_LIVE);
        if(isset($urls['path']) AND strlen($urls['path']) > 0){
            $mainurl = substr(ACYMAILING_LIVE, 0, strrpos(ACYMAILING_LIVE, $urls['path'])).'/';
            $otherarguments = trim(str_replace($mainurl, '', ACYMAILING_LIVE), '/');
            if(strlen($otherarguments) > 0) $otherarguments .= '/';
        }else{
            $mainurl = ACYMAILING_LIVE;
        }
    }

    if($otherarguments && strpos($link, $otherarguments) === false) $link = $otherarguments.$link;

    return $mainurl;
}

function acymailing_bytes($val){
	$val = trim($val);
	if(empty($val)){
		return 0;
	}
	$last = strtolower($val[strlen($val) - 1]);
	switch($last){
		case 'g':
			$val = intval($val) * 1073741824;
		case 'm':
			$val = intval($val) * 1048576;
		case 'k':
			$val = intval($val) * 1024;
	}

	return (int)$val;
}

function acymailing_display($messages, $type = 'success', $close = false){
	if(empty($messages)) return;

	if(!is_array($messages)) $messages = array($messages);
	if(ACYMAILING_J30 || acymailing_isAdmin()){
		if(acymailing_isAdmin() && !acymailing_isNoTemplate()) echo '<div style="padding:1px;">';
		echo '<div id="acymailing_messages_'.$type.'" class="alert alert-'.$type.' alert-block">';
		if($close && ACYMAILING_J30) echo '<button type="button" class="close" data-dismiss="alert">×</button>';
		echo '<p>'.implode('</p><p>', $messages).'</p></div>';
		if(acymailing_isAdmin() && !acymailing_isNoTemplate()) echo '</div>';
	}else{
		echo '<div id="acymailing_messages_'.$type.'" class="acymailing_messages acymailing_'.$type.'"><ul><li>'.implode('</li><li>', $messages).'</li></ul></div>';
	}
}

function acymailing_table($name, $component = true){
	$prefix = $component ? ACYMAILING_DBPREFIX : '#__';
	return $prefix.$name;
}

function acymailing_secureField($fieldName){
	if(!is_string($fieldName) OR preg_match('|[^a-z0-9#_.-]|i', $fieldName) !== 0){
		die('field "'.htmlspecialchars($fieldName, ENT_COMPAT, 'UTF-8').'" not secured');
	}
	return $fieldName;
}

function acymailing_displayErrors(){
	error_reporting(E_ALL);
	@ini_set("display_errors", 1);
}

function acymailing_increasePerf(){
	@ini_set('max_execution_time', 600);
	@ini_set('pcre.backtrack_limit', 1000000);
}

function acymailing_config($reload = false){
	static $configClass = null;
	if($configClass === null || $reload){
		$configClass = acymailing_get('class.cpanel');
		$configClass->load();
	}
	return $configClass;
}

function acymailing_listingsearch($search){
	$searchBar = '<div class="filter-search">';
	$searchBar .= '<input placeholder="'.acymailing_translation('ACY_SEARCH').'" type="text" name="search" id="search" value="'.htmlspecialchars($search, ENT_COMPAT, 'UTF-8').'" class="text_area" title="'.acymailing_translation('ACY_SEARCH').'"/>';
	$searchBar .= '<button style="float:none;" onclick="document.adminForm.task.value=\'\';document.adminForm.limitstart.value=0;this.form.submit();" class="btn tip hasTooltip" type="submit" title="'.acymailing_translation('ACY_SEARCH').'"><i class="acyicon-search"></i></button>';
	$searchBar .= '<button style="float:none;margin-left:0px;" onclick="document.adminForm.task.value=\'\';document.adminForm.limitstart.value=0;document.getElementById(\'search\').value=\'\';this.form.submit();" class="btn tip hasTooltip" type="button" title="'.acymailing_translation('JOOMEXT_RESET').'"><i class="acyicon-cancel"></i></button>';
	$searchBar .= '</div>';
	echo $searchBar;
}

function acymailing_getModuleFormName(){
	static $i = 1;
	return 'formAcymailing'.rand(1000, 9999).$i++;
}

function acymailing_initModule($params){
	$includejs = 'header';
	if(method_exists($params, 'get')) $includejs = $params->get('includejs', 'header');

	static $alreadyThere = false;
	if($alreadyThere && $includejs == 'header') return;

	$alreadyThere = true;

	acymailing_initJSStrings($includejs, $params);
	$config = acymailing_config();
	if($includejs == 'header'){
		if(ACYMAILING_J16){
			acymailing_addScript(false, ACYMAILING_JS.'acymailing_module.js?v='.str_replace('.', '', $config->get('version')), 'text/javascript', false, true);
		}else{
			acymailing_addScript(false, ACYMAILING_JS.'acymailing_module.js?v='.str_replace('.', '', $config->get('version')));
		}
	}else{
		echo "\n".'<script type="text/javascript" src="'.ACYMAILING_JS.'acymailing_module.js?v='.str_replace('.', '', $config->get('version')).'" ></script>'."\n";
	}

	$moduleCSS = $config->get('css_module', 'default');
	if(!empty($moduleCSS)){
		if($includejs == 'header'){
			acymailing_addStyle(false, ACYMAILING_CSS.'module_'.$moduleCSS.'.css?v='.filemtime(ACYMAILING_MEDIA.'css'.DS.'module_'.$moduleCSS.'.css'));
		}else{
			echo "\n".'<link rel="stylesheet" property="stylesheet" href="'.ACYMAILING_CSS.'module_'.$moduleCSS.'.css?v='.filemtime(ACYMAILING_MEDIA.'css'.DS.'module_'.$moduleCSS.'.css').'" type="text/css" />'."\n";
		}
	}
}

function acymailing_footer(){
	$config = acymailing_config();
	$description = ACYMAILING_CMS.' E-mail Marketing';
	$text = '<!-- '.ACYMAILING_NAME.' Component powered by http://www.acyba.com -->
		<!-- version '.$config->get('level').' : '.$config->get('version').' -->';
	if(acymailing_level(1) && !acymailing_level(4)) return $text;
	$level = $config->get('level');
	$text .= '<div class="acymailing_footer" align="center" style="text-align:center"><a href="https://www.acyba.com/?utm_source=acymailing-'.$level.'&utm_medium=front-end&utm_content=txt&utm_campaign=powered-by" target="_blank" title="'.ACYMAILING_NAME.' : '.str_replace('TM ', ' ', strip_tags($description)).'">'.ACYMAILING_NAME;
	$text .= ' - '.$description.'</a></div>';
	return $text;
}

function acymailing_dispSearch($string, $searchString){
	$secString = htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
	if(strlen($searchString) == 0) return $secString;
	return preg_replace('#('.preg_quote($searchString, '#').')#i', '<span class="searchtext">$1</span>', $secString);
}

function acymailing_perf($name){
	static $previoustime = 0;
	static $previousmemory = 0;
	static $file = '';

	if(empty($file)){
		$file = ACYMAILING_ROOT.'acydebug_'.rand().'.txt';
		$previoustime = microtime(true);
		$previousmemory = memory_get_usage();
		file_put_contents($file, "\r\n\r\n-- new test : ".$name." -- ".date('d M H:i:s')." from ".@$_SERVER['REMOTE_ADDR'], FILE_APPEND);
		return;
	}

	$nowtime = microtime(true);
	$totaltime = $nowtime - $previoustime;
	$previoustime = $nowtime;

	$nowmemory = memory_get_usage();
	$totalmemory = $nowmemory - $previousmemory;
	$previousmemory = $nowmemory;

	file_put_contents($file, "\r\n".$name.' : '.number_format($totaltime, 2).'s - '.$totalmemory.' / '.memory_get_usage(), FILE_APPEND);
}

function acymailing_search($searchString, $object){

	if(empty($object) || is_numeric($object)) return $object;

	if(is_string($object)){
		return preg_replace('#('.str_replace('#', '\#', $searchString).')#i', '<span class="searchtext">$1</span>', $object);
	}

	if(is_array($object)){
		foreach($object as $key => $element){
			$object[$key] = acymailing_search($searchString, $element);
		}
	}elseif(is_object($object)){
		foreach($object as $key => $element){
			$object->$key = acymailing_search($searchString, $element);
		}
	}

	return $object;
}

function acymailing_get($path){
	list($group, $class) = explode('.', $path);
	if($group == 'helper' && $class == 'user') $class = 'acyuser';
	if($group == 'helper' && $class == 'mailer') $class = 'acymailer';

	$className = $class.ucfirst(str_replace('_front', '', $group));
	if($group == 'helper' && strpos($className, 'acy') !== 0) $className = 'acy'.$className;

	if(substr($group, 0, 4) == 'view'){
		$className = $className.ucfirst($class);
		$class .= DS.'view.html';
	}

	if(!class_exists($className)) include(constant(strtoupper('ACYMAILING_'.$group)).$class.'.php');

	if(!class_exists($className)) return null;
	return new $className();
}

function acymailing_getCID($field = ''){
	$oneResult = acymailing_getVar('array', 'cid', array(), '');
	$oneResult = intval(reset($oneResult));
	if(!empty($oneResult) || empty($field)) return $oneResult;

	$oneResult = acymailing_getVar('int', $field, 0, '');
	return intval($oneResult);
}

function acymailing_checkRobots(){
	if(preg_match('#(libwww-perl|python|googlebot)#i', @$_SERVER['HTTP_USER_AGENT'])) die('Not allowed for robots. Please contact us if you are not a robot');
}

function acymailing_removeChzn($eltsToClean){
	if(!ACYMAILING_J30) return;

	$js = ' function removeChosen(){';
	foreach($eltsToClean as $elt){
		$js .= 'jQuery("#'.$elt.' .chzn-container").remove();
					jQuery("#'.$elt.' .chzn-done").removeClass("chzn-done").show();
					';
	}
	$js .= '}
		document.addEventListener("DOMContentLoaded", function(){removeChosen();
			setTimeout(function(){
				removeChosen();
		}, 100);});';
	acymailing_addScript(true, $js);
}

function acymailing_importFile($file, $uploadPath, $onlyPict, $maxwidth = ''){
	acymailing_checkToken();

	$config = acymailing_config();
	$additionalMsg = '';

	if($file["error"] > 0){
		$file["error"] = intval($file["error"]);
		if($file["error"] > 8) $file["error"] = 0;

		$phpFileUploadErrors = array(
			0 => 'Unknown error',
			1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
			2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
			3 => 'The uploaded file was only partially uploaded',
			4 => 'No file was uploaded',
			6 => 'Missing a temporary folder',
			7 => 'Failed to write file to disk',
			8 => 'A PHP extension stopped the file upload'
		);

		acymailing_display("Error Uploading file: ".$phpFileUploadErrors[$file["error"]], 'error');
		return false;
	}

	acymailing_createDir($uploadPath, true);

	if(!is_writable($uploadPath)){
		@chmod($uploadPath, '0755');
		if(!is_writable($uploadPath)){
			acymailing_display(acymailing_translation_sprintf('WRITABLE_FOLDER', $uploadPath), 'error');
			return false;
		}
	}

	if($onlyPict){
		$allowedExtensions = array('png', 'jpeg', 'jpg', 'gif', 'ico', 'bmp');
	}else{
		$allowedExtensions = explode(',', $config->get('allowedfiles'));
	}

	if(!preg_match('#\.('.implode('|', $allowedExtensions).')$#Ui', $file["name"], $extension)){
		$ext = substr($file["name"], strrpos($file["name"], '.') + 1);
		acymailing_display(acymailing_translation_sprintf('ACCEPTED_TYPE', htmlspecialchars($ext, ENT_COMPAT, 'UTF-8'), implode(', ', $allowedExtensions)), 'error');
		return false;
	}

	if(preg_match('#\.(php.?|.?htm.?|pl|py|jsp|asp|sh|cgi)#Ui', $file["name"])){
		acymailing_display('This extension name is blocked by the system regardless your configuration for security reasons', 'error');
		return false;
	}

	$file["name"] = preg_replace('#[^a-z0-9]#i', '_', strtolower(substr($file["name"], 0, strrpos($file["name"], '.')))).'.'.$extension[1];

	if($onlyPict){
		$imageSize = getimagesize($file['tmp_name']);
		if(empty($imageSize)){
			acymailing_display('Invalid image', 'error');
			return false;
		}
	}

	if(file_exists($uploadPath.DS.$file["name"])){
		$i = 1;
		$nameFile = preg_replace("/\\.[^.\\s]{3,4}$/", "", $file["name"]);
		$ext = substr($file["name"], strrpos($file["name"], '.') + 1);
		while(file_exists($uploadPath.DS.$nameFile.'_'.$i.'.'.$ext)){
			$i++;
		}

		$file["name"] = $nameFile.'_'.$i.'.'.$ext;
		$additionalMsg = '<br />'.acymailing_translation_sprintf('FILE_RENAMED', $file["name"]);
		if($onlyPict) $additionalMsg .= '<br /><a style="color: blue; cursor: pointer;" onclick="confirmBox(\'rename\', \''.$file['name'].'\', \''.$nameFile.'.'.$ext.'\')">'.acymailing_translation('ACY_RENAME_OR_REPLACE').'</a>';
	}

	if(!acymailing_uploadFile($file["tmp_name"], rtrim($uploadPath, DS).DS.$file["name"])){
		if(!move_uploaded_file($file["tmp_name"], rtrim($uploadPath, DS).DS.$file["name"])){
			acymailing_display(acymailing_translation_sprintf('FAIL_UPLOAD', '<b><i>'.htmlspecialchars($file["tmp_name"], ENT_COMPAT, 'UTF-8').'</i></b>', '<b><i>'.htmlspecialchars(rtrim($uploadPath, DS).DS.$file["name"], ENT_COMPAT, 'UTF-8').'</i></b>'), 'error');
			return false;
		}
	}

	if(!empty($maxwidth) || ($onlyPict && $imageSize[0] > 1000)){
		$pictureHelper = acymailing_get('helper.acypict');
		if($pictureHelper->available()){
			$pictureHelper->maxHeight = 9999;
			if(empty($maxwidth)){
				$pictureHelper->maxWidth = 700;
				$message = 'IMAGE_RESIZED';
			}else{
				$pictureHelper->maxWidth = $maxwidth;
				$message = 'ACY_IMAGE_RESIZED';
			}
			$pictureHelper->destination = $uploadPath;
			$thumb = $pictureHelper->generateThumbnail(rtrim($uploadPath, DS).DS.$file["name"], $file["name"]);
			$resize = acymailing_moveFile($thumb['file'], $uploadPath.DS.$file["name"]);
			if($thumb) $additionalMsg .= '<br />'.acymailing_translation($message);
		}
	}
	acymailing_display('<strong>'.acymailing_translation('SUCCESS_FILE_UPLOAD').'</strong>'.$additionalMsg, 'success');
	return $file["name"];
}

function acymailing_getFilesFolder($folder = 'upload', $multipleFolders = false){
	$listClass = acymailing_get('class.list');
	if(acymailing_isAdmin()){
		$allLists = $listClass->getLists('listid');
	}else{
		$allLists = $listClass->getFrontendLists('listid');
	}
	$newFolders = array();

	$config = acymailing_config();
	if($folder == 'upload'){
		$uploadFolder = $config->get('uploadfolder', ACYMAILING_MEDIA_FOLDER.'/upload');
	}else{
		$uploadFolder = $config->get('mediafolder', ACYMAILING_MEDIA_FOLDER.'/upload');
	}

	$folders = explode(',', $uploadFolder);

	foreach($folders as $k => $folder){
		$folders[$k] = trim($folder, '/');
		if(strpos($folder, '{userid}') !== false) $folders[$k] = str_replace('{userid}', acymailing_currentUserId(), $folders[$k]);

		if(strpos($folder, '{listalias}') !== false){
			if(empty($allLists)){
				$noList = new stdClass();
				$noList->alias = 'none';
				$allLists = array($noList);
			}

			foreach($allLists as $oneList){
				$newFolders[] = str_replace('{listalias}', strtolower(str_replace(array(' ', '-'), '_', $oneList->alias)), $folders[$k]);
			}

			$folders[$k] = '';
			continue;
		}

		if(strpos($folder, '{groupid}') !== false || strpos($folder, '{groupname}') !== false){
			$groups = acymailing_getGroupsByUser(acymailing_currentUserId(), false);
			acymailing_arrayToInteger($groups);

			if(ACYMAILING_J16){
				$completeGroups = acymailing_loadObjectList('SELECT id, title FROM #__usergroups WHERE id IN ('.implode(',', $groups).')');
			}else{
				$groupObject = new stdClass();
				$groupObject->id = $groups[0];
				$groupObject->title = acymailing_getGroupsByUser();
				$completeGroups = array($groupObject);
			}

			foreach($completeGroups as $group){
				$newFolders[] = str_replace(array('{groupid}', '{groupname}'), array($group->id, strtolower(str_replace(' ', '_', $group->title))), $folders[$k]);
			}

			$folders[$k] = '';
		}
	}

	$folders = array_merge($folders, $newFolders);
	$folders = array_filter($folders);
	sort($folders);
	if($multipleFolders){
		return $folders;
	}else{
		return array_shift($folders);
	}
}

function acymailing_generateArborescence($folders){
	$folderList = array();
	foreach($folders as $folder){
		$folderPath = acymailing_cleanPath(ACYMAILING_ROOT.trim(str_replace('/', DS, trim($folder)), DS));
		if(!file_exists($folderPath)) acymailing_createDir($folderPath);
		$subFolders = acymailing_listFolderTree($folderPath, '', 15);
		$folderList[$folder] = array();
		foreach($subFolders as $oneFolder){
			$subFolder = str_replace(ACYMAILING_ROOT, '', $oneFolder['relname']);
			$subFolder = str_replace(DS, '/', $subFolder);
			$folderList[$folder][$subFolder] = ltrim($subFolder, '/');
		}
		$folderList[$folder] = array_unique($folderList[$folder]);
	}
	return $folderList;
}

function acymailing_arrayToInteger(&$array){
	if(is_array($array)){
		$array = array_map('intval', $array);
	}else{
		$array = array();
	}
}

function acymailing_arrayToString($array, $inner_glue = '=', $outer_glue = ' ', $keepOuterKey = false){
	$output = array();

	foreach($array as $key => $item){
		if(is_array($item)){
			if($keepOuterKey) $output[] = $key;

			$output[] = acymailing_arrayToString($item, $inner_glue, $outer_glue, $keepOuterKey);
		}else{
			$output[] = $key.$inner_glue.'"'.$item.'"';
		}
	}

	return implode($outer_glue, $output);
}

function acymailing_makeSafeFile($file){
	$file = rtrim($file, '.');
	$regex = array('#(\.){2,}#', '#[^A-Za-z0-9\.\_\- ]#', '#^\.#');
	return trim(preg_replace($regex, '', $file));
}

function acymailing_sortablelist($table, $ordering){
	acymailing_addScript(false, ACYMAILING_JS.'sortable.js?v='.@filemtime(ACYMAILING_MEDIA.'js'.DS.'sortable.js'));

	$js = "
		document.addEventListener(\"DOMContentLoaded\", function(event) {
			Sortable.create(document.getElementById('acymailing_sortable_listing'), {
				handle: '.acyicon-draghandle',
				animation: 150,
				dataIdAttr: 'acyorderid',
				ghostClass: 'acysortable-ghost',
				store: {
					set: function (sortable) {
						var cid = sortable.toArray();
						var order = [".$ordering."];
						
						var xhr = new XMLHttpRequest();
						xhr.open('GET', '".acymailing_prepareAjaxURL($table)."&task=saveorder&'+cid.join('&')+'&'+order.join('&')+'&".acymailing_getFormToken()."');
						xhr.send();
					}
				}
			});
		});";

	acymailing_addScript(true, $js);
}

function acymailing_tooltip($desc, $title = '', $image = 'tooltip.png', $name = '', $href = '', $alt = ''){
	static $loaded = false;

	if(!$loaded) {
		acymailing_addScript(false, ACYMAILING_JS.'acymailing.js?v='.filemtime(ACYMAILING_MEDIA.'js'.DS.'acymailing.js'));
		acymailing_addStyle(false, ACYMAILING_CSS.'acytooltip.css?v='.filemtime(ACYMAILING_MEDIA.'css'.DS.'acytooltip.css'));
		$loaded = true;
	}

	$content = $desc;
	if(!empty($title)) $content = '<span style="font-weight: bold;">'.$title.'</span><br/>'.$content;
	if(empty($name)) $name = '<img alt="" src="'.ACYMAILING_IMAGES.$image.'"/>';
	if(!empty($href)) $name = '<a href="'.$href.'" alt="'.htmlspecialchars($alt, ENT_QUOTES, 'UTF-8').'"">'.$name.'</a>';

	return '<span class="acymailingtooltip"><span class="acymailingtooltiptext">'.$content.'</span>'.$name.'</span>';
}

function acymailing_deleteFolder($path){
	$path = acymailing_cleanPath($path);
	if(!is_dir($path)){
		acymailing_enqueueMessage($path.' is not a folder', 'error');
		return false;
	}
	$files = acymailing_getFiles($path);
	if(!empty($files)){
		foreach($files as $oneFile){
			if(!acymailing_deleteFile($path.DS.$oneFile)) return false;
		}
	}

	$folders = acymailing_getFolders($path);
	if(!empty($folders)){
		foreach($folders as $oneFolder){
			if(!acymailing_deleteFolder($path.DS.$oneFolder)) return false;
		}
	}

	if (@rmdir($path)){
		$ret = true;
	}else{
		acymailing_enqueueMessage('Could not delete folder '.$path, 'error');
		$ret = false;
	}

	return $ret;
}

function acymailing_createFolder($path = '', $mode = 0755){
	$path = acymailing_cleanPath($path);
	if(file_exists($path)) return true;

	$origmask = @umask(0);
	$ret = @mkdir($path, $mode, true);
	@umask($origmask);

	return $ret;
}

function acymailing_getFolders($path, $filter = '.', $recurse = false, $full = false, $exclude = array('.svn', 'CVS', '.DS_Store', '__MACOSX'), $excludefilter = array('^\..*')){
	$path = acymailing_cleanPath($path);

	if (!is_dir($path)){
		acymailing_enqueueMessage($path.' is not a folder', 'error');
		return false;
	}

	if (count($excludefilter)){
		$excludefilter_string = '/(' . implode('|', $excludefilter) . ')/';
	}else{
		$excludefilter_string = '';
	}

	$arr = acymailing_getItems($path, $filter, $recurse, $full, $exclude, $excludefilter_string, false);
	asort($arr);

	return array_values($arr);
}

function acymailing_getFiles($path, $filter = '.', $recurse = false, $full = false, $exclude = array('.svn', 'CVS', '.DS_Store', '__MACOSX'), $excludefilter = array('^\..*', '.*~'), $naturalSort = false){
	$path = acymailing_cleanPath($path);

	if (!is_dir($path)){
		acymailing_enqueueMessage($path.' is not a folder', 'error');
		return false;
	}

	if (count($excludefilter)){
		$excludefilter_string = '/(' . implode('|', $excludefilter) . ')/';
	}else{
		$excludefilter_string = '';
	}

	$arr = acymailing_getItems($path, $filter, $recurse, $full, $exclude, $excludefilter_string, true);

	if ($naturalSort){
		natsort($arr);
	}else{
		asort($arr);
	}

	return array_values($arr);
}

function acymailing_getItems($path, $filter, $recurse, $full, $exclude, $excludefilter_string, $findfiles){
	$arr = array();

	if(!($handle = @opendir($path))) return $arr;

	while(($file = readdir($handle)) !== false){
		if($file == '.' || $file == '..' || in_array($file, $exclude) || (!empty($excludefilter_string) && preg_match($excludefilter_string, $file))) continue;
		$fullpath = $path . '/' . $file;

		$isDir = is_dir($fullpath);

		if(($isDir xor $findfiles) && preg_match("/$filter/", $file)){
			if($full){
				$arr[] = $fullpath;
			}else{
				$arr[] = $file;
			}
		}

		if($isDir && $recurse){
			if(is_int($recurse)){
				$arr = array_merge($arr, acymailing_getItems($fullpath, $filter, $recurse - 1, $full, $exclude, $excludefilter_string, $findfiles));
			}else{
				$arr = array_merge($arr, acymailing_getItems($fullpath, $filter, $recurse, $full, $exclude, $excludefilter_string, $findfiles));
			}
		}
	}

	closedir($handle);

	return $arr;
}

function acymailing_copyFolder($src, $dest, $path = '', $force = false, $use_streams = false){

	if($path){
		$src  = acymailing_cleanPath($path . '/' . $src);
		$dest = acymailing_cleanPath($path . '/' . $dest);
	}

	$src = rtrim($src, DIRECTORY_SEPARATOR);
	$dest = rtrim($dest, DIRECTORY_SEPARATOR);

	if (!file_exists($src)){
		acymailing_enqueueMessage('Folder '.$src.' does not exist', 'error');
		return false;
	}

	if(file_exists($dest) && !$force){
		acymailing_enqueueMessage('Folder '.$dest.' already exists', 'error');
		return true;
	}

	if (!acymailing_createFolder($dest)){
		acymailing_enqueueMessage('Cannot create destination folder', 'error');
		return false;
	}

	if (!($dh = @opendir($src))){
		acymailing_enqueueMessage('Cannot open source folder', 'error');
		return false;
	}

	while(($file = readdir($dh)) !== false){
		$sfid = $src . '/' . $file;
		$dfid = $dest . '/' . $file;

		switch (filetype($sfid)){
			case 'dir':
				if ($file != '.' && $file != '..'){
					$ret = acymailing_copyFolder($sfid, $dfid, null, $force, $use_streams);

					if ($ret !== true)
					{
						return $ret;
					}
				}
				break;

			case 'file':
				if (!@copy($sfid, $dfid)){
					acymailing_enqueueMessage('Copy file '.$sfid.' failed, check permissions', 'error');
					return false;
				}
				break;
		}
	}

	return true;
}

function acymailing_moveFolder($src, $dest, $path = '', $use_streams = false){
	if($path){
		$src = acymailing_cleanPath($path . '/' . $src);
		$dest = acymailing_cleanPath($path . '/' . $dest);
	}

	if (!file_exists($src)){
		acymailing_enqueueMessage('Folder '.$src.' does not exist', 'error');
		return false;
	}

	if (!@rename($src, $dest)){
		acymailing_enqueueMessage('Could not move folder '.$src.' to '.$dest.', check permissions', 'error');
		return false;
	}

	return true;
}

function acymailing_listFolderTree($path, $filter, $maxLevel = 3, $level = 0, $parent = 0){
	$dirs = array();

	if($level == 0) $GLOBALS['acymailing_folder_tree_index'] = 0;

	if ($level < $maxLevel){
		$folders = acymailing_getFolders($path, $filter);

		foreach ($folders as $name){
			$id = ++$GLOBALS['acymailing_folder_tree_index'];
			$fullName = acymailing_cleanPath($path . '/' . $name);
			$dirs[] = array(
				'id' => $id,
				'parent' => $parent,
				'name' => $name,
				'fullname' => $fullName,
				'relname' => str_replace(ACYMAILING_ROOT, '', $fullName),
			);
			$dirs2 = acymailing_listFolderTree($fullName, $filter, $maxLevel, $level + 1, $id);
			$dirs = array_merge($dirs, $dirs2);
		}
	}

	return $dirs;
}

function acymailing_deleteFile($file){
	$file = acymailing_cleanPath($file);
	if(!is_file($file)){
		acymailing_enqueueMessage($file.' is not a file', 'error');
		return false;
	}

	@chmod($file, 0777);

	if (!@unlink($file)){
		$filename = basename($file);
		acymailing_enqueueMessage('Failed to delete '.$filename, 'error');
		return false;
	}

	return true;
}

function acymailing_writeFile($file, $buffer, $use_streams = false){
	if (!file_exists(dirname($file)) && acymailing_createFolder(dirname($file)) == false) return false;

	$file = acymailing_cleanPath($file);
	$ret = is_int(file_put_contents($file, $buffer));

	return $ret;
}

function acymailing_moveFile($src, $dest, $path = '', $use_streams = false){
	if ($path){
		$src = acymailing_cleanPath($path . '/' . $src);
		$dest = acymailing_cleanPath($path . '/' . $dest);
	}

	if (!is_readable($src)){
		acymailing_enqueueMessage('Could not find source file, check permissions: '.$src, 'error');
		return false;
	}

	if (!@rename($src, $dest)){
		acymailing_enqueueMessage('Could not move the file', 'error');
		return false;
	}

	return true;
}

function acymailing_uploadFile($src, $dest){
	$dest = acymailing_cleanPath($dest);

	$baseDir = dirname($dest);
	if(!file_exists($baseDir)) acymailing_createFolder($baseDir);

	if(is_writeable($baseDir) && move_uploaded_file($src, $dest)){
		if (@chmod($dest, octdec('0644'))){
			return true;
		}else{
			acymailing_enqueueMessage('The file has been rejected for safety reason', 'error');
		}
	}else{
		acymailing_enqueueMessage('Couldn\'t upload file, check permissions for the folder '.$baseDir, 'error');
	}

	return false;
}

function acymailing_copyFile($src, $dest, $path = null, $use_streams = false){
	if ($path){
		$src = acymailing_cleanPath($path . '/' . $src);
		$dest = acymailing_cleanPath($path . '/' . $dest);
	}

	if (!is_readable($src)){
		acymailing_enqueueMessage('Could not find source file, check permissions: '.$src, 'error');
		return false;
	}

	if (!@copy($src, $dest)){
		acymailing_enqueueMessage('Could not copy the file '.$src.' to '.$dest, 'error');
		return false;
	}

	return true;
}

function acymailing_fileGetExt($file){
	$dot = strrpos($file, '.');
	if($dot === false) return '';

	return substr($file, $dot + 1);
}

function acymailing_cleanPath($path, $ds = DIRECTORY_SEPARATOR){
	$path = trim($path);

	if(empty($path)){
		$path = ACYMAILING_ROOT;
	}elseif (($ds == '\\') && substr($path, 0, 2) == '\\\\'){
		$path = "\\" . preg_replace('#[/\\\\]+#', $ds, $path);
	}else{
		$path = preg_replace('#[/\\\\]+#', $ds, $path);
	}

	return $path;
}

function acymailing_popup($url, $text, $class = '', $width = 800, $height = 500, $id = '', $params = ''){
	static $loaded = false;

	if(!$loaded) {
		acymailing_addScript(false, ACYMAILING_JS . 'acymailing.js?v=' . filemtime(ACYMAILING_MEDIA . 'js' . DS . 'acymailing.js'));
		acymailing_addStyle(false, ACYMAILING_CSS . 'acypopup.css?v=' . filemtime(ACYMAILING_MEDIA . 'css' . DS . 'acypopup.css'));
		acymailing_addStyle(false, ACYMAILING_CSS.'acyicon.css?v='.filemtime(ACYMAILING_MEDIA.'css'.DS.'acyicon.css'));
		$loaded = true;
	}

	if(!empty($id)) $id = ' id="'.$id.'" ';
	$url .= '&'.acymailing_noTemplate();
	return '<a onclick="acymailing.openpopup(\''.$url.'\','.$width.','.$height.'); return false;" class="acymailingpopup '.$class.'" '.$id.$params.'>'.$text.'</a>';
}

function acymailing_createArchive($name, $files){
	$contents = array();
	$ctrldir = array();

	$timearray = getdate();
	$dostime = (($timearray['year'] - 1980) << 25) | ($timearray['mon'] << 21) | ($timearray['mday'] << 16) | ($timearray['hours'] << 11) | ($timearray['minutes'] << 5) | ($timearray['seconds'] >> 1);
	$dtime = dechex($dostime);
	$hexdtime = chr(hexdec($dtime[6] . $dtime[7])) . chr(hexdec($dtime[4] . $dtime[5])) . chr(hexdec($dtime[2] . $dtime[3])) . chr(hexdec($dtime[0] . $dtime[1]));

	foreach ($files as $file){
		$data = $file['data'];
		$filename = str_replace('\\', '/', $file['name']);

		$fr = "\x50\x4b\x03\x04\x14\x00\x00\x00\x08\x00".$hexdtime;

		$unc_len = strlen($data);
		$crc = crc32($data);
		$zdata = gzcompress($data);
		$zdata = substr(substr($zdata, 0, strlen($zdata) - 4), 2);
		$c_len = strlen($zdata);

		$fr .= pack('V', $crc).pack('V', $c_len).pack('V', $unc_len).pack('v', strlen($filename)).pack('v', 0).$filename.$zdata;

		$old_offset = strlen(implode('', $contents));
		$contents[] = $fr;

		$cdrec = "\x50\x4b\x01\x02\x00\x00\x14\x00\x00\x00\x08\x00".$hexdtime;
		$cdrec .= pack('V', $crc).pack('V', $c_len).pack('V', $unc_len).pack('v', strlen($filename)).pack('v', 0).pack('v', 0).pack('v', 0).pack('v', 0).pack('V', 32).pack('V', $old_offset).$filename;

		$ctrldir[] = $cdrec;
	}

	$data = implode('', $contents);
	$dir = implode('', $ctrldir);
	$buffer = $data . $dir . "\x50\x4b\x05\x06\x00\x00\x00\x00" . pack('v', count($ctrldir)) . pack('v', count($ctrldir)) . pack('V', strlen($dir)) . pack('V', strlen($data)) . "\x00\x00";

	return acymailing_writeFile($name.'.zip', $buffer);
}

function acymailing_currentURL(){
	$url = isset($_SERVER['HTTPS']) ? 'https' : 'http';
	$url .= '://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	return $url;
}

function acymailing_accessList(){
	$listid = acymailing_getVar('int', 'listid');
	if(empty($listid)) return false;

	$listClass = acymailing_get('class.list');
	$myList = $listClass->get($listid);
	if(empty($myList->listid)) die('Invalid List');

	$currentUserid = acymailing_currentUserId();
	if(!empty($currentUserid) && $currentUserid == (int)$myList->userid) return true;
	if(empty($currentUserid) || $myList->access_manage == 'none') return false;
	if($myList->access_manage != 'all' && !acymailing_isAllowed($myList->access_manage)) return false;
	
	return true;
}

function acymailing_gridSort($title, $order, $direction = 'asc', $selected = '', $task = null, $new_direction = 'asc', $tip = ''){
	$direction = strtolower($direction);
	if ($order != $selected){
		$direction = $new_direction;
	}else{
		$direction = $direction == 'desc' ? 'asc' : 'desc';
	}

	$icon = array('acyicon-up', 'acyicon-down');
	$index = (int) ($direction == 'desc');

	$result = '<a href="#" onclick="acymailing.tableOrdering(\''.$order.'\', \''.$direction.'\', \''.$task.'\');return false;">';
	$result .= acymailing_tooltip(acymailing_translation('ACY_ORDER_COLUMN'), '', '', acymailing_translation($title));
	if ($order == $selected) $result .= '<span class="' . $icon[$index] . '"></span>';
	$result .= '</a>';

	return $result;
}

function acymailing_session(){
	$sessionID = session_id();
	if(empty($sessionID)) @session_start();
}

class acymailingController extends acymailingBridgeController{

	var $pkey = '';
	var $table = '';
	var $groupMap = '';
	var $groupVal = '';
	var $aclCat = '';

	function __construct($config = array()){
		parent::__construct($config);

		$this->registerDefaultTask('listing');
	}

	function getModel($name = '', $prefix = '', $config = array()){
		return false;
	}

	function listing(){
		if(!empty($this->aclCat) && !$this->isAllowed($this->aclCat, 'manage')) return;
		acymailing_setVar('layout', 'listing');
		return parent::display();
	}

	function isAllowed($cat, $action){
		if(acymailing_level(3)){
			$config = acymailing_config();
			if(!acymailing_isAllowed($config->get('acl_'.$cat.'_'.$action, 'all'))){
				acymailing_display(acymailing_translation('ACY_NOTALLOWED'), 'error');
				return false;
			}
		}
		return true;
	}

	function edit(){
		if(!empty($this->aclCat) && !$this->isAllowed($this->aclCat, 'manage')) return;
		acymailing_setVar('layout', 'form');
		return parent::display();
	}


	function add(){
		if(!empty($this->aclCat) && !$this->isAllowed($this->aclCat, 'manage')) return;
		acymailing_setVar('cid', array());
		acymailing_setVar('layout', 'form');
		return parent::display();
	}

	function apply(){
		$this->store();
		return $this->edit();
	}

	function save(){
		$this->store();
		return $this->listing();
	}

	function save2new(){
		$this->store();
		acymailing_setVar('cid', array());
		acymailing_setVar('layout', 'form');
		acymailing_setVar($this->pkey, '');
		return parent::display();
	}

	function saveorder(){
		if(!empty($this->aclCat) && !$this->isAllowed($this->aclCat, 'manage')) return;
		acymailing_checkToken();

		$orderClass = acymailing_get('helper.order');
		$orderClass->pkey = $this->pkey;
		$orderClass->table = $this->table;
		$orderClass->groupMap = $this->groupMap;
		$orderClass->groupVal = $this->groupVal;
		$orderClass->save();

		return $this->listing();
	}
}


class acymailingClass{

	var $tables = array();

	var $pkey = '';

	var $namekey = '';

	var $errors = array();

	function __construct($config = array()){
		global $acymailingCmsUserVars;
		$this->cmsUserVars = $acymailingCmsUserVars;
	}

	function save($element){
		$pkey = $this->pkey;
		if(empty($element->$pkey)){
			$status = acymailing_insertObject(acymailing_table(end($this->tables)), $element);
		}else{
			if(count((array)$element) > 1){
				$status = acymailing_updateObject(acymailing_table(end($this->tables)), $element, $pkey);
			}else{
				$status = true;
			}
		}
		if(!$status){
			$this->errors[] = substr(strip_tags(acymailing_getDBError()), 0, 200).'...';
		}

		if($status) return empty($element->$pkey) ? $status : $element->$pkey;
		return false;
	}

	function delete($elements){
		if(!is_array($elements)){
			$elements = array($elements);
		}

		if(empty($elements)) return 0;

		$column = is_numeric(reset($elements)) ? $this->pkey : $this->namekey;

		foreach($elements as $key => $val){
			$elements[$key] = acymailing_escapeDB($val);
		}

		if(empty($column) || empty($this->pkey) || empty($this->tables) || empty($elements)) return false;

		$whereIn = ' WHERE '.acymailing_secureField($column).' IN ('.implode(',', $elements).')';
		$result = true;

		acymailing_importPlugin('acymailing');

		$affected = 0;
		foreach($this->tables as $oneTable){
			acymailing_trigger('onAcyBefore'.ucfirst($oneTable).'Delete', array(&$elements));
			$query = 'DELETE FROM '.acymailing_table($oneTable).$whereIn;
			$affected = acymailing_query($query);
			$result = $affected !== false && $result;
		}


		if(!$result) return false;

		return $affected;
	}
}

acymailing_loadLanguage();

$config = acymailing_config();
if(!$config->get('ssl_links', 0)){
	define('ACYMAILING_LIVE', rtrim(str_replace('https:', 'http:', acymailing_rootURI()), '/').'/');
}else{
	define('ACYMAILING_LIVE', rtrim(str_replace('http:', 'https:', acymailing_rootURI()), '/').'/');
}

class acyEmoji
{
	public static function Encode($text){
		return self::convertEmoji($text, "ENCODE");
	}

	public static function Decode($text){
		return self::convertEmoji($text, "DECODE");
	}
	private static function convertEmoji($text,$op) {
		if(empty($text) || !file_exists(ACYMAILING_ROOT.'plugins'.DS.'acymailing'.DS.'emojis')) return $text;
		if($op=="ENCODE"){
			return preg_replace_callback('/([0-9|#][\x{20E3}])|[\x{00ae}|\x{00a9}|\x{203C}|\x{2047}|\x{2048}|\x{2049}|\x{3030}|\x{303D}|\x{2139}|\x{2122}|\x{3297}|\x{3299}][\x{FE00}-\x{FEFF}]?|[\x{2190}-\x{21FF}][\x{FE00}-\x{FEFF}]?|[\x{2300}-\x{23FF}][\x{FE00}-\x{FEFF}]?|[\x{2460}-\x{24FF}][\x{FE00}-\x{FEFF}]?|[\x{25A0}-\x{25FF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{FE00}-\x{FEFF}]?|[\x{2600}-\x{27BF}][\x{1F000}-\x{1FEFF}]?|[\x{2900}-\x{297F}][\x{FE00}-\x{FEFF}]?|[\x{2B00}-\x{2BF0}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F9FF}][\x{FE00}-\x{FEFF}]?|[\x{1F000}-\x{1F9FF}][\x{1F000}-\x{1FEFF}]?/u',array('self',"encodeEmoji"),$text);
		}else{
			return preg_replace_callback('/(\\\u[0-9a-f]{4})+/i', array('self', "decodeEmoji"), $text);
		}
	}

	private static function encodeEmoji($match){
		return str_replace(array('[', ']', '"'), '', json_encode($match));
	}

	private static function decodeEmoji($text){
		if(!$text) return '';
		$text = $text[0];
		$decode = json_decode($text, true);
		if($decode) return $decode;
		$text = '["'.$text.'"]';
		$decode = json_decode($text);
		if(count($decode) == 1){
			return $decode[0];
		}
		return $text;
	}
}

class acyPagination {
	var $total;
	var $start;
	var $value;

	public function __construct($total, $start, $value) {
		$this->total = $total;
		$this->start = $start;
		$this->value = $value;
	}

	function getListFooter(){
		$pagination = '<input type="hidden" name="limitstart" value="'.$this->start.'">';
		$nbPages = ceil($this->total / $this->value);
		if($nbPages < 2) return $pagination;

		$pagination .= '<ul class="acypagination">';
		$onclick = $this->start > 0 ? '" onclick="document.adminForm.limitstart.value=0; acymailing.submitform();"' : ' acypaginactive"';
		$pagination .= '<li><span class="acyicon-first'.$onclick.'></span></li>';
		$onclick = $this->start-$this->value >= 0 ? '" onclick="document.adminForm.limitstart.value='.($this->start-$this->value).'; acymailing.submitform();"' : ' acypaginactive"';
		$pagination .= '<li><span class="acyicon-backward'.$onclick.'></span></li>';

		acymailing_addScript(true, 'document.addEventListener("DOMContentLoaded", function(){
			document.getElementById("acypagination").addEventListener("keyup", function(e){
				var code = e.which;
				if(code == 13 || code == 188 || code == 186){
					if(this.value > '.$nbPages.') this.value = '.$nbPages.';
					var selectedPage = this.value-1;
					document.adminForm.limitstart.value = selectedPage*'.$this->value.';
					acymailing.submitform();
				}
			});
		});');
		$input = '<input id="acypagination" type="text" value="'.($this->start/$this->value+1).'" onkeyup="" />';

		$pagination .= '<li class="selectedPage">'.acymailing_translation_sprintf('ACY_PAGINATION_PAGE', $input, $nbPages).'</li>';

		$lastPage = floor(($this->total-1)/$this->value)*$this->value;

		$onclick = $this->start < $lastPage ? '" onclick="document.adminForm.limitstart.value='.($this->start+$this->value).'; acymailing.submitform();"' : ' acypaginactive"';
		$pagination .= '<li><span class="acyicon-forward'.$onclick.'></span></li>';
		$onclick = $this->start < $lastPage ? '" onclick="document.adminForm.limitstart.value='.$lastPage.'; acymailing.submitform();"' : ' acypaginactive"';
		$pagination .= '<li><span class="acyicon-last'.$onclick.'></span></li></ul>';
		return $pagination;
	}

	function getResultsCounter(){
		if(empty($this->total)) return '<div class="acypagination_counter">'.acymailing_translation('ACY_PAGINATION_NONE').'</div>';
		$from = $this->start+1;
		$to = $this->start+$this->value;
		if($to > $this->total) $to = $this->total;

		$paginationNb = array();
		$paginationNb[] = acymailing_selectOption(5,5);
		$paginationNb[] = acymailing_selectOption(10,10);
		$paginationNb[] = acymailing_selectOption(15,15);
		$paginationNb[] = acymailing_selectOption(20,20);
		$paginationNb[] = acymailing_selectOption(25,25);
		$paginationNb[] = acymailing_selectOption(30,30);
		$paginationNb[] = acymailing_selectOption(50,50);
		$paginationNb[] = acymailing_selectOption(100,100);

		$result = '<div class="acypagination_counter">'.acymailing_translation('DISPLAY').' # ';
		$onChange = 'if(document.adminForm.limitstart){ document.adminForm.limitstart.value = 0;} document.getElementById(\'adminForm\').submit();';
		$result .= acymailing_select($paginationNb, 'limit' , 'size="1" style="width:60px" onchange="'.$onChange.'"', 'value', 'text', $this->value).'<br />';
		return $result.acymailing_translation_sprintf('ACY_PAGINATION', $from, $to, $this->total).'</div>';
	}

	function getRowOffset($i){
		return $this->start + 1 + $i;
	}
}

class acyParameter {
	function __construct($params = null){
		if(is_string($params)) {
			if (ACYMAILING_J16) {
				$this->params = json_decode($params);
			} else {
				$params = explode("\n", $params);
				foreach ($params as $oneParam) {
					if (empty($oneParam)) continue;
					list($key, $val) = explode('=', $oneParam, 2);
					$this->params->$key = $val;
				}
			}
		}elseif(is_object($params)){
			$this->paramObject = $params;
		}elseif(is_array($params)){
			$this->params = (object) $params;
		}
	}

	function get($path, $default = null){
		if(empty($this->paramObject)) {
			if (empty($this->params->$path)) return $default;
			return $this->params->$path;
		}else{
			$value = $this->paramObject->get($path, 'noval');
			if($value === 'noval') $value = $this->paramObject->get('data.'.$path, $default);
			return $value;
		}
	}
}
