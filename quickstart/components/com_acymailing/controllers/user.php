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

class UserController extends acymailingController{

	function __construct($config = array()){
		parent::__construct($config);

		$this->registerDefaultTask('subscribe');
		$this->registerTask('optout', 'unsub');
		$this->registerTask('out', 'unsub');
	}

	function confirm(){
		if(acymailing_isRobot()) return false;

		$config = acymailing_config();


		$userClass = acymailing_get('class.subscriber');
		$userClass->geolocRight = true;

		$user = $userClass->identify();
		if(empty($user)) return false;

		$redirectUrl = $config->get('confirm_redirect');
		$listRedirection = '';
		$subscription = $userClass->getSubscriptionStatus($user->subid);
		foreach($subscription as $i => $onelist){
			if(!in_array($onelist->status, array(1, 2)) || acymailing_translation('REDIRECTION_CONFIRMATION_'.$i) == 'REDIRECTION_CONFIRMATION_'.$i) continue;
			$listRedirection = acymailing_translation('REDIRECTION_CONFIRMATION_'.$i);
			break;
		}

		if(!empty($listRedirection)) $redirectUrl = $listRedirection;

		if($config->get('confirmation_message', 1)){
			if($user->confirmed && strlen(acymailing_translation('ALREADY_CONFIRMED')) > 0){
				acymailing_enqueueMessage(acymailing_translation('ALREADY_CONFIRMED'));
			}elseif(!$user->confirmed && strlen(acymailing_translation('SUBSCRIPTION_CONFIRMED')) > 0) acymailing_enqueueMessage(acymailing_translation('SUBSCRIPTION_CONFIRMED'));
		}

		if(!$user->confirmed) $userClass->confirmSubscription($user->subid);

		$notifConfirm = $config->get('notification_confirm');
		if(!empty($notifConfirm)){
			$listsubClass = acymailing_get('class.listsub');
			$userHelper = acymailing_get('helper.user');
			$mailer = acymailing_get('helper.mailer');
			$mailer->autoAddUser = true;
			$mailer->checkConfirmField = false;
			$mailer->report = false;
			foreach($user as $field => $value) $mailer->addParam('user:'.$field, $value);
			$mailer->addParam('user:subscription', $listsubClass->getSubscriptionString($user->subid));
			$mailer->addParam('user:subscriptiondates', $listsubClass->getSubscriptionString($user->subid, true));
			$mailer->addParam('user:ip', $userHelper->getIP());
			if(!empty($userClass->geolocData)){
				foreach($userClass->geolocData as $map => $value){
					$mailer->addParam('geoloc:notif_'.$map, $value);
				}
			}
			$mailer->addParamInfo();
			$allUsers = explode(' ', trim(str_replace(array(';', ','), ' ', $notifConfirm)));
			foreach($allUsers as $oneUser){
				if(empty($oneUser)) continue;
				$mailer->sendOne('notification_confirm', $oneUser);
			}
		}

		if(!empty($redirectUrl)){
			$replace = array();
			foreach($user as $key => $val){
				$replace['{'.$key.'}'] = $val;
				$replace['{user:'.$key.'}'] = $val;
			}
			if($config->get('redirect_tags', 0) == 1) $redirectUrl = str_replace(array_keys($replace), $replace, $redirectUrl);
			acymailing_redirect($redirectUrl);
		}

		if('joomla' == 'wordpress') acymailing_redirect(acymailing_rootURI());

		acymailing_setVar('layout', 'confirm');
		return parent::display();
	}//endfct

	function modify(){
		$userClass = acymailing_get('class.subscriber');
		$userClass->geolocRight = true;

		$user = $userClass->identify(true);
		if(empty($user)) return $this->subscribe();

		acymailing_setVar('layout', 'modify');
		return parent::display();
	}

	function subscribe(){
		$userClass = acymailing_get('class.subscriber');
		$userClass->geolocRight = true;

		$currentUserid = acymailing_currentUserId();
		if(!empty($currentUserid) AND $userClass->identify(true)){
			return $this->modify();
		}

		$config = acymailing_config();
		$allowvisitor = $config->get('allow_visitor', 1);
		if(empty($allowvisitor)){
			acymailing_askLog(true, 'ONLY_LOGGED', 'message');
			return false;
		}

		acymailing_setVar('layout', 'modify');
		return parent::display();
	}

