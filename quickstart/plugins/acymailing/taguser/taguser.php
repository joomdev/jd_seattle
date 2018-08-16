<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php

class plgAcymailingTaguser extends JPlugin
{

    var $sendervalues = array();

    function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);
        if (!isset($this->params)) {
            $plugin = JPluginHelper::getPlugin('acymailing', 'taguser');
            $this->params = new acyParameter($plugin->params);
        }
    }

    function acymailing_getPluginType()
    {
        if ($this->params->get('frontendaccess') == 'none' && !acymailing_isAdmin()) {
            return;
        }
        $onePlugin = new stdClass();
        $onePlugin->name = acymailing_translation('TAGUSER_TAGUSER');
        $onePlugin->function = 'acymailingtaguser_show';
        $onePlugin->help = 'plugin-taguser';

        return $onePlugin;
    }

    function acymailingtaguser_show()
    {
        ?>

        <script language="javascript" type="text/javascript">
            function applyTag(tagname){
                var string = '{usertag:' + tagname;
                for(var i = 0; i < document.adminForm.typeinfo.length; i++){
                    if(document.adminForm.typeinfo[i].checked){
                        string += '|info:' + document.adminForm.typeinfo[i].value;
                    }
                }
                string += '}';
                setTag(string);
                insertTag();
            }
        </script>
        <?php
        $typeinfo = array();
        $typeinfo[] = acymailing_selectOption("receiver", acymailing_translation('RECEIVER_INFORMATION'));
        $typeinfo[] = acymailing_selectOption("sender", acymailing_translation('SENDER_INFORMATIONS'));
        echo acymailing_radio($typeinfo, 'typeinfo', '', 'value', 'text', 'receiver');


        $notallowed = array('password', 'params', 'sendemail', 'gid', 'block', 'email', 'name', 'id');
        $text = '<div class="onelineblockoptions"><table class="acymailing_table" cellpadding="1">';
        $fields = acymailing_getColumns('#__users');
        if (ACYMAILING_J30) {
            $fields = array_merge($fields, array('usertype' => 'usertype'));
        }

        $descriptions['username'] = acymailing_translation('TAGUSER_USERNAME');
        $descriptions['usertype'] = acymailing_translation('TAGUSER_GROUP');
        $descriptions['lastvisitdate'] = acymailing_translation('TAGUSER_LASTVISIT');
        $descriptions['registerdate'] = acymailing_translation('TAGUSER_REGISTRATION');

        $k = 0;
        foreach ($fields as $fieldname => $oneField) {
            if (in_array(strtolower($fieldname), $notallowed)) {
                continue;
            }
            $type = '';
            if (strpos(strtolower($oneField), 'date') !== false) {
                $type = '|type:date';
            }
            $text .= '<tr style="cursor:pointer" class="row'.$k.'" onclick="applyTag(\''.$fieldname.$type.'\');" ><td class="acytdcheckbox"></td><td>'.$fieldname.'</td><td>'.@$descriptions[strtolower($fieldname)].'</td></tr>';
            $k = 1 - $k;
        }

        if (ACYMAILING_J16) {
            $extraFields = acymailing_loadObjectList('SELECT DISTINCT `profile_key` FROM `#__user_profiles`');
            if (!empty($extraFields)) {
                foreach ($extraFields as $oneField) {
                    $text .= '<tr style="cursor:pointer" class="row'.$k.'" onclick="applyTag(\''.$oneField->profile_key.'|type:extra\');" ><td class="acytdcheckbox"></td><td>'.$oneField->profile_key.'</td><td></td></tr>';
                    $k = 1 - $k;
                }
            }
        }
        if (ACYMAILING_J30) {
            $link = 'index.php/component/users/?task=registration.activate&token={usertag:activation|info:receiver}';
        } elseif (ACYMAILING_J16) {
            $link = 'index.php?option=com_users&task=registration.activate&token={usertag:activation|info:receiver}';
        } else {
            $link = 'index.php?option=com_user&task=activate&activation={usertag:activation|info:receiver}';
        }
        $text .= '<tr style="cursor:pointer" class="row'.$k.'" onclick="setTag(\''.htmlentities('<a target="_blank" href="'.$link.'">'.acymailing_translation('JOOMLA_CONFIRM_ACCOUNT').'</a>').'\'); insertTag();" ><td class="acytdcheckbox"></td><td>confirmJoomla</td><td>'.acymailing_translation('JOOMLA_CONFIRM_LINK').'</td></tr>';
        $text .= '</table></div>';

        $jversion = preg_replace('#[^0-9\.]#i', '', JVERSION);
        if (version_compare($jversion, '3.7.0', '>=')) {
            $query = 'SELECT id, title FROM #__fields_groups WHERE context = "com_users.user" AND state = 1 ORDER BY title ASC';
            $groups = acymailing_loadObjectList($query);
            $defaultGroup = new stdClass();
            $defaultGroup->id = 0;
            $defaultGroup->title = acymailing_translation('ACY_NO_GROUP');
            array_unshift($groups, $defaultGroup);

            $query = 'SELECT id, title, group_id FROM #__fields WHERE context = "com_users.user" AND state = 1 ORDER BY title ASC';
            $customFields = acymailing_loadObjectList($query);

            if (!empty($customFields)) {
                $text .= '<div class="onelineblockoptions">
							<span class="acyblocktitle">'.acymailing_translation('EXTRA_FIELDS').'</span>
							<table class="acymailing_table" cellpadding="1">';
                foreach ($groups as $oneGroup) {
                    $openedGroup = false;
                    foreach ($customFields as $oneCF) {
                        if ($oneCF->group_id != $oneGroup->id) {
                            continue;
                        }
                        if (!$openedGroup) {
                            $text .= '<tr><td></td><td style="font-weight: bold;">'.$oneGroup->title.'</td><td></td></tr>';
                            $openedGroup = true;
                        }
                        $text .= '<tr style="cursor:pointer" onclick="applyTag(\''.$oneCF->id.'|type:custom\');" ><td class="acytdcheckbox"></td><td>'.$oneCF->title.'</td><td></td></tr>';
                    }
                }
                $text .= '</table></div>';
            }
        }


        echo $text;
    }

    function acymailing_replaceusertags(&$email, &$user, $send = true)
    {
        $pluginsHelper = acymailing_get('helper.acyplugins');
        $extractedTags = $pluginsHelper->extractTags($email, 'usertag');
        if (empty($extractedTags)) {
            return;
        }

        $jversion = preg_replace('#[^0-9\.]#i', '', JVERSION);
        if (empty($this->customFields) && version_compare($jversion, '3.7.0', '>=')) {
            $this->customFields = acymailing_loadObjectList('SELECT * FROM #__fields WHERE context = "com_users.user"', 'id');
            foreach ($this->customFields as &$oneCF) {
                if (!empty($oneCF->fieldparams)) {
                    $oneCF->fieldparams = json_decode($oneCF->fieldparams, true);
                }
            }
        }

        $tags = array();
        $receivervalues = array();
        foreach ($extractedTags as $i => $mytag) {
            if (isset($tags[$i])) {
                continue;
            }
            $mytag->default = $this->params->get('default_'.$mytag->id, '');

            $values = new stdClass();
            $idused = 0;
            $save = false;

            if (!empty($mytag->info) && $mytag->info == 'sender' && !empty($email->userid)) {
                $idused = $email->userid;
                $save = true;
            }
            if (!empty($mytag->info) && $mytag->info == 'current') {
                $currentUserid = acymailing_currentUserId();
                if (!empty($currentUserid)) {
                    $idused = $currentUserid;
                }
            }
            if ((empty($mytag->info) || $mytag->info == 'receiver') && !empty($user->userid)) {
                $idused = $user->userid;
            }

            if (!empty($idused) && empty($this->sendervalues[$idused]) && empty($receivervalues[$idused])) {
                $receivervalues[$idused] = acymailing_loadObject('SELECT * FROM '.acymailing_table('users', false).' WHERE id = '.intval($idused).' LIMIT 1');

                if (ACYMAILING_J16) {
                    $receivervalues[$idused]->extraFields = acymailing_loadObjectList('SELECT * FROM #__user_profiles WHERE user_id = '.intval($idused), 'profile_key');
                }

                if ($save) {
                    $this->sendervalues[$idused] = $receivervalues[$idused];
                }
            }

            if (!empty($this->sendervalues[$idused])) {
                $values = $this->sendervalues[$idused];
            } elseif (!empty($receivervalues[$idused])) {
                $values = $receivervalues[$idused];
            }

            if ($mytag->id == 'usertype' && ACYMAILING_J16) {
                if (empty($this->acyuserHelper)) {
                    $this->acyuserHelper = acymailing_get('helper.acyuser');
                }
                $groups = $this->acyuserHelper->getUserGroups($idused);
                $allGroups = array();
                foreach ($groups as $oneGroup) {
                    $allGroups[] = $oneGroup->title;
                }
                $values->usertype = implode(', ', $allGroups);
            }

            if (empty($mytag->type)) {
                $mytag->type = '';
            }
            if ($mytag->type == 'extra') {
                $replaceme = isset($values->extraFields[$mytag->id]) ? trim(json_decode($values->extraFields[$mytag->id]->profile_value), '"') : $mytag->default;
            } elseif ($mytag->type == 'custom') {
                $mytag->id = intval($mytag->id);
                if (empty($mytag->id)) {
                    $replaceme = '';
                } else {
                    $userFieldVals = acymailing_loadResultArray('SELECT value FROM #__fields_values WHERE item_id = '.intval($idused).' AND field_id = '.intval($mytag->id));

                    $fieldValues = trim(implode(', ', $userFieldVals), ', ');
                    if (empty($fieldValues)) {
                        $defaultValue = acymailing_loadObject('SELECT default_value, type FROM #__fields WHERE id = '.intval($mytag->id));
                        if (($defaultValue->type == 'user' && !empty($defaultValue->default_value)) || ($defaultValue->type != 'user' && strlen($defaultValue->default_value) > 0)) {
                            $userFieldVals = array($defaultValue->default_value);
                        }
                    }

                    foreach ($userFieldVals as &$oneFieldVal) {
                        switch ($this->customFields[$mytag->id]->type) {
                            case 'radio':
                            case 'list':
                            case 'checkboxes':
                                foreach ($this->customFields[$mytag->id]->fieldparams['options'] as $oneOPT) {
                                    if ($oneOPT['value'] == $oneFieldVal) {
                                        $oneFieldVal = $oneOPT['name'];
                                        break;
                                    }
                                }
                                break;

                            case 'usergrouplist':
                                if (empty($this->usergroups)) {
                                    $this->usergroups = acymailing_loadObjectList('SELECT id, title FROM #__usergroups', 'id');
                                }

                                $oneFieldVal = $this->usergroups[$oneFieldVal]->title;
                                break;

                            case 'imagelist':
                                if (strlen($this->customFields[$mytag->id]->fieldparams['directory']) > 1) {
                                    $oneFieldVal = '/'.$oneFieldVal;
                                } else {
                                    $this->customFields[$mytag->id]->fieldparams['directory'] = '';
                                }
                                $oneFieldVal = '<img src="images/'.$this->customFields[$mytag->id]->fieldparams['directory'].$oneFieldVal.'" />';
                                break;

                            case 'url':
                                $oneFieldVal = '<a target="_blank" href="'.$oneFieldVal.'">'.$oneFieldVal.'</a>';
                                break;

                            case 'sql':
                                if (empty($this->customFields[$mytag->id]->options)) {
                                    $this->customFields[$mytag->id]->options = acymailing_loadObjectList($this->customFields[$mytag->id]->fieldparams['query'], 'value');
                                }

                                $oneFieldVal = $this->customFields[$mytag->id]->options[$oneFieldVal]->text;
                                break;

                            case 'user':
                                $oneFieldVal = acymailing_currentUserName($oneFieldVal);
                                break;

                            case 'media':
                                $oneFieldVal = '<img src="'.$oneFieldVal.'" />';
                                break;

                            case 'calendar':
                                $format = $this->customFields[$mytag->id]->fieldparams['showtime'] == '1' ? 'Y-m-d H:i' : 'Y-m-d';
                                $oneFieldVal = acymailing_date(strtotime($oneFieldVal), $format);
                                break;
                        }
                    }

                    $replaceme = implode(', ', $userFieldVals);
                }
            } else {
                $replaceme = isset($values->{$mytag->id}) ? $values->{$mytag->id} : $mytag->default;
            }

            $tags[$i] = $replaceme;
            $pluginsHelper->formatString($tags[$i], $mytag);
        }

        $pluginsHelper->replaceTags($email, $tags);
    }//endfct

    function onAcyDisplayFilters(&$type, $context = "massactions")
    {

        if ($this->params->get('displayfilter_'.$context, true) == false) {
            return;
        }

        $fields = acymailing_getColumns('#__users');
        if (empty($fields)) {
            return;
        }

        $type['joomlafield'] = acymailing_translation('JOOMLA_FIELD');
        $type['joomlagroup'] = acymailing_translation('ACY_GROUP');

        $field = array();
        $field[] = acymailing_selectOption(0, '- - -');
        foreach ($fields as $oneField => $fieldType) {
            $field[] = acymailing_selectOption($oneField, $oneField);
        }

        if (ACYMAILING_J16) {
            $extraFields = acymailing_loadObjectList('SELECT DISTINCT `profile_key` FROM `#__user_profiles`');
            if (!empty($extraFields)) {
                foreach ($extraFields as $oneField) {
                    $field[] = acymailing_selectOption('customfield_'.$oneField->profile_key, $oneField->profile_key);
                }
            }
        }

        $jversion = preg_replace('#[^0-9\.]#i', '', JVERSION);
        if (version_compare($jversion, '3.7.0', '>=')) {
            $query = 'SELECT id, title 
						FROM #__fields 
						WHERE context = "com_users.user"
							AND state = 1
							AND type IN ("calendar", "checkboxes", "color", "integer", "list", "imagelist", "radio", "sql", "text", "textarea", "url", "user", "usergrouplist")
						ORDER BY title ASC';
            $customFields = acymailing_loadObjectList($query);
            foreach ($customFields as $oneCF) {
                $field[] = acymailing_selectOption($oneCF->id, $oneCF->title);
            }
        }

        $jsOnChange = "displayCondFilter('displayUserValues', 'toChange__num__',__num__,'map='+document.getElementById('filter__num__joomlafieldmap').value+'&cond='+document.getElementById('filter__num__joomlafieldoperator').value+'&value='+document.getElementById('filter__num__joomlafieldvalue').value); ";

        $operators = acymailing_get('type.operators');
        $operators->extra = 'onchange="'.$jsOnChange.'countresults(__num__)"';

        $return = '<div id="filter__num__joomlafield">'.acymailing_select($field, "filter[__num__][joomlafield][map]", 'class="inputbox" size="1" onchange="'.$jsOnChange.'countresults(__num__)"', 'value', 'text');
        $return .= ' '.$operators->display("filter[__num__][joomlafield][operator]").' <span id="toChange__num__"><input onchange="countresults(__num__)" class="inputbox" type="text" name="filter[__num__][joomlafield][value]" id="filter__num__joomlafieldvalue" style="width:200px" value=""></span></div>';

        if (!ACYMAILING_J16) {
            $acl = JFactory::getACL();
            $groups = $acl->get_group_children_tree(null, 'USERS', false);
        } else {
            $groups = acymailing_loadObjectList('SELECT a.*, a.title as text, a.id as value FROM #__usergroups AS a ORDER BY a.lft ASC', 'id');
            foreach ($groups as $id => $group) {
                if (isset($groups[$group->parent_id])) {
                    $groups[$id]->level = empty($groups[$group->parent_id]->level) ? 1 : intval($groups[$group->parent_id]->level + 1);
                    $groups[$id]->text = str_repeat('- - ', $groups[$id]->level).$groups[$id]->text;
                }
            }
        }

        $inoperator = acymailing_get('type.operatorsin');
        $inoperator->js = 'onchange="countresults(__num__)"';

        $return .= '<div id="filter__num__joomlagroup">'.$inoperator->display("filter[__num__][joomlagroup][type]").' '.acymailing_select($groups, "filter[__num__][joomlagroup][group]", 'class="inputbox" size="1" onchange="countresults(__num__)"', 'value', 'text').'<label for="filter__num__joomlagroupsubgroups"><input type="checkbox" value="1" id="filter__num__joomlagroupsubgroups" name="filter[__num__][joomlagroup][subgroups]" onchange="countresults(__num__)"/>'.acymailing_translation('ACY_SUB_GROUPS').'</label></div>';

        return $return;
    }

    function onAcyTriggerFct_displayUserValues()
    {
        $num = acymailing_getVar('int', 'num');
        $map = acymailing_getVar('cmd', 'map');
        $cond = acymailing_getVar('string', 'cond', '', '', ACY_ALLOWHTML);
        $value = acymailing_getVar('string', 'value', '', '', ACY_ALLOWHTML);

        $emptyInputReturn = '<input onchange="countresults('.$num.')" class="inputbox" type="text" name="filter['.$num.'][joomlafield][value]" id="filter'.$num.'joomlafieldvalue" style="width:200px" value="'.$value.'">';
        $dateInput = '<input onclick="displayDatePicker(this,event)" onchange="countresults('.$num.')" class="inputbox" type="text" name="filter['.$num.'][joomlafield][value]" id="filter'.$num.'joomlafieldvalue" style="width:200px" value="'.$value.'">';

        if (in_array($map, array('registerDate', 'lastvisitDate', 'lastResetTime'))) {
            return $dateInput;
        }

        if (empty($map) || in_array($map, array('password', 'params', 'optKey', 'otep')) || !in_array($cond, array('=', '!='))) {
            return $emptyInputReturn;
        }

        if (strpos($map, 'customfield_') !== false) {
            $prop = acymailing_loadObjectList('SELECT DISTINCT TRIM(BOTH \'"\' FROM `profile_value`) AS value FROM #__user_profiles WHERE profile_key = '.acymailing_escapeDB(str_replace('customfield_', '', $map)).' LIMIT 100');
        } elseif (intval($map) != 0) {
            $prop = acymailing_loadObjectList('SELECT DISTINCT `value` FROM #__fields_values WHERE field_id = '.intval($map).' LIMIT 100');
        } else {
            $prop = acymailing_loadObjectList('SELECT DISTINCT `'.acymailing_secureField($map).'` AS value FROM #__users LIMIT 100');
        }

        if (empty($prop) || count($prop) >= 100 || (count($prop) == 1 && (empty($prop[0]->value) || $prop[0]->value == '-'))) {
            return $emptyInputReturn;
        }

        return acymailing_select($prop, "filter[$num][joomlafield][value]", 'onchange="countresults('.$num.')" class="inputbox" size="1" style="width:200px"', 'value', 'value', $value, 'filter'.$num.'joomlafieldvalue');
    }

    function onAcyProcessFilterCount_joomlafield(&$query, $filter, $num)
    {
        $this->onAcyProcessFilter_joomlafield($query, $filter, $num);

        return acymailing_translation_sprintf('SELECTED_USERS', $query->count());
    }

    function onAcyDisplayFilter_joomlafield($filter)
    {
        return acymailing_translation('JOOMLA_FIELD').' : '.$filter['map'].' '.$filter['operator'].' '.$filter['value'];
    }

    function onAcyProcessFilter_joomlafield(&$query, $filter, $num)
    {
        if (empty($filter['map'])) {
            return;
        }
        $type = '';
        if (strpos($filter['map'], 'customfield_') !== false) {
            $query->leftjoin['joomlauserprofiles'.$num] = '#__user_profiles AS joomlauserprofiles'.$num.' ON joomlauserprofiles'.$num.'.user_id = sub.userid AND joomlauserprofiles'.$num.'.profile_key = '.acymailing_escapeDB(str_replace('customfield_', '', $filter['map']));
            $val = trim($filter['value'], '"');
            if (in_array($filter['operator'], array('=', '!=', '<', '>', '<=', '>=', 'BEGINS', 'LIKE', 'NOT LIKE'))) {
                $val = '"'.$val;
            }
            if (in_array($filter['operator'], array('=', '!=', '<', '>', '<=', '>=', 'END', 'LIKE', 'NOT LIKE'))) {
                $val = $val.'"';
            }

            $query->where[] = $query->convertQuery('joomlauserprofiles'.$num, 'profile_value', $filter['operator'], $val, $type);
        } elseif (intval($filter['map']) != 0) {
            $query->leftjoin['joomlauserfields'.$num] = '#__fields_values AS joomlauserfields'.$num.' ON joomlauserfields'.$num.'.item_id = sub.userid AND joomlauserfields'.$num.'.field_id = '.intval($filter['map']);
            $query->where[] = $query->convertQuery('joomlauserfields'.$num, 'value', $filter['operator'], $filter['value'], $type);
        } else {
            $query->leftjoin['joomlauser'.$num] = '#__users AS joomlauser'.$num.' ON joomlauser'.$num.'.id = sub.userid';
            if (in_array($filter['map'], array('registerDate', 'lastvisitDate'))) {
                $filter['value'] = acymailing_replaceDate($filter['value']);
                if (!is_numeric($filter['value']) && strtotime($filter['value']) !== false) {
                    $filter['value'] = strtotime($filter['value']);
                }
                if (is_numeric($filter['value'])) {
                    $filter['value'] = strftime('%Y-%m-%d %H:%M:%S', $filter['value']);
                }
                $type = 'datetime';
            }
            $query->where[] = $query->convertQuery('joomlauser'.$num, $filter['map'], $filter['operator'], $filter['value'], $type);
        }
    }

    function onAcyProcessFilterCount_joomlagroup(&$query, $filter, $num)
    {
        $this->onAcyProcessFilter_joomlagroup($query, $filter, $num);

        return acymailing_translation_sprintf('SELECTED_USERS', $query->count());
    }

    function onAcyDisplayFilter_joomlagroup($filter)
    {
        if (!ACYMAILING_J16) {
            $acl = JFactory::getACL();
            $group = $acl->get_group_name($filter['type']);
        } else {
            $group = acymailing_loadObject('SELECT * FROM #__usergroups WHERE id='.$filter['group']);
        }

        return acymailing_translation('ACY_GROUP').' : '.$filter['type'].' '.$group->title;
    }

    function onAcyProcessFilter_joomlagroup(&$query, $filter, $num)
    {
        $operator = (empty($filter['type']) || $filter['type'] == 'IN') ? 'IS NOT NULL AND joomlauser'.$num.'.'.(ACYMAILING_J16 ? 'user_' : '').'id != 0' : "IS NULL";
        $filter['group'] = intval($filter['group']);

        if (!empty($filter['subgroups'])) {
            $groupTable = ACYMAILING_J16 ? 'usergroups' : 'core_acl_aro_groups';
            $lftrgt = acymailing_loadObject('SELECT lft, rgt FROM #__'.$groupTable.' WHERE id = '.$filter['group']);
            $allGroups = acymailing_loadResultArray('SELECT id FROM #__'.$groupTable.' WHERE lft > '.$lftrgt->lft.' AND rgt < '.$lftrgt->rgt);
            array_unshift($allGroups, $filter['group']);
            $value = ' IN ('.implode(', ', $allGroups).')';
        } else {
            $value = ' = '.$filter['group'];
        }

        if (!ACYMAILING_J16) {
            $query->leftjoin['joomlauser'.$num] = "#__users AS joomlauser$num ON joomlauser$num.id = sub.userid AND joomlauser$num.gid".$value;
            $query->where[] = "joomlauser$num.id ".$operator;
        } else {
            $query->leftjoin['joomlauser'.$num] = "#__user_usergroup_map AS joomlauser$num ON joomlauser$num.user_id = sub.userid AND joomlauser$num.group_id".$value;
            $query->where[] = "joomlauser$num.user_id ".$operator;
        }
    }
}//endclass

