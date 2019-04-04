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

class plgAcymSubscriber extends acymPlugin
{
    var $fields = array();

    function dynamicText()
    {
        $onePlugin = new stdClass();
        $onePlugin->name = acym_translation('ACYM_SUBSCRIBER');
        $onePlugin->plugin = __CLASS__;
        $onePlugin->help = 'plugin-subscriber';

        return $onePlugin;
    }

    function textPopup()
    {
        $fieldClass = acym_get('class.field');
        $fieldsUser = acym_getColumns('user');
        $fieldsStats = acym_getColumns('user_stat');
        $fields = array_merge($fieldsUser, $fieldsStats);
        $customFields = $fieldClass->getAllFieldsForUser();
        $descriptions = array();

        foreach ($customFields as $one) {
            $descriptions[$one->id] = acym_translation('ACYM_CUSTOM_FIELD');
            $fields[] = $one->id;
        }


        $descriptions['id'] = acym_translation('ACYM_USER_ID');
        $descriptions['email'] = acym_translation('ACYM_USER_EMAIL');
        $descriptions['name'] = acym_translation('ACYM_USER_NAME');
        $descriptions['cms_id'] = acym_translation('ACYM_USER_CMSID');
        $descriptions['source'] = acym_translation('ACYM_USER_SOURCE');
        $descriptions['confirmed'] = acym_translation('ACYM_USER_CONFIRMED');
        $descriptions['active'] = acym_translation('ACYM_USER_ACTIVE');
        $descriptions['creation_date'] = acym_translation('ACYM_USER_CREATION_DATE');
        $descriptions['open_date'] = acym_translation('ACYM_USER_OPEN_DATE');
        $descriptions['date_click'] = acym_translation('ACYM_USER_CLICK_DATE');
        $descriptions['send_date'] = acym_translation('ACYM_USER_SEND_DATE');

        $text = '<div class="acym__popup__listing text-center grid-x">
					<h1 class="acym__popup__plugin__title cell">'.acym_translation('ACYM_RECEIVER_INFORMATION').'</h1>
					';

        $others = array();
        $others['{subtag:name|part:first|ucfirst}'] = array('name' => acym_translation('ACYM_USER_FIRSTPART'), 'desc' => acym_translation('ACYM_USER_FIRSTPART_DESC'));
        $others['{subtag:name|part:last|ucfirst}'] = array('name' => acym_translation('ACYM_USER_LASTPART'), 'desc' => acym_translation('ACYM_USER_LASTPART_DESC'));

        foreach ($others as $tagname => $tag) {
            $text .= '<div style="cursor:pointer" class="grid-x medium-12 cell acym__listing__row acym__listing__row__popup text-left" onclick="setTag(\''.$tagname.'\', $(this));" ><div class="cell medium-6 small-12 acym__listing__title acym__listing__title__dynamics">'.$tag['name'].'</div><div class="cell medium-6 small-12 acym__listing__title acym__listing__title__dynamics">'.$tag['desc'].'</div></div>';
        }

        foreach ($fields as $fieldname) {
            if (empty($descriptions[$fieldname])) {
                continue;
            }

            $type = '';
            if (in_array($fieldname, array('creation_date', 'open_date', 'date_click', 'send_date'))) {
                $type = '|type:time';
            }

            $text .= '<div style="cursor:pointer" class="grid-x medium-12 cell acym__listing__row acym__listing__row__popup text-left" onclick="setTag(\'{subtag:'.(empty($customFields[$fieldname]) ? $fieldname.$type : 'custom,'.$customFields[$fieldname]->id).'}\', $(this));" >
                        <div class="cell medium-6 small-12 acym__listing__title acym__listing__title__dynamics">'.(empty($customFields[$fieldname]) ? $fieldname : $customFields[$fieldname]->name).'</div>
                        <div class="cell medium-6 small-12 acym__listing__title acym__listing__title__dynamics">'.$descriptions[$fieldname].'</div>
                     </div>';
        }

        $text .= '</div>';

        echo $text;
    }

