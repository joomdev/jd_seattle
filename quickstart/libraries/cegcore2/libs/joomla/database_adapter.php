<?php
/**
* ChronoCMS version 1.0
* Copyright (c) 2012 ChronoCMS.com, All rights reserved.
* Author: (ChronoCMS.com Team)
* license: Please read LICENSE.txt
* Visit http://www.ChronoCMS.com for regular updates and information.
**/
namespace G2\L\Joomla;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class DatabaseAdapter extends \G2\L\DatabaseObject {
	
	function __construct($options = array(), $driver_options = null){
		//$this->adapter = \JFactory::getDBO();
		if(empty($options['type']) OR empty($options['host'])){
			return false;
		}
		$params = array();
		$params['driver'] = $options['type'];
		$params['host'] = $options['host'];
		$params['user'] = $options['user'];
		$params['password'] = $options['pass'];
		$params['database'] = $options['name'];
		$params['prefix'] = $options['prefix'];
		$this->adapter = \JDatabase::getInstance($params);
		
		$_adapter = (array)$this->adapter;
		//if(is_object($_adapter['*connection'])){
		if(strpos(json_encode($_adapter), 'client_info') !== false){
			$this->connected = true;
		}
		
		$this->connected = true;
		try{
			$result = $this->getTablesList();
		}catch(\Exception $e){
			$this->connected = false;
		}
		//$mainframe = \JFactory::getApplication();
		//date_default_timezone_set($mainframe->getCfg('offset'));
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
		$this->adapter->setQuery($sql);
		//$this->_log($sql);
		$data = $this->adapter->loadAssoc();
		
		//$mainframe = \JFactory::getApplication();
		//date_default_timezone_set($mainframe->getCfg('offset'));
		
		return $data;
	}
	
	function loadAssocList($sql, $params = array()){
		$this->adapter->setQuery($sql);
		//$this->_log($sql);
		$data = $this->adapter->loadAssocList();
		
		//$mainframe = \JFactory::getApplication();
		//date_default_timezone_set($mainframe->getCfg('offset'));
		
		return $data;
	}
	
	function checkDriver($d){
		return true;
	}	
	//override the query() function to terminate execution
	/*function query($statement){
		$pdo_state = $this->adapter->query($statement);
		if($pdo_state === false){
			echo 'Database Error:'."\n";
			pr($this->adapter->errorInfo());
			die();
		}
		return $pdo_state;
	}*/
	
	function quote($v){
		return $this->adapter->quote($v);
	}
	
	function exec($sql){
		$this->adapter->setQuery($sql);
		if($this->adapter->query()){
			//$mainframe = \JFactory::getApplication();
			//date_default_timezone_set($mainframe->getCfg('offset'));
		
			return $this->adapter->getAffectedRows();
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
		$this->adapter->setQuery($sql);
		$result = $this->adapter->query();
		//Joomla chnages the timezone after the previous function!! !@#!@#!@??
		//$mainframe = \JFactory::getApplication();
		//date_default_timezone_set($mainframe->getCfg('offset'));
		return $result;
	}
	
	function lastInsertId(){
		return $this->adapter->insertid();
	}
	
	function split_sql($sql){
		return $this->adapter->splitSql($sql);
	}
}