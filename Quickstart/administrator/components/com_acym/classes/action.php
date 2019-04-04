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

class acymactionClass extends acymClass
{
    var $table = 'action';
    var $pkey = 'id';

    public function getActionsByStepId($stepId)
    {
        $query = 'SELECT action.* FROM #__acym_action as action LEFT JOIN #__acym_condition as conditionT ON action.condition_id = conditionT.id WHERE conditionT.step_id = '.$stepId.' ORDER BY action.order';

        return acym_loadObjectList($query);
    }

    public function save($action)
    {
        $conditionClass = acym_get('class.condition');

        $condition = new stdClass();
        if (empty($action->id)) {
            $condition->step_id = $action->step_id;
            $condition->id = $conditionClass->save($condition);
            unset($action->step_id);
            $action->condition_id = $condition->id;
        }
        unset($action->step_id);

        return parent::save($action);
    }
}