    function replaceUserInformation(&$email, &$user, $send = true)
    {
        $extractedTags = $this->acympluginHelper->extractTags($email, 'subtag');
        if (empty($extractedTags)) {
            return;
        }

        $tags = array();
        foreach ($extractedTags as $i => $oneTag) {
            if (isset($tags[$i])) {
                continue;
            }
            $tags[$i] = empty($user->id) ? $oneTag->default : $this->replaceSubTag($oneTag, $user);
        }

        $this->acympluginHelper->replaceTags($email, $tags);
    }

    private function replaceSubTag(&$mytag, $user)
    {
        $fieldClass = acym_get('class.field');
        $field = $mytag->id;
        if (strpos($mytag->id, 'custom') === false) {
            $replaceme = (isset($user->$field) && strlen($user->$field) > 0) ? $user->$field : $mytag->default;
        } else {
            $value = empty($user->id) ? "" : $fieldClass->getAllfieldBackEndListingByUserIds($user->id, explode(',', $field)[1]);

            $replaceme = empty($value) ? $mytag->default : $value[explode(',', $field)[1].$user->id];
        }
        $replaceme = nl2br($replaceme);

        $this->acympluginHelper->formatString($replaceme, $mytag);

        return $replaceme;
    }

    function onAcymDeclareTriggers(&$triggers)
    {
        $triggers['user']['user_creation'] = new stdClass();
        $triggers['user']['user_creation']->name = acym_translation('ACYM_ON_USER_CREATION');
        $triggers['user']['user_creation']->option = '<input type="hidden" name="[triggers][user][user_creation][]" value="">';

        $triggers['user']['user_modification'] = new stdClass();
        $triggers['user']['user_modification']->name = acym_translation('ACYM_ON_USER_MODIFICATION');
        $triggers['user']['user_modification']->option = '<input type="hidden" name="[triggers][user][user_modification][]" value="">';

        $triggers['user']['user_click'] = new stdClass();
        $triggers['user']['user_click']->name = acym_translation('ACYM_WHEN_USER_CLICKS_MAIL');
        $triggers['user']['user_click']->option = '<input type="hidden" name="[triggers][user][user_click][]" value="">';

        $triggers['user']['user_open'] = new stdClass();
        $triggers['user']['user_open']->name = acym_translation('ACYM_WHEN_USER_OPEN_MAIL');
        $triggers['user']['user_open']->option = '<input type="hidden" name="[triggers][user][user_open][]" value="">';

        $triggers['user']['user_subscribe'] = new stdClass();
        $triggers['user']['user_subscribe']->name = acym_translation('ACYM_WHEN_USER_SUBSCRIBES');
        $triggers['user']['user_subscribe']->option = '<input type="hidden" name="[triggers][user][user_subscribe][]" value="">';
    }

    function onAcymDeclareFilters(&$filters)
    {
        $userClass = acym_get('class.user');
        $fieldClass = acym_get('class.field');
        $fields = $userClass->getAllColumnsUserAndCustomField();
        unset($fields['automation']);

        $customFields = $fieldClass->getAllFieldsForUser();
        $customFieldValues = array();
        foreach ($customFields as $field) {
            if (in_array($field->type, array('single_dropdown', 'radio', 'checkbox', 'multiple_dropdown')) && !empty($field->value)) {
                $values = array();
                $field->value = json_decode($field->value, true);
                foreach ($field->value as $value) {
                    $valueTmp = new stdClass();
                    $valueTmp->text = $value['title'];
                    $valueTmp->value = $value['value'];
                    if ($value['disabled'] == 'y') $valueTmp->disable = true;
                    $values[$value['value']] = $valueTmp;
                }
                $customFieldValues[$field->id] = '<div class="acym__automation__one-field intext_select_automation cell" style="display: none">';
                $customFieldValues[$field->id] .= acym_select($values, '[filters][__num-or__][__num-and__][acy_field][value]', null, 'class="acym__select acym__automation__filters__fields__select" data-filter-field="'.$field->id.'"');
                $customFieldValues[$field->id] .= '</div>';
            } else if ('date' == $field->type) {
                $field->option = json_decode($field->option, true);
                $customFieldValues[$field->id] = acym_tooltip('<input class="acym__automation__one-field acym__automation__filters__fields__select intext_input_automation cell" type="text" name="[filters][__num-or__][__num-and__][acy_field][value]" style="display: none" data-filter-field="'.$field->id.'">', acym_translation_sprintf('ACYM_DATE_AUTOMATION_INPUT', $field->option['format']), 'intext_select_automation cell');
            }
        }

        $operator = acym_get('type.operator');

        $filters['both']['acy_field'] = new stdClass();
        $filters['both']['acy_field']->name = acym_translation('ACYM_ACYMAILING_FIELD');
        $filters['both']['acy_field']->option = '<div class="intext_select_automation cell">';
        $filters['both']['acy_field']->option .= acym_select($fields, 'acym_action[filters][__num-or__][__num-and__][acy_field][field]', null, 'class="acym__select acym__automation__filters__fields__dropdown"');
        $filters['both']['acy_field']->option .= '</div>';
        $filters['both']['acy_field']->option .= '<div class="intext_select_automation cell">';
        $filters['both']['acy_field']->option .= $operator->display('acym_action[filters][__num-or__][__num-and__][acy_field][operator]', '', 'acym__automation__filters__operator__dropdown');
        $filters['both']['acy_field']->option .= '</div>';
        $filters['both']['acy_field']->option .= '<input class="acym__automation__one-field intext_input_automation cell acym__automation__filter__regular-field" type="text" name="acym_action[filters][__num-or__][__num-and__][acy_field][value]">';
        $filters['both']['acy_field']->option .= implode(' ', $customFieldValues);
    }

