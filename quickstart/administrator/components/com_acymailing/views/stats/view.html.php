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


class StatsViewStats extends acymailingView{

	var $searchFields = array('b.subject', 'b.alias', 'a.mailid');
	var $selectFields = array('b.subject', 'b.alias', 'b.type', 'a.*', 'a.bouncedetails');
	var $searchHistory = array('b.subject', 'c.email', 'c.name');
	var $historyFields = array('a.*', 'b.subject', 'c.email', 'c.name');
	var $detailSearchFields = array('b.subject', 'b.alias', 'a.mailid', 'c.name', 'c.email', 'a.subid');
	var $detailSelectFields = array('b.subject', 'b.alias', 'c.name', 'c.email', 'b.type', 'a.ip', 'a.*');


	function display($tpl = null){
		$function = $this->getLayout();
		if(method_exists($this, $function)) $this->$function();

		parent::display($tpl);
	}

	function unsubchart(){
		$mailid = acymailing_getVar('int', 'mailid');
		if(empty($mailid)) return;

		acymailing_addStyle(false, ACYMAILING_CSS.'acyprint.css?v='.filemtime(ACYMAILING_MEDIA.'css'.DS.'acyprint.css'), 'text/css', 'print');

		$entries = acymailing_loadObjectList('SELECT * FROM #__acymailing_history WHERE mailid = '.intval($mailid).' AND action="unsubscribed" LIMIT 10000');

		if(empty($entries)){
			acymailing_display("No data recorded for that Newsletter", 'warning');
			return;
		}

		$acyToolbar = acymailing_get('helper.toolbar');
		$acyToolbar->link(acymailing_completeLink((acymailing_isAdmin() ? '' : 'front').'stats&task=unsubchart&export=1&mailid='.acymailing_getVar('int', 'mailid'), true), acymailing_translation('ACY_EXPORT'), 'export');
		$acyToolbar->directPrint();
		$acyToolbar->setTitle(acymailing_translation('ACTION_UNSUBSCRIBED'));
		$acyToolbar->display();

		$unsubreasons = array();
		$unsubreasons['NO_REASON'] = 0;
		foreach($entries as $oneEntry){
			if(empty($oneEntry->data)){
				$unsubreasons['NO_REASON']++;
				continue;
			}

			$allReasons = explode("\n", $oneEntry->data);
			$added = false;
			foreach($allReasons as $oneReason){
				list($reason, $value) = explode('::', $oneReason);
				if(empty($value) || $reason != 'REASON') continue;
				$unsubreasons[$value] = @$unsubreasons[$value] + 1;
				$added = true;
			}
			if(!$added) $unsubreasons['NO_REASON']++;
		}

		$finalReasons = array();
		foreach($unsubreasons as $oneReason => $total){
			$name = $oneReason;
			if(preg_match('#^[A-Z_]*$#', $name)) $name = acymailing_translation($name);
			$finalReasons[$name] = $total;
		}

		arsort($finalReasons);

		acymailing_addScript(false, "https://www.google.com/jsapi");

		$this->unsubreasons = $finalReasons;

		if(acymailing_getVar('cmd', 'export')){
			$exportHelper = acymailing_get('helper.export');
			$exportHelper->exportOneData($finalReasons, 'unsub_'.acymailing_getVar('int', 'mailid'));
		}
	}

	function forward(){
		$this->unsubscribed();
	}

