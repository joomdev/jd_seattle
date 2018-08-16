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
class Dater{
	public static function ago($timestamp){
		$diff = time() - $timestamp;
		if($diff < 60){
			//1 minute
			return sprintf(l_('DATE_X_TIME_AGO'), $diff, l_('SECONDS'));
		}else if($diff < 3600){
			//1 hour
			return sprintf(l_('DATE_X_TIME_AGO'), round($diff/60), l_('MINUTES'));
		}else if($diff < 86400){
			//1 day
			return sprintf(l_('DATE_X_TIME_AGO'), round($diff/3600), l_('HOURS'));
		}else if($diff < 604800){
			//1 week
			return sprintf(l_('DATE_X_TIME_AGO'), round($diff/86400), l_('DAYS'));
		}else if($diff < 2592000){
			//1 month
			return sprintf(l_('DATE_X_TIME_AGO'), round($diff/604800), l_('WEEKS'));
		}else if($diff < 31536000){
			//1 year
			return sprintf(l_('DATE_X_TIME_AGO'), round($diff/2592000), l_('MONTHS'));
		}else{
			//more than 1 year
			return sprintf(l_('DATE_X_TIME_AGO'), round($diff/31536000), l_('YEARS'));
		}
	}
	
	public static function format($timestamp, $format = 'H:i {F} d Y'){
		return self::translate(self::datetime($format, $timestamp));
	}
	
	public static function short($timestamp){
		if(self::datetime('Ymd', 'now', 'local') == self::datetime('Ymd', $timestamp, 'local')){
			//is today
			return self::translate(self::datetime('\{\T\o\d\a\y\} \{\a\t\} H:i', $timestamp, 'local'));
		}else if(self::datetime('Y', 'now', 'local') == self::datetime('Y', $timestamp, 'local')){
			//is this year
			return self::translate(self::datetime('{F} d \{\a\t\} H:i', $timestamp, 'local'));
		}else{
			return self::translate(self::datetime('{F} d Y', $timestamp, 'local'));
		}
	}
	
	public static function translate($date){
		return preg_replace_callback('|\{((\w\s?)+)\}|', function($matches){
			return rl($matches[1]);
		}, $date);
	}
	
	public static function _($format, $timestamp){
		$oldLocale = setlocale(LC_TIME, 0);
		$newLocale = setlocale(LC_TIME, Config::get('site.language', 'en_gb'));
		if($newLocale == false){
			return false;
		}
		$output = utf8_encode(strftime($format, $timestamp));
		setlocale(LC_TIME, $oldLocale);
		return $output;
	}
	
	public static function datetime($format = 'Y-m-d H:i:s', $time = 'now', $zone = 'utc'){
		if(is_numeric($time)){
			$time = '@'.$time;
		}
		if($zone == 'utc'){
			$zone = new \DateTimeZone('UTC');
		}else{
			$zone = \G2\L\Config::get('site.timezone', 'UTC');
			$zone = new \DateTimeZone($zone);
		}
		$date = new \DateTime($time, new \DateTimeZone('UTC'));
		$date->setTimeZone($zone);
		return $date->format($format);
	}
	
	public static function strtotime($time, $zone = 'utc'){
		if($zone == 'utc'){
			$zone = new \DateTimeZone('UTC');
		}else{
			$zone = \G2\L\Config::get('site.timezone', 'UTC');
			$zone = new \DateTimeZone($zone);
		}
		$date = new \DateTime($time, $zone);
		return $date->format('U');
	}
	
	/*
	public static function _($format, $timestamp = null){
		$param_D = l_('DATE_D');
		$param_l = l_('DATE_l');
		$param_F = l_('DATE_F');
		$param_M = l_('DATE_M');
		
		$return = '';
		if(is_null($timestamp)){
			$timestamp = mktime();
		}
		
		if(is_array($param_D) AND is_array($param_l) AND is_array($param_F) AND is_array($param_M)){
			for($i = 0, $len = strlen($format); $i < $len; $i++){
				switch($format[$i]) {
					case '\\' : // fix.slashes
						$i++;
						$return .= isset($format[$i]) ? $format[$i] : '';
						break;
					case 'D' :
						$return .= $param_D[date('N', $timestamp)];
						break;
					case 'l' :
						$return .= $param_l[date('N', $timestamp)];
						break;
					case 'F' :
						$return .= $param_F[date('n', $timestamp)];
						break;
					case 'M' :
						$return .= $param_M[date('n', $timestamp)];
						break;
					default :
						$return .= date($format[$i], $timestamp);
						break;
				}
			}
		}else{
			$return = date($format, $timestamp);
		}
		return $return;
	}
	*/
}