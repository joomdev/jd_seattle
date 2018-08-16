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

class listsType extends acymailingClass{
	function __construct(){
		parent::__construct();

		$listClass = acymailing_get('class.list');
		$this->data = $listClass->getLists('listid');
	}

	function display($map, $value, $js = true, $clickableCategories = false){
		if(empty($this->values)) $this->getValues($clickableCategories);
		$onchange = $js ? 'onchange="document.adminForm.limitstart.value=0;document.adminForm.submit();"' : '';
		return acymailing_select($this->values, $map, 'class="inputbox" style="max-width:220px" size="1" '.$onchange, 'value', 'text', $value, str_replace(array('[', ']'), array('_', ''), $map));
	}

	function getData(){
		return $this->data;
	}

	function getValues($clickableCategories = false){
		$allCats = array();
		foreach($this->data as $oneList){
			if(empty($oneList->category)) $oneList->category = acymailing_translation('ACY_NO_CATEGORY');
			$allCats[$oneList->category][] = $oneList->listid;
		}

		$this->values = array();
		$this->values[] = acymailing_selectOption('0', acymailing_translation('ALL_LISTS'));
		foreach($allCats as $name => $lists){
			if($clickableCategories){
				$this->values[] = acymailing_selectOption(implode(',', $lists).',', $name);
			}else{
				$this->values[] = acymailing_selectOption('<OPTGROUP>', $name);
			}

			foreach($lists as $listId){
				$this->values[] = acymailing_selectOption($listId, (count($allCats) > 1 ? ' - - ' : '').$this->data[$listId]->name);
			}

			if(!$clickableCategories) $msgType[] = acymailing_selectOption('</OPTGROUP>');
		}
		return $this->values;
	}
}
