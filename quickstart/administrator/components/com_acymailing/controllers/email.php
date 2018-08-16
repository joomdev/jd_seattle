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

class EmailController extends acymailingController{
	
	function test(){

		$this->store();

		$mailHelper = acymailing_get('helper.mailer');

		$receiver = acymailing_currentUserEmail();
		$mailid = acymailing_getCID('mailid');

		$mailHelper->report = false;
		$result = $mailHelper->sendOne($mailid, $receiver);
		acymailing_enqueueMessage($mailHelper->reportMessage, $result ? 'success' : 'error');

		return $this->edit();
	}

	function store(){
		acymailing_checkToken();

		$oldMailid = acymailing_getCID('mailid');
		$mailClass = acymailing_get('class.mail');

		if($mailClass->saveForm()){
			$data = acymailing_getVar('none', 'data');
			$type = @$data['mail']['type'];
			if(!empty($type) AND in_array($type, array('unsub', 'welcome'))){
				$subject = addslashes($data['mail']['subject']);
				$mailid = acymailing_getVar('int', 'mailid');
				if($type == 'unsub'){
					$js = "var mydrop = window.top.document.getElementById('datalistunsubmailid'); ";
					$js .= "var type = 'unsub';";
				}else{ //type=welcome
					$js = "var mydrop = window.top.document.getElementById('datalistwelmailid'); ";
					$js .= "var type = 'welcome';";
				}
				if(empty($oldMailid)){
					$js .= 'var optn = document.createElement("OPTION");';
					$js .= "optn.text = '[$mailid] $subject'; optn.value = '$mailid';";
					$js .= 'mydrop.options.add(optn);';
					$js .= 'lastid = 0; while(mydrop.options[lastid+1]){lastid = lastid+1;} mydrop.selectedIndex = lastid;';
					$js .= 'window.top.changeMessage(type,'.$mailid.');';
				}else{
					$js .= "lastid = 0; notfound = true; while(notfound && mydrop.options[lastid]){if(mydrop.options[lastid].value == $mailid){mydrop.options[lastid].text = '[$mailid] $subject';notfound = false;} lastid = lastid+1;}";
				}
				if(ACYMAILING_J30) $js .= 'window.top.jQuery("#datalist'.($type == 'unsub' ? 'unsub' : 'wel').'mailid").trigger("liszt:updated");';
				acymailing_addScript(true, $js);
			}
			acymailing_enqueueMessage(acymailing_translation('JOOMEXT_SUCC_SAVED'), 'success');
		}else{
			acymailing_enqueueMessage(acymailing_translation('ERROR_SAVING'), 'error');
		}
	}//endfct store

	function chooseListBeforeSend(){
		return $this->listing();
	}

	function sendArticle(){
		$mailClass = acymailing_get('class.mail');
		$listmailClass = acymailing_get('class.listmail');
		$mailerHelper = acymailing_get('helper.mailer');

		$query = 'SELECT * FROM #__acymailing_mail WHERE type = \'article\'';
		$mail = acymailing_loadObject($query);

		$listsids = acymailing_getVar('array', 'cid', array(), '');
		acymailing_arrayToInteger($listsids);

		$newMailId = $mailClass->copyOneNewsletter($mail->mailid);
		$newMail = $mailClass->get($newMailId);
		$newMail->alias = '';
		$newMail->senddate = time();
		$newMail->published = 2;
		$newMail->type = 'news';
		$mailerHelper->triggerTagsWithRightLanguage($newMail, false); //We replace the tags in the mail
		$mailid = $mailClass->save($newMail);

		$listmailClass->save($mailid, $listsids);

		$schedHelper = acymailing_get('helper.schedule');
		$schedHelper->queueScheduled();
		if(!empty($schedHelper->messages)) acymailing_enqueueMessage($schedHelper->messages);
	}
}//endclass
