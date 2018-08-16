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

class queueClass extends acymailingClass{

	var $onlynew = false;
	var $mindelay = 0;
	var $limit = 0;
	var $orderBy = '';
	var $emailtypes = array();

	function delete($filters){

		if(!empty($filters)){
			$query = 'DELETE a.* FROM '.acymailing_table('queue').' as a';
			$query .= ' JOIN '.acymailing_table('subscriber').' as b on a.subid = b.subid';
			$query .= ' JOIN '.acymailing_table('mail').' as c on a.mailid = c.mailid';
			$query .= ' WHERE ('.implode(') AND (', $filters).')';
		}else{
			$nbRecords = acymailing_loadResult('SELECT COUNT(*) FROM #__acymailing_queue');

			$query = 'TRUNCATE TABLE '.acymailing_table('queue');
		}
		$affected = acymailing_query($query);
		if(empty($nbRecords)) $nbRecords = $affected;

		return $nbRecords;
	}

	function nbQueue($mailid){
		$mailid = (int)$mailid;
		return acymailing_loadResult('SELECT count(subid) FROM '.acymailing_table('queue').' WHERE mailid = '.$mailid.' GROUP BY mailid');
	}

	function queue($mailid, $time){
		$mailid = intval($mailid);
		if(empty($mailid)) return false;

		$classLists = acymailing_get('class.listmail');
		$lists = $classLists->getReceivers($mailid, false);
		if(empty($lists)) return 0;

		$config = acymailing_config();
		acymailing_importPlugin('acymailing');
		$filterClass = acymailing_get('class.filter'); // Keep it, it loads the acyQuery class

		$mailClass = acymailing_get('class.mail');
		$mail = $mailClass->get($mailid);

		if(empty($mail->filter['type'])){
			$cquery = $this->initialQuery($lists);
			$query = 'INSERT IGNORE INTO '.acymailing_table('queue').' (subid,mailid,senddate,priority) '.$cquery->getQuery(array('a.subid',$mailid,$time,(int)$config->get('priority_newsletter', 3)));
			$totalinserted = acymailing_query($query);
		}else{
			$totalinserted = 0;
			foreach($mail->filter['type'] as $block => $oneFilter) {
				$cquery = $this->initialQuery($lists);
				foreach($oneFilter as $num => $oneType) {
					if(empty($oneType)) continue;
					acymailing_trigger('onAcyProcessFilter_' . $oneType, array(&$cquery, $mail->filter[$num][$oneType], $num));
				}
				$query = 'INSERT IGNORE INTO '.acymailing_table('queue').' (subid,mailid,senddate,priority) '.$cquery->getQuery(array('a.subid',$mailid,$time,(int)$config->get('priority_newsletter', 3)));
				$totalinserted += acymailing_query($query);
			}
		}

		if($this->onlynew){
			$affected = acymailing_query('DELETE b.* FROM `#__acymailing_userstats` as a JOIN `#__acymailing_queue` as b ON a.subid = b.subid AND a.mailid = b.mailid WHERE a.mailid = '.$mailid);
			$totalinserted = $totalinserted - $affected;
		}

		if(!empty($this->mindelay)){
			$affected = acymailing_query('DELETE b.* FROM `#__acymailing_queue` as b JOIN `#__acymailing_userstats` AS a ON a.subid = b.subid WHERE b.mailid = '.$mailid.' AND a.senddate > '.(time() - ($this->mindelay * 24 * 60 * 60)));
			$totalinserted = $totalinserted - $affected;
		}

		acymailing_trigger('onAcySendNewsletter', array($mailid));

		return $totalinserted;
	}

