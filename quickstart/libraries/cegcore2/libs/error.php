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
class Error {
	static $errors = array();
	// CATCHABLE ERRORS
	public static function capture_normal($number, $message, $file, $line){
		// Insert all in one table
		$error = array('type' => $number, 'message' => $message, 'file' => $file, 'line' => $line);
		self::$errors[] = $error;
	}

	public static function capture_exception($exception){
		self::$errors[] = $exception;
	}

	// UNCATCHABLE ERRORS
	public static function capture_shutdown(){
		$error = error_get_last();
		if($error){
			//ob_end_clean();
			self::$errors[] = $error;
		}else{
			return true;
		}
	}
	
	public static function initialize(){
		ini_set('display_errors', 1);
		error_reporting(-1);
		set_error_handler(array('\G2\L\Error', 'capture_normal'));
		set_exception_handler(array('\G2\L\Error', 'capture_exception'));
		register_shutdown_function(array('\G2\L\Error', 'capture_shutdown'));
	}
	
	public static function getErrors(){
		return self::$errors;
	}
}