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
class Request {
	public static $data = array();
	
	public static function post($key, $default = null){
		if(isset($_POST[$key])){
			return $_POST[$key];
		}else{
			return $default;
		}
	}
	
	public static function get($key, $default = null){
		if(isset($_GET[$key])){
			return $_GET[$key];
		}else{
			return $default;
		}
	}
	
	public static function data($key, $default = null){
		self::$data = &self::raw();
		//check POST
		$value = Arr::getVal(self::$data, explode('.', $key), null);
		if(!is_null($value)){
			return $value;
		}
		/*
		//check POST
		$post = Arr::getVal($_POST, explode('.', $key), null);
		if(!is_null($post)){
			return $post;
		}
		//check GET
		$get = Arr::getVal($_GET, explode('.', $key), null);
		if(!is_null($get)){
			return $get;
		}
		*/
		//return default
		return $default;
	}
	
	public static function set($key, $value){
		self::$data = Arr::setVal(self::$data, explode('.', $key), $value);
	}
	
	public static function &raw(){
		self::$data = array_merge($_REQUEST, $_GET, $_POST, Arr::getVal($_FILES, '[n].name', []), self::$data);
		return self::$data;
	}
	
}