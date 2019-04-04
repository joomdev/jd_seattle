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

class AutomationController extends acymController
{
    public function __construct()
    {
        parent::__construct();
        $this->breadcrumb[acym_translation('ACYM_AUTOMATION')] = acym_completeLink('automation');
        $this->loadScripts = array(
            'all' => array('datepicker'),
        );
        acym_setVar('edition', '1');
    }

    public function listing()
    {
        if (acym_level(2)) {
            acym_session();
            $_SESSION['massAction'] = array('filters' => array(), 'actions' => array());
            acym_setVar('layout', 'listing');
            $pageIdentifier = 'automation';

            $searchFilter = acym_getVar('string', 'automation_search', '');
            $status = acym_getVar('string', 'automation_status', '');
            $tagFilter = acym_getVar('string', 'automation_tag', '');
            $ordering = acym_getVar('string', 'automation_ordering', 'id');
            $orderingSortOrder = acym_getVar('string', 'automation_ordering_sort_order', 'asc');

            $automationsPerPage = acym_getCMSConfig('list_limit', 20);
            $page = empty(acym_getVar('int', 'automation_pagination_page', 1)) ? 1 : acym_getVar('int', 'automation_pagination_page', 1);


            $automationClass = acym_get('class.automation');
            $matchingAutomations = $automationClass->getMatchingAutomations(
                array(
                    'ordering' => $ordering,
                    'search' => $searchFilter,
                    'automationsPerPage' => $automationsPerPage,
                    'offset' => ($page - 1) * $automationsPerPage,
                    'tag' => $tagFilter,
                    'ordering_sort_order' => $orderingSortOrder,
                )
            );

            $pagination = acym_get('helper.pagination');
            $pagination->setStatus($matchingAutomations['total'], $page, $automationsPerPage);

            $automationActive = array();
            $automationInactive = array();

            foreach ($matchingAutomations['automations'] as $automation) {
                if (empty($automation->active)) {
                    $automationInactive[] = $automation;
                } else {
                    $automationActive[] = $automation;
                }
            }

            $filters = array(
                'all' => count($matchingAutomations['automations']),
                'active' => count($automationActive),
                'inactive' => count($automationInactive),
            );


            $data = array(
                'allAutomations' => $matchingAutomations['automations'],
                'allTags' => acym_get('class.tag')->getAllTagsByType('automation'),
                'pagination' => $pagination,
                'search' => $searchFilter,
                'ordering' => $ordering,
                'tag' => $tagFilter,
                'status' => $status,
                'orderingSortOrder' => $orderingSortOrder,
                'automationNumberPerStatus' => $filters,
            );

            parent::display($data);
        }

        if (acym_level(0) && !acym_level(2)) {
            acym_redirect(acym_completeLink('dashboard&task=upgrade&version=enterprise', false, true));
        }
    }

    public function info()
    {
        acym_setVar('layout', 'info');
        acym_setVar('step', 'info');

        $automationId = acym_getVar('int', 'id');
        $automationClass = acym_get('class.automation');
        $stepClass = acym_get('class.step');

        if (empty($automationId)) {
            $automation = new stdClass();
            $step = new stdClass();

            $automation->name = '';
            $automation->description = '';
            $automation->active = 0;
            $this->breadcrumb[acym_translation('ACYM_NEW_AUTOMATION')] = acym_completeLink('automation&task=edit&step=info');
        } else {
            $automation = $automationClass->getOneById($automationId);
            $this->breadcrumb[$automation->name] = acym_completeLink('automation&task=edit&step=info&id='.$automation->id);

            $step = $stepClass->getOneStepByAutomationId($automationId);
        }

        $defaultValues = empty($step->triggers) ? array() : json_decode($step->triggers, true);
        $triggers = array('classic' => array(), 'user' => array());
        acym_trigger('onAcymDeclareTriggers', array(&$triggers, &$defaultValues));

        $data = array(
            'automation' => $automation,
            'step' => $step,
            'user' => $triggers['user'],
            'classic' => $triggers['classic'],
            'defaultValues' => !empty($defaultValues) ? array_keys($defaultValues) : array(),
            'type_trigger' => !empty($defaultValues) ? $defaultValues['type_trigger'] : '',
        );

        parent::display($data);
    }

