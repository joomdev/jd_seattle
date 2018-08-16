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

class acyhistoryClass extends acymailingClass{

	function insert($subid,$action,$data = array(),$mailid = 0){
		$currentUserid = acymailing_currentUserId();
		if(!empty($currentUserid)){
			$data[] = acymailing_translation('EXECUTED_BY').'::'.$currentUserid.' ( '.acymailing_currentUserName().' )';
		}
		$history = new stdClass();
		$history->subid = intval($subid);
		$history->action = strip_tags($action);
		$history->data = implode("\n",$data);
		if(strlen($history->data) > 100000) $history->data = substr($history->data,0,10000);

		static $date = null;
		if(empty($date)) $date = time();

		$history->date = ++$date;
		while($this->alreadyExists($history->subid, $history->date)){
			$history->date++;
		}

		$date = $history->date;

		$history->mailid = $mailid;
		$userHelper = acymailing_get('helper.user');
		$config = acymailing_config();
		if($config->get('anonymous_tracking', 0) == 0) $history->ip = $userHelper->getIP();

		if (!empty($_SERVER)) {
			$source = array();
			if($config->get('anonymous_tracking', 0) == 0) {
				$vars = array('HTTP_REFERER', 'HTTP_USER_AGENT', 'HTTP_HOST', 'SERVER_ADDR', 'REMOTE_ADDR', 'REQUEST_URI', 'QUERY_STRING');
			}else{
				$vars = array('HTTP_REFERER', 'HTTP_HOST', 'SERVER_ADDR', 'REQUEST_URI', 'QUERY_STRING');
			}

			foreach ($vars as $oneVar) {
				if (!empty($_SERVER[$oneVar])) {
					$source[] = $oneVar.'::'.strip_tags($_SERVER[$oneVar]);
				}
			}
			$history->source = implode("\n", $source);
		}

		return acymailing_insertObject(acymailing_table('history'),$history);
	}

	function alreadyExists($subid, $date){
		$result = acymailing_loadResult('SELECT subid FROM #__acymailing_history WHERE subid = '.intval($subid).' AND date = '.acymailing_escapeDB($date));
		return !empty($result);
	}
}
