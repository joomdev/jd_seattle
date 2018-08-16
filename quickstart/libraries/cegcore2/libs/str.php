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
class Str {
	//make sure string ends with a slash and doesn't have 2 consecutive slashes
	public static function fixSeparator($string, $sep = DIRECTORY_SEPARATOR){
		return str_replace($sep.$sep, $sep, $string.$sep);
	}
	//convert a string to a camplized form: class_name => ClassName
	public static function camilize($string = ''){
		$class = preg_replace_callback('/(?:^|_)(.?)/', function($matches){return strtoupper($matches[1]);}, $string);
		return $class;
	}
	//convert camel case to original
	public static function uncamilize($class = ''){
		$string = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $class));
		return $string;
	}
	//clean string from any special characters
	public static function clean($str = '', $regex = 'A-Za-z0-9-_ '){
		$str = preg_replace('/[^'.$regex.']+/', '', $str);;
		return $str;
	}
	//replaces curly brackets strings into their values from the supplied data array
	public static function replacer($content = '', $data = array(), $other = array()){
		$other = (array)$other;
		$exploder = isset($other['exploder']) ? $other['exploder'] : '.';
		$replace_null = isset($other['replace_null']) ? $other['replace_null'] : false;
		$repeater = isset($other['repeater']) ? $other['repeater'] : false;
		$brackets = isset($other['brackets']) ? $other['brackets'] : array("{", "}");
		
		$op = $brackets[0];
		$cl = $brackets[1];
		
		if(!empty($repeater)){
			preg_match_all('/'.preg_quote($op).$repeater.'([^'.preg_quote($cl).']*)'.preg_quote($cl).'(.*?)'.preg_quote($op).'\/'.$repeater.preg_quote($cl).'/is', $content, $repeater_matches);
			if(!empty($repeater_matches[0])){
				foreach($repeater_matches[0] as $k => $match){
					$path = '';
					if(!empty($repeater_matches[1][$k])){
						$path = str_replace(':', '', $repeater_matches[1][$k]);
					}
					$sub_data = Arr::getVal($data, explode('.', $path), array());
					if(!Arr::is_assoc($sub_data)){
						$sub_content = '';
						foreach($sub_data as $i => $sub_data_array){
							$sub_content .= self::replacer($repeater_matches[2][$k], $sub_data_array);
						}
						$content = str_replace($repeater_matches[0][$k], $sub_content, $content);
					}
				}
			}
		}
		
		preg_match_all('/'.preg_quote($op).'([^('.$cl.'|'.$op.'| )]*?)'.preg_quote($cl).'/i', $content, $curly_matches);
		if(isset($curly_matches[1]) && !empty($curly_matches[1])){
			foreach($curly_matches[1] as $match){
				$value = Arr::getVal($data, explode($exploder, $match));
				if(is_string($value) AND !empty($other['nl2br'])){
					$value = nl2br($value);
				}
				if(!is_null($value)){
					if(is_array($value)){
						$content = str_replace($op.$match.$cl, var_export($value, true), $content);
					}else{
						if(!empty($other['escape'])){
							$content = str_replace($op.$match.$cl, htmlspecialchars($value), $content);
						}else{
							$content = str_replace($op.$match.$cl, $value, $content);
						}
					}
				}else{
					if($replace_null === true){
						$content = str_replace($op.$match.$cl, '', $content);
					}
				}
			}
		}
		return $content;
	}
	//generate a UUID V4, credits: http://www.php.net/manual/en/function.uniqid.php#94959
	public static function uuid() {
		return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			// 32 bits for "time_low"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

			// 16 bits for "time_mid"
			mt_rand( 0, 0xffff ),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand( 0, 0x0fff ) | 0x4000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand( 0, 0x3fff ) | 0x8000,

			// 48 bits for "node"
			mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
		);
	}
	//generate a 4 chunks 5 characters each serial number
	public static function serial($len = 5){
		$chunk1 = substr(md5(uniqid(rand(), true)), -$len, $len);
		$chunk2 = substr(md5(uniqid(rand(), true)), -$len, $len);
		$chunk3 = substr(md5(uniqid(rand(), true)), -$len, $len);
		$chunk4 = substr(md5(uniqid(rand(), true)), -$len, $len);
		$serial  = sprintf('%s-%s-%s-%s', $chunk1, $chunk2, $chunk3, $chunk4);
		return strtoupper($serial);
	}
	//generate random md5 string
	public static function rand(){
		return sha1((string)mt_rand().(string)mt_rand());
	}
	//generate slug of a string
	public static function slug($str, $limiter = '-', $unicode = false){
		$pattern = $unicode ? '/[^\pL\pN-]+/u' : '/[^A-Za-z0-9'.$limiter.']+/';
		$str = mb_convert_encoding((string)$str, 'UTF-8', mb_list_encodings());
		$str = str_replace(array("'", '"'), '', $str);
		$str = preg_replace($pattern, $limiter, $str);
		if(!empty($limiter)){
			$str = preg_replace('/['.$limiter.']+/', $limiter, $str);
		}
		$str = str_replace($limiter.$limiter, $limiter, $str);
		$str = trim($str, $limiter);
		return mb_strtolower($str, 'UTF-8');
	}
	//extract an attribute from attributes list
	public static function getAttr($str, $name, $default = null){
		$regex = '#'.$name.'=("|\')(.*?)(\1)#i';
		preg_match($regex, $str, $matches);
		if(!empty($matches[2])){
			return $matches[2];
		}else{
			return $default;
		}
	}
}