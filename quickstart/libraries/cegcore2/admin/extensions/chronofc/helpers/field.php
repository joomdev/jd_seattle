<?php
/**
* ChronoCMS version 1.0
* Copyright (c) 2012 ChronoCMS.com, All rights reserved.
* Author: (ChronoCMS.com Team)
* license: Please read LICENSE.txt
* Visit http://www.ChronoCMS.com for regular updates and information.
**/
namespace G2\A\E\Chronofc\H;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class Field extends \G2\L\Helper{
	//var $tooltip_loaded = false;
	
	function setup($type, $view, &$Parser, &$Html){
		$field_data = !empty($view['params']) ? $view['params'] : [];
		
		$this->parseBasics($view, $field_data, $Parser);
		
		if(isset($view['attrs'])){
			$this->setAttrs($view['attrs'], $Parser, $Html);
		}
		
		$state_class = '';
		if(!empty($view['states'])){
			if(!empty($view['states']['nonexistent'])){
				$Html->out = false;
				return false;
			}
			if(!empty($view['states']['hidden'])){
				$state_class .= ' hidden';
			}
			if(!empty($view['states']['disabled'])){
				$state_class .= ' disabled';
				$Html->attr('disabled', true);
			}
		}
		/*
		if(!empty($view['label'])){
			$Html->label($view['label']);
		}
		*/
		if(!empty($view['description']['text'])){
			$Html->desc($Parser->parse($view['description']['text'], true, true));
		}
		
		$connection = $Parser->_connection();
		/*if(!empty($connection['alias']) AND empty(\GApp::session()->get($connection['alias'].'.fields'))){
			\GApp::session()->clear($connection['alias']);
		}*/
		if(!empty($view['validation'])){
			$this->setValidations($view['validation'], $view, $field_data, $Parser, $Html);
		}
		\GApp::session()->set($connection['alias'].'.fields.'.$view['name'], true);
		/*
		if(!empty($view['validation'])){
			$this->setValidations($view['validation'], $view, $field_data, $Parser, $Html);
		}else{
			if(!empty($connection['alias']) AND !empty($field_data['name'])){
				\GApp::session()->set($connection['alias'].'.fields.'.$field_data['name'], true);
			}
		}
		*/
		if(!empty($view['tooltip']['text'])){
			$tooltip = '<i class="'.$view['tooltip']['class'].'" data-hint="'.htmlspecialchars($Parser->parse($view['tooltip']['text'], true, true)).'"></i>';
			$view['label'] = $view['label'].'&nbsp;'.$tooltip;
			/*
			if($this->tooltip_loaded === false){
				\GApp::document()->_('tooltipster');
				$this->tooltip_loaded = true;
			}
			*/
		}
		
		if(!empty($view['label'])){
			$Html->label($view['label']);
		}
		
		if(!empty($view['events'])){
			$this->setEvents($view['events'], $Parser, $Html);
		}
		
		if(!empty($view['options'])){
			$this->setOptions($view['options'], $Parser, $Html);
		}
		
		if(!empty($view['dynamics'])){
			$this->setDynamics($view, $Parser, $Html);
		}
		
		if(!empty($view['data-values'])){
			$this->setDataValues($view['data-values'], $Parser, $Html);
		}
		
		if(!empty($view['selected'])){
			$this->setSelected($view['selected'], $Parser, $Html);
		}
		
		if(!empty($view['ghost']['enabled'])){
			$Html->ghost($view['ghost']['value']);
		}
		
		if(!empty($view['content'])){
			$Html->content($view['content']);
		}
		
		if(!empty($view['checked'])){
			$Html->attr('checked', 'checked');
		}
		
		if(!empty($view['checked_provider']) AND !empty($Parser->parse($view['checked_provider'], true))){
			$Html->attr('checked', 'checked');
		}
		
		if(!empty($view['multiple'])){
			$Html->attr('multiple', 'multiple');
		}
		
		if(!empty($view['autocomplete']['event'])){
			$Html->addClass('search selection');
			$Html->attr('data-autocomplete', 1);
			$Html->attr('data-url', r2($Parser->_url().rp('event', $view['autocomplete']).rp('tvout', 'view')));
		}
		
		if(!empty($view['reload']['event'])){
			$Html->attr('data-reloadurl', r2($Parser->_url().rp('event', $view['reload']).rp('tvout', 'view')));
		}
		
		if(!empty($view['editor']['enabled'])){
			$Html->attr('data-editor', 1);
		}
		
		if(!empty($view['resize']['enabled'])){
			$Html->attr('data-autoresize', 1);
		}
		
		if(!empty($view['inputmask'])){
			$Html->attr('data-inputmask', "'".implode("': '", explode(':', $view['inputmask'], 2))."'");
		}
		
		if(!empty($view['color'])){
			$Html->addClass($view['color']);
		}
		
		if(!empty($view['class'])){
			$Html->addClass($view['class']);
		}
		
		if(!empty($view['fluid'])){
			$Html->addClass('fluid');
		}
		
		if(!empty($view['calendar'])){
			foreach($view['calendar'] as $k => $v){
				if($k == 'format'){
					//legacy format support
					$newformat = str_replace(['y', 'm', 'd', 'h', 'i'], ['YYYY', 'MM', 'DD', 'HH', 'mm'], $v);
					$Html->attr('data-dformat', $newformat);
					$Html->attr('data-sformat', $newformat);
				}else{
					$Html->attr('data-'.$k, $v);
				}
			}
		}
		
		$layout_class = '';
		if($type == 'checkboxes' OR $type == 'radios'){
			$layout_class = !empty($view['layout']) ? $view['layout'].' fields' : 'grouped fields';
		}
		
		//fix for old radios/checkboxes saved with container class = "field"
		if($type == 'checkboxes' OR $type == 'radios'){
			if($view['container']['class'] == 'field'){
				$view['container']['class'] = 'multifield';
			}else if(strpos($view['container']['class'], 'field ') === 0){
				$view['container']['class'] = str_replace('field ', 'multifield ', $view['container']['class']);
			}
		}
		
		$field_class = '';
		$field_width = !empty($view['container']['width']) ? ' '.$view['container']['width'] : '';
		if(!empty($view['container']['class'])){
			$field_class = $view['container']['class'].' '.$layout_class.$field_width;
		}else{
			if(!empty($layout_class)){
				$field_class = 'multifield '.$layout_class.$field_width;
			}else{
				
			}
		}
		
		$Html->attrs($field_data);
		
		return $field_class.$state_class;
	}
	
