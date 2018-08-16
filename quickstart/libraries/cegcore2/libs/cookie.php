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
class Cookie {
	public function get($var, $default = null){
		$value = Arr::getVal($_COOKIE, $var, $default);
		
		return $value;
	}
	
	public function set($var, $value, $expiry = 0){
		//$cookies = $_COOKIE;
		//$cookies = Arr::setVal($cookies, $var, $value);
		//$_COOKIE = $cookies;
		setrawcookie($var, $value, $expiry);
	}
}