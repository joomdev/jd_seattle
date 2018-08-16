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

class listClass extends acymailingClass{

	var $tables = array('listsub', 'listcampaign', 'listmail', 'list');
	var $pkey = 'listid';
	var $namekey = 'alias';
	var $type = 'list';
	var $newlist = false;
	var $allowedFields = array('name', 'description', 'listid', 'published', 'userid', 'alias', 'color', 'visible', 'welmailid', 'unsubmailid', 'type', 'access_sub', 'access_manage', 'languages', 'startrule', 'category', 'ordering');

	function getLists($index = '', $listids = 'all'){
		$onlyListids = array();
		if(strtolower($listids) != 'all'){
			$onlyListids = explode(',', $listids);
			acymailing_arrayToInteger($onlyListids);
		}

		$query = 'SELECT * FROM '.acymailing_table('list').' WHERE type = \''.$this->type.'\' '.(empty($onlyListids) ? '' : 'AND listid IN ('.implode(',', $onlyListids).')').' ORDER BY ordering ASC';
		return acymailing_loadObjectList($query, $index);
	}

	function getAllCampaigns($index = ''){
		$query = 'SELECT * FROM '.acymailing_table('list').' WHERE type = \'campaign\' ORDER BY ordering ASC';
		return acymailing_loadObjectList($query, $index);
	}

	function delete($elements){
		if(!is_array($elements)){
			$elements = array($elements);
		}

		acymailing_arrayToInteger($elements);

		if(empty($elements)) return 0;

		acymailing_query('DELETE FROM #__acymailing_listcampaign WHERE `campaignid` IN ('.implode(',', $elements).')');

		acymailing_query('DELETE #__acymailing_mail, #__acymailing_listmail FROM #__acymailing_mail INNER JOIN #__acymailing_listmail WHERE #__acymailing_mail.mailid=#__acymailing_listmail.mailid AND #__acymailing_mail.type=\'followup\' AND #__acymailing_listmail.listid IN ('.implode(',', $elements).')');

		return parent::delete($elements);
	}

	function getFrontendLists($index = ''){
		$userid = acymailing_currentUserId();
		if(empty($userid)) return array();

		$groups = acymailing_getGroupsByUser(acymailing_currentUserId(), false);

		$possibleValues = array();
		$possibleValues[] = 'access_manage = \'all\'';
		$possibleValues[] = 'userid = '.intval(acymailing_currentUserId());
		foreach($groups as $oneGroup){
			$possibleValues[] = 'access_manage LIKE \'%,'.intval($oneGroup).',%\'';
		}

		$query = 'SELECT * FROM '.acymailing_table('list').' WHERE published = 1 AND type = \''.$this->type.'\' AND ('.implode(' OR ', $possibleValues).') ORDER BY ordering ASC';
		return acymailing_loadObjectList($query, $index);
	}

	function getFrontendCampaigns($index = ''){
		$userid = acymailing_currentUserId();
		if(empty($userid)) return array();

		$groups = acymailing_getGroupsByUser($userid, false);

		$possibleValues = array();
		$possibleValues[] = 'access_manage = \'all\'';
		$possibleValues[] = 'userid = '.intval($userid);
		foreach($groups as $oneGroup){
			$possibleValues[] = 'access_manage LIKE \'%,'.intval($oneGroup).',%\'';
		}

		$query = 'SELECT DISTINCT l.* FROM '.acymailing_table('list').' AS l INNER JOIN '.acymailing_table('listcampaign').' AS lc ON l.listid = lc.campaignid WHERE lc.listid IN (SELECT DISTINCT il.listid FROM '.acymailing_table('listcampaign').' AS ilc INNER JOIN '.acymailing_table('list').' AS il ON ilc.listid = il.listid WHERE il.published = 1 AND il.type = \'list\' AND ('.implode(' OR ', $possibleValues).')) AND l.published = 1 ORDER BY ordering ASC';
		return acymailing_loadObjectList($query, $index);
	}

