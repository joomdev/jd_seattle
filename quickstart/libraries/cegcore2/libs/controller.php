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
class Controller {
	use \G2\L\T\GetSet;
	use \G2\L\T\View;
	use \G2\L\T\Model;
	use \G2\L\T\Helper;
	
	var $name = '';
	var $action = '';
	var $extension = '';
	var $site = '';
	
	var $libs = [];
	var $models = [];
	var $helpers = [];
	
	var $_vars = [];
	var $path = '';
	var $url = '';
	
	var $view = [];
	//var $views_dir = '';
	
	var $data = [];
	var $theme = 'default';
	var $layouts = [];
	var $errors = [];
	
	function __construct($site = GCORE_SITE){
		$app = \GApp::instance($site);
		$this->site = $site;
		$this->_vars = &$app->_vars;
		$this->data = &Request::raw();//&$_POST;
		//$this->data = array_merge($_GET, $this->data);
		$this->path = $app->path;
		$this->url = $app->url;
		$this->name = get_class($this);
		$this->alias = Base::getClassName(get_class($this));
		$this->action = $app->action;
		$this->extension = $app->extension;
		$this->tvout = $app->tvout;
		//set models properties
		if(!empty($this->models)){
			$this->models = (array)$this->models;
			foreach($this->models as $k => $model){
				if(is_numeric($k)){
					$alias = Base::getClassName($model);
					$this->$alias = new $model();
				}
			}			
		}
		//set libs properties
		if(!empty($this->libs)){
			$this->libs = (array)$this->libs;
			foreach($this->libs as $lib){
				$alias = Base::getClassName($lib);
				
				$this->$alias = new $lib($this);
			}			
		}
	}
	
	function _initialize(){
		
	}
	
	function _finalize(){
		
	}
	
	function getController($controller, $ext = null){
		$extension = '';
		if(is_null($ext)){
			$extension = $this->extension;
		}else{
			$extension = $ext;
		}
		
		$classname = '\G2\E\\'.Str::camilize($extension).'\C\\'.Str::camilize($controller);
		${$classname} = new $classname($this->site);
		//$continue = ${$classname}->_initialize();
		${$classname}->extension = $extension;
		${$classname}->action = '';
		
		return ${$classname};
	}
	/*
	function set($key, $value = null){
		if(isset($this->_vars[$key])){
			//\GApp::session()->flash('error', rl('$'.$key.' var is already set.'));
		}
		
		if(is_array($key)){
			$this->_vars = array_merge($this->_vars, $key);
			return;
		}
		$this->_vars[$key] = $value;
	}
	
	function get($key, $default = null){
		if(isset($this->_vars[$key])){
			return $this->_vars[$key];
		}
		return $default;
	}
	*/
	/*
	public function get($var, $default = null){
		$value = Arr::getVal($this->_vars, $var, $default);
		
		return $value;
	}
	
	public function set($var, $value){
		$this->_vars = Arr::setVal($this->_vars, $var, $value);
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
	function layout($layout){
		$this->layouts[] = $layout;
	}
	
	function redirect($url){
		Env::redirect($url);
	}
}