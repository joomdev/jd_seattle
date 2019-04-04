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

class operatorinType extends acymClass
{
    var $values = array();
    var $class = 'acym__select';
    var $extra = '';

    function __construct()
    {
        parent::__construct();

        $this->values[] = acym_selectOption('in', acym_translation('ACYM_IN'));
        $this->values[] = acym_selectOption('not-in', acym_translation('ACYM_NOT_IN'));
    }

    function display($name, $valueSelected = '')
    {
        return acym_select($this->values, $name, $valueSelected, $this->extra.' class="'.$this->class.'"');
    }
}
