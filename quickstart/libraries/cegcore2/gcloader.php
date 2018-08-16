<?php
/**
* ChronoCMS version 1.0
* Copyright (c) 2012 ChronoCMS.com, All rights reserved.
* Author: (ChronoCMS.com Team)
* license: Please read LICENSE.txt
* Visit http://www.ChronoCMS.com for regular updates and information.
**/

//global namespace for the global helper function pr()
namespace {
	/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
	defined('GCORE_SITE') or die;
	//multi purpose function
	if(!function_exists('pr')){
		function pr($array = array(), $return = false){
			if(is_array($array)){
				array_walk_recursive($array, function(&$v){
					if(is_string($v)){
						$v = htmlspecialchars($v);
					}
				});
			}else if(is_string($array)){
				$array = htmlspecialchars($array);
			}
			if($return){
				return '<pre style="word-wrap:break-word; white-space:pre-wrap;">'.print_r($array, $return).'</pre>';
			}else{
				echo '<pre style="word-wrap:break-word; white-space:pre-wrap;">';
				print_r($array, $return);
				echo '</pre>';
			}
		}
	}
	
	function rl($text, $data = [], $id = false){
		return \G2\L\Lang::_($text, $data, $id);
	}
	
	function el($text, $data = [], $id = false){
		echo \G2\L\Lang::_($text, $data, $id);
	}
	
	function rp($name, $data){
		return \G2\L\Url::appendParam($name, $data);
	}
	
	function geta($array, $path, $default = null){
		return \G2\L\Arr::getVal($array, $path, $default);
	}
	
	function seta($array, $path, $value){
		return \G2\L\Arr::setVal($array, $path, $value);
	}
	
	//if(!function_exists('r2')){
	function r2($url, $xhtml = false, $absolute = false, $ssl = null){
		$router = \G2\Globals::getClass('route');
		return $router::_($url, $xhtml, $absolute, $ssl);
	}
	//}

	if(get_magic_quotes_gpc()){
		function stripslashes_gpc(&$value){
			$value = stripslashes($value);
		}
		array_walk_recursive($_GET, 'stripslashes_gpc');
		array_walk_recursive($_POST, 'stripslashes_gpc');
		array_walk_recursive($_COOKIE, 'stripslashes_gpc');
		array_walk_recursive($_REQUEST, 'stripslashes_gpc');
	}
}
//G2 namespace for the loader
namespace G2{
	if(!defined('DS')){
		define('DS', DIRECTORY_SEPARATOR);
	}

	class Globals {
		static $settings = array();

		public static function get($key, $default = null){
			if(isset(self::$settings[$key])){
				return self::$settings[$key];
			}else{
				return $default;
			}
		}

		public static function set($key, $value){
			self::$settings[$key] = $value;
		}
		
		public static function ready(){
			if(!class_exists('GApp', false)){
				class_alias(\G2\Globals::getClass('app'), 'GApp');
			}
		}
		
		public static function getClass($name){
			$parts = [];
			if(self::get('app')){
				$parts[] = \G2\L\Str::camilize(self::get('app'));
			}
			
			$parts[] = \G2\L\Str::camilize($name);
			
			return '\G2\L\\'.implode('\\', $parts);
		}

		public static function ext_path($ext, $area = 'admin'){
			return \GApp::extension($ext)->path($area);
		}
		/*public static function ext_path($ext, $area = 'admin'){
			$path = '';
			if($area == 'admin'){
				$path .= self::get('ADMIN_PATH');
			}else{
				$path .= self::get('FRONT_PATH');
			}
			$path .= 'extensions'.DS.$ext.DS;
			$path = self::fix_path($path);
			return $path;
		}*/
		public static function ext_url($ext, $area = 'admin'){
			return \GApp::extension($ext)->url($area);
		}
		/*public static function ext_url($ext, $area = 'admin'){
			$path = '';
			if($area == 'admin'){
				$path .= self::get('ADMIN_URL');
			}else{
				$path .= self::get('FRONT_URL');
			}
			$path .= 'extensions/'.$ext.'/';
			$path = self::fix_urls($path);
			return $path;
		}*/
		