	function initialQuery($lists){
		$query = new acyQuery();

		$query->from = acymailing_table('listsub').' as a ';
		$query->join[] = acymailing_table('subscriber').' as sub ON a.subid = sub.subid ';
		$query->where[] = 'sub.enabled = 1';
		$query->where[] = 'sub.accept = 1';
		$query->where[] = 'a.listid IN ('.implode(',', array_keys($lists)).')';
		$query->where[] = 'a.status = 1';
		$config = acymailing_config();
		if($config->get('require_confirmation', '0')) $query->where[] = 'sub.confirmed = 1';
		$query->orderBy = $this->orderBy;
		$query->limit = $this->limit;

		return $query;
	}

	public function getReady($limit, $mailid = 0){
		if(empty($limit)) return array();

		$config = acymailing_config();
		$order = $config->get('sendorder');
		if(empty($order)){
			$order = 'a.`subid` ASC';
		}else{
			if($order == 'rand'){
				$order = 'RAND()';
			}else{
				$ordering = explode(',', $order);
				$order = 'a.`'.acymailing_secureField(trim($ordering[0])).'` '.acymailing_secureField(trim($ordering[1]));
			}
		}

		$query = 'SELECT a.* FROM '.acymailing_table('queue').' AS a';
		$query .= ' JOIN '.acymailing_table('mail').' AS b on a.`mailid` = b.`mailid` ';
		$query .= ' WHERE a.`senddate` <= '.time().' AND b.`published` = 1';
		if(!empty($this->emailtypes)){
			foreach($this->emailtypes as &$oneType){
				$oneType = acymailing_escapeDB($oneType);
			}
			$query .= ' AND (b.type = '.implode(' OR b.type = ', $this->emailtypes).')';
		}
		if(!empty($mailid)) $query .= ' AND a.`mailid` = '.$mailid;
		$query .= ' ORDER BY a.`priority` ASC, a.`senddate` ASC, '.$order;
		$query .= ' LIMIT '.acymailing_getVar('int', 'startqueue', 0).','.intval($limit);
		try{
			$results = acymailing_loadObjectList($query);
		}catch(Exception $e){
			$results = null;
		}

		if($results === null){
			acymailing_query('REPAIR TABLE #__acymailing_queue, #__acymailing_subscriber, #__acymailing_mail');
		}

		if(empty($results)) return array();

		if(!empty($results)){
			$firstElementQueued = reset($results);
			acymailing_query('UPDATE #__acymailing_queue SET senddate = senddate + 1 WHERE mailid = '.$firstElementQueued->mailid.' AND subid = '.$firstElementQueued->subid.' LIMIT 1');
		}

		$subids = array();
		foreach($results as $oneRes){
			$subids[$oneRes->subid] = intval($oneRes->subid);
		}

		$cleanQueue = false;
		if(!empty($subids)){
			$allusers = acymailing_loadObjectList('SELECT * FROM #__acymailing_subscriber WHERE subid IN ('.implode(',', $subids).')', 'subid');
			foreach($results as $oneId => $oneRes){
				if(empty($allusers[$oneRes->subid])){
					$cleanQueue = true;
					continue;
				}
				foreach($allusers[$oneRes->subid] as $oneVar => $oneVal){
					$results[$oneId]->$oneVar = $oneVal;
				}
			}
		}

		if($cleanQueue){
			acymailing_query('DELETE a.* FROM #__acymailing_queue as a LEFT JOIN #__acymailing_subscriber as b ON a.subid = b.subid WHERE b.subid IS NULL');
		}

		return $results;
	}


	function queueStatus($mailid, $all = false){
		$query = 'SELECT a.mailid, count(a.subid) as nbsub,min(a.senddate) as senddate, b.subject FROM '.acymailing_table('queue').' as a';
		$query .= ' JOIN '.acymailing_table('mail').' as b on a.mailid = b.mailid';
		$query .= ' WHERE b.published > 0';
		if(!$all){
			$query .= ' AND a.senddate < '.time();
			if(!empty($mailid)) $query .= ' AND a.mailid = '.$mailid;
		}
		$query .= ' GROUP BY a.mailid';
		$queueStatus = acymailing_loadObjectList($query, 'mailid');

		return $queueStatus;
	}

}
