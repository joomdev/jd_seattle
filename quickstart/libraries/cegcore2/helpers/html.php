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
class Html extends \G2\L\Helper{
	var $attributes = [];
	var $options = [];
	var $selected = [];
	var $tag = '';
	var $content = false;
	var $value = false;
	var $label = false;
	var $desc = false;
	var $holder = false;
	var $ghost = false;
	var $out = true;
	
	public function reset(){
		$this->tag = '';
		$this->out = true;
		$this->content = false;
		$this->value = false;
		$this->label = false;
		$this->desc = false;
		$this->holder = false;
		$this->ghost = false;
		$this->attributes = [];
		$this->mattributes = [];
		$this->options = [];
		$this->selected = [];
	}
	
	public function content($content){
		$this->content = $content;
		return $this;
	}
	
	public function ghost($ghost){
		$this->ghost = $ghost;
		return $this;
	}
	
	public function options($params){
		$this->options = $params;
		return $this;
	}
	
	public function multiAttr($name, $params){
		foreach($params as $option_value => $attr_value){
			$this->mattributes[$option_value][$name] = $attr_value;
		}
		return $this;
	}
	
	public function selected($params){
		$this->selected = $params;
		return $this;
	}
	
	public function attr($name, $value){
		$this->attributes[$name] = $value;
		return $this;
	}
	
	public function attrs($params){
		$this->attributes = array_merge($this->attributes, $params);
		return $this;
	}
	
	public function _attr($name, $value){
		if(strpos($value, "'") !== false AND strpos($value, '"') !== false){
			return $name.'="'.htmlspecialchars($value).'"';
		}else if(strpos($value, '"') !== false){
			return $name."='".$value."'";
		}else{
			return $name.'="'.$value.'"';
		}
	}
	
	public function label($text){
		$this->label = $text;
		return $this;
	}
	
	public function desc($text){
		$this->desc = $text;
		return $this;
	}
	
	public function val($value){
		$this->value = $value;
		$this->attributes['value'] = $value;
		return $this;
	}
	
	public function name($name){
		$this->attributes['name'] = $name;
		return $this;
	}
	
	public function checked($checked = true){
		if($checked){
			$this->attributes['checked'] = 'checked';
		}else{
			if(isset($this->attributes['checked'])){
				unset($this->attributes['checked']);
			}
		}
		
		return $this;
	}
	
	public function addClass($class, $prepend = false){
		if(empty($this->attributes['class'])){
			$this->attributes['class'] = $class;
		}else{
			if($prepend){
				$this->attributes['class'] = $class.' '.$this->attributes['class'];
			}else{
				$this->attributes['class'] = $this->attributes['class'].' '.$class;
			}
		}
		
		return $this;
	}
	
	public function input($type = 'text', $type2 = ''){
		$this->tag = 'input';
		
		if($this->out === false){
			return $this;
		}
		
		if(empty($this->attributes['type'])){
			$this->attributes['type'] = $type;
		}
		
		if($type == 'checkbox'){
			$holder = [];
			$holder[] = 'ui checkbox';
			if($type2){
				$holder[] = $type2;
			}
			$this->holder = implode(' ', $holder);
		}
		
		if($type == 'radio'){
			$holder = [];
			$holder[] = 'ui checkbox';
			if($type2){
				$holder[] = $type2;
			}
			$this->holder = implode(' ', $holder);
		}
		
		if($type == 'textarea'){
			$this->tag = 'textarea';
			$this->content = !empty($this->attributes['value']) ? $this->attributes['value'] : '';
			
			if(isset($this->attributes['value'])){
				unset($this->attributes['value']);
			}
			
			if(empty($this->attributes['data-rows'])){
				$this->attributes['data-rows'] = $this->attributes['rows'];
			}
		}
		
		if($type == 'button'){
			$this->tag = 'button';
			$this->addClass('ui button', true);
		}
		
		if($type == 'button_link'){
			$this->tag = 'a';
			$this->addClass('ui button', true);
			unset($this->attributes['type']);
		}
		
		if(in_array($type, ['header', 'message', 'custom', 'image'])){
			$this->tag = $this->attributes['tag'];
			unset($this->attributes['type']);
			unset($this->attributes['tag']);
		}
		
		if($type == 'calendar'){
			$this->attributes['type'] = 'text';
			$this->attributes['data-calendar'] = '1';
		}
		
		if($type == 'select'){
			$this->tag = 'select';
			$this->addClass('ui dropdown');
			
			$options = [];
			if(!empty($this->options)){
				foreach($this->options as $value => $label){
					$option_params = ['value' => $value];
					
					if(!empty($this->selected) AND is_array($this->selected) AND in_array($value, $this->selected)){
						$option_params['selected'] = 'selected';
					}
					
					if(!empty($this->mattributes[$value])){
						$option_params = array_merge($option_params, $this->mattributes[$value]);
					}
					
					$options[] = $this->_build('option', $option_params, $label);
				}
			}
			$this->content = implode("\n", $options);
		}
		
		//return $this->build();
		return $this;
	}
	
