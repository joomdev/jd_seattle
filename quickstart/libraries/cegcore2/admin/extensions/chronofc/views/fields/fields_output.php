<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$connection = $this->get('__connection');
	
	$Html = new \G2\H\Html();
	
	$fields_get_field = function($field_data) use ($connection, $Html){
		//parse some data
		foreach(['label', 'name', 'id', 'placeholder', 'value', 'content', 'src', 'href'] as $attr){
			if(isset($field_data[$attr])){
				$field_data[$attr] = $this->Parser->parse($field_data[$attr], true, true);
			}
		}
		
		if(!empty($field_data['ghost']['enabled'])){
			$Html->ghost($field_data['ghost']['value']);
			unset($field_data['ghost']);
		}
		
		$field_type = $input_type = $field_data['field_type'];
		unset($field_data['field_type']);
		
		if(isset($field_data['label'])){
			$Html->label($field_data['label']);
			unset($field_data['label']);
		}
		
		if(isset($field_data['checked']) AND !in_array($field_data['checked'], ['checked', true])){
			unset($field_data['checked']);
		}
		
		if(!empty($field_data['content'])){
			$Html->content($field_data['content']);
			unset($field_data['content']);
		}
		
		if(!empty($field_data['color'])){
			$Html->addClass($field_data['color']);
			unset($field_data['color']);
		}
		
		if(isset($field_data['attrs'])){
			if(!empty($field_data['attrs'])){
				$extra_attrs = explode("\n", $field_data['attrs']);
				$extra_attrs = array_map('trim', $extra_attrs);
				
				foreach($extra_attrs as $k => $extra_attr){
					$attribute = $this->Parser->parse($extra_attr, true);
					$extra_attr_data = explode(':', $attribute, 2);
					
					if(empty($extra_attr_data[1])){
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
			
			unset($field_data['attrs']);
		}
		
		if(isset($field_data['calendar'])){
			foreach($field_data['calendar'] as $k => $v){
				$field_data['data-'.$k] = $v;
			}
			unset($field_data['calendar']);
		}
		
		if(!empty($field_data['validation']['rules'])){
			$vrules = explode("\n", $field_data['validation']['rules']);
			$vrules = array_map('trim', $vrules);
			$field_vrules = [];
			
			$validate_tag = ['identifier' => $field_data['id'].'-main'];
			
			foreach($vrules as $k => $vrule){
				$vrule = $this->Parser->parse($vrule, true);
				
				if($vrule == 'optional'){
					$validate_tag['optional'] = 'true';
					continue;
				}
				
				if(!empty($vrule)){
					$vrule_data = explode(':', $vrule, 2);
					$field_vrules[$k]['type'] = array_shift($vrule_data);
					
					if(!empty($vrule_data)){
						$field_vrules[$k]['prompt'] = array_shift($vrule_data);
					}
				}
				
			}
			
			$validate_tag['rules'] = array_values($field_vrules);
			
			$Html->attr('data-validationrules', json_encode($validate_tag));
			$Html->attr('data-validate', $field_data['id'].'-main');
			
			unset($field_data['validation']);
		}
		
		if(isset($field_data['events'])){
			//if(!empty($field_data['events'][0]['identifier'])){
				$valid_events = [];
				foreach($field_data['events'] as $field_event){
					if(!empty($field_event['identifier'])){
						$field_event['identifier'] = $this->Parser->parse($field_event['identifier'], true, true);
						$valid_events[] = $field_event;
					}
				}
				$Html->attr('data-events', json_encode($valid_events));
			//}
			unset($field_data['events']);
		}
		
		/*if(isset($field_data['onclick'])){
			unset($field_data['onclick']);
		}*/
		
		if(isset($field_data['field_destination'])){
			unset($field_data['field_destination']);
		}
		
		if(isset($field_data['multiple'])){
			if(empty($field_data['multiple'])){
				unset($field_data['multiple']);
			}
		}
		
		if(isset($field_data['autocomplete'])){
			if(!empty($field_data['autocomplete']['event'])){
				$Html->addClass('search selection');
				$Html->attr('data-autocomplete', 1);
				$Html->attr('data-url', r2($this->Parser->_url().rp('event', $field_data['autocomplete']).rp('tvout', 'view')));
			}
			unset($field_data['autocomplete']);
		}
		
		if($field_type == 'checkboxes' OR $field_type == 'radios' OR $field_type == 'select'){
			
			$options = explode("\n", $field_data['options']);
			$options = array_map('trim', $options);
			
			$field_options = [];
			foreach($options as $option){
				
				$option = $this->Parser->parse($option, true);
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
			if(isset($field_data['options'])){
				unset($field_data['options']);
			}
			
			$selected = [];
			if(!empty($field_data['selected'])){
				$selected = explode("\n", $field_data['selected']);
				$selected = array_map('trim', $selected);
				
				foreach($selected as $k => $selected_v){
					$selected[$k] = $this->Parser->parse($selected[$k], true);
				}
				
				$selected = \G2\L\Arr::flatten($selected);
			}
			if(isset($field_data['selected'])){
				unset($field_data['selected']);
			}
			
			$layout_class = !empty($field_data['layout']) ? $field_data['layout'].' fields' : 'grouped fields';
			if($field_type == 'select'){
				$layout_class = '';
			}
			if(isset($field_data['layout'])){
				unset($field_data['layout']);
			}
			
			$field_class = '';
			if(isset($field_data['container']['class'])){
				$field_class = $field_data['container']['class'].' field '.$layout_class;
				unset($field_data['container']);
			}
			
			$Html->attrs($field_data);
			
			if($field_type == 'checkboxes'){
				$input_type = 'checkbox';
				
				$main_field = $Html->options($field_options)->selected($selected)->input($input_type, 'checkbox')->fields([], $field_class);
			}else if($field_type == 'radios'){
				$input_type = 'radio';
				
				$main_field = $Html->options($field_options)->selected($selected)->input($input_type, 'radio')->fields([], $field_class);
			}else if($field_type == 'select'){
				
				$main_field = $Html->options($field_options)->selected($selected)->input($input_type)->field($field_class);
			}
		}else{
			$field_class = '';
			if(isset($field_data['container']['class'])){
				$field_class = $field_data['container']['class'].' field';
				unset($field_data['container']);
			}
			
			$Html->attrs($field_data);
			$main_field = $Html->input($input_type)->field($field_class);
		}
		
		return $main_field;
	};
	
	if(!empty($view['fields'])){
		
		echo '<div class="'.$view['container']['class'].'" data-id="'.\G2\L\Str::slug($view['name']).'">';
		
		foreach($view['fields'] as $field_number => $field_data){
			if($field_data['field_type'] == 'multiple'){
				$contents = [];
				foreach($view['fields'] as $sub_key => $sub_field_data){
					if($sub_field_data['field_destination'] == $field_number){
						$contents[] = $fields_get_field($sub_field_data);
					}
				}
				
				echo $Html->fields($contents, 'two fields');
			}else{
				if(empty($field_data['field_destination'])){
					echo $fields_get_field($field_data);
				}
			}
		}
		
		echo '</div>';
	}
	
	unset($Html);
?>