    public function filter()
    {
        acym_session();
        acym_setVar('layout', 'filter');
        $id = acym_getVar('int', 'id');
        $stepId = acym_getVar('int', 'stepId');
        $automationClass = acym_get('class.automation');
        $stepClass = acym_get('class.step');
        $actionClass = acym_get('class.action');

        $action = new stdClass();
        $step = new stdClass();

        if (!empty($id)) {
            $automation = $automationClass->getOneById($id);
            $this->breadcrumb[$automation->name] = acym_completeLink('automation&task=edit&step=filter&id='.$automation->id);

            $steps = $stepClass->getStepsByAutomationId($id);
            if (!empty($steps)) {
                $step = $steps[0];
                $actions = $actionClass->getActionsByStepId($step->id);
                if (!empty($actions)) $action = $actions[0];
            }
        } else {
            $automation = new stdClass();
            $this->breadcrumb[acym_translation('ACYM_NEW_MASS_ACTION')] = acym_completeLink('automation&task=edit&step=filter');

            $action->filters = json_encode($_SESSION['massAction']['filters']);
        }

        if (empty($action->filters)) $action->filters = '[]';

        $filters = array('user' => array(), 'classic' => array());
        $selectFilter = new stdClass();
        $selectFilter->name = acym_translation('ACYM_SELECT_FILTER');
        $selectFilter->option = '';
        $filters['user'] = array();
        $filters['classic'] = array();
        $filters['both'][0] = $selectFilter;

        $currentFilters = empty($action->filters) ? array() : json_decode($action->filters, true);
        $currentTriggers = empty($step->triggers) ? array() : json_decode($step->triggers, true);
        if (empty($currentFilters)) {
            if (empty($currentTriggers) || $currentTriggers['type_trigger'] != 'user') {
                $typeFilter = 'classic';
            } else {
                $typeFilter = 'user';
            }
        } else {
            $typeFilter = $currentFilters['type_filter'];
        }

        acym_trigger('onAcymDeclareFilters', array(&$filters));

        $filtersUser = array('name' => array(), 'option');
        $filtersClassic = array('name' => array(), 'option');
        foreach ($filters['both'] as $key => $filter) {
            $filtersUser['name'][$key] = $filter->name;
            $filtersUser['option'][$key] = $filter->option;
            $filtersClassic['name'][$key] = $filter->name;
            $filtersClassic['option'][$key] = $filter->option;
        }

        foreach ($filters['user'] as $key => $filter) {
            $filtersUser['name'][$key] = $filter->name;
            $filtersUser['option'][$key] = $filter->option;
        }

        foreach ($filters['classic'] as $key => $filter) {
            $filtersClassic['name'][$key] = $filter->name;
            $filtersClassic['option'][$key] = $filter->option;
        }


        $data = array(
            'automation' => $automation,
            'step' => $step,
            'action' => $action,
            'id' => $id,
            'step_automation_id' => empty($step->id) ? 0 : $step->id,
            'user_name' => $filtersUser['name'],
            'user_option' => json_encode(preg_replace_callback('#(data\-switch=")(switch_.+id=")(switch_.+for=")(switch_)#Uis', array($this, 'switches'), $filtersUser['option'])),
            'classic_name' => $filtersClassic['name'],
            'classic_option' => json_encode(preg_replace_callback('#(data\-switch=")(switch_.+id=")(switch_.+for=")(switch_)#Uis', array($this, 'switches'), $filtersClassic['option'])),
            'type_trigger' => empty($step->triggers) ? 'classic' : json_decode($step->triggers, true)['type_trigger'],
            'type_filter' => $typeFilter,
        );

        parent::display($data);
    }

    public function switches($matches)
    {
        return $matches[1].'__num-and__'.$matches[2].'__num-and__'.$matches[3].'__num-and__'.$matches[4];
    }

