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
class Parameter{
	var $params = null;

	function __construct($string = ''){
		if(is_array($string)){
			$this->params = $string;
		}else{
			$this->setParams($string);
		}
	}

	function get($k, $v = null){
		$return = Arr::getVal($this->params, explode('.', $k), '___NOT_SET___');
		if(!is_string($return) OR $return != '___NOT_SET___'){
			return $return;
		}else{
			return $v;
		}
	}

	function set($k, $v){
		//$this->params[$k] = $v;
		$this->params = Arr::setVal($this->params, explode('.', $k), $v);
	}

	function setParams($string = ''){
		if(strlen(trim(($string))) > 0){
			$data = json_decode($string, true);
			$this->params = $data;
		}else{
			$this->params = array();
		}
	}

	function toString(){
		return json_encode($this->params);
	}

	function toArray(){
		return $this->params;
	}

	function toObject(){
		return json_decode(json_encode($this->params));
	}
}