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

class detailstatsbounceType extends acymailingClass{
	function display($map, $value){
		$query = 'SELECT DISTINCT bouncerule FROM '.acymailing_table('userstats').' WHERE bouncerule IS NOT NULL';
		$bouncerules = acymailing_loadObjectList($query);
		if(empty($bouncerules)) return '';
		$valueBounce = array();
		$valueBounce[] = acymailing_selectOption(0, acymailing_translation('ALL_RULES'));
		foreach($bouncerules as $oneRule){
			$found = preg_match('#^([A-Z0-9_]*) \[#Uis', $oneRule->bouncerule, $match);
			$text = $found ? str_replace($match[1], acymailing_translation($match[1]), $oneRule->bouncerule) : $oneRule->bouncerule;
			$valueBounce[] = acymailing_selectOption($oneRule->bouncerule, $text);
		}
		return acymailing_select($valueBounce, $map, 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', $value);
	}
}