    public function action()
    {
        acym_session();
        acym_setVar('layout', 'action');
        $id = acym_getVar('int', 'id');
        $mailId = acym_getVar('string', 'mailid');
        $andMailEditor = acym_getVar('int', 'and');
        $stepClass = acym_get('class.step');
        $automationClass = acym_get('class.automation');
        $actionClass = acym_get('class.action');

        $actionObject = new stdClass();
        $step = new stdClass();

        if (!empty($id)) {
            $automation = $automationClass->getOneById($id);
            $this->breadcrumb[$automation->name] = acym_completeLink('automation&task=edit&step=action&id='.$automation->id);
            $steps = $stepClass->getStepsByAutomationId($id);

            if (!empty($steps)) {
                $step = $steps[0];
                $actionsObject = $actionClass->getActionsByStepId($step->id);
                if (!empty($actionsObject)) $actionObject = $actionsObject[0];
            }
        } else {
            $automation = new stdClass();
            $this->breadcrumb[acym_translation('ACYM_NEW_MASS_ACTION')] = acym_completeLink('automation&task=edit&step=action');

            $actionObject->actions = $_SESSION['massAction']['actions'];
        }

        if (!empty($actionObject->actions) && !is_array($actionObject->actions)) $actionObject->actions = json_decode($actionObject->actions, true);

        if (!empty($actionObject->actions[$andMailEditor]) && !empty($mailId)) {
            $actionObject->actions[$andMailEditor]['acy_add_queue']['mail_id'] = $mailId;
        }


        $actionObject->actions = empty($actionObject->actions) ? '[]' : json_encode($actionObject->actions);

        $actions = array();
        $firstAction = new stdClass();
        $firstAction->name = acym_translation('ACYM_CHOOSE_ACTION');
        $firstAction->option = '';
        $actions[0] = $firstAction;

        acym_trigger('onAcymDeclareActions', array(&$actions));

        $actionsOption = array();

        foreach ($actions as $key => $action) {
            $actionsOption[$key] = $action->name;
        }

        $data = array(
            'automation' => $automation,
            'step' => $step,
            'action' => $actionObject,
            'actionsOption' => $actionsOption,
            'actions' => json_encode($actions),
            'id' => empty($id) ? '' : $id,
            'step_automation_id' => empty($step->id) ? 0 : $step->id,
        );

        parent::display($data);
    }

    public function summary()
    {
        acym_session();
        acym_setVar('layout', 'summary');
        $automationClass = acym_get('class.automation');
        $stepClass = acym_get('class.step');
        $actionClass = acym_get('class.action');
        $id = acym_getVar('int', 'id');
        $massAction = empty($_SESSION['massAction']) ? '' : $_SESSION['massAction'];

        $automation = new stdClass();
        $step = new stdClass();
        $action = new stdClass();

        if (!empty($id)) {
            $automation = $automationClass->getOneById($id);
            $this->breadcrumb[$automation->name] = acym_completeLink('automation&task=edit&step=summary&id='.$automation->id);
            $steps = $stepClass->getStepsByAutomationId($id);

            if (!empty($steps)) {
                $step = $steps[0];
                if (!empty($step->triggers)) $step->triggers = json_decode($step->triggers, true);
                acym_trigger('onAcymDeclareSummary_triggers', array(&$step));

                $actions = $actionClass->getActionsByStepId($step->id);
                if (!empty($actions)) $action = $actions[0];

                if (!empty($action->filters)) $action->filters = json_decode($action->filters, true);

                if (!empty($action->actions)) $action->actions = json_decode($action->actions, true);
            }
        } else if (!empty($massAction)) {
            $action->filters = !empty($massAction['filters']) ? $massAction['filters'] : '';
            $action->actions = !empty($massAction['actions']) ? $massAction['actions'] : '';
            $this->breadcrumb[acym_translation('ACYM_NEW_MASS_ACTION')] = acym_completeLink('automation&task=edit&step=summary');
        }


        if (!empty($action->filters)) {
            foreach ($action->filters as $or => $orValues) {
                if ($or === 'type_filter') continue;
                foreach ($orValues as $and => $andValues) {
                    acym_trigger('onAcymDeclareSummary_filters', array(&$action->filters[$or][$and]));
                }
            }
        }
        if (!empty($action->actions)) {
            foreach ($action->actions as $and => $andValue) {
                acym_trigger('onAcymDeclareSummary_actions', array(&$action->actions[$and]));
            }
        }

        $data = array(
            'id' => $id,
            'automation' => $automation,
            'step' => $step,
            'action' => $action,
        );

        parent::display($data);
    }

