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


class NewsletterViewNewsletter extends acymailingView{
	var $type = 'news';
	var $ctrl = 'newsletter';
	var $nameListing = 'NEWSLETTERS';
	var $nameForm = 'NEWSLETTER';
	var $icon = 'newsletter';
	var $aclCat = 'newsletters';
	var $doc = 'newsletters';

	function display($tpl = null){
		$function = $this->getLayout();
		if(method_exists($this, $function)) $this->$function();

		parent::display($tpl);
	}

	function listing(){
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();

		$config = acymailing_config();

		$paramBase = ACYMAILING_COMPONENT.'.'.$this->getName();
		$pageInfo->filter->order->value = acymailing_getUserVar($paramBase.".filter_order", 'filter_order', 'a.mailid', 'cmd');
		$pageInfo->filter->order->dir = acymailing_getUserVar($paramBase.".filter_order_Dir", 'filter_order_Dir', 'desc', 'word');
		if(strtolower($pageInfo->filter->order->dir) !== 'desc') $pageInfo->filter->order->dir = 'asc';

		$pageInfo->search = acymailing_getUserVar($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = strtolower(trim($pageInfo->search));
		$selectedList = acymailing_getUserVar($paramBase."filter_list", 'filter_list', 0, 'int');
		$selectedCreator = acymailing_getUserVar($paramBase."filter_creator", 'filter_creator', 0, 'int');
		$selectedTags = acymailing_getUserVar($paramBase."filter_tags", 'filter_tags', array(), 'array');
		
		$pageInfo->limit->value = acymailing_getUserVar($paramBase.'.list_limit', 'limit', acymailing_getCMSConfig('list_limit'), 'int');
		$pageInfo->limit->start = acymailing_getUserVar($paramBase.'.limitstart', 'limitstart', 0, 'int');

		$searchMap = array('a.mailid', 'a.alias', 'a.subject', 'a.fromname', 'a.fromemail', 'a.replyname', 'a.replyemail', 'a.userid', 'b.'.$this->cmsUserVars->name, 'b.'.$this->cmsUserVars->username, 'b.'.$this->cmsUserVars->email);
		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acymailing_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $searchMap)." LIKE $searchVal";
		}

		if($this->type == 'news'){
			$actionExists = acymailing_loadResult('SELECT mailid FROM #__acymailing_mail WHERE type = "action" LIMIT 1');

			$selectedType = acymailing_getUserVar($paramBase."filter_type", 'filter_type', 'news', 'string');
			if(!empty($selectedType) && $actionExists){
				$filters[] = 'a.type = '.acymailing_escapeDB($selectedType);
			}else{
				$filters[] = 'a.type IN ("news","action")';
			}
		}else{
			$filters[] = 'a.type = \''.$this->type.'\'';
		}

		if(!empty($selectedList)) $filters[] = 'c.listid = '.$selectedList;
		if(!empty($selectedCreator)) $filters[] = 'a.userid = '.$selectedCreator;
		if($this->type == 'news'){
			$selectedDate = acymailing_getUserVar($paramBase."filter_date", 'filter_date', 0, 'string');
			if(!empty($selectedDate)){
				if(strlen($selectedDate) > 4){
					$filters[] = 'DATE_FORMAT(FROM_UNIXTIME(senddate),"%Y-%m") = '.acymailing_escapeDB($selectedDate);
				}else $filters[] = 'DATE_FORMAT(FROM_UNIXTIME(senddate),"%Y") = '.acymailing_escapeDB($selectedDate);
			}
		}

		$selection = array('a.mailid', 'a.alias', 'a.subject', 'a.fromname', 'a.fromemail', 'a.replyname', 'a.replyemail', 'a.userid', 'b.'.$this->cmsUserVars->name.' AS name', 'b.'.$this->cmsUserVars->username.' AS username', 'b.'.$this->cmsUserVars->email.' AS email', 'a.created', 'a.frequency', 'a.senddate', 'a.published', 'a.type', 'a.visible', 'a.abtesting');

		if(empty($selectedList)){
			if(acymailing_isAdmin()){
				$query = 'SELECT '.implode(',', $selection).' FROM '.acymailing_table('mail').' as a';
				$queryCount = 'SELECT COUNT(a.mailid) FROM '.acymailing_table('mail').' as a';
			}else{
				$query = 'SELECT '.implode(',', $selection).' FROM '.acymailing_table('listmail').' as c';
				$query .= ' JOIN '.acymailing_table('mail').' as a on a.mailid = c.mailid ';
				$queryCount = 'SELECT COUNT(DISTINCT c.mailid) FROM '.acymailing_table('listmail').' as c';
				$queryCount .= ' JOIN '.acymailing_table('mail').' as a on a.mailid = c.mailid ';
			}
		}else{
			$query = 'SELECT '.implode(',', $selection).' FROM '.acymailing_table('listmail').' as c';
			$query .= ' JOIN '.acymailing_table('mail').' as a on a.mailid = c.mailid ';
			$queryCount = 'SELECT COUNT(c.mailid) FROM '.acymailing_table('listmail').' as c';
			$queryCount .= ' JOIN '.acymailing_table('mail').' as a on a.mailid = c.mailid ';
		}

		$query .= ' LEFT JOIN '.acymailing_table($this->cmsUserVars->table, false).' as b on a.userid = b.'.$this->cmsUserVars->id;

