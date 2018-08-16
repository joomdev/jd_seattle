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

class mailClass extends acymailingClass{

	var $tables = array('queue', 'listmail', 'stats', 'userstats', 'urlclick', 'mail');
	var $pkey = 'mailid';
	var $namekey = 'alias';
	var $allowedFields = array('subject', 'published', 'fromname', 'fromemail', 'replyname', 'replyemail', 'type', 'visible', 'alias', 'html', 'tempid', 'altbody', 'filter', 'metakey', 'metadesc', 'language', 'summary', 'thumb', 'params');

	function get($id, $default = null){

		if(empty($id)) return null;

		$query = 'SELECT a.* FROM '.acymailing_table('mail').' as a WHERE ';
		$query .= is_numeric($id) ? 'a.mailid' : 'a.alias';
		$query .= ' = '.acymailing_escapeDB($id);
		$query .= ' LIMIT 1';

		$mail = acymailing_loadObject($query);

		if(empty($mail) || empty($mail->mailid)) return $default;

		if(!empty($mail->userid)){
			$author = acymailing_loadObject('SELECT b.'.$this->cmsUserVars->username.' AS username, b.'.$this->cmsUserVars->name.' AS name, b.'.$this->cmsUserVars->email.' AS email FROM '.acymailing_table('users', false).' as b WHERE b.'.$this->cmsUserVars->id.' = '.intval($mail->userid).' LIMIT 1');
			if(!empty($author)){
				foreach($author as $var => $value){
					$mail->$var = $value;
				}
			}
		}

		$mail->subject = acyEmoji::Decode($mail->subject);
		$mail->attach = empty($mail->attach) ? array() : unserialize($mail->attach);
		$mail->favicon = empty($mail->favicon) ? new stdClass() : unserialize($mail->favicon);
		$mail->params = empty($mail->params) ? array() : unserialize($mail->params);
		$mail->filter = empty($mail->filter) ? array() : unserialize($mail->filter);

		return $mail;
	}

	function getMails($types = null, $key = 'mailid'){
		$query = 'SELECT * FROM '.acymailing_table('mail');

		$allowedTypes = array('action', 'autonews', 'followup', 'joomlanotification', 'news', 'notification', 'unsub', 'welcome');
		if(!empty($types)){
			$notAllowed = array_diff($types, $allowedTypes);
			if(!empty($notAllowed)) die('Invalid type(s) '.implode(', ', $types));
			$query .= ' WHERE type = "'.implode('" OR type = "', $types).'"';
		}

		$query .= ' ORDER BY created DESC LIMIT 3000';

		$mails = acymailing_loadObjectList($query);

		$result = array();
		if(!empty($key) && !empty($mails) && !isset($mails[0]->$key)) die('Invalid key '.$key);
		foreach($mails as $oneMail){
			$oneMail->subject = acyEmoji::Decode($oneMail->subject);
			$oneMail->attach = empty($oneMail->attach) ? array() : unserialize($oneMail->attach);
			$oneMail->favicon = empty($oneMail->favicon) ? new stdClass() : unserialize($oneMail->favicon);
			$oneMail->params = empty($oneMail->params) ? array() : unserialize($oneMail->params);
			$oneMail->filter = empty($oneMail->filter) ? array() : unserialize($oneMail->filter);

			if(empty($key))	$result[$oneMail->type][] = $oneMail;
			else $result[$oneMail->type][$oneMail->$key] = $oneMail;
		}

		return $result;
	}

