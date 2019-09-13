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

class acymClass
{
    var $table = '';

    var $pkey = '';

    var $namekey = '';

    var $errors = [];

    public function __construct()
    {
        global $acymCmsUserVars;
        $this->cmsUserVars = $acymCmsUserVars;
    }

    function save($element)
    {
        foreach ($element as $column => $value) {
            acym_secureDBColumn($column);
        }

        $pkey = $this->pkey;

        if (empty($element->$pkey)) {
            $status = acym_insertObject('#__acym_'.$this->table, $element);
        } else {
            $status = acym_updateObject('#__acym_'.$this->table, $element, $pkey);
        }

        if (!$status) {
            $dbError = strip_tags(acym_getDBError());
            if (!empty($dbError)) {
                if (strlen($dbError) > 203) $dbError = substr($dbError, 0, 200).'...';
                $this->errors[] = $dbError;
            }

            return false;
        }

        return empty($element->$pkey) ? $status : $element->$pkey;
    }

    function delete($elements)
    {
        if (!is_array($elements)) {
            $elements = [$elements];
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

        acym_trigger('onAcymAfter'.$this->table.'Delete', [&$elements]);

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
        acym_query('UPDATE '.acym_secureDBColumn('#__acym_'.$this->table).' SET active = 1 WHERE id IN ('.implode(',', $elements).')');
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
        acym_query('UPDATE '.acym_secureDBColumn('#__acym_'.$this->table).' SET active = 0 WHERE id IN ('.implode(',', $elements).')');
    }
}

