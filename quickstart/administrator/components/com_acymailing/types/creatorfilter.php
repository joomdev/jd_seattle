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

class creatorfilterType extends acymailingClass{
	var $type = '';
	function load($table){
		$query = 'SELECT COUNT(*) as total,userid FROM '.acymailing_table($table).' WHERE `userid` > 0';
		if(!empty($this->type)) $query .= ' AND `type` = '.acymailing_escapeDB($this->type);
		$query .= ' GROUP BY userid';
		$allusers = acymailing_loadObjectList($query, 'userid');

		$allnames = array();
		if(!empty($allusers)){
			$allnames = acymailing_loadObjectList('SELECT '.$this->cmsUserVars->name.' AS name, '.$this->cmsUserVars->id.' AS id FROM '.acymailing_table($this->cmsUserVars->table, false).' WHERE '.$this->cmsUserVars->id.' IN ('.implode(',',array_keys($allusers)).') ORDER BY '.$this->cmsUserVars->name.' ASC', 'id');
		}

		$this->values = array();
		$this->values[] = acymailing_selectOption('0', acymailing_translation('ALL_CREATORS'));
		foreach($allnames as $userid => $oneCreator){
			$this->values[] = acymailing_selectOption($userid, $oneCreator->name.' ( '.$allusers[$userid]->total.' )' );
		}
	}

	function display($map,$value,$table){
		$this->load($table);
		return acymailing_select(  $this->values, $map, 'class="inputbox" size="1" onchange="document.adminForm.submit( );"', 'value', 'text', (int) $value );
	}
}