	function parseBasics(&$view, &$field_data, &$Parser){
		foreach(['label', 'name', 'id', 'placeholder', 'value', 'content', 'src', 'href'] as $attr){
			if(isset($field_data[$attr])){
				$field_data[$attr] = $Parser->parse($field_data[$attr], true, true);
			}
			if(isset($view[$attr])){
				$view[$attr] = $Parser->parse($view[$attr], true, true);
			}
		}
	}
	
	function setAttrs($attrs, &$Parser, &$Html){
		if(!empty($attrs)){
			$extra_attrs = explode("\n", $attrs);
			$extra_attrs = array_map('trim', $extra_attrs);
			
			foreach($extra_attrs as $k => $extra_attr){
				$attribute = $Parser->parse($extra_attr, true);
				$extra_attr_data = explode(':', $attribute, 2);
				
				if(!isset($extra_attr_data[1])){
					$Html->attr($extra_attr_data[0], $extra_attr_data[0]);
				}else{
					if($extra_attr_data[0] == 'class'){
						$Html->addClass($extra_attr_data[1]);
					}else{
						$Html->attr($extra_attr_data[0], $extra_attr_data[1]);
					}
				}
			}
		}
	}
	
	function setDynamics($view, &$Parser, &$Html){
		$dynamics = $view['dynamics'];
		$field_data = $view['params'];
		
		$connection = $Parser->_connection();
		
		if(strpos($field_data['name'], '.') !== false){
			$field_data['name'] = $Parser->parse($field_data['name'], true);
		}
		
		if(!empty($connection['alias'])){
			foreach($dynamics as $type => $dynamic){
				if(!empty($dynamic['enabled'])){
					\GApp::session()->set($connection['alias'].'.'.$type.'.'.$field_data['name'].'.name', $field_data['name']);
					if($view['type'] == 'field_file'){
						\GApp::session()->set($connection['alias'].'.'.$type.'.'.$field_data['name'].'.extensions', $view['extensions']);
					}
					\GApp::session()->set($connection['alias'].'.'.$type.'.'.$field_data['name'].'.label', isset($view['label']) ? $view['label'] : $field_data['name']);
					\GApp::session()->set($connection['alias'].'.'.$type.'.'.$field_data['name'].'.type', $view['type']);
					
					if(!empty($Html->options)){
						\GApp::session()->set($connection['alias'].'.'.$type.'.'.$field_data['name'].'.options', $Html->options);
					}
				}else{
					$stored = \GApp::session()->get($connection['alias'].'.'.$type);
					if(isset($stored[$field_data['name']])){
						unset($stored[$field_data['name']]);
						\GApp::session()->set($connection['alias'].'.'.$type, $stored);
					}
				}
			}
		}
	}
	