	function unsub(){
		$userClass = acymailing_get('class.subscriber');

		$user = $userClass->identify();
		if(empty($user)) return false;

		$statsClass = acymailing_get('class.stats');
		$statsClass->countReturn = false;
		$statsClass->saveStats();

		acymailing_setVar('layout', 'unsub');
		return parent::display();
	}

	function saveunsub(){
		acymailing_checkRobots();

		$subscriberClass = acymailing_get('class.subscriber');
		$subscriberClass->sendConf = false;

		$listsubClass = acymailing_get('class.listsub');
		$userHelper = acymailing_get('helper.user');
		$config = acymailing_config();


		$subscriber = new stdClass();
		$subscriber->subid = acymailing_getVar('int', 'subid');

		$user = $subscriberClass->identify();
		if(!$user || empty($subscriber->subid) || $user->subid != $subscriber->subid){
			echo "<script>alert('ERROR : You are not allowed to modify this user'); window.history.go(-1);</script>";
			exit;
		}

		$refusemails = acymailing_getVar('int', 'refuse');
		$unsuball = acymailing_getVar('int', 'unsuball');
		$mailid = acymailing_getVar('int', 'mailid');

		$oldUser = $subscriberClass->get($subscriber->subid);

		$survey = acymailing_getVar('array', 'survey', array(), '');
		$tagSurvey = '';
		$data = array();
		if(!empty($survey)){
			foreach($survey as $oneResult){
				if(empty($oneResult)) continue;
				$data[] = "REASON::".str_replace(array("\n", "\r"), array('<br />', ''), strip_tags($oneResult));
			}

			$tagSurvey = implode('<br />', $data);
		}

		$replace = array();
		$replace['REASON::'] = '<br />'.acymailing_translation('REASON').' : ';
		$reasons = unserialize($config->get('unsub_reasons'));
		foreach($reasons as $i => $oneReason){
			if(preg_match('#^[A-Z_]*$#', $oneReason)){
				$replace[$oneReason] = acymailing_translation($oneReason);
			}
		}

		$tagSurvey = str_replace(array_keys($replace), $replace, $tagSurvey);

		$historyClass = acymailing_get('class.acyhistory');
		$historyClass->insert($subscriber->subid, 'unsubscribed', $data, $mailid);

		$notifToSend = '';

		$incrementUnsub = false;
		if($refusemails OR $unsuball){

			if($refusemails){
				$subscriber->accept = 0;
				if($config->get('unsubscription_message', 1) && strlen(acymailing_translation('CONFIRM_UNSUB_FULL')) > 0) acymailing_enqueueMessage(acymailing_translation('CONFIRM_UNSUB_FULL'));
				$notifToSend = 'notification_refuse';
			}elseif($unsuball){
				$notifToSend = 'notification_unsuball';
			}


			$subscription = $subscriberClass->getSubscriptionStatus($subscriber->subid);
			$updatelists = array();
			foreach($subscription as $listid => $oneList){
				if($oneList->status != -1){
					$updatelists[-1][] = $listid;
				}
			}

			$listsubClass->sendNotif = false;

			if(!empty($updatelists)){
				$status = $listsubClass->updateSubscription($subscriber->subid, $updatelists);
				if($config->get('unsubscription_message', 1) && strlen(acymailing_translation('CONFIRM_UNSUB_ALL')) > 0) acymailing_enqueueMessage(acymailing_translation('CONFIRM_UNSUB_ALL'));
				$incrementUnsub = true;
			}else{
				if($config->get('unsubscription_message', 1) && strlen(acymailing_translation('ERROR_NOT_SUBSCRIBED')) > 0) acymailing_enqueueMessage(acymailing_translation('ERROR_NOT_SUBSCRIBED'));
			}

			$subscriber->confirmed = 0;
			$subscriberClass->save($subscriber);
		}else{

			$subscription = $subscriberClass->getSubscriptionStatus($subscriber->subid);

			$allLists = acymailing_loadObjectList('SELECT b.listid, b.name, b.type FROM '.acymailing_table('listmail').' as a JOIN '.acymailing_table('list').' as b on a.listid = b.listid WHERE a.mailid = '.$mailid);

			if(empty($allLists)){
				$allLists = acymailing_loadObjectList('SELECT b.listid, b.name, b.type FROM '.acymailing_table('list').' as b WHERE b.welmailid = '.$mailid.' OR b.unsubmailid = '.$mailid);
			}

			if(empty($allLists)){
				$allLists = acymailing_loadObjectList('SELECT b.listid, b.name, b.type FROM #__acymailing_listsub as a JOIN #__acymailing_list as b on a.listid = b.listid WHERE a.subid = '.$subscriber->subid);
			}


			$otherSubscriptionsBoxes = acymailing_getVar('array', 'unsubotherlists', array(), 'post');
			$otherSubscriptionsId = acymailing_getVar('array', 'unsubotherlistsid', array(), 'post');
			$othersubscriptionsToRemove = array();
			if(!empty($otherSubscriptionsBoxes)){
				$i = 0;
				foreach($otherSubscriptionsBoxes as $anotherSubscriptionsBox => $value){
					if($value == 1) $othersubscriptionsToRemove[] = intval($otherSubscriptionsId[$i]);
					$i++;
				}

				$otherSubscriptions = acymailing_loadObjectList('SELECT listid, name, type FROM #__acymailing_list WHERE listid IN ('.implode(',', $othersubscriptionsToRemove).')');

				foreach($otherSubscriptions as $anotherSubscription){
					array_push($allLists, $anotherSubscription);
				}
			}


			if(empty($allLists)){
				echo "<script>alert('ERROR : Could not get the list for the mailing $mailid'); window.history.go(-1);</script>";
				exit;
			}

			$campaignList = array();
			$unsubList = array();
			foreach($allLists as $oneList){
				if(isset($subscription[$oneList->listid]) AND $subscription[$oneList->listid]->status != -1){
					if($oneList->type == 'campaign'){
						$campaignList[] = $oneList->listid;
					}else{
						$unsubList[$oneList->listid] = $oneList;
					}
				}
			}

			if(!empty($campaignList)){
				$otherLists = acymailing_loadObjectList('SELECT b.listid, b.name, b.type FROM '.acymailing_table('listcampaign').' as a LEFT JOIN '.acymailing_table('list').' as b on a.listid = b.listid WHERE a.campaignid IN ('.implode(',', $campaignList).')');
				if(!empty($otherLists)){
					foreach($otherLists as $oneList){
						if(isset($subscription[$oneList->listid]) AND $subscription[$oneList->listid]->status != -1){
							$unsubList[$oneList->listid] = $oneList;
						}
					}
				}
			}

			if(!empty($unsubList)){
				$updatelists = array();
				$updatelists[-1] = array_keys($unsubList);
				$listsubClass->survey = $tagSurvey;
				$status = $listsubClass->updateSubscription($subscriber->subid, $updatelists);
				if($config->get('unsubscription_message', 1) && strlen(acymailing_translation('CONFIRM_UNSUB_CURRENT')) > 0) acymailing_enqueueMessage(acymailing_translation('CONFIRM_UNSUB_CURRENT'));
				$incrementUnsub = true;
			}else{
				if($config->get('unsubscription_message', 1) && strlen(acymailing_translation('ERROR_NOT_SUBSCRIBED_CURRENT')) > 0) acymailing_enqueueMessage(acymailing_translation('ERROR_NOT_SUBSCRIBED_CURRENT'));
			}
		}

		if($incrementUnsub){
			$alreadythere = acymailing_loadResult('SELECT subid FROM #__acymailing_history WHERE `action` = "unsubscribed" AND `subid` = '.intval($subscriber->subid).' AND `mailid` = '.intval($mailid).' LIMIT 1,1');

			if(empty($alreadythere)){
				acymailing_query('UPDATE '.acymailing_table('stats').' SET `unsub` = `unsub` +1 WHERE `mailid` = '.(int)$mailid);
			}
		}

		$classGeoloc = acymailing_get('class.geolocation');
		$classGeoloc->saveGeolocation('unsubscription', $subscriber->subid);

		if(!empty($notifToSend)){
			$notifyUsers = $config->get($notifToSend);

			if(!empty($notifyUsers)){
				$mailer = acymailing_get('helper.mailer');
				$mailer->autoAddUser = true;
				$mailer->checkConfirmField = false;
				$mailer->report = false;
				foreach($oldUser as $field => $value) $mailer->addParam('user:'.$field, $value);
				$mailer->addParam('user:subscription', $listsubClass->getSubscriptionString($oldUser->subid));
				$mailer->addParam('user:subscriptiondates', $listsubClass->getSubscriptionString($oldUser->subid, true));
				$mailer->addParam('user:ip', $userHelper->getIP());
				$mailer->addParam('survey', $tagSurvey);
				$mailer->addParamInfo();
				$allUsers = explode(' ', trim(str_replace(array(';', ','), ' ', $notifyUsers)));
				foreach($allUsers as $oneUser){
					if(empty($oneUser)) continue;
					$mailer->sendOne($notifToSend, $oneUser);
				}
			}
		}


		$redirectUnsub = $config->get('unsub_redirect');
		if(!empty($redirectUnsub)){
			$replace = array();
			foreach($oldUser as $key => $val){
				$replace['{'.$key.'}'] = $val;
				$replace['{user:'.$key.'}'] = $val;
			}
			if($config->get('redirect_tags', 0) == 1) $redirectUnsub = str_replace(array_keys($replace), $replace, $redirectUnsub);
			acymailing_redirect($redirectUnsub);
			return;
		}elseif('joomla' == 'wordpress'){
			acymailing_redirect(acymailing_rootURI());
			return;
		}

		acymailing_setVar('layout', 'saveunsub');
		return parent::display();
	}

