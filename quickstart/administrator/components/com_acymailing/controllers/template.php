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

class TemplateController extends acymailingController{

	var $pkey = 'tempid';
	var $table = 'template';
	var $aclCat = 'templates';

	function load(){
		$class = acymailing_get('class.template');
		$tempid = acymailing_getVar('int', 'tempid');
		if(empty($tempid)) exit;
		$template = $class->get($tempid);

		header("Content-type: text/css");
		echo $class->buildCSS($template->styles, $template->stylesheet);
		exit;
	}

	function applyareas(){
		if(!$this->isAllowed($this->aclCat, 'manage')) return;

		$class = acymailing_get('class.template');
		$tempid = acymailing_getVar('int', 'tempid');
		if(empty($tempid)) exit;
		$template = $class->get($tempid);
		$class->applyAreas($template->body);
		$class->save($template);

		$class->createTemplateFile($tempid);

		acymailing_enqueueMessage(acymailing_translation('ACYEDITOR_ADDAREAS_DONE'));

		if(acymailing_isNoTemplate()){
			$js = "setTimeout('redirect()',2000); function redirect(){window.top.location.href = '".acymailing_completeLink('template')."'; }";
			acymailing_addScript(true, $js);
		}else{
			return $this->listing();
		}
	}

	function remove(){
		if(!$this->isAllowed($this->aclCat, 'delete')) return;
		acymailing_checkToken();
		acymailing_isAdmin() or die('Only from the back-end');

		$cids = acymailing_getVar('array', 'cid', array(), '');

		$class = acymailing_get('class.template');
		$num = $class->delete($cids);

		acymailing_enqueueMessage(acymailing_translation_sprintf('SUCC_DELETE_ELEMENTS', $num), 'message');

		return $this->listing();
	}

	function copy(){
		if(!$this->isAllowed($this->aclCat, 'manage')) return;
		acymailing_checkToken();

		$cids = acymailing_getVar('array', 'cid', array(), '');
		$time = time();

		acymailing_arrayToInteger($cids);

		$query = 'INSERT IGNORE INTO `#__acymailing_template` (`name`, `description`, `body`, `altbody`, `created`, `published`, `premium`, `ordering`, `namekey`, `styles`, `subject`,`stylesheet`,`fromname`,`fromemail`,`replyname`,`replyemail`,`thumb`,`readmore`,`category`)';
		$query .= " SELECT CONCAT('copy_',`name`), `description`, `body`, `altbody`, $time, `published`, 0, `ordering`, CONCAT('$time',`tempid`,`namekey`), `styles`, `subject`,`stylesheet`,`fromname`,`fromemail`,`replyname`,`replyemail`,`thumb`,`readmore`,`category` FROM `#__acymailing_template` WHERE `tempid` IN (".implode(',', $cids).')';
		acymailing_query($query);

		$orderClass = acymailing_get('helper.order');
		$orderClass->pkey = 'tempid';
		$orderClass->table = 'template';
		$orderClass->reOrder();

		return $this->listing();
	}

	function store(){
		if(!$this->isAllowed($this->aclCat, 'manage')) return;
		acymailing_checkToken();

		acymailing_isAdmin() or die('Only from the back-end');

		$templateClass = acymailing_get('class.template');
		$status = $templateClass->saveForm();
		if($status){
			acymailing_enqueueMessage(acymailing_translation('JOOMEXT_SUCC_SAVED'), 'message');
			$templateClass->proposeApplyAreas(acymailing_getVar('int', 'tempid'));
		}else{
			acymailing_enqueueMessage(acymailing_translation('ERROR_SAVING'), 'error');
			if(!empty($templateClass->errors)){
				foreach($templateClass->errors as $oneError){
					acymailing_enqueueMessage($oneError, 'error');
				}
			}
		}
	}

	function theme(){
		if(!$this->isAllowed($this->aclCat, 'view')) return;
		acymailing_setVar('layout', 'theme');
		return parent::display();
	}

