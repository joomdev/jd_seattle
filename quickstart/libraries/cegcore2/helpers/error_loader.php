<?php
/**
* ChronoCMS version 1.0
* Copyright (c) 2012 ChronoCMS.com, All rights reserved.
* Author: (ChronoCMS.com Team)
* license: Please read LICENSE.txt
* Visit http://www.ChronoCMS.com for regular updates and information.
**/
namespace G2\H;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class ErrorLoader extends \G2\L\Helper{
	var $names = [];
	var $errors = [];
	
	var $ghost_pattern = '/data-ghost=["-\']1["-\']/i';
	var $name_pattern = '/ name=("|\')(.*?)("|\')/i';
	var $error_pattern = '/ data-error=("|\')(.*?)(\1)/i';
	
	public function load($html, $errors = array(), $skipped = array()){
		if(!empty($html)){
			//get all fields names			
			preg_match_all('/name=("|\')([^(>|"|\')]*?)("|\')/i', $html, $names);
			
			$this->names = $names[2];
			
			if(!empty($errors)){
				$this->errors = explode('&', urldecode(http_build_query($errors)));
			}
			
			if(!empty($this->names)){
				$this->setErrors($html);
			}
		}
		
		return $html;
	}
	
	private function setErrors(&$html){
		$html = preg_replace([$this->error_pattern], '', $html);
		
		foreach($this->names as $name){
			
			$error_value = $this->getError($name);
			
			if(!empty($error_value)){
				$html = str_replace(['name="'.$name.'"', "name='".$name."'"], 'name="'.$name.'" data-error="'.$error_value.'"', $html);
			}
			
		}
	}
	
	private function getError($name){
		foreach($this->errors as $error){
			if(strpos($error, $name.'=') === 0){
				return str_replace($name.'=', '', $error);
			}
		}
		return '';
	}
}