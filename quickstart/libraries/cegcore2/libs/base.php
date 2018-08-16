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
class Base {
	/*
	public static function getSession(){
		return Session::getInstance(self::getConfig('session_handler', 'php'), array(
			'lifetime' => self::getConfig('session_lifetime', 15)
		));
	}
	
	public static function getUser(){
		$session = self::getSession();
		$user = $session->get('user');
		if(empty($user)){
			$user = Authenticate::set_public_user();
		}
		return $user;
	}
	
	public static function getDocument(){
		return Document::getInstance();
	}
	
	public static function getConfig($key, $default = null){
		if(isset(\G2\Config::$$key)){
			return \G2\Config::$$key;
		}else{
			return $default;
		}
	}
	
	public static function setConfig($key, $value){
		if(isset(\G2\Config::$$key)){
			\G2\Config::$$key = $value;
		}
	}
	*/
	
	public static function getClassName($class){
		$class_name = explode('\\', rtrim($class, '\\'));
		return $class_name[count($class_name) - 1];
	}
	
	public static function getClassPath($class){
		$class_name = explode('\\', rtrim($class, '\\'));
		array_pop($class_name);
		return implode('\\', $class_name);
	}
	/*
	public static function getSettings($ext){
		static $loaded;
		if(isset($loaded[$ext])){
			return $loaded[$ext];
		}else{
			$settings_model = new \G2\A\M\Extension();
			$settings = $settings_model->find('first', array('conditions' => array('name' => $ext), 'cache' => true, 'recursive' => -1));
			if(!empty($settings['Extension']['settings'])){
				return $loaded[$ext] = new Parameter($settings['Extension']['settings']);
			}else{
				return $loaded[$ext] = new Parameter('');
			}
		}
	}
	*/
}