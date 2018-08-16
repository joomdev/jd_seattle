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

class cpanelClass extends acymailingClass{

	function load(){
		$query = 'SELECT * FROM '.acymailing_table('config');
		$this->values = acymailing_loadObjectList($query, 'namekey');
	}

	function get($namekey,$default = ''){
		if(isset($this->values[$namekey])) return $this->values[$namekey]->value;
		return $default;
	}

	function save($configObject){
		if(is_array($configObject)) {
			if (isset($configObject['anonymous_tracking']) && empty($configObject['anonymous_tracking'])) {
				$configObject['anonymizeold'] = 1;
			}
		}elseif(is_object($configObject)){
			if (isset($configObject->anonymous_tracking) && empty($configObject->anonymous_tracking)) {
				$configObject->anonymizeold = 1;
			}
		}

		$query = 'REPLACE INTO '.acymailing_table('config').' (namekey,value) VALUES ';
		$params = array();
		$i = 0;

		foreach($configObject as $namekey => $value){
			if(strpos($namekey,'password') !== false && !empty($value) && trim($value,'*') == '') continue;
			$i++;
			if(is_array($value)) $value = implode(',', $value);
			if($i>100){
				$query .= implode(',',$params);
				$affected = acymailing_query($query);
				if($affected === false) return false;
				$i = 0;
				$query = 'REPLACE INTO '.acymailing_table('config').' (namekey,value) VALUES ';
				$params = array();
			}
			if (empty($this->values[$namekey])) $this->values[$namekey] = new stdClass();
			$this->values[$namekey]->value = $value;
			$params[] = '('.acymailing_escapeDB(strip_tags($namekey)).','.acymailing_escapeDB(strip_tags($value)).')';
		}
		if(empty($params)) return true;
		$query .= implode(',',$params);

		try{
			$status = acymailing_query($query);
		}catch(Exception $e){
			$status = false;
		}
		if($status === false) acymailing_display(isset($e) ? $e->getMessage() : substr(strip_tags(acymailing_getDBError()),0,200).'...','error');

		return $status;
	}

}
