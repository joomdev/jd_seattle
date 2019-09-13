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

class acymqueryClass extends acymClass
{
    var $from = ' `#__acym_user` AS `user`';
    var $leftjoin = [];
    var $join = [];
    var $where = [];
    var $orderBy = '';
    var $limit = '';

    public function getQuery($select = [])
    {
        $query = '';
        if (!empty($select)) $query .= ' SELECT DISTINCT '.implode(',', $select);
        if (!empty($this->from)) $query .= ' FROM '.$this->from;
        if (!empty($this->join)) $query .= ' JOIN '.implode(' JOIN ', $this->join);
        if (!empty($this->leftjoin)) $query .= ' LEFT JOIN '.implode(' LEFT JOIN ', $this->leftjoin);
        if (!empty($this->where)) $query .= ' WHERE ('.implode(') AND (', $this->where).')';
        if (!empty($this->orderBy)) $query .= ' ORDER BY '.$this->orderBy;
        if (!empty($this->limit)) $query .= ' LIMIT '.$this->limit;

        return $query;
    }

    public function count()
    {
        return acym_loadResult($this->getQuery(['COUNT(DISTINCT user.id)']));
    }

    public function addFlag($id)
    {
        if (!empty($this->orderBy) || !empty($this->limit)) {
            $flagQuery = 'UPDATE #__acym_user';
            $flagQuery .= ' SET automation = CONCAT(automation, "a'.intval($id).'a")';
            $flagQuery .= ' WHERE id IN (
			SELECT id FROM (SELECT user.id FROM #__acym_user AS user';
            if (!empty($this->join)) $flagQuery .= ' JOIN '.implode(' JOIN ', $this->join);
            if (!empty($this->leftjoin)) $flagQuery .= ' LEFT JOIN '.implode(' LEFT JOIN ', $this->leftjoin);
            if (!empty($this->where)) $flagQuery .= ' WHERE ('.implode(') AND (', $this->where).')';
            if (!empty($this->orderBy)) $flagQuery .= ' ORDER BY '.$this->orderBy;
            if (!empty($this->limit)) $flagQuery .= ' LIMIT '.$this->limit;
            $flagQuery .= ') tmp);';
        } else {
            $flagQuery = 'UPDATE #__acym_user AS user ';
            if (!empty($this->join)) $flagQuery .= ' JOIN '.implode(' JOIN ', $this->join);
            if (!empty($this->leftjoin)) $flagQuery .= ' LEFT JOIN '.implode(' LEFT JOIN ', $this->leftjoin);
            $flagQuery .= ' SET user.automation = CONCAT(user.automation, "a'.intval($id).'a")';
            if (!empty($this->where)) $flagQuery .= ' WHERE ('.implode(') AND (', $this->where).')';
        }
        acym_query($flagQuery);

        $this->join = [];
        $this->leftjoin = [];
        $this->where = ['user.automation LIKE "%a'.intval($id).'a%"'];
        $this->orderBy = '';
        $this->limit = '';
    }

    public function removeFlag($id)
    {
        acym_query('UPDATE #__acym_user SET automation = REPLACE(automation, "a'.intval($id).'a", "") WHERE automation LIKE "%a'.intval($id).'a%"');
    }

    function convertQuery($table, $column, $operator, $value, $type = '')
    {
        $operator = str_replace(['&lt;', '&gt;'], ['<', '>'], $operator);

        if ($operator == 'CONTAINS' || ($type == 'phone' && $operator == '=')) {
            $operator = 'LIKE';
            $value = '%'.$value.'%';
        } elseif ($operator == 'BEGINS') {
            $operator = 'LIKE';
            $value = $value.'%';
        } elseif ($operator == 'END') {
            $operator = 'LIKE';
            $value = '%'.$value;
        } elseif ($operator == 'NOTCONTAINS' || ($type == 'phone' && $operator == '!=')) {
            $operator = 'NOT LIKE';
            $value = '%'.$value.'%';
        } elseif ($operator == 'REGEXP') {
            if ($value === '') return '1 = 1';
        } elseif ($operator == 'NOT REGEXP') {
            if ($value === '') return '0 = 1';
        } elseif (!in_array($operator, ['IS NULL', 'IS NOT NULL', 'NOT LIKE', 'LIKE', '=', '!=', '>', '<', '>=', '<='])) {
            die(acym_translation_sprintf('ACYM_UNKNOWN_OPERATOR', $operator));
        }

        if (strpos($value, '[time]') !== false) {
            $value = acym_replaceDate($value);
            $value = strftime('%Y-%m-%d %H:%M:%S', $value);
        }

        $replace = ['{year}', '{month}', '{weekday}', '{day}'];
        $replaceBy = [date('Y'), date('m'), date('N'), date('d')];
        $value = str_replace($replace, $replaceBy, $value);

        if (preg_match_all('#{(year|month|weekday|day)\|(add|remove):([^}]*)}#Uis', $value, $results)) {

            foreach ($results[0] as $i => $oneMatch) {
                $format = str_replace(['year', 'month', 'weekday', 'day'], ['Y', 'm', 'N', 'd'], $results[1][$i]);
                $delay = str_replace(['add', 'remove'], ['+', '-'], $results[2][$i]).intval($results[3][$i]).' '.str_replace('weekday', 'day', $results[1][$i]);
                $value = str_replace($oneMatch, date($format, strtotime($delay)), $value);
            }
        }

        if (!is_numeric($value) || in_array($operator, ['REGEXP', 'NOT REGEXP', 'NOT LIKE', 'LIKE', '=', '!='])) {
            $value = acym_escapeDB($value);
        }

        if (in_array($operator, ['IS NULL', 'IS NOT NULL'])) {
            $value = '';
        }

        if ($type == 'datetime' && in_array($operator, ['=', '!='])) {
            return 'DATE_FORMAT('.acym_secureDBColumn($table).'.`'.acym_secureDBColumn($column).'`, "%Y-%m-%d") '.$operator.' '.'DATE_FORMAT('.$value.', "%Y-%m-%d")';
        }
        if ($type == 'timestamp' && in_array($operator, ['=', '!='])) {
            return 'FROM_UNIXTIME('.acym_secureDBColumn($table).'.`'.acym_secureDBColumn($column).'`, "%Y-%m-%d") '.$operator.' '.'FROM_UNIXTIME('.$value.', "%Y-%m-%d")';
        }

        return acym_secureDBColumn($table).'.`'.acym_secureDBColumn($column).'` '.$operator.' '.$value;
    }
}

