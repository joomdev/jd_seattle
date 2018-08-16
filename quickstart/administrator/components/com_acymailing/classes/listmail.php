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

class listmailClass extends acymailingClass{

	function getLists($mailid){
		$query = 'SELECT a.*,b.mailid 
				FROM '.acymailing_table('list').' as a 
				LEFT JOIN '.acymailing_table('listmail').' as b on a.listid = b.listid AND b.mailid = '.intval($mailid).' 
				WHERE a.type = \'list\' 
				ORDER BY b.mailid DESC, a.ordering ASC';
		return acymailing_loadObjectList($query);
	}

	function save($mailid, $listids = array(), $removelists = array()){
		$mailid = intval($mailid);
		if(!empty($removelists)){
			acymailing_arrayToInteger($removelists);
			$query = 'DELETE FROM '.acymailing_table('listmail').' WHERE mailid = '.$mailid.' AND listid IN ('.implode(',', $removelists).')';
			$affected = acymailing_query($query);
			if($affected === false) return false;
		}

		acymailing_arrayToInteger($listids);
		if(empty($listids)) return true;

		$query = 'INSERT IGNORE INTO '.acymailing_table('listmail').' (mailid,listid) VALUES ('.$mailid.','.implode('),('.$mailid.',', $listids).')';
		return acymailing_query($query) !== false;
	}

	function getCampaign($mailid){
		$query = 'SELECT a.*,b.mailid FROM '.acymailing_table('listmail').' as b LEFT JOIN '.acymailing_table('list').' as a on a.listid = b.listid WHERE b.mailid = '.intval($mailid).' AND a.type = \'campaign\' LIMIT 1';
		return acymailing_loadObject($query);
	}

	function getReceivers($mailid, $total = true, $onlypublished = true){
		$query = 'SELECT a.name,a.description,a.published,a.color,b.listid,b.mailid FROM '.acymailing_table('listmail').' as b JOIN '.acymailing_table('list').' as a on a.listid = b.listid WHERE b.mailid = '.intval($mailid);
		if($onlypublished) $query .= ' AND a.published = 1';
		$lists = acymailing_loadObjectList($query, 'listid');

		if(empty($lists) OR !$total) return $lists;

		$config = acymailing_config();
		$confirmed = $config->get('require_confirmation') ? 'b.confirmed = 1 AND' : '';
		$countQuery = 'SELECT a.listid, count(b.subid) as nbsub FROM `#__acymailing_listsub` as a JOIN `#__acymailing_subscriber` as b ON a.subid = b.subid WHERE '.$confirmed.' b.`enabled` = 1 AND b.`accept` = 1 AND a.`status` = 1 AND a.`listid` IN ('.implode(',', array_keys($lists)).') GROUP BY a.`listid`';
		$countResult = acymailing_loadObjectList($countQuery, 'listid');

		foreach($lists as $listid => $count){
			$lists[$listid]->nbsub = empty($countResult[$listid]->nbsub) ? 0 : $countResult[$listid]->nbsub;
		}

		return $lists;
	}

	function getFollowup($listid){
		$query = 'SELECT a.* FROM '.acymailing_table('listmail').' as b LEFT JOIN '.acymailing_table('mail').' as a on a.mailid = b.mailid WHERE b.listid = '.intval($listid).' ORDER BY a.senddate ASC';
		return acymailing_loadObjectList($query);
	}

}


