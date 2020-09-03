<?php
defined('_JEXEC') or die('Restricted access');
?><?php

class acymqueueHelper extends acymObject
{
    var $id = 0;
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
    var $messages = [];
    var $pause = 0;
    var $userClass;
    var $mod_security2 = false;
    var $obend = 0;
    var $emailtypes = [];

    public function __construct()
    {
        parent::__construct();

        $this->queueClass = acym_get('class.queue');
        $this->userClass = acym_get('class.user');

        $this->send_limit = (int)$this->config->get('queue_nbmail', 40);

        acym_increasePerf();

        @ini_set('default_socket_timeout', 10);

        @ignore_user_abort(true);

        $timelimit = intval(ini_get('max_execution_time'));
        if (empty($timelimit)) {
            $timelimit = 600;
        }

        $calculatedTimeout = $this->config->get('max_execution_time');
        if (!empty($calculatedTimeout)) {
            $timelimit = $calculatedTimeout;
        }

        if (!empty($timelimit)) {
            $this->stoptime = time() + $timelimit - 4;
        }
    }

    public function process()
    {
        $queueClass = acym_get('class.queue');
        $queueClass->emailtypes = $this->emailtypes;
        $queueElements = $queueClass->getReady($this->send_limit, $this->id);

        if (empty($queueElements)) {
            $this->finish = true;
            if ($this->report) {
                acym_display(acym_translation('ACYM_NO_PROCESS'), 'info');
            }

            return true;
        }

        if ($this->report) {
            if (function_exists('apache_get_modules')) {
                $modules = apache_get_modules();
                $this->mod_security2 = in_array('mod_security2', $modules);
            }

            @ini_set('output_buffering', 'off');
            @ini_set('zlib.output_compression', 0);

            if (!headers_sent()) {
                while (ob_get_level() > 0 && $this->obend++ < 3) {
                    @ob_end_flush();
                }
            }

            $disp = '<html><head><meta http-equiv="Content-Type" content="text/html;charset=utf-8" />';
            $disp .= '<title>'.acym_translation('ACYM_SEND_PROCESS').'</title>';
            $disp .= '<style>body{font-size:12px;font-family: Arial,Helvetica,sans-serif;}</style></head><body>';
            $disp .= '<div style="margin-bottom: 18px;padding: 8px !important; background-color: #fcf8e3; border: 1px solid #fbeed5; border-radius: 4px;"><p style="margin:0;">'.acym_translation('ACYM_DONT_CLOSE').'</p></div>';
            $disp .= "<div style='display: inline;background-color : white;border : 1px solid grey; padding : 3px;font-size:14px'>";
            $disp .= "<span id='divpauseinfo' style='padding:10px;margin:5px;font-size:16px;font-weight:bold;display:none;background-color:black;color:white;'> </span>";
            $disp .= acym_translation('ACYM_SEND_PROCESS').': <span id="counter" >'.$this->start.'</span> / '.$this->total;
            $disp .= '</div>';
            $disp .= "<div id='divinfo' style='display:none; position:fixed; bottom:3px;left:3px;background-color : white; border : 1px solid grey; padding : 3px;'> </div>";
            $disp .= '<br /><br />';
            $url = acym_completeLink('queue&task=continuesend&id='.$this->id.'&totalsend='.$this->total, true, true).'&alreadysent=';
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
            if (function_exists('ob_flush')) {
                @ob_flush();
            }
            if (!$this->mod_security2) {
                @flush();
            }
        }//endifreport

        $mailHelper = acym_get('helper.mailer');
        $mailHelper->report = false;
        if ($this->config->get('smtp_keepalive', 1) || in_array($this->config->get('mailer_method'), ['elasticemail'])) {
            $mailHelper->SMTPKeepAlive = true;
        }

        $queueDelete = [];
        $queueUpdate = [];
        $statsAdd = [];
        $actionSubscriber = [];

        $maxTry = (int)$this->config->get('queue_try', 0);

        $currentMail = $this->start;
        $this->nbprocess = 0;

        if (count($queueElements) < $this->send_limit) {
            $this->finish = true;
        }

        foreach ($queueElements as $oneQueue) {
            $currentMail++;
            $this->nbprocess++;
            if ($this->report) {
                echo '<script type="text/javascript" language="javascript">setCounter('.$currentMail.')</script>';
                if (function_exists('ob_flush')) {
                    @ob_flush();
                }
                if (!$this->mod_security2) {
                    @flush();
                }
            }

            $result = $mailHelper->sendOne($oneQueue->mail_id, $oneQueue->user_id);

            $queueDeleteOk = true;
            $otherMessage = '';

            if ($result) {
                $this->successSend++;
                $this->consecutiveError = 0;
                $queueDelete[$oneQueue->mail_id][] = $oneQueue->user_id;
                $statsAdd[$oneQueue->mail_id][1][] = $oneQueue->user_id;

                $queueDeleteOk = $this->_deleteQueue($queueDelete);
                $queueDelete = [];

                if ($this->nbprocess % 10 == 0) {
                    $this->statsAdd($statsAdd);
                    $this->_queueUpdate($queueUpdate);
                    $statsAdd = [];
                    $queueUpdate = [];
                }
            } else {
                $this->errorSend++;

                $newtry = false;
                if (in_array($mailHelper->errorNumber, $mailHelper->errorNewTry)) {
                    if (empty($maxTry) || $oneQueue->try < $maxTry - 1) {
                        $newtry = true;
                        $otherMessage = acym_translation_sprintf('ACYM_QUEUE_NEXT_TRY', 60);
                    }
                    if ($mailHelper->errorNumber == 1) {
                        $this->consecutiveError++;
                    }
                    if ($this->consecutiveError == 2) {
                        sleep(1);
                    }
                }

                if (!$newtry) {
                    $queueDelete[$oneQueue->mail_id][] = $oneQueue->user_id;
                    $statsAdd[$oneQueue->mail_id][0][] = $oneQueue->user_id;
                    if ($mailHelper->errorNumber == 1 && $this->config->get('bounce_action_maxtry')) {
                        $queueDeleteOk = $this->_deleteQueue($queueDelete);
                        $queueDelete = [];
                        $otherMessage .= $this->_failedActions($oneQueue->user_id);
                    }
                } else {
                    $queueUpdate[$oneQueue->mail_id][] = $oneQueue->user_id;
                }
            }

            $messageOnScreen = '[ ID '.$oneQueue->mail_id.'] '.$mailHelper->reportMessage;
            if (!empty($otherMessage)) {
                $messageOnScreen .= ' => '.$otherMessage;
            }
            $this->_display($messageOnScreen, $result, $currentMail);

            if (!$queueDeleteOk) {
                $this->finish = true;
                break;
            }

            if (!empty($this->stoptime) && $this->stoptime < time()) {
                $this->_display(acym_translation('ACYM_SEND_REFRESH_TIMEOUT'));
                if ($this->nbprocess < count($queueElements)) {
                    $this->finish = false;
                }
                break;
            }

            if ($this->consecutiveError > 3 && $this->successSend > 3) {
                $this->_display(acym_translation('ACYM_SEND_REFRESH_CONNECTION'));
                break;
            }

            if ($this->consecutiveError > 5 || connection_aborted()) {
                $this->finish = true;
                break;
            }
        }

        $this->_deleteQueue($queueDelete);
        $this->statsAdd($statsAdd);
        $this->_queueUpdate($queueUpdate);

        if ($mailHelper->SMTPKeepAlive) {
            $mailHelper->smtpClose();
        }

        if (!empty($this->total) && $currentMail >= $this->total) {
            $this->finish = true;
        }

        if ($this->consecutiveError > 5) {
            $this->_handleError();

            return false;
        }

        if ($this->report && !$this->finish) {
            echo '<script type="text/javascript" language="javascript">handlePause();</script>';
        }

        if ($this->report) {
            echo "</body></html>";
            while ($this->obend-- > 0) {
                ob_start();
            }
            exit;
        }

        return true;
    }

