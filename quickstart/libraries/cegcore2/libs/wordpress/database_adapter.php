<?php
/**
* ChronoCMS version 1.0
* Copyright (c) 2012 ChronoCMS.com, All rights reserved.
* Author: (ChronoCMS.com Team)
* license: Please read LICENSE.txt
* Visit http://www.ChronoCMS.com for regular updates and information.
**/
namespace G2\L\Wordpress;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class DatabaseAdapter extends \G2\L\DatabaseObject {
	
	function __construct($options = array(), $driver_options = null){
		
		if(!empty($options)){
			$wpdbNew = new \wpdb($options['user'], $options['pass'], $options['name'], $options['host']);
			$this->adapter = $wpdbNew;
		}else{
			global $wpdb;
			$this->adapter = $wpdb;
		}
	}
	
	function getTablesList(){
		$tables = array();
		$sql = 'SHOW TABLES';
		$this->_log($sql);
		$result = $this->loadAssocList($sql);
		foreach($result as $r){
			$clean = array_values($r);
			$tables[] = $clean[0];
		}
		return $tables;
	}
	
	function _getTableInfo($tablename){
		$sql = 'DESCRIBE '.$this->quoteName($tablename);
		$this->_log($sql);
		$result = $this->loadAssocList($sql);
		return $result;
	}
	
	function loadAssoc($sql, $params = array()){
		//$this->adapter->setQuery($sql);
		//$this->_log($sql);
		$data = $this->adapter->get_row($sql, ARRAY_A);
		return $data;
	}
	
	function loadAssocList($sql, $params = array()){
		//$this->adapter->setQuery($sql);
		//$this->_log($sql);
		$data = $this->adapter->get_results($sql, ARRAY_A);
		return $data;
	}
	
	function checkDriver($d){
		return true;
	}	
	//override the query() function to terminate execution
	function query($statement){
		$pdo_state = $this->adapter->query($statement);
		if($pdo_state === false){
			echo 'Database Error:'."\n";
			//pr($this->adapter->last_error);
			//die();
		}
		return $pdo_state;
	}
	
	function quote($v){
		return "'".$this->adapter->escape($v)."'";
	}
	
	function exec($sql){
		//$this->adapter->setQuery($sql);
		if($result = $this->adapter->query($sql)){
			return $result;
		}else{
			return false;
		}
	}
	
	function execute_query($sql, $params = array(), $driver_options = array()){
		foreach($params as $k => $v){
			if(!is_null($v)){
				$v = $this->quote($v);
			}else{
				$v = 'NULL';
			}
			//$sql = preg_replace('/:'.$k.'( |,|;|\))/', $v."$1", $sql);
			$sql = preg_replace('/:'.$k.'( |,|;|\))/', ':'.$k.':'."$1", $sql);
			$sql = str_replace(':'.$k.':', $v, $sql);
		}
		//$this->adapter->setQuery($sql);
		return $this->adapter->query($sql);
	}
	
	function lastInsertId(){
		return $this->adapter->insert_id;
	}
	
	function split_sql($sql){
		$statements = explode(";", $sql);
		$statements = array_filter($statements);
		return $statements;
	}
}