    private function _saveInfos($isMassAction = false)
    {
        if ($isMassAction) {
            acym_session();
        }

        $automationId = acym_getVar('int', 'id');
        $automation = acym_getVar('array', 'automation');
        $automationClass = acym_get('class.automation');

        $stepAutomationId = acym_getVar('int', 'stepAutomationId');
        $stepAutomation = acym_getVar('array', 'stepAutomation');
        $stepClass = acym_get('class.step');

        if (!empty($automationId)) {
            $automation['id'] = $automationId;
        }

        if (!empty($stepAutomationId)) {
            $stepAutomation['id'] = $stepAutomationId;
        }

        $typeTrigger = acym_getVar('string', 'type_trigger');

        if (empty($automation['name'])) {
            return false;
        }

        if (empty($stepAutomation['triggers'][$typeTrigger])) {
            acym_enqueueNotification(acym_translation('ACYM_PLEASE_SELECT_ONE_TRIGGER'), 'error', 5000);

            $this->info();

            return false;
        }

        $stepAutomation['triggers'][$typeTrigger]['type_trigger'] = $typeTrigger;
        $stepAutomation['triggers'] = json_encode($stepAutomation['triggers'][$typeTrigger]);

        $stepAutomation['automation_id'] = $automationId;

        foreach ($automation as $column => $value) {
            acym_secureDBColumn($column);
        }

        foreach ($stepAutomation as $stepColumn => $stepValue) {
            acym_secureDBColumn($stepColumn);
        }

        $automation = (object)$automation;
        $stepAutomation = (object)$stepAutomation;

        $automation->id = $automationClass->save($automation);
        $stepAutomation->automation_id = $automation->id;
        $stepAutomation->id = $stepClass->save($stepAutomation);

        $returnIds = array(
            "automationId" => $automation->id,
            "stepId" => $stepAutomation->id,
        );

        if ($isMassAction) {
            return true;
        } elseif (!empty($returnIds['automationId']) && !empty($returnIds['stepId'])) {
            return $returnIds;
        } else {
            return false;
        }
    }

    private function _saveFilters($isMassAction = false)
    {
        $automationID = acym_getVar('int', 'id');
        $actionId = acym_getVar('int', 'actionId');
        $action = acym_getVar('array', 'acym_action', array());
        $actionClass = acym_get('class.action');

        $stepAutomationId = acym_getVar('int', 'stepAutomationId');

        if (!empty($stepAutomationId)) {
            $stepAutomation['id'] = $stepAutomationId;
        }

        if (!empty($actionId)) {
            $action['id'] = $actionId;
        }

        $action['filters']['type_filter'] = acym_getVar('string', 'type_filter');

        if ($isMassAction) {
            acym_session();
            $_SESSION['massAction']['filters'] = $action['filters'];

            return true;
        }

        $action['filters'] = json_encode($action['filters']);

        $action['order'] = 1;

        $action['step_id'] = $stepAutomationId;

        foreach ($action as $column => $value) {
            acym_secureDBColumn($column);
        }

        $action = (object)$action;

        $action->id = $actionClass->save($action);

        $returnIds = array(
            'automationId' => $automationID,
            'stepId' => $stepAutomationId,
            'actionId' => $action->id,
        );

        return $returnIds;
    }

    private function _saveActions($isMassAction = false)
    {
        if ($isMassAction) {
            acym_session();
        }

        $automationID = acym_getVar('int', 'id');
        $stepID = acym_getVar('int', 'id');
        $actionId = acym_getVar('int', 'actionId');
        $action = acym_getVar('array', 'acym_action');
        $actionClass = acym_get('class.action');
        $stepAutomationId = acym_getVar('int', 'stepAutomationId');

        if (!empty($stepAutomationId)) {
            $stepAutomation['id'] = $stepAutomationId;
        }

        if (!empty($actionId)) {
            $action['id'] = $actionId;
        }

        if (empty($action['actions'])) {
            $action['actions'] = array();
        }

        if ($isMassAction) {
            $_SESSION['massAction']['actions'] = $action['actions'];

            return true;
        }

        $action['actions'] = json_encode($action['actions']);

        $action['step_id'] = $stepAutomationId;

        foreach ($action as $column => $value) {
            acym_secureDBColumn($column);
        }

        $action = (object)$action;

        $action->id = $actionClass->save($action);

        $returnIds = array(
            'automationId' => $automationID,
            'stepId' => $stepAutomationId,
            'actionId' => $action->id,
        );

        return $returnIds;
    }

