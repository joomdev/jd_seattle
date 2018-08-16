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
class Lang {
	//static $lang = null;
	//static $class = null;
	static $active = array();
	static $locales = array();
	static $reserved = array('NULL', 'YES', 'NO', 'TRUE', 'FALSE', 'ON', 'OFF', 'NONE');
	//static $loaded = array();

	public static function _($string = '', $data = [], $id = false){
		//get translatation
		$match = self::build($string);
		$string = isset(self::$active[$match]) ? self::$active[$match] : $string;
		
		return vsprintf($string, $data);
	}
	
	public static function find($string, $language = ''){
		if(empty($language)){
			$language = Config::get('site.language', 'en_GB');
		}
		//get translatation
		$translation = isset(self::$locales[$language][$string]) ? self::$locales[$language][$string] : false;
		return $translation;
	}
	
	public static function build($str, $regex = 'A-Za-z0-9-_'){
		$str = str_replace(['.', '!', '?', '%', '"', '\'', '/', '{', '}', '&', '=', '#', '@'], '', $str);
		$str = str_replace([',', ';', ':', ' ', '-'], '_', $str);
		$str = preg_replace('/\_\_+/', '_', $str);
		$str = trim($str, '_');
		$str = strtoupper($str);
		
		if(in_array($str, self::$reserved)){
			$str = $str.'_RES';
		}
		return $str;
	}
	
	public static function initialize(){
		if(Config::get('site.autolanguage', 0) == 1){
			$browser_language = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
			$chunks = explode(',', $browser_language);
			if(!empty($chunks) AND strlen($chunks[0]) == 5){
				$lang = $chunks[0];
				$detected = str_replace('-', '_', strtolower($lang));
				
				Config::set('site.language', $detected);
			}
		}
		
		self::load();
	}
	
	public static function read($path, $language = ''){
		if(empty($language)){
			$language = Config::get('site.language', 'en_GB');
		}
		
		self::$locales[$language] = [];
		
		if(file_exists($path.'locales'.DS.$language.'.ini')){
			$data = parse_ini_file($path.'locales'.DS.$language.'.ini', false, INI_SCANNER_RAW);
			self::$locales[$language] = array_merge(self::$locales[$language], $data);
		}
		if(file_exists($path.'locales'.DS.$language.'.custom.ini')){
			$data = parse_ini_file($path.'locales'.DS.$language.'.custom.ini', false, INI_SCANNER_RAW);
			self::$locales[$language] = array_merge(self::$locales[$language], $data);
		}
	}
	
	public static function load($extension = '', $language = ''){
		if(empty($language)){
			$language = Config::get('site.language', 'en_GB');
		}
		
		if(!empty($extension)){
			$path = \G2\Globals::ext_path($extension, 'front');
			self::read($path);
			$path = \G2\Globals::ext_path($extension, 'admin');
			self::read($path);
		}else{
			$path = \G2\Globals::get('FRONT_PATH');
			self::read($path);
		}
		
		self::$active = self::$locales[Config::get('site.language', 'en_GB')];
	}
}