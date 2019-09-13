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

class acymurlClickClass extends acymClass
{
    var $table = 'url_click';

    public function save($urlClick)
    {
        $column = [];
        $valueColumn = [];
        $columnName = acym_getColumns("url_click");

        if (!is_array($urlClick)) {
            $urlClick = (array)$urlClick;
        }

        foreach ($urlClick as $key => $value) {
            if (in_array($key, $columnName)) {
                $column[] = '`'.acym_secureDBColumn($key).'`';
                $valueColumn[] = acym_escapeDB($value);
            }
        }

        $query = "#__acym_url_click (".implode(',', $column).") VALUES (".implode(',', $valueColumn).")";

        $onDuplicate = [];

        if (!empty($urlClick['click'])) {
            $onDuplicate[] = "click = click + 1";
            $automationClass = acym_get('class.automation');
            $automationClass->trigger('user_click', ['userId' => $urlClick['user_id']]);
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

    public function getClickRate($mailid = '')
    {
        $query = 'SELECT COUNT(DISTINCT user_id) as click FROM #__acym_url_click ';
        $query .= empty($mailid) ? '' : ' WHERE `mail_id` = '.intval($mailid);
        $query .= ' ORDER BY user_id';

        return acym_loadObject($query);
    }

    public function getAllClickByMailMonth($mailid = '', $start = '', $end = '')
    {
        $query = 'SELECT COUNT(*) as click, DATE_FORMAT(date_click, \'%Y-%m\') as date_click FROM #__acym_url_click WHERE click > 0';
        $query .= empty($mailid) ? '' : ' AND `mail_id` = '.intval($mailid);
        $query .= empty($start) ? '' : ' AND date_click >= '.acym_escapeDB($start);
        $query .= empty($end) ? '' : ' AND date_click <= '.acym_escapeDB($end);
        $query .= ' GROUP BY MONTH(date_click), YEAR(date_click) ORDER BY date_click';

        return acym_loadObjectList($query);
    }

    public function getAllClickByMailWeek($mailid = '', $start = '', $end = '')
    {
        $query = 'SELECT COUNT(*) as click, DATE_FORMAT(date_click, \'%Y-%m-%d\') as date_click FROM #__acym_url_click WHERE click > 0';
        $query .= empty($mailid) ? '' : ' AND `mail_id` = '.intval($mailid);
        $query .= empty($start) ? '' : ' AND date_click >= '.acym_escapeDB($start);
        $query .= empty($end) ? '' : ' AND date_click <= '.acym_escapeDB($end);
        $query .= ' GROUP BY WEEK(date_click), YEAR(date_click) ORDER BY date_click';

        return acym_loadObjectList($query);
    }

    public function getAllClickByMailDay($mailid = '', $start = '', $end = '')
    {
        $query = 'SELECT COUNT(*) as click, DATE_FORMAT(date_click, \'%Y-%m-%d\') as date_click FROM #__acym_url_click WHERE click > 0';
        $query .= empty($mailid) ? '' : ' AND `mail_id` = '.intval($mailid);
        $query .= empty($start) ? '' : ' AND date_click >= '.acym_escapeDB($start);
        $query .= empty($end) ? '' : ' AND date_click <= '.acym_escapeDB($end);
        $query .= ' GROUP BY DAYOFYEAR(date_click), YEAR(date_click) ORDER BY date_click';

        return acym_loadObjectList($query);
    }

    public function getAllClickByMailHour($mailid = '', $start = '', $end = '')
    {
        $query = 'SELECT COUNT(*) as click, DATE_FORMAT(date_click, \'%Y-%m-%d %H:00:00\') as date_click FROM #__acym_url_click WHERE click > 0';
        $query .= empty($mailid) ? '' : ' AND `mail_id` = '.intval($mailid);
        $query .= empty($start) ? '' : ' AND date_click >= '.acym_escapeDB($start);
        $query .= empty($end) ? '' : ' AND date_click <= '.acym_escapeDB($end);
        $query .= ' GROUP BY HOUR(date_click), DAYOFYEAR(date_click), YEAR(date_click) ORDER BY date_click';

        return acym_loadObjectList($query);
    }

    public function getAllLinkFromEmail($id)
    {
        $queryClickUrl = 'SELECT url.name, SUM(urlclick.click) as click FROM #__acym_url_click AS urlclick 
                          LEFT JOIN #__acym_url AS url ON urlclick.url_id = url.id 
                          WHERE `mail_id` = '.intval($id).' GROUP BY `url_id`';

        $queryCountAllClicks = 'SELECT SUM(click) FROM #__acym_url_click WHERE `mail_id` = '.intval($id);

        $return = [
            'urls_click' => acym_loadObjectList($queryClickUrl),
            'allClick' => acym_loadResult($queryCountAllClicks),
        ];

        return $return;
    }
}