		if(!empty($selectedTags) && count($selectedTags) > 1){
			$tagCondition = array();
			foreach($selectedTags as $oneTag){
				if(strpos($oneTag, '|') === false) continue;
				$tag = explode('|', $oneTag);
				$tagCondition[] = intval($tag[0]);
			}
			$query .= ' JOIN #__acymailing_tagmail AS tm ON a.mailid = tm.mailid AND tagid IN ('.implode(',', $tagCondition).') ';
			$queryCount .= ' JOIN #__acymailing_tagmail AS tm ON a.mailid = tm.mailid AND tagid IN ('.implode(',', $tagCondition).') ';
		}

		$query .= ' WHERE ('.implode(') AND (', $filters).')';

		if(!empty($pageInfo->search)) $queryCount .= ' LEFT JOIN '.acymailing_table($this->cmsUserVars->table, false).' as b on a.userid = b.'.$this->cmsUserVars->id;

		$queryCount .= ' WHERE ('.implode(') AND (', $filters).')';

		$listClass = acymailing_get('class.list');
		if(!acymailing_isAdmin()){
			$lists = $listClass->getFrontendLists();
			if(!empty($lists)){
				$frontListsIds = array();
				if(empty($selectedList)){
					foreach($lists as $oneList){
						$frontListsIds[] = $oneList->listid;
					}
					$query .= ' AND c.listid IN ('.implode(',', $frontListsIds).')';
					$queryCount .= ' AND c.listid IN ('.implode(',', $frontListsIds).')';
				}
			}
			$query .= ' GROUP BY a.mailid ';
		}

		if(!empty($pageInfo->filter->order->value) && !in_array($pageInfo->filter->order->value, array('a.date', 'c.email'))){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir;
		}

		$rows = acymailing_loadObjectList($query, 'mailid', $pageInfo->limit->start, $pageInfo->limit->value);

		if(!empty($rows)){
			$queueCount = acymailing_loadObjectList('SELECT COUNT(*) AS countqueued, mailid FROM '.acymailing_table('queue').' WHERE mailid IN ('.implode(',', array_keys($rows)).') GROUP BY mailid');
			if(!empty($queueCount)){
				foreach($queueCount as $oneQueueCount){
					$rows[$oneQueueCount->mailid]->countqueued = $oneQueueCount->countqueued;
				}
			}
		}

		$pageInfo->elements->total = acymailing_loadResult($queryCount);
		$pageInfo->elements->page = count($rows);

