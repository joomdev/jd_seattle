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

class acymfieldClass extends acymClass
{
    var $table = 'field';
    var $pkey = 'id';

    public function getMatchingFields()
    {
        $query = 'SELECT * FROM #__acym_field ORDER BY `ordering` ASC';

        return acym_loadObjectList($query, 'id');
    }

    public function getOneFieldByID($id)
    {
        $query = 'SELECT * FROM #__acym_field WHERE `id`='.intval($id);

        return acym_loadObject($query);
    }

    public function getFieldsByID($ids)
    {
        acym_arrayToInteger($ids);
        if (empty($ids)) return array();
        $query = 'SELECT * FROM #__acym_field WHERE `id` IN('.implode(',', $ids).') ORDER BY `ordering` ASC';

        return acym_loadObjectList($query);
    }

    public function getOrdering()
    {
        $query = 'SELECT COUNT(id) AS ordering_number FROM #__acym_field';

        return acym_loadObject($query);
    }

    public function getAllfields()
    {
        return acym_loadObjectList('SELECT * FROM #__acym_field', 'id');
    }

    public function getAllFieldsForUser()
    {
        $query = 'SELECT * FROM #__acym_field WHERE id NOT IN (1, 2) ORDER BY '.acym_secureDBColumn('ordering').' ASC';

        return acym_loadObjectList($query, 'id');
    }

    public function getAllFieldsForModuleFront()
    {
        $query = 'SELECT * FROM #__acym_field WHERE id != 2 ORDER BY '.acym_secureDBColumn('ordering').' ASC';

        return acym_loadObjectList($query, 'id');
    }

    public function getFieldsValueByUserId($userId)
    {
        $query = 'SELECT * FROM #__acym_user_has_field  WHERE user_id = '.intval($userId);

        return acym_loadObjectList($query, 'field_id');
    }

    public function generateNamekey($name, $namekey = '')
    {
        $fieldsNamekey = acym_loadResultArray('SELECT namekey FROM #__acym_field');

        $namekey = empty($namekey) ? substr(preg_replace('#[^a-z0-9_]#i', '', strtolower($name)), 0, 50) : $namekey;
        if (in_array($namekey, $fieldsNamekey)) {
            $namekey = $namekey.'_'.count($fieldsNamekey);
        }

        return $namekey;
    }

    public function getValueFromDB($fieldDB)
    {
        $query = 'SELECT '.$fieldDB->value.' as value, '.$fieldDB->title.' as title
                    FROM '.$fieldDB->database.'.'.$fieldDB->table;
        $query .= empty($fieldDB->where_value) ? '' : ' WHERE `'.acym_secureDBColumn($fieldDB->where).'` '.$fieldDB->where_sign.' '.acym_escapeDB($fieldDB->where_value).'';
        if (!empty($fieldDB->order_by)) $query .= ' ORDER BY '.$fieldDB->order_by.' '.$fieldDB->sort_order;

        return acym_loadObjectList($query);
    }

    public function store($fields, $userID)
    {
        if (!empty($_FILES['customField'])) {
            $uploadFolder = trim(acym_cleanPath(html_entity_decode(acym_getFilesFolder())), DS.' ').DS;
            $uploadPath = acym_cleanPath(ACYM_ROOT.$uploadFolder.'userfiles'.DS);
            foreach ($_FILES['customField']['tmp_name'] as $key => $value) {
                if (empty($value[0])) {
                    continue;
                }
                $error = acym_uploadFile($value[0], $uploadPath.$_FILES['customField']['name'][$key][0]);
                if (!$error) {
                    acym_enqueueNotification(acym_translation('ACYM_ERROR_SAVING'), 'error', 5000);

                    return;
                }
                $fields[$key] = $_FILES['customField']['name'][$key][0];
            }
        }
        foreach ($fields as $id => $field) {
            $query = 'INSERT INTO #__acym_user_has_field (`user_id`, `field_id`, `value`) VALUES ';
            if (is_array($field)) {
                $fullField = $this->getOneFieldByID($id);
                if (in_array($fullField->type, array('multiple_dropdown', 'radio', 'phone'))) {
                    $field = implode(',', $field);
                } else if ($fullField->type == 'checkbox') {
                    $field = implode(',', array_keys($field));
                } else if ($fullField->type == 'date') {
                    $field = implode('/', $field);
                } else {
                    $field = json_encode($field);
                }
            }
            $query .= '('.$userID.', '.$id.', \''.$field.'\')';
            $query .= ' ON DUPLICATE KEY UPDATE `value`=\''.$field.'\'';
            acym_query($query);
        }
    }

