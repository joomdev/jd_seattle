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

class operatorsType extends acymailingClass{
	var $extra = '';
	function __construct(){
		parent::__construct();

		$this->values = array();

		$this->values[] = acymailing_selectOption('<OPTGROUP>', acymailing_translation('ACY_NUMERIC'));
		$this->values[] = acymailing_selectOption('=', '=');
		$this->values[] = acymailing_selectOption('!=', '!=');
		$this->values[] = acymailing_selectOption('>', '>');
		$this->values[] = acymailing_selectOption('<', '<');
		$this->values[] = acymailing_selectOption('>=', '>=');
		$this->values[] = acymailing_selectOption('<=', '<=');
		$this->values[] = acymailing_selectOption('</OPTGROUP>');
		$this->values[] = acymailing_selectOption('<OPTGROUP>', acymailing_translation('ACY_STRING'));
		$this->values[] = acymailing_selectOption('BEGINS', acymailing_translation('ACY_BEGINS_WITH'));
		$this->values[] = acymailing_selectOption('END', acymailing_translation('ACY_ENDS_WITH'));
		$this->values[] = acymailing_selectOption('CONTAINS', acymailing_translation('ACY_CONTAINS'));
		$this->values[] = acymailing_selectOption('NOTCONTAINS', acymailing_translation('ACY_NOT_CONTAINS'));
		$this->values[] = acymailing_selectOption('LIKE', 'LIKE');
		$this->values[] = acymailing_selectOption('NOT LIKE', 'NOT LIKE');
		$this->values[] = acymailing_selectOption('REGEXP', 'REGEXP');
		$this->values[] = acymailing_selectOption('NOT REGEXP', 'NOT REGEXP');
		$this->values[] = acymailing_selectOption('</OPTGROUP>');
		$this->values[] = acymailing_selectOption('<OPTGROUP>', acymailing_translation('OTHER'));
		$this->values[] = acymailing_selectOption('IS NULL', 'IS NULL');
		$this->values[] = acymailing_selectOption('IS NOT NULL', 'IS NOT NULL');
		$this->values[] = acymailing_selectOption('</OPTGROUP>');

	}

	function display($map, $valueSelected = '', $otherClass = ''){
		return acymailing_select($this->values, $map, 'class="inputbox'. (!empty($otherClass)?' '.$otherClass:'') .'" size="1" style="width:120px;" '.$this->extra, 'value', 'text', $valueSelected);
	}

}