	function get($listid, $default = null){
		$query = 'SELECT a.*, b.'.$this->cmsUserVars->name.' as creatorname, b.'.$this->cmsUserVars->username.' AS username, b.'.$this->cmsUserVars->email.' AS email FROM '.acymailing_table('list').' as a LEFT JOIN '.acymailing_table($this->cmsUserVars->table, false).' as b on a.userid = b.'.$this->cmsUserVars->id.' WHERE listid = '.intval($listid).' LIMIT 1';
		return acymailing_loadObject($query);
	}

	function saveForm(){

		$list = new stdClass();
		$list->listid = acymailing_getCID('listid');

		$formData = acymailing_getVar('array', 'data', array(), '');

		if(!empty($formData['list']['category']) && $formData['list']['category'] == -1){
			$formData['list']['category'] = acymailing_getVar('string', 'newcategory', '');
		}

		foreach($formData['list'] as $column => $value){
			if(acymailing_isAdmin() || in_array($column, $this->allowedFields)){
				acymailing_secureField($column);
				$list->$column = strip_tags($value);
			}
		}

		$list->description = acymailing_getVar('string', 'editor_description', '', '', ACY_ALLOWHTML);
		if(isset($list->published) && $list->published != 1) $list->published = 0;
		$listid = $this->save($list);
		if(!$listid) return false;

		if(empty($list->listid)){
			$orderClass = acymailing_get('helper.order');
			$orderClass->pkey = 'listid';
			$orderClass->table = 'list';
			$orderClass->groupMap = 'type';
			$orderClass->groupVal = empty($list->type) ? $this->type : $list->type;
			$orderClass->reOrder();

			$this->newlist = true;
		}

		if(!empty($formData['listcampaign'])){
			$affectedLists = array();
			foreach($formData['listcampaign'] as $affectlistid => $receiveme){
				if(!empty($receiveme)){
					$affectedLists[] = $affectlistid;
				}
			}

			$listCampaignClass = acymailing_get('class.listcampaign');
			$listCampaignClass->save($listid, $affectedLists);
		}

		acymailing_setVar('listid', $listid);

		return true;
	}

	function save($list){
		if(empty($list->listid)){
			if(empty($list->userid)){
				$list->userid = acymailing_currentUserId();
			}
			if(empty($list->alias)) $list->alias = $list->name;
		}

		if(isset($list->alias)){
			if(empty($list->alias)) $list->alias = $list->name;
			$list->alias = acymailing_cleanSlug($list->alias);
		}

		acymailing_importPlugin('acymailing');
		if(empty($list->listid)){
			acymailing_trigger('onAcyBeforeListCreate', array(&$list));
			$status = acymailing_insertObject(acymailing_table('list'), $list);
		}else{
			acymailing_trigger('onAcyBeforeListModify', array(&$list));
			$status = acymailing_updateObject(acymailing_table('list'), $list, 'listid');
		}


		if($status) return empty($list->listid) ? $status : $list->listid;
		return false;
	}

	function onlyCurrentLanguage($lists){
		$currentLang = strtolower(acymailing_getLanguageTag());

		$newLists = array();
		foreach($lists as $id => $oneList){
			if($oneList->languages == 'all' OR in_array($currentLang, explode(',', $oneList->languages))){
				$newLists[$id] = $oneList;
			}
		}

		return $newLists;
	}

	function onlyAllowedLists($lists){
		$newLists = array();
		foreach($lists as $id => $oneList){
			if(!$oneList->published) continue;
			if(!acymailing_isAllowed($oneList->access_sub)) continue;
			$newLists[$id] = $oneList;
		}
		return $newLists;
	}

	function getCampaigns($listid){
		if(empty($listid)) return array();

		if(is_array($listid)) $listid = implode(',', $listid);
		$query = 'SELECT  b.listid, b.campaignid FROM '.acymailing_table('list').' as a LEFT JOIN '.acymailing_table('listcampaign').' as b on a.listid = b.listid WHERE a.type = \'list\' AND b.listid IN ( '.$listid.') ORDER BY b.listid';
		$resSql = acymailing_loadObjectList($query);
		$listCampaigns = array();
		foreach($resSql as $oneList){
			$listCampaigns[$oneList->listid][] = $oneList->campaignid;
		}
		return $listCampaigns;
	}

}
