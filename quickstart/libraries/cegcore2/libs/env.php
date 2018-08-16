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
class Env {

	public static function redirect($url){
		header('Location: '.str_replace('&amp;', '&', $url));
		ob_end_flush();
		exit();
	}
	
	public static function e404(){
		header('HTTP/1.0 404 Not Found');
	}
	
	public static function send_async($url, $params = array()){
		$post_params = array();
		foreach($params as $key => &$val){
			if (is_array($val)) $val = implode(',', $val);
			$post_params[] = $key.'='.urlencode($val);
		}
		$post_string = implode('&', $post_params);
		
		$parts = parse_url($url);
		
		$fp = fsockopen($parts['host'],	isset($parts['port']) ? $parts['port'] : 80, $errno, $errstr, 30);
		
		$out = "POST ".$parts['path']." HTTP/1.1\r\n";
		$out.= "Host: ".$parts['host']."\r\n";
		$out.= "Content-Type: application/x-www-form-urlencoded\r\n";
		$out.= "Content-Length: ".strlen($post_string)."\r\n";
		$out.= "Connection: Close\r\n\r\n";
		if(isset($post_string)){
			$out.= $post_string;
		}

		fwrite($fp, $out);
		fclose($fp);
		/*
		$fields = '';
		foreach($params as $key => $value){
			$fields .= "$key=".urlencode($value)."&";
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_POSTFIELDS, rtrim($fields, '& '));
		$output = curl_exec($ch);
		curl_close($ch);
		*/
	}
}