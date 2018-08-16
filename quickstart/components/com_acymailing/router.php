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

function AcymailingBuildRoute(&$query){
	$segments = array();

	if(isset($query['ctrl']) && in_array($query['ctrl'], array('stats', 'moduleloader', 'cron', 'fronteditor', 'frontfilter', 'sub'))){
		return $segments;
	}

	$ctrl = '';
	$task = '';

	if(isset($query['ctrl'])){
		$ctrl = $query['ctrl'];
		if($ctrl != 'archive' || (!empty($query['task']) && $query['task'] != 'view')) $segments[] = $query['ctrl'];
		unset($query['ctrl']);
		if(isset($query['task'])){
			$task = $query['task'];
			if($ctrl != 'archive' || $task != 'view') $segments[] = $query['task'];
			unset($query['task']);
		}
	}elseif(isset($query['view'])){
		$ctrl = $query['view'];
		$segments[] = $query['view'];
		unset($query['view']);
		if(isset($query['layout'])){
			$task = $query['layout'];
			$segments[] = $query['layout'];
			unset($query['layout']);
		}
	}

	if(empty($query)) return $segments;

	foreach($query as $name => $value){
		if(in_array($name, array('option', 'Itemid', 'start', 'format', 'limitstart', 'no_html', 'val', 'key', 'acyformname', 'subid', 'tmpl', 'lang', 'limit', 'acm', 'idU'))) continue;
		if(strlen($name) > 25) continue;

		if($ctrl == 'user' && $name == 'mailid') continue;

		$segments[] = $name.':'.$value;
		unset($query[$name]);
	}

	return $segments;
}

function AcymailingParseRoute($segments){
	$vars = array();

	if(empty($segments)) return $vars;

	$i = 0;
	foreach($segments as $name){
		if(strpos($name, ':')){
			list($arg, $val) = explode(':', $name);
			if(is_numeric($arg)){
				$vars['Itemid'] = $arg;
			}else{
				$vars[$arg] = $val;
			}
		}else{
			$i++;
			if($i == 1){
				$vars['ctrl'] = $name;
			}elseif($i == 2){
				$vars['task'] = $name;
			}
		}
	}

	if(empty($vars['ctrl']) && (!empty($vars['listid']) || !empty($vars['mailid']))){
		$vars['ctrl'] = 'archive';
		if(!empty($vars['mailid'])) $vars['task'] = 'view';
	}

	return $vars;
}
