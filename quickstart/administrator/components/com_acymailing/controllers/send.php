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

class SendController extends acymailingController{

	function sendready(){
		if(!$this->isAllowed('newsletters', 'send')) return;
		acymailing_setVar('layout', 'sendconfirm');
		return parent::display();
	}

	function send(){
		if(!$this->isAllowed('newsletters', 'send')) return;
		acymailing_checkToken();

		acymailing_setNoTemplate();
		$mailid = acymailing_getCID('mailid');
		if(empty($mailid)) exit;

		$time = time();
		$queueClass = acymailing_get('class.queue');
		$queueClass->onlynew = acymailing_getVar('int', 'onlynew');
		$queueClass->mindelay = acymailing_getVar('int', 'mindelay');
		$totalSub = $queueClass->queue($mailid, $time);

		if(empty($totalSub)){
			acymailing_display(acymailing_translation('NO_RECEIVER'), 'warning');
			return;
		}

		$mailObject = new stdClass();
		$mailObject->senddate = $time;
		$mailObject->published = 1;
		$mailObject->mailid = $mailid;
		$mailObject->sentby = acymailing_currentUserId();
		acymailing_updateObject(acymailing_table('mail'), $mailObject, 'mailid');

		$config = acymailing_config();
		$queueType = $config->get('queue_type');
		if($queueType == 'onlyauto'){
			$messages = array();
			$messages[] = acymailing_translation_sprintf('ADDED_QUEUE', $totalSub);
			$messages[] = acymailing_translation('AUTOSEND_CONFIRMATION');
			acymailing_display($messages, 'success');
			return;
		}else{
			acymailing_setVar('totalsend', $totalSub);
			acymailing_redirect(acymailing_completeLink('send&task=continuesend&mailid='.$mailid.'&totalsend='.$totalSub, true, true));
			exit;
		}
	}

	function continuesend(){
		$config = acymailing_config();

		if(acymailing_level(1) && $config->get('queue_type') == 'onlyauto'){
			acymailing_setNoTemplate();
			acymailing_display(acymailing_translation('ACY_ONLYAUTOPROCESS'), 'warning');
			return;
		}


		$newcrontime = time() + 120;
		if($config->get('cron_next') < $newcrontime){
			$newValue = new stdClass();
			$newValue->cron_next = $newcrontime;
			$config->save($newValue);
		}

		$mailid = acymailing_getCID('mailid');

		$totalSend = acymailing_getVar('int', 'totalsend', 0, '');
		$alreadySent = acymailing_getVar('int', 'alreadysent', 0, '');

		$helperQueue = acymailing_get('helper.queue');
		$helperQueue->mailid = $mailid;
		$helperQueue->report = true;
		$helperQueue->total = $totalSend;
		$helperQueue->start = $alreadySent;
		$helperQueue->pause = $config->get('queue_pause');
		$helperQueue->process();

		acymailing_setNoTemplate();



	}


	function spamtest(){
		$mailid = acymailing_getVar('int', 'mailid');
		if(empty($mailid)) return;

		$config = acymailing_config();
		ob_start();
		$urlSite = trim(base64_encode(preg_replace('#https?://(www\.)?#i', '', ACYMAILING_LIVE)), '=/');
		$url = ACYMAILING_SPAMURL.'spamTestSystem&component=acymailing&level='.strtolower($config->get('level', 'starter')).'&urlsite='.$urlSite;
		$spamtestSystem = acymailing_fileGetContent($url, 30);

		$warnings = ob_get_clean();

		if(empty($spamtestSystem) || $spamtestSystem === false || !empty($warnings)){
			acymailing_display('Could not load your information from our server'.((!empty($warnings) && acymailing_isDebug()) ? $warnings : ''), 'error');
			return;
		}
		$decodedInformation = json_decode($spamtestSystem, true);
		if(!empty($decodedInformation['messages']) || !empty($decodedInformation['error'])){
			$msgError = (!empty($decodedInformation['messages'])) ? $decodedInformation['messages'].'<br />' : '';
			$msgError .= (!empty($decodedInformation['error'])) ? $decodedInformation['error'] : '';
			acymailing_display($msgError, 'error');
			return;
		}
		if(empty($decodedInformation['email'])){
			acymailing_display('Missing test mail address', 'error');
			return;
		}

		$receiver = new stdClass();
		$receiver->subid = 0;
		$receiver->email = $decodedInformation['email'];
		$receiver->name = $decodedInformation['name'];
		$receiver->html = 1;
		$receiver->confirmed = 1;
		$receiver->enabled = 1;

		$mailerHelper = acymailing_get('helper.mailer');
		$mailerHelper->checkConfirmField = false;
		$mailerHelper->checkEnabled = false;
		$mailerHelper->checkPublished = false;
		$mailerHelper->checkAccept = false;
		$mailerHelper->loadedToSend = true;
		$mailerHelper->report = false;

		if(!$mailerHelper->sendOne($mailid, $receiver)){
			acymailing_display($mailerHelper->reportMessage, 'error');
			return;
		}
		
		acymailing_redirect($decodedInformation['displayURL']);
		return;
	}
}