    public function getAllfieldBackEndListingByUserIds($ids, $fields, $forBackEnd = false)
    {
        $query = 'SELECT field.type as type, field.name as field_name, user_field.user_id as user_id, user_field.field_id as field_id, user_field.value as field_value FROM #__acym_user_has_field AS user_field
                    LEFT JOIN #__acym_field AS field ON user_field.field_id = field.id';

        $conditions = array();

        if ($forBackEnd) $conditions[] = 'field.backend_listing = 1';
        $conditions[] = is_array($ids) ? 'user_field.user_id IN ('.implode(',', $ids).')' : 'user_field.user_id = '.$ids;
        $conditions[] = is_array($fields) ? 'user_field.field_id IN ('.implode(',', $fields).')' : 'user_field.field_id = '.$fields;

        $query .= !empty($conditions) ? ' WHERE ('.implode(') AND (', $conditions).')' : '';

        $fieldValues = array();
        foreach (acym_loadObjectList($query) as $one) {
            if ($one->type == 'phone') {
                $fieldValues[$one->field_id.$one->user_id] = empty($value->code) ? '' : str_replace(',', '', $one->field_value);
            } else {
                $fieldValues[$one->field_id.$one->user_id] = is_array(json_decode($one->field_value)) ? implode(', ', json_decode($one->field_value)) : $one->field_value;
            }
        }

        return $fieldValues;
    }

    public function getAllFieldsBackendListing()
    {
        $query = 'SELECT id, name FROM #__acym_field WHERE backend_listing = 1 AND id NOT IN (1, 2)';

        $return = array(
            'names' => array(),
            'ids' => array(),
        );

        foreach (acym_loadObjectList($query) as $one) {
            $return['names'][] = $one->name;
            $return['ids'][] = $one->id;
        }

        return $return;
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

        acym_query('DELETE FROM #__acym_user_has_field WHERE field_id IN ('.implode(',', $elements).')');

        return parent::delete($elements);
    }

