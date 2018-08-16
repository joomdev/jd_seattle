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
class Upload {
	
	public static function valid($file){
		return (isset($file['error']) AND isset($file['name']) AND isset($file['type']) AND isset($file['tmp_name']) AND isset($file['size']));
	}
	
	public static function not_empty($file){
		return (isset($file['error']) AND isset($file['tmp_name']) AND $file['error'] === UPLOAD_ERR_OK AND is_uploaded_file($file['tmp_name']));
	}
	
	public static function check_type($file, $allowed){
		if($file['error'] !== UPLOAD_ERR_OK){
			return false;
		}
		$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
		return in_array($ext, (array)$allowed);
	}
	
	public static function save($tmp_name, $filename, $chmod = 0644){
		if(move_uploaded_file($tmp_name, $filename)){
			if($chmod !== false){
				chmod($filename, $chmod);
			}
			return $filename;
		}
		return false;
	}
	
}