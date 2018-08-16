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
class Fields extends \G2\L\Helper{
	
	public function tag($name, $attrs = [], $content = ''){
		$output = [];
		$output[] = '<'.$name;
		
		if(!empty($attrs)){
			$attrs2 = [];
			foreach($attrs as $k => $v){
				$attrs2[] = $k.'='.'\''.$v.'\'';
			}
			
			$output[] = ' ';
			$output[] = implode(' ', $attrs2);
		}
		
		if(!empty($content)){
			$output[] = '>';
			if(is_string($content)){
				$output[] = $content;
			}
			$output[] = '</'.$name.'>';
		}else{
			$output[] = ' />';
		}
		
		return implode('', $output);
	}
	
	public function styles($rules){
		$output = [];
		
		foreach($rules as $rule => $value){
			$output[] = $rule.':'.$value.';';
		}
		
		return implode('', $output);
	}
	
	public function prompts($prompts){
		$output = [];
		
		foreach($prompts as $prompt){
			$output[] = '<div class="ui label red error-msg">'.$prompt.'</div>';
		}
		
		return implode('', $output);
	}
}