	function unsubscribed(){

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();

		$paramBase = ACYMAILING_COMPONENT.'.'.$this->getName().$this->getLayout();
		$pageInfo->filter->order->value = acymailing_getUserVar($paramBase.".filter_order", 'filter_order', 'a.date', 'cmd');
		$pageInfo->filter->order->dir = acymailing_getUserVar($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		if(strtolower($pageInfo->filter->order->dir) !== 'desc') $pageInfo->filter->order->dir = 'asc';
		$pageInfo->search = acymailing_getUserVar($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = strtolower(trim($pageInfo->search));
		$selectedMail = acymailing_getUserVar($paramBase."filter_mail", 'filter_mail', 0, 'int');
		$pageInfo->limit->value = acymailing_getUserVar($paramBase.'.list_limit', 'limit', acymailing_getCMSConfig('list_limit'), 'int');
		$pageInfo->limit->start = acymailing_getVar('int', 'start', acymailing_getUserVar($paramBase.'.limitstart', 'limitstart', 0, 'int'));

		$filters = array();
		$filters[] = "a.action = ".acymailing_escapeDB($this->getLayout());

		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acymailing_getEscaped($pageInfo->search).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $this->searchHistory)." LIKE $searchVal";
		}

		if(!empty($selectedMail)){
			$filters[] = 'a.mailid = '.$selectedMail;
		}

		$query = 'SELECT '.implode(' , ', $this->historyFields).' FROM '.acymailing_table('history').' as a';
		$query .= ' JOIN '.acymailing_table('mail').' as b on a.mailid = b.mailid';
		$query .= ' JOIN '.acymailing_table('subscriber').' as c on a.subid = c.subid';
		$query .= ' WHERE ('.implode(') AND (', $filters).')';
		if(!empty($pageInfo->filter->order->value)) $query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;

		if(empty($pageInfo->limit->value)) $pageInfo->limit->value = 100;

		$rows = acymailing_loadObjectList($query, '', $pageInfo->limit->start, $pageInfo->limit->value);

		$queryCount = 'SELECT COUNT(*) FROM #__acymailing_history as a';
		if(!empty($pageInfo->search)){
			$queryCount .= ' JOIN '.acymailing_table('mail').' as b on a.mailid = b.mailid';
			$queryCount .= ' JOIN '.acymailing_table('subscriber').' as c on a.subid = c.subid';
		}
		$queryCount .= ' WHERE ('.implode(') AND (', $filters).')';
		
		$pageInfo->elements->total = acymailing_loadResult($queryCount);
		$pageInfo->elements->page = count($rows);

		$pagination = new acyPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);

		$query = 'SELECT DISTINCT a.mailid FROM `#__acymailing_history` as a WHERE a.action = '.acymailing_escapeDB($this->getLayout()).' AND a.mailid > 0';
		$allMailids = acymailing_loadResultArray($query);

		$emails = array();
		if(!empty($allMailids)){
			if(!empty($selectedMail) && !in_array($selectedMail, $allMailids)) array_unshift($allMailids, $selectedMail);
			$query = 'SELECT subject, mailid FROM `#__acymailing_mail` WHERE mailid IN ('.implode(',', $allMailids).') ORDER BY mailid DESC';
			$emails = acymailing_loadObjectList($query);
		}


		$newsletters = array();
		$newsletters[] = acymailing_selectOption('0', acymailing_translation('ALL_EMAILS'));
		foreach($emails as $oneMail){
			if(!empty($oneMail->subject)) $oneMail->subject = acyEmoji::Decode($oneMail->subject);
			$newsletters[] = acymailing_selectOption($oneMail->mailid, $oneMail->subject);
		}
		$filterMail = acymailing_select($newsletters, 'filter_mail', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', (int)$selectedMail);

		if(acymailing_isAdmin() && acymailing_isNoTemplate()){
			$acyToolbar = acymailing_get('helper.toolbar');
			if(!empty($rows)) $acyToolbar->custom('export'.ucfirst(acymailing_getVar('cmd', 'task')), acymailing_translation('ACY_EXPORT'), 'export', false, '');
			$acyToolbar->custom('', acymailing_translation('ACY_CANCEL'), 'cancel', false, 'location.href=\''.acymailing_completeLink('diagram&task=mailing&mailid='.acymailing_getVar('int', 'filter_mail'), true).'\';');
			$acyToolbar->setTitle(acymailing_translation($this->getLayout() == 'forward' ? 'FORWARDED' : 'UNSUBSCRIBECAPTION'));
			$acyToolbar->topfixed = false;
			$acyToolbar->display();
		}elseif(acymailing_isNoTemplate()){
			$filterMail = '<input type="hidden" value="'.acymailing_getVar('int', 'mailid').'" name="mailid" />';
			$filterMail .= '<input type="hidden" value="'.acymailing_getVar('int', 'filter_mail').'" name="filter_mail" />';
		}

		$this->filterMail = $filterMail;
		$this->rows = $rows;
		$this->pageInfo = $pageInfo;
		$this->pagination = $pagination;

		$this->setLayout('unsubscribed');
	}