    function onAcymProcessFilter_acy_field(&$query, &$filterOptions, $num)
    {
        $usersColumns = acym_getColumns('user');

        if (!in_array($filterOptions['field'], $usersColumns)) {
            $fieldClass = acym_get('class.field');
            $field = $fieldClass->getOneFieldByID($filterOptions['field']);
            if ('date' == $field->type) {
                $filterOptions['value'] = explode('/', $filterOptions['value']);
                $filterOptions['value'] = json_encode($filterOptions['value']);
            }

            $type = 'phone' == $field->type ? 'phone' : '';

            $query->leftjoin['userfield'.$num] = ' #__acym_user_has_field as userfield'.$num.' ON userfield'.$num.'.user_id = user.id AND userfield'.$num.'.field_id = '.intval($filterOptions['field']);
            $query->where[] = $query->convertQuery('userfield'.$num, 'value', $filterOptions['operator'], $filterOptions['value'], $type);
        } else {
            if ($filterOptions['field'] == 'creation_date') $filterOptions['value'] = acym_date($filterOptions['value'], "Y-m-d H:i:s");
            $query->where[] = $query->convertQuery('user', $filterOptions['field'], $filterOptions['operator'], $filterOptions['value']);
        }
    }

    function onAcymProcessFilterCount_acy_field(&$query, $options, $num)
    {
        $this->onAcymProcessFilter_acy_field($query, $options, $num);

        return acym_translation_sprintf('ACYM_SELECTED_USERS', $query->count());
    }

