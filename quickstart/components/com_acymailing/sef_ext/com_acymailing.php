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

	if(!class_exists('Sh404sefFactory') || !method_exists('Sh404sefFactory','getConfig')){
		$dosef = false;
		return;
	}

	global $sh_LANG;
	$sefConfig = &Sh404sefFactory::getConfig();
	$shLangName = '';
	$shLangIso = '';
	$shItemidString = '';
	$acysefview = array('frontsubscriber','archive','lists','frontnewsletter','newsletter','user','frontdata','frontstats','frontstatsurl');

	$dosef = shInitializePlugin( $lang, $shLangName, $shLangIso, $option);

	if(!$dosef) return;

	if(isset($view)){
		if(!in_array($view, $acysefview)) $dosef = false;
		shRemoveFromGETVarsList('view');
	}

	if(isset($ctrl)){
		if(!in_array($ctrl, $acysefview)) $dosef = false;
		shRemoveFromGETVarsList('ctrl');
	}

	$title = array();

	$title[] = getMenuTitle($option, (isset($view) ? $view : null), (isset($Itemid) ? $Itemid : null), null, $shLangName);

	if(isset($layout)){ $title[] = $layout; shRemoveFromGETVarsList('layout'); }
	if(isset( $task )){ $title[] = $task; shRemoveFromGETVarsList('task'); }
	if(isset($listid)){ $title[] = $listid; shRemoveFromGETVarsList('listid'); }
	if(isset($mailid) && !(isset($task) && $task == 'edit' && isset($ctrl) && $ctrl == 'frontnewsletter')){ $title[] = $mailid; shRemoveFromGETVarsList('mailid'); }

	if(isset($option)) shRemoveFromGETVarsList('option');
	if(isset($lang)) shRemoveFromGETVarsList('lang'); // Already handled by sh404SEF
	if(isset($Itemid)) shRemoveFromGETVarsList('Itemid'); else $dosef = false; // There must be the Itemid


	if($dosef && !empty($title)){
		$string = shFinalizePlugin( $string, $title, $shAppendString, $shItemidString,
		(isset($limit) ? $limit : null), (isset($limitstart) ? $limitstart : null),
		(isset($shLangName) ? $shLangName : null), (isset($showall) ? $showall : null));
	}
