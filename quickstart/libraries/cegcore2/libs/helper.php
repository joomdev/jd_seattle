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
class Helper {
	use \G2\L\T\GetSet;
	
	var $_vars = array();
	var $data = array();
	var $params = array();
	
	function __construct(&$view = null, $config = []){
		$this->_vars = &$view->_vars;
		$this->data = &$view->data;
		
		if(!empty($config)){
			foreach($config as $k => $v){
				$this->$k = $v;
			}
		}
	}

	function initialize(){
		
	}
	/*
	public function get($var, $default = null){
		$value = Arr::getVal($this->vars, $var, $default);
		
		return $value;
	}
	
	public function set($var, $value){
		$this->vars = Arr::setVal($this->vars, $var, $value);
	}
	
	function data($key, $default = null, $setter = false){
		if($setter){
			$this->data = Arr::setVal($this->data, explode('.', $key), $default);
			return $default;
		}else{
			$value = Arr::getVal($this->data, explode('.', $key), $default);
			return $value;
		}
	}
	*/
}