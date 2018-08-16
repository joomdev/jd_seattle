<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$field_data = $this->data('Connection.views.'.$n.'.fields.'.$field_number, []);
	
	if(!empty($field_data)){
		unset($field_data['field_type']);
		
		if($field_type == 'hidden'){
			$field_data['label'] = $field_data['name'];
		}
		
		if(!isset($field_data['label'])){
			if(isset($field_data['name'])){
				$field_data['label'] = $field_data['name'];
			}else{
				$field_data['label'] = $field_type;
			}
		}
		
		if(!empty($field_data['type'])){
			unset($field_data['type']);
		}
		
		if(empty($field_data['events'])){
			$field_data['events'] = [1];
		}
	}else{
		$field_data['label'] = \G2\L\Str::camilize($field_type);
		
		if($field_type == 'button'){
			$field_data['label'] = '';
			$field_data['color'] = 'green';
			$field_data['content'] = \G2\L\Str::camilize($field_type);
		}
		
		if($field_type == 'button_link'){
			$field_data['label'] = '';
			$field_data['color'] = 'green';
			$field_data['content'] = \G2\L\Str::camilize($field_type);
		}
		
		if(in_array($field_type, ['header', 'message', 'custom'])){
			$field_data['tag'] = 'div';
		}
		
		if(in_array($field_type, ['image'])){
			$field_data['tag'] = 'img';
		}
		
		$field_data['events'] = [1];
	}
	
	if($field_type == 'custom'){
		$field_data['content'] = rl('Content will be displayed when the view is rendered.');
	}
	
	if(isset($field_data['content'])){
		if($field_type == 'header' OR $field_type == 'message'){
			$field_data['content'] = strip_tags($field_data['content']);
		}
	}
	
	if(!empty($field_data['ghost']['enabled'])){
		$this->Html->ghost($field_data['ghost']['value']);
	}
	
	if(!empty($field_data['content'])){
		$this->Html->content($field_data['content']);
	}
	
	if(!empty($field_data['color'])){
		$this->Html->addClass($field_data['color']);
	}
	
	if(empty($field_data['checked'])){
		unset($field_data['checked']);
	}
	
	if(isset($field_data['id'])){
		unset($field_data['id']);
	}
	
	if(isset($field_data['multiple'])){
		if(empty($field_data['multiple'])){
			unset($field_data['multiple']);
		}
	}
	
	if(!isset($field_data['label'])){
		$field_data['label'] = '';
	}
	$this->Html->label($field_data['label']);
	unset($field_data['label']);
	
	$this->Html->attrs($field_data);
	
	$input_type = $field_type;
	
	$field_class = 'field';
	if(isset($field_data['container']['class'])){
		$field_class = $field_data['container']['class'].' field';
	}
	
	if($field_type == 'checkboxes' OR $field_type == 'radios' OR $field_type == 'select'){
		if(empty($field_data['options'])){
			$field_data['options'] = "y=Yes\nn=No";
		}
		
		$options = explode("\n", $field_data['options']);
		$options = array_map('trim', $options);
		
		$field_options = [];
		foreach($options as $option){
			$option_data = explode('=', $option, 2);
			
			if(count($option_data) == 1){
				$field_options[$option_data[0]] = $option_data[0];
			}else{
				$field_options[$option_data[0]] = $option_data[1];
			}
		}
		
		$selected = [];
		if(!empty($field_data['selected'])){
			$selected = explode("\n", $field_data['selected']);
			$selected = array_map('trim', $selected);
		}
		
		if($field_type == 'checkboxes'){
			$input_type = 'checkbox';
			
			$main_field = $this->Html->options($field_options)->selected($selected)->input($input_type)->fields([], !empty($field_data['layout']) ? $field_data['layout'].' fields' : 'grouped fields');
		}else if($field_type == 'radios'){
			$input_type = 'radio';
			
			$main_field = $this->Html->options($field_options)->selected($selected)->input($input_type)->fields([], !empty($field_data['layout']) ? $field_data['layout'].' fields' : 'grouped fields');
		}else if($field_type == 'select'){
			
			$main_field = $this->Html->options($field_options)->selected($selected)->input($input_type)->field();
		}
	}else{
		$main_field = $this->Html->input($input_type)->field();
	}
	
	if($field_type == 'multiple'){
		$main_field = '<label class="ui label bottom attached small"></label>';
	}
?>
<?php ob_start(); ?>
<div class="ui segment hidden field-config" style="background-color:#f2f2f2;" data-field="form-<?php echo $n; ?>-<?php echo $field_number; ?>" data-count="<?php echo $n; ?>" data-type="<?php echo $field_type; ?>" data-number="<?php echo $field_number; ?>">
	
	<input type="hidden" value="<?php echo $field_type; ?>" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][field_type]">
	<input type="hidden" value="" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][field_destination]" class="field_destination_number">
	
	<?php $this->view(dirname(__FILE__).DS.'field_'.$field_type.'.php', ['field_type' => $field_type, 'field_number' => $field_number, 'field_data' => $field_data, 'n' => $n]); ?>
	
</div>
<?php
	$field_config = ob_get_clean();
	
	$holder_class = $field_class;
	
	if($field_type == 'multiple'){
		$holder_class = 'ui segment two fields fields-destination';
		//$main_field .= '<div class="fields fields-destination"></div>';
	}
	
	$contents = [$main_field, $field_config];
	
	if(!empty($all) AND $field_type == 'multiple'){
		foreach($all as $some_field_number => $some_field){
			if($some_field['field_destination'] == $field_number){
				$field_output = $this->view(dirname(__FILE__).DS.'field.php', ['field_number' => $some_field_number, 'field_type' => $some_field['field_type'], 'field_data' => $field_data, 'n' => $n], true);
				$contents[] = $field_output;
			}
		}
	}
	
	echo $this->Html->fields($contents, $holder_class);