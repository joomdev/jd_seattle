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
class User{
	var $data = [];
	
	function __construct($user_id = 0){
		$session = \GApp::session();
		$user = $session->get('user');
		if(empty($user)){
			$user = Authenticate::set_public_user();
		}
		
		if(empty($this->get('guest_id'))){
			$this->set('guest_id', $this->get('id'));
		}
		/*
		foreach($user as $k => $v){
			$this->set($k, $v);
		}
		*/
		//return Obj::getInstance('user', $user);
	}
	
	public static function getInstance($user_id = 0){
		static $instances;
		if(!isset($instances)){
			$instances = array();
		}
		if(empty($instances[$user_id])){
			//$instances[$user_id] = new self($user_id);
			if(\G2\Globals::get('app')){
				//$user = '\G2\L\Users\User'.strtoupper(\G2\Globals::get('app'));
				$user = \G2\Globals::getClass('user');
				$instances[$user_id] = new $user($user_id);
			}else{
				$instances[$user_id] = new self($user_id);
			}
			return $instances[$user_id];
		}else{
			return $instances[$user_id];
		}
	}
	
	public function get($key, $default = null){
		//$value = Arr::getVal($this->data, $key, $default);
		$value = \GApp::session()->get('user.'.$key, $default);
		
		return $value;
	}
	
	public function set($key, $value){
		//$this->data = Arr::setVal($this->data, $key, $value);
		\GApp::session()->set('user.'.$key, $value);
	}
	
	public static function login($username, $password){
		return false;
	}
	
	public static function model($type = 'user'){
		if($type == 'user'){
			return (new \G2\A\M\User());
		}
	}
	
}