	function detaillisting(){

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();
		$config = acymailing_config();

		$paramBase = ACYMAILING_COMPONENT.'.'.$this->getName().$this->getLayout();
		$pageInfo->filter->order->value = acymailing_getUserVar($paramBase.".filter_order", 'filter_order', 'a.senddate', 'cmd');
		$pageInfo->filter->order->dir = acymailing_getUserVar($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		if(strtolower($pageInfo->filter->order->dir) !== 'desc') $pageInfo->filter->order->dir = 'asc';
		$pageInfo->search = acymailing_getUserVar($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = strtolower(trim($pageInfo->search));
		$selectedMail = acymailing_getUserVar($paramBase."filter_mail", 'filter_mail', 0, 'int');
		$selectedStatus = acymailing_getUserVar($paramBase."filter_status", 'filter_status', 0, 'string');
		$selectedBounce = acymailing_getUserVar($paramBase."filter_bounce", 'filter_bounce', 0, 'string');

		$pageInfo->limit->value = acymailing_getUserVar($paramBase.'.list_limit', 'limit', acymailing_getCMSConfig('list_limit'), 'int');
		$pageInfo->limit->start = acymailing_getUserVar($paramBase.'.limitstart', 'limitstart', 0, 'int');

		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acymailing_getEscaped($pageInfo->search).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $this->detailSearchFields)." LIKE $searchVal";
		}

		if(!empty($selectedMail)) $filters[] = 'a.mailid = '.$selectedMail;
		if(!empty($selectedStatus)){
			if($selectedStatus == 'bounce'){
				$filters[] = 'a.bounce > 0';
			}elseif($selectedStatus == 'open') $filters[] = 'a.open > 0';
			elseif($selectedStatus == 'notopen') $filters[] = 'a.open < 1';
			elseif($selectedStatus == 'failed') $filters[] = 'a.fail > 0';
		}
		if(!empty($selectedStatus) && $selectedStatus == 'bounce' && !empty($selectedBounce)) $filters[] = 'a.bouncerule='.acymailing_escapeDB($selectedBounce);

		$extrajoin = '';

		$query = 'SELECT '.implode(' , ', $this->detailSelectFields);
		$query .= ' FROM '.acymailing_table('userstats').' as a';
		$query .= ' JOIN '.acymailing_table('mail').' as b on a.mailid = b.mailid';
		$query .= ' JOIN '.acymailing_table('subscriber').' as c on a.subid = c.subid';
		$query .= $extrajoin;
		if(!empty($filters)) $query .= ' WHERE ('.implode(') AND (', $filters).')';
		if(!empty($pageInfo->filter->order->value)) $query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;

		if(empty($pageInfo->limit->value)) $pageInfo->limit->value = 100;

		$rows = acymailing_loadObjectList($query, '', $pageInfo->limit->start, $pageInfo->limit->value);

		if($rows === null){
			acymailing_display(substr(strip_tags(acymailing_getDBError()), 0, 200).'...', 'error');
			if(file_exists(ACYMAILING_BACK.'install.joomla.php')){
				include_once(ACYMAILING_BACK.'install.joomla.php');
				$installClass = new acymailingInstall();
				$installClass->fromVersion = '3.7.0';
				$installClass->update = true;
				$installClass->updateSQL();
			}
		}

		$queryCount = 'SELECT COUNT(a.subid) FROM #__acymailing_userstats as a';
		$queryCount .= ' JOIN '.acymailing_table('mail').' as b on a.mailid = b.mailid';
		if(!empty($pageInfo->search)){
			$queryCount .= ' JOIN '.acymailing_table('subscriber').' as c on a.subid = c.subid';
		}
		$queryCount .= $extrajoin;
		if(!empty($filters)) $queryCount .= ' WHERE ('.implode(') AND (', $filters).')';
		
		$pageInfo->elements->total = acymailing_loadResult($queryCount);
		$pageInfo->elements->page = count($rows);

		$pagination = new acyPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);

		$toggleClass = acymailing_get('helper.toggle');

		$maildetailstatstype = acymailing_get('type.detailstatsmail');
		$deliverstatus = acymailing_get('type.deliverstatus');
		$filtersType = new stdClass();
		if(!acymailing_isAdmin()){
			$filtersType->mail = '<input type="hidden" value="'.$selectedMail.'" name="filter_mail" />';
			$mailClass = acymailing_get('class.mail');
			$this->mailing = $mailClass->get($selectedMail);
		}else{
			$filtersType->mail = $maildetailstatstype->display('filter_mail', $selectedMail);
		}
		$filtersType->status = $deliverstatus->display('filter_status', $selectedStatus);

		$detailstatsbouncetype = acymailing_get('type.detailstatsbounce');
		if(!empty($selectedStatus) && $selectedStatus == 'bounce'){
			$filtersType->bounce = $detailstatsbouncetype->display('filter_bounce', $selectedBounce);
		}else $filtersType->bounce = '';

