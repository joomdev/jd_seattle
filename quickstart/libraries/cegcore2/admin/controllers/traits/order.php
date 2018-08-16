<?php
/**
* COMPONENT FILE HEADER
**/
namespace G2\A\C\T;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
trait Order {
	
	function Order($Model, $fields){
		$this->helpers[] = '\G2\H\Sorter';
		$return = [];
		
		$this->set('helpers.sorter.fields', $fields);
		
		foreach($fields as $alias => $name){
			if(is_numeric($alias)){
				$alias = str_replace('.', '_', $name);
			}
			if($this->data('orderfld') == $alias OR $this->data('orderfld') == $name){
				$direction = $this->data('orderdir', 'asc');
				
				if($direction == 'clear'){
					\GApp::session()->clear('helpers.sorter.'.$alias);
				}else{
					$return[$name] = $direction;
					\GApp::session()->set('helpers.sorter.'.$alias, array('fld' => $name, 'dir' => $return[$name]));
				}
			}
		}
		//if no order is set in url then try to find one in session
		$saved = \GApp::session()->get('helpers.sorter', array());
		if(count($saved)){
			foreach($fields as $alias => $name){
				if(is_numeric($alias)){
					$alias = str_replace('.', '_', $name);
				}
				if(isset($saved[$alias])){
					$return[$saved[$alias]['fld']] = $saved[$alias]['dir'];
				}
			}
		}
		$Model->order($return);
	}
	
}
?>