	function saveForm(){
		$config = acymailing_config();

		$mail = new stdClass();
		$mail->mailid = acymailing_getCID('mailid');

		$formData = acymailing_getVar('array', 'data', array(), '');
		if(!empty($formData['mail']['subject'])) $formData['mail']['subject'] = str_replace(chr(226).chr(128).chr(168), '', $formData['mail']['subject']);
		$formData['mail']['subject'] = acyEmoji::Encode($formData['mail']['subject']);

		$result = preg_match('/(\\\u[0-9a-f]{4})+/i', $formData['mail']['subject']);
		if($result){
			$toggleClass = acymailing_get('helper.toggle');
			if($config->get('emojiwarning', 1)){
				$notremind = acymailing_isAdmin() ? '<small style="float:right;margin-right:30px;position:relative;">'.$toggleClass->delete('acymailing_messages_warning', 'emojiwarning_0', 'config', false, acymailing_translation('DONT_REMIND')).'</small>' : '';
				acymailing_enqueueMessage(acymailing_translation_sprintf('ACY_EMOJI_CONFIRMATION', '<a target="_blank" href="'.ACYMAILING_HELPURL.'newsletters&level=Enterprise#infos">', '</a>').' '.$notremind, 'warning');
			}
		}

		foreach($formData['mail'] as $column => $value){
			if(!acymailing_isAdmin() && !in_array($column, $this->allowedFields)) continue;
			acymailing_secureField($column);
			if(in_array($column, array('params', 'summary'))){
				$mail->$column = $value;
			}else{
				$mail->$column = strip_tags($value, '<ADV>');
			}
		}

		$mail->lastupdate = time();
		$mail->userlastupdate = acymailing_currentUserId();

		$mail->body = acymailing_getVar('string', 'editor_body', '', '', ACY_ALLOWRAW);
		$mail->body = acymailing_filterText($mail->body);

		$acypluginsHelper = acymailing_get('helper.acyplugins');
		$acypluginsHelper->cleanHtml($mail->body);
		$mail->body = $acypluginsHelper->removeJS($mail->body);

		$mail->attach = array();
		$attachments = acymailing_getVar('array', 'attachments', array(), '');

		if(!empty($attachments)){
			foreach($attachments as $id => $filepath){
				if(empty($filepath)) continue;
				$attachment = new stdClass();
				$attachment->filename = $filepath;
				$attachment->size = filesize(ACYMAILING_ROOT.$filepath);
				$extension = substr($attachment->filename, strrpos($attachment->filename, '.'));

				if(preg_match('#\.(php.?|.?htm.?|pl|py|jsp|asp|sh|cgi)#Ui', $attachment->filename)){
					acymailing_enqueueMessage(acymailing_translation_sprintf('ACCEPTED_TYPE', substr($attachment->filename, strrpos($attachment->filename, '.') + 1), $config->get('allowedfiles')), 'notice');
					continue;
				}
				$attachment->filename = str_replace(array('.', ' '), '_', substr($attachment->filename, 0, strpos($attachment->filename, $extension))).$extension;

				$mail->attach[] = $attachment;
			}
		}

		$faviconRequest = acymailing_getVar('none', 'favicon', '');
		if(!empty($faviconRequest[0])){
			$faviconRequest = $faviconRequest[0];
			$favicon = new stdClass();
			$favicon->filename = $faviconRequest;
			$favicon->size = filesize(ACYMAILING_ROOT.$faviconRequest);
			$extension = substr($favicon->filename, strrpos($favicon->filename, '.'));
			if(preg_match('#\.(php.?|.?htm.?|pl|py|jsp|asp|sh|cgi)#Ui', $favicon->filename)){
				acymailing_enqueueMessage(acymailing_translation_sprintf('ACCEPTED_TYPE', substr($favicon->filename, strrpos($favicon->filename, '.') + 1), $config->get('allowedfiles')), 'notice');
			}

			$favicon->filename = str_replace(array('.', ' '), '_', substr($favicon->filename, 0, strpos($favicon->filename, $extension))).$extension;

			$mail->favicon = $favicon;
		}

		if(isset($mail->filter)){
			$mail->filter = array();
			$filterData = acymailing_getVar('none', 'filter');
			unset($filterData['type']['__block__']);
			unset($filterData['__num__']);
			$realNum = 0;
			$blockNum = 0;
			foreach ($filterData['type'] as $oneFilter){
				foreach($oneFilter as $num => $oneType) {
					if (empty($oneType)) continue;
					$mail->filter['type'][$blockNum][$realNum] = $oneType;
					$mail->filter[$realNum][$oneType] = $filterData[$num][$oneType];
					$realNum++;
				}
				$blockNum++;
			}
		}

		$toggleHelper = acymailing_get('helper.toggle');
		if(!empty($mail->type) && $mail->type == 'followup' && !empty($mail->mailid)){
			$oldMail = $this->get($mail->mailid);
			if(!empty($mail->published) AND !$oldMail->published){
				$this->_publishfollowup($mail);
			}
			if($oldMail->senddate != $mail->senddate){
				$text = acymailing_translation('FOLLOWUP_CHANGED_DELAY_INFORMED');
				$text .= ' '.$toggleHelper->toggleText('update', $mail->mailid, 'followup', acymailing_translation('FOLLOWUP_CHANGED_DELAY'));
				acymailing_enqueueMessage($text, 'notice');
			}
		}

		if(preg_match('#<a[^>]*subid=[0-9].*</a>#Uis', $mail->body, $pregResult)){
			acymailing_enqueueMessage(acymailing_translation_sprintf('ACY_PERSONAL_LINK', $pregResult[0]), 'warning');
		}

		if(empty($mail->thumb)){
			unset($mail->thumb);
		}elseif($mail->thumb == 'delete'){
			$mail->thumb = '';
		}
		if(isset($mail->published) && $mail->published != 1) $mail->published = 0;
		if(isset($mail->html) && $mail->html != 1) $mail->html = 0;
		if(isset($mail->visible) && $mail->visible != 1) $mail->visible = 0;
		$mailid = $this->save($mail);
		if(!$mailid) return false;
		acymailing_setVar('mailid', $mailid);

		$selectedTags = acymailing_getVar('array', 'tags', array(), '');
		
		acymailing_query('DELETE FROM #__acymailing_tagmail WHERE mailid = '.intval($mailid));

		if(!empty($selectedTags)){
			$securedTags = array();

			foreach($selectedTags as $oneTag){
				$securedTags[] = acymailing_escapeDB($oneTag);
			}

			$existingTags = acymailing_loadResultArray('SELECT name FROM #__acymailing_tag WHERE name = '.implode(' OR name = ', $securedTags));
			$nonExistingTags = array_diff($selectedTags, $existingTags);

			if(!empty($nonExistingTags)){
				$query = 'INSERT INTO #__acymailing_tag (name, userid) VALUES ';
				foreach($nonExistingTags as &$oneTag){
					$oneTag = '('.acymailing_escapeDB($oneTag).', '.intval(acymailing_currentUserId()).')';
				}
				acymailing_query($query.implode(',', $nonExistingTags));
			}

			$allTags = acymailing_loadResultArray('SELECT tagid FROM #__acymailing_tag WHERE name = '.implode(' OR name = ', $securedTags));

			acymailing_query('INSERT INTO #__acymailing_tagmail (tagid, mailid) VALUES ('.implode(','.intval($mailid).'),(', $allTags).','.intval($mailid).')');
		}

		$status = true;

		if(!empty($formData['listmail'])){
			$receivers = array();
			$remove = array();

			foreach($formData['listmail'] as $listid => $receiveme){
				if(!empty($receiveme)){
					$receivers[] = $listid;
				}else{
					$remove[] = $listid;
				}
			}

			$listMailClass = acymailing_get('class.listmail');
			$status = $listMailClass->save($mailid, $receivers, $remove);
		}

		if(!empty($mail->type) && $mail->type == 'followup' && empty($mail->mailid) && !empty($mail->published)){
			$mail->mailid = $mailid;
			$this->_publishfollowup($mail);
		}

		return $status;
	}

