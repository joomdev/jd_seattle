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

class acymconfigurationClass extends acymClass
{
    var $table = 'configuration';
    var $pkey = 'name';
    var $values = array();

    function load()
    {
        $this->values = acym_loadObjectList('SELECT * FROM #__acym_configuration', 'name');
    }

    function get($namekey, $default = '')
    {
        if (isset($this->values[$namekey])) {
            return $this->values[$namekey]->value;
        }

        return $default;
    }

    function save($newConfig)
    {
        $query = 'REPLACE INTO #__acym_configuration (name,value) VALUES ';

        $params = array();
        foreach ($newConfig as $name => $value) {
            if (strpos($name, 'password') !== false && !empty($value) && trim($value, '*') == '') {
                continue;
            }

            if (is_array($value)) {
                $value = implode(',', $value);
            }

            if (empty($this->values[$name])) {
                $this->values[$name] = new stdClass();
            }
            $this->values[$name]->value = $value;

            $params[] = '('.acym_escapeDB(strip_tags($name)).','.acym_escapeDB(strip_tags($value)).')';
        }

        if (empty($params)) {
            return true;
        }

        $query .= implode(',', $params);

        try {
            $status = acym_query($query);
        } catch (Exception $e) {
            $status = false;
        }
        if ($status === false) {
            acym_display(isset($e) ? $e->getMessage() : substr(strip_tags(acym_getDBError()), 0, 200).'...', 'error');
        }

        return $status;
    }
}
