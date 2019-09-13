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

class acymautomationClass extends acymClass
{

    var $table = 'automation';
    var $pkey = 'id';
    var $didAnAction = false;
    var $report = [];

    public function getMatchingAutomations($settings)
    {
        $query = 'SELECT * FROM #__acym_automation';
        $queryCount = 'SELECT COUNT(id) FROM #__acym_automation';
        $filters = [];

        if (!empty($settings['search'])) {
            $filters[] = 'name LIKE '.acym_escapeDB('%'.$settings['search'].'%');
        }

        if (!empty($filters)) {
            $query .= ' WHERE ('.implode(') AND (', $filters).')';
            $queryCount .= ' WHERE ('.implode(') AND (', $filters).')';
        }

        if (!empty($settings['ordering']) && !empty($settings['ordering_sort_order'])) {
            $query .= ' ORDER BY '.acym_secureDBColumn($settings['ordering']).' '.acym_secureDBColumn(strtoupper($settings['ordering_sort_order']));
        } else {
            $query .= ' ORDER BY id asc';
        }
        $results['automations'] = acym_loadObjectList($query, '', $settings['offset'], $settings['automationsPerPage']);


        $results['total'] = acym_loadResult($queryCount);

        return $results;
    }

    public function getOneById($id)
    {
        $query = 'SELECT * FROM #__acym_automation WHERE `id` = '.intval($id);

        return acym_loadObject($query);
    }

    public function save($automation)
    {
        foreach ($automation as $oneAttribute => $value) {
            if (empty($value)) {
                continue;
            }

            if (is_array($value)) {
                $value = json_encode($value);
            }

            $automation->$oneAttribute = strip_tags($value);
        }

        return parent::save($automation);
    }

    public function delete($elements)
    {
        if (!is_array($elements)) {
            $elements = [$elements];
        }
        acym_arrayToInteger($elements);

        if (empty($elements)) {
            return 0;
        }

        $steps = acym_loadResultArray('SELECT id FROM #__acym_step WHERE automation_id IN ('.implode(',', $elements).')');
        $stepClass = acym_get('class.step');
        $stepsDeleted = $stepClass->delete($steps);

        return parent::delete($elements);
    }

    public function trigger($trigger, $data = [])
    {
        if (!acym_level(2)) return;

        $stepClass = acym_get('class.step');
        $actionClass = acym_get('class.action');
        $conditionClass = acym_get('class.condition');
        $steps = $stepClass->getActiveStepByTrigger($trigger);

        $data['time'] = time();
        foreach ($steps as $step) {
            $execute = false;

            if (!empty($step->next_execution) && $step->next_execution <= $data['time']) {
                $execute = true;
            }

            acym_trigger('onAcymExecuteTrigger', [&$step, &$execute, $data]);

            $data['automation'] = $this->getOneById($step->automation_id);

            if ($execute) {
                $step->last_execution = $data['time'];
                $conditions = $conditionClass->getConditionsByStepId($step->id);
                if (!empty($conditions)) {
                    foreach ($conditions as $condition) {
                        if (!$this->_verifyCondition($condition->conditions, $data)) continue;

                        $actions = $actionClass->getActionsByStepId($step->id);
                        if (empty($actions)) continue;

                        foreach ($actions as $action) {
                            $this->execute($action, $data);
                        }
                    }
                }
            }

            $stepClass->save($step);
        }
    }

    public function execute($action, $data = [])
    {
        $userTriggeringAction = empty($data['userId']) ? 0 : $data['userId'];
        $action->actions = json_decode($action->actions, true);
        if (empty($action->actions)) return false;

        $isMassAction = false;
        static $massAction = 0;
        if (empty($action->id)) {
            $action->id = $massAction--;
            $isMassAction = true;
        }

        $action->filters = json_decode($action->filters, true);
        if (empty($action->filters)) return false;


        $query = acym_get('class.query');

        $initialWhere = ['1 = 1'];
        $query->removeFlag($action->id);

        if (!empty($action->filters['type_filter']) && $action->filters['type_filter'] == 'user') {
            $initialWhere = ['user.id = '.intval($userTriggeringAction)];
        }

        $typeFilter = $action->filters['type_filter'];

        unset($action->filters['type_filter']);
        if (empty($action->filters)) {
            $query->where = $initialWhere;
        }

        foreach ($action->filters as $or => $orValue) {
            if (empty($orValue)) {
                continue;
            }
            $num = 0;
            $query->where = $initialWhere;
            foreach ($orValue as $and => $andValue) {
                $num++;
                foreach ($andValue as $filterName => $filterOptions) {
                    acym_trigger('onAcymProcessFilter_'.$filterName, [&$query, &$filterOptions, &$num]);
                }
            }

            $query->addFlag($action->id);
        }

        $this->didAnAction = $this->didAnAction || $query->count() > 0;
        foreach ($action->actions as $and => $andValue) {
            foreach ($andValue as $actionName => $actionOptions) {
                $this->report = array_merge(
                    $this->report,
                    acym_trigger(
                        'onAcymProcessAction_'.$actionName,
                        [&$query, &$actionOptions, ['automationAdmin' => !empty($data['automation']->admin), 'user_id' => $userTriggeringAction]]
                    )
                );
                $action->actions[$and][$actionName] = $actionOptions;
            }
        }

        if (!$isMassAction) {
            $action->filters['type_filter'] = $typeFilter;
            $action->filters = json_encode($action->filters);
            $action->actions = json_encode($action->actions);
            $actionClass = acym_get('class.action');
            $actionClass->save($action);
        }

        $query->removeFlag($action->id);

        return $this->didAnAction;
    }

    private function _verifyCondition($conditions, $data = [])
    {
        if (empty($conditions)) return true;
        $userTriggeringAction = empty($data['userId']) ? 0 : $data['userId'];

        $conditions = json_decode($conditions, true);
        $query = acym_get('class.query');
        $initialWhere = ['1 = 1'];
        if (!empty($conditions['type_condition']) && $conditions['type_condition'] == 'user') {
            $initialWhere = ['user.id = '.intval($userTriggeringAction)];
        }
        unset($conditions['type_condition']);

        if (empty($conditions)) return true;

        foreach ($conditions as $or => $orValue) {
            if (empty($orValue)) continue;

            $conditionNotValid = 0;
            $num = 0;
            foreach ($orValue as $and => $andValue) {
                $num++;
                $query->where = $initialWhere;
                foreach ($andValue as $filterName => $filterOptions) {
                    acym_trigger('onAcymProcessCondition_'.$filterName, [&$query, &$filterOptions, &$num, &$conditionNotValid]);
                }
            }

            if ($conditionNotValid == 0) return true;
        }

        return false;
    }

    public function getAutomationsAdmin($ids = [])
    {
        acym_arrayToInteger($ids);

        $query = 'SELECT * FROM #__acym_automation WHERE `admin` = 1';
        if (!empty($ids)) $query .= ' AND `id` IN ('.implode(', ', $ids).')';

        return acym_loadObjectList($query, 'name');
    }
}

