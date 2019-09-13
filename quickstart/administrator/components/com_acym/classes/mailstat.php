<?php
/**
 * @package	AcyMailing for Joomla
 * @version	6.2.2
 * @author	acyba.com
 * @copyright	(C) 2009-2019 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php

class acymmailStatClass extends acymClass
{
    var $table = 'mail_stat';
    var $pkey = 'mail_id';

    public function save($mailStat)
    {
        $column = [];
        $valueColumn = [];
        $columnName = acym_getColumns("mail_stat");

        if (!is_array($mailStat)) {
            $mailStat = (array)$mailStat;
        }

        foreach ($mailStat as $key => $value) {
            if (in_array($key, $columnName)) {
                $column[] = '`'.acym_secureDBColumn($key).'`';
                $valueColumn[] = acym_escapeDB($value);
            }
        }

        $query = "#__acym_mail_stat (".implode(',', $column).") VALUES (".implode(',', $valueColumn).")";

        $onDuplicate = [];

        if (!empty($mailStat['sent'])) {
            $onDuplicate[] = " sent = sent + ".intval($mailStat['sent']);
        }

        if (!empty($mailStat['fail'])) {
            $onDuplicate[] = " fail = fail + ".intval($mailStat['fail']);
        }

        if (!empty($mailStat['open_unique'])) {
            $onDuplicate[] = "open_unique = open_unique + 1";
        }

        if (!empty($mailStat['open_total'])) {
            $onDuplicate[] = "open_total = open_total + 1";
        }

        if (!empty($mailStat['total_subscribers'])) {
            $onDuplicate[] = "total_subscribers = ".intval($mailStat['total_subscribers']);
        }

        if (!empty($onDuplicate)) {
            $query .= " ON DUPLICATE KEY UPDATE ";
            $query .= implode(',', $onDuplicate);
            $query = "INSERT INTO ".$query;
        } else {
            $query = "INSERT IGNORE INTO ".$query;
        }

        acym_query($query);
    }

    public function getTotalSubscribersByMailId($mailId)
    {
        $result = acym_loadResult("SELECT total_subscribers FROM #__acym_mail_stat WHERE mail_id = ".intval($mailId)." LIMIT 1");

        return $result === null ? 0 : $result;
    }

    function getOneByMailId($id = '')
    {
        $query = 'SELECT SUM(sent) as sent, SUM(fail) as fail FROM #__acym_mail_stat';
        $query .= empty($id) ? '' : ' WHERE `mail_id` = '.intval($id);

        return acym_loadObject($query);
    }

    public function getAllFromMailIds($mailsIds = [])
    {
        acym_arrayToInteger($mailsIds);
        if (empty($mailsIds)) {
            $mailsIds[] = 0;
        }

        $result = acym_loadObjectList("SELECT * FROM #__acym_mail_stat WHERE mail_id IN (".implode(",", $mailsIds).")", "mail_id");

        return $result === null ? 0 : $result;
    }

    public function getOneRowByMailId($mailId)
    {
        $query = 'SELECT * FROM #__acym_mail_stat WHERE mail_id = '.intval($mailId);

        return acym_loadObject($query);
    }

    public function getAllMailsForStats()
    {
        $mailClass = acym_get('class.mail');

        $query = 'SELECT mail.* FROM #__acym_mail_stat AS mail_stat LEFT JOIN #__acym_mail AS mail ON mail.id = mail_stat.mail_id';

        return $mailClass->decode(acym_loadObjectList($query));
    }

}

