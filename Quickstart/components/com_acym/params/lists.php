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

class JFormFieldLists extends JFormField
{
    var $type = 'lists';

    function getInput()
    {
        if ('Joomla' == 'Joomla' && !include_once(rtrim(JPATH_ADMINISTRATOR, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acym'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php')) {
            echo 'This extension cannot work without AcyMailing';
        }

        $listClass = acym_get('class.list');
        $lists = $listClass->getAll();
        foreach ($lists as $i => $oneList) {
            if ($oneList->active == 0) {
                unset($lists[$i]);
            }
        }

        if ('Joomla' == 'Joomla' && $this->value == 'All') {
            $formId = $this->form->getData()->get('id');
            if (!empty($formId)) {
                $this->value = '';
            }
        }

        if (is_string($this->value)) {
            $this->value = explode(',', $this->value);
        }

        if (in_array('None', $this->value)) {
            $this->value = array();
        }
        if (in_array('All', $this->value)) {
            $this->value = array_keys($lists);
        }

        return acym_selectMultiple($lists, $this->name, $this->value, array('id' => $this->name), 'id', 'name');
    }
}