		if(acymailing_isAdmin()){
			$acyToolbar = acymailing_get('helper.toolbar');
			if(acymailing_isNoTemplate()){
				if(acymailing_isAllowed($config->get('acl_subscriber_export', 'all'))) $acyToolbar->custom('export', acymailing_translation('ACY_EXPORT'), 'export', false);
				$acyToolbar->custom('', acymailing_translation('ACY_CANCEL'), 'cancel', false, 'location.href=\''.acymailing_completeLink('diagram&task=mailing&mailid='.acymailing_getVar('int', 'filter_mail'), true).'\';');
				$acyToolbar->setTitle(acymailing_translation('DETAILED_STATISTICS'));
				$acyToolbar->topfixed = false;
			}else{
				if(acymailing_isAllowed($config->get('acl_subscriber_export', 'all'))){
					$acyToolbar->custom('export', acymailing_translation('ACY_EXPORT'), 'export', false);
				}
				$acyToolbar->link(acymailing_completeLink('stats'), acymailing_translation('GLOBAL_STATISTICS'), 'cancel');
				$acyToolbar->divider();
				$acyToolbar->help('statistics');
				$acyToolbar->setTitle(acymailing_translation('DETAILED_STATISTICS'), 'stats&task=detaillisting');
			}
			$acyToolbar->display();
		}
		
		if(acymailing_isNoTemplate()){
			$filtersType->mail = '<input type="hidden" value="'.acymailing_getVar('int', 'mailid').'" name="mailid" />';
			$filtersType->mail .= '<input type="hidden" value="'.acymailing_getVar('int', 'filter_mail').'" name="filter_mail" />';
		}

