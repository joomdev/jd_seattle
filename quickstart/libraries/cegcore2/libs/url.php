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
class Url {
	static $root = '';
	static $root_ext = array();
	
	public static function current(){
		$pageURL = self::domain();
		$pageURL .= self::path();
		return $pageURL;//strip_tags(htmlspecialchars($pageURL));
	}
	
	public static function path($full = true){
		$pageURL = '';
		if(isset($_SERVER['PHP_SELF']) AND isset($_SERVER['REQUEST_URI'])){
			//APACHE			
			$pageURL .= $_SERVER['REQUEST_URI'];
		}else{
			//IIS
			$pageURL .= $_SERVER['SCRIPT_NAME'];
			if(!empty($_SERVER['QUERY_STRING'])){
				$pageURL .= '?'.$_SERVER['QUERY_STRING'];
			}
		}
		
		if(empty($full) AND isset($_SERVER['SCRIPT_NAME']) AND strpos($pageURL, $_SERVER['SCRIPT_NAME']) === 0){
			return str_replace($_SERVER['SCRIPT_NAME'], '', $pageURL);
		}
		return $pageURL;//strip_tags(htmlspecialchars($pageURL));
	}
	
	public static function domain($protocol = true) {
		$dURL = '';
		
		if($protocol){
			//if((!empty($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] !== 'off') OR $_SERVER['SERVER_PORT'] == 443){
			if(
				(!empty($_SERVER['HTTPS']) AND $_SERVER['HTTPS'] !== 'off')
				OR $_SERVER['SERVER_PORT'] == 443
				OR (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) AND $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')
				OR (!empty($_SERVER['HTTP_X_FORWARDED_SSL']) AND $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on')
			){
				$dURL = 'https://';
			}else{
				$dURL = 'http://';
			}
		}

		if(!empty($_SERVER['HTTP_HOST'])){
			$dURL .= $_SERVER['HTTP_HOST'];
		}else{
			$dURL .= $_SERVER['SERVER_NAME'];
		}

		if($_SERVER['SERVER_PORT'] != '80' AND $_SERVER['SERVER_PORT'] != '443'){
			if(empty($_SERVER['HTTP_X_FORWARDED_PORT']) OR !in_array($_SERVER['HTTP_X_FORWARDED_PORT'], ['80', '443'])){
				if(strpos($dURL, ':'.$_SERVER['SERVER_PORT']) === false){
					$dURL .= ':'.$_SERVER['SERVER_PORT'];
				}
			}
		}
		return $dURL;
	}
	
	public static function referer(){
		return !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
	}
	
	public static function root(){
		return Config::get('site.url');
	}
	/*
	public static function root($absolute = false){
		if(!empty(self::$root) AND !$absolute){
			return self::$root;
		}
		if(isset($_SERVER['SCRIPT_NAME'])){
			$slices = explode(DS, str_replace(array('/', '\\'), DS, $_SERVER['SCRIPT_NAME']));
			$slices = array_unique($slices);
			$chunks = array(self::domain());
			foreach($slices as $slice){
				if(empty($slice) OR in_array($slice, array('index.php', 'admin'))){
					continue;
				}
				$chunks[] = $slice;
			}
			if($absolute === true){
				return implode('/', $chunks).'/';
			}
			if(!empty(self::$root_ext)){
				$chunks = array_merge($chunks, self::$root_ext);
				goto end1;
			}
			$file = str_replace(array('/', '\\'), DS, __FILE__);
			$fs = explode(DS, $file);
			$script_filename = str_replace(array('/', '\\'), DS, $_SERVER['SCRIPT_FILENAME']);
			$dirs = explode(DS, $script_filename);
			foreach($fs as $f){
				if(in_array($f, $dirs) || in_array($f, array('libs', 'url.php'))){
					continue;
				}
				$chunks[] = $f;
			}
			end1:
			return self::$root = implode('/', $chunks).'/';
		}
		$file = str_replace(array('/', '\\'), DS, __FILE__);
		$doc_root = str_replace(array('/', '\\'), DS, $_SERVER['DOCUMENT_ROOT']);
		$fs = explode(DS, $file);
		$dirs = explode(DS, $doc_root);
		$chunks = array(self::domain());
		foreach($fs as $f){
			if(in_array($f, $dirs) || in_array($f, array('libs', 'url.php'))){
				continue;
			}
			$chunks[] = $f;
		}
		if(substr($chunks[count($chunks) - 1], 0, -1) != '/'){
			$chunks[count($chunks) - 1] .= '/';
		}
		return self::$root = implode('/', $chunks);
	}
	*/
	public static function abs_to_url($path){
		return str_replace(array(\G2\Globals::get('FRONT_PATH'), DS), array(\G2\Globals::get('FRONT_URL'), '/'), $path);
	}
	
	public static function url_to_abs($url){
		return str_replace(array(\G2\Globals::get('FRONT_URL'), '/'), array(\G2\Globals::get('FRONT_PATH'), DS), $url);
	}
	
	public static function build($path, $params = array()){
		//$path = strip_tags(htmlspecialchars($path));
		$oparams = $params;
		//filtering moved to the no query url section because the query one needs to accept empty params to unset vars
		/*
		$params = array_filter($params,
			function($value){
				if(!is_array($value)){
					return (string)$value !== '';
				}else{
					return true;
				}
			}
		);
		*/
		if(empty($params)){
			return $path;
		}
		$url_params = array();
		if(strpos($path, '?') !== false){
			$path_pcs = explode('?', $path);
			$path_comps = parse_url($path);
			
			if(empty($path_comps['query'])){
				return $path;
			}
			
			$query = $path_comps['query'];
			parse_str($query, $fragments);
			$fragments = array_merge($fragments, $params);
			//remove empty params
			foreach($fragments as $fragmentk => $fragmentv){
				if(isset($oparams[$fragmentk])){
					if((is_string($fragments[$fragmentk]) AND strlen($oparams[$fragmentk]) == 0) OR is_null($fragments[$fragmentk])){
						unset($fragments[$fragmentk]);
					}
				}
			}
			$path = $path_pcs[0].'?'.http_build_query($fragments);
		}else{
			$params = array_filter($params,
				function($value){
					if(!is_array($value)){
						return (string)$value !== '';
					}else{
						return true;
					}
				}
			);
			$path = $path.'?'.http_build_query($params);
		}
		return rtrim($path, '?');
	}
	
	public static function full($url){
		$root_url = \G2\Globals::get('ROOT_URL');
		
		$parts = parse_url($url);//, PHP_URL_HOST);
		if(array_keys($parts)[0] == 'path'){
			return rtrim($root_url, '/').'/'.ltrim($url, '/');
		}
		
		return $url;
	}
	
	public static function noprotocol($url, $replacements = []){
		$comps = parse_url($url);
		$comps = array_replace($comps, $replacements);
		unset($comps['scheme']);
		if(isset($comps['port'])){
			$comps['port'] = ':'.$comps['port'];
		}
		return '//'.implode('', $comps);
	}
	
	public static function appendParam($name, $data){
		$out = '&'.$name.'=';
		if(!empty($data) OR is_int($data) OR is_string($data)){
			if(is_array($data)){
				if(isset($data[$name])){
					$out .= $data[$name];
				}else{
					return '';
				}
			}else{
				$out .= $data;
			}
			
			return $out;
		}else{
			return '';
		}
	}
}