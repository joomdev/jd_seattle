<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$attrs = [];
	
	$attrs['type'] = $field['type'];
	$attrs['name'] = !empty($field['params']['name']) ? $field['params']['name'] : $field['name'];
	$attrs['name'] = !empty($field['model']) ? $field['model'].'['.$attrs['name'].']' : $attrs['name'];
	
	
	$attrs['id'] = !empty($field['params']['id']) ? $field['params']['id'] : 'field'.$field['id'];
	
	$label = !empty($field['params']['label']) ? $field['params']['label'] : $field['title'];
	$label = '<label for="'.$attrs['id'].'">'.$label.'</label>';
	
	if(!empty($field['params']['placeholder'])){
		$attrs['placeholder'] = $field['params']['placeholder'];
	}
	
	$required = '';
	$prompts = [];
	if(!empty($field['validations'])){
		$vid = $field['name'];
		$attrs['data-validate'] = $vid;
		$attrs['data-vrules'] = json_encode([
			'identifier' => $vid, 
			'optional' => !empty($field['params']['voptional']), 
			'rules' => $field['validations']['rules']
		]);
		
		$rules = \G2\L\Arr::getVal($field['validations']['rules'], ['[n]', 'type'], []);
		if(array_intersect(['required'], $rules)){
			$required = ' required';
		}
		
		$prompts = \G2\L\Arr::getVal($field['validations']['rules'], ['[n]', 'prompt'], []);
	}
	/*
	if(!empty($field['params']['vrules'])){
		$vid = $field['name'];
		$attrs['data-validate'] = $vid;
		$attrs['data-vrules'] = json_encode([
			'identifier' => $vid, 
			'optional' => !empty($field['params']['voptional']), 
			'rules' => $field['params']['vrules']
		]);
		
		$rules = \G2\L\Arr::getVal($field['params']['vrules'], ['[n]', 'type'], []);
		if(array_intersect(['required'], $rules)){
			$required = ' required';
		}
		
		$prompts = \G2\L\Arr::getVal($field['params']['vrules'], ['[n]', 'prompt'], []);
	}
	*/
	$error = '';
	if(!empty($field['form_name']) AND !empty($this->get('forms.'.$field['module'].'.'.$field['form_name'].'.errors.'.$field['name']))){
		$error = ' error';
	}
?>
<?php $this->view('views.forms.'.$field['group'].'.'.$field['type'], [
	'field' => $field, 
	'fields' => $fields, 
	'label' => $label, 
	'attrs' => $attrs,
	'required' => $required,
	'error' => $error,
	'prompts' => $prompts,
]); ?>