		public static function url_to_path($url){
			return str_replace([\G2\Globals::get('FRONT_URL'), \G2\Globals::get('ROOT_URL')], [\G2\Globals::get('FRONT_PATH'), \G2\Globals::get('ROOT_PATH')], $url);
		}
		/*
		public static function fix_path($path){
			$extensions_paths = self::get('EXTENSIONS_PATHS', array());
			$extensions_names = self::get('EXTENSIONS_NAMES', array());
			if(!empty($extensions_paths) AND !empty($extensions_names)){
				foreach($extensions_paths as $int_path => $ext_path){
					foreach($extensions_names as $int_name => $ext_name){
						$path = str_replace($int_path.$int_name, $ext_path.$ext_name.DS.$int_name, $path);
					}
				}
			}
			return $path;
		}
		*/
		/*
		public static function fix_urls($output){
			$extensions_urls = self::get('EXTENSIONS_URLS', array());
			$extensions_names = self::get('EXTENSIONS_NAMES', array());
			if(!empty($extensions_urls) AND !empty($extensions_names)){
				foreach($extensions_urls as $int_url => $ext_url){
					foreach($extensions_names as $int_name => $ext_name){
						$output = str_replace($int_url.$int_name, $ext_url.$ext_name.'/'.$int_name, $output);
					}
				}
			}
			return $output;
		}
		*/
		
	}

	class Loader {
		static $classname = "";
		static $filepath = "";
		static $memory_usage = 0;
		static $start_time = 0;
		
		protected static function translate_path($segments){
			$classes_aliases = array('Libs' => 'L', 'Helpers' => 'H', 'Models' => 'M', 'Admin' => 'A', 'Extensions' => 'E', 'Controllers' => 'C', 'Traits' => 'T');//, 'Components' => 'Com', 'Plugins' => 'P');
			foreach($segments as $k => $dir){
				$class_match = array_search($dir, $classes_aliases);
				if($class_match !== false){
					$segments[$k] = $class_match;
				}
			}
			return $segments;
		}

		static public function register($name){
			if(empty(self::$start_time)){
				self::$start_time = microtime(true);
				self::$memory_usage = memory_get_usage();
			}
			if(strlen(trim($name)) > 0){
				$dirs = explode("\\", $name);
				$dirs = array_values(array_filter($dirs));
				//translate class names to path
				$dirs = self::translate_path($dirs);
				
				//if the class doesn't belong to the G2 then don't try to auto load it
				if($dirs[0] !== 'G2'){
					return false;
				}
				//build the include file path
				$strings = array();
				$extension_next = false;
				foreach($dirs as $k => $dir){
					if($dir === 'G2'){
						//root dir
						$strings[] = dirname(__FILE__);
						continue;
					}
					if($k == (count($dirs) - 1)){
						//last dir (file name)
						$strings[] = strtolower(preg_replace('/([a-z]|[0-9])([A-Z])/', '$1_$2', $dir)).".php";
						continue;
					}
					if(empty($dirs[$k])){
						//empty value
						continue;
					}
					//otherwise, uncamilize the namespace name to get the directory name
					$string = strtolower(preg_replace('/([a-z]|[0-9])([A-Z])/', '$1_$2', $dir));
					
					if($extension_next){
						$string = rtrim(\G2\Globals::ext_path($string, (!in_array('Admin', $dirs) ? 'front' : 'admin')), DS);
						$strings = [];
						$extension_next = false;
					}
					
					if($string == 'extensions'){
						$extension_next = true;
					}
					
					$strings[] = $string;
				}
				//load the file if exists
				$file = implode(DIRECTORY_SEPARATOR, $strings);
				//$file = \G2\Globals::fix_path($file);
				//pr($file);
				if(file_exists($file) AND substr($file, -4, 4) == ".php"){
					require_once($file);
					if(class_exists($name)){
						if($name == 'G2\L\App'){
							//class_alias($name, 'GApp');
						}
						return true;
					}else{
						self::$filepath = $file;
						self::$classname = $name;
					}
				}
				/*if(L\Base::getConfig('debug', 0)){
					self::debug();
				}*/
			}
		}

		static public function debug(){
			if(!empty(self::$classname))
			echo nl2br("\nClass name: \"".self::$classname."\" could NOT be found, additionally, the file below does NOT exist: \n".self::$filepath);
		}
	}
	spl_autoload_register(__NAMESPACE__ .'\Loader::register');
}