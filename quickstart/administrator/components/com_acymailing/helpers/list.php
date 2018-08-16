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

class acylistHelper{

	var $sendNotif = true;
	var $sendConf = true;
	var $forceConf = false;
	var $survey = '';
	var $campaigndelay = 0;
	var $skipedfollowups = 0;

	function subscribe($subid,$listids){


		acymailing_importPlugin('acymailing');
		$resultsTrigger = acymailing_trigger('onAcySubscribe', array($subid, $listids));

	}//endfct

	function unsubscribe($subid,$listids){

		if(acymailing_level(3)){
			$campaignClass = acymailing_get('helper.campaign');
			$campaignClass->stop($subid,$listids);
		}

		$config = acymailing_config();
		static $alreadySent = false;
		if($this->sendNotif AND !$alreadySent AND $config->get('notification_unsub') AND !acymailing_isAdmin()){
			$alreadySent = true;
			$mailer = acymailing_get('helper.mailer');
			$mailer->report = false;
			$mailer->autoAddUser = true;
			$mailer->checkConfirmField = false;
			$userClass = acymailing_get('class.subscriber');
			$subscriber = $userClass->get($subid);
			$ipClass = acymailing_get('helper.user');
			$mailer->addParam('survey',$this->survey);
			$listSubClass= acymailing_get('class.listsub');
			$mailer->addParam('user:subscription',$listSubClass->getSubscriptionString($subscriber->subid));
			$mailer->addParam('user:subscriptiondates',$listSubClass->getSubscriptionString($subscriber->subid, true));
			$mailer->addParamInfo();
			$subscriber->ip = $ipClass->getIP();
			foreach($subscriber as $fieldname => $value) $mailer->addParam('user:'.$fieldname,$value);
			$allUsers = explode(',',$config->get('notification_unsub'));
			foreach($allUsers as $oneUser){
				$mailer->sendOne('notification_unsub',$oneUser);
			}
		}

		if($this->forceConf || ($this->sendConf AND !acymailing_isAdmin())){
			$messages = acymailing_loadResultArray('SELECT DISTINCT `unsubmailid` FROM '.acymailing_table('list').' WHERE `listid` IN ('.implode(',',$listids).') AND `published` = 1  AND `unsubmailid` > 0');

			if(!empty($messages)){
				$config = acymailing_config();
				$mailHelper = acymailing_get('helper.mailer');
				$mailHelper->report = $config->get('unsub_message',true);
				$mailHelper->checkAccept = false;
				foreach($messages as $mailid){
					$mailHelper->trackEmail = true;
					$mailHelper->sendOne($mailid,$subid);
				}
			}
		}//end only frontend

		acymailing_query('DELETE FROM '.acymailing_table('queue').' WHERE `subid` = '.(int) $subid.' AND `mailid` IN (SELECT `mailid` FROM '.acymailing_table('listmail').' WHERE `listid` IN ('.implode(',',$listids).'))');

		acymailing_importPlugin('acymailing');
		$resultsTrigger = acymailing_trigger('onAcyUnsubscribe', array($subid, $listids));
	}
}//endclass