	function setValidations($validations, $view, $field_data, &$Parser, &$Html){
		$field_id = $field_data['id'];
		
		$field_vrules = [];
		$validate_tag = ['identifier' => $field_id.'-main'];
		
		$optional = true;
		if(!empty($validations['rules'])){
			$validation_rules = $validations['rules'];
			$vrules = explode("\n", $validation_rules);
			$vrules = array_map('trim', $vrules);
			
			foreach($vrules as $k => $vrule){
				$vrule = $Parser->parse($vrule, true);
				
				if($vrule == 'optional'){
					$validate_tag['optional'] = true;
					continue;
				}
				
				if(!empty($vrule)){
					$vrule_data = explode(':', $vrule, 2);
					$field_vrules[$k]['type'] = array_shift($vrule_data);
					
					if(strpos($field_vrules[$k]['type'], 'required') !== false OR stripos($field_vrules[$k]['type'], 'checked') !== false){
						$optional = false;
					}
					
					if(in_array($view['type'], ['field_checkbox', 'field_radios', 'field_secicon', 'field_checkboxes']) AND $field_vrules[$k]['type'] == 'required'){
						if($view['type'] == 'field_checkbox'){
							$field_vrules[$k]['type'] = 'checked';
						}else{
							$field_vrules[$k]['type'] = 'minChecked[1]';
						}
					}
					
					if(!empty($vrule_data)){
						$field_vrules[$k]['prompt'] = array_shift($vrule_data);
					}
				}
				
			}
		}
		unset($validations['rules']);
		//other rules
		if(!empty($validations)){
			if(!empty($validations['disabled'])){
				$validate_tag['disabled'] = 'true';
				unset($validations['disabled']);
			}
			
			if(empty($validations['required']) AND empty($validations['minChecked']) AND $optional){
				$validate_tag['optional'] = true;
				if(isset($validations['optional'])){
					if(empty($validations['optional'])){
						$validate_tag['optional'] = false;
					}
					unset($validations['optional']);
				}
			}
			
			$prompt = !empty($view['params']['placeholder']) ? $view['params']['placeholder'] : $view['label'];
			if(!empty($view['verror'])){
				$prompt = $view['verror'];
			}
			
			foreach($validations as $rule => $value){
				if(!empty($value)){
					if($value == 'true'){
						if(in_array($view['type'], ['field_checkbox', 'field_radios', 'field_secicon', 'field_checkboxes']) AND $rule == 'required'){
							if($view['type'] == 'field_checkbox'){
								$rule = 'checked';
							}else{
								$rule = 'minChecked[1]';
							}
						}
						$field_vrules[] = ['type' => $rule, 'prompt' => $prompt];
					}else{
						$field_vrules[] = ['type' => $rule.'['.$value.']', 'prompt' => $prompt];
					}
				}
			}
		}
		
		$connection = $Parser->_connection();
		$event = $Parser->_event();
		
		if(!empty($field_vrules)){
			$validate_tag['rules'] = array_values($field_vrules);
			
			$Html->attr('data-validationrules', json_encode($validate_tag));
			$Html->attr('data-validate', $field_id.'-main');
			
			//store validation info
			/*
			if(!empty($connection['alias'])){
				if(!empty($validate_tag['optional'])){
					\GApp::session()->set($connection['alias'].'.fields.'.$field_data['name'], array_values($field_vrules) + ['optional' => true]);
				}else{
					\GApp::session()->set($connection['alias'].'.fields.'.$field_data['name'], array_values($field_vrules));
				}
			}
			*/
		}else{
			/*
			if(!empty($connection['alias'])){
				\GApp::session()->set($connection['alias'].'.fields.'.$field_data['name'], true);
			}
			*/
		}
	}
	
