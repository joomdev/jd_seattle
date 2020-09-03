<?php
defined('_JEXEC') or die('Restricted access');
?><?php

class JFormFieldFields extends JFormField
{
    var $type = 'fields';

    public function getInput()
    {
        if ('Joomla' == 'Joomla' && !include_once(rtrim(JPATH_ADMINISTRATOR, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acym'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php')) {
            echo 'This extension cannot work without AcyMailing';
        }

        $fieldsClass = acym_get('class.field');
        $allFields = $fieldsClass->getAllFieldsForModuleFront();
        $fields = [];
        foreach ($allFields as $field) {
            $fields[$field->id] = acym_translation($field->name);
        }


        if (ACYM_CMS == 'joomla' && $this->value == '1') {
            $formId = $this->form->getData()->get('id');
            if (!empty($formId)) {
                $this->value = '';
            }
        }

        if (is_string($this->value)) {
            $this->value = explode(',', $this->value);
        }

        if (in_array('None', $this->value)) {
            $this->value = [];
        }
        if (in_array('All', $this->value)) {
            $this->value = array_keys($fields);
        }

        return acym_selectMultiple($fields, $this->name, $this->value, ['id' => $this->name]);
    }
}

