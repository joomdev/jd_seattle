<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$data = $this->Parser->parse($function['data_provider'], true);
	
	$result = false;
	
	if(!empty($function['values'])){
		
		list($values_data) = $this->Parser->multiline($function['values'], true, false);
		
		foreach($values_data as $value_data){
			if(strlen($value_data['name']) AND strlen($value_data['value'])){
				
				$target_value = $this->Parser->value($value_data['name']);
				
				$test_result = null;
				
				if(!empty($function['array']) AND is_array($data)){
					$test_result = in_array($target_value, $data);
					if($test_result){
						$result[$value_data['value']] = $this->Parser->parse($value_data['value'], !empty($function['return']));
					}
				}else{
					$test_result = (($data == $target_value) OR ($target_value === '*'));
					
					if($test_result){
						$result = $this->Parser->parse($value_data['value'], !empty($function['return']));
						break;
					}
				}
				
			}
		}
	}
	
	$this->set($function['name'], $result);
	$this->Parser->debug[$function['name']]['finished'] = true;