	function addFollowUpQueue($mailid, $all = false){
		$followup = $this->get($mailid);
		if(empty($followup->mailid)){
			$this->errors[] = 'Could not load mailid '.$mailid;
			return false;
		}

		$listmailClass = acymailing_get('class.listmail');
		$mycampaign = $listmailClass->getCampaign($followup->mailid);
		if(empty($mycampaign->listid)){
			$this->errors[] = 'Could not get the attached campaign';
			return false;
		}

		$config = acymailing_config();

		$query = 'INSERT IGNORE INTO `#__acymailing_queue` (`mailid`,`senddate`,`priority`,`subid`) ';
		$query .= 'SELECT '.$followup->mailid.', b.`subdate` + '.intval($followup->senddate).' , '.(int)$config->get('priority_followup', 2).', b.`subid` ';
		$query .= 'FROM `#__acymailing_listsub` as b';
		$query .= ' WHERE b.`status` = 1 AND b.`listid` = '.intval($mycampaign->listid);
		if(!$all) $query .= ' AND b.`subdate` > '.(time() - $followup->senddate);
		$nbinserted = acymailing_query($query);

		if(!empty($nbupdated)){
			$campaignHelper = acymailing_get('helper.campaign');
			$campaignHelper->updateUnsubdate($mycampaign->listid, $followup->senddate);
		}

		return $nbinserted;
	}

