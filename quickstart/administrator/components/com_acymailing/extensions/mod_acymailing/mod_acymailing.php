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

if(!include_once(rtrim(JPATH_ADMINISTRATOR, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acymailing'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php')){
	echo 'This module can not work without the AcyMailing Component';
	return;
};


if(!function_exists('acymailing_getArticleURL')) {
	function acymailing_getArticleURL($id, $params, $text)
	{
		if (is_numeric($id)) {
			if (!ACYMAILING_J16) {
				$query = 'SELECT a.id,a.alias,a.catid,a.sectionid, c.alias as catalias, s.alias as secalias FROM #__content as a ';
				$query .= ' LEFT JOIN #__categories AS c ON c.id = a.catid ';
				$query .= ' LEFT JOIN #__sections AS s ON s.id = a.sectionid ';
				$query .= 'WHERE a.id = '.$id.' LIMIT 1';
				$article = acymailing_loadObject($query);

				$section = $article->sectionid.(!empty($article->secalias) ? ':'.$article->secalias : '');
				$category = $article->catid.(!empty($article->catalias) ? ':'.$article->catalias : '');
				$articleid = $article->id.(!empty($article->alias) ? ':'.$article->alias : '');
				$url = ContentHelperRoute::getArticleRoute($articleid, $category, $section);
			} else {
				$query = 'SELECT a.id,a.alias,a.catid, c.alias as catalias FROM #__content as a ';
				$query .= ' LEFT JOIN #__categories AS c ON c.id = a.catid ';
				$query .= 'WHERE a.id = '.$id.' LIMIT 1';
				$article = acymailing_loadObject($query);

				$category = $article->catid.(!empty($article->catalias) ? ':'.$article->catalias : '');
				$articleid = $article->id.(!empty($article->alias) ? ':'.$article->alias : '');

				$url = ContentHelperRoute::getArticleRoute($articleid, $category);
			}
			$url .= (strpos($url, '?') ? '&' : '?').'tmpl=component';
		} else {
			$url = $id;
		}

		if ($params->get('showtermspopup', 1) == 1) {
			$acypop = acymailing_get('helper.acypopup');
			$url = $acypop->display(acymailing_translation($text), acymailing_translation($text, true), $url, $articleid, 0, 0, '', '', 'text');
		} else {
			$url = '<a title="'.acymailing_translation($text, true).'"  href="'.$url.'" target="_blank">'.acymailing_translation($text).'</a>';
		}

		return $url;
	}
}

$config = acymailing_config();
$overridedesign = preg_replace('#[^a-z0-9_]#i', '', acymailing_getVar('cmd', 'design'));
if(!empty($overridedesign)){
	if($overridedesign == 'popup') $overridedesign = '';
	$params->set('effect', 'mootools-box');
}

$redirectMode = $params->get('redirectmode', '0');
switch($redirectMode){
	case 1 :
		$redirectUrl = acymailing_completeLink('lists', false, true);
		$redirectUrlUnsub = $redirectUrl;
		break;
	case 2 :
		$redirectUrl = $params->get('redirectlink');
		$redirectUrlUnsub = $params->get('redirectlinkunsub');
		break;
	default :
		if(isset($_SERVER["REQUEST_URI"])){
			$requestUri = $_SERVER["REQUEST_URI"];
		}else{
			$requestUri = $_SERVER['PHP_SELF'];
			if(!empty($_SERVER['QUERY_STRING'])) $requestUri = rtrim($requestUri, '/').'?'.$_SERVER['QUERY_STRING'];
		}
		$redirectUrl = (((!empty($_SERVER['HTTPS']) AND strtolower($_SERVER['HTTPS']) == "on") || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://').$_SERVER["HTTP_HOST"].$requestUri;
		$redirectUrlUnsub = $redirectUrl;
		if($params->get('effect', 'normal') == 'mootools-box') $redirectUrlUnsub = $redirectUrl = '';
}

$subController = acymailing_get('controller_front.sub');
$subController->_checkRedirectUrl($redirectUrl);
$subController->_checkRedirectUrl($redirectUrlUnsub);

$formName = acymailing_getModuleFormName();
if(!empty($overridedesign)){
	$params->set('includejs', 'module');
}

$introText = $params->get('introtext');
$postText = $params->get('finaltext');
$mootoolsIntro = $params->get('mootoolsintro', '');
if(!empty($introText) && preg_match('#^[A-Z_]*$#', $introText)){
	$introText = acymailing_translation($introText);
}
if(!empty($postText) && preg_match('#^[A-Z_]*$#', $postText)){
	$postText = acymailing_translation($postText);
}
if(!empty($mootoolsIntro) && preg_match('#^[A-Z_]*$#', $mootoolsIntro)){
	$mootoolsIntro = acymailing_translation($mootoolsIntro);
}


if($params->get('effect') == 'mootools-box' AND acymailing_getVar('string', 'tmpl') != 'component'){
	$mootoolsButton = $params->get('mootoolsbutton', '');
	if(empty($mootoolsButton)){
		$mootoolsButton = acymailing_translation('SUBSCRIBE');
	}else{
		if(!empty($mootoolsButton) && preg_match('#^[A-Z_]*$#', $mootoolsButton)){
			$mootoolsButton = acymailing_translation($mootoolsButton);
		}
	}

	$moduleCSS = $config->get('css_module', 'default');
	if(!empty($moduleCSS)){
		acymailing_addStyle(false, ACYMAILING_CSS.'module_'.$moduleCSS.'.css?v='.filemtime(ACYMAILING_MEDIA.'css'.DS.'module_'.$moduleCSS.'.css'));
	}
	require(JModuleHelper::getLayoutPath('mod_acymailing', 'popup'));
	return;
}
acymailing_initModule($params);

$userClass = acymailing_get('class.subscriber');
$identifiedUser = null;
$currentUserEmail = acymailing_currentUserEmail();
if($params->get('loggedin', 1) && !empty($currentUserEmail)){
	$identifiedUser = $userClass->get($currentUserEmail);
}

if(!empty($currentUserEmail)) $currentUserEmail = acymailing_punycode($currentUserEmail, 'emailToUTF8');
if(!empty($identifiedUser->email)) $identifiedUser->email = acymailing_punycode($identifiedUser->email, 'emailToUTF8');

$visibleLists = trim($params->get('lists', 'None'));
$hiddenLists = trim($params->get('hiddenlists', 'All'));
$visibleListsArray = array();
$hiddenListsArray = array();
$listsClass = acymailing_get('class.list');
if(empty($identifiedUser->subid)){
	$allLists = $listsClass->getLists('listid');
}else{
	$allLists = $userClass->getSubscription($identifiedUser->subid, 'listid');
}


if(strpos($visibleLists, ',') OR is_numeric($visibleLists)){
	$allvisiblelists = explode(',', $visibleLists);
	foreach($allLists as $oneList){
		if($oneList->published AND in_array($oneList->listid, $allvisiblelists)) $visibleListsArray[] = $oneList->listid;
	}
}elseif(strtolower($visibleLists) == 'all'){
	foreach($allLists as $oneList){
		if($oneList->published){
			$visibleListsArray[] = $oneList->listid;
		}
	}
}

if(strpos($hiddenLists, ',') OR is_numeric($hiddenLists)){
	$allhiddenlists = explode(',', $hiddenLists);
	foreach($allLists as $oneList){
		if($oneList->published AND in_array($oneList->listid, $allhiddenlists)) $hiddenListsArray[] = $oneList->listid;
	}
}elseif(strtolower($hiddenLists) == 'all'){
	$visibleListsArray = array();
	foreach($allLists as $oneList){
		if(!empty($oneList->published)){
			$hiddenListsArray[] = $oneList->listid;
		}
	}
}

if(!empty($visibleListsArray) AND !empty($hiddenListsArray)){
	$visibleListsArray = array_diff($visibleListsArray, $hiddenListsArray);
}

$visibleLists = $params->get('dropdown', 0) ? '' : implode(',', $visibleListsArray);
$hiddenLists = implode(',', $hiddenListsArray);

if(!$params->get('dropdown', 0) && empty($hiddenLists) && empty($visibleLists)){
	echo '<p style="color:red">Error : Please select some lists in your AcyMailing module configuration for the field "'.acymailing_translation('AUTO_SUBSCRIBE_TO').'" and make sure the selected lists are enabled </p>';
}

if(!empty($identifiedUser->subid)){
	$countSub = 0;
	$countUnsub = 0;
	foreach($visibleListsArray as $idOneList){
		if($allLists[$idOneList]->status == -1){
			$countSub++;
		}elseif($allLists[$idOneList]->status == 1) $countUnsub++;
	}
	foreach($hiddenListsArray as $idOneList){
		if($allLists[$idOneList]->status == -1){
			$countSub++;
		}elseif($allLists[$idOneList]->status == 1) $countUnsub++;
	}
}

$checkedLists = $params->get('listschecked', 'All');
if(strtolower($checkedLists) == 'all'){
	$checkedListsArray = $visibleListsArray;
}elseif(strpos($checkedLists, ',') OR is_numeric($checkedLists)){
	$checkedListsArray = explode(',', $checkedLists);
}else{
	$checkedListsArray = array();
}

$listPosition = $params->get('listposition', 'before');


$nameCaption = $params->get('nametext', acymailing_translation('NAMECAPTION'));
$emailCaption = $params->get('emailtext', acymailing_translation('EMAILCAPTION'));
$displayOutside = $params->get('displayfields', 0);
$displayInline = ($params->get('displaymode', 'vertical') == 'vertical') ? false : true;

$displayedFields = $params->get('customfields', 'name,email');
$fieldsToDisplay = explode(',', $displayedFields);
$extraFields = array();

$fieldsize = $params->get('fieldsize', '80%');
if(is_numeric($fieldsize)) $fieldsize .= 'px';

$currentUserid = acymailing_currentUserId();
if(!in_array('email', $fieldsToDisplay) && empty($currentUserid)) $fieldsToDisplay[] = 'email';

if($params->get('effect') == 'mootools-slide'){
	$mootoolsButton = $params->get('mootoolsbutton', '');
	if(empty($mootoolsButton)) $mootoolsButton = acymailing_translation('SUBSCRIBE');
	
	$js .= "document.addEventListener(\"DOMContentLoaded\", function(){
				var acytogglemodule = document.getElementById('acymailing_togglemodule_$formName');
				var module = document.getElementById('acymailing_fulldiv_$formName');
				module.style.display = 'none';

				acytogglemodule.addEventListener('click', function(){
					module.style.display = '';
					if(acytogglemodule.className.indexOf('acyactive') > -1){
						acytogglemodule.className = 'acymailing_togglemodule';
						module.className = 'slide_close';
					}else{
						acytogglemodule.className = 'acymailing_togglemodule acyactive';
						module.className = 'slide_open';
					}
					
					return false;
				});
			});
		";

	if($params->get('includejs', 'header') == 'header'){
		acymailing_addScript(true, $js);
	}else{
		echo "<script type=\"text/javascript\">
			<!--
				$js
			//-->
				</script>";
	}
}

if($params->get('showterms', false)){
	require_once JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php';
	$termsIdContent = $params->get('termscontent', 0);
	$privacyIdContent = $params->get('privacypolicy', 0);

	$termsURL = null;
	$privacyURL = null;

	if(!empty($termsIdContent)){
		$termsURL = acymailing_getArticleURL($termsIdContent, $params, 'JOOMEXT_TERMS');
	}

	if(!empty($privacyIdContent)){
		$privacyURL = acymailing_getArticleURL($privacyIdContent, $params, 'ACY_PRIVACY_POLICY');
	}

	if(empty($termsURL) && empty($privacyURL)){
		$termslink = acymailing_translation('JOOMEXT_TERMS');
	}else{
		if(empty($privacyURL)){
			$termslink = acymailing_translation_sprintf('ACY_I_AGREE_TERMS', $termsURL);
		}elseif(empty($termsURL)){
			$termslink = acymailing_translation_sprintf('ACY_I_AGREE_PRIVACY', $privacyURL);
		}else{
			$termslink = acymailing_translation_sprintf('ACY_I_AGREE', $termsURL, $privacyURL);
		}
	}
}

if(!empty($overridedesign)){
	ob_start();
}

if($params->get('displaymode') == 'tableless'){
	require(JModuleHelper::getLayoutPath('mod_acymailing', 'tableless'));
}else{
	require(JModuleHelper::getLayoutPath('mod_acymailing'));
}

$currentEmail = acymailing_currentUserEmail();
if(!empty($currentEmail)){
	echo '<span style="display:none">{emailcloak=off}</span>';
}

if(!empty($overridedesign)){
	$moduleDisplay = ob_get_clean();
	$file = ACYMAILING_MEDIA.'plugins'.DS.'squeezepage'.DS.$overridedesign.'.php';
	if(file_exists($file)){
		ob_start();
		require($file);
		$squeezePage = ob_get_clean();
		$squeezePage = str_replace('{module}', $moduleDisplay, $squeezePage);
		echo $squeezePage;
	}else{
		echo $moduleDisplay;
	}
}
