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

class acymmailStatClass extends acymClass
{
    var $table = 'mail_stat';
    var $pkey = 'mail_id';

    public function save($mailStat)
    {
        $column = array();
        $valueColumn = array();
        $columnName = acym_getColumns("mail_stat");

        if (!array($mailStat)) {
            $mailStat = (array)$mailStat;
        }

        foreach ($mailStat as $key => $value) {
            if (in_array($key, $columnName)) {
                $column[] = '`'.$key.'`';
                $valueColumn[] = "'".$value."'";
            }
        }

        $query = "#__acym_mail_stat (".implode(',', $column).") VALUES (".implode(',', $valueColumn).")";

        $onDuplicate = array();

        if (!empty($mailStat['sent'])) {
            $onDuplicate[] = " sent = sent + ".$mailStat['sent'];
        }

        if (!empty($mailStat['fail'])) {
            $onDuplicate[] = " fail = fail + ".$mailStat['fail'];
        }

        if (!empty($mailStat['open_unique'])) {
            $onDuplicate[] = "open_unique = open_unique + 1";
        }

        if (!empty($mailStat['open_total'])) {
            $onDuplicate[] = "open_total = open_total + 1";
        }

        if (!empty($mailStat['total_subscribers'])) {
            $onDuplicate[] = "total_subscribers = ".$mailStat['total_subscribers'];
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
        $result = acym_loadResult("SELECT total_subscribers FROM #__acym_mail_stat WHERE mail_id = ".$mailId." LIMIT 1");

        return $result === null ? 0 : $result;
    }

    function getOneByMailId($id = '')
    {
        $query = 'SELECT SUM(sent) as sent, SUM(fail) as fail FROM #__acym_mail_stat';
        $query .= empty($id) ? '' : ' WHERE `mail_id` = '.intval($id);

        return acym_loadObject($query);
    }

    public function getTotalSubscribersByMailsIs($mailsIds = array())
    {
        acym_arrayToInteger($mailsIds);
        if (empty($mailsIds)) {
            $mailsIds[] = 0;
        }

        $result = acym_loadObjectList("SELECT mail_id, total_subscribers FROM #__acym_mail_stat WHERE mail_id IN (".implode(",", $mailsIds).")");

        return $result === null ? 0 : $result;
    }

    public function getAllFromMailIds($mailsIds = array())
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

}
