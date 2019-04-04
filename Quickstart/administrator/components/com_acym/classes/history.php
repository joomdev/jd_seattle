<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.1.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php

class acymhistoryClass extends acymClass
{
    function insert($userId, $action, $data = array(), $mailid = 0)
    {
        $currentUserid = acym_currentUserId();
        if (!empty($currentUserid)) {
            $data[] = acym_translation('EXECUTED_BY').'::'.$currentUserid.' ( '.acym_currentUserName().' )';
        }
        $history = new stdClass();
        $history->user_id = intval($userId);
        $history->action = strip_tags($action);
        $history->data = implode("\n", $data);
        if (strlen($history->data) > 100000) {
            $history->data = substr($history->data, 0, 10000);
        }

        static $date = null;
        if (empty($date)) {
            $date = time();
        }

        $history->date = ++$date;
        while ($this->alreadyExists($history->user_id, $history->date)) {
            $history->date++;
        }

        $date = $history->date;

        $history->mail_id = $mailid;
        $config = acym_config();
        if ($config->get('anonymous_tracking', 0) == 0) {
            $history->ip = acym_getIP();
        }

        if (!empty($_SERVER)) {
            $source = array();
            if ($config->get('anonymous_tracking', 0) == 0) {
                $vars = array('HTTP_REFERER', 'HTTP_USER_AGENT', 'HTTP_HOST', 'SERVER_ADDR', 'REMOTE_ADDR', 'REQUEST_URI', 'QUERY_STRING');
            } else {
                $vars = array('HTTP_REFERER', 'HTTP_HOST', 'SERVER_ADDR', 'REQUEST_URI', 'QUERY_STRING');
            }

            foreach ($vars as $oneVar) {
                if (!empty($_SERVER[$oneVar])) {
                    $source[] = $oneVar.'::'.strip_tags($_SERVER[$oneVar]);
                }
            }
            $history->source = implode("\n", $source);
        }

        return acym_insertObject('#__acym_history', $history);
    }

    function alreadyExists($userId, $date)
    {
        $result = acym_loadResult('SELECT user_id FROM #__acym_history WHERE user_id = '.intval($userId).' AND date = '.acym_escapeDB($date));

        return !empty($result);
    }
}