    function onAcymDeclareActions(&$actions)
    {
        $userActions = array(
            'confirm' => acym_translation('ACYM_CONFIRM_USER'),
            'unconfirm' => acym_translation('ACYM_UNCONFIRM_USER'),
            'active' => acym_translation('ACYM_ACTIVE_USER'),
            'block' => acym_translation('ACYM_BLOCK_USER'),
            'delete' => acym_translation('ACYM_DELETE_USER'),
        );

        $actions['acy_user'] = new stdClass();
        $actions['acy_user']->name = acym_translation('ACYM_ACTION_ON_USERS');
        $actions['acy_user']->option = '<div class="intext_select_automation cell">'.acym_select($userActions, 'acym_action[actions][__and__][acy_user][action]', null, 'class="acym__select"').'</div>';


        $userClass = acym_get('class.user');
        $userFields = $userClass->getAllColumnsUserAndCustomField(true);
        unset($userFields['id']);
        unset($userFields['cms_id']);
        unset($userFields['key']);
        unset($userFields['active']);
        unset($userFields['source']);
        unset($userFields['confirmed']);
        unset($userFields['automation']);
        unset($userFields['creation_date']);

        $userOperator = array(
            '=' => '=',
            '-' => '-',
            '+' => '+',
            'add_end' => acym_translation('ACYM_ADD_AT_END'),
            'add_begin' => acym_translation('ACYM_ADD_AT_BEGINNING'),
        );

        $fieldClass = acym_get('class.field');
        $customFields = $fieldClass->getAllFieldsForUser();
        $customFieldValues = array();
        foreach ($customFields as $field) {
            if (in_array($field->type, array('single_dropdown', 'radio', 'checkbox', 'multiple_dropdown')) && !empty($field->value)) {
                $values = array();
                $field->value = json_decode($field->value, true);
                foreach ($field->value as $value) {
                    $valueTmp = new stdClass();
                    $valueTmp->text = $value['title'];
                    $valueTmp->value = $value['value'];
                    if ($value['disabled'] == 'y') $valueTmp->disable = true;
                    $values[$value['value']] = $valueTmp;
                }
                $customFieldValues[$field->id] = '<div class="acym__automation__one-field intext_select_automation cell" style="display: none">';
                $customFieldValues[$field->id] .= acym_select($values, '[actions][__and__][acy_user_value][value]', null, 'class="acym__select acym__automation__actions__fields__select" data-action-field="'.$field->id.'"');
                $customFieldValues[$field->id] .= '</div>';
            } else if ('date' == $field->type) {
                $field->option = json_decode($field->option, true);
                $customFieldValues[$field->id] = acym_tooltip('<input class="acym__automation__one-field acym__automation__actions__fields__select intext_input_automation cell" type="text" name="[actions][__and__][acy_user_value][value]" style="display: none" data-action-field="'.$field->id.'">', acym_translation_sprintf('ACYM_DATE_AUTOMATION_INPUT', $field->option['format']), 'intext_select_automation cell');
            }
        }

        $actions['acy_user_value'] = new stdClass();
        $actions['acy_user_value']->name = acym_translation('ACYM_SET_USER_VALUE');
        $actions['acy_user_value']->option = '<div class="intext_select_automation">'.acym_select($userFields, 'acym_action[actions][__and__][acy_user_value][field]', null, 'class="acym__select acym__automation__actions__fields__dropdown"').'</div><div class="intext_select_automation cell">'.acym_select($userOperator, 'acym_action[actions][__and__][acy_user_value][operator]', null, 'class="acym__select acym__automation__actions__operator__dropdown"').'</div><input type="text" name="acym_action[actions][__and__][acy_user_value][value]" class="intext_input_automation cell acym__automation__one-field acym__automation__action__regular-field">';
        $actions['acy_user_value']->option .= implode(' ', $customFieldValues);

        $mailClass = acym_get('class.mail');

        $mailRemove = $mailClass->getAllTemplateForSelect(true);
        $actions['acy_remove_queue'] = new stdClass();
        $actions['acy_remove_queue']->name = acym_translation('ACYM_REMOVE_EMAIL_QUEUE');
        $actions['acy_remove_queue']->option = '<div class="intext_select_automation">'.acym_select($mailRemove, 'acym_action[actions][__and__][acy_remove_queue][mail_id]', null, 'class="acym__select"').'</div>';
    }

    function onAcymProcessAction_acy_user(&$query, $action)
    {
        if ($action['action'] == 'delete') {
            $userClass = acym_get('class.user');
            $usersToDelete = acym_loadResultArray($query->getQuery(array('user.id')));
            if (!empty($usersToDelete)) $userClass->delete($usersToDelete);
        } else {
            $fieldToUpdate = '';
            if ($action['action'] == 'confirm') $fieldToUpdate = 'confirmed = 1';
            if ($action['action'] == 'unconfirm') $fieldToUpdate = 'confirmed = 0';
            if ($action['action'] == 'active') $fieldToUpdate = 'active = 1';
            if ($action['action'] == 'block') $fieldToUpdate = 'active = 0';

            $queryToProcess = 'UPDATE #__acym_user AS user SET '.$fieldToUpdate.' WHERE ('.implode(') AND (', $query->where).')';
            $nbRows = acym_query($queryToProcess);

            return acym_translation_sprintf('ACYM_X_USERS_X', $nbRows, acym_translation('ACYM_ACTION_'.strtoupper($action['action'])));
        }
    }

