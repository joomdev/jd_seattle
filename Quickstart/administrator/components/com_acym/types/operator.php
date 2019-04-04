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

class operatorType extends acymClass
{
    var $values = array();
    var $class = 'acym__select';
    var $extra = '';

    function __construct()
    {
        parent::__construct();

        $this->values[] = acym_selectOption('=', '=');
        $this->values[] = acym_selectOption('!=', '!=');
        $this->values[] = acym_selectOption('>', '>');
        $this->values[] = acym_selectOption('<', '<');
        $this->values[] = acym_selectOption('>=', '>=');
        $this->values[] = acym_selectOption('<=', '<=');
        $this->values[] = acym_selectOption('BEGINS', acym_translation('ACYM_BEGINS_WITH'));
        $this->values[] = acym_selectOption('END', acym_translation('ACYM_ENDS_WITH'));
        $this->values[] = acym_selectOption('CONTAINS', acym_translation('ACYM_CONTAINS'));
        $this->values[] = acym_selectOption('NOTCONTAINS', acym_translation('ACYM_NOT_CONTAINS'));
        $this->values[] = acym_selectOption('LIKE', 'LIKE');
        $this->values[] = acym_selectOption('NOT LIKE', 'NOT LIKE');
        $this->values[] = acym_selectOption('REGEXP', 'REGEXP');
        $this->values[] = acym_selectOption('NOT REGEXP', 'NOT REGEXP');
        $this->values[] = acym_selectOption('IS NULL', 'IS NULL');
        $this->values[] = acym_selectOption('IS NOT NULL', 'IS NOT NULL');
    }

    function display($name, $valueSelected = '', $class = '')
    {
        return acym_select($this->values, $name, $valueSelected, $this->extra.' class="'.$this->class.' '.$class.'"');
    }
}