    public function displayField($field, $defaultValue, $size, $valuesArray, $displayOutside = true, $displayFront = false, $user = null, $display = 1, $displayIf = '')
    {
        if ($display == 0) {
            return '';
        }
        $cmsUser = false;
        if ($displayFront && !empty($user->id)) {
            $cmsUser = !empty($user->cms_id) ? true : false;
            if ($field->id == 1) {
                $defaultValue = $user->name;
            } else if ($field->id == 2) {
                $defaultValue = $user->email;
            } else {
                $allValues = array();
                $defaultUserValue = $this->getFieldsValueByUserId($user->id);
                if (!empty($defaultUserValue)) {
                    foreach ($defaultUserValue as $one) {
                        $allValues[$one->field_id] = $one->value;
                    }
                }

                if (isset($allValues[$field->id])) {
                    $defaultValue = is_null(json_decode($allValues[$field->id])) ? $allValues[$field->id] : json_decode($allValues[$field->id]);
                }
            }
        }

        if (in_array($field->type, array('radio', 'checkbox'))) {
            $valuesArrayTmp = array();
            foreach ($valuesArray as $oneValue) {
                if (!is_object($oneValue)) {
                    $valuesArrayTmp = $valuesArray;
                    break;
                }

                if (!empty($oneValue->disable)) continue;
                $valuesArrayTmp[$oneValue->value] = $oneValue->text;
            }
            $valuesArray = $valuesArrayTmp;
        }

        $return = '';

        $field->name = acym_translation($field->name);

        $messageRequired = empty($field->option->error_message) ? acym_translation_sprintf('ACYM_DEFAULT_REQUIRED_MESSAGE', $field->name) : acym_translation($field->option->error_message);
        $requiredJson = json_encode(array('type' => $field->type, 'message' => $messageRequired));
        $required = $field->required ? 'data-required="'.acym_escape($requiredJson).'"' : '';

        if ($field->id == 1) {
            $return .= ($displayOutside ? '<label class="cell"><div class="acym__users__creation__fields__title">'.$field->name.'</div>' : '').'<input '.($displayOutside ? '' : 'placeholder="'.$field->name.'"').' type="text" class="cell" name="user[name]" '.$required.' value="'.$defaultValue.'">'.($displayOutside ? '</label>' : '');
        } else if ($field->id == 2) {
            $return .= ($displayOutside ? '<label class="cell"><div class="acym__users__creation__fields__title">'.$field->name.'</div>' : '').'<input '.($displayOutside ? '' : 'placeholder="'.$field->name.'"').' id="acym__user__edit__email" required type="email" class="cell" name="user[email]" '.($displayFront && $cmsUser ? 'disabled' : '').' value="'.$defaultValue.'">'.($displayOutside ? '</label>' : '');
        } else if ($field->type == 'text') {
            $field->option->authorized_content->message = $field->option->error_message_invalid;
            $authorizedContent = 'data-authorized-content=\''.json_encode($field->option->authorized_content).'\'';
            $return .= ($displayOutside ? '<label '.$displayIf.' class="cell margin-top-1"><div class="acym__users__creation__fields__title">'.$field->name.'</div>' : '').'<input '.$required.' '.($displayOutside ? '' : 'placeholder="'.$field->name.'"').' '.$authorizedContent.' style="'.$size.'" type="text" name="customField['.$field->id.']" value="'.$defaultValue.'">'.($displayOutside ? '</label>' : '');
        } else if ($field->type == 'textarea') {
            $return .= ($displayOutside ? '<label '.$displayIf.' class="cell margin-top-1"><div class="acym__users__creation__fields__title">'.$field->name.'</div>' : '').'<textarea '.$required.' rows="'.$field->option->rows.'" cols="'.$field->option->columns.'" name="customField['.$field->id.']">'.(empty($defaultValue) ? $field->name : $defaultValue).'</textarea>'.($displayOutside ? '</label>' : '');
        } else if ($field->type == 'radio') {
            if ($displayFront) {
                $return .= '<div '.$displayIf.' class="cell acym__content"><div class="acym__users__creation__fields__title">'.$field->name.'</div>';
                $defaultValue = empty($defaultValue) ? null : (is_array($defaultValue) ? $defaultValue[0] : $defaultValue);
                foreach ($valuesArray as $key => $value) {
                    $defaultValue = $defaultValue == $key ? 'checked' : '';
                    $return .= '<label>'.$value.'<input '.$required.' type="radio" name="customField['.$field->id.']" value="'.$key.'" '.$defaultValue.'></label>';
                }
                $return .= '</div>';
            } else {
                $return .= '<div '.$displayIf.' class="cell acym__content"><div class="cell"><div class="acym__users__creation__fields__title">'.$field->name.'</div>'.acym_radio($valuesArray, 'customField['.$field->id.'][]', empty($defaultValue) ? null : (is_array($defaultValue) ? $defaultValue[0] : $defaultValue), null, ($field->required ? array('data-required' => $requiredJson) : array())).'</div></div>';
            }
        } else if ($field->type == 'checkbox') {
            $return .= '<div '.$displayIf.' class="cell margin-top-1 acym__content"><div class="acym__users__creation__fields__title margin-bottom-1">'.$field->name.'</div>';
            if ($displayFront) {
                $defaultValue = empty($defaultValue) ? null : (explode(',', $defaultValue));
                foreach ($valuesArray as $key => $value) {
                    $checked = (!empty($defaultValue) && in_array($key, $defaultValue)) ? 'checked' : '';
                    $return .= '<label>'.$value.'<input '.$required.' type="checkbox" name="customField['.$field->id.']['.$key.']" value="'.$key.'" '.$checked.'></label>';
                }
            } else {
                if (!empty($defaultValue) && !is_object($defaultValue)) {
                    $defaultValue = explode(',', $defaultValue);
                    $temporaryObject = new stdClass();
                    foreach ($defaultValue as $value) {
                        $temporaryObject->$value = 'on';
                    }
                    $defaultValue = $temporaryObject;
                }
                $defaultValue = is_object($defaultValue) ? $defaultValue : new stdClass();
                foreach ($valuesArray as $key => $value) {
                    $return .= !empty($defaultValue->$key) ? '<label>'.$value.'<input '.$required.' type="checkbox" name="customField['.$field->id.']['.$key.']" class="acym__users__creation__fields__checkbox" checked></label>' : '<label class="cell margin-top-1">'.$value.'<input type="checkbox" name="customField['.$field->id.']['.$key.']" class="acym__users__creation__fields__checkbox"></label>';
                }
            }
            $return .= '</div>';
        } else if ($field->type == 'single_dropdown') {
            $return .= ($displayOutside ? '<label '.$displayIf.' class="cell margin-top-1"><div class="acym__users__creation__fields__title">'.$field->name.'</div>' : '').acym_select($valuesArray, 'customField['.$field->id.']', empty($defaultValue) ? '' : $defaultValue, 'class="acym__custom__fields__select__form" style="'.$size.'"'.$required, 'value', 'text').($displayOutside ? '</label>' : '');
        } else if ($field->type == 'multiple_dropdown') {
            $defaultValue = is_array($defaultValue) ? $defaultValue : explode(',', $defaultValue);
            $return .= ($displayOutside ? '<label '.$displayIf.' class="cell margin-top-1"><div class="acym__users__creation__fields__title">'.$field->name.'</div>' : '').acym_selectMultiple($valuesArray, 'customField['.$field->id.']', empty($defaultValue) ? array() : $defaultValue, $field->required ? array('data-required' => $requiredJson, 'class' => 'acym__custom__fields__select__multiple__form', 'style' => $size) : array('class' => 'acym__custom__fields__select__multiple__form', 'style' => $size)).($displayOutside ? '</label>' : '');
        } else if ($field->type == 'date') {
            $defaultValue = is_array($defaultValue) ? implode('/', $defaultValue) : $defaultValue;
            $return .= '<label '.$displayIf.' class="cell margin-top-1"><div class="acym__users__creation__fields__title">'.$field->name.'</div>'.acym_displayDateFormat($field->option->format, 'customField['.$field->id.'][]', $defaultValue).'</label>';
        } else if ($field->type == 'file') {
            $defaultValue = is_array($defaultValue) ? $defaultValue[0] : $defaultValue;
            if ($displayFront) {
                $return .= '<label '.$displayIf.' class="cell margin-top-1 grid-x grid-margin-x"><div class="acym__users__creation__fields__title cell">'.$field->name.'</div>';
                $return .= '<input '.$required.' type="file" name="customField['.$field->id.']"></label>';
            } else {
                $return .= acym_inputFile('customField['.$field->id.'][]', $defaultValue, '', '', $required);
            }
        } else if ($field->type == 'phone') {
            $defaultValue = !empty($defaultValue) ? explode(',', $defaultValue) : '';
            $return .= $displayOutside ? '<label '.$displayIf.' class="cell margin-top-1 grid-x grid-margin-x"><div class="acym__users__creation__fields__title cell">'.$field->name.'</div>' : '';
            $return .= '<div class="medium-3">'.acym_generateCountryNumber('customField['.$field->id.'][code]', empty($defaultValue) ? '' : $defaultValue[0]).'</div><input '.$required.' '.($displayOutside ? '' : 'placeholder="'.$field->name.'"').' style="'.$size.'" class="medium-9 cell" type="tel" name="customField['.$field->id.'][phone]" value="'.(empty($defaultValue) ? '' : $defaultValue[1]).'" data-format="'.$field->option->format.'">'.($displayOutside ? '</label>' : '');
        } else if ($field->type == 'custom_text') {
            $return .= $displayOutside ? '<label '.$displayIf.' class="cell margin-top-1"><div class="acym__users__creation__fields__title">'.$field->name.'</div>' : '';
            $return .= '<textarea name="customField['.$field->id.']" rows="10" cols="10" readonly>'.$field->option->custom_text.'</textarea>'.($displayOutside ? '</label>' : '');
        }

        return $return;
    }
}