    function onAcymProcessAction_acy_user_value(&$query, $action)
    {
        $value = $action['value'];

        $replace = array('{year}', '{month}', '{weekday}', '{day}');
        $replaceBy = array(date('Y'), date('m'), date('N'), date('d'));
        $value = str_replace($replace, $replaceBy, $value);

        if (preg_match_all('#{(year|month|weekday|day)\|(add|remove):([^}]*)}#Uis', $value, $results)) {
            foreach ($results[0] as $i => $oneMatch) {
                $format = str_replace(array('year', 'month', 'weekday', 'day'), array('Y', 'm', 'N', 'd'), $results[1][$i]);
                $delay = str_replace(array('add', 'remove'), array('+', '-'), $results[2][$i]).intval($results[3][$i]).' '.str_replace('weekday', 'day', $results[1][$i]);
                $value = str_replace($oneMatch, date($format, strtotime($delay)), $value);
            }
        }

        if (empty($action['operator'])) $action['operator'] = '=';

        if (in_array($action['operator'], array('+', '-'))) {
            $value = intval($value);
        } else {
            $value = acym_escapeDB($value);
        }

        $where = $query->where;
        $usersColumns = acym_getColumns('user');

        if (in_array($action['field'], $usersColumns)) {
            $execute = 'UPDATE #__acym_user AS user';

            $column = "user.`".acym_secureDBColumn($action['field'])."`";
        } else {
            $fieldClass = acym_get('class.field');
            $field = $fieldClass->getOneFieldById($action['field']);
            if (empty($field)) return 'Unknown field: '.$action['field'];
            if ('date' == $field->type) $value = acym_escapeDB(json_encode(explode('/', $value)));

            $allColumn = "`user_id`, `field_id`, `value`";
            $column = "`value`";
        }

        if ($action['operator'] == '=') {
            $newValue = $value;
        } elseif (in_array($action['operator'], array('+', '-'))) {
            $newValue = $column.' '.$action['operator']." ".$value;
        } elseif ($action['operator'] == 'add_end') {
            $newValue = "CONCAT(".$column.", ".$value.")";
        } elseif ($action['operator'] == 'add_begin') {
            $newValue = "CONCAT(".$value.", ".$column.")";
        } else {
            return 'Unknown operator: '.acym_escape($action['operator']);
        }

        if (in_array($action['field'], $usersColumns)) {
            $execute .= " SET ".$column." = ".$newValue;
            if (!empty($where)) $execute .= ' WHERE ('.implode(') AND (', $where).')';
        } else {
            $customFieldAlreadyExists = acym_loadResult('SELECT COUNT(user_id) FROM #__acym_user_has_field WHERE field_id = '.$action['field']);
            $execute = 'INSERT INTO #__acym_user_has_field ('.$allColumn.') SELECT id as user_id, '.$action['field'].' as field_id, '.$newValue.' as value FROM #__acym_user as user WHERE ('.implode(') AND (', $where).') ON DUPLICATE KEY UPDATE '.$column.' = '.$newValue;
        }

        $nbAffected = acym_query($execute);

        if (!empty($customFieldAlreadyExists)) {
            $nbAffected -= $customFieldAlreadyExists;
        }

        return acym_translation_sprintf('ACYM_UPDATED_USERS', $nbAffected);
    }

    function onAcymProcessAction_acy_add_queue(&$query, &$action)
    {
        if (empty($action['time'])) return;
        $sendDate = acym_replaceDate($action['time']);
        $sendDate = acym_date($sendDate, "Y-m-d H:i:s", false);
        $campaignClass = acym_get('class.campaign');
        $mailClass = acym_get('class.mail');

        $mail = $mailClass->getOneById($action['mail_id']);
        $mailId = $mail->id;

        $newCampaign = $campaignClass->getOneCampaignByMailId($mailId);

        if (empty($newCampaign)) {
            $newCampaign = new stdClass();
            $newCampaign->sending_date = $sendDate;
            $newCampaign->draft = 0;
            $newCampaign->active = 1;
            $newCampaign->mail_id = $mailId;
            $newCampaign->scheduled = 0;
            $newCampaign->sent = 0;
            $newCampaign->automation = 1;
            $campaignId = $campaignClass->save($newCampaign);
        } else {
            if (empty($newCampaign->automation)) {
                $newCampaign->automation = 1;
                unset($newCampaign->id);
                $newCampaign->id = $campaignClass->save($newCampaign);
            }
            $campaignId = $newCampaign->id;
        }

        $userIds = acym_loadResultArray($query->getQuery(array('user.id')));
        $nbRows = $campaignClass->sendAutomation($campaignId, $userIds);

        return acym_translation_sprintf('ACYM_EMAILS_ADDED_QUEUE', $nbRows);
    }

