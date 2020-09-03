<?php
defined('_JEXEC') or die('Restricted access');
?><?php

class JFormFieldLists extends JFormField
{
    var $type = 'lists';

    public function getInput()
    {
        if ('Joomla' == 'Joomla' && !include_once(rtrim(JPATH_ADMINISTRATOR, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acym'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php')) {
            echo 'This extension cannot work without AcyMailing';
        }

        $listClass = acym_get('class.list');
        $lists = $listClass->getAllWIthoutManagement();
        foreach ($lists as $i => $oneList) {
            if ($oneList->active == 0) {
                unset($lists[$i]);
            }
        }

        if (ACYM_CMS == 'joomla' && $this->value == 'All' && !empty($this->form)) {
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
            $this->value = array_keys($lists);
        }

        return acym_selectMultiple($lists, $this->name, $this->value, ['id' => $this->name], 'id', 'name');
    }
}

