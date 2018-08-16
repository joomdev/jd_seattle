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

class NewsletterController extends acymailingController{

	var $aclCat = 'newsletters';

	function replacetags(){
		if(!$this->isAllowed($this->aclCat, 'manage')) return;
		$this->store();
		return $this->edit();
	}

	function copy(){
		if(!$this->isAllowed($this->aclCat, 'manage')) return;
		acymailing_checkToken();

		$cids = acymailing_getVar('array', 'cid', array(), '');
		$time = time();

		$creatorId = intval(acymailing_currentUserId());

		$addSendDate = '';
		if(!empty($this->copySendDate)) $addSendDate = ', `senddate`';

		foreach($cids as $oneMailid){
			$query = 'INSERT INTO `#__acymailing_mail` (`subject`, `body`, `altbody`, `published`'.$addSendDate.', `created`, `fromname`, `fromemail`, `replyname`, `replyemail`, `bccaddresses`, `type`, `visible`, `userid`, `alias`, `attach`, `html`, `tempid`, `key`, `frequency`, `params`,`filter`,`metakey`,`metadesc`)';
			$query .= " SELECT CONCAT('copy_',`subject`), `body`, `altbody`, 0".$addSendDate.", '.$time.', `fromname`, `fromemail`, `replyname`, `replyemail`, `bccaddresses`, `type`, `visible`, '.$creatorId.', `alias`, `attach`, `html`, `tempid`, ".acymailing_escapeDB(acymailing_generateKey(8)).', `frequency`, `params`,`filter`,`metakey`,`metadesc` FROM `#__acymailing_mail` WHERE `mailid` = '.(int)$oneMailid;
			acymailing_query($query);
			$newMailid = acymailing_insertID();
			acymailing_query('INSERT IGNORE INTO `#__acymailing_listmail` (`listid`,`mailid`) SELECT `listid`,'.$newMailid.' FROM `#__acymailing_listmail` WHERE `mailid` = '.(int)$oneMailid);
			acymailing_query('INSERT IGNORE INTO `#__acymailing_tagmail` (`tagid`,`mailid`) SELECT `tagid`,'.$newMailid.' FROM `#__acymailing_tagmail` WHERE `mailid` = '.(int)$oneMailid);
		}

		return $this->listing();
	}

	function store(){
			if(!$this->isAllowed($this->aclCat, 'manage')) return;
			acymailing_checkToken();
			header('X-XSS-Protection:0');

			$mailClass = acymailing_get('class.mail');
			$status = $mailClass->saveForm();
			if($status){
				acymailing_enqueueMessage(acymailing_translation('JOOMEXT_SUCC_SAVED'), 'message');
			}else{
				acymailing_enqueueMessage(acymailing_translation('ERROR_SAVING'), 'error');
				if(!empty($mailClass->errors)){
					foreach($mailClass->errors as $oneError){
						acymailing_enqueueMessage($oneError, 'error');
					}
				}
			}
	}

	function unschedule(){
		if(!$this->isAllowed($this->aclCat, 'schedule')) return;
		acymailing_checkToken();
		$mailid = acymailing_getCID('mailid');

		if(empty($mailid)) die('Missing mail ID');
		$mail = new stdClass();
		$mail->mailid = $mailid;
		$mail->senddate = 0;
		$mail->published = 0;

		$mailClass = acymailing_get('class.mail');
		$mailClass->save($mail);

		acymailing_enqueueMessage(acymailing_translation('SUCC_UNSCHED'));

		return $this->preview();
	}

	function remove(){
		if(!$this->isAllowed($this->aclCat, 'delete')) return;
		acymailing_checkToken();

		$cids = acymailing_getVar('array', 'cid', array(), '');

		$class = acymailing_get('class.mail');
		$num = $class->delete($cids);

		acymailing_arrayToInteger($cids);
		acymailing_query('DELETE FROM `#__acymailing_listmail` WHERE `mailid` IN ('.implode(',', $cids).')');

		acymailing_enqueueMessage(acymailing_translation_sprintf('SUCC_DELETE_ELEMENTS', $num), 'message');

		return $this->listing();
	}

	function savepreview(){
		$this->store();
		return $this->preview();
	}


	function saveastmpl(){
		$this->store();
		$mailclass = acymailing_get('class.mail');
		$mailclass->saveastmpl();
		return $this->edit();
	}

	function preview(){
		acymailing_setVar('layout', 'preview');
		return parent::display();
	}

	function sendtest(){
		$this->_sendtest();
		return $this->preview();
	}

	function _sendtest(){
		acymailing_checkToken();

		$mailid = acymailing_getCID('mailid');
		$test_selection = acymailing_getVar('string', 'test_selection', '', '');

		if(empty($mailid) OR empty($test_selection)) return false;

		$mailer = acymailing_get('helper.mailer');
		$mailer->forceVersion = acymailing_getVar('int', 'test_html', 1, '');
		$mailer->autoAddUser = true;
		if(acymailing_isAdmin()) $mailer->SMTPDebug = 1;
		$mailer->checkConfirmField = false;
		$comment = acymailing_getVar('string', 'commentTest', '');
		if(!empty($comment)) $mailer->introtext = '<div align="center" style="max-width:600px;margin:auto;margin-top:10px;margin-bottom:10px;padding:10px;border:1px solid #cccccc;background-color:#f6f6f6;color:#333333;">'.nl2br($comment).'</div>';

		$receivers = array();
		if($test_selection == 'users'){
			$receiverEntry = acymailing_getVar('string', 'test_emails', '', '');
			if(!empty($receiverEntry)){
				if(substr_count($receiverEntry, '@') > 1){
					$receivers = explode(',', trim(preg_replace('# +#', '', $receiverEntry)));
				}else{
					$receivers[] = trim($receiverEntry);
				}
			}
		}else{
			$gid = acymailing_getVar('int', 'test_group', '-1');
			if($gid == -1) return false;
			if(!ACYMAILING_J16){
				$receivers = acymailing_loadResultArray('SELECT '.$this->cmsUserVars->email.' AS email FROM '.acymailing_table($this->cmsUserVars->table, false).' WHERE gid = '.intval($gid));
			}else{
				$receivers = acymailing_loadResultArray('SELECT u.'.$this->cmsUserVars->email.' AS email FROM '.acymailing_table($this->cmsUserVars->table, false).' AS u JOIN '.acymailing_table('user_usergroup_map', false).' AS ugm ON u.'.$this->cmsUserVars->id.' = ugm.user_id WHERE ugm.group_id = '.intval($gid));
			}
		}

		if(empty($receivers)){
			acymailing_enqueueMessage(acymailing_translation('NO_SUBSCRIBER'), 'notice');
			return false;
		}

		$result = true;
		foreach($receivers as $receiver){
			$result = $mailer->sendOne($mailid, $receiver) && $result;
		}

		return $result;
	}

