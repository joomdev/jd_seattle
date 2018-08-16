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

class acyqueueHelper{

	var $mailid = 0;
	var $report = true;
	var $send_limit = 0;
	var $finish = false;
	var $error = false;
	var $nbprocess = 0;
	var $start = 0;
	var $stoptime = 0;
	var $successSend = 0;
	var $errorSend = 0;
	var $consecutiveError = 0;
	var $messages = array();
	var $pause = 0;
	var $config;
	var $listsubClass;
	var $subClass;
	var $mod_security2 = false;
	var $obend = 0;
	var $emailtypes = array();

	public function __construct(){
		$this->config = acymailing_config();
		$this->subClass = acymailing_get('class.subscriber');
		$this->listsubClass = acymailing_get('class.listsub');
		$this->listsubClass->checkAccess = false;
		$this->listsubClass->sendNotif = false;
		$this->listsubClass->sendConf = false;

		$this->send_limit = (int)$this->config->get('queue_nbmail', 40);

		acymailing_increasePerf();

		@ini_set('default_socket_timeout', 10);

		@ignore_user_abort(true);

		$timelimit = intval(ini_get('max_execution_time'));
		if(empty($timelimit)) $timelimit = 600;

		$calculatedTimeout = $this->config->get('max_execution_time');
		if(!empty($calculatedTimeout)) $timelimit = $calculatedTimeout;

		if(!empty($timelimit)){
			$this->stoptime = time() + $timelimit - 4;
		}
	}

	public function process(){

		$queueClass = acymailing_get('class.queue');
		$queueClass->emailtypes = $this->emailtypes;
		$queueElements = $queueClass->getReady($this->send_limit, $this->mailid);

		if(empty($queueElements)){
			$this->finish = true;
			if($this->report){
				acymailing_display('<a href="'.acymailing_completeLink('queue').'" target="_blank">'.acymailing_translation('NO_PROCESS').'</a>', 'warning');
			}
			return true;
		}

		if($this->report){
			if(function_exists('apache_get_modules')){
				$modules = apache_get_modules();
				$this->mod_security2 = in_array('mod_security2', $modules);
			}

			@ini_set('output_buffering', 'off');
			@ini_set('zlib.output_compression', 0);

			if(!headers_sent()){
				while(ob_get_level() > 0 && $this->obend++ < 3){
					@ob_end_flush();
				}
			}

			$disp = '<html><head><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />';
			$disp .= '<title>'.acymailing_translation('SEND_PROCESS').'</title>';
			$disp .= '<style>body{font-size:12px;font-family: Arial,Helvetica,sans-serif;}</style></head><body>';
			$disp .= '<div style="margin-bottom: 18px;padding: 8px !important; background-color: #fcf8e3; border: 1px solid #fbeed5; border-radius: 4px;"><p style="margin:0;">'.acymailing_translation('ACY_DONT_CLOSE').'</p></div>';
			$disp .= "<div style='display: inline;background-color : white;border : 1px solid grey; padding : 3px;font-size:14px'>";
			$disp .= "<span id='divpauseinfo' style='padding:10px;margin:5px;font-size:16px;font-weight:bold;display:none;background-color:black;color:white;'> </span>";
			$disp .= acymailing_translation('SEND_PROCESS').': <span id="counter" >'.$this->start.'</span> / '.$this->total;
			$disp .= '</div>';
			$disp .= "<div id='divinfo' style='display:none; position:fixed; bottom:3px;left:3px;background-color : white; border : 1px solid grey; padding : 3px;'> </div>";
			$disp .= '<br /><br />';
			$url = acymailing_completeLink('send&task=continuesend&mailid='.$this->mailid.'&totalsend='.$this->total, true, true).'&alreadysent=';
			$disp .= '<script type="text/javascript" language="javascript">';
			$disp .= 'var mycounter = document.getElementById("counter");';
			$disp .= 'var divinfo = document.getElementById("divinfo");
					var divpauseinfo = document.getElementById("divpauseinfo");
					function setInfo(message){ divinfo.style.display = \'block\';divinfo.innerHTML=message; }
					function setPauseInfo(nbpause){ divpauseinfo.style.display = \'\';divpauseinfo.innerHTML=nbpause;}
					function setCounter(val){ mycounter.innerHTML=val;}
					var scriptpause = '.intval($this->pause).';
					function handlePause(){
						setPauseInfo(scriptpause);
						if(scriptpause > 0){
							scriptpause = scriptpause - 1;
							setTimeout(\'handlePause()\',1000);
						}else{
							document.location.href=\''.$url.'\'+mycounter.innerHTML;
						}
					}
					</script>';
			echo $disp;
			if(function_exists('ob_flush')) @ob_flush();
			if(!$this->mod_security2) @flush();
		}//endifreport

		$mailHelper = acymailing_get('helper.mailer');
		$mailHelper->report = false;
		if($this->config->get('smtp_keepalive', 1) || in_array($this->config->get('mailer_method'), array('elasticemail'))) $mailHelper->SMTPKeepAlive = true;

		$queueDelete = array();
		$queueUpdate = array();
		$statsAdd = array();
		$actionSubscriber = array();

		$maxTry = (int)$this->config->get('queue_try', 0);

		$currentMail = $this->start;
		$this->nbprocess = 0;

		if(count($queueElements) < $this->send_limit){
			$this->finish = true;
		}

		foreach($queueElements as $oneQueue){
			$currentMail++;
			$this->nbprocess++;
			if($this->report){
				echo '<script type="text/javascript" language="javascript">setCounter('.$currentMail.')</script>';
				if(function_exists('ob_flush')) @ob_flush();
				if(!$this->mod_security2){
					@flush();
				}
			}

			$result = $mailHelper->sendOne($oneQueue->mailid, $oneQueue);

			$queueDeleteOk = true;
			$otherMessage = '';

			if($result){
				$this->successSend++;
				$this->consecutiveError = 0;
				$queueDelete[$oneQueue->mailid][] = $oneQueue->subid;
				$statsAdd[$oneQueue->mailid][1][(int)$mailHelper->sendHTML][] = $oneQueue->subid;

				$queueDeleteOk = $this->_deleteQueue($queueDelete);
				$queueDelete = array();

				if($this->nbprocess % 10 == 0){
					$this->statsAdd($statsAdd);
					$this->_queueUpdate($queueUpdate);
					$statsAdd = array();
					$queueUpdate = array();
				}
			}else{
				$this->errorSend++;

				$newtry = false;
				if(in_array($mailHelper->errorNumber, $mailHelper->errorNewTry)){
					if(empty($maxTry) OR $oneQueue->try < $maxTry - 1){
						$newtry = true;
						$otherMessage = acymailing_translation_sprintf('QUEUE_NEXT_TRY', 60);
					}
					if($mailHelper->errorNumber == 1) $this->consecutiveError++;
					if($this->consecutiveError == 2) sleep(1);
				}

				if(!$newtry){
					$queueDelete[$oneQueue->mailid][] = $oneQueue->subid;
					$statsAdd[$oneQueue->mailid][0][(int)@$mailHelper->sendHTML][] = $oneQueue->subid;
					if($mailHelper->errorNumber == 1 AND $this->config->get('bounce_action_maxtry')){
						$queueDeleteOk = $this->_deleteQueue($queueDelete);
						$queueDelete = array();
						$otherMessage .= $this->_subscriberAction($oneQueue->subid);
					}
				}else{
					$queueUpdate[$oneQueue->mailid][] = $oneQueue->subid;
				}
			}

			$messageOnScreen = '[ ID '.$oneQueue->mailid.'] '.$mailHelper->reportMessage;
			if(!empty($otherMessage)) $messageOnScreen .= ' => '.$otherMessage;
			$this->_display($messageOnScreen, $result, $currentMail);

			if(!$queueDeleteOk){
				$this->finish = true;
				break;
			}

			if(!empty($this->stoptime) AND $this->stoptime < time()){
				$this->_display(acymailing_translation('SEND_REFRESH_TIMEOUT'));
				if($this->nbprocess < count($queueElements)) $this->finish = false;
				break;
			}

			if($this->consecutiveError > 3 AND $this->successSend > 3){
				$this->_display(acymailing_translation('SEND_REFRESH_CONNECTION'));
				break;
			}

			if($this->consecutiveError > 5 OR connection_aborted()){
				$this->finish = true;
				break;
			}
		}

		$this->_deleteQueue($queueDelete);
		$this->statsAdd($statsAdd);
		$this->_queueUpdate($queueUpdate);

		if($mailHelper->SMTPKeepAlive) $mailHelper->smtpClose();

		if(!empty($this->total) AND $currentMail >= $this->total){
			$this->finish = true;
		}

		if($this->consecutiveError > 5){
			$this->_handleError();
			return false;
		}

		if($this->report && !$this->finish){
			echo '<script type="text/javascript" language="javascript">handlePause();</script>';
		}

		if($this->report){
			echo "</body></html>";
			while($this->obend-- > 0){
				ob_start();
			}
			exit;
		}

		return true;
	}

	private function _deleteQueue($queueDelete){
		if(empty($queueDelete)) return true;
		$status = true;

		foreach($queueDelete as $mailid => $subscribers){
			$nbsub = count($subscribers);
			$query = 'DELETE FROM '.acymailing_table('queue').' WHERE mailid = '.intval($mailid).' AND subid IN ('.implode(',', $subscribers).') LIMIT '.$nbsub;
			$res = acymailing_query($query);
			if($res === false){
				$status = false;
				$this->_display(acymailing_getDBError());
			}else{
				$nbdeleted = $res;
				if($nbdeleted != $nbsub){
					$status = false;
					$this->_display($nbdeleted < $nbsub ? acymailing_translation('QUEUE_DOUBLE') : $nbdeleted.' emails deleted from the queue whereas we only have '.$nbsub.' subscribers');
				}
			}
		}

		return $status;
	}


	public function statsAdd($statsAdd){

		if(empty($statsAdd)) return true;

		$time = time();


		$subids = array();

		foreach($statsAdd as $mailid => $infos){
			$mailid = intval($mailid);

			foreach($infos as $status => $infosSub){
				foreach($infosSub as $html => $subscribers){

					$query = 'INSERT INTO '.acymailing_table('userstats').' (mailid,subid,html,sent,fail,senddate) VALUES ';
					$query .= '('.$mailid.','.implode(','.$html.','.($status ? 1 : 0).','.($status ? 0 : 1).','.$time.'),('.$mailid.',', $subscribers).','.$html.','.($status ? 1 : 0).','.($status ? 0 : 1).','.$time.') ';
					$query .= 'ON DUPLICATE KEY UPDATE html = '.$html.',sent = sent + '.($status ? 1 : 0).', fail = '.($status ? '0' : 'fail + 1').', senddate = '.$time;
					acymailing_query($query);

					if($status){
						$subids = array_merge($subids, $subscribers);
					}
				}
			}

			$nbhtml = empty($infos[1][1]) ? 0 : count($infos[1][1]); //nbhtml sent
			$nbtext = empty($infos[1][0]) ? 0 : count($infos[1][0]); //nbtext sent
			$nbfail = 0;
			if(!empty($infos[0][0])) $nbfail += count($infos[0][0]); //fail text version
			if(!empty($infos[0][1])) $nbfail += count($infos[0][1]); //fail html version

			$query = 'INSERT INTO '.acymailing_table('stats').' (mailid,senthtml,senttext,fail,senddate) ';
			$query .= 'VALUES ('.$mailid.','.$nbhtml.', '.$nbtext.', '.$nbfail.', '.$time.') ';
			$query .= 'ON DUPLICATE KEY UPDATE senthtml = senthtml + '.$nbhtml.', senttext = senttext + '.$nbtext.', fail = fail + '.$nbfail.', senddate = '.$time;
			acymailing_query($query);
		}

		if(!empty($subids)){
			acymailing_query('UPDATE #__acymailing_subscriber SET `lastsent_date` = '.time().' WHERE `subid` IN ('.implode(',', $subids).')');
		}
	}

	private function _queueUpdate($queueUpdate){
		if(empty($queueUpdate)) return true;

		$delay = 3600;


		foreach($queueUpdate as $mailid => $subscribers){
			$query = 'UPDATE '.acymailing_table('queue').' SET senddate = senddate + '.$delay.', try = try +1 WHERE mailid = '.$mailid.' AND subid IN ('.implode(',', $subscribers).')';
			acymailing_query($query);
		}
	}

	private function _handleError(){
		$this->finish = true;
		$message = acymailing_translation('SEND_STOPED');
		$message .= '<br />';
		$message .= acymailing_translation('SEND_KEPT_ALL');
		$message .= '<br />';
		if($this->report){
			if(empty($this->successSend) AND empty($this->start)){
				$message .= acymailing_translation('SEND_CHECKONE');
				$message .= '<br />';
				$message .= acymailing_translation('SEND_ADVISE_LIMITATION');
			}else{
				$message .= acymailing_translation('SEND_REFUSE');
				$message .= '<br />';
				if(!acymailing_level(1)){
					$message .= acymailing_translation('SEND_CONTINUE_COMMERCIAL');
				}else{
					$message .= acymailing_translation('SEND_CONTINUE_AUTO');
				}
			}
		}

		$this->_display($message);
	}

	private function _display($message, $status = '', $num = ''){
		$this->messages[] = strip_tags($message);

		if(!$this->report) return;

		if(!empty($num)){
			$color = $status ? 'green' : 'red';
			echo '<br />'.$num.' : <span style="color:'.$color.';">'.$message.'</span>';
		}else{
			echo '<script type="text/javascript" language="javascript">setInfo(\''.addslashes($message).'\')</script>';
		}
		if(function_exists('ob_flush')) @ob_flush();
		if(!$this->mod_security2){
			@flush();
		}
	}

	private function _subscriberAction($subid){
		if($this->config->get('bounce_action_maxtry') == 'delete'){
			$this->subClass->delete($subid);
			return ' user '.$subid.' deleted';
		}
		$listId = 0;
		if(in_array($this->config->get('bounce_action_maxtry'), array('sub', 'remove', 'unsub'))){
			$status = $this->subClass->getSubscriptionStatus($subid);
		}
		$message = '';
		switch($this->config->get('bounce_action_maxtry')){
			case 'sub' :
				$listId = $this->config->get('bounce_action_lists_maxtry');
				if(!empty($listId)){
					$message .= ' user '.$subid.' subscribed to '.$listId;
					if(empty($status[$listId])){
						$this->listsubClass->addSubscription($subid, array('1' => array($listId)));
					}elseif($status[$listId]->status != 1){
						$this->listsubClass->updateSubscription($subid, array('1' => array($listId)));
					}
				}
			case 'remove' :
				$unsubLists = array_diff(array_keys($status), array($listId));
				if(!empty($unsubLists)){
					$message .= ' user '.$subid.' removed from lists '.implode(',', $unsubLists);
					$this->listsubClass->removeSubscription($subid, $unsubLists);
				}else{
					$message .= ' user '.$subid.' not subscribed';
				}
				break;
			case 'unsub' :
				$unsubLists = array_diff(array_keys($status), array($listId));
				if(!empty($unsubLists)){
					$message .= ' user '.$subid.' unsubscribed from lists '.implode(',', $unsubLists);
					$this->listsubClass->updateSubscription($subid, array('-1' => $unsubLists));
				}else{
					$message .= ' user '.$subid.' not subscribed';
				}
				break;
			case 'delete' :
				$message .= ' user '.$subid.' deleted';
				$this->subClass->delete($subid);
				break;
			case 'block' :
				$message .= ' user '.$subid.' blocked';
				acymailing_query('UPDATE `#__acymailing_subscriber` SET `enabled` = 0 WHERE `subid` = '.intval($subid));
				acymailing_query('DELETE FROM `#__acymailing_queue` WHERE `subid` = '.intval($subid));
				break;
		}
		return $message;
	}
}
