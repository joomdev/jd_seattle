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
class Database {
	/*
	private static function _setOptions($options = array()){
		if(empty($options)){
			$options['user'] = Config::get('db.user');
			$options['pass'] = Config::get('db.pass');
			$options['name'] = Config::get('db.name');
			$options['host'] = Config::get('db.host');
			$options['type'] = Config::get('db.type');
			$options['prefix'] = Config::get('db.prefix');
		}
		return $options;
	}
	*/
	public static function getInstance($options = array()){
		static $instances;
		if(!isset($instances)){
			$instances = array();
		}
		//$options = self::_setOptions($options);
		if(empty($options)){
			$options['user'] = Config::get('db.user');
			$options['pass'] = Config::get('db.pass');
			$options['name'] = Config::get('db.name');
			$options['host'] = Config::get('db.host');
			$options['type'] = Config::get('db.type');
			$options['prefix'] = Config::get('db.prefix');
		}
		/*
		if(empty($adapter)){
			$adapter = Config::get('db.adapter', 'pdo');
		}
		*/
		ksort($options);
		$id = md5(serialize($options));
		if(empty($instances[$id]) OR empty($instances[$id]->connected)){
			$instances[$id] = \G2\L\DatabaseObject::getInstance($options);
			/*if(!empty($instances[$id])){
				$instances[$id]->connected = true;
				//$instances[$id]->_initialize($options);
			}*/
			return $instances[$id];
		}else{
			return $instances[$id];
		}
	}
}