	private function _publishfollowup(&$mail){
		$listmailClass = acymailing_get('class.listmail');
		$mycampaign = $listmailClass->getCampaign($mail->mailid);

		if(empty($mycampaign->listid)){
			return;
		}

		$toggleHelper = acymailing_get('helper.toggle');
		$startdate = (time() - $mail->senddate);
		$total = acymailing_loadResult('SELECT COUNT(subid) as total FROM `#__acymailing_listsub` as b WHERE b.`status` = 1 AND b.`listid` = '.intval($mycampaign->listid).' AND b.`subdate` > '.intval($startdate));

		$totalall= acymailing_loadResult('SELECT COUNT(subid) as total FROM `#__acymailing_listsub` as b WHERE b.`status` = 1 AND b.`listid` = '.intval($mycampaign->listid));

		if(empty($total) && empty($totalall)) return;

		$text = acymailing_translation('FOLLOWUP_PUBLISHED_INFORMED');
		$text .= '<ul>';
		if(!empty($total)) $text .= '<li>'.$toggleHelper->toggleText('add', $mail->mailid, 'followup', acymailing_translation_sprintf('FOLLOWUP_ADDQUEUE_USERS', acymailing_getDate($startdate)).' ( '.acymailing_translation_sprintf('SELECTED_USERS', $total).' )').'</li>';
		if(!empty($totalall)) $text .= '<li>'.$toggleHelper->toggleText('addall', $mail->mailid, 'followup', acymailing_translation('FOLLOWUP_ADDQUEUE_ALLUSERS').' ( '.acymailing_translation_sprintf('SELECTED_USERS', $totalall).' )').'</li>';

		acymailing_enqueueMessage($text, 'notice');
	}

	function save($mail){
		if(isset($mail->alias) OR empty($mail->mailid)){
			if(empty($mail->alias)){
				$mail->alias = $mail->subject;
				$mail->alias = preg_replace('/(\\\u[0-9a-f]{4})+/i', '', $mail->alias);
			}
			$mail->alias = acymailing_cleanSlug($mail->alias);
		}

		if(empty($mail->mailid)){
			if(empty($mail->created)) $mail->created = time();
			if(empty($mail->userid)){
				$mail->userid = acymailing_currentUserId();
			}
			if(empty($mail->key)) $mail->key = acymailing_generateKey(8);
		}else{
			if(!empty($mail->attach)){
				$oldMailObject = $this->get($mail->mailid);
				if(!empty($oldMailObject) && is_array($oldMailObject->attach)){
					$mail->attach = array_merge($oldMailObject->attach, $mail->attach);
				}
			}
		}

		if(empty($mail->attach)) unset($mail->attach);
		if(empty($mail->favicon)) unset($mail->favicon);

		if(!empty($mail->attach) && !is_string($mail->attach)) $mail->attach = serialize($mail->attach);
		if(!empty($mail->favicon) && !is_string($mail->favicon)) $mail->favicon = serialize($mail->favicon);
		if(isset($mail->filter) && !is_string($mail->filter)) $mail->filter = serialize($mail->filter);

		if(!empty($mail->params)){
			if(!empty($mail->params['lastgenerateddate']) && !is_numeric($mail->params['lastgenerateddate'])){
				$mail->params['lastgenerateddate'] = acymailing_getTime($mail->params['lastgenerateddate']);
			}

			if(!empty($mail->mailid)) {
				$oldMail = $this->get($mail->mailid);
				if(!empty($oldMail->params)){
					foreach($oldMail->params as $key => $val){
						if(!isset($mail->params[$key])) $mail->params[$key] = $val;
					}
				}
			}

			$mail->params = serialize($mail->params);
		}

		if(!empty($mail->senddate) && !is_numeric($mail->senddate)){
			$mail->senddate = acymailing_getTime($mail->senddate);
		}

		acymailing_importPlugin('acymailing');

		if(empty($mail->mailid)){
			acymailing_trigger('onAcyBeforeMailCreate', array(&$mail));
			$status = acymailing_insertObject(acymailing_table('mail'), $mail);
		}else{
			acymailing_trigger('onAcyBeforeMailModify', array(&$mail));
			$status = acymailing_updateObject(acymailing_table('mail'), $mail, 'mailid');
		}

		if(!$status){
			$this->errors[] = substr(strip_tags(acymailing_getDBError()), 0, 200).'...';
		}

		if(!empty($mail->params) && is_string($mail->params)) $mail->params = unserialize($mail->params);
		if(!empty($mail->attach) && is_string($mail->attach)) $mail->attach = unserialize($mail->attach);
		if(!empty($mail->favicon) && is_string($mail->favicon)) $mail->favicon = unserialize($mail->favicon);

		if($status) return empty($mail->mailid) ? $status : $mail->mailid;
		return false;
	}

