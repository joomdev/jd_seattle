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
        $query = 'SELECT * FROM #__acym_field WHERE `id` = '.intval($id);

        return acym_loadObject($query);
    }

    public function getFieldsByID($ids)
    {
        acym_arrayToInteger($ids);
        if (empty($ids)) return [];
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
        $query = 'SELECT * FROM #__acym_field WHERE id NOT IN (1, 2) ORDER BY `ordering` ASC';

        return acym_loadObjectList($query, 'id');
    }

    public function getAllFieldsForModuleFront()
    {
        $query = 'SELECT * FROM #__acym_field WHERE id != 2 ORDER BY `ordering` ASC';

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
        $query = 'SELECT '.acym_secureDBColumn($fieldDB->value).' AS value, '.acym_secureDBColumn($fieldDB->title).' AS title
                    FROM '.acym_secureDBColumn($fieldDB->database).'.'.acym_secureDBColumn($fieldDB->table);
        $query .= empty($fieldDB->where_value) ? '' : ' WHERE `'.acym_secureDBColumn($fieldDB->where).'` '.$fieldDB->where_sign.' '.acym_escapeDB($fieldDB->where_value);
        if (!empty($fieldDB->order_by)) $query .= ' ORDER BY '.acym_secureDBColumn($fieldDB->order_by).' '.acym_secureDBColumn($fieldDB->sort_order);

        return acym_loadObjectList($query);
    }

    public function store($userID, $fields, $ajax = false)
    {
        if (!empty($_FILES['customField'])) {
            $uploadFolder = trim(acym_cleanPath(html_entity_decode(acym_getFilesFolder())), DS.' ').DS;
            $uploadPath = acym_cleanPath(ACYM_ROOT.$uploadFolder.'userfiles'.DS);
            $config = acym_config();
            $allowedExtensions = explode(',', $config->get('allowed_files'));

            foreach ($_FILES['customField']['tmp_name'] as $key => $value) {
                if (empty($value)) continue;

                $fileName = $_FILES['customField']['name'][$key];
                while (is_array($fileName) && isset($fileName[0])) {
                    $fileName = $fileName[0];
                }

                if (!preg_match('#\.('.implode('|', $allowedExtensions).')$#Ui', $fileName)) {
                    $ext = substr($fileName, strrpos($fileName, '.') + 1);
                    if ($ajax) {
                        $this->errors[] = acym_translation_sprintf(
                            'ACYM_ACCEPTED_TYPE',
                            acym_escape($ext),
                            implode(', ', $allowedExtensions)
                        );
                    } else {
                        acym_enqueueNotification(
                            acym_translation_sprintf(
                                'ACYM_ACCEPTED_TYPE',
                                acym_escape($ext),
                                implode(', ', $allowedExtensions)
                            ),
                            'error',
                            5000
                        );
                    }

                    continue;
                }

                if (!acym_uploadFile($value, $uploadPath.$fileName)) {
                    if ($ajax) {
                        $this->errors[] = acym_translation('ACYM_ERROR_SAVING');
                    } else {
                        acym_enqueueNotification(acym_translation('ACYM_ERROR_SAVING'), 'error', 5000);
                    }

                    continue;
                }
                $fields[$key] = $_FILES['customField']['name'][$key];
            }
        }

        if (empty($fields)) return;
        foreach ($fields as $id => $field) {
            $query = 'INSERT INTO #__acym_user_has_field (`user_id`, `field_id`, `value`) VALUES ';
            if (is_array($field)) {
                $fullField = $this->getOneFieldByID($id);
                if (in_array($fullField->type, ['multiple_dropdown', 'radio', 'phone'])) {
                    $field = implode(',', $field);
                } elseif ($fullField->type == 'checkbox') {
                    $field = implode(',', array_keys($field));
                } elseif ($fullField->type == 'date') {
                    $field = implode('/', $field);
                } else {
                    $field = json_encode($field);
                }
            }
            $query .= '('.intval($userID).', '.intval($id).', '.acym_escapeDB($field).')';
            $query .= ' ON DUPLICATE KEY UPDATE `value`= VALUES(`value`)';
            acym_query($query);
        }
    }

    public function getAllfieldBackEndListingByUserIds($ids, $fields, $forBackEnd = false)
    {
        $query = 'SELECT field.type as type, field.name as field_name, user_field.user_id as user_id, user_field.field_id as field_id, user_field.value as field_value 
                    FROM #__acym_user_has_field AS user_field
                    LEFT JOIN #__acym_field AS field ON user_field.field_id = field.id';

        $conditions = [];

        if ($forBackEnd) $conditions[] = 'field.backend_listing = 1';

        if (!is_array($ids)) $ids = [$ids];
        acym_arrayToInteger($ids);
        if (empty($ids)) $ids[] = 0;

        if (!is_array($fields)) $fields = [$fields];
        acym_arrayToInteger($fields);
        if (empty($fields)) $fields[] = 0;

        $conditions[] = 'user_field.user_id IN ('.implode(',', $ids).')';
        $conditions[] = 'user_field.field_id IN ('.implode(',', $fields).')';

        $query .= !empty($conditions) ? ' WHERE ('.implode(') AND (', $conditions).')' : '';

        $fieldValues = [];
        foreach (acym_loadObjectList($query) as $one) {
            $fieldValues[$one->field_id.$one->user_id] = is_array(json_decode($one->field_value)) ? implode(', ', json_decode($one->field_value)) : $one->field_value;
        }

        return $fieldValues;
    }

    public function getAllFieldsBackendListing()
    {
        $query = 'SELECT id, name FROM #__acym_field WHERE backend_listing = 1 AND id NOT IN (1, 2)';

        $return = [
            'names' => [],
            'ids' => [],
        ];

        foreach (acym_loadObjectList($query) as $one) {
            $return['names'][] = $one->name;
            $return['ids'][] = $one->id;
        }

        return $return;
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

        acym_query('DELETE FROM #__acym_user_has_field WHERE field_id IN ('.implode(',', $elements).')');

        return parent::delete($elements);
    }

    public function displayField($field, $defaultValue, $size, $valuesArray, $displayOutside = true, $displayFront = false, $user = null, $display = 1, $displayIf = '')
    {
        if ($display == 0) return '';

        $cmsUser = false;
        if ($displayFront && !empty($user->id)) {
            $cmsUser = !empty($user->cms_id) ? true : false;
            if ($field->id == 1) {
                $defaultValue = $user->name;
            } elseif ($field->id == 2) {
                $defaultValue = $user->email;
            } else {
                $allValues = [];
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

        if (in_array($field->type, ['radio', 'checkbox'])) {
            $valuesArrayTmp = [];
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

        if (is_array($valuesArray)) {
            foreach ($valuesArray as $key => $oneValue) {
                if (is_object($oneValue) && !empty($valuesArray[$key]->text)) {
                    $valuesArray[$key]->text = acym_translation($valuesArray[$key]->text);
                } elseif (is_string($oneValue)) {
                    $valuesArray[$key] = acym_translation($valuesArray[$key]);
                }
            }
        }

        $return = '';

        $field->name = acym_translation($field->name);

        $style = empty($size) ? '' : ' style="'.$size.'"';
        $messageRequired = empty($field->option->error_message) ? acym_translation_sprintf('ACYM_DEFAULT_REQUIRED_MESSAGE', $field->name) : acym_translation($field->option->error_message);
        $requiredJson = json_encode(['type' => $field->type, 'message' => $messageRequired]);
        $required = $field->required ? ' data-required="'.acym_escape($requiredJson).'"' : '';
        $placeholder = '';
        if (!$displayOutside) $placeholder = ' placeholder="'.acym_escape($field->name).'"';

        $name = 'customField['.intval($field->id).']';
        $nameAttribute = ' name="'.$name.'"';
        $value = ' value="'.acym_escape($defaultValue).'"';


        if ($field->type == 'date' || ($displayOutside && (in_array($field->id, [1, 2]) || in_array($field->type, ['text', 'textarea', 'single_dropdown', 'multiple_dropdown', 'custom_text'])))) {
            $return .= '<label '.$displayIf.' class="cell margin-top-1"><div class="acym__users__creation__fields__title">'.$field->name.'</div>';
        }

        if ($field->id == 1) {
            $nameAttribute = ' name="user[name]"';
            $return .= '<input '.$nameAttribute.$placeholder.$required.$value.' type="text" class="cell">';
        } elseif ($field->id == 2) {
            $nameAttribute = ' name="user[email]"';
            $return .= '<input '.$nameAttribute.$placeholder.$value.' required type="email" class="cell" id="acym__user__edit__email" '.($displayFront && $cmsUser ? 'disabled' : '').'>';
        } elseif ($field->type == 'text') {
            $field->option->authorized_content->message = $field->option->error_message_invalid;
            $authorizedContent = ' data-authorized-content="'.acym_escape(json_encode($field->option->authorized_content)).'"';
            $return .= '<input '.$nameAttribute.$placeholder.$required.$value.$authorizedContent.$style.' type="text">';
        } elseif ($field->type == 'textarea') {
            $return .= '<textarea '.$nameAttribute.$required.' rows="'.intval($field->option->rows).'" cols="'.intval($field->option->columns).'">'.(empty($defaultValue) ? $field->name : $defaultValue).'</textarea>';
        } elseif ($field->type == 'radio') {
            if ($displayFront) {
                $return .= '<div '.$displayIf.' class="cell acym__content"><div class="acym__users__creation__fields__title">'.$field->name.'</div>';
                $defaultValue = empty($defaultValue) ? null : (is_array($defaultValue) ? $defaultValue[0] : $defaultValue);
                foreach ($valuesArray as $key => $value) {
                    $defaultValue = $defaultValue == $key ? 'checked' : '';
                    $return .= '<label>'.$value.'<input '.$nameAttribute.$required.' type="radio" value="'.acym_escape($key).'" '.$defaultValue.'></label>';
                }
                $return .= '</div>';
            } else {
                $return .= '<div '.$displayIf.' class="cell acym__content"><div class="cell">';
                $return .= '<div class="acym__users__creation__fields__title">'.$field->name.'</div>';
                $return .= acym_radio($valuesArray, $name.'[]', empty($defaultValue) ? null : (is_array($defaultValue) ? $defaultValue[0] : $defaultValue), null, ($field->required ? ['data-required' => $requiredJson] : []));
                $return .= '</div></div>';
            }
        } elseif ($field->type == 'checkbox') {
            $return .= '<div '.$displayIf.' class="cell margin-top-1"><div class="acym__users__creation__fields__title margin-bottom-1">'.$field->name.'</div>';
            if ($displayFront) {
                $defaultValue = empty($defaultValue) ? null : (explode(',', $defaultValue));
                foreach ($valuesArray as $key => $value) {
                    $checked = !empty($defaultValue) && in_array($key, $defaultValue) ? 'checked' : '';
                    $return .= '<label>'.$value.'<input '.$required.' type="checkbox" name="'.$name.'['.acym_escape($key).']" value="'.acym_escape($key).'" '.$checked.'></label>';
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
                    if (empty($defaultValue->$key)) {
                        $labelClass = 'class="cell margin-top-1"';
                        $attributes = '';
                    } else {
                        $labelClass = '';
                        $attributes = 'checked '.$required;
                    }
                    $return .= '<label '.$labelClass.'>'.$value;
                    $return .= '<input '.$attributes.' type="checkbox" name="'.$name.'['.acym_escape($key).']" class="acym__users__creation__fields__checkbox"></label>';
                }
            }
            $return .= '</div>';
        } elseif ($field->type == 'single_dropdown') {
            $return .= acym_select($valuesArray, $name, empty($defaultValue) ? '' : $defaultValue, 'class="acym__custom__fields__select__form"'.$style.$required);
        } elseif ($field->type == 'multiple_dropdown') {
            $defaultValue = is_array($defaultValue) ? $defaultValue : explode(',', $defaultValue);

            $attributes = [
                'class' => 'acym__custom__fields__select__multiple__form',
                'style' => $size,
            ];
            if ($field->required) $attributes['data-required'] = $requiredJson;

            $return .= acym_selectMultiple($valuesArray, $name, empty($defaultValue) ? [] : $defaultValue, $attributes);
        } elseif ($field->type == 'date') {
            $defaultValue = is_array($defaultValue) ? implode('/', $defaultValue) : $defaultValue;
            $return .= acym_displayDateFormat($field->option->format, $name.'[]', $defaultValue);
        } elseif ($field->type == 'file') {
            $defaultValue = is_array($defaultValue) ? $defaultValue[0] : $defaultValue;
            if ($displayFront) {
                $return .= '<label '.$displayIf.' class="cell margin-top-1 grid-x grid-margin-x"><div class="acym__users__creation__fields__title cell">'.$field->name.'</div>';
                $return .= '<input '.$nameAttribute.$required.' type="file"></label>';
            } else {
                $return .= acym_inputFile($name.'[]', $defaultValue, '', '', $required);
            }
        } elseif ($field->type == 'phone') {
            $defaultValue = !empty($defaultValue) ? explode(',', $defaultValue) : '';

            if ($displayOutside) $return .= '<label '.$displayIf.' class="cell margin-top-1 grid-x grid-margin-x"><div class="acym__users__creation__fields__title cell">'.$field->name.'</div>';
            $return .= '<div class="medium-3">';
            $return .= acym_generateCountryNumber($name.'[code]', empty($defaultValue) ? '' : $defaultValue[0]);
            $return .= '</div>';
            $return .= '<input '.$placeholder.$required.$style.' class="medium-9 cell" type="tel" name="'.$name.'[phone]" value="'.acym_escape(empty($defaultValue) ? '' : $defaultValue[1]).'" data-format="'.acym_escape($field->option->format).'">';
        } elseif ($field->type == 'custom_text') {
            $return .= $field->option->custom_text;
        }


        if ($field->type == 'date' || ($displayOutside && (in_array($field->id, [1, 2]) || in_array($field->type, ['text', 'textarea', 'single_dropdown', 'multiple_dropdown', 'phone', 'custom_text'])))) {
            $return .= '</label>';
        }

        return $return;
    }
}

