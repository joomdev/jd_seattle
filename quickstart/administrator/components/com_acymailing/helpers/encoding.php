<?php
/**
 * @package	AcyMailing for Joomla!
 * @version	5.10.3
 * @author	acyba.com
 * @copyright	(C) 2009-2018 ACYBA S.A.R.L. All rights reserved.
 * @license	GNU/GPLv3 http://www.gnu.org/licenses/gpl-3.0.html
 */

defined('_JEXEC') or die('Restricted access');
?><?php


class acyencodingHelper{

	function change($data, $input, $output){

		$input = strtoupper(trim($input));
		$output = strtoupper(trim($output));

		$supportedEncodings = array("BIG5", "ISO-8859-1", "ISO-8859-2", "ISO-8859-3", "ISO-8859-4", "ISO-8859-5", "ISO-8859-6", "ISO-8859-7", "ISO-8859-8", "ISO-8859-9", "ISO-8859-10", "ISO-8859-13", "ISO-8859-14", "ISO-8859-15", "ISO-2022-JP", "US-ASCII", "UTF-7", "UTF-8", "UTF-16", "WINDOWS-1251", "WINDOWS-1252", "ARMSCII-8", "ISO-8859-16");
		if(!in_array($input, $supportedEncodings)){
			acymailing_enqueueMessage('Encoding not supported: '.$input, 'error');
		}elseif(!in_array($output, $supportedEncodings)){
			acymailing_enqueueMessage('Encoding not supported: '.$output, 'error');
		}

		if($input == $output) return $data;

		if($input == 'UTF-8' && $output == 'ISO-8859-1'){
			$data = str_replace(array('€', '„', '“'), array('EUR', '"', '"'), $data);
		}

		if(function_exists('iconv')){
			set_error_handler('acymailing_error_handler_encoding');
			$encodedData = iconv($input, $output."//IGNORE", $data);
			restore_error_handler();
			if(!empty($encodedData) && !acymailing_error_handler_encoding('result')){
				return $encodedData;
			}
		}

		if(function_exists('mb_convert_encoding')){
			return mb_convert_encoding($data, $output, $input);
		}

		if($input == 'UTF-8' && $output == 'ISO-8859-1'){
			return utf8_decode($data);
		}

		if($input == 'ISO-8859-1' && $output == 'UTF-8'){
			return utf8_encode($data);
		}

		return $data;
	}

	function detectEncoding(&$content){

		if(!function_exists('mb_check_encoding')) return '';

		$toTest = array('UTF-8');
		
		$tag = acymailing_getLanguageTag();

		if($tag == 'el-GR'){
			$toTest[] = 'ISO-8859-7';
		}
		$toTest[] = 'ISO-8859-1';
		$toTest[] = 'ISO-8859-2';
		$toTest[] = 'Windows-1252';

		foreach($toTest as $oneEncoding){
			if(mb_check_encoding($content, $oneEncoding)) return $oneEncoding;
		}

		return '';
	}

}//endclass

function acymailing_error_handler_encoding($errno, $errstr = ''){
	static $error = false;
	if(is_string($errno) && $errno == 'result'){
		$currentError = $error;
		$error = false;
		return $currentError;
	}
	$error = true;
	return true;
}