    private function _saveAutomation($from, $isMassAction = false)
    {
        if ($isMassAction) {
            acym_session();
        }

        $automationId = acym_getVar('int', 'id');
        $automation = acym_getVar('array', 'automation');
        $automationClass = acym_get('class.automation');

        $stepAutomationId = acym_getVar('int', 'stepAutomationId');
        $stepAutomation = acym_getVar('array', 'stepAutomation');
        $stepClass = acym_get('class.step');

        if (!empty($automationId)) {
            $automation['id'] = $automationId;
        }

        if (!empty($stepAutomationId)) {
            $stepAutomation['id'] = $stepAutomationId;
        }

        if ($from == 'info') {
            $typeTrigger = acym_getVar('string', 'type_trigger');

            if (empty($automation['name'])) {
                return false;
            }

            if (empty($stepAutomation['triggers'][$typeTrigger])) {
                acym_enqueueNotification(acym_translation('ACYM_PLEASE_SELECT_ONE_TRIGGER'), 'error', 5000);

                $this->info();

                return false;
            }

            $stepAutomation['triggers'][$typeTrigger]['type_trigger'] = $typeTrigger;
            $stepAutomation['triggers'] = json_encode($stepAutomation['triggers'][$typeTrigger]);

            $stepAutomation['automation_id'] = $automationId;

            foreach ($automation as $column => $value) {
                acym_secureDBColumn($column);
            }

            foreach ($stepAutomation as $stepColumn => $stepValue) {
                acym_secureDBColumn($stepColumn);
            }

            $automation = (object)$automation;
            $stepAutomation = (object)$stepAutomation;

            $saveIdStepAutomation = $stepClass->save($stepAutomation);
            $saveIdAutomation = $automationClass->save($automation);

            $returnIds = array(
                "automationId" => $saveIdAutomation,
                "stepId" => $saveIdStepAutomation,
            );

            if ($isMassAction) {
                return true;
            } elseif (!empty($returnIds['automationId']) && !empty($returnIds['stepId'])) {
                return $returnIds;
            } else return false;
        } else if ($from == 'filters') {
            $stepAutomation['filters']['type_filter'] = acym_getVar('string', 'type_filter');
            if ($isMassAction) {
                $_SESSION['massAction']['filters'] = $stepAutomation['filters'];
            }
            $stepAutomation['filters'] = json_encode($stepAutomation['filters']);
        } else if ($from == 'actions') {
            if (empty($stepAutomation['actions'])) {
                acym_enqueueNotification(acym_translation('ACYM_PLEASE_SET_ACTIONS'), 'error', 5000);
                if (!empty($automationId)) acym_setVar('id', $automationId);
                $this->action();

                return false;
            }
            if ($isMassAction) {
                $_SESSION['massAction']['actions'] = $stepAutomation['actions'];
            }
            $stepAutomation['actions'] = json_encode($stepAutomation['actions']);
        } else if ($from == 'summary') {
            $automation = $automationClass->getOneById($automationId);
            $automation->active = 1;
        }

        if ($isMassAction) {
            return true;
        } else {
            switch ($from) {
                case 'info':
                case 'summary':
                    foreach ($automation as $column => $value) {
                        acym_secureDBColumn($column);
                    }

                    $automation = (object)$automation;

                    return $automationClass->save($automation);
                case 'filters':
                case 'actions':
                    $stepAutomation['automation_id'] = $automationId;
                    $stepAutomation['order'] = 1;

                    foreach ($stepAutomation as $column => $value) {
                        acym_secureDBColumn($column);
                    }

                    $stepAutomation = (object)$stepAutomation;

                    return !empty($stepClass->save($stepAutomation)) ? $automationId : false;
                default:
                    return false;
            }
        }
    }

    public function saveExitInfo()
    {
        $ids = $this->_saveInfos();

        if (empty($ids)) {
            return;
        }

        acym_enqueueNotification(acym_translation('ACYM_SUCCESSFULLY_SAVED'), 'success', 8000);

        acym_setVar('id', $ids['automationId']);
        acym_setVar('stepId', $ids['stepId']);
        $this->listing();
    }

    public function saveInfo()
    {
        $ids = $this->_saveInfos();

        if (empty($ids)) {
            return;
        }

        acym_setVar('id', $ids['automationId']);
        acym_setVar('stepId', $ids['stepId']);
        $this->filter();
    }

    public function saveExitFilters()
    {
        $ids = $this->_saveFilters();

        if (empty($ids)) {
            return;
        }

        acym_enqueueNotification(acym_translation('ACYM_SUCCESSFULLY_SAVED'), 'success', 8000);

        $this->listing();
    }