		$pagination = new acyPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);

		$isAdmin = false;
		if(acymailing_isAdmin()){
			$isAdmin = true;

			$buttonPreview = acymailing_translation('ACY_PREVIEW');
			$acyToolbar = acymailing_get('helper.toolbar');
			if($this->type == 'autonews'){
				$acyToolbar->custom('generate', acymailing_translation('GENERATE'), 'process', false, '');
			}elseif($this->type == 'news'){
				$buttonPreview .= ' / '.acymailing_translation('SEND');
			}

			$acyToolbar->custom('preview', $buttonPreview, 'search', true);

			if(acymailing_level(3) && acymailing_isAllowed($config->get('acl_'.$this->aclCat.'_abtesting', 'all')) && $this->type == 'news') $acyToolbar->popup('ABtesting', acymailing_translation('ABTESTING'), acymailing_completeLink('newsletter&task=abtesting', true), 800, 600);

			if(acymailing_level(3)){
				$acyToolbar->popup('import', acymailing_translation('IMPORT'), acymailing_completeLink("newsletter&task=upload", true), 450, 200);
			}
			if(acymailing_level(3) || acymailing_isAllowed($config->get('acl_'.$this->aclCat.'_copy', 'all'))) $acyToolbar->divider();

			$acyToolbar->add();
			$acyToolbar->edit();
			if(acymailing_isAllowed($config->get('acl_'.$this->aclCat.'_copy', 'all'))) $acyToolbar->copy();
			if(acymailing_isAllowed($config->get('acl_'.$this->aclCat.'_delete', 'all'))) $acyToolbar->delete();
			$acyToolbar->divider();
			$acyToolbar->help($this->doc);
			$acyToolbar->setTitle(acymailing_translation($this->nameListing), $this->ctrl);
			$acyToolbar->display();
		}

		$filters = new stdClass();
		if(acymailing_isAdmin()){
			$listmailType = acymailing_get('type.listsmail');
			$listmailType->type = $this->type;
			$filters->list = $listmailType->display('filter_list', $selectedList);
		}else{
			$accessibleLists = array();
			$accessibleLists[] = acymailing_selectOption('0', acymailing_translation('ALL_LISTS'));
			foreach($lists as $oneList){
				$accessibleLists[] = acymailing_selectOption($oneList->listid, $oneList->name);
			}
			$filters->list = acymailing_select($accessibleLists, 'filter_list', 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', (int)$selectedList);
		}
		$creatorfilterType = acymailing_get('type.creatorfilter');
		$creatorfilterType->type = $this->type;

		$filters->creator = $creatorfilterType->display('filter_creator', $selectedCreator, 'mail');

		if($this->type == 'news'){
			$senddates = acymailing_loadResultArray('SELECT DATE_FORMAT(FROM_UNIXTIME(senddate),"%Y-%m") AS date FROM #__acymailing_mail WHERE senddate IS NOT NULL AND senddate != 0 AND type = "news" GROUP BY date ORDER BY date DESC');
			$sendFilter = array();
			$sendFilter[] = acymailing_selectOption('0', acymailing_translation('SEND_DATE'));
			if(!empty($senddates)){
				$currentYear = '';
				foreach($senddates as $oneSenddate){
					list($year, $month) = explode('-', $oneSenddate);
					if($year != $currentYear){
						$sendFilter[] = acymailing_selectOption($year, '- '.$year.' -');
						$currentYear = $year;
					}
					$sendFilter[] = acymailing_selectOption($oneSenddate, acymailing_date(strtotime($oneSenddate.'-15'), ACYMAILING_J16 ? 'F' : '%B', false));
				}
			}
			$filters->date = acymailing_select($sendFilter, 'filter_date', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $selectedDate);

			if(empty($actionExists)){
				$filters->type = '';
			}else{
				$typeFilter = array();
				$typeFilter[] = acymailing_selectOption('', acymailing_translation('ACY_TYPE'));
				$typeFilter[] = acymailing_selectOption('news', acymailing_translation('NEWSLETTER'));
				$typeFilter[] = acymailing_selectOption('action', acymailing_translation('ACY_DISTRIBUTION'));
				$filters->type = acymailing_select($typeFilter, 'filter_type', 'class="inputbox" size="1" onchange="document.adminForm.submit();"', 'value', 'text', $selectedType);
			}
		}

		if(acymailing_level(3)){
			$tagfieldtype = acymailing_get('type.tagfield');
			$tagfieldtype->onclick = 'document.adminForm.submit();';
			$filters->tags = $tagfieldtype->display('filter_tags', 'listing', $selectedTags);
		}else{
			$filters->tags = '';
		}

		$mailToLists = array();
		foreach($rows as $row){
			$queryList = "SELECT listid FROM #__acymailing_listmail WHERE mailid=".$row->mailid;
			$listMail = acymailing_loadObjectList($queryList, 'listid');
			$mailToLists[$row->mailid] = array_keys($listMail);
		}
		$listColor = acymailing_loadObjectList("SELECT listid, color, name FROM #__acymailing_list", 'listid');
		$this->mailToLists = $mailToLists;
		$this->listColor = $listColor;


		$this->filters = $filters;
		$toggleClass = acymailing_get('helper.toggle');
		$this->toggleClass = $toggleClass;
		$this->rows = $rows;
		$this->pageInfo = $pageInfo;
		$this->pagination = $pagination;
		$delay = acymailing_get('type.delaydisp');
		$this->delay = $delay;
		$this->config = $config;
		$this->isAdmin = $isAdmin;

		if($this->type == 'autonews'){
			$frequency = acymailing_get('type.frequency');
			$this->frequencyType = $frequency;
		}
	}

	function form(){
		$_SESSION['timeOnModification'] = time();
		$this->chosen = false;
		$mailid = acymailing_getCID('mailid');
		$templateClass = acymailing_get('class.template');
		$config = acymailing_config();

		if(!empty($mailid)){
			$mailClass = acymailing_get('class.mail');
			$mail = $mailClass->get($mailid);

			if(empty($mail->mailid)){
				acymailing_display('Newsletter '.$mailid.' not found', 'error');
				$mailid = 0;
			}
		}

		if(empty($mailid)){
			$mail = new stdClass();
			$mail->created = time();
			$mail->published = 0;
			$mail->thumb = '';
			if($this->type == 'followup') $mail->published = 1;
			$mail->visible = 1;
			$mail->html = 1;
			$mail->body = '';
			$mail->altbody = '';
			$mail->tempid = 0;

			$templateid = acymailing_getVar('int', 'templateid');
			$email = acymailing_currentUserEmail();
			if(empty($templateid) AND !empty($email)){
				$subscriberClass = acymailing_get('class.subscriber');
				$currentSubscriber = $subscriberClass->get($email);
				if(!empty($currentSubscriber->template)) $templateid = $currentSubscriber->template;
			}

			if(empty($templateid)){
				$myTemplate = $templateClass->getDefault();
			}else{
				$myTemplate = $templateClass->get($templateid);
			}

			if(!empty($myTemplate->tempid)){
				$mail->body = acymailing_absoluteURL($myTemplate->body);
				$mail->altbody = $myTemplate->altbody;
				$mail->tempid = $myTemplate->tempid;
				$mail->subject = $myTemplate->subject;
				$mail->replyname = $myTemplate->replyname;
				$mail->replyemail = $myTemplate->replyemail;
				$mail->fromname = $myTemplate->fromname;
				$mail->fromemail = $myTemplate->fromemail;
			}

			if($this->type == 'autonews'){
				$mail->frequency = 2592000;
			}

			if(!acymailing_isAdmin()){
				if($config->get('frontend_sender', 0)){
					$mail->fromname = acymailing_currentUserName();
					$mail->fromemail = acymailing_currentUserEmail();
				}else{
					if(empty($mail->fromname)) $mail->fromname = $config->get('from_name');
					if(empty($mail->fromemail)) $mail->fromemail = $config->get('from_email');
				}

				if($config->get('frontend_reply', 0)){
					$mail->replyname = acymailing_currentUserName();
					$mail->replyemail = acymailing_currentUserEmail();
				}else{
					if(empty($mail->replyname)) $mail->replyname = $config->get('reply_name');
					if(empty($mail->replyemail)) $mail->replyemail = $config->get('reply_email');
				}
			}
		}

		$sentbyname = '';
		if(!empty($mail->sentby)){
			$sentbyname = acymailing_loadResult('SELECT `'.$this->cmsUserVars->name.'` AS name FROM '.acymailing_table($this->cmsUserVars->table, false).' WHERE `'.$this->cmsUserVars->id.'`= '.intval($mail->sentby).' LIMIT 1');
		}
		$this->sentbyname = $sentbyname;

		if(acymailing_getVar('none', 'task', '') == 'replacetags'){
			$mailerHelper = acymailing_get('helper.mailer');
			$templateClass = acymailing_get('class.template');
			$mail->template = $templateClass->get($mail->tempid);

			acymailing_importPlugin('acymailing');
			$mailerHelper->triggerTagsWithRightLanguage($mail, false);

			if(!empty($mail->altbody)) $mail->altbody = $mailerHelper->textVersion($mail->altbody, false);
		}

		$extraInfos = '';
		$lists = array();
		$values = new stdClass();
		if($this->type == 'followup'){
			$campaignid = acymailing_getVar('int', 'campaign', 0);
			$extraInfos .= '&campaign='.$campaignid;

			$values->delay = acymailing_get('type.delay');
			$this->campaignid = $campaignid;
		}else{
			$listmailClass = acymailing_get('class.listmail');
			$lists = $listmailClass->getLists($mailid);
		}

		if(acymailing_isAdmin()){


			$acyToolbar = acymailing_get('helper.toolbar');
			if(acymailing_isAllowed($config->get('acl_templates_view', 'all'))){
				$acyToolbar->popup('template', acymailing_translation('ACY_TEMPLATE'), acymailing_completeLink("template&task=theme", true));
			}

			if(acymailing_isAllowed($config->get('acl_tags_view', 'all'))) $acyToolbar->popup('tag', acymailing_translation('TAGS'), acymailing_completeLink("tag&task=tag&type=".$this->type, true));

			if(in_array($this->type, array('news', 'followup')) && acymailing_isAllowed($config->get('acl_tags_view', 'all'))){
				$acyToolbar->custom('replacetags', acymailing_translation('REPLACE_TAGS'), 'replacetag', false);
			}

			$buttonPreview = acymailing_translation('ACY_PREVIEW');
			if($this->type == 'news'){
				$buttonPreview .= ' / '.acymailing_translation('SEND');
			}
			$acyToolbar->custom('savepreview', $buttonPreview, 'search', false, '');
			$acyToolbar->divider();
			$acyToolbar->addButtonOption('apply', acymailing_translation('ACY_APPLY'), 'apply', false);
			if(acymailing_isAdmin() && acymailing_level(1)){
				$acyToolbar->addButtonOption('saveastmpl', acymailing_translation('ACY_SAVEASTMPL'), 'saveastmpl', false);
			}
			$acyToolbar->save();
			$acyToolbar->cancel();
			$acyToolbar->divider();
			$acyToolbar->help($this->doc, 'stepbystep');
			$acyToolbar->setTitle(acymailing_translation($this->nameForm), $this->ctrl.'&task=edit&mailid='.$mailid.$extraInfos);
			$acyToolbar->display();
		}

		$values->maxupload = (acymailing_bytes(ini_get('upload_max_filesize')) > acymailing_bytes(ini_get('post_max_size'))) ? ini_get('post_max_size') : ini_get('upload_max_filesize');


		$toggleClass = acymailing_get('helper.toggle');
		if(!acymailing_isAdmin()){
			$toggleClass->ctrl = 'frontnewsletter';
			$toggleClass->extra = '&listid='.acymailing_getVar('int', 'listid');

			$copyAllLists = $lists;
			$userid = acymailing_currentUserId();
			foreach($copyAllLists as $listid => $oneList){
				if(!$oneList->published || empty($userid)){
					unset($lists[$listid]);
					continue;
				}
				if($oneList->access_manage == 'all') continue;
				if($userid == (int)$oneList->userid) continue;
				if(!acymailing_isAllowed($oneList->access_manage)){
					unset($lists[$listid]);
					continue;
				}
			}

			if(empty($lists)){
				acymailing_enqueueMessage('You don\'t have the rights to add or edit an e-mail', 'error');
				acymailing_redirect(acymailing_completeLink('frontnewsletter', false, true));
			}
		}


		$editor = acymailing_get('helper.editor');
		$editor->setTemplate($mail->tempid);
		$editor->name = 'editor_body';
		$editor->content = $mail->body;
		$editor->prepareDisplay();

		$js = 'function updateAcyEditor(htmlvalue){
			if(htmlvalue == "0"){
				window.document.getElementById("htmlfieldset").style.display = "none";
			}else{
				window.document.getElementById("htmlfieldset").style.display = "block";
			}
		}';

		$script = '
		var attachmentNb = 1;
		function addFileLoader(){
			if(attachmentNb > 9) return;
			window.document.getElementById("attachmentsdiv"+attachmentNb).style.display = "";
			attachmentNb++;
		}';


		$script .= '
		document.addEventListener("DOMContentLoaded", function(){
			acymailing.submitbutton = function(pressbutton) {
				if (pressbutton == "cancel") {
					acymailing.submitform(pressbutton,document.adminForm);
					return;
				}
				';

		if(!acymailing_isAdmin()){
			$script .= '
				if(document.getElementsByClassName("acy_list_checked").length < 1){
					alert("'.acymailing_translation('SELECT_LISTS', true).'");
					return false;
				}
				';
		}

		$script .= '
			var subjectObj = window.document.getElementById("subject");
			if(subjectObj.tagName.toLowerCase() == "input"){
				subjectValue = subjectObj.value;
			}else{
				subjectValue = subjectObj.innerHTML;
			}
			
			if(subjectValue.length < 2){
				alert("'.acymailing_translation('ENTER_SUBJECT', true).'");
				return false;
			}
			
			subjectValue = subjectValue.replace(/<img[^>]+>/g,"");
			aliasValue = document.getElementById("alias").value;
			if(subjectValue.length < 2 && aliasValue < 2){
				alert("'.acymailing_translation('ACY_ENTER_SUBJECT_OR_ALIAS', true).'");
				return false;
			}
			'.$editor->jsCode().'
			
			if(pressbutton == "save" || pressbutton == "apply" || pressbutton == "savepreview" || pressbutton == "replacetags" || pressbutton == "saveastmpl"){
				var emailVars = ["fromemail", "replyemail"];
				var val = "";
				for(var key in emailVars){
					if(isNaN(key)) continue;
					val = document.getElementById(emailVars[key]).value;
					if(!validateEmail(val, emailVars[key])){
						return;
					}
				}
				';

		if(!empty($mail->mailid)){
			$urlCheckVersion = acymailing_prepareAjaxURL((acymailing_isAdmin() ? '' : 'front').'newsletter').'&task=checkifedited&mailId='.$mail->mailid;
			$script .= '
				var popup = false;
				var xhr = new XMLHttpRequest();
				xhr.open("GET", "'.$urlCheckVersion.'");
				xhr.onreadystatechange = function(){
					if (xhr.readyState === 4) {
						var response = xhr.responseText.toString();
						var responseSplit = response.split("|");
						
						if(xhr.status !== 200 || response.indexOf("|") == -1 || responseSplit[0] == '.acymailing_currentUserId().'){
							acymailing.submitform(pressbutton,document.adminForm);
							return false;
						}
						
						document.getElementById("confirmTxtMM").innerHTML = responseSplit[1] + " '.acymailing_translation('ACY_SAVE_ANYWAY_NAME', true).'";
						document.getElementById("confirmBoxMM").style.display="inline";
						document.getElementById("modal-background").style.display="inline";
					}
				}
				xhr.send();
				
				return false;
			}
		};
				';
		}else{
			$script .= '}
			acymailing.submitform(pressbutton,document.adminForm);
		};';
		}

		$script .= '});';


		$script .= $editor->jsMethods();

		$script .= "
		function changeTemplate(newhtml,newtext,newsubject,stylesheet,fromname,fromemail,replyname,replyemail,tempid){
			if(newhtml.length>2){".$editor->setContent('newhtml')."}
			var vartextarea = document.getElementById('altbody');
		    if(newtext.length>2) vartextarea.innerHTML = newtext;
			document.getElementById('tempid').value = tempid;
			if(fromname.length>1){
				fromname = fromname.replace('&amp;', '&');
				document.getElementById('fromname').value = fromname;
			}
			if(fromemail.length>1){document.getElementById('fromemail').value = fromemail;}
			if(replyname.length>1){
				replyname = replyname.replace('&amp;', '&');
				document.getElementById('replyname').value = replyname;
			}
			if(replyemail.length>1){document.getElementById('replyemail').value = replyemail;}
			if(newsubject.length>1){
				newsubject = newsubject.replace('&amp;', '&');
				var subjectObj = document.getElementById('subject');
				if(subjectObj.tagName.toLowerCase() == 'input'){
					subjectObj.value = newsubject;
				}else{
				    subjectObj.innerHTML = newsubject;
				}
			}
			".$editor->setEditorStylesheet('tempid')."
		}
		";

		if($mail->html == 1){
			$script .= "var zoneEditor = 'editor_body';";
		}else{
			$script .= "var zoneEditor = 'altbody';";
		}
		$script .= "
			document.addEventListener('DOMContentLoaded', function(){
				setTimeout(function() {
					document.getElementById('htmlfieldset').addEventListener('click', function(){
						zoneToTag = 'editor';
					});	
					
					var ediframe = document.getElementById('htmlfieldset').getElementsByTagName('iframe');
					if(ediframe && ediframe[0]){
						var children = ediframe[0].contentDocument.getElementsByTagName('*');
						for (var i = 0; i < children.length; i++) {
							children[i].addEventListener('click', function(){
								zoneToTag = 'editor';
							});			
						}
					}		
				}, 1000);
			});
		
			var zoneToTag = 'editor';
			function initTagZone(html){ if(html == 0){ zoneEditor = 'altbody'; }else{ zoneEditor = 'editor_body'; }}
		";

		$script .= "var previousSelection = false;
			function insertTag(tag){
				if(zoneEditor == 'editor_body' && zoneToTag == 'editor'){
					try{
						jInsertEditorText(tag,'editor_body',previousSelection);
						return true;
					} catch(err){
						alert('Your editor does not enable AcyMailing to automatically insert the tag, please copy/paste it manually in your Newsletter');
						return false;
					}
				} else{
					try{
						simpleInsert(zoneToTag, tag);
						return true;
					} catch(err){
						alert('Error inserting the tag in the '+ zoneToTag + 'zone. Please copy/paste it manually in your Newsletter.');
						return false;
					}
				}
			}
				
			function simpleInsert(myField, myValue) {
				myField = document.getElementById(myField);

				if (document.selection) {
					myField.focus();
					sel = document.selection.createRange();
					sel.text = myValue;
				} else if (myField.selectionStart || myField.selectionStart == '0') {
					var startPos = myField.selectionStart;
					var endPos = myField.selectionEnd;
					myField.value = myField.value.substring(0, startPos)
						+ myValue
						+ myField.value.substring(endPos, myField.value.length);
				} else if (myField.tagName == 'DIV') {
					myField.innerHTML += myValue;
					document.getElementById('subject').value += myValue;
				} else {
					myField.value += myValue;
				}
			}";

		$script .= "function deleteAttachment(i){
			document.getElementById('attachments'+i+'selection').innerHTML = '';
			document.getElementById('attachments'+i+'suppr').style.display = 'none';
			document.getElementById('attachments'+i).value = '';
			return;
		}";

		acymailing_addScript(true, $js.$script);

		$css = '#confirmBoxMM {
			width: 370px;
			background: rgba(255, 255, 255, 0.8);
			border: 1px solid #d6d6d6;
			padding: 5px;
			border-radius: 5px;
			box-shadow: 1px 1px 5px #dddddd;
			-moz-box-shadow: 1px 1px 5px #dddddd;
			-webkit-box-shadow: 1px 1px 5px #dddddd;
			position: fixed;
			left: 43%;
			top: 40%;
			z-index: 999;
		}
		
		#modal-background{
			position: fixed;
			top: 0px;
			right: 0px;
			left: 0px;
			bottom: 0px;
			z-index: 998;
			background-color: #000;
			opacity: 0.8;
		}
		
		#confirmOkMM:hover{
			-moz-transition: 0.3s;
		  	-o-transition: 0.3s;
		  	-webkit-transition: 0.3S
			transition: 0.3s;
			opacity: 0.7;
		}';

		if(!empty($mail->mailid)) acymailing_addStyle(true, $css);
		$installedPlugin = acymailing_getPlugin('acymailing', 'emojis');
		if(!empty($installedPlugin)){
			$params = new acyParameter($installedPlugin->params);
			if(acymailing_isPluginEnabled('acymailing', 'emojis') && $params->get('subject', 1) == 1){
				if(!ACYMAILING_J30){
					acymailing_addScript(false, ACYMAILING_JS.'jquery/jquery-1.9.1.min.js?v='.filemtime(ACYMAILING_ROOT.'media'.DS.'com_acymailing'.DS.'js'.DS.'jquery'.DS.'jquery-1.9.1.min.js'));
					acymailing_addScript(false, ACYMAILING_JS.'jquery/jquery-ui.min.js?v='.filemtime(ACYMAILING_ROOT.'media'.DS.'com_acymailing'.DS.'js'.DS.'jquery'.DS.'jquery-ui.min.js'));
				}

				acymailing_addScript(false, acymailing_rootURI().'plugins/editors/acyeditor/acyeditor/ckeditor/plugins/smiley/emojionearea.js?v='.filemtime(ACYMAILING_ROOT.'plugins'.DS.'editors'.DS.'acyeditor'.DS.'acyeditor'.DS.'ckeditor'.DS.'plugins'.DS.'smiley'.DS.'emojionearea.js'));
				acymailing_addScript(false, acymailing_rootURI().'plugins/editors/acyeditor/acyeditor/ckeditor/plugins/smiley/dialogs/emojimap.js?v='.filemtime(ACYMAILING_ROOT.'plugins'.DS.'editors'.DS.'acyeditor'.DS.'acyeditor'.DS.'ckeditor'.DS.'plugins'.DS.'smiley'.DS.'dialogs'.DS.'emojimap.js'));
				acymailing_addStyle(false, acymailing_rootURI().'plugins/editors/acyeditor/acyeditor/ckeditor/plugins/smiley/emojionearea.css?v='.filemtime(ACYMAILING_ROOT.'plugins'.DS.'editors'.DS.'acyeditor'.DS.'acyeditor'.DS.'ckeditor'.DS.'plugins'.DS.'smiley'.DS.'emojionearea.css'));
				acymailing_addScript(true, '
					document.addEventListener("DOMContentLoaded", function(){
						jQuery("#subject").emojioneArea({
							pickerPosition: "bottom",
							shortnames: true
						});
					});
				');
			}
		}

		if($this->type == 'autonews'){
			$this->frequencyType = acymailing_get('type.frequency');
			$this->generatingMode = acymailing_get('type.generatemode');
		}

		$this->toggleClass = $toggleClass;
		$this->lists = $lists;
		$this->editor = $editor;
		$this->mail = $mail;
		$tabs = acymailing_get('helper.acytabs');

		$this->tabs = $tabs;
		$this->values = $values;
		$this->config = $config;
	}

	function preview(){
		$mailid = acymailing_getCID('mailid');
		$config = acymailing_config();

		$mailerHelper = acymailing_get('helper.mailer');
		$mailerHelper->loadedToSend = false;
		$mail = $mailerHelper->load($mailid);

		$userClass = acymailing_get('class.subscriber');
		$receiver = $userClass->get(acymailing_currentUserEmail());
		$mail->sendHTML = true;
		acymailing_trigger('acymailing_replaceusertags', array(&$mail, &$receiver, false));
		if(!empty($mail->altbody)) $mail->altbody = $mailerHelper->textVersion($mail->altbody, false);

		$listmailClass = acymailing_get('class.listmail');
		$lists = $listmailClass->getReceivers($mail->mailid, true, false);

		$testreceiverType = acymailing_get('type.testreceiver');

		$paramBase = ACYMAILING_COMPONENT.'.'.$this->getName();
		$infos = new stdClass();
		$infos->test_selection = acymailing_getUserVar($paramBase.".test_selection", 'test_selection', '', 'string');
		$infos->test_group = acymailing_getUserVar($paramBase.".test_group", 'test_group', '', 'string');
		$infos->test_emails = acymailing_getUserVar($paramBase.".test_emails", 'test_emails', '', 'string');
		$infos->test_html = acymailing_getUserVar($paramBase.".test_html", 'test_html', 1, 'int');

		if(acymailing_isAdmin()){


			$acyToolbar = acymailing_get('helper.toolbar');
			if(acymailing_isAllowed($config->get('acl_'.$this->aclCat.'_spam_test', 'all'))){
				$acyToolbar->popup('spamtest', acymailing_translation('SPAM_TEST'), acymailing_completeLink("send&task=spamtest&mailid=".$mailid, true));
			}
			if($this->type == 'news'){
				if(acymailing_level(1) && acymailing_isAllowed($config->get('acl_newsletters_schedule', 'all'))){
					if($mail->published == 2){
						$acyToolbar->custom('unschedule', acymailing_translation('UNSCHEDULE'), 'schedule', false);
					}else{
						$acyToolbar->popup('schedule', acymailing_translation('SCHEDULE'), acymailing_completeLink("send&task=scheduleready&mailid=".$mailid, true));
					}
				}
				if(acymailing_isAllowed($config->get('acl_newsletters_send', 'all'))){
					$acyToolbar->popup('send', acymailing_translation('SEND'), acymailing_completeLink("send&task=sendready&mailid=".$mailid, true));
				}
			}


			$acyToolbar->divider();
			$acyToolbar->custom('edit', acymailing_translation('ACY_EDIT'), 'edit', false);
			$acyToolbar->cancel();
			$acyToolbar->divider();
			$acyToolbar->help($this->doc);
			$acyToolbar->setTitle(acymailing_translation('ACY_PREVIEW').' : '.$mail->subject, $this->ctrl.'&task=preview&mailid='.$mailid);
			$acyToolbar->display();
		}

		preg_match('@href="{unsubscribe:(.*)}"@', $mail->body, $match);//we get the tag unsubscribe
		if(!empty($match)){
			$mail->body = str_replace($match[0], 'href="'.$match[1].'"', $mail->body);
		}

		$this->lists = $lists;
		$this->infos = $infos;
		$this->testreceiverType = $testreceiverType;
		$this->mail = $mail;

		if($mail->html){
			$templateClass = acymailing_get('class.template');
			if(!empty($mail->tempid)) $templateClass->createTemplateFile($mail->tempid);
			$templateClass->displayPreview('newsletter_preview_area', $mail->tempid, $mail->subject);
		}
	}

	function upload(){
		$acyToolbar = acymailing_get('helper.toolbar');
		$acyToolbar->custom('douploadnewsletter', acymailing_translation('IMPORT'), 'import', false);
		$acyToolbar->setTitle(acymailing_translation('IMPORT'));
		$acyToolbar->topfixed = false;
		$acyToolbar->display();
	}

	function abtesting(){
		$mailids = acymailing_getVar('string', 'mailid');
		$validationStatus = acymailing_getVar('string', 'validationStatus');
		$noMsg = false;
		$noBtn = false;
		if((!empty($mailids) && strpos($mailids, ',') !== false)){
			$warningMsg = array();

			$mailsArray = explode(',', $mailids);
			acymailing_arrayToInteger($mailsArray);

			$mailids = implode(',', $mailsArray);
			$this->mailid = $mailids;
			$query = 'SELECT abtesting FROM #__acymailing_mail WHERE mailid IN ('.implode(',', $mailsArray).') AND abtesting IS NOT NULL';
			$resDetail = acymailing_loadResultArray($query);
			if(!empty($resDetail) && count($resDetail) != count($mailsArray)){
				$titlePage = acymailing_translation('ABTESTING');
				acymailing_display(acymailing_translation('ABTESTING_MISSINGEMAIL'), 'warning');
				$this->missingMail = true;
			}else{
				$abTestDetail = array();
				if(empty($resDetail)){
					$abTestDetail['mailids'] = $mailids;
					$abTestDetail['prct'] = 10;
					$abTestDetail['delay'] = 2;
					$abTestDetail['action'] = 'manual';
				}else{
					$abTestDetail = unserialize($resDetail[0]);
					$savedIds = explode(',', $abTestDetail['mailids']);
					sort($savedIds);
					sort($mailsArray);
					if(!empty($abTestDetail['status']) && in_array($abTestDetail['status'], array('inProgress', 'testSendOver', 'abTestFinalSend')) && $savedIds != $mailsArray){
						$warningMsg[] = acymailing_translation('ABTESTING_TESTEXIST');
						$mailsArray = $savedIds;
						$mailids = implode(',', $mailsArray);
					}
					$this->savedValues = true;
					if($abTestDetail['status'] == 'inProgress') $warningMsg[] = acymailing_translation('ABTESTING_INPROGRESS');
				}

				if($validationStatus == 'abTestAdd') $noMsg = true;

				if(!empty($abTestDetail['status']) && $abTestDetail['status'] == 'abTestFinalSend' && !empty($abTestDetail['newMail'])){
					$mailInQueueErrorMsg = acymailing_translation('ABTESTING_FINALMAILINQUEUE');
					$mailTocheck = '='.$abTestDetail['newMail'];
				}else{
					$mailInQueueErrorMsg = acymailing_translation('ABTESTING_TESTMAILINQUEUE');
					$mailTocheck = ' IN ('.implode(',', $mailsArray).')';
				}
				$query = "SELECT COUNT(*) FROM #__acymailing_queue WHERE mailid".$mailTocheck;
				$queueCheck = acymailing_loadResult($query);
				if(!empty($queueCheck) && $validationStatus != 'abTestAdd'){
					acymailing_enqueueMessage($mailInQueueErrorMsg, 'error');
					$noMsg = true;
				}

				if(!empty($resDetail) && empty($queueCheck) && in_array($abTestDetail['status'], array('inProgress', 'abTestFinalSend'))){
					if($abTestDetail['status'] == 'inProgress'){
						$abTestDetail['status'] = 'testSendOver';
					}else $abTestDetail['status'] = 'completed';
					$query = "UPDATE #__acymailing_mail SET abtesting=".acymailing_escapeDB(serialize($abTestDetail))." WHERE mailid IN (".implode(',', $mailsArray).")";
					acymailing_query($query);
				}

				if(!empty($abTestDetail['status']) && $abTestDetail['status'] == 'testSendOver') acymailing_enqueueMessage(acymailing_translation('ABTESTING_READYTOSEND'), 'info');
				if(!empty($abTestDetail['status']) && $abTestDetail['status'] == 'completed') acymailing_enqueueMessage(acymailing_translation('ABTESTING_COMPLETE'), 'info');

				$this->abTestDetail = $abTestDetail;

				$nbMails = count($mailsArray);
				$titleStr = "A/B/C/D/E/F/G/H/I/J/K/L/M/N/O/P/Q/R/S/T/U/V/W/X/Y/Z";
				$titlePage = acymailing_translation_sprintf('ABTESTING_TITLE', substr($titleStr, 0, min($nbMails, 26) * 2 - 1));
				$mailClass = acymailing_get('class.mail');
				$mailsDetails = array();
				foreach($mailsArray as $mailid){
					$mailsDetails[] = $mailClass->get($mailid);
				}
				$this->mailsdetails = $mailsDetails;

				$mailerHelper = acymailing_get('helper.mailer');
				$mailerHelper->loadedToSend = false;
				$mailReceiver = $mailerHelper->load($mailsArray[0]);
				$listmailClass = acymailing_get('class.listmail');
				$lists = $listmailClass->getReceivers($mailReceiver->mailid, true, false);
				$this->lists = $lists;
				$this->mailReceiver = $mailReceiver;
				$filterClass = acymailing_get('class.filter');
				$this->filterClass = $filterClass;
				$listids = array();
				foreach($lists as $oneList){
					$listids[] = $oneList->listid;
				}
				$nbTotalReceivers = $filterClass->countReceivers($listids, $this->mailReceiver->filter, $this->mailReceiver->mailid);
				if($nbTotalReceivers < 50){
					$warningMsg[] = acymailing_translation_sprintf('ABTESTING_NOTENOUGHUSER', $nbTotalReceivers);
					$noBtn = true;
				}
				$this->nbTotalReceivers = $nbTotalReceivers;
				$this->nbTestReceivers = floor($nbTotalReceivers * $abTestDetail['prct'] / 100);

				if($noMsg || $noBtn) $noButton = true;

				$queryStat = 'SELECT mailid, openunique, clickunique, senthtml, senttext, bounceunique FROM #__acymailing_stats WHERE mailid IN ('.$mailids.')';
				$resStat = acymailing_loadObjectList($queryStat, 'mailid');
				if(!empty($resStat)){
					$this->statMail = $resStat;
					$warningMsg[] = acymailing_translation('ABTESTING_STAT_WARNING');
				}
				if(!empty($warningMsg) && $noMsg == false) acymailing_enqueueMessage(implode('<br />', $warningMsg), 'warning');
			}
		}else{
			$titlePage = acymailing_translation('ABTESTING');
		}

		$this->validationStatus = $validationStatus;
		$this->titlePage = $titlePage;

		$acyToolbar = acymailing_get('helper.toolbar');
		if(empty($noButton) && (!empty($this->mailid) || !empty($this->validationStatus))){
			$acyToolbar->custom('test', acymailing_translation('ABTESTING_TEST'), 'test', false, "if(confirm('".acymailing_translation('PROCESS_CONFIRMATION', true)."')){acymailing.submitbutton('abtest');} return false;");
		}
		$acyToolbar->help('a-b-testing');
		$acyToolbar->setTitle(acymailing_translation('ABTESTING'));
		$acyToolbar->topfixed = false;
		$acyToolbar->display();
	}
}
