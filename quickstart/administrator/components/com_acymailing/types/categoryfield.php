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

class categoryfieldType extends acymailingClass{
	function display($table, $map, $previous){
		$allCats = acymailing_loadObjectList('SELECT DISTINCT category FROM `#__acymailing_'.$table.'` WHERE category NOT LIKE "" ORDER BY category');
		$possibleCats = array();
		$possibleCats[] = acymailing_selectOption('', '- - -');
		$possibleCats[] = acymailing_selectOption('-1', acymailing_translation('ACY_NEW_CATEGORY'));
		if(!empty($allCats)){
			$separator = acymailing_selectOption('-1', '-----------------------------------------');
			$separator->disable = true;
			$possibleCats[] = $separator;
			foreach($allCats as &$oneCat){
				$oneCat->category = htmlspecialchars($oneCat->category);
				$possibleCats[] = acymailing_selectOption($oneCat->category, $oneCat->category);
			}
		}

		$result = acymailing_select($possibleCats, $map, 'onchange="if(this.value == -1){document.getElementById(\'newcategory\').style.display = \'\';}else{document.getElementById(\'newcategory\').style.display = \'none\';}" size="1" style="width:208px;font-size:12px;"', 'value', 'text', htmlspecialchars($previous));
		$result .= '<input type="text" id="newcategory" name="newcategory" class="inputbox" style="display:none;width:200px;"/>';

		return $result;
	}

	function getFilter($table, $map, $previous, $js = ''){
		$allCats = acymailing_loadObjectList('SELECT DISTINCT category FROM '.acymailing_table($table).' WHERE category NOT LIKE "" ORDER BY category');
		$possibleCats = array();
		$possibleCats[] = acymailing_selectOption(0, acymailing_translation('ACY_ALL_CATEGORIES'));
		$catExists = empty($previous);
		if(!empty($allCats)){
			foreach($allCats as &$oneCat){
				$possibleCats[] = acymailing_selectOption($oneCat->category, $oneCat->category);
				if(!$catExists && $oneCat->category == $previous) $catExists = true;
			}
		}
		if(!$catExists) $possibleCats[] = acymailing_selectOption($previous, $previous);

		return acymailing_select($possibleCats, $map, 'size="1"'.$js, 'value', 'text', $previous);
	}
}