    function onAcymProcessAction_acy_remove_queue(&$query, $action)
    {
        $queryToProcess = 'DELETE FROM #__acym_queue WHERE user_id IN ('.$query->getQuery(array('user.id')).')';
        $nbRows = acym_query($queryToProcess);

        return acym_translation_sprintf('ACYM_EMAILS_REMOVED_QUEUE', $nbRows);
    }

    function onAcymAfterUserCreate(&$user)
    {
        $automationClass = acym_get('class.automation');
        $automationClass->triggerUser('user_creation', $user->id);
    }

    function onAcymAfterUserModify(&$user)
    {
        $automationClass = acym_get('class.automation');
        $automationClass->triggerUser('user_modification', $user->id);
    }

    function onAcymDeclareSummary_filters(&$automationFilter)
    {
        if (!empty($automationFilter['acy_field'])) {

            $usersColumns = acym_getColumns('user');

            if (!in_array($automationFilter['acy_field']['field'], $usersColumns)) {
                $fieldClass = acym_get('class.field');
                $field = $fieldClass->getOneFieldById($automationFilter['acy_field']['field']);
                $automationFilter['acy_field']['field'] = $field->name;
            }
            $automationFilter = acym_translation_sprintf('ACYM_FILTER_ACY_FIELD_SUMMARY', $automationFilter['acy_field']['field'], $automationFilter['acy_field']['operator'], $automationFilter['acy_field']['value']);
        }
    }

    function onAcymDeclareSummary_actions(&$automationAction)
    {
        if (!empty($automationAction['acy_user'])) {
            $userActions = array(
                'confirm' => acym_translation('ACYM_WILL_CONFIRM'),
                'unconfirm' => acym_translation('ACYM_WILL_UNCONFIRM'),
                'active' => acym_translation('ACYM_WILL_ACTIVE'),
                'block' => acym_translation('ACYM_WILL_BLOCK'),
                'delete' => acym_translation('ACYM_WILL_DELETE'),
            );
            $automationAction = $userActions[$automationAction['acy_user']['action']];
        }
        if (!empty($automationAction['acy_user_value'])) {
            $usersColumns = acym_getColumns('user');

            if (!in_array($automationAction['acy_user_value']['field'], $usersColumns)) {
                $fieldClass = acym_get('class.field');
                $field = $fieldClass->getOneFieldById($automationAction['acy_user_value']['field']);
                $automationAction['acy_user_value']['field'] = $field->name;
            }
            $automationAction = acym_translation_sprintf('ACYM_ACTION_USER_VALUE_SUMMARY', $automationAction['acy_user_value']['field'], $automationAction['acy_user_value']['operator'], $automationAction['acy_user_value']['value']);
        }
    }

    function onAcymDeclareSummary_triggers(&$automation)
    {
        if (!empty($automation->triggers['user_open'])) $automation->triggers['user_open'] = acym_translation('ACYM_WHEN_USER_OPEN_MAIL');
        if (!empty($automation->triggers['user_click'])) $automation->triggers['user_click'] = acym_translation('ACYM_WHEN_USER_CLICKS_MAIL');
        if (!empty($automation->triggers['user_modification'])) $automation->triggers['user_modification'] = acym_translation('ACYM_ON_USER_MODIFICATION');
        if (!empty($automation->triggers['user_creation'])) $automation->triggers['user_creation'] = acym_translation('ACYM_ON_USER_CREATION');
        if (!empty($automation->triggers['user_subscribe'])) $automation->triggers['user_subscribe'] = acym_translation('ACYM_WHEN_USER_SUBSCRIBES');
    }
}
