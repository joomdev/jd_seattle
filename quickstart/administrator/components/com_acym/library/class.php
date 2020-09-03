<?php
defined('_JEXEC') or die('Restricted access');
?><?php

class acymClass extends acymObject
{
    var $table = '';

    var $pkey = '';

    var $namekey = '';

    var $errors = [];

    var $messages = [];

    public function getMatchingElements($settings = [])
    {
        if (!empty($this->table) && !empty($this->pkey)) {
            $query = 'SELECT * FROM #__acym_'.acym_secureDBColumn($this->table);
            $queryCount = 'SELECT COUNT(*) FROM #__acym_'.acym_secureDBColumn($this->table);

            if (empty($settings['ordering'])) $settings['ordering'] = $this->pkey;
            $query .= ' ORDER BY `'.acym_secureDBColumn($settings['ordering']).'`';
            if (!empty($settings['ordering_sort_order'])) $query .= ' '.acym_secureDBColumn(strtoupper($settings['ordering_sort_order']));

            $elements = acym_loadObjectList($query);
            $total = acym_loadResult($queryCount);
        } else {
            $elements = [];
            $total = '0';
        }

        return [
            'elements' => $elements,
            'total' => $total,
        ];
    }

    public function getOneById($id)
    {
        return acym_loadObject('SELECT * FROM #__acym_'.acym_secureDBColumn($this->table).' WHERE `'.acym_secureDBColumn($this->pkey).'` = '.intval($id));
    }

    public function getAll($key = null)
    {
        if (empty($key)) $key = $this->pkey;

        return acym_loadObjectList('SELECT * FROM #__acym_'.acym_secureDBColumn($this->table), $key);
    }

    public function save($element)
    {
        $tableColumns = acym_getColumns($this->table);
        $cloneElement = clone $element;
        foreach ($cloneElement as $column => $value) {
            if (!in_array($column, $tableColumns)) {
                unset($cloneElement->$column);
                continue;
            }
            acym_secureDBColumn($column);
        }

        $pkey = $this->pkey;

        if (empty($cloneElement->$pkey)) {
            $status = acym_insertObject('#__acym_'.$this->table, $cloneElement);
        } else {
            $status = acym_updateObject('#__acym_'.$this->table, $cloneElement, $pkey);
        }

        if (!$status) {
            $dbError = strip_tags(acym_getDBError());
            if (!empty($dbError)) {
                if (strlen($dbError) > 203) $dbError = substr($dbError, 0, 200).'...';
                $this->errors[] = $dbError;
            }

            return false;
        }

        return empty($cloneElement->$pkey) ? $status : $cloneElement->$pkey;
    }

    public function delete($elements)
    {
        if (!is_array($elements)) $elements = [$elements];
        if (empty($elements)) return 0;

        $column = is_numeric(reset($elements)) ? $this->pkey : $this->namekey;

        $escapedElements = [];
        foreach ($elements as $key => $val) {
            $escapedElements[$key] = acym_escapeDB($val);
        }

        if (empty($column) || empty($this->pkey) || empty($this->table) || empty($escapedElements)) {
            return false;
        }

        acym_trigger('onAcymBefore'.ucfirst($this->table).'Delete', [&$elements]);

        $query = 'DELETE FROM #__acym_'.acym_secureDBColumn($this->table).' WHERE '.acym_secureDBColumn($column).' IN ('.implode(',', $escapedElements).')';
        $result = acym_query($query);

        if (!$result) {
            return false;
        }

        acym_trigger('onAcymAfter'.ucfirst($this->table).'Delete', [&$elements]);

        return $result;
    }

    public function setActive($elements)
    {
        if (!is_array($elements)) {
            $elements = [$elements];
        }

        if (empty($elements)) {
            return 0;
        }

        acym_arrayToInteger($elements);
        acym_query('UPDATE '.acym_secureDBColumn('#__acym_'.$this->table).' SET active = 1 WHERE `'.acym_secureDBColumn($this->pkey).'` IN ('.implode(',', $elements).')');
    }

    public function setInactive($elements)
    {
        if (!is_array($elements)) {
            $elements = [$elements];
        }

        if (empty($elements)) {
            return 0;
        }

        acym_arrayToInteger($elements);
        acym_query('UPDATE '.acym_secureDBColumn('#__acym_'.$this->table).' SET active = 0 WHERE `'.acym_secureDBColumn($this->pkey).'` IN ('.implode(',', $elements).')');
    }
}

