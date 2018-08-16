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
class View {
	use \G2\L\T\GetSet;
	
	var $site = '';
	var $extension = '';
	var $controller = '';
	var $action = '';
	
	var $view = true;
	var $_vars = array();
	var $data = array();
	var $errors = array();
	//var $views_site = '';
	var $layouts = [];
	var $theme = 'default';
	var $tvout = 'view';

	function __construct(&$controller = null){
		/*if(!empty($controller->_vars)){
			$this->vars = $controller->_vars;
		}*/
		
		$this->site = \GApp::instance()->site;
		$this->extension = \GApp::instance()->extension;
		$this->controller = \GApp::instance()->controller;
		$this->action = \GApp::instance()->action;
		
		//$this->views_site = $this->site;
		
		if(!empty($controller)){
			$this->_vars = &$controller->_vars;
			$this->theme = $controller->theme;
			$this->layouts = array_merge($this->layouts, $controller->layouts);
			$this->view = $controller->view;
			//$this->views_dir = isset($controller->views_dir) ? $controller->views_dir : '';
			//$this->views_site = isset($controller->views_site) ? $controller->views_site : $this->site;
			$this->data = &$controller->data;
			$this->errors = $controller->errors;
			$this->tvout = $controller->tvout;
			//set helpers properties
			if(!empty($controller->helpers)){
				if(!empty($controller->Composer->helpers)){
					$controller->helpers = array_merge($controller->helpers, $controller->Composer->helpers);
				}
				foreach($controller->helpers as $k => $v){
					$config = [];
					$alias = '';
					
					if(is_numeric($k)){
						$helper = $v;
					}else{
						$alias = $k;
						if(is_array($v)){
							//$helper = $k;
							$helper = $v['name'];
							//$config = $v;
						}else{
							$helper = $v;
						}
					}
					
					if(!empty($v['params'])){
						//$this->$k->params = array_merge($this->$k->params, $v['params']);
						//continue;
					}
					
					if(empty($alias)){
						$alias = Base::getClassName($helper);
					}
					
					if(empty($this->$alias) AND is_string($helper)){
						$this->$alias = new $helper($this, $config);
						if(!empty($v['params'])){
							$this->$alias->params = $v['params'];
						}
					}
				}
			}
		}
	}
	
	function _path($view = null, $type = 'views', $theme = null, $path = 'extension'){
		if(empty($view)){
			$view = $this->view;
		}
		
		if(empty($view)){
			$view = $this->action;
		}
		
		if(empty($theme)){
			$theme = $this->theme;
		}
		
		if(is_string($view) AND strpos($view, DS) !== false){
			return $view;
		}
		
		$path_parts = [];
		
		$path_site = !empty($view[$type]['site']) ? $view[$type]['site'] : \GApp::instance()->site;
		$path_ext = !empty($view[$type]['ext']) ? $view[$type]['ext'] : \GApp::instance()->extension;
		$path_cont = !empty($view[$type]['cont']) ? $view[$type]['cont'] : \GApp::instance()->controller;
		$path_act = !empty($view[$type]['act']) ? $view[$type]['act'] : \GApp::instance()->action;
		
		if(!empty($view[$type]['path'])){
			$path_parts = [$view[$type]['path']];
		}else{
			if($path == 'extension'){
				$path_parts[] = \G2\Globals::ext_path($path_ext, $path_site).'themes';
				$path_parts[] = $theme;
			}else{
				$path_parts[] = \G2\Globals::get('ADMIN_PATH').'themes';
				$path_parts[] = $theme;
			}
		}
		
		if(is_array($view)){
			$view = $path_act;
		}
		
		if($type == 'themes'){
			$file = implode(DS, $path_parts).DS;
		}
		
		if($type == 'layouts'){
			$path_parts[] = 'layouts';
			$path_parts[] = $view.'.php';
			
			$file = implode(DS, $path_parts);
		}
		
		if($type == 'views'){
			if(strpos($view, '.') !== false){
				$view_parts = explode('.', $view);
				if(!empty($view_parts[count($view_parts) - 1]) AND $view_parts[count($view_parts) - 1] == 'tmpl'){
					array_pop($view_parts);
					$view_parts[count($view_parts) - 1] = $view_parts[count($view_parts) - 1].'.tmpl';
				}
				$view_path = implode(DS, $view_parts).'.php';
				
				$path_parts[] = $view_path;
			}else{
				$path_parts[] = 'views';
				
				if(!empty($path_cont)){
					$path_parts[] = $path_cont;
				}
				
				$path_parts[] = $view.'.php';
			}
			
			$file = implode(DS, $path_parts);
		}
		
		if(!file_exists($file)){
			//pr($file);
			if($path == 'site'){
				if($theme == 'default'){
					return $file;
				}else{
					return $this->_path($view, $type, 'default', 'site');
				}
			}else{
				if($theme == 'default'){
					//return $this->_path($view, $type, $theme, 'site');
				}else{
					$file = $this->_path($view, $type, 'default', $path);
				}
				
				if(!file_exists($file)){
					return $this->_path($view, $type, $theme, 'site');
				}
			}
		}
		/*
		if(!file_exists($file) AND $path != 'site'){
			if($theme != 'default'){
				return $this->_path($view, $type, 'default');
			}else if($path != 'site'){
				return $this->_path($view, $type, 'default', 'site');
			}
		}
		*/
		
		return $file;
	}
	
