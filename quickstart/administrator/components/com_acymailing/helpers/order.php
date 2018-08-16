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

class acyorderHelper{

	var $table = '';
	var $pkey = '';
	var $groupMap = '';
	var $groupVal = '';

	function order($down = true){

		if($down){
			$sign = '>';
			$dir = 'ASC';
		}else{
			$sign = '<';
			$dir = 'DESC';
		}

		$ids = acymailing_getVar('array',  'cid', array(), '');
		$id = (int) $ids[0];

		$pkey = $this->pkey;

		$query = 'SELECT a.ordering,a.'.$pkey.' FROM '.acymailing_table($this->table).' as b, '.acymailing_table($this->table).' as a';
		$query .= ' WHERE a.ordering '.$sign.' b.ordering AND b.'.$pkey.' = '.$id;
		if(!empty($this->groupMap)) $query .= ' AND a.'.$this->groupMap.' = '.acymailing_escapeDB($this->groupVal);
		$query .= ' ORDER BY a.ordering '.$dir.' LIMIT 1';
		$secondElement = acymailing_loadObject($query);

		if(empty($secondElement)) return false;

		$firstElement = new stdClass();
		$firstElement->$pkey = $id;
		$firstElement->ordering = $secondElement->ordering;
		if($down)$secondElement->ordering--;
		else $secondElement->ordering++;


		$status1 = acymailing_updateObject(acymailing_table($this->table),$firstElement,$pkey);
		$status2 = acymailing_updateObject(acymailing_table($this->table),$secondElement,$pkey);

		$status = $status1 && $status2;
		if($status){
			acymailing_enqueueMessage(acymailing_translation( 'SUCC_MOVED' ), 'message');
		}

		return $status;
	}

	function save(){
		$pkey = $this->pkey;

		$cid	= acymailing_getVar('array',  'cid', array());
		$order	= acymailing_getVar('array',  'order', array());

		acymailing_arrayToInteger($cid);

		$query = 'SELECT `ordering`,`'.$pkey.'` FROM '.acymailing_table($this->table).' WHERE `'.$pkey.'` NOT IN ('.implode(',',$cid).') ';
		if(!empty($this->groupMap)) $query .= ' AND '.$this->groupMap.' = '.acymailing_escapeDB($this->groupVal);
		$query .= ' ORDER BY `ordering` ASC';
		$results = acymailing_loadObjectList($query, $pkey);

		$oldResults = $results;

		asort($order);

		$newOrder = array();
		while(!empty($order) OR !empty($results)){
			$dbElement = reset($results);
			if(empty($dbElement->ordering) OR (!empty($order) AND reset($order) <= $dbElement->ordering)){
				$newOrder[] = $cid[(int)key($order)];
				unset($order[key($order)]);
			}else{
				$newOrder[] = $dbElement->$pkey;
				unset($results[$dbElement->$pkey]);
			}
		}

		$i = 1;
		$status = true;
		$element = new stdClass();
		foreach($newOrder as $val){
			$element->$pkey = $val;
			$element->ordering = $i;
			if(!isset($oldResults[$val]) OR $oldResults[$val]->ordering != $i){
				$status = acymailing_updateObject(acymailing_table($this->table),$element,$pkey) && $status;
			}
			$i++;
		}

		if($status){
			acymailing_enqueueMessage(acymailing_translation( 'ACY_NEW_ORDERING_SAVED' ), 'message');
		}else{
			acymailing_enqueueMessage(acymailing_translation( 'ERROR_ORDERING' ), 'error');
		}
		return $status;
	}

	function reOrder(){
		$query = 'UPDATE '.acymailing_table($this->table).' SET `ordering` = `ordering`+1';
		if(!empty($this->groupMap)) $query .= ' WHERE '.$this->groupMap.' = '.acymailing_escapeDB($this->groupVal);

		acymailing_query($query);

		$query = 'SELECT `ordering`,`'.$this->pkey.'` FROM '.acymailing_table($this->table);
		if(!empty($this->groupMap)) $query .= ' WHERE '.$this->groupMap.' = '.acymailing_escapeDB($this->groupVal);
		$query .= ' ORDER BY `ordering` ASC';
		$results = acymailing_loadObjectList($query);

		$i = 1;
		foreach($results as $oneResult){
			if($oneResult->ordering != $i){
				$oneResult->ordering = $i;
				acymailing_updateObject( acymailing_table($this->table), $oneResult, $this->pkey);
			}
			$i++;
		}
	}

}