	function saveastmpl(){
		$tmplClass = acymailing_get('class.template');
		$newTmpl = new stdClass();

		$formData = acymailing_getVar('array', 'data', array(), '');
		if(!empty($formData['mail']['tempid'])){
			$template = $tmplClass->get($formData['mail']['tempid']);
			$newTmpl->styles = $template->styles;
			$newTmpl->stylesheet = $template->stylesheet;
			$newTmpl->category = $template->category;
		}
		if(!empty($formData['mail']['subject'])){
			$formData['mail']['subject'] = str_replace(chr(226).chr(128).chr(168), '', $formData['mail']['subject']);
			$newTmpl->subject = strip_tags($formData['mail']['subject']);
			$newTmpl->name = strip_tags($formData['mail']['subject']);
		}

		$newTmpl->body = acymailing_getVar('string', 'editor_body', '', '', ACY_ALLOWRAW);
		$newTmpl->body = acymailing_filterText($newTmpl->body);
		$acypluginsHelper = acymailing_get('helper.acyplugins');
		$acypluginsHelper->cleanHtml($newTmpl->body);

		if(!empty($formData['mail']['thumb']) && $formData['mail']['thumb'] == 'delete'){
			$newTmpl->thumb = null;
		}elseif(!empty($formData['mail']['thumb'])){
			$newTmpl->thumb = strip_tags($formData['mail']['thumb']);
		}else{
			$mailid = acymailing_getCID('mailid');
			if(!empty($mailid)){
				$mail = $this->get($mailid);
				$newTmpl->thumb = $mail->thumb;
			}
		}
		if(!empty($formData['mail']['altbody'])) $newTmpl->altbody = strip_tags($formData['mail']['altbody']);
		if(!empty($formData['mail']['fromname'])) $newTmpl->fromname = strip_tags($formData['mail']['fromname']);
		if(!empty($formData['mail']['fromemail'])) $newTmpl->fromemail = strip_tags($formData['mail']['fromemail']);
		if(!empty($formData['mail']['replyname'])) $newTmpl->replyname = strip_tags($formData['mail']['replyname']);
		if(!empty($formData['mail']['replyemail'])) $newTmpl->replyemail = strip_tags($formData['mail']['replyemail']);
		if(!empty($formData['mail']['summary'])) $newTmpl->description = strip_tags($formData['mail']['summary']);
		$newTmpl->ordering = 1;

		$tempid = $tmplClass->save($newTmpl);
		if(!empty($tempid)){
			$formData['mail']['tempid'] = $tempid;
			acymailing_enqueueMessage(acymailing_translation('ACY_SAVEASTMPL_VALID'), 'message');
		}else{
			acymailing_enqueueMessage(acymailing_translation('ERROR_SAVING'), 'error');
		}

		return true;
	}


