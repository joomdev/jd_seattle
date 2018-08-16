<?php
/**
* ChronoCMS version 1.0
* Copyright (c) 2012 ChronoCMS.com, All rights reserved.
* Author: (ChronoCMS.com Team)
* license: Please read LICENSE.txt
* Visit http://www.ChronoCMS.com for regular updates and information.
**/
namespace G2\L\DatabaseAdapters;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class Pdo extends \G2\L\DatabaseObject {
	
	function __construct($options, $driver_options = null){
		try{
			$this->adapter = new \PDO($options['type'].':dbname='.$options['name'].';host='.$options['host'], $options['user'], $options['pass'], $driver_options);
		}catch(\PDOException $e){
			$this->error = $e->getMessage();
			return $this;
		}
		
		$this->connected = true;
		$this->adapter->exec('SET CHARACTER SET utf8');
		$this->adapter->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_WARNING);
		//$this->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
		
		return $this;
	}
	
	function getTablesList(){
		$tables = array();
		$sql = 'SHOW TABLES';
		$query = $this->query($sql);
		$query->execute();
		$this->_log($sql);
		$result = $query->fetchAll(\PDO::FETCH_ASSOC);
		foreach($result as $r){
			$clean = array_values($r);
			$tables[] = $clean[0];
		}
		return $tables;
	}
	
	function _getTableInfo($tablename){
		$sql = $this->_prefixTable('DESCRIBE '.$this->quoteName($tablename));
		//$query = $this->adapter->query($sql);
		$query = $this->adapter->prepare($sql);
		$query->execute();
		$this->_log($sql);
		$result = $query->fetchAll(\PDO::FETCH_ASSOC);
		return $result;
	}
	
	function loadAssoc($sql, $params = array()){
		$sql = $this->_close($sql);
		$sql = $this->_prefixTable($sql);
		$query = $this->adapter->prepare($sql);
		$query->execute($params);
		//$this->_log($sql, $params);
		return $data = $query->fetch(\PDO::FETCH_ASSOC);
	}
	
	function loadAssocList($sql, $params = array()){
		$sql = $this->_close($sql);
		$sql = $this->_prefixTable($sql);
		$query = $this->adapter->prepare($sql);
		$query->execute($params);
		//$this->_log($sql, $params);
		return $data = $query->fetchAll(\PDO::FETCH_ASSOC);
	}
	
	function checkDriver($d){
		return in_array($d, \PDO::getAvailableDrivers());
	}	
	//override the query() function to terminate execution
	function query($statement){
		$pdo_state = $this->adapter->query($statement);
		if($pdo_state === false){
			echo 'Database Error:'."\n";
			//pr($this->adapter->errorInfo());
			//die();
		}
		return $pdo_state;
	}
	
	function quote($v){
		return $this->adapter->quote($v);
	}
	
	function exec($sql){
		return $this->adapter->exec($sql);
	}
	
	function execute_query($sql, $params = array(), $driver_options = array()){
		$query = $this->adapter->prepare($sql, $driver_options);
		return $query->execute($params);
	}
	
	function lastInsertId(){
		return $this->adapter->lastInsertId();
	}
}