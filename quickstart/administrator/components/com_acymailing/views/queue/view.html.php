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


class QueueViewQueue extends acymailingView{
	var $searchFields = array('b.name', 'b.email', 'c.subject', 'a.mailid', 'a.subid');
	var $selectFields = array('b.name', 'b.email', 'c.subject', 'c.type', 'c.published', 'a.mailid', 'a.subid', 'a.senddate', 'a.priority', 'a.try');

	function display($tpl = null){
		$function = $this->getLayout();
		if(method_exists($this, $function)) $this->$function();

		parent::display($tpl);
	}

	function preview(){
		$mailid = acymailing_getVar('int', 'mailid');
		$subid = acymailing_getVar('int', 'subid');

		$mailerHelper = acymailing_get('helper.mailer');
		$mailerHelper->loadedToSend = false;
		$mail = $mailerHelper->load($mailid);

		$userClass = acymailing_get('class.subscriber');
		$receiver = $userClass->get($subid);
		if(empty($receiver)) die(acymailing_translation_sprintf('SEND_ERROR_USER', $subid));
		if(empty($mail)) die('Newsletter not found: '.$mailid);
		$mail->sendHTML = $mail->html && $receiver->html;

		$receiver->paramqueue = acymailing_loadResult('SELECT paramqueue FROM #__acymailing_queue WHERE mailid = '.intval($mailid).' AND subid = '.intval($subid));

		acymailing_trigger('acymailing_replaceusertags', array(&$mail, &$receiver, false));
		if(!empty($mail->altbody)) $mail->altbody = $mailerHelper->textVersion($mail->altbody, false);

		if($mail->html){
			$templateClass = acymailing_get('class.template');
			$templateClass->displayPreview('newsletter_preview_area', $mail->tempid, $mail->subject);
		}

		$this->mail = $mail;

		$acyToolbar = acymailing_get('helper.toolbar');
		$acyToolbar->setTitle($this->mail->subject);
		$acyToolbar->directPrint();
		$acyToolbar->topfixed = false;
		$acyToolbar->display();
	}

