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

class acymconditionClass extends acymClass
{
    var $table = 'condition';
    var $pkey = 'id';

    public function getOneByStepId($stepId)
    {
        $query = "SELECT * FROM #__acym_condition WHERE step_id = ".$stepId;

        return acym_loadObject($query);
    }

    public function delete($elements)
    {
        if (!is_array($elements)) {
            $elements = array($elements);
        }
        acym_arrayToInteger($elements);

        if (empty($elements)) {
            return 0;
        }

        acym_query('DELETE FROM #__acym_action WHERE condition_id IN ('.implode(',', $elements).')');

        return parent::delete($elements);
    }

    public function save($condition)
    {
        foreach ($condition as $oneAttribute => $value) {
            if (empty($value)) {
                continue;
            }

            if (is_array($value)) {
                $value = json_encode($value);
            }

            $condition->$oneAttribute = strip_tags($value);
        }

        return parent::save($condition);
    }
}
