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


class archiveViewArchive extends acymailingView{
	function display($tpl = null){
		$function = $this->getLayout();
		if(method_exists($this, $function)) $this->$function();

		parent::display($tpl);
	}

	function forward(){
		$subkeys = acymailing_getVar('string', 'subid', acymailing_getVar('string', 'sub'));
		if(!empty($subkeys)){
			$subid = intval(substr($subkeys, 0, strpos($subkeys, '-')));
			$subkey = substr($subkeys, strpos($subkeys, '-') + 1);
			$receiver = acymailing_loadObject('SELECT * FROM '.acymailing_table('subscriber').' WHERE `subid` = '.intval($subid).' AND `key` = '.acymailing_escapeDB($subkey).' LIMIT 1');
		}
		$currentEmail = acymailing_currentUserEmail();
		if(empty($receiver) AND !empty($currentEmail)){
			$userClass = acymailing_get('class.subscriber');
			$receiver = $userClass->get($currentEmail);
		}
		if(empty($receiver)){
			$receiver = new stdClass();
			$receiver->name = '';
			$receiver->email = '';
		}
		$this->senderName = $receiver->name;
		$this->senderMail = $receiver->email;
		$config = acymailing_config();
		$this->config = $config;

		$js = 'var numForwarders = 1;function addLine(){
							if(numForwarders > 4) return;
							var myTable = window.document.getElementById("friend_table");
							var line1 = document.createElement("tr");
							var tdname = document.createElement("td");
							var itdname = document.createElement("td");
							var line2 = document.createElement("tr");
							var tdemail = document.createElement("td");
							var itdemail = document.createElement("td");

							var inputName = document.createElement("input");
							inputName.type = \'text\';
							inputName.name = \'forwardusers[\'+numForwarders+\'][name]\';
							inputName.style.width = "200px";

							var inputEmail = document.createElement("input");
							inputEmail.type = \'text\';
							inputEmail.name = \'forwardusers[\'+numForwarders+\'][email]\';
							inputEmail.style.width = "200px";

							var nameLabel = document.createElement("label");
							nameLabel.innerHTML="'.acymailing_translation('FRIEND_NAME', true).'";

							var emailLabel = document.createElement("label");
							emailLabel.innerHTML="'.acymailing_translation('FRIEND_EMAIL', true).'";

							tdname.appendChild(nameLabel);
							itdname.appendChild(inputName);
							line1.appendChild(tdname);
							line1.appendChild(itdname);
							myTable.appendChild(line1);

							tdemail.appendChild(emailLabel);
							itdemail.appendChild(inputEmail);
							line2.appendChild(tdemail);
							line2.appendChild(itdemail);
							myTable.appendChild(line2);
							numForwarders++;
			}
';