	function listing(){
		$pageInfo = new stdClass();
		$pageInfo->filter = new stdClass();
		$pageInfo->filter->order = new stdClass();
		$pageInfo->limit = new stdClass();
		$pageInfo->elements = new stdClass();

		$config = acymailing_config();

		$paramBase = ACYMAILING_COMPONENT.'.'.$this->getName();
		$pageInfo->filter->order->value = acymailing_getUserVar($paramBase.".filter_order", 'filter_order', 'a.senddate', 'cmd');
		$pageInfo->filter->order->dir = acymailing_getUserVar($paramBase.".filter_order_Dir", 'filter_order_Dir', 'asc', 'word');
		if(strtolower($pageInfo->filter->order->dir) !== 'desc') $pageInfo->filter->order->dir = 'asc';
		$pageInfo->search = acymailing_getUserVar($paramBase.".search", 'search', '', 'string');
		$pageInfo->search = strtolower(trim($pageInfo->search));

		$pageInfo->selectedMail = acymailing_getUserVar($paramBase."filter_mail", 'filter_mail', 0, 'int');

		$pageInfo->limit->value = acymailing_getUserVar($paramBase.'.list_limit', 'limit', acymailing_getCMSConfig('list_limit'), 'int');
		$pageInfo->limit->start = acymailing_getUserVar($paramBase.'.limitstart', 'limitstart', 0, 'int');

		$filters = array();
		if(!empty($pageInfo->search)){
			$searchVal = '\'%'.acymailing_getEscaped($pageInfo->search, true).'%\'';
			$filters[] = implode(" LIKE $searchVal OR ", $this->searchFields)." LIKE $searchVal";
		}

		if(!empty($pageInfo->selectedMail)) $filters[] = 'a.mailid = '.intval($pageInfo->selectedMail);

		$query = 'SELECT '.implode(' , ', $this->selectFields);
		$query .= ' FROM '.acymailing_table('queue').' as a';
		$query .= ' JOIN '.acymailing_table('subscriber').' as b on a.subid = b.subid';
		$query .= ' JOIN '.acymailing_table('mail').' as c on a.mailid = c.mailid';
		if(!empty($filters)) $query .= ' WHERE ('.implode(') AND (', $filters).')';
		if(!empty($pageInfo->filter->order->value)){
			$query .= ' ORDER BY '.$pageInfo->filter->order->value.' '.$pageInfo->filter->order->dir.', a.`subid` ASC';
		}

		if(empty($pageInfo->limit->value)) $pageInfo->limit->value = 100;
		$rows = acymailing_loadObjectList($query, '', $pageInfo->limit->start, $pageInfo->limit->value);
		if(empty($rows) && $pageInfo->limit->start != 0){
			$pageInfo->limit->start = 0;
			$rows = acymailing_loadObjectList($query, '', $pageInfo->limit->start, $pageInfo->limit->value);
		}

		$pageInfo->elements->page = count($rows);

		if($pageInfo->limit->value > $pageInfo->elements->page){
			$pageInfo->elements->total = $pageInfo->limit->start + $pageInfo->elements->page;
		}else{
			$queryCount = 'SELECT COUNT(a.mailid) FROM '.acymailing_table('queue').' as a';
			if(!empty($pageInfo->search)){
				$queryCount .= ' JOIN '.acymailing_table('subscriber').' as b on a.subid = b.subid';
				$queryCount .= ' JOIN '.acymailing_table('mail').' as c on a.mailid = c.mailid';
			}
			if(!empty($filters)) $queryCount .= ' WHERE ('.implode(') AND (', $filters).')';

			$pageInfo->elements->total = acymailing_loadResult($queryCount);
		}

		$pagination = new acyPagination($pageInfo->elements->total, $pageInfo->limit->start, $pageInfo->limit->value);

		$mailqueuetype = acymailing_get('type.queuemail');
		$filtersType = new stdClass();
		$filtersType->mail = $mailqueuetype->display('filter_mail', $pageInfo->selectedMail);


		$acyToolbar = acymailing_get('helper.toolbar');
		if(acymailing_isAllowed($config->get('acl_queue_process', 'all'))){
			$acyToolbar->popup('process', acymailing_translation('PROCESS'), acymailing_completeLink("queue&task=process&mailid=".$pageInfo->selectedMail, true));
		}
		if(!empty($pageInfo->elements->total) AND acymailing_isAllowed($config->get('acl_queue_delete', 'all'))){
			$onClick = "if (confirm('".str_replace("'", "\'", acymailing_translation_sprintf('CONFIRM_DELETE_QUEUE', $pageInfo->elements->total))."')){acymailing.submitbutton('remove');}";
			$acyToolbar->custom('remove', acymailing_translation('ACY_DELETE'), 'delete', false, $onClick);
		}

		$acyToolbar->divider();
		$acyToolbar->help('queue-listing');
		$acyToolbar->setTitle(acymailing_translation('QUEUE'), 'queue');
		$acyToolbar->display();

		$toggleClass = acymailing_get('helper.toggle');

		$this->toggleClass = $toggleClass;
		$this->filters = $filtersType;
		$this->rows = $rows;
		$this->pageInfo = $pageInfo;
		$this->pagination = $pagination;
	}

	function process(){

		$mailid = acymailing_getCID('mailid');
		$queueClass = acymailing_get('class.queue');
		$queueStatus = $queueClass->queueStatus($mailid);
		$nextqueue = $queueClass->queueStatus($mailid, true);
		if(acymailing_level(1)){
			$scheduleClass = acymailing_get('helper.schedule');
			$scheduleNewsletter = $scheduleClass->getScheduled();
			$this->schedNews = $scheduleNewsletter;
		}

		if(empty($queueStatus) AND empty($scheduleNewsletter)) acymailing_display(acymailing_translation('NO_PROCESS'), 'info');

		$infos = new stdClass();
		$infos->mailid = $mailid;
		$this->queue = $queueStatus;
		$this->nextqueue = $nextqueue;
		$this->infos = $infos;
	}
}
