<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	
	if(!empty($function['data_provider'])){
		$data = $this->Parser->parse($function['data_provider'], true);
		
	}else{
		$data = [];
	}
	
	if(!empty($function['data_override'])){
		list($new_data) = $this->Parser->multiline($function['data_override']);
		
		if(is_array($new_data)){
			foreach($new_data as $new_data_line){
				$new_data_value = $this->Parser->parse($new_data_line['value'], true);
				$data[$new_data_line['name']] = $new_data_value;
			}
		}
	}
	
	//$this->Parser->debug[$function['name']]['data'] = $data;
	
	if(!empty($function['var_name'])){
		$this->set($function['var_name'], $data);
	}else{
		$this->set($function['name'], $data);
	}