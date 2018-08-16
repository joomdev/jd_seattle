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
class File {
	
	public static function create($path){
		if(!$handle = fopen($path, 'w+')){
			return false;
		}
		fclose($handle);
		return true;
	}
	
	public static function write($path, $content = ''){
		if(!$handle = fopen($path, 'w+')){
			return false;
		}
		if(fwrite($handle, $content) === false){
			return false;
		}
		fclose($handle);
		return true;
	}
	
	public static function makeSafe($filename){
		return preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $filename);
	}
	
	public static function delete($path){
		return unlink(rtrim($path, DS));
	}
	
	public static function move($src, $dest){
		if(file_exists($dest)){
			self::delete($dest);
		}
		$return = rename(rtrim($src, DS), rtrim($dest, DS));
		return $return;
	}
	
	public static function humanSize($bytes, $decimals = 2){
		$sz = 'BKMGTP';
		$factor = floor((strlen($bytes) - 1) / 3);
		return sprintf("%.{$decimals}f", $bytes/pow(1024, $factor)).@$sz[$factor];
	}
	
	public static function exists($path){
		if(strpos($path, 'http') === 0){
			//check if url exists
			return true;
		}else{
			if(file_exists($path)){
				return true;
			}
		}
		return false;
	}
	
}