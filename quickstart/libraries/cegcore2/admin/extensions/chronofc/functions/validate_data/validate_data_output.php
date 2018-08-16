<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	
	$_result = true;
	$_errors = [];
	$_debug = [];
	
	$data = (array)$this->Parser->parse($function['data_provider'], true);
	
	if(!empty($function['fields'])){
		
		list($fields_data) = $this->Parser->multiline($function['fields']);
		
		$errors = [];
		foreach($fields_data as $field_data){
			if(!empty($field_data['name']) AND !empty($field_data['namep'])){
				
				$vfn = $field_data['namep'];
				if((bool)\G2\L\Validate::$vfn(\G2\L\Arr::getVal($data, $field_data['name'], null)) !== true){
					
					$error_message = !empty($field_data['value']) ? $field_data['value'] : $function['error_message'];
					$error_message = $this->Parser->parse($error_message, true);
					
					$errors[] = $error_message;
					$_result = false;
					
					if(!empty($function['list_errors'])){
						$this->errors = \G2\L\Arr::setVal($this->errors, $field_data['name'], $error_message);
					}
				}
				
			}
		}
		
	}
	
	$this->set($function['name'], $_result);
	
	if(empty($_result)){
		$this->Parser->fevents[$function['name']]['fail'] = true;
	}else{
		$this->Parser->fevents[$function['name']]['success'] = true;
	}