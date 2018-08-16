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
class Boot {
	
	function __construct(){
		self::initialize();
		
		\G2\Globals::set('FRONT_URL', \G2\L\Url::root());
		\G2\Globals::set('ADMIN_URL', \G2\L\Url::root().'admin/');
		\G2\Globals::set('ROOT_URL', \G2\Globals::get('FRONT_URL'));
		
		\G2\Globals::set('ROOT_PATH', dirname(dirname(__FILE__)).DS);
		
		\G2\Globals::set('CURRENT_PATH', \G2\Globals::get(''.strtoupper(GCORE_SITE).'_PATH'));
		\G2\Globals::set('CURRENT_URL', \G2\Globals::get(''.strtoupper(GCORE_SITE).'_URL'));
	}
	
	function initialize(){
		//CONSTANTS
		\G2\Globals::set('FRONT_PATH', dirname(dirname(__FILE__)).DS);
		\G2\Globals::set('ADMIN_PATH', dirname(dirname(__FILE__)).DS.'admin'.DS);
		//initialize language
		\G2\L\Lang::initialize();
		//SET ERROR CONFIG
		if((int)\G2\L\Config::get('error.reporting') != 1){
			error_reporting((int)\G2\L\Config::get('error.reporting'));
		}
		if((bool)\G2\L\Config::get('error.debug') === true){
			\G2\L\Error::initialize();
		}
		//timezone
		date_default_timezone_set(\G2\L\Config::get('site.timezone', 'UTC'));
	}
}