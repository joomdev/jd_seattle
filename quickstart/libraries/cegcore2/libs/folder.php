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
class Folder {

	public static function create($path, $perms = 0755, $recursive = true){
		$path = rtrim($path, DS);
		if(!is_file($path) AND !is_dir($path)){
			return mkdir($path, $perms, $recursive);
		}
		return false;
	}
	
	public static function getFiles($path, $recursive = false, $match = '*'){
		$path = rtrim($path, DS).DS;
		if(!$recursive){
			return glob($path.$match);
		}else{
			$files = array();
			foreach(glob($path.$match) as $file){
				if(is_dir($file)){
					$files = array_merge($files, self::getFiles($file, $recursive));
				}else{
					$files[] = $file;
				}
			}
			return $files;
		}
	}
	
	public static function getFolders($path, $recursive = false){
		$path = rtrim($path, DS).DS;
		if(!$recursive){
			return array_filter(glob($path.'*'), 'is_dir');
		}else{
			//need to be coded
			return $files;
		}
	}
	
	public static function move($src, $dest, $return = true){
		$src = rtrim($src, DS);
		$dest = rtrim($dest, DS);
		if($return == false){
			return false;
		}
		//check if file/folder exists
		if(file_exists($dest)){
			if(is_dir($dest)){
				foreach(glob($src.DS.'*') as $file){
					$return = self::move($file, str_replace($src, $dest, $file), $return);
				}
			}else{
				//delete file
				unlink(rtrim($dest, DS));
				//move
				$return = rename(rtrim($src, DS), rtrim($dest, DS));
			}
		}else{
			//move
			$return = rename(rtrim($src, DS), rtrim($dest, DS));
		}
		return $return;
	}
	
	public static function delete($path){
		$path = rtrim($path, DS).DS;
		foreach(glob($path.'*') as $file){
			if(is_dir($file)){
				self::delete($file);
			}else{
				unlink($file);
			}
		}
		return rmdir($path);
	}
	
}