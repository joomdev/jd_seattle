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
class Session {
	var $_data = array();
	var $_open = false;
	var $name = '';
	var $settings = null;
	
	function __construct($params){
		$this->settings = $params;
		$this->open();
		//$this->_data = &$_SESSION;
	}
	
	public static function getInstance($handler = 'php', $params = array()){
		static $instances;
		if(!isset($instances)){
			$instances = array();
		}
		if(empty($instances[$handler])){
			
			if($handler == 'php'){
				$class = '\G2\L\Session';
			}else{
				$class = '\G2\L\SessionHandlers\\'.Str::camilize($handler);
			}
			
			$instances[$handler] = new $class($handler);
			return $instances[$handler];
		}else{
			return $instances[$handler];
		}
	}
	
	function open(){
		if(session_status() == PHP_SESSION_NONE){
			//pr('open session');
			if(!empty($this->settings['lifetime'])){
				ini_set('session.gc_maxlifetime', (int)$this->settings['lifetime'] * 60);
			}
			
			session_start();
		}
		
		$this->_open = true;
		
		$this->_data = &$_SESSION;
	}
	
	function close(){
		if(session_status() == PHP_SESSION_ACTIVE){
			session_unset();
			session_destroy();
			
			$this->_open = false;
		}
	}
	
	function is_alive(){
		return $this->_open;
		//return true;
	}
	
	function get($name, $default = null, $namespace = 'gcore'){
		if(!$this->is_alive()){
			return null;
		}
		$value = Arr::getVal($this->_data, explode('.', $namespace.'.'.$name));
		if($value !== null){
			return $value;
		}
		return $default;
	}
	
	function set($name, $value = null, $namespace = 'gcore'){
		if(!$this->is_alive()){
			return null;
		}
		$this->_data = Arr::setVal($this->_data, explode('.', $namespace.'.'.$name), $value);
		return true;
	}
	
	function has($name, $namespace = 'gcore'){
		if(!$this->is_alive()){
			return null;
		}
		return (Arr::getVal($this->_data, explode('.', $namespace.'.'.$name)) !== null);
	}
	
	function clear($name, $namespace = 'gcore'){
		if(!$this->is_alive()){
			return null;
		}
		$this->_data = Arr::setVal($this->_data, explode('.', $namespace.'.'.$name), null);
		return true;
	}
	
	function flash($type = null, $msg = '', $group = ''){
		$flashes = $this->get('__FLASH__', array());
		if(!empty($msg)){
			//set flash
			$path = array($type);
			if(!empty($group)){
				$path = array($type, $group);
			}
			$type_msgs = Arr::getVal($flashes, $path, array());
			$type_msgs = array_merge($type_msgs, (array)$msg);
			return $this->set('__FLASH__', Arr::setVal($flashes, $path, $type_msgs));
		}else{
			//get flash
			$this->clear('__FLASH__');
			if(!empty($type)){
				if(!empty($flashes[$type])){
					return $flashes[$type];
				}
				return array();
			}else{
				//get all
				return $flashes;
			}
		}
	}
}