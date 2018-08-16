<?php
namespace G2\L\T;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
trait Fields{
	public function Fields(){
		return FieldsObject::getInstance($this);
	}
	
}

class FieldsObject extends \G2\L\Component{
	use \G2\L\T\Model;
	use \G2\L\T\Antispam;
	
	var $models = [
		'Field' => '\G2\A\M\Field',
	];
	
	function select($module){
		static $fields;
		
		if(!isset($fields[$module])){
			$_fields = $this->Model('Field')
			->where('module', $module)
			->where('enabled', 1)
			->order(['ordering' => 'asc'])
			->select('all', ['json' => ['params']]);
			
			$_fields = \G2\L\Arr::getVal($_fields, ['[n]', 'Field'], []);
			
			$fields[$module] = $_fields;
		}
		
		$this->set('fields.'.$module, $fields[$module]);
		
		return $fields[$module];
	}
	
	function fdata($module){
		$fields = $this->select($module);
		$list = [];
		foreach($fields as $field){
			if(!empty($field['model'])){
				$list[$field['model']][$field['name']] = $this->data($field['model'].'.'.$field['name']);
			}
		}
		return $list;
	}
	
	function models($module){
		$list = array_unique(array_filter(\G2\L\Arr::getVal($this->select($module), ['[n]', 'model'], [])));
		
		return $list;
	}
	
	function validate($module){
		$fields = $this->select($module);
		
		$validator = new \G2\L\Validate();
		
		$errors = [];
		foreach($fields as $field){
			if(!empty($field['params']['vrules'])){
				foreach($field['params']['vrules'] as $rule){
					$vfn = $rule['type'];
					$condition = true;
					/*
					if(!method_exists($validator, $vfn)){
						continue;
					}
					*/
					if(!empty($rule['params'])){
						if(in_array($vfn, ['match', 'different'])){
							$ruledata = \G2\L\Arr::getVal($data, $ruledata);
						}
						$condition = (bool)$validator::$vfn(\G2\L\Arr::getVal($data, $fname), $ruledata);
					}else{
						if($vfn == 'library'){
							$lib = \G2\L\Str::camilize($field['group']);
							$type = $field['type'];
							$condition = $this->$lib()->$type($field['params']);
						}else{
							$fname = !empty($field['model']) ? $field['model'].'.'.$field['name'] : $field['name'];
							$condition = (bool)$validator::$vfn(\G2\L\Arr::getVal($this->data, $fname));
						}
					}
					
					if(empty($condition)){
						$errors[$field['name']] = $rule['prompt'];
						break;
					}
				}
			}
		}
		
		$this->set('errors.'.$module, $errors);
		
		return $errors;
	}
	
}
?>