	function setEvents($events, &$Parser, &$Html){
		$valid_events = [];
		foreach($events as $field_event){
			if(!empty($field_event['identifier'])){
				$ids = explode("\n", $field_event['identifier']);
				$field_event['identifier'] = [];
				foreach($ids as $id){
					$field_event['identifier'][] = $Parser->parse(trim($id), true, true);
				}
				//$field_event['identifier'] = $Parser->parse($field_event['identifier'], true, true);
				
				if(isset($field_event['value']) AND strlen(trim($field_event['value']))){
					$values = explode("\n", $field_event['value']);
					$field_event['value'] = [];
					foreach($values as $value){
						$field_event['value'][] = $Parser->parse(trim($value), true, true);
					}
				}
				
				$valid_events[] = $field_event;
			}
		}
		$Html->attr('data-events', json_encode($valid_events));
	}
	
	function setOptions($options_string, &$Parser, &$Html){
		$options = explode("\n", $options_string);
		$options = array_map('trim', $options);
		
		$field_options = [];
		foreach($options as $option){
			
			$option = $Parser->parse($option, true);
			if(is_array($option)){
				$field_options = array_replace($field_options, $option);
				continue;
			}
			
			$option_data = explode('=', $option, 2);
			
			if(count($option_data) == 1){
				$field_options[$option_data[0]] = $option_data[0];
			}else{
				$field_options[$option_data[0]] = $option_data[1];
			}
		}
		
		$Html->options($field_options);
	}
	
	function setDataValues($options_string, &$Parser, &$Html){
		$options = explode("\n", $options_string);
		$options = array_map('trim', $options);
		
		$field_options = [];
		foreach($options as $option){
			
			$option = $Parser->parse($option, true);
			if(is_array($option)){
				$field_options = array_replace($field_options, $option);
				continue;
			}
			
			$option_data = explode('=', $option, 2);
			
			if(count($option_data) == 1){
				$field_options[$option_data[0]] = $option_data[0];
			}else{
				$field_options[$option_data[0]] = $option_data[1];
			}
		}
		
		$Html->multiAttr('data-value', $field_options);
	}
	
	function setSelected($selected_string, &$Parser, &$Html){
		$selected = explode("\n", $selected_string);
		$selected = array_map('trim', $selected);
		
		foreach($selected as $k => $selected_v){
			$selected[$k] = $Parser->parse($selected[$k], true);
		}
		
		$selected = \G2\L\Arr::flatten($selected);
		
		$Html->selected($selected);
	}
	
}