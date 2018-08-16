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
class DataLoader extends \G2\L\Helper{
	var $names = [];
	var $data = [];
	
	var $ghost_pattern = '/data-ghost=["-\']1["-\']/i';
	var $name_pattern = '/ name=("|\')(.*?)("|\')/i';
	var $value_pattern = '/ value=("|\')(.*?)(\1)/i';
	var $checked_pattern = '/ checked(=("|\')checked("|\'))?/i';
	var $textarea_pattern = '/(<textarea(.*?)>)(.*?)(<\/textarea>)/is';
	//var $selected_pattern = '/selected=("|\')selected("|\')/i';
	//var $option_pattern = '/<option(.*?)<\/option>/is';
	
	public function load($html, $data = array(), $skipped = array()){
		if(!empty($html)){
			//get all fields names			
			preg_match_all('/name=("|\')([^(>|"|\')]*?)("|\')/i', $html, $names);
			
			$this->names = $names[2];
			
			if(!empty($data)){
				//$this->data = explode('_-&-_', urldecode(http_build_query($data, '', '_-&-_')));
				$this->data = $data;
			}
			
			if(!empty($this->names)){
				$this->text($html);
				$this->check($html);
				$this->textarea($html);
				$this->select($html);
			}
		}
		
		return $html;
	}
	
	private function text(&$html){
		$pattern = '/<input([^>]*?)type=("|\')(text|password|hidden|color|date|datetime|datetime-local|email|month|number|range|search|tel|time|url|week)(\2)([^>]*?)>/is';
		preg_match_all($pattern, $html, $matches);
		
		if(!empty($matches)){
			foreach($matches[0] as $field){
				if(strpos($field, 'data-ghost=') !== false){
					continue;
				}
				
				preg_match($this->name_pattern, $field, $name_attr);
				if(!empty($name_attr[2])){
					$field_name = $name_attr[2];
					$data_value = $this->getValue($field_name);
					
					if(is_array($data_value)){
						$data_value = array_shift($data_value);
					}
					
					if($data_value !== false){
						$field_cleaned = preg_replace([$this->name_pattern, $this->value_pattern], '', $field);
						
						$updated_field = str_replace('<input ', '<input name="'.$field_name.'" value="'.$data_value.'" ', $field_cleaned);
						
						$pos = strpos($html, $field);
						$html = substr_replace($html, $updated_field, $pos, strlen($field));
					}
				}
			}
		}
	}
	
	private function check(&$html){
		//checkboxes or radios fields
		$pattern = '/<input([^>]*?)type=("|\')(checkbox|radio)("|\')([^>]*?)>/is';
		preg_match_all($pattern, $html, $matches);
		
		if(!empty($matches)){
			foreach($matches[0] as $field){
				if(strpos($field, 'data-ghost=') !== false){
					continue;
				}
				
				preg_match($this->name_pattern, $field, $name_attr);
				preg_match($this->value_pattern, $field, $value_attr);
				
				if(!empty($name_attr[2])){
					$field_name = $name_attr[2];
					$field_value = isset($value_attr[2]) ? $value_attr[2] : null;
					
					$data_value = $this->getValue($field_name);
					
					if($data_value !== false){
						$updated_field = $field;
						//multi values
						if(is_array($data_value)){
							if(!is_null($field_value) AND in_array($field_value, $data_value)){
								$updated_field = preg_replace($this->name_pattern, ' name="${2}" checked="checked"', $field);
							}else{
								//remove any default value set in the html code
								$updated_field = preg_replace($this->checked_pattern, ' ', $field);
							}
						//single values
						}else{
							if(!is_null($field_value) AND $data_value == $field_value){
								$updated_field = preg_replace($this->name_pattern, ' name="${2}" checked="checked"', $field);
							}else{
								//remove any default value set in the html code
								$updated_field = preg_replace($this->checked_pattern, ' ', $field);
							}
							//single checkbox with no value attaribute, accepted value should be "on"
							if(is_null($field_value) AND $data_value == 'on'){
								$updated_field = preg_replace($this->name_pattern, ' name="${2}" checked="checked"', $field);
							}
						}
						
						$html = str_replace($field, $updated_field, $html);
					}
				}
			}
		}
	}
	
	private function textarea(&$html){
		//textarea fields
		$pattern = '/<textarea([^>]*?)>(.*?)<\/textarea>/is';
		preg_match_all($pattern, $html, $matches);
		
		if(!empty($matches)){
			foreach($matches[0] as $field){
				if(strpos($field, 'data-ghost=') !== false){
					continue;
				}
				
				preg_match($this->name_pattern, $field, $name_attr);
				if(!empty($name_attr[2])){
					$field_name = $name_attr[2];
					$data_value = $this->getValue($field_name);
					
					if($data_value !== false){
						$updated_field = preg_replace($this->textarea_pattern, '${1}'.str_replace(['\\', '$'], ['\\\\', '\$'], $data_value).'${4}', $field);
						$html = str_replace($field, $updated_field, $html);
					}
				}
			}
		}
	}
	
	private function select(&$html){
		//select boxes
		$pattern = '/<select([^>]*?)>(.*?)<\/select>/is';
		preg_match_all($pattern, $html, $matches);
		
		if(!empty($matches)){
			foreach($matches[0] as $field){
				if(strpos($field, 'data-ghost=') !== false){
					continue;
				}
				$updated_field = $field;
				
				preg_match($this->name_pattern, $field, $name_attr);
				if(!empty($name_attr[2])){
					$field_name = $name_attr[2];
					$data_value = $this->getValue($field_name);
					
					if($data_value !== false){
						preg_match_all('/<option(.*?)<\/option>/is', $field, $matched_options);
						foreach($matched_options[0] as $matched_option){
							preg_match($this->value_pattern, $matched_option, $matched_option_value);
							$updated_option = $matched_option;
							$option_value = isset($matched_option_value[2]) ? $matched_option_value[2] : null;
							
							if(!is_null($option_value)){
								if(in_array($option_value, (array)$data_value)){
									//this option is selected
									$updated_option = preg_replace('/<option/i', '<option selected="selected"', $matched_option);
								}else{
									//this option is not selected
									$updated_option = preg_replace('/selected=("|\')selected("|\')/i', '', $matched_option);
								}
							}
							$updated_field = str_replace($matched_option, $updated_option, $updated_field);
						}
						
						$pos = strpos($html, $field);
						$html = substr_replace($html, $updated_field, $pos, strlen($field));
					}
				}
			}
		}
	}
	
	private function getValue($name){
		$path = str_replace(']', '', $name);
		$parts = explode('[', $path);
		foreach($parts as $k => $v){
			if(strlen(trim($v)) == 0){
				$parts[$k] = '[n]';
			}
		}
		
		$return = \G2\L\Arr::getVal($this->data, $parts, false);
		
		if($return !== false AND is_string($return)){
			$return = htmlspecialchars($return, ENT_QUOTES);//escape any special (") or regex chars ($)
		}
		return $return;
		/*
		foreach($this->data as $field){
			if(strpos($field, $name.'=') === 0){
				$return = str_replace($name.'=', '', $field);
				$return = htmlspecialchars($return, ENT_QUOTES);//escape any special (") or regex chars ($)
				return $return;
			}
		}
		return false;
		*/
	}
}