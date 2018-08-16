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

class listcampaignClass extends acymailingClass{

	function getLists($campaignid){
		$query = 'SELECT a.*,b.campaignid FROM '.acymailing_table('list').' as a LEFT JOIN '.acymailing_table('listcampaign').' as b on a.listid = b.listid AND b.campaignid = '.intval($campaignid).' WHERE a.type = \'list\' ORDER BY b.campaignid DESC, a.ordering ASC';
		return acymailing_loadObjectList($query);
	}

	function save($campaignid,$listids = array()){
		$campaignid = intval($campaignid);
		$query = 'DELETE FROM '.acymailing_table('listcampaign').' WHERE campaignid = '.$campaignid;
		$affected = acymailing_query($query);
		if($affected === false) return false;

		acymailing_arrayToInteger($listids);
		if(empty($listids))	return true;

		$query = 'INSERT IGNORE INTO '.acymailing_table('listcampaign').' (campaignid,listid) VALUES ('.$campaignid.','.implode('),('.$campaignid.',',$listids).')';
		return acymailing_query($query) !== false;
	}

	function getAffectedCampaigns($listids){
		$query = 'SELECT DISTINCT a.campaignid FROM '.acymailing_table('listcampaign').' as a JOIN '.acymailing_table('list').' as b on a.campaignid = b.listid WHERE a.listid IN ('.implode(',',$listids) .') AND b.type = \'campaign\' AND b.published = 1';
		return acymailing_loadResultArray($query);
	}

}


