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

class acymClass
{
    var $table = '';

    var $pkey = '';

    var $namekey = '';

    var $errors = array();

    public function __construct()
    {
        global $acymCmsUserVars;
        $this->cmsUserVars = $acymCmsUserVars;
    }

    function save($element)
    {
        $pkey = $this->pkey;

        if (empty($element->$pkey)) {
            $status = acym_insertObject('#__acym_'.$this->table, $element);
        } else {
            $status = acym_updateObject('#__acym_'.$this->table, $element, $pkey);
        }

        if (!$status) {
            $this->errors[] = substr(strip_tags(acym_getDBError()), 0, 200).'...';

            return false;
        }

        return empty($element->$pkey) ? $status : $element->$pkey;
    }

    function delete($elements)
    {
        if (!is_array($elements)) {
            $elements = array($elements);
        }

        if (empty($elements)) {
            return 0;
        }

        $column = is_numeric(reset($elements)) ? $this->pkey : $this->namekey;

        foreach ($elements as $key => $val) {
            $elements[$key] = acym_escapeDB($val);
        }

        if (empty($column) || empty($this->pkey) || empty($this->table) || empty($elements)) {
            return false;
        }

        $query = 'DELETE FROM #__acym_'.$this->table.' WHERE '.acym_secureDBColumn($column).' IN ('.implode(',', $elements).')';
        $result = acym_query($query);

        if (!$result) {
            return false;
        }

        return $result;
    }

    public function setActive($elements)
    {
        if (!is_array($elements)) {
            $elements = array($elements);
        }

        if (empty($elements)) {
            return 0;
        }

        acym_arrayToInteger($elements);
        acym_query('UPDATE #__acym_'.$this->table.' SET active = 1 WHERE id IN ('.implode(',', $elements).')');
    }

    public function setInactive($elements)
    {
        if (!is_array($elements)) {
            $elements = array($elements);
        }

        if (empty($elements)) {
            return 0;
        }

        acym_arrayToInteger($elements);
        acym_query('UPDATE #__acym_'.$this->table.' SET active = 0 WHERE id IN ('.implode(',', $elements).')');
    }
}
