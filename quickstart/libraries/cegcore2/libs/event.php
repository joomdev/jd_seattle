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
class Event {
	static $extensions = null;
	
	private static function extensions(){
		return ['chronoforms','chronoconnectivity', 'chronoforums'];//array_keys(\G2\Globals::get('EXTENSIONS_NAMES'));
	}
	
	public static function trigger(){
		$args = func_get_args();
		if(!empty($args)){
			$event = array_shift($args);
			$extensions = self::extensions();
			$prefix = '\G2';
			$site = '';
			if(GCORE_SITE == 'admin'){
				$site = '\A';
				$prefix = '\G2\A';
			}
			$return = array();
			foreach($extensions as $extension){
				if(is_callable(array($prefix.'\E\\'.Str::camilize($extension).'\\Events', $event))){
					Lang::load($site.'\E\\'.Str::camilize($extension));
					$return[$extension] = call_user_func_array(array($prefix.'\E\\'.Str::camilize($extension).'\\Events', $event), self::get_references($args));
				}else if(is_callable(array($prefix.'\E\\'.Str::camilize($extension).'\\Events', 'listener'))){
					Lang::load($site.'\E\\'.Str::camilize($extension));
					$return[$extension] = call_user_func_array(array($prefix.'\E\\'.Str::camilize($extension).'\\Events', 'listener'), self::get_references(array_merge([$event], $args)));
				}
			}
			
			return $return;
		}
	}
	
	protected static function get_references($vals){
		$refs = array();
		foreach($vals as $k => $val){
			$refs[$k] = &$vals[$k];
		}
		return $refs;
	}
}