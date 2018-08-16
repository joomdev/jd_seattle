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
	
	protected static $instance;
	var $_active = false;
	var $_expired = false;
	var $_destroyed = false;
	var $_data = array();
	var $_handler = null;
	var $_lifetime = 15;
	var $_encrypted = false;
	var $_secure = false;

	function __construct($handler = 'php', $params = array()){
		if((bool)\G2\Globals::get('app') === false){
			$this->_initialize();
			$this->_setParams($params);
			$this->_setCookies();
			$handler = !empty($handler) ? $handler : Config::get('session.handler', 'php');
			$params = !empty($params) ? $params : array('lifetime' => Config::get('session.lifetime', 15));
			//load handler
			$this->_handler = SessionHandler::getInstance($handler, $params);
		}
		$this->_start();
		$this->_data = &$_SESSION;
		if((bool)\G2\Globals::get('app') === false){
			$this->_sync();
			$this->_validate();
		}
	}

	function __destruct(){
		$this->close();
	}
	
	protected function _start($id = null){
		if(!$this->_active AND !$this->get_id()){
			if(!empty($id)){
				session_id($id);
			}
			session_start();
			$this->_active = true;
			return true;
		}
	}
	
	protected function _sync(){
		if($this->is_alive()){
			$last_active = $this->get('system.now', time(), 'gcore__system');

			if($last_active + ($this->_lifetime * 60) < time()){
				//session expired
				$this->_expired = true;
				$this->restart();
			}
			$this->set('system.last', $last_active, 'gcore__system');
			$this->set('system.now', time(), 'gcore__system');
		}
	}
	
	protected function _validate(){
		$agent = $this->get('user.agent', null, 'gcore__system');
		if(is_null($agent)){
			$this->set('user.agent', $_SERVER['HTTP_USER_AGENT'], 'gcore__system');
			return true;
		}
		if($agent != $_SERVER['HTTP_USER_AGENT']){
			//incorrect agent
			$this->restart();
		}
	}
	
	protected function _initialize(){
		if(session_id()){
			session_unset();
			session_destroy();
		}
		session_cache_limiter(false);
		ini_set('session.save_handler', 'files');
		ini_set('session.use_trans_sid', 0);
		ini_set('session.use_only_cookies', '1');
	}
	
	protected function _setParams($params){
		if(isset($params['name'])){
			session_name($params['name']);
		}else{
			session_name(md5('gcore_'.GCORE_SITE.mt_rand()));
		}
		if(isset($params['id'])){
			session_id($params['id']);
		}
		if(isset($params['lifetime'])){
			$this->_lifetime = $params['lifetime'];			
		}
		ini_set('session.gc_maxlifetime', $this->_lifetime * 60);
		
		if(!empty($params['encrypted'])){
			$this->_encrypted = true;
		}
		if(!empty($params['secure'])){
			$this->_secure = true;
		}
	}
	
	protected function _setCookies(){
		$cookie = session_get_cookie_params();
		if ($this->_secure){
			$cookie['secure'] = true;
		}
		if(strlen(Config::get('cookie.domain', '')) > 0){
			$cookie['domain'] = Config::get('cookie.domain', '');
		}
		if(strlen(Config::get('cookie_path', '')) > 0){
			$cookie['path'] = Config::get('cookie.path', '');
		}
		session_set_cookie_params($cookie['lifetime'], $cookie['path'], $cookie['domain'], $cookie['secure']);
	}

	public static function getInstance($handler = null, $params = array()){
		if(!is_object(self::$instance)){
			self::$instance = new Session($handler, $params);
		}
		return self::$instance;
	}
	
	function get_name(){
		return session_name();
	}
	
	function get_id(){
		return session_id();
	}
	
	function is_alive(){
		if((bool)\G2\Globals::get('app') === false){
			if(!$this->_active OR $this->_expired OR $this->_destroyed){
				return false;
			}
		}
		return true;
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
	/*
	function setFlash($type, $msg = '', $group = ''){
		$flashes = $this->get('__FLASH__', array());
		$path = array($type);
		if(!empty($group)){
			$path = array($type, $group);
		}
		$type_msgs = Arr::getVal($flashes, $path, array());
		$type_msgs = array_merge($type_msgs, (array)$msg);
		return $this->set('__FLASH__', Arr::setVal($flashes, $path, $type_msgs));
	}
	
	function getFlash($type = null){
		$flashes = $this->get('__FLASH__', array());
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
	*/
	function close(){
		session_write_close();
	}
	
	function regenerate(){
		session_regenerate_id(true);
	}
	
	function destroy(){
		if($this->_destroyed === false){
			if ($this->_destroyed = $this->_destroy()){
				$this->_data = array();
			}
		}
		return $this->_destroyed;
	}
	
	protected function _destroy(){
		session_destroy();
		$return = !session_id();
		if($return AND isset($_COOKIE[session_name()])){
			//remove any session presence in the cookies
			setcookie(session_name(), '', 0, Config::get('cookie.path', ''), Config::get('cookie.domain', ''));
		}
		return $return;
	}
	
	function restart(){
		if($this->_destroyed === false){
			$this->destroy();
		}
		$this->_reset_flags();
		return $this->_restart();
	}
	
	protected function _restart(){
		$this->regenerate();
		$return = $this->_start(Str::rand());		
		$this->_sync();
		$this->_data = &$_SESSION;
		return $return;
	}
	
	protected function _reset_flags(){
		$this->_destroyed = false;
		$this->_expired = false;
		$this->_active = false;
	}
}