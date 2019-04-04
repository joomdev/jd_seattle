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

class FieldsController extends acymController
{
    public function __construct()
    {
        parent::__construct();
        $this->breadcrumb[acym_translation('ACYM_CUSTOM_FIELDS')] = acym_completeLink('fields');
    }

    public function listing()
    {
        if (acym_level(2)) {
            acym_setVar('layout', 'listing');
            $data = array();
            $fieldClass = acym_get('class.field');

            $data['allFields'] = $fieldClass->getMatchingFields();

            return parent::display($data);
        }

        if (!acym_level(2)) {
            acym_redirect(acym_completeLink('dashboard&task=upgrade&version=enterprise', false, true));
        }
    }

    public function edit()
    {
        acym_setVar('layout', 'edit');
        $id = acym_getVar('int', 'id');
        $fieldClass = acym_get('class.field');

        if (empty($id)) {
            $field = new stdClass();
            $field->id = 0;
            $field->name = '';
            $field->active = 1;
            $field->type = 'text';
            $field->value = '';
            $field->option = '';
            $field->default_value = '';
            $field->required = 0;
            $field->backend_profile = 1;
            $field->backend_listing = 0;
            $field->backend_filter = 1;
            $field->frontend_form = 1;
            $field->frontend_profile = 1;
            $field->frontend_filter = 1;
            $field->access = 1;
            $field->fieldDB = new stdClass();
        } else {
            $field = $fieldClass->getOneFieldByID($id);
            $field->option = json_decode($field->option);
            $field->value = json_decode($field->value);
            $field->fieldDB = empty($field->option->fieldDB) ? new stdClass() : json_decode($field->option->fieldDB);
            if (!in_array($id, array(1, 2)) && !empty($field->fieldDB->table)) {
                $tables = acym_loadResultArray('SHOW TABLES FROM `'.$field->fieldDB->database.'`');
                $field->fieldDB->tables = array();
                foreach ($tables as $one) {
                    $field->fieldDB->tables[$one] = $one;
                }
                $columns = empty($field->fieldDB->table) ? array() : acym_loadResultArray('SHOW COLUMNS FROM '.$field->fieldDB->table.' FROM '.$field->fieldDB->database);
                $field->fieldDB->columns = array();
                foreach ($columns as $one) {
                    $field->fieldDB->columns[$one] = $one;
                }
                array_unshift($field->fieldDB->columns, acym_translation('ACYM_CHOOSE_COLUMN'));
            }
        }

        if (!empty($id)) {
            $this->breadcrumb[htmlspecialchars(acym_translation($field->name))] = acym_completeLink('fields&task=edit&id='.$id);
        } else {
            $this->breadcrumb[acym_translation('ACYM_NEW_CUSTOM_FIELD')] = acym_completeLink('fields&task=edit');
        }

        $allDatabases = acym_loadResultArray('SHOW DATABASES');
        $databases = array();
        foreach ($allDatabases as $database) {
            $databases[$database] = $database;
        }

        $allFields = $fieldClass->getAllfields();

        $allFieldsName = array();
        foreach ($allFields as $one) {
            $allFieldsName[$one->id] = $one->name;
        }

        $data = array(
            'field' => $field,
            'database' => $databases,
            'allFields' => $allFieldsName,
        );

        $data['fieldType'] = array(
            'text' => acym_translation('ACYM_TEXT'),
            'textarea' => acym_translation('ACYM_TEXTAREA'),
            'radio' => acym_translation('ACYM_RADIO'),
            'checkbox' => acym_translation('ACYM_CHECKBOX'),
            'single_dropdown' => acym_translation('ACYM_SINGLE_DROPDOWN'),
            'multiple_dropdown' => acym_translation('ACYM_MULTIPLE_DROPDOWN'),
            'date' => acym_translation('ACYM_DATE'),
            'file' => acym_translation('ACYM_FILE'),
            'phone' => acym_translation('ACYM_PHONE'),
            'custom_text' => acym_translation('ACYM_CUSTOM_TEXT'),
        );

        return parent::display($data);
    }

    public function getTables()
    {
        $database = acym_getVar('string', 'database');
        $allTables = acym_loadResultArray('SHOW TABLES FROM '.$database);
        echo json_encode($allTables);
        exit;
    }

    public function setColumns()
    {
        $table = acym_getVar('string', 'table');
        $database = acym_getVar('string', 'database');
        $query = 'SHOW COLUMNS FROM '.$table.' FROM '.$database;
        $columns = acym_loadResultArray($query);
        array_unshift($columns, 'ACYM_CHOOSE_COLUMN');
        echo json_encode($columns);
        exit;
    }