		$this->filters = $filtersType;
		$this->toggleClass = $toggleClass;
		$this->rows = $rows;
		$this->pageInfo = $pageInfo;
		$this->pagination = $pagination;
	}

	function listing(){
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();
		$config = acymailing_config();

		$paramBase = ACYMAILING_COMPONENT.'.'.$this->getName().$this->getLayout();
		$pageInfo->filter->order->value = acymailing_getUserVar($paramBase.".filter_order", 'filter_order', 'a.senddate', 'cmd');
		$pageInfo->filter->order->dir = acymailing_getUserVar($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		if(strtolower($pageInfo->filter->order->dir) !== 'desc') $pageInfo->filter->order->dir = 'asc';
		$pageInfo->search = acymailing_getUserVar($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = strtolower(trim($pageInfo->search));
		$selectedTags = acymailing_getUserVar($paramBase."filter_tags", 'filter_tags', array(), 'array');

		$pageInfo->limit->value = acymailing_getUserVar($paramBase.'.list_limit', 'limit', acymailing_getCMSConfig('list_limit'), 'int');
		$pageInfo->limit->start = acymailing_getUserVar($paramBase.'.limitstart', 'limitstart', 0, 'int');

		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acymailing_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $this->searchFields)." LIKE $searchVal";
		}

		$listClass = acymailing_get('class.list');
		if(acymailing_isAdmin()) {
			$lists = $listClass->getLists();
		}else {
			$lists = $listClass->getFrontendLists();
		}
		$msgType = array();
		$msgType[] = acymailing_selectOption('0', acymailing_translation('ALL_EMAILS'));
		$msgType[] = acymailing_selectOption('<OPTGROUP>', acymailing_translation('NEWSLETTER'));
		if(acymailing_isAdmin()) $msgType[] = acymailing_selectOption('news', acymailing_translation('ALL_LISTS'));
		foreach($lists as $oneList){
			$msgType[] = acymailing_selectOption('list_'.$oneList->listid, $oneList->name);
		}
		$msgType[] = acymailing_selectOption('</OPTGROUP>');

		if(acymailing_isAdmin()) {
			$msgType[] = acymailing_selectOption('notification', acymailing_translation('NOTIFICATIONS'));
			if (acymailing_level(1)) {
				$msgType[] = acymailing_selectOption('autonews', acymailing_translation('AUTONEW'));
				$msgType[] = acymailing_selectOption('joomlanotification', acymailing_translation('JOOMLA_NOTIFICATIONS'));
			}
			if (acymailing_level(3)) {
				$listCampaign = acymailing_get('class.list');
				$listCampaign->type = 'campaign';
				$campaigns = $listCampaign->getLists();
				$msgType[] = acymailing_selectOption('<OPTGROUP>', acymailing_translation('FOLLOWUP'));
				$msgType[] = acymailing_selectOption('followup', acymailing_translation('ACY_ALL_CAMPAIGNS'));
				foreach ($campaigns as $oneCamp) {
					$msgType[] = acymailing_selectOption('camp_' . $oneCamp->listid, $oneCamp->name);
				}
				$msgType[] = acymailing_selectOption('</OPTGROUP>');
			}
			$msgType[] = acymailing_selectOption('welcome', acymailing_translation('MSG_WELCOME'));
			$msgType[] = acymailing_selectOption('unsub', acymailing_translation('MSG_UNSUB'));
			if (acymailing_level(3)) {
				$msgType[] = acymailing_selectOption('action', acymailing_translation('ACY_DISTRIBUTION'));
			}
		}

		$selectedMsgType = acymailing_getUserVar($paramBase."filter_msg", 'filter_msg', 0, 'string');
		$msgTypeChoice = acymailing_select($msgType, "filter_msg", 'class="inputbox" style="max-width: 200px;" onchange="document.adminForm.limitstart.value=0;document.adminForm.submit( );"', 'value', 'text', $selectedMsgType);
		$extraJoin = '';

		if(!empty($selectedMsgType)){
			$subfilter = substr($selectedMsgType, 0, 5);
			if($subfilter == 'camp_' || $subfilter == 'list_'){
				$filters[] = " b.type = '".($subfilter == 'camp_' ? 'followup' : 'news')."'";
				$filters[] = " lm.listid = ".substr($selectedMsgType, 5);
				$extraJoin .= " JOIN #__acymailing_listmail AS lm ON a.mailid = lm.mailid";
			}else{
				$filters[] = " b.type = '".$selectedMsgType."'";
			}
		}elseif (!acymailing_isAdmin()) {
			if (!empty($lists)) {
				$frontListsIds = array();
				foreach ($lists as $oneList) {
					$frontListsIds[] = $oneList->listid;
				}
				$extraJoin .= " JOIN #__acymailing_listmail AS lm ON a.mailid = lm.mailid";
				$filters[] = 'lm.listid IN (' . implode(',', $frontListsIds) . ')';
			}
		}

		if(!empty($selectedTags) && count($selectedTags) > 1){
			$tagCondition = array();
			foreach($selectedTags as $oneTag){
				if(strpos($oneTag, '|') === false) continue;
				$tag = explode('|', $oneTag);
				$tagCondition[] = intval($tag[0]);
			}
			$extraJoin .= ' JOIN #__acymailing_tagmail AS tm ON b.mailid = tm.mailid AND tagid IN ('.implode(',', $tagCondition).') ';
		}

		$query = 'SELECT '.implode(' , ', $this->selectFields);
		$query .= ', CASE WHEN (a.senthtml+a.senttext) <= a.bounceunique THEN 0 ELSE (a.openunique/(a.senthtml+a.senttext-a.bounceunique)) END AS openprct';
		$query .= ', CASE WHEN (a.senthtml+a.senttext) <= a.bounceunique THEN 0 ELSE (a.clickunique/(a.senthtml+a.senttext-a.bounceunique)) END AS clickprct';
		$query .= ', CASE WHEN a.openunique = 0 THEN 0 ELSE (a.clickunique/a.openunique) END AS efficiencyprct';
		$query .= ', CASE WHEN (a.senthtml+a.senttext) <= a.bounceunique THEN 0 ELSE (a.unsub/(a.senthtml+a.senttext-a.bounceunique)) END AS unsubprct';
		$query .= ', (a.senthtml+a.senttext) as totalsent';
		$query .= ', CASE WHEN (a.senthtml+a.senttext) = 0 THEN 0 ELSE (a.bounceunique/(a.senthtml+a.senttext)) END AS bounceprct';
		$query .= ' FROM '.acymailing_table('stats').' as a';
		$query .= ' JOIN '.acymailing_table('mail').' as b on a.mailid = b.mailid';
		if(!empty($extraJoin)) $query .= $extraJoin;
		if(!empty($filters)) $query .= ' WHERE ('.implode(') AND (', $filters).')';
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' GROUP BY b.mailid ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}

		$rows = acymailing_loadObjectList($query, '', $pageInfo->limit->start, $pageInfo->limit->value);

		if($rows === null){
			acymailing_display(substr(strip_tags(acymailing_getDBError()), 0, 200).'...', 'error');
			if(file_exists(ACYMAILING_BACK.'install.joomla.php')){
				include_once(ACYMAILING_BACK.'install.joomla.php');
				$installClass = new acymailingInstall();
				$installClass->fromVersion = '3.6.0';
				$installClass->update = true;
				$installClass->updateSQL();
			}
		}

		$queryCount = 'SELECT COUNT(a.mailid) FROM '.acymailing_table('stats').' as a';
		if(!empty($pageInfo->search) || !empty($filters) || !empty($extraJoin)){
			$queryCount .= ' JOIN '.acymailing_table('mail').' as b on a.mailid = b.mailid';
			if(!empty($extraJoin)) $queryCount .= $extraJoin;
		}
		if(!empty($filters)) $queryCount .= ' WHERE ('.implode(') AND (', $filters).')';

		$pageInfo->elements->total = acymailing_loadResult($queryCount);
		$pageInfo->elements->page = count($rows);

		$pagination = new acyPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);

		if(acymailing_level(3)) {
			$tagfieldtype = acymailing_get('type.tagfield');
			$tagfieldtype->onclick = 'document.adminForm.submit();';
			$tagChoice = $tagfieldtype->display('filter_tags', 'listing', $selectedTags);
			$this->filterTag = $tagChoice;
		}

		$menuparams = new acyParameter();

		if(acymailing_isAdmin()) {
			$acyToolbar = acymailing_get('helper.toolbar');

			$acyToolbar->divider();
			$acyToolbar->custom('compare', trim(acymailing_translation('ACY_COMPARE'), '.') . (empty($_SESSION['acycomparison']) ? '' : ' (' . count($_SESSION['acycomparison']) . ')'), 'detailed-stat', false);
			$acyToolbar->custom('addcompare', acymailing_translation('ACY_ADD'), 'addcompare', true, '', acymailing_translation('ACY_ADD_COMPARE'));
			$acyToolbar->custom('resetcompare', acymailing_translation('JOOMEXT_RESET'), 'resetcompare', false);
			$acyToolbar->divider();
			$acyToolbar->custom('exportglobal', acymailing_translation('ACY_EXPORT'), 'export', false);
			if (acymailing_isAllowed($config->get('acl_statistics_delete', 'all'))) $acyToolbar->delete();
			$acyToolbar->divider();
			$acyToolbar->help('statistics');
			$acyToolbar->setTitle(acymailing_translation('GLOBAL_STATISTICS'), 'stats');
			$acyToolbar->display();
		}else {
			$menuparams = new acyParameter(array(
				'number' => 1,
				'opens' => 1,
				'clicks' => 1,
				'efficiency' => 0,
				'unsubscribe' => 1,
				'forward' => 0,
				'sent' => 1,
				'bounces' => 0,
				'failed' => 0,
				'id' => 1
			));

			$menu = acymailing_getMenu();

			if(is_object($menu)){
				$menuparams = new acyParameter($menu->params);
			}
		}

		$this->menuparams = $menuparams;
		$this->config = $config;
		$this->rows = $rows;
		$this->pageInfo = $pageInfo;
		$this->pagination = $pagination;
		$this->filterMsg = $msgTypeChoice;
	}

	function mailinglist($export = 0){
		$mailid = acymailing_getVar('int', 'mailid');
		if(empty($mailid)) return;

		acymailing_addStyle(false, ACYMAILING_CSS.'acyprint.css?v='.filemtime(ACYMAILING_MEDIA.'css'.DS.'acyprint.css'), 'text/css', 'print');

		$mailClass = acymailing_get('class.mail');
		$mailing = $mailClass->get($mailid);

		$mydata = array();
		$isData = true;

		if($mailing->type == 'followup'){
			$query = 'SELECT l.listid, l.name, l.color FROM #__acymailing_list l';
			$query .= ' JOIN #__acymailing_listcampaign lc ON l.listid = lc.listid';
			$query .= ' JOIN #__acymailing_listmail lm ON lc.campaignid = lm.listid';
			$query .= ' WHERE lm.mailid = '.intval($mailid).' ORDER BY l.ordering';
			$sqlRes = acymailing_loadObjectList($query);
		}else{
			$query = 'SELECT lm.listid, l.name, l.color FROM #__acymailing_list l';
			$query .= ' JOIN #__acymailing_listmail lm ON l.listid=lm.listid';
			$query .= ' WHERE lm.mailid='.intval($mailid).' ORDER BY l.ordering';
			$sqlRes = acymailing_loadObjectList($query);
		}

		if(empty($sqlRes)){
			$query = 'SELECT listid, name, color FROM #__acymailing_list';
			$query .= ' WHERE welmailid='.intval($mailid).' OR unsubmailid='.intval($mailid).' GROUP BY listid';
			$sqlRes = acymailing_loadObjectList($query);
			if(empty($sqlRes)){
				acymailing_display("This newsletter is not assigned to any list", 'warning');
				$isData = false;
				return;
			}
		}

		$arrayColors = array();
		$arrayList = array();
		foreach($sqlRes as $list){
			$mydata[$list->listid] = array();
			$mydata[$list->listid]['listid'] = $list->listid;
			$mydata[$list->listid]['listname'] = $list->name;
			$mydata[$list->listid]['nbMailSent'] = 0;
			$mydata[$list->listid]['nbHtml'] = 0;
			$mydata[$list->listid]['nbOpen'] = 0;
			$mydata[$list->listid]['nbOpenRatio'] = 0;
			$mydata[$list->listid]['nbClic'] = 0;
			$mydata[$list->listid]['nbClicRatio'] = 0;
			$mydata[$list->listid]['nbForward'] = 0;
			$mydata[$list->listid]['nbBounce'] = 0;
			$mydata[$list->listid]['nbBounceRatio'] = 0;
			$mydata[$list->listid]['nbUnsub'] = 0;
			$mydata[$list->listid]['nbUnsubRatio'] = 0;

			$mydata[$list->listid]['color'] = (!empty($list->color) ? $list->color : '#162955');
			array_push($arrayColors, (!empty($list->color) ? $list->color : '#162955'));
			array_push($arrayList, $list->listid);
		}
		$listColors = "'".implode("', '", $arrayColors)."'";
		$listListes = implode(',', $arrayList);

		$query = 'SELECT ls.listid, COUNT(*) as nbSent, SUM(IF(html=1, 1, 0)) as nbHtml, SUM(IF(open<>0, 1, 0)) as nbOpen, SUM(IF(bounce<>0, 1, 0)) as nbBounce ';
		$query .= ' FROM #__acymailing_userstats us JOIN #__acymailing_listsub ls ON us.subid = ls.subid';
		$query .= ' WHERE ls.listid IN ('.$listListes.') AND us.mailid='.intval($mailid).' GROUP BY ls.listid';
		$sqlRes = acymailing_loadObjectList($query);
		$totalSent = 0;
		if(!empty($sqlRes)){
			foreach($sqlRes as $lineRes){
				$mydata[$lineRes->listid]['nbMailSent'] = $lineRes->nbSent;
				$mydata[$lineRes->listid]['nbHtml'] = $lineRes->nbHtml;
				$mydata[$lineRes->listid]['nbOpen'] = $lineRes->nbOpen;
				$mydata[$lineRes->listid]['nbOpenRatio'] = number_format($lineRes->nbOpen / $mydata[$lineRes->listid]['nbHtml'] * 100, 1);
				$mydata[$lineRes->listid]['nbBounce'] = $lineRes->nbBounce;
				$mydata[$lineRes->listid]['nbBounceRatio'] = number_format($lineRes->nbBounce / $mydata[$lineRes->listid]['nbMailSent'] * 100, 1);
				$totalSent += $lineRes->nbSent;
			}
		}else{
			acymailing_display("No statistics recorded", 'warning');
			$isData = false;
			return;
		}

		$query = 'SELECT ls.listid, COUNT(DISTINCT(uc.subid)) AS nbClic FROM #__acymailing_urlclick as uc JOIN #__acymailing_listsub as ls ON uc.subid=ls.subid';
		$query .= ' WHERE ls.listid IN ('.$listListes.') AND uc.mailid='.intval($mailid).' GROUP BY ls.listid';
		$sqlRes = acymailing_loadObjectList($query);
		if(!empty($sqlRes)){
			foreach($sqlRes as $lineRes){
				$mydata[$lineRes->listid]['nbClic'] = $lineRes->nbClic;
				$mydata[$lineRes->listid]['nbClicRatio'] = number_format($lineRes->nbClic / $mydata[$lineRes->listid]['nbHtml'] * 100, 1);
			}
		}

		$query = 'SELECT ls.listid, SUM(IF(h.action=\'forward\', 1, 0)) as nbForward, SUM(IF(h.action=\'unsubscribed\', 1, 0)) as nbUnsub';
		$query .= ' FROM #__acymailing_history as h JOIN #__acymailing_listsub ls ON h.subid=ls.subid';
		$query .= ' WHERE ls.listid IN ('.$listListes.') AND h.mailid='.intval($mailid).' GROUP BY ls.listid';
		$sqlRes = acymailing_loadObjectList($query);
		if(!empty($sqlRes)){
			foreach($sqlRes as $lineRes){
				$mydata[$lineRes->listid]['nbForward'] = $lineRes->nbForward;
				$mydata[$lineRes->listid]['nbUnsub'] = $lineRes->nbUnsub;
				$mydata[$lineRes->listid]['nbUnsubRatio'] = number_format($lineRes->nbUnsub / $mydata[$lineRes->listid]['nbMailSent'] * 100, 1);
			}
		}

		if(acymailing_isAdmin() && acymailing_isNoTemplate()){
			$acyToolbar = acymailing_get('helper.toolbar');
			$acyToolbar->custom('', acymailing_translation('ACY_EXPORT'), 'export', false, 'location.href=\''.acymailing_completeLink('stats&task=mailinglist&export=1&mailid='.acymailing_getVar('int', 'mailid'), true).'\';');
			$acyToolbar->directPrint();
			$acyToolbar->setTitle($mailing->subject);
			$acyToolbar->topfixed = false;
			$acyToolbar->display();
		}
		$this->mydata = $mydata;
		$this->mailing = $mailing;
		$this->listColors = $listColors;
		$this->isData = $isData;
		$this->totalSent = $totalSent;

		if(acymailing_getVar('cmd', 'export')){
			$exportHelper = acymailing_get('helper.export');
			$config = acymailing_config();
			$encodingClass = acymailing_get('helper.encoding');

			$exportHelper->addHeaders('mailingList_'.acymailing_getVar('int', 'mailid'));

			$eol = "\r\n";
			$before = '"';
			$separator = '"'.str_replace(array('semicolon', 'comma'), array(';', ','), $config->get('export_separator', ';')).'"';
			$exportFormat = $config->get('export_format', 'UTF-8');
			$after = '"';

			$titles = array(acymailing_translation('LIST'), acymailing_translation('LIST_NAME'), acymailing_translation('ACY_SENT_EMAILS'), acymailing_translation('SENT_HTML'), acymailing_translation('OPEN'), acymailing_translation('OPEN').' (%)', acymailing_translation('CLICKED_LINK'), acymailing_translation('CLICKED_LINK').' (%)', acymailing_translation('FORWARDED'), acymailing_translation('BOUNCES'), acymailing_translation('BOUNCES').' (%)', acymailing_translation('UNSUBSCRIBED'), acymailing_translation('UNSUBSCRIBED').' (%)', acymailing_translation('COLOUR'));
			$titleLine = $before.implode($separator, $titles).$after.$eol;
			echo $titleLine;

			foreach($mydata as $listid => $listDetails){
				$line = '';
				foreach($listDetails as $name => $value){
					$line .= $value.$separator;
				}
				$line = substr($line, 0, strlen($line) - strlen($separator));
				$line = $before.$encodingClass->change($line, 'UTF-8', $exportFormat).$after.$eol;
				echo $line;
			}
			exit;
		}
	}

	function compare(){
		if(empty($_SESSION['acycomparison'])){
			acymailing_enqueueMessage(acymailing_translation('ACY_MIN_COMPARE'), 'info');
			acymailing_redirect(acymailing_completeLink('stats', false, true));
			return;
		}

		acymailing_arrayToInteger($_SESSION['acycomparison']);

		$rows = acymailing_loadObjectList('SELECT stats.*, mail.subject, mail.alias 
							FROM '.acymailing_table('stats').' AS stats 
							JOIN '.acymailing_table('mail').' AS mail 
								ON stats.mailid = mail.mailid 
							WHERE stats.mailid IN ('.implode(',', $_SESSION['acycomparison']).')');

		$acyToolbar = acymailing_get('helper.toolbar');
		$acyToolbar->custom('exportglobal', acymailing_translation('ACY_EXPORT'), 'export', false);
		$acyToolbar->custom('resetcompare', acymailing_translation('JOOMEXT_RESET'), 'resetcompare', false);
		$acyToolbar->cancel();
		$acyToolbar->divider();
		$acyToolbar->help('compare');
		$acyToolbar->setTitle(acymailing_translation('ACY_COMPARE_PAGE'), 'stats&task=compare');
		$acyToolbar->display();

		$this->rows = $rows;
	}
}
