<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	if(!empty($function['parameters'])){
		$options = explode("\n", $function['parameters']);
		$options = array_map('trim', $options);
		
		foreach($options as $option){
			
			$option = $this->Parser->parse($option, true);
			
			$option_data = explode(':', $option, 2);
			
			$_REQUEST[$option_data[0]] = $option_data[1];
			$this->data($option_data[0], $option_data[1], true);
		}
	}