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

class acymruleClass extends acymClass
{
    var $table = 'rule';
    var $pkey = 'id';
    var $errors = array();

    public function getAll($active = false)
    {
        $rules = acym_loadObjectList('SELECT * FROM `#__acym_rule` '.($active ? 'WHERE active = 1' : '').' ORDER BY `ordering` ASC');

        foreach ($rules as $i => $rule) {
            $rules[$i] = $this->_prepareRule($rule);
        }

        return $rules;
    }

    public function getOneById($id)
    {
        $rule = acym_loadObject('SELECT * FROM `#__acym_rule` WHERE `id` = '.intval($id).' LIMIT 1');

        return $this->_prepareRule($rule);
    }

    private function _prepareRule($rule)
    {
        $columns = array('executed_on', 'action_message', 'action_user');
        foreach ($columns as $oneColumn) {
            if (!empty($rule->$oneColumn)) {
                $rule->$oneColumn = json_decode($rule->$oneColumn, true);
            }
        }

        return $rule;
    }

    public function save($rule)
    {
        if (empty($rule)) {
            return false;
        }

        return parent::save($rule);
    }

    public function delete($ids)
    {
        return parent::delete($ids);
    }
}