    public function saveFilters()
    {
        $ids = $this->_saveFilters();

        if (empty($ids)) {
            return;
        }

        acym_setVar('id', $ids['automationId']);
        acym_setVar('stepId', $ids['stepId']);
        acym_setVar('actionId', $ids['actionId']);
        $this->action();
    }

    public function saveExitActions()
    {
        $ids = $this->_saveActions();

        if (empty($ids)) {
            return;
        }

        acym_enqueueNotification(acym_translation('ACYM_SUCCESSFULLY_SAVED'), 'success', 8000);

        $this->listing();
    }

    public function saveActions()
    {
        $ids = $this->_saveActions();

        if (empty($ids)) {
            return;
        }

        acym_setVar('id', $ids['automationId']);
        acym_setVar('stepId', $ids['stepId']);
        acym_setVar('actionId', $ids['actionId']);
        $this->summary();
    }

    public function activeAutomation()
    {
        $automationClass = acym_get('class.automation');
        $automation = $automationClass->getOneById(acym_getVar('int', 'id'));
        $automation->active = 1;
        $saved = $automationClass->save($automation);
        if (!empty($saved)) {
            acym_enqueueNotification(acym_translation('ACYM_SUCCESSFULLY_SAVED'), 'success', 8000);
            $this->listing();
        } else {
            acym_enqueueNotification(acym_translation('ACYM_ERROR_SAVING'), 'error', 5000);
            $this->listing();
        }
    }


    public function setFilterMassAction()
    {
        $this->_saveFilters(true);
        $this->action();
    }

    public function setActionMassAction()
    {
        $res = $this->_saveActions(true);
        if (!$res) return false;
        $this->summary();
    }

    function processMassAction()
    {
        acym_session();
        $automationClass = acym_get('class.automation');
        $massAction = empty($_SESSION['massAction']) ? '' : $_SESSION['massAction'];
        if (!empty($massAction)) {
            $automation = new stdClass();
            $automation->filters = json_encode($massAction['filters']);
            $automation->actions = json_encode($massAction['actions']);
            $automationClass->execute($automation);

            if (!empty($automationClass->report)) {
                foreach ($automationClass->report as $oneReport) {
                    acym_enqueueNotification($oneReport, 'info', 5000);
                }
            }
        }
        $this->listing();
    }

    public function createMail()
    {
        $id = acym_getVar('int', 'id');
        $and = acym_getVar('string', 'and_action');
        $this->_saveActions(empty($id));
        $actions = acym_getVar('array', 'acym_action');
        $mailId = $actions['actions'][$and]['acy_add_queue']['mail_id'];
        acym_redirect(acym_completeLink('mails&task=edit&step=editEmail&type=automation&type_editor=acyEditor&from='.$mailId.'&return='.urlencode(acym_completeLink('automation&task=edit&step=action&id='.$id.'&fromMailEditor=1&mailid={mailid}&and='.$and)), false, true));
    }


    public function countresults()
    {
        $or = acym_getVar('int', 'or');
        $and = acym_getVar('int', 'and');
        $stepAutomation = acym_getVar('array', 'acym_action');

        if (empty($stepAutomation['filters'][$or][$and])) die(acym_translation('ACYM_AUTOMATION_NOT_FOUND'));

        $query = acym_get('class.query');
        $messages = '';

        foreach ($stepAutomation['filters'][$or][$and] as $filterName => $options) {
            $messages = acym_trigger('onAcymProcessFilterCount_'.$filterName, array(&$query, &$options, &$and));
            break;
        }

        echo implode(' | ', $messages);
        exit;
    }

    public function countResultsOrTotal()
    {
        $or = acym_getVar('int', 'or');
        $stepAutomation = acym_getVar('array', 'acym_action');

        $query = acym_get('class.query');

        if (!empty($stepAutomation) && !empty($stepAutomation['filters'][$or])) {

            foreach ($stepAutomation['filters'][$or] as $and => $andValues) {
                foreach ($andValues as $filterName => $options) {
                    acym_trigger('onAcymProcessFilter_'.$filterName, array(&$query, &$options, &$and));
                }
            }
        }

        $result = acym_loadObject($query->getQuery(array('COUNT(user.id) as result')));

        echo acym_translation_sprintf('ACYM_SELECTED_USERS_TOTAL', $result->result);
        exit;
    }


}
