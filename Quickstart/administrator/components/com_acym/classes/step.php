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

class acymstepClass extends acymClass
{

    var $table = 'step';
    var $pkey = 'id';


    public function save($step)
    {
        foreach ($step as $oneAttribute => $value) {
            if (empty($value)) {
                continue;
            }

            if (is_array($value)) {
                $value = json_encode($value);
            }

            $step->$oneAttribute = strip_tags($value);
        }

        return parent::save($step);
    }

    public function getOneStepByAutomationId($automationId)
    {
        $query = 'SELECT * FROM #__acym_step WHERE automation_id = '.$automationId.' LIMIT 1';

        return acym_loadObject($query);
    }

    public function getOneById($id)
    {
        $query = 'SELECT * FROM #__acym_step WHERE id = '.$id;

        return acym_loadObject($query);
    }

    public function getStepsByAutomationId($automationId)
    {
        $query = 'SELECT * FROM #__acym_step as step WHERE automation_id = '.intval($automationId);

        return acym_loadObjectList($query);
    }

    public function getActiveStepByTrigger($trigger)
    {
        $query = 'SELECT step.* FROM #__acym_step as step LEFT JOIN #__acym_automation as automation ON step.automation_id = automation.id WHERE step.triggers LIKE \'%"'.$trigger.'"%\' AND automation.active = 1';

        return acym_loadObjectList($query);
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

        $conditions = acym_loadResultArray('SELECT id FROM #__acym_condition WHERE step_id IN ('.implode(',', $elements).')');
        $conditionClass = acym_get('class.condition');
        $conditionsDeleted = $conditionClass->delete($conditions);

        return parent::delete($elements);
    }
}
