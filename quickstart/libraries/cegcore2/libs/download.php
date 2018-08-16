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
class Download {
	
	public static function send($path, $view = 'D', $filename = '', $cache = false){
		@error_reporting(0);
		
		if(!File::exists($path)){
			header('HTTP/1.1 404 Not Found');
			exit;
		}
		
		$contenttype = 'application/octet-stream';
		if($view == 'I'){
			//get mime
			if(class_exists('\finfo')){
				$finfo = new \finfo(FILEINFO_MIME_TYPE);
				$contenttype = $finfo->file($path);
			}else if(function_exists('mime_content_type')){
				$contenttype = mime_content_type($path);
			}else{
				$contenttype = 'application/octet-stream';
			}
			$contenttype = empty($contenttype) ? 'application/octet-stream' : $contenttype;
		}
		$filename = !empty($filename) ? $filename : basename($path);
		
		if(isset($_SERVER['HTTP_RANGE'])){
			$range = $_SERVER['HTTP_RANGE'];
		}else if(function_exists('apache_request_headers') AND $apache = apache_request_headers()){
			$headers = array();
			foreach($apache as $header => $val){
				$headers[strtolower($header)] = $val;
				if(isset($headers['range'])){
					$range = $headers['range'];
				}else{
					$range = FALSE;
				}
			}
		}else{
			$range = FALSE;
		}

		// Get the data range requested (if any)
		$filesize = @filesize($path);
		if($range AND $filesize !== false){
			$partial = true;
			list($param,$range) = explode('=', $range);
			if(strtolower(trim($param)) != 'bytes'){
				header('HTTP/1.1 400 Invalid Request');
				exit;
			}
			$range = explode(',', $range);
			$range = explode('-', $range[0]);
			if(count($range) != 2){
				header('HTTP/1.1 400 Invalid Request');
				exit;
			}
			if($range[0] === ''){
				$end = $filesize - 1;
				$start = $end - intval($range[0]);
			}else if($range[1] === ''){
				$start = intval($range[0]);
				$end = $filesize - 1;
			}else{
				$start = intval($range[0]);
				$end = intval($range[1]);
				if($end >= $filesize || (!$start && (!$end || $end == ($filesize - 1)))){
					$partial = false;
				}
			}      
			$length = $end - $start + 1;
		}else{
			$partial = false;
		}
		if($view == 'D'){
			$agent = $_SERVER['HTTP_USER_AGENT'];			
			if(preg_match('%Opera(/| )([0-9].[0-9]{1,2})%', $agent)){
				header('Content-Type: application/octet-stream');
			}else if(preg_match('/MSIE ([0-9].[0-9]{1,2})/', $agent)){
				header('Content-Type: application/force-download');
				header('Content-Type: application/octet-stream');
				header('Content-Type: application/download');
			}else{
				header('Content-Type: '.$contenttype);
			}
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Pragma: private');
			header('Content-Disposition: attachment; filename="'.$filename.'"');
			header('Accept-Ranges: bytes');
		}else{
			header('Date: '.\G2\L\Dater::datetime('D, d M Y H:i:s').' GMT');
			if(!empty($cache)){
				header('Cache-Control: private, max-age=10800, pre-check=10800');
				header('Pragma: private');
				header('Expires: '. \G2\L\Dater::datetime('D, d M Y H:i:s', strtotime('2 day')));
			}else{
				header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
				header('Pragma: no-cache');
			}
			header('Content-Type: '.$contenttype);
		}
		if($filesize !== false){
			header('Content-Length: '.$filesize);
		}
		
		
		if($partial){
			header('HTTP/1.1 206 Partial Content'); 
			header("Content-Range: bytes $start-$end/$filesize"); 
			if(!$fp = fopen($path, 'r')){
				header('HTTP/1.1 500 Internal Server Error');
				exit;
			}
			if($start){
				fseek($fp,$start);
			}
			while($length){
				$read = ($length > 8192) ? 8192 : $length;
				$length -= $read;
				echo fread($fp,$read);
				@flush();
				@ob_flush();
			}
			fclose($fp);
		}else{
			readfile($path);
		}
		exit;
	}	
}