    public function apply()
    {
        $fieldClass = acym_get('class.field');
        $newField = $this->setFieldToSave();
        $id = $fieldClass->save($newField);
        if (!empty($id)) {
            acym_setVar('id', $id);
            acym_enqueueNotification(acym_translation('ACYM_SUCCESSFULLY_SAVED'), 'success', 5000);
        } else {
            acym_enqueueNotification(acym_translation('ACYM_ERROR_SAVING'), 'error', 5000);
        }

        $this->edit();
    }

    public function save()
    {
        $fieldClass = acym_get('class.field');
        $newField = $this->setFieldToSave();
        $id = $fieldClass->save($newField);
        if (!empty($id)) {
            acym_setVar('id', $id);
            acym_enqueueNotification(acym_translation('ACYM_SUCCESSFULLY_SAVED'), 'success', 5000);
        } else {
            acym_enqueueNotification(acym_translation('ACYM_ERROR_SAVING'), 'error', 5000);
        }
        $this->listing();
    }

    private function setFieldToSave()
    {
        $fieldClass = acym_get('class.field');
        $field = acym_getVar('array', 'field');
        $fieldDB = json_encode(acym_getVar('array', 'fieldDB'));
        $id = acym_getVar('int', 'id');
        if ($id == 2) {
            $field['required'] = 1;
        }
        if (empty($field['name'])) {
            return false;
        }


        $value = array();

        $fieldValues = $field['value'];
        $field['type'] = in_array($id, array(1, 2)) ? 'text' : $field['type'];

        $i = 0;
        foreach ($fieldValues['value'] as $one) {
            if (empty($one) && $one != '0' && ($i != 0 || !in_array($field['type'], array('single_dropdown', 'multiple_dropdown')))) {
                $i++;
                continue;
            } else {
                $value[$i] = array(
                    'value' => $one,
                    'title' => $fieldValues['title'][$i],
                    'disabled' => $fieldValues['disabled'][$i],
                );
                $i++;
            }
        }

        $field['namekey'] = empty($field['namekey']) ? $fieldClass->generateNamekey($field['name']) : $field['namekey'];
        $field['option']['format'] = ($field['type'] == 'date' && empty($field['option']['format'])) ? '%d%m%y' : $field['option']['format'];
        $field['option']['rows'] = ($field['type'] == 'textarea' && empty($field['option']['rows'])) ? '5' : $field['option']['rows'];
        $field['option']['columns'] = ($field['type'] == 'textarea' && empty($field['option']['columns'])) ? '30' : $field['option']['columns'];

        $field['value'] = json_encode($value);
        $field['option']['fieldDB'] = $fieldDB;
        $field['option']['format'] = !empty($field['option']['format']) ? preg_replace('/[^a-zA-Z\%]/', '', $field['option']['format']) : $field['option']['format'];
        $newField = new stdClass();
        $newField->name = $field['name'];
        $newField->active = $field['active'];
        $newField->namekey = $field['namekey'];
        $newField->type = in_array($id, array(1, 2)) ? 'text' : $field['type'];
        $newField->required = $field['required'];
        $newField->option = json_encode($field['option']);
        $newField->value = $field['value'];
        $newField->default_value = $field['default_value'];
        $newField->frontend_form = $field['frontend_form'];
        $newField->frontend_profile = $field['frontend_profile'];
        $newField->backend_profile = $field['backend_profile'];
        $newField->backend_listing = $field['backend_listing'];
        $newField->backend_filter = 1;
        $newField->frontend_filter = 1;
        $newField->access = 'all';
        if (empty($id)) {
            $newField->ordering = $fieldClass->getOrdering()->ordering_number + 1;
        } else {
            $newField->id = $id;
        }

        return $newField;
    }

    public function setOrdering()
    {
        $order = json_decode(acym_getVar('string', 'order'));
        $i = 1;
        $error = false;
        foreach ($order as $field) {
            $query = 'UPDATE #__acym_field SET `ordering`='.intval($i).' WHERE `id`='.intval($field);
            $error = acym_query($query) >= 0 ? false : true;
            $i++;
        }
        if ($error) {
            echo 'error';
        } else {
            echo 'updated';
        }
        exit;
    }

    public function delete()
    {
        $ids = acym_getVar('cmd', 'elements_checked');
        if (in_array('1', $ids) || in_array('2', $ids)) {
            acym_enqueueNotification(acym_translation('ACYM_CANT_DELETE'), 'error', 5000);
            $this->listing();

            return;
        } else {
            return parent::delete();
        }
    }
}
