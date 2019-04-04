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

class acymuserStatClass extends acymClass
{
    var $table = 'user_stat';

    public function save($userStat)
    {
        $column = array();
        $valueColumn = array();
        $columnName = acym_getColumns("user_stat");
        if (!array($userStat)) {
            $userStat = (array)$userStat;
        }

        foreach ($userStat as $key => $value) {
            if (in_array($key, $columnName)) {
                $column[] = '`'.acym_secureDBColumn($key).'`';
                $valueColumn[] = acym_escapeDB($value);
            }
        }

        $query = "INSERT INTO #__acym_user_stat (".implode(',', $column).") VALUE (".implode(',', $valueColumn).")";
        $onDuplicate = array();

        if (!empty($userStat['statusSending'])) {
            $onDuplicate[] = $userStat['statusSending'] == 0 ? "fail = fail + 1" : "sent = sent + 1";
        }

        if (!empty($userStat['open'])) {
            $onDuplicate[] = "open = open + 1";
            $automationClass = acym_get('class.automation');
            $automationClass->triggerUser('user_open', $userStat['user_id']);
        }

        if (!empty($userStat['open_date'])) {
            $onDuplicate[] = 'open_date = '.acym_escapeDB($userStat['open_date']);
        }

        if (!empty($onDuplicate)) {
            $query .= " ON DUPLICATE KEY UPDATE ";
            $query .= implode(',', $onDuplicate);
        }

        acym_query($query);
    }

    public function getOneByMailAndUserId($mail_id, $user_id)
    {
        $query = 'SELECT * FROM #__acym_user_stat WHERE `mail_id` = '.intval($mail_id).' AND `user_id`='.intval($user_id);

        return acym_loadObject($query);
    }

    public function getAllUserStatByUserId($idUser)
    {
        $query = 'SELECT * FROM #__acym_user_stat WHERE user_id = '.intval($idUser);

        return acym_loadObjectList($query);
    }


    public function getDetailedStats($settings)
    {
        $query = 'SELECT us.*, m.name, m.subject, u.email, c.id as campaign_id FROM #__acym_user_stat as us
                    LEFT JOIN #__acym_user as u ON us.user_id = u.id
                    INNER JOIN #__acym_mail as m ON us.mail_id = m.id
                    INNER JOIN #__acym_campaign as c ON us.mail_id = c.mail_id';
        $queryCount = 'SELECT COUNT(*) FROM #__acym_user_stat as us
                            LEFT JOIN #__acym_user as u ON us.user_id = u.id
                            INNER JOIN #__acym_mail as m ON us.mail_id = m.id
                            INNER JOIN #__acym_campaign as c ON us.mail_id = c.mail_id';
        $where = array();

        if (empty(!$settings['mail_id'])) {
            $where[] = 'us.mail_id = '.intval($settings['mail_id']);
        }

        if (empty(!$settings['search'])) {
            $where[] = '(m.name LIKE '.acym_escapeDB('%'.$settings['search'].'%').' OR u.email LIKE '.acym_escapeDB('%'.$settings['search'].'%').')';
        }

        if (!empty($where)) {
            $query .= ' WHERE ('.implode(') AND (', $where).')';
            $queryCount .= ' WHERE ('.implode(') AND (', $where).')';
        }

        if (!empty($settings['ordering']) && !empty($settings['ordering_sort_order'])) {
            if ($settings['ordering'] == 'email') {
                $table = 'u';
            } elseif ($settings['ordering'] == 'subject') {
                $table = 'm';
            } else {
                $table = 'us';
            }
            $query .= ' ORDER BY '.$table.'.'.acym_secureDBColumn($settings['ordering']).' '.strtoupper($settings['ordering_sort_order']);
        }

        $results['detailed_stats'] = acym_loadObjectList($query, '', $settings['offset'], $settings['detailedStatsPerPage']);
        $results['total'] = acym_loadResult($queryCount);

        return $results;
    }

    public function getTotalFailClickOpenByMailIds($mailIds)
    {
        $query = "SELECT mail_id, SUM(fail) as fail, SUM(sent) as sent, SUM(open) as open FROM #__acym_user_stat WHERE mail_id IN (".implode($mailIds, ",").") GROUP BY mail_id";

        return acym_loadObjectList($query, 'mail_id');
    }
}