    private function _deleteQueue($queueDelete)
    {
        if (empty($queueDelete)) {
            return true;
        }
        $status = true;

        foreach ($queueDelete as $mailid => $subscribers) {
            $nbsub = count($subscribers);
            $res = $this->queueClass->deleteOne($subscribers, $mailid);
            if ($res === false || !empty($this->queueClass->errors)) {
                $status = false;
                $this->_display($this->queueClass->errors);
            } else {
                $nbdeleted = $res;
                if ($nbdeleted != $nbsub) {
                    $status = false;
                    $this->_display($nbdeleted < $nbsub ? acym_translation('ACYM_QUEUE_DOUBLE') : $nbdeleted.' emails deleted from the queue whereas we only have '.$nbsub.' subscribers');
                }
            }
        }

        return $status;
    }

    public function statsAdd($statsAdd)
    {
        if (empty($statsAdd)) {
            return true;
        }

        $userStatClass = acym_get('class.userstat');
        $mailStatClass = acym_get('class.mailstat');

        $time = acym_date("now", "Y-m-d H:i:s");

        foreach ($statsAdd as $mailId => $infos) {

            $mailId = intval($mailId);

            foreach ($infos as $status => $subscribers) {
                foreach ($subscribers as $oneSubscriber) {

                    $oneSubscriber = intval($oneSubscriber);

                    $userStat = [];
                    $userStat['user_id'] = $oneSubscriber;
                    $userStat['mail_id'] = $mailId;
                    $userStat['send_date'] = $time;
                    $userStat['fail'] = $status ? 0 : 1;
                    $userStat['sent'] = $status ? 1 : 0;
                    $userStat['statusSending'] = $status;

                    $userStatClass->save($userStat);
                }
            }

            $nbSent = empty($infos[1]) ? 0 : count($infos[1]);
            $nbFail = empty($infos[0]) ? 0 : count($infos[0]);

            $mailStat = [];
            $mailStat['mail_id'] = $mailId;
            $mailStat['sent'] = $nbSent;
            $mailStat['fail'] = $nbFail;

            $mailStatClass->save($mailStat);
        }

        return true;
    }

