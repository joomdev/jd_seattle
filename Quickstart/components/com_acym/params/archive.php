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

class JFormFieldArchive extends JFormField
{
    var $type = 'archive';

    function getInput()
    {
        if ('Joomla' == 'Joomla' && !include_once(rtrim(JPATH_ADMINISTRATOR, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_acym'.DIRECTORY_SEPARATOR.'helpers'.DIRECTORY_SEPARATOR.'helper.php')) {
            echo 'This extension cannot work without AcyMailing';
        }

        $value = empty($this->value) ? 0 : $this->value;

        $return = '<input type="number" value="'.$value.'" name="'.$this->name.'" min="0">';

        return $return;
    }
}
