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

class subscriberClass extends acymailingClass{

	var $tables = array('listsub', 'userstats', 'queue', 'history', 'subscriber');
	var $pkey = 'subid';
	var $namekey = 'email';
	var $restrictedFields = array('subid', 'key', 'confirmed', 'enabled', 'ip', 'userid', 'created');
	var $errors = array();
	var $checkVisitor = true;
	var $checkAccess = true;
	var $sendConf = true;
	var $forceConf = false;
	var $requireId = false;
	var $newUser = null;
	var $confirmationSent = false;
	var $sendNotif = true;
	var $sendWelcome = true;
	var $recordHistory = false;
	var $allowModif = false;
	var $extendedEmailVerif = false;

	var $userForNotification;
	var $triggerFilterBE = false;

	var $geolocRight = false;
	var $geolocData = null;


	function save($subscriber){
		$config = acymailing_config();
		acymailing_importPlugin('acymailing');

		if(isset($subscriber->email)){
			$subscriber->email = strtolower($subscriber->email);
			$userHelper = acymailing_get('helper.user');
			if(!$userHelper->validEmail($subscriber->email, $this->extendedEmailVerif)){
				echo "<script>alert('".acymailing_translation('VALID_EMAIL', true)."'); window.history.go(-1);</script>";
				exit;
			}
		}
		if(empty($subscriber->subid)){
			$currentUserid = acymailing_currentUserId();
			$currentEmail = acymailing_currentUserEmail();
			if($this->checkVisitor && !acymailing_isAdmin() && (int)$config->get('allow_visitor', 1) != 1 && (empty($currentUserid) OR strtolower($currentEmail) != $subscriber->email)){
				echo "<script> alert('".acymailing_translation('ONLY_LOGGED', true)."'); window.history.go(-1);</script>\n";
				exit;
			}
			if(empty($subscriber->email)) return false;
			$subscriber->subid = $this->subid($subscriber->email);
		}

		if(empty($subscriber->userid) && !empty($subscriber->email)){
			$cmsId = acymailing_currentUserId($subscriber->email);
			if(!empty($cmsId)){
				$subscriber->userid = $cmsId;
			}
		}

		if(empty($subscriber->subid)){
			if(empty($subscriber->created)) $subscriber->created = time();
			if(empty($subscriber->ip) && $config->get('anonymous_tracking', 0) == 0){       
				$ipClass = acymailing_get('helper.user');
				$subscriber->ip = $ipClass->getIP();
			}

			$source = acymailing_getVar('cmd', 'acy_source');
			if(empty($subscriber->source) && !empty($source)) $subscriber->source = $source;

			if(empty($subscriber->name) && $config->get('generate_name', 1)) $subscriber->name = ucwords(trim(str_replace(array('.', '_', ')', ',', '(', '-', 1, 2, 3, 4, 5, 6, 7, 8, 9, 0), ' ', substr($subscriber->email, 0, strpos($subscriber->email, '@')))));
			$subscriber->key = acymailing_generateKey(14);
			acymailing_trigger('onAcyBeforeUserCreate', array(&$subscriber));
			$status = acymailing_insertObject(acymailing_table('subscriber'), $subscriber);
		}else{
			if(count((array)$subscriber) > 1){
				acymailing_trigger('onAcyBeforeUserModify', array(&$subscriber));
				$status = acymailing_updateObject(acymailing_table('subscriber'), $subscriber, 'subid');
			}else{
				$status = true;
			}
		}

		if(!$status) return false;

		$subid = empty($subscriber->subid) ? $status : $subscriber->subid;

		if($this->triggerFilterBE || !acymailing_isAdmin()){
			$filterClass = acymailing_get('class.filter');
			$filterClass->subid = $subid;
			$filterClass->trigger((empty($subscriber->subid) ? 'subcreate' : 'subchange'));
		}

		$classGeoloc = acymailing_get('class.geolocation');
		if(empty($subscriber->subid)){
			$subscriber->subid = $subid;

			if($this->geolocRight){
				$this->geolocData = $classGeoloc->saveGeolocation('creation', $subscriber->subid);
			}

			$this->userForNotification = $subscriber;
			$resultsTrigger = acymailing_trigger('onAcyUserCreate', array(&$subscriber));
			$this->recordHistory = true;
			$action = 'created';
		}else{
			if($this->geolocRight){
				$this->geolocData = $classGeoloc->saveGeolocation('modify', $subscriber->subid);
			}

			$resultsTrigger = acymailing_trigger('onAcyUserModify', array($subscriber));
			$action = 'modified';
		}

		if($this->recordHistory){
			$historyClass = acymailing_get('class.acyhistory');
			$historyClass->insert($subscriber->subid, $action);
			$this->recordHistory = false;
		}

		if($this->forceConf || (!acymailing_isAdmin() AND $this->sendConf)){
			$this->sendConf($subid);
		}

		return $subid;
	}