    private function _queueUpdate($queueUpdate)
    {
        if (empty($queueUpdate)) {
            return true;
        }

        foreach ($queueUpdate as $mailid => $subscribers) {
            $this->queueClass->delayFailed($mailid, $subscribers);
        }
    }

    private function _handleError()
    {
        $this->finish = true;
        $message = acym_translation('ACYM_SEND_STOPED');
        $message .= '<br />';
        $message .= acym_translation('ACYM_SEND_KEPT_ALL');
        $message .= '<br />';
        if ($this->report) {
            if (empty($this->successSend) && empty($this->start)) {
                $message .= acym_translation('ACYM_SEND_CHECKONE');
                $message .= '<br />';
                $message .= acym_translation('ACYM_SEND_ADVISE_LIMITATION');
            } else {
                $message .= acym_translation('ACYM_SEND_REFUSE');
                $message .= '<br />';
                if (!acym_level(1)) {
                    $message .= acym_translation('ACYM_SEND_CONTINUE_COMMERCIAL');
                } else {
                    $message .= acym_translation('ACYM_SEND_CONTINUE_AUTO');
                }
            }
        }

        $this->_display($message);
    }

    private function _display($messages, $status = '', $num = '')
    {
        if (!is_array($messages)) {
            $messages = [$messages];
        }

        foreach ($messages as $message) {
            $this->messages[] = strip_tags($message);
        }

        if (!$this->report) {
            return;
        }

        $color = $status ? 'green' : 'red';
        foreach ($messages as $message) {
            if (!empty($num)) {
                echo '<br />'.$num.' : <span style="color:'.$color.';">'.$message.'</span>';
            } else {
                echo '<script type="text/javascript" language="javascript">setInfo(\''.addslashes($message).'\')</script>';
            }
        }

        if (function_exists('ob_flush')) {
            @ob_flush();
        }
        if (!$this->mod_security2) {
            @flush();
        }
    }

    private function _failedActions($userId)
    {
        $listId = 0;
        if (in_array($this->config->get('bounce_action_maxtry'), ['sub', 'remove', 'unsub'])) {
            $subscriptions = $this->userClass->getUserSubscriptionById($userId);
        }

        $message = '';
        switch ($this->config->get('bounce_action_maxtry')) {
            case 'sub' :
                $listId = $this->config->get('bounce_action_lists_maxtry');
                if (!empty($listId)) {
                    $message .= ' user '.$userId.' subscribed to list n°'.$listId;
                    $this->userClass->subscribe($userId, [$listId]);
                }
            case 'remove' :
                $unsubLists = array_diff(array_keys($subscriptions), [$listId]);
                if (!empty($unsubLists)) {
                    $message .= ' user '.$userId.' removed from lists '.implode(',', $unsubLists);
                    $this->userClass->removeSubscription($userId, $unsubLists);
                } else {
                    $message .= ' user '.$userId.' not subscribed';
                }
                break;
            case 'unsub' :
                $unsubLists = array_diff(array_keys($subscriptions), [$listId]);
                if (!empty($unsubLists)) {
                    $message .= ' user '.$userId.' unsubscribed from lists '.implode(',', $unsubLists);
                    $this->userClass->unsubscribe($userId, $unsubLists);
                } else {
                    $message .= ' user '.$userId.' not unsubscribed';
                }
                break;
            case 'delete' :
                $message .= ' user '.$userId.' deleted';
                $this->userClass->delete($userId);
                break;
            case 'block' :
                $message .= ' user '.$userId.' blocked';
                $this->userClass->deactivate($userId);
                $this->queueClass->deleteOne($userId);
                break;
        }

        return $message;
    }
}