	function ab_test($abTestDetail, $mailsArray, $nbTotalReceivers){
		$query = "UPDATE #__acymailing_mail SET abtesting=".acymailing_escapeDB(serialize($abTestDetail)).", published=1 WHERE mailid IN (".implode(',', $mailsArray).")";
		acymailing_query($query);

		if($abTestDetail['action'] != 'manual'){
			$config = acymailing_config();
			$currentAbTests = $config->get('currentABTests', '');
			if(!empty($currentAbTests)){
				$currentData = unserialize($currentAbTests);
			}else $currentData = array();
			$newTest = new stdClass();
			$newTest->sendDate = $abTestDetail['time'] + ($abTestDetail['delay'] * 86400);
			$newTest->ids = $abTestDetail['mailids'];
			$currentData[] = $newTest;
			$newconfig = new stdClass();
			$newconfig->currentABTests = serialize($currentData);
			$config->save($newconfig);
		}

		$statsClass = acymailing_get('class.stats');
		$statsClass->delete($mailsArray);

		$queueClass = acymailing_get('class.queue');
		$time = time();
		$nbReceiversTest = floor($nbTotalReceivers * $abTestDetail['prct'] / 100);
		$queueClass->limit = $nbReceiversTest;
		$queueClass->orderBy = 'RAND()';
		$queueClass->queue($mailsArray[0], $time);
		$nbReceiversPerMail = floor($nbReceiversTest / count($mailsArray));
		foreach($mailsArray as $oneMail){
			if($oneMail == $mailsArray[0]) continue;
			$query = "UPDATE #__acymailing_queue SET mailid=".intval($oneMail)." WHERE mailid=".intval($mailsArray[0])." LIMIT ".$nbReceiversPerMail;
			acymailing_query($query);
		}
		$query = "UPDATE #__acymailing_mail SET senddate=".$time." WHERE mailid IN (".implode(',', $mailsArray).")";
		acymailing_query($query);
		return $nbReceiversTest;
	}


	function complete_abtest($typeAction, $mailid){
		$resDetails = acymailing_loadResultArray("SELECT abtesting FROM #__acymailing_mail WHERE mailid=".(int)$mailid);
		$abTestDetail = unserialize($resDetails[0]);
		$dataForCopy = array('mailid' => $mailid, 'abTestDetail' => $abTestDetail);
		$newMailid = $this->abTest_createFinalNewletter($typeAction, $dataForCopy);

		$queueClass = acymailing_get('class.queue');
		$time = time();
		$queueClass->queue($newMailid, $time);

		$mailidsTest = $abTestDetail['mailids'];
		$resUsersFromTest = acymailing_loadResultArray("SELECT subid FROM #__acymailing_userstats WHERE mailid IN (".$mailidsTest.")");
		if(!empty($resUsersFromTest)){
			acymailing_query("DELETE FROM #__acymailing_queue WHERE subid IN (".implode(',', $resUsersFromTest).") AND mailid=".$newMailid);
		}

		$abTestDetail['status'] = 'abTestFinalSend';
		$abTestDetail['newMail'] = $newMailid;
		$query = "UPDATE #__acymailing_mail SET abtesting=".acymailing_escapeDB(serialize($abTestDetail))." WHERE mailid IN (".$mailidsTest.")";
		acymailing_query($query);

		return $newMailid;
	}

	function abTest_createFinalNewletter($typeAction, $dataForCopy){

		if($typeAction == 'manual'){
			$mailid = $dataForCopy['mailid'];
			$newMailid = $this->copyOneNewsletter($mailid);
			return $newMailid;
		}

		$queryStat = 'SELECT mailid, openunique, clickunique, senthtml, senttext FROM #__acymailing_stats WHERE mailid IN ('.$dataForCopy['abTestDetail']['mailids'].')';
		$resStat = acymailing_loadObjectList($queryStat, 'mailid');
		$betterClick = -1;
		$betterOpen = -1;
		if(empty($resStat)) return 0;
		foreach($resStat as $mailid => $statsMail){
			if($statsMail->openunique > $betterOpen){
				$idOpen = $mailid;
				$betterOpen = $statsMail->openunique;
			}
			if($statsMail->clickunique > $betterClick){
				$idClick = $mailid;
				$betterClick = $statsMail->clickunique;
			}
		}
		if($dataForCopy['abTestDetail']['action'] == 'open'){
			$newMailid = $this->copyOneNewsletter($idOpen);
		}elseif($dataForCopy['abTestDetail']['action'] == 'click') $newMailid = $this->copyOneNewsletter($idClick);
		elseif($dataForCopy['abTestDetail']['action'] == 'mix'){
			$newSubject = acymailing_loadObjectList("SELECT subject, fromname, fromemail, replyname, replyemail FROM #__acymailing_mail WHERE mailid=".$idOpen);
			$newMailid = $this->copyOneNewsletter($idClick, $newSubject[0]);
		}
		return $newMailid;
	}

	function copyOneNewsletter($mailid, $subject = ''){
		$time = time();
		$query = 'INSERT INTO `#__acymailing_mail` (`subject`, `fromname`, `fromemail`, `replyname`, `replyemail`, `body`, `altbody`, `published`, `created`, `type`, `visible`, `userid`, `alias`, `attach`, `html`, `tempid`, `key`, `frequency`, `params`,`filter`,`metakey`,`metadesc`,`summary`,`thumb`,`senddate`)';
		if(empty($subject)){
			$query .= " SELECT `subject`, `fromname`, `fromemail`, `replyname`, `replyemail`";
		}else{
			$query .= " SELECT ".acymailing_escapeDB($subject->subject).", ".acymailing_escapeDB($subject->fromname).", ".acymailing_escapeDB($subject->fromemail).", ".acymailing_escapeDB($subject->replyname).", ".acymailing_escapeDB($subject->replyemail);
		}
		$query .= ", `body`, `altbody`, `published`, '.$time.', `type`, `visible`, `userid`, `alias`, `attach`, `html`, `tempid`, ".acymailing_escapeDB(md5(rand(1000, 999999))).', `frequency`, `params`,`filter`,`metakey`,`metadesc`,`summary`,`thumb`,'.time().' FROM `#__acymailing_mail` WHERE `mailid` = '.(int)$mailid;
		acymailing_query($query);
		$newMailid = acymailing_insertID();
		acymailing_query('INSERT IGNORE INTO `#__acymailing_listmail` (`listid`,`mailid`) SELECT `listid`,'.$newMailid.' FROM `#__acymailing_listmail` WHERE `mailid` = '.(int)$mailid);
		acymailing_query('INSERT IGNORE INTO `#__acymailing_tagmail` (`tagid`,`mailid`) SELECT `tagid`,'.$newMailid.' FROM `#__acymailing_tagmail` WHERE `mailid` = '.(int)$mailid);
		return $newMailid;
	}

	function updateAbTest_auto($idsToSend){
		if(empty($idsToSend)) return;
		$resDetails = acymailing_loadObjectList("SELECT mailid, abtesting FROM #__acymailing_mail WHERE mailid IN (".$idsToSend.") AND abtesting IS NOT NULL", 'mailid');
		if(empty($resDetails)) return;

		$oneAbTest = current($resDetails);
		$oneMailid = $oneAbTest->mailid;
		$abTestDetail = unserialize($oneAbTest->abtesting);
		$mailsArray = explode(',', $abTestDetail['mailids']);

		$query = "SELECT COUNT(*) FROM #__acymailing_queue WHERE mailid IN (".$abTestDetail['mailids'].")";
		$queueCheck = acymailing_loadResult($query);

		if(empty($queueCheck)){
			if(($abTestDetail['time'] + ($abTestDetail['delay'] * 24 * 3600)) < time()){
				$newMailid = $this->complete_abtest($abTestDetail['action'], $oneMailid);
				return $newMailid;
			}
		}
	}
}