	private function _build($tag, $params = array(), $content = false){
		$out = [];
		
		if($this->out === false){
			return '';
		}
		
		$out[] = '<'.$tag;
		foreach($params as $param => $val){
			if(!is_array($val)){
				$out[] = $this->_attr($param, $val);
			}
		}
		if($content === false){
			$out[] = '/>';
		}else{
			$out[] = '>'.$content.'</'.$tag.'>';
		}
		return implode(' ', $out);
	}
	
	
	
	public function build(){
		$return = $this->_build($this->tag, $this->attributes, $this->content);
		
		$this->reset();
		
		return $return;
	}
	
	public function tag($tag){
		$this->tag = $tag;
		
		return $this->build();
	}
	
	public function field($class = 'field', $reset = true, $ghost = true){
		$output = [];
		
		if($this->out === false){
			return '';
		}
		
		if(!empty($this->attributes['type']) AND ($this->attributes['type'] == 'checkbox' OR $this->attributes['type'] == 'radio')){
			if(empty($class)){
				$class = 'inline field';
			}
		}
		
		if($this->label !== false){
			$label_attrs = [];
			if(!empty($this->attributes['id'])){
				$label_attrs['for'] = $this->attributes['id'];
			}
			$output[] = $this->_build('label', $label_attrs, $this->label);
		}
		
		if($this->ghost !== false AND $ghost === true){
			$output[] = $this->_build('input', ['type' => 'hidden', 'name' => str_replace('[]', '', $this->attributes['name']), 'value' => $this->ghost, 'data-ghost' => 1]);
		}
		
		$output[] = $this->_build($this->tag, $this->attributes, $this->content);
		
		if($this->holder !== false){
			$label = array_shift($output);
			$output[] = $label;
		}
		
		if($this->desc !== false){
			$output[] = $this->_build('small', ['class' => 'field-desc'], $this->desc);
		}
		
		$output = implode("\n", $output);
		
		if($this->holder !== false){
			$output = $this->_build('div', ['class' => $this->holder], $output);
		}
		
		if(!empty($class)){
			$output = $this->_build('div', ['class' => $class], $output);
		}
		
		if($reset){
			$this->reset();
		}
		
		return $output;
	}
	
	public function fields($fields = [], $class = ''){
		$output = [];
		
		if($this->out === false){
			return '';
		}
		
		if(empty($fields) AND !empty($this->options)){
			$fields_label = $this->label;
			$fields_desc = $this->desc;
			$this->desc = false;
			
			$counter = 0;
			$field_id = !empty($this->attributes['id']) ? $this->attributes['id'] : '';
			
			foreach($this->options as $value => $label){
				$this->label($label);
				$this->val($value);
				
				if(!empty($this->mattributes[$value])){
					$this->attributes = array_merge($this->attributes, $this->mattributes[$value]);
				}
				
				if(!empty($this->selected) AND is_array($this->selected) AND in_array($value, $this->selected)){
					$this->checked();
				}else{
					$this->checked(false);
				}
				
				if(!empty($field_id)){
					$this->attributes['id'] = $field_id.$counter;
				}
				
				if(!empty($this->attributes['data-validationrules']) AND $counter > 0){
					unset($this->attributes['data-validationrules']);
				}
				
				if(!empty($this->attributes['data-validate']) AND $counter > 0){
					unset($this->attributes['data-validate']);
				}
				
				$fields[] = $this->field('field', false, false);
				
				$counter++;
			}
			
			$this->label($fields_label);
			$this->desc($fields_desc);
		}
		
		if($this->label !== false){
			$output[] = $this->_build('label', [], $this->label);
		}
		
		if($this->ghost !== false){
			$output[] = $this->_build('input', ['type' => 'hidden', 'name' => str_replace('[]', '', $this->attributes['name']), 'value' => $this->ghost, 'data-ghost' => 1]);
		}
		
		$output = array_merge($output, $fields);
		
		if($this->desc !== false){
			$output[] = $this->_build('small', ['class' => 'field-desc'], $this->desc);
		}
		
		$output = implode("\n", $output);
		
		$output = $this->_build('div', ['class' => !empty($class) ? $class : 'fields'], $output);
		
		$this->reset();
		
		return $output;
	}
}