	function sendNotification(){
		if(empty($this->userForNotification)) return;
		$subscriber = $this->userForNotification;
		unset($this->userForNotification);

		$config = acymailing_config();
		$notifyUsers = $config->get('notification_created');
		if(acymailing_isAdmin() || empty($notifyUsers)) return;

		$mailer = acymailing_get('helper.mailer');
		$mailer->report = false;
		$mailer->autoAddUser = true;
		$mailer->checkConfirmField = false;
		foreach($subscriber as $map => $value){
			$mailer->addParam('user:'.$map, $value);
		}

		$mailer->addParam('action', acymailing_translation('ACY_NEW'));

		if(!empty($subscriber->subid)){
			$listSubClass = acymailing_get('class.listsub');
			$mailer->addParam('user:subscription', $listSubClass->getSubscriptionString($subscriber->subid));
			$mailer->addParam('user:subscriptiondates', $listSubClass->getSubscriptionString($subscriber->subid, true));
		}

		if(!empty($this->geolocData)){
			foreach($this->geolocData as $map => $value){
				$mailer->addParam('geoloc:notif_'.$map, $value);
			}
		}

		$mailer->addParamInfo();

		$allUsers = explode(' ', trim(str_replace(array(';', ','), ' ', $notifyUsers)));
		foreach($allUsers as $oneUser){
			if(empty($oneUser)) continue;
			$mailer->sendOne('notification_created', $oneUser);
		}
	}

	function sendConf($subid){
		if($this->confirmationSent) return false;

		$myuser = $this->get($subid);
		$config = acymailing_config();
		if(!empty($myuser->confirmed)) return false;

		if(!$config->get('require_confirmation', false)) return false;

		$mailClass = acymailing_get('helper.mailer');
		$mailClass->checkConfirmField = false;
		$mailClass->checkEnabled = false;
		$mailClass->checkAccept = false;
		$mailClass->report = $config->get('confirm_message', 0);
		$alias = "confirmation";
		if(acymailing_getVar('cmd', 'acy_source')){
			$sourceparams = explode('_', acymailing_getVar('cmd', 'acy_source'));
			$alias = acymailing_loadResult('SELECT alias FROM #__acymailing_mail WHERE published = 1 AND alias IN ("confirmation",'.acymailing_escapeDB('confirmation-'.$sourceparams[0]).','.acymailing_escapeDB('confirmation-'.$sourceparams[0].'-'.@$sourceparams[1]).','.acymailing_escapeDB('confirmation-'.$sourceparams[0].'-'.@$sourceparams[1].'-'.@$sourceparams[2]).') ORDER BY alias DESC');
		}

		$this->confirmationSentSuccess = $mailClass->sendOne($alias, $myuser);
		$this->confirmationSentError = $mailClass->reportMessage;
		$this->confirmationSent = true;
		return true;
	}

	function subid($email){
		if(is_numeric($email)){
			$cond = ' userid = '.$email;
		}else{
			if(!empty($email)) $email = acymailing_punycode($email);
			$cond = 'email = '.acymailing_escapeDB(trim($email));
		}
		return acymailing_loadResult('SELECT subid FROM '.acymailing_table('subscriber').' WHERE '.$cond);
	}


	function get($subid, $default = null){
		if(is_numeric($subid)){
			$column = 'subid';
		}else{
			$column = 'email';
			if(!empty($subid)) $subid = acymailing_punycode($subid);
		}
		return acymailing_loadObject('SELECT * FROM '.acymailing_table('subscriber').' WHERE '.$column.' = '.acymailing_escapeDB(trim($subid)).' LIMIT 1');
	}

	function getFull($subid){
		if(is_numeric($subid)){
			$column = 'subid';
		}else{
			$column = 'email';
			if(!empty($subid)) $subid = acymailing_punycode($subid);
		}
		return acymailing_loadObject('SELECT b.'.$this->cmsUserVars->username.' AS username, a.* FROM '.acymailing_table('subscriber').' as a LEFT JOIN '.acymailing_table($this->cmsUserVars->table, false).' as b on a.userid = b.'.$this->cmsUserVars->id.' WHERE '.$column.' = '.acymailing_escapeDB(trim($subid)).' LIMIT 1');
	}

	function getFrontendSubscription($subid, $index = ''){
		$subscription = $this->getSubscription($subid, $index);
		$copyAllLists = $subscription;
		$currentUserid = acymailing_currentUserId();
		foreach($copyAllLists as $id => $oneList){
			if(!$oneList->published OR empty($currentUserid)){
				unset($subscription[$id]);
				continue;
			}
			if($currentUserid == (int)$oneList->userid) continue;
			if(!acymailing_isAllowed($oneList->access_manage)){
				unset($subscription[$id]);
				continue;
			}
		}

		return $subscription;
	}

	function getSubscription($subid, $index = ''){
		$query = 'SELECT a.*, b.* FROM '.acymailing_table('list').' as b ';
		$query .= 'LEFT JOIN '.acymailing_table('listsub').' as a on a.listid = b.listid AND a.subid = '.intval($subid);
		$query .= ' WHERE b.type = \'list\'';
		$query .= ' ORDER BY b.ordering ASC';
		return acymailing_loadObjectList($query, $index);
	}

	function getSubscriptionStatus($subid, $listids = null){
		$query = 'SELECT status,listid FROM '.acymailing_table('listsub').' WHERE subid = '.intval($subid);
		if(!empty($listids)){
			acymailing_arrayToInteger($listids);
			$query .= ' AND listid IN ('.implode(',', $listids).')';
		}
		return acymailing_loadObjectList($query, 'listid');
	}

	function checkFields(&$data, &$subscriber){

		foreach($data as $column => $value){
			$column = trim(strtolower($column));
			if($this->allowModif || !in_array($column, $this->restrictedFields)){
				acymailing_secureField($column);
				if(is_array($value)){
					if(isset($value['day']) || isset($value['month']) || isset($value['year'])){
						$value = (empty($value['year']) ? '0000' : intval($value['year'])).'-'.(empty($value['month']) ? '00' : $value['month']).'-'.(empty($value['day']) ? '00' : $value['day']);
					}else{
						$value = implode(',', $value);
					}
				}

				$subscriber->$column = trim(strip_tags($value));

				if(!is_numeric($subscriber->$column)){
					if(function_exists('mb_detect_encoding') && mb_detect_encoding($subscriber->$column, 'UTF-8', true) != 'UTF-8'){
						$subscriber->$column = utf8_encode($subscriber->$column);
					}elseif(!function_exists('mb_detect_encoding') && !preg_match('%^(?:[\x09\x0A\x0D\x20-\x7E]|[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2})*$%xs', $subscriber->$column)){
						$subscriber->$column = utf8_encode($subscriber->$column);
					}
				}
			}
		}

		if(!acymailing_level(3) || empty($_FILES)) return;

		
		$config = acymailing_config();
		$uploadFolder = trim(acymailing_cleanPath(html_entity_decode(acymailing_getFilesFolder())), DS.' ').DS;
		$uploadPath = acymailing_cleanPath(ACYMAILING_ROOT.$uploadFolder.'userfiles'.DS);
		acymailing_createDir(acymailing_cleanPath(ACYMAILING_ROOT.$uploadFolder), true);
		acymailing_createDir($uploadPath, true);


		foreach($_FILES as $typename => $type){
			$type2 = isset($type['name']['subscriber']) ? $type['name']['subscriber'] : $type['name'];
			if(empty($type2) || !is_array($type2)) continue;
			foreach($type2 as $fieldname => $filename){
				if(empty($filename)) continue;
				acymailing_secureField($fieldname);
				$attachment = new stdClass();
				$filename = acymailing_makeSafeFile(strtolower(strip_tags($filename)));
				$attachment->filename = time().rand(1, 999).'_'.$filename;
				while(file_exists($uploadPath.$attachment->filename)){
					$attachment->filename = time().rand(1, 999).'_'.$filename;
				}

				if(!preg_match('#\.('.str_replace(array(',', '.'), array('|', '\.'), $config->get('allowedfiles')).')$#Ui', $attachment->filename, $extension) || preg_match('#\.(php.?|.?htm.?|pl|py|jsp|asp|sh|cgi)#Ui', $attachment->filename)){
					echo "<script>alert('".acymailing_translation_sprintf('ACCEPTED_TYPE', substr($attachment->filename, strrpos($attachment->filename, '.') + 1), $config->get('allowedfiles'))."');window.history.go(-1);</script>";
					exit;
				}
				$attachment->filename = str_replace(array('.', ' '), '_', substr($attachment->filename, 0, strpos($attachment->filename, $extension[0]))).$extension[0];

				$tmpFile = isset($type['name']['subscriber']) ? $_FILES[$typename]['tmp_name']['subscriber'][$fieldname] : $_FILES[$typename]['tmp_name'][$fieldname];
				if(!acymailing_uploadFile($tmpFile, $uploadPath.$attachment->filename)){
					echo "<script>alert('".acymailing_translation_sprintf('FAIL_UPLOAD', '<b><i>'.$tmpFile.'</i></b>', '<b><i>'.$uploadPath.$attachment->filename.'</i></b>')."');window.history.go(-1);</script>";
					exit;
				}

				$subscriber->$fieldname = $attachment->filename;
			}
		}
	}

	function saveForm(){
		$config = acymailing_config();
		$allowUserModifications = (bool)($config->get('allow_modif', 'data') == 'all') || $this->allowModif;
		$allowSubscriptionModifications = (bool)($config->get('allow_modif', 'data') != 'none') || $this->allowModif;

		$subscriber = new stdClass();
		$subscriber->subid = acymailing_getCID('subid');

		if(!$this->allowModif && !empty($subscriber->subid)){
			$user = $this->identify();
			$allowUserModifications = true;
			$allowSubscriptionModifications = true;
			if($user->subid != $subscriber->subid){
				die('You are not allowed to modify this user');
			}
		}

		$formData = acymailing_getVar('array', 'data', array(), '');
		if(!empty($formData['subscriber'])){
			$this->checkFields($formData['subscriber'], $subscriber);
		}

		if(!empty($subscriber->email)) $subscriber->email = acymailing_punycode($subscriber->email);

		if(empty($subscriber->subid)){
			if(empty($subscriber->email)){
				echo "<script>alert('".acymailing_translation('VALID_EMAIL', true)."'); window.history.go(-1);</script>";
				exit;
			}
		}

		if(!empty($subscriber->email)){
			$existSubscriber = acymailing_loadObject('SELECT * FROM #__acymailing_subscriber WHERE email = '.acymailing_escapeDB($subscriber->email).' AND subid != '.intval(@$subscriber->subid));
			if(!empty($existSubscriber->subid)){
				$overwritenow = true;
				if($this->allowModif){
					if(acymailing_isAdmin()){
						$overwritenow = false;
					}else{
						$listClass = acymailing_get('class.list');
						$allowedLists = $listClass->getFrontendLists('listid');
						if(empty($allowedLists)){
							$this->errors[] = "Not sure how you were able to edit this user if you don't own any list...";
							return false;
						}
						$allowedlistid = acymailing_loadResult('SELECT listid FROM #__acymailing_listsub WHERE subid = '.intval($existSubscriber->subid).' AND listid IN ('.implode(',', array_keys($allowedLists)).')');
						if(!empty($allowedlistid)) $overwritenow = false;
					}
				}

				if($overwritenow){
					$subscriber->subid = $existSubscriber->subid;
					$subscriber->confirmed = $existSubscriber->confirmed;
				}else{
					$this->errors[] = acymailing_translation_sprintf('USER_ALREADY_EXISTS', $subscriber->email);
					$this->errors[] = '<a href="'.acymailing_completeLink((acymailing_isAdmin() ? 'subscriber' : 'frontsubscriber&listid='.$allowedlistid).'&task=edit&subid='.$existSubscriber->subid).'" >'.acymailing_translation('CLICK_EDIT_USER').'</a>';
					return false;
				}
			}
		}

		if(!$this->allowModif && !empty($subscriber->subid) && !empty($subscriber->email)){
			$existSubscriber = $this->get($subscriber->subid);
			if(trim(strtolower($subscriber->email)) != strtolower($existSubscriber->email)){
				$subscriber->confirmed = 0;
			}
		}

		$this->recordHistory = true;
		$this->newUser = empty($subscriber->subid) ? true : false;
		if(empty($subscriber->subid) OR $allowUserModifications){
			if(isset($subscriber->html) && $subscriber->html != 1) $subscriber->html = 0;
			if(isset($subscriber->confirmed) && $subscriber->confirmed != 1) $subscriber->confirmed = 0;
			if(isset($subscriber->enabled) && $subscriber->enabled != 1) $subscriber->enabled = 0;
			if(isset($subscriber->accept) && $subscriber->accept != 1) $subscriber->accept = 0;
			$subid = $this->save($subscriber);
			$allowSubscriptionModifications = true;
		}else{
			$subid = $subscriber->subid;
			if(isset($subscriber->confirmed) && empty($subscriber->confirmed)) $this->sendConf($subid);
		}
		acymailing_setVar('subid', $subid);

		if(empty($subid)) return false;

		if(!$this->allowModif && isset($subscriber->accept) && $subscriber->accept == 0) $formData['masterunsub'] = 1;

		if(!acymailing_isAdmin()){
			$hiddenlistsString = acymailing_getVar('string', 'hiddenlists', '');
			if(!empty($hiddenlistsString)){
				$hiddenlists = explode(',', $hiddenlistsString);
				acymailing_arrayToInteger($hiddenlists);
				foreach($hiddenlists as $oneListId){
					$formData['listsub'][$oneListId] = array('status' => 1);
				}
			}
		}

		if(empty($formData['listsub'])) return true;

		if(!$allowSubscriptionModifications){
			$mailClass = acymailing_get('helper.mailer');
			$mailClass->checkConfirmField = false;
			$mailClass->checkEnabled = false;
			$mailClass->report = false;
			$mailClass->sendOne('modif', $subid);
			$this->requireId = true;
			return false;
		}
		$subscriptionSaved = $this->saveSubscription($subid, $formData['listsub']);

		$notifContact = $config->get('notification_contact_menu');
		if(!empty($notifContact) && !acymailing_isAdmin()){
			$userHelper = acymailing_get('helper.user');
			$mailer = acymailing_get('helper.mailer');
			$listsubClass = acymailing_get('class.listsub');
			$mailer->autoAddUser = true;
			$mailer->checkConfirmField = false;
			$mailer->report = false;
			foreach($subscriber as $field => $value) $mailer->addParam('user:'.$field, $value);
			if(empty($subscriber->email)){
				$myUser = $this->get($subscriber->subid);
				$mailer->addParam('user:name', $myUser->name);
				$mailer->addParam('user:email', $myUser->email);
			}
			$mailer->addParam('user:subscription', $listsubClass->getSubscriptionString($subscriber->subid));
			$mailer->addParam('user:subscriptiondates', $listsubClass->getSubscriptionString($subscriber->subid, true));
			$mailer->addParam('user:ip', $userHelper->getIP());
			if(!empty($this->geolocData)){
				foreach($this->geolocData as $map => $value){
					$mailer->addParam('geoloc:notif_'.$map, $value);
				}
			}
			$mailer->addParamInfo();
			$allUsers = explode(' ', trim(str_replace(array(';', ','), ' ', $notifContact)));
			foreach($allUsers as $oneUser){
				if(empty($oneUser)) continue;
				$mailer->sendOne('notification_contact_menu', $oneUser);
			}
		}
		return $subscriptionSaved;
	}

	function saveSubscription($subid, $formlists){

		$addlists = array();
		$removelists = array();
		$updatelists = array();

		$listids = array_keys($formlists);
		$currentSubscription = $this->getSubscriptionStatus($subid, $listids);

		foreach($formlists as $listid => $oneList){
			if(empty($oneList['status'])){
				if(isset($currentSubscription[$listid])) $removelists[] = $listid;
				continue;
			}

			if($this->confirmationSent && $oneList['status'] == 1) $oneList['status'] = 2;

			if(!isset($currentSubscription[$listid])){
				if($oneList['status'] != -1) $addlists[$oneList['status']][] = $listid;

				continue;
			}

			if($currentSubscription[$listid]->status == $oneList['status']) continue;

			if($currentSubscription[$listid]->status == 1 && $oneList['status'] == 2 && !$this->allowModif) continue;

			$updatelists[$oneList['status']][] = $listid;
		}

		$listsubClass = acymailing_get('class.listsub');
		$listsubClass->checkAccess = $this->checkAccess;
		$status = true;
		if(!empty($updatelists)) $status = $listsubClass->updateSubscription($subid, $updatelists) && $status;
		if(!empty($removelists)) $status = $listsubClass->removeSubscription($subid, $removelists) && $status;
		if(!empty($addlists)) $status = $listsubClass->addSubscription($subid, $addlists) && $status;

		return $status;
	}

	function confirmSubscription($subid){

		$historyClass = acymailing_get('class.acyhistory');
		$historyClass->insert($subid, 'confirmed');

		$userHelper = acymailing_get('helper.user');                                 
		$config = acymailing_config();
		if($config->get('anonymous_tracking', 0) == 0) {
			$ip = $userHelper->getIP();
		}else{
			$ip = '';
		}

		$res = acymailing_query('UPDATE '.acymailing_table('subscriber').' SET `confirmed` = 1, `confirmed_date` = '.time().', `confirmed_ip` = '.acymailing_escapeDB($ip).' WHERE `subid` = '.intval($subid).' LIMIT 1');
		if($res === false){
			acymailing_display('Please contact the admin of this website with the error message :<br />'.substr(strip_tags(acymailing_getDBError()), 0, 200).'...', 'error');
			exit;
		}

		$listids = acymailing_loadResultArray('SELECT `listid` FROM '.acymailing_table('listsub').' WHERE `status` = 2 AND `subid` = '.intval($subid));

		acymailing_importPlugin('acymailing');
		acymailing_trigger('onAcyConfirmUser', array($subid));

		if($this->geolocRight){
			$classGeoloc = acymailing_get('class.geolocation');
			$this->geolocData = $classGeoloc->saveGeolocation('confirm', $subid);
		}

		if(empty($listids)) return;

		$listsubClass = acymailing_get('class.listsub');
		$listsubClass->sendConf = $this->sendWelcome;
		$listsubClass->forceConf = $this->forceConf;
		$listsubClass->sendNotif = $this->sendNotif;
		$listsubClass->updateSubscription($subid, array(1 => $listids));
	}

	function identify($onlyvalue = false){
		$subid = acymailing_getVar('int', "subid", 0);
		$key = acymailing_getVar('string', "key", '');

		if(empty($subid) OR empty($key)){
			$currentUserid = acymailing_currentUserId();
			if(!empty($currentUserid)){
				$userIdentified = $this->get(acymailing_currentUserEmail());
				return $userIdentified;
			}
			if(!$onlyvalue){
				acymailing_enqueueMessage(acymailing_translation('ASK_LOG'), 'error');
			}
			return false;
		}

		$userIdentified = acymailing_loadObject('SELECT * FROM '.acymailing_table('subscriber').' WHERE `subid` = '.acymailing_escapeDB($subid).' AND `key` = '.acymailing_escapeDB($key).' LIMIT 1');
		if(!empty($userIdentified->email)) $userIdentified->email = acymailing_punycode($userIdentified->email, 'emailToUTF8');

		if(empty($userIdentified)){
			if(!$onlyvalue) acymailing_enqueueMessage(acymailing_translation('INVALID_KEY'), 'error');
			return false;
		}

		return $userIdentified;
	}

	function delete($elements){
		$fileFields = acymailing_loadResultArray('SELECT namekey FROM #__acymailing_fields WHERE type = "file" OR type = "gravatar"');
		if(!empty($fileFields)){
			if(!is_array($elements)){
				$elements = array($elements);
			}

			if(empty($elements)) return 0;
			$column = is_numeric(reset($elements)) ? $this->pkey : $this->namekey;

			$selection = array();
			foreach($elements as $key => $val){
				$selection[$key] = acymailing_escapeDB($val);
			}

			$uploadFolder = trim(acymailing_cleanPath(html_entity_decode(acymailing_getFilesFolder())), DS.' ').DS;

			$files = acymailing_loadObjectList('SELECT '.implode(', ', $fileFields).' FROM #__acymailing_subscriber WHERE '.acymailing_secureField($column).' IN ('.implode(',', $selection).')');
			foreach($files as $oneUserFiles){
				foreach($fileFields as $oneField){
					if(!empty($oneUserFiles->$oneField) && file_exists(ACYMAILING_ROOT.$uploadFolder.'userfiles'.DS.$oneUserFiles->$oneField)){
						acymailing_deleteFile(ACYMAILING_ROOT.$uploadFolder.'userfiles'.DS.$oneUserFiles->$oneField);
					}
				}
			}
		}

		return parent::delete($elements);
	}
}