	function renderView($action = ''){
		if($this->view === false){
			return false;
		}
		/*
		if(!empty($this->view)){
			$action = $this->view;
		}
		*/
		//$action_file = $this->get_file('views', $action);
		$action_file = $this->_path();
		//pr($action_file);
		//die();
		if(file_exists($action_file)){
			//view file exists, load it
			if(!empty($this->layouts) AND $this->tvout != 'view'){
				$output = '{VIEW}';
				
				$output = $this->renderLayouts($this->layouts, $output);
				
				return str_replace('{VIEW}', $this->_contents($action_file), $output);
			}
			
			return $this->_contents($action_file);
		}
	}
	
	function renderLayouts($layouts, $output){
		foreach($layouts as $layoutpath => $layout){
			
			//$layout_file = $this->get_file('layouts', $layout);
			$layout_file = $this->_path($layout, 'layouts');
			//pr($layout_file);
			
			$layout_content = $this->_contents($layout_file);
			$output = str_replace('{VIEW}', $layout_content, $output);
		}
		
		return $output;
	}
	
	function vars($arr){
		if(!empty($arr['__vars__'])){
			return $arr['__vars__'];
		}else{
			return [];
		}
	}
	
	private function _contents($__file__, $__vars__ = []){
		if(empty($__vars__)){
			foreach($this->_vars as $___k => $___val){
				$$___k = $___val;
			}
		}
		
		foreach($__vars__ as $___k => $___val){
			$$___k = $___val;
		}
		
		$contents = '';
		
		if(file_exists($__file__)){
			ob_start();
			include($__file__);
			$contents = ob_get_clean();
		}
		
		if(!empty($this->data)){
			$DataLoader = new \G2\H\DataLoader();
			$contents = $DataLoader->load($contents, Request::raw());
			unset($DataLoader);
			
			$ErrorLoader = new \G2\H\ErrorLoader();
			$contents = $ErrorLoader->load($contents, $this->errors);
			unset($ErrorLoader);
		}
		return $contents;
	}
	/*
	private function get_path($type = 'views', $section = 'extension'){
		if($section == 'extension'){
			$strings = array(\G2\Globals::ext_path(\GApp::instance()->getMirrored('ext', $this->extension), $this->views_site).'themes');
		}else if($section == 'site'){
			$strings = array(\G2\Globals::get('ADMIN_PATH').'themes');
		}
		//$strings[] = 'themes';
		$strings[] = $this->theme;
		
		if($type == 'views'){
			$strings[] = 'views';
			if(!empty($this->controller)){
				$strings[] = \GApp::instance()->getMirrored('cont', $this->controller);
			}
			
		}else if($type == 'layouts'){
			$strings[] = 'layouts';
			
		}else if($type == 'theme'){
			
		}
		
		return implode(DS, $strings).DS;
	}
	
	private function get_file($type, $name, $section = 'extension'){
		if(strpos($name, DS) !== false){
			$file = $name;
		}else{
			if(strpos($name, '.') !== false){
				$theme_path = $this->get_path('theme', $section);
				$chunks = explode('.', $name);
				$file = $theme_path.implode(DS, $chunks).'.php';
			}else{
				$type_path = $this->get_path($type, $section);
				$file = $type_path.$name.'.php';
			}
		}
		
		if(file_exists($file) === false){
			if($section != 'site'){
				return $this->get_file($type, $name, 'site');
			}else{
				return false;
			}
		}
		
		return $file;
	}
	*/
	public function view($path, $vars = [], $return = false){
		
		$view_file = $this->_path($path);
		
		$content = '';
		if($view_file !== false){
			$content = $this->_contents($view_file, $vars);
		}
		
		if($return){
			return $content;
		}
		
		echo $content;
	}
	
	function layout($layout){
		echo $output = $this->renderLayouts([$layout], '{VIEW}');
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