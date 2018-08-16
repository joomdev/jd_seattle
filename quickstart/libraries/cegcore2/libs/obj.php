<?php
/**
* ChronoCMS version 1.0
* Copyright (c) 2012 ChronoCMS.com, All rights reserved.
* Author: (ChronoCMS.com Team)
* license: Please read LICENSE.txt
* Visit http://www.ChronoCMS.com for regular updates and information.
**/
namespace G2\L;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class Obj{
	var $data = [];
	
	function __construct($data = []){
		foreach($data as $k => $v){
			$this->set($k, $v);
		}
	}
	
	public static function getInstance($name, $data = []){
		static $instances;
		if(!isset($instances)){
			$instances = array();
		}
		if(empty($instances[$name])){
			$instances[$name] = new self($data);
			
			return $instances[$name];
		}else{
			return $instances[$name];
		}
	}
	
	public function get($key, $default = null){
		$value = Arr::getVal($this->data, $key, $default);
		
		return $value;
	}
	
	public function set($key, $value){
		$this->data = Arr::setVal($this->data, $key, $value);
	}
	
	function toString(){
		return json_encode($this);
	}
	
	function toArray(){
		return (array)$this;
	}
}