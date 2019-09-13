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

class acymactionClass extends acymClass
{
    var $table = 'action';
    var $pkey = 'id';

    public function getActionsByStepId($stepId)
    {
        $query = 'SELECT action.* FROM #__acym_action AS action LEFT JOIN #__acym_condition AS conditionT ON action.condition_id = conditionT.id WHERE conditionT.step_id = '.intval($stepId).' ORDER BY action.order';

        return acym_loadObjectList($query);
    }

    public function getActionsByConditionId($id)
    {
        $query = 'SELECT action.* FROM #__acym_action as action LEFT JOIN #__acym_condition as acycondition ON acycondition.id = action.condition_id WHERE acycondition.id = '.intval($id);

        return acym_loadObjectList($query);
    }

    public function getAll()
    {
        return acym_loadObjectList('SELECT * FROM #__acym_action');
    }
}