		acymailing_addScript(true, $js);
		return $this->view();
	}

	private function addFeed(){

		$config = acymailing_config();
		$feedType = $config->get('acyrss_format', '');

		if(empty($feedType)) return;

		$document = JFactory::getDocument();

		$link = '&format=feed&limitstart=';
		if($feedType == 'rss' || $feedType == 'both'){
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$document->addHeadLink(acymailing_route($link.'&type=rss'), 'alternate', 'rel', $attribs);
		}
		if($feedType == 'atom' || $feedType == 'both'){
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$document->addHeadLink(acymailing_route($link.'&type=atom'), 'alternate', 'rel', $attribs);
		}
	}

	function listing(){
		global $Itemid;

		$values = new stdClass();
		$menu = acymailing_getMenu();

		$myItem = empty($Itemid) ? '' : '&Itemid='.$Itemid;
		$this->item = $myItem;

		if(is_object($menu)){
			$menuparams = new acyParameter($menu->params);
		}

		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();

		$paramBase = ACYMAILING_COMPONENT.'.'.$this->getName();
		$pageInfo->filter->order->dir = acymailing_getUserVar($paramBase.".ordering_dir", 'ordering_dir', 'DESC', 'word');
		$pageInfo->filter->order->value = acymailing_getUserVar($paramBase.".ordering", 'ordering', 'senddate', 'cmd');
		if(strtolower($pageInfo->filter->order->dir) !== 'desc') $pageInfo->filter->order->dir = 'asc';
		$pageInfo->search = acymailing_getUserVar($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = strtolower(trim($pageInfo->search));

		$pageInfo->limit->value = acymailing_getUserVar($paramBase.'.list_limit', 'limit', acymailing_getCMSConfig('list_limit'), 'int');
		$pageInfo->limit->start = acymailing_getVar('int', 'limitstart', 0);

		$listClass = acymailing_get('class.list');
		$listid = acymailing_getCID('listid');

		if(empty($listid) && !empty($menuparams)){
			$listid = $menuparams->get('listid');
		}

		$currentUserid = acymailing_currentUserId();
		if(empty($listid)){
			$allLists = $listClass->getLists('listid');
		}else{
			$oneList = $listClass->get($listid);
			if(empty($oneList->listid)) return acymailing_raiseError(E_ERROR, 404, 'Mailing List not found : '.$listid);
			$allLists = array($oneList->listid => $oneList);
			if($oneList->access_sub != 'all' && ($oneList->access_sub == 'none' || empty($currentUserid) || !acymailing_isAllowed($oneList->access_sub))) $allLists = array();
		}

		if(empty($allLists)){
			if(empty($currentUserid)){
				acymailing_askLog();
			}else{
				acymailing_enqueueMessage(acymailing_translation('ACY_NOTALLOWED'), 'error');
				acymailing_redirect(acymailing_completeLink('lists', false, true));
			}
			return false;
		}

		$config = acymailing_config();

		if(!empty($menuparams)){
			$values->suffix = $menuparams->get('pageclass_sfx', '');
			$values->page_title = $menuparams->get('page_title');
			$values->page_heading = ACYMAILING_J16 ? $menuparams->get('page_heading') : $menuparams->get('page_title');
			$values->show_page_heading = ACYMAILING_J16 ? $menuparams->get('show_page_heading', 1) : $menuparams->get('show_page_title', 1);
		}else{
			$values->suffix = '';
			$values->show_page_heading = 1;
		}

		$values->show_description = $config->get('show_description', 1);
		$values->show_senddate = $config->get('show_senddate', 1);
		$values->show_receiveemail = $config->get('show_receiveemail', 0) && acymailing_level(1);
		$values->filter = $config->get('show_filter', 1);

		if(empty($values->page_title)) $values->page_title = (count($allLists) > 1 || empty($listid)) ? acymailing_translation('NEWSLETTERS') : $allLists[$listid]->name;
		if(empty($values->page_heading)) $values->page_heading = (count($allLists) > 1 || empty($listid)) ? acymailing_translation('NEWSLETTERS') : $allLists[$listid]->name;

		if(empty($menuparams)){
			acymailing_addBreadcrumb(acymailing_translation('MAILING_LISTS'), acymailing_completeLink('lists'));
			acymailing_addBreadcrumb($values->page_title);
		}elseif(!$menuparams->get('listid')){
			acymailing_addBreadcrumb($values->page_title);
		}

		acymailing_setPageTitle($values->page_title);

		$this->addFeed();

		$searchMap = array('a.mailid', 'a.subject', 'a.alias', 'a.body');
		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acymailing_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchMap)." LIKE $searchVal";
		}

		$filters[] = 'a.type = \'news\'';

		$noManageableLists = array();
		$currentUserid = acymailing_currentUserId();
		foreach($allLists as &$oneList){
			if(empty($currentUserid)) $noManageableLists[] = $oneList->listid;
			if((int)acymailing_currentUserId() == (int)$oneList->userid) continue;
			if($oneList->access_manage == 'all' || acymailing_isAllowed($oneList->access_manage)) continue;
			$noManageableLists[] = $oneList->listid;
		}

		$accessFilter = '';
		$manageableLists = array_diff(array_keys($allLists), $noManageableLists);
		if(!empty($manageableLists)) $accessFilter = 'c.listid IN ('.implode(',', $manageableLists).')';
		if(!empty($noManageableLists)){
			if(empty($accessFilter)){
				$accessFilter = 'c.listid IN ('.implode(',', $noManageableLists).') AND a.published = 1 AND a.visible = 1';
			}else $accessFilter .= ' OR (c.listid IN ('.implode(',', $noManageableLists).') AND a.published = 1 AND a.visible = 1)';
		}
		if(!empty($accessFilter)) $filters[] = $accessFilter;

		$selection = array_merge($searchMap, array('a.senddate', 'a.created', 'a.visible', 'a.published', 'a.fromname', 'a.fromemail', 'a.replyname', 'a.replyemail', 'a.userid', 'a.summary', 'a.thumb', 'c.listid'));

		$query = 'SELECT "" AS body, "" AS altbody, html AS sendHTML, '.implode(',', $selection);
		$query .= ' FROM '.acymailing_table('listmail').' as c';
		$query .= ' JOIN '.acymailing_table('mail').' as a on a.mailid = c.mailid ';
		$query .= ' WHERE ('.implode(') AND (', $filters).')';
		$query .= ' GROUP BY c.mailid';
		$query .= ' ORDER BY a.'.acymailing_secureField($pageInfo->filter->order->value).' '.acymailing_secureField($pageInfo->filter->order->dir).', c.mailid DESC';

		$rows = acymailing_loadObjectList($query, '', $pageInfo->limit->start, $pageInfo->limit->value);
		$pageInfo->elements->page = count($rows);

		if($pageInfo->limit->value > $pageInfo->elements->page){
			$pageInfo->elements->total = $pageInfo->limit->start + $pageInfo->elements->page;
		}else{
			$queryCount = 'SELECT COUNT(DISTINCT c.mailid) FROM '.acymailing_table('listmail').' as c';
			$queryCount .= ' JOIN '.acymailing_table('mail').' as a on a.mailid = c.mailid ';
			$queryCount .= ' WHERE ('.implode(') AND (', $filters).')';
			$pageInfo->elements->total = acymailing_loadResult($queryCount);
		}

		$currentEmail = acymailing_currentUserEmail();
		if(!empty($currentEmail)){
			$userClass = acymailing_get('class.subscriber');
			$receiver = $userClass->get($currentEmail);
		}
		if(empty($receiver)){
			$receiver = new stdClass();
			$receiver->name = acymailing_translation('VISITOR');
		}
		acymailing_importPlugin('acymailing');
		foreach($rows as $mail){
			if(strpos($mail->subject, "{") !== false){
				acymailing_trigger('acymailing_replacetags', array(&$mail, false));
				acymailing_trigger('acymailing_replaceusertags', array(&$mail, &$receiver, false));
			}
		}

		$pagination = new acyPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);

		$js = 'function changeReceiveEmail(checkedbox){
			var form = document.adminForm;
			if(checkedbox){
				form.nbreceiveemail.value++;
			}else{
				form.nbreceiveemail.value--;
			}

			if(form.nbreceiveemail.value > 0 ){
				document.getElementById(\'receiveemailbox\').className = \'receiveemailbox receiveemailbox_visible\';
			}else{
				document.getElementById(\'receiveemailbox\').className = \'receiveemailbox receiveemailbox_hidden\';
			}
		}
		';

		acymailing_addScript(true, $js);
		if(!empty($menuparams)) {
			$data = $menuparams->get("data", 1);
			if(!empty($data->{"menu-meta_description"})) acymailing_addMetadata('description', $data->{"menu-meta_description"});
			if(!empty($data->{"menu-meta_keywords"})) acymailing_addMetadata('keywords', $data->{"menu-meta_keywords"});
		}

		$orderValues = array();
		$orderValues[] = acymailing_selectOption('senddate', acymailing_translation('SEND_DATE'));
		$orderValues[] = acymailing_selectOption('subject', acymailing_translation('JOOMEXT_SUBJECT'));
		$orderValues[] = acymailing_selectOption('created', acymailing_translation('CREATED_DATE'));
		$orderValues[] = acymailing_selectOption('mailid', acymailing_translation('ACY_ID'));

		$ordering = '';
		if($config->get('show_order', 1) == 1){
			$ordering = '<span style="float:right;" id="orderingoption">';
			$ordering .= acymailing_select($orderValues, 'ordering', 'size="1" style="width:100px;" onchange="this.form.submit();"', 'value', 'text', $pageInfo->filter->order->value);

			$orderDir = array();
			$orderDir[] = acymailing_selectOption('ASC', acymailing_translation('ACY_ASC'));
			$orderDir[] = acymailing_selectOption('DESC', acymailing_translation('ACY_DESC'));
			$ordering .= ' '.acymailing_select($orderDir, 'ordering_dir', 'size="1" style="width:75px;" onchange="this.form.submit();"', 'value', 'text', $pageInfo->filter->order->dir);
			$ordering .= '</span>';
		}

		$this->ordering = $ordering;
		$this->rows = $rows;
		$this->values = $values;
		if(count($allLists) > 1){
			$list = new stdClass();
			$list->listid = 0;
			$list->description = '';
		}else{
			$list = array_pop($allLists);
		}
		$this->list = $list;
		$this->manageableLists = $manageableLists;
		$this->pagination = $pagination;
		$this->pageInfo = $pageInfo;
		$this->config = $config;
	}

	function view(){
		$this->addFeed();

		$frontEndManagement = false;
		$listid = acymailing_getCID('listid');

		$values = new stdClass();
		$values->suffix = '';
		$menu = acymailing_getMenu();

		if(is_object($menu)){
			$menuparams = new acyParameter($menu->params);
		}

		if(!empty($menuparams)){
			$values->suffix = $menuparams->get('pageclass_sfx', '');
		}

		if(empty($listid) && !empty($menuparams)){
			$listid = $menuparams->get('listid');
			if($menuparams->get('menu-meta_description')) acymailing_addMetadata('description', $menuparams->get('menu-meta_description'));
			if($menuparams->get('menu-meta_keywords')) acymailing_addMetadata('keywords', $menuparams->get('menu-meta_keywords'));
			if($menuparams->get('robots')) acymailing_addMetadata('robots', $menuparams->get('robots'));
			if($menuparams->get('page_title')) acymailing_setPageTitle($menuparams->get('page_title'));
		}

		$config = acymailing_config();
		$indexFollow = $config->get('indexFollow', '');
		$tagIndFol = array();
		if(strpos($indexFollow, 'noindex') !== false) $tagIndFol[] = 'noindex';
		if(strpos($indexFollow, 'nofollow') !== false) $tagIndFol[] = 'nofollow';
		if(!empty($tagIndFol)) acymailing_addMetadata('robots', implode(',', $tagIndFol));

		if(!empty($listid)){
			$listClass = acymailing_get('class.list');
			$oneList = $listClass->get($listid);
			if(!empty($oneList->visible) && $oneList->published && (empty($menuparams) || !$menuparams->get('listid'))){
				acymailing_addBreadcrumb($oneList->name, acymailing_completeLink('archive&listid='.$oneList->listid.':'.$oneList->alias));
			}

			$currentUserid = acymailing_currentUserId();
			if(!empty($oneList->listid) && acymailing_level(3)){
				if(!empty($currentUserid) && $currentUserid == (int)$oneList->userid){
					$frontEndManagement = true;
				}
				if(!empty($currentUserid)){
					if($oneList->access_manage == 'all' || acymailing_isAllowed($oneList->access_manage)){
						$frontEndManagement = true;
					}
				}
			}
		}

		$mailid = acymailing_getVar('string', 'mailid', 'nomailid');
		if(empty($mailid)){
			die('This is a Newsletter-template... and you can not access the online version of a Newsletter-template!<br />Please create a Newsletter using your template and then try again your "view it online" link!');
			exit;
		}

		if($mailid == 'nomailid'){
			$query = 'SELECT m.`mailid` FROM `#__acymailing_list` as l JOIN `#__acymailing_listmail` as lm ON l.listid=lm.listid JOIN `#__acymailing_mail` as m on lm.mailid = m.mailid';
			$query .= ' WHERE l.`visible` = 1 AND l.`published` = 1 AND m.`visible`= 1 AND m.`published` = 1 AND m.`type` = "news" AND l.`type` = "list"';
			if(!empty($listid)) $query .= ' AND l.`listid` = '.(int)$listid;
			$query .= ' ORDER BY m.`senddate` DESC, m.`mailid` DESC LIMIT 1';
			$mailid = acymailing_loadResult($query);
		}
		$mailid = intval($mailid);
		if(empty($mailid)) return acymailing_raiseError(E_ERROR, 404, 'Newsletter not found');

		$access_sub = true;

		$mailClass = acymailing_get('helper.mailer');
		$mailClass->loadedToSend = false;
		$oneMail = $mailClass->load($mailid);

		if(empty($oneMail->mailid)){
			return acymailing_raiseError(E_ERROR, 404, 'Newsletter not found : '.$mailid);
		}

		if(!$frontEndManagement AND (!$access_sub OR !$oneMail->published OR !$oneMail->visible)){
			$key = acymailing_getVar('cmd', 'key');
			if(empty($key) OR $key !== $oneMail->key){
				$reason = (!$oneMail->published) ? 'Newsletter not published' : (!$oneMail->visible ? 'Newsletter not visible' : (!$access_sub ? 'Access not allowed' : ''));
				acymailing_enqueueMessage('You can not have access to this e-mail : '.$reason, 'error');
				acymailing_redirect(acymailing_completeLink('lists', false, true));
				return false;
			}
		}

		$fshare = '';
		if(preg_match('#<img[^>]*id="pictshare"[^>]*>#i', $oneMail->body, $pregres) && preg_match('#src="([^"]*)"#i', $pregres[0], $pict)){
			$fshare = $pict[1];
		}elseif(preg_match('#<img[^>]*class="[^"]*pictshare[^"]*"[^>]*>#i', $oneMail->body, $pregres) && preg_match('#src="([^"]*)"#i', $pregres[0], $pict)){
			$fshare = $pict[1];
		}elseif(preg_match('#class="acymailing_content".*(<img[^>]*>)#is', $oneMail->body, $pregres) && preg_match('#src="([^"]*)"#i', $pregres[1], $pict)){
			if(strpos($pregres[1], acymailing_translation('JOOMEXT_READ_MORE')) === false) $fshare = $pict[1];
		}

		if(!empty($fshare)){
			acymailing_addMetadata('og:image', $fshare);
		}

		acymailing_addMetadata('og:url', acymailing_frontendLink('archive&task=view&mailid='.$oneMail->mailid, false, acymailing_isNoTemplate(), true));
		acymailing_addMetadata('og:title', $oneMail->subject);
		if(!empty($oneMail->metadesc)) acymailing_addMetadata('og:description', $oneMail->metadesc);

		$subkeys = acymailing_getVar('string', 'subid', acymailing_getVar('string', 'sub'));
		if(!empty($subkeys)){
			$subid = intval(substr($subkeys, 0, strpos($subkeys, '-')));
			$subkey = substr($subkeys, strpos($subkeys, '-') + 1);
			$receiver = acymailing_loadObject('SELECT * FROM '.acymailing_table('subscriber').' WHERE `subid` = '.acymailing_escapeDB($subid).' AND `key` = '.acymailing_escapeDB($subkey).' LIMIT 1');
		}

		$currentEmail = acymailing_currentUserEmail();
		if(empty($receiver) AND !empty($currentEmail)){
			$userClass = acymailing_get('class.subscriber');
			$receiver = $userClass->get($currentEmail);
		}

		if(empty($receiver)){
			$receiver = new stdClass();
			$receiver->name = acymailing_translation('VISITOR');
		}

		$oneMail->sendHTML = true;
		acymailing_trigger('acymailing_replaceusertags', array(&$oneMail, &$receiver, false));

		acymailing_addBreadcrumb($oneMail->subject);

		preg_match('@href="{unsubscribe:(.*)}"@', $oneMail->body, $match);//we get the tag unsubscribe
		if(!empty($match)){
			$oneMail->body = str_replace($match[0], 'href="'.$match[1].'"', $oneMail->body);
		}

		acymailing_setPageTitle($oneMail->subject);

		if(!empty($oneMail->metadesc)){
			acymailing_addMetadata('description', $oneMail->metadesc);
		}
		if(!empty($oneMail->metakey)){
			acymailing_addMetadata('keywords', $oneMail->metakey);
		}

		$this->mail = $oneMail;
		$this->frontEndManagement = $frontEndManagement;
		$config = acymailing_config();
		$this->config = $config;
		$this->receiver = $receiver;
		$this->values = $values;

		if($oneMail->html){
			$templateClass = acymailing_get('class.template');
			$templateClass->archiveSection = true;
			$templateClass->displayPreview('newsletter_preview_area', $oneMail->tempid, $oneMail->subject);
		}
	}
}