	function upload(){
		if(!$this->isAllowed($this->aclCat, 'manage')) return;
		acymailing_setVar('layout', 'upload');
		return parent::display();
	}

	function doupload(){
		if(!$this->isAllowed($this->aclCat, 'manage')) return;
		acymailing_checkToken();

		$templateClass = acymailing_get('class.template');
		$statusUpload = $templateClass->doupload();

		if($statusUpload){
			if(!$templateClass->proposedAreas){
				acymailing_setNoTemplate(false);
				$js = "setTimeout('redirect()',2000); function redirect(){window.top.location.href = '".acymailing_completeLink('template', false, true)."'; }";
				acymailing_addScript(true, $js);
			}
			return;
		}else{
			return $this->upload();
		}
	}

	function export(){
		if(!$this->isAllowed($this->aclCat, 'manage')) return;
		acymailing_checkToken();

		$cids = acymailing_getVar('array', 'cid', array(), '');

		acymailing_arrayToInteger($cids);
		$templateClass = acymailing_get('class.template');
		$resExport = $templateClass->export($cids[0]);

		if(!empty($resExport)) acymailing_enqueueMessage(acymailing_translation_sprintf('ACYTEMPLATE_EXPORTED', '<a href="'.$resExport.'">', '</a>'), 'success');
		return $this->listing();
	}

	function test(){
		if(!$this->isAllowed($this->aclCat, 'manage')) return;
		$this->store();

		$tempid = acymailing_getCID('tempid');
		$test_selection = acymailing_getVar('string', 'test_selection', '', '');
		if(empty($tempid) OR empty($test_selection)) return;

		$mailer = acymailing_get('helper.mailer');
		$mailer->report = true;
		$config = acymailing_config();
		$subscriberClass = acymailing_get('class.subscriber');
		$userHelper = acymailing_get('helper.user');
		acymailing_importPlugin('acymailing');

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
			return $this->edit();
		}

		$classTemplate = acymailing_get('class.template');
		$myTemplate = $classTemplate->get($tempid);
		$myTemplate->sendHTML = 1;
		$myTemplate->mailid = 0;
		$myTemplate->template = $myTemplate;
		if(empty($myTemplate->subject)) $myTemplate->subject = $myTemplate->name;
		if(empty($myTemplate->altBody)) $myTemplate->altbody = $mailer->textVersion($myTemplate->body);
		acymailing_trigger('acymailing_replacetags', array(&$myTemplate, true));

		$myTemplate->body = acymailing_absoluteURL($myTemplate->body);

		$result = true;
		foreach($receivers as $receiveremail){
			$copy = $myTemplate;
			$mailer->clearAll();
			$mailer->setFrom($copy->fromemail, $copy->fromname);
			if(!empty($copy->replyemail)){
				$replyToName = $config->get('add_names', true) ? $mailer->cleanText($copy->replyname) : '';
				$mailer->AddReplyTo($mailer->cleanText($copy->replyemail), $replyToName);
			}

			$receiver = $subscriberClass->get($receiveremail);
			if(empty($receiver->subid)){
				if($userHelper->validEmail($receiveremail)){
					$newUser = new stdClass();
					$newUser->email = $receiveremail;
					$subscriberClass->sendConf = false;
					$subid = $subscriberClass->save($newUser);
					$receiver = $subscriberClass->get($subid);
				}
				if(empty($receiver->subid)) continue;
			}

			$addedName = $config->get('add_names', true) ? $mailer->cleanText($receiver->name) : '';
			$mailer->AddAddress($mailer->cleanText($receiver->email), $addedName);

			acymailing_trigger('acymailing_replaceusertags', array(&$copy, &$receiver, true));
			$mailer->isHTML(true);
			$mailer->Body = $copy->body;
			$mailer->Subject = $copy->subject;
			if($config->get('multiple_part', false)){
				$mailer->AltBody = $copy->altbody;
			}

			$mailer->send();
		}

		return $this->edit();
	}
}