	function upload(){
		if(!$this->isAllowed($this->aclCat, 'manage')) return;
		acymailing_setVar('layout', 'upload');
		return parent::display();
	}

	function abtesting(){
		acymailing_setVar('layout', 'abtesting');
		return parent::display();
	}

	function abtest(){
		$nbTotalReceivers = acymailing_getVar('int', 'nbTotalReceivers');
		$mailids = acymailing_getVar('string', 'mailid');
		$mailsArray = explode(',', $mailids);
		acymailing_arrayToInteger($mailsArray);


		$abTesting_prct = acymailing_getVar('int', 'abTesting_prct');
		$abTesting_delay = acymailing_getVar('int', 'abTesting_delay');
		$abTesting_action = acymailing_getVar('string', 'abTesting_action');

		if(empty($abTesting_prct)){
			acymailing_display(acymailing_translation('ABTESTING_NEEDVALUE'), 'warning');
			$this->abtesting();
			return;
		}

		$newAbTestDetail = array();
		$newAbTestDetail['mailids'] = implode(',', $mailsArray);
		$newAbTestDetail['prct'] = (!empty($abTesting_prct) ? $abTesting_prct : '');
		$newAbTestDetail['delay'] = (isset($abTesting_delay) && strlen($abTesting_delay) > 0 ? $abTesting_delay : '2');
		$newAbTestDetail['action'] = (!empty($abTesting_action) ? $abTesting_action : 'manual');
		$newAbTestDetail['time'] = time();
		$newAbTestDetail['status'] = 'inProgress';
		$mailClass = acymailing_get('class.mail');
		$nbReceiversTest = $mailClass->ab_test($newAbTestDetail, $mailsArray, $nbTotalReceivers);

		acymailing_enqueueMessage(acymailing_translation_sprintf('ABTESTING_SUCCESSADD', $nbReceiversTest), 'info');
		acymailing_setVar('validationStatus', 'abTestAdd');
		$this->abtesting();
	}

	function complete_abtest(){
		$mailid = acymailing_getVar('int', 'mailToSend');
		$mailClass = acymailing_get('class.mail');
		$newMailid = $mailClass->complete_abtest('manual', $mailid);

		$finalMail = $mailClass->get($newMailid);
		acymailing_enqueueMessage(acymailing_translation_sprintf('ABTESTING_FINALSEND', $finalMail->subject), 'info');
		acymailing_setVar('validationStatus', 'abTestFinalSend');
		$this->abtesting();
	}

	function douploadnewsletter(){
		if(!$this->isAllowed($this->aclCat, 'manage')) return;
		acymailing_checkToken();

		$templateClass = acymailing_get('class.template');
		$templateClass->checkAreas = false;
		$statusUpload = $templateClass->doupload();

		if($statusUpload){
			$mailClass = acymailing_get('class.mail');
			$mail = new stdClass();
			$newTemplate = $templateClass->get($templateClass->templateId);
			$mail->subject = $newTemplate->name;
			$mail->body = $newTemplate->body;
			$mail->tempid = $templateClass->templateId;

			$idMailCreated = $mailClass->save($mail);
			if($idMailCreated){
				acymailing_enqueueMessage(acymailing_translation('NEWSLETTER_INSTALLED'), 'success');
				acymailing_setNoTemplate(false);
				$js = "setTimeout('redirect()',2000); function redirect(){window.top.location.href = '".acymailing_completeLink('newsletter&task=edit&mailid='.$idMailCreated, false, true)."'; }";
				acymailing_addScript(true, $js);
				return;
			}else{
				acymailing_enqueueMessage(acymailing_translation('ERROR_SAVING'), 'error');
				return $this->upload();
			}
		}else{
			return $this->upload();
		}
	}

	function cancelNewsletter(){
		$queueController = acymailing_get('controller.queue');
		$queueController->cancelNewsletter();
		return $this->listing();
	}

	function checkifedited(){
		if(empty($_SESSION['timeOnModification'])) exit;

		$mailClass = acymailing_get('class.mail');
		$mailId = acymailing_getVar('int', 'mailId');
		$mail = $mailClass->get($mailId);

		if(!empty($mail->lastupdate) && $_SESSION['timeOnModification'] < $mail->lastupdate){
			$userId = acymailing_loadResult('SELECT userlastupdate FROM #__acymailing_mail WHERE mailid = '.intval($mailId));
			echo $userId.'|'.acymailing_currentUserName($userId);
		}
		exit;
	}

	function cancel(){
		header('X-XSS-Protection:0');
		return $this->listing();
	}
}