	function savechanges(){
		acymailing_checkToken();
		acymailing_checkRobots();

		$config = acymailing_config();
		$subscriberClass = acymailing_get('class.subscriber');
		$subscriberClass->geolocRight = true;
		$subscriberClass->extendedEmailVerif = true;


		$status = $subscriberClass->saveForm();
		$subscriberClass->sendNotification();
		if($status){
			if($subscriberClass->confirmationSent){
				if($config->get('subscription_message', 1) && strlen(acymailing_translation('CONFIRMATION_SENT')) > 0) acymailing_enqueueMessage(acymailing_translation('CONFIRMATION_SENT'), 'message');
				$redirectlink = $config->get('sub_redirect');
			}elseif($subscriberClass->newUser){
				if($config->get('subscription_message', 1) && strlen(acymailing_translation('SUBSCRIPTION_OK')) > 0) acymailing_enqueueMessage(acymailing_translation('SUBSCRIPTION_OK'), 'message');
				$redirectlink = $config->get('sub_redirect');
			}else{
				if(strlen(acymailing_translation('SUBSCRIPTION_UPDATE_OK')) > 0) acymailing_enqueueMessage(acymailing_translation('SUBSCRIPTION_UPDATED_OK'), 'message');
				$redirectlink = $config->get('modif_redirect');
			}
		}elseif($subscriberClass->requireId){
			if(strlen(acymailing_translation('IDENTIFICATION_SENT')) > 0) acymailing_enqueueMessage(acymailing_translation('IDENTIFICATION_SENT'), 'notice');
		}else{
			if(strlen(acymailing_translation('ERROR_SAVING')) > 0) acymailing_enqueueMessage(acymailing_translation('ERROR_SAVING'), 'error');
		}

		if(!empty($redirectlink)){
			if($config->get('redirect_tags', false)) {
				$user = $subscriberClass->identify(true);
				if(!empty($user->subid)) {
					$replace = array();
					foreach ($user as $key => $val) {
						if(!is_array($val) && !is_object($val)) $replace['{' . $key . '}'] = $val;
					}
					$redirectlink = str_replace(array_keys($replace), $replace, $redirectlink);
				}
			}

			acymailing_redirect($redirectlink);
			return;
		}

		if($subscriberClass->identify(true)) return $this->modify();
		return $this->subscribe();
	}

	function exportdata(){
		acymailing_checkToken();

		$subscriberClass = acymailing_get('class.subscriber');
		$subscriber = $subscriberClass->identify(true);

		if(empty($subscriber->subid)) acymailing_redirect(acymailing_rootURI());

		$userHelper = acymailing_get('helper.user');
		$userHelper->exportdata($subscriber->subid);
	}

	function delete(){
		acymailing_checkToken();

		$subscriberClass = acymailing_get('class.subscriber');
		$subscriber = $subscriberClass->identify(true);

		if(empty($subscriber->subid)) acymailing_redirect(acymailing_rootURI());

		if($subscriberClass->delete($subscriber->subid)){
			acymailing_enqueueMessage(acymailing_translation('ACY_DATA_DELETED'), 'success');
		}else{
			acymailing_enqueueMessage(acymailing_translation('ACY_ERROR_DELETE_DATA'), 'error');
		}
		acymailing_redirect(acymailing_rootURI());
	}
}
