<?php
namespace G2\L\T;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
trait Forms{
	public function Forms($module, $name){
		return FormsObject::getInstance($this, ['module' => $module, 'name' => $name]);
	}
	
}

class FormsObject extends \G2\L\Component{
	use \G2\L\T\Model;
	use \G2\L\T\Antispam;
	
	var $models = [
		'Form' => '\G2\A\M\Form',
		'Field' => '\G2\A\M\Field',
		'FormField' => '\G2\A\M\FormField',
		'FieldValidation' => '\G2\A\M\FieldValidation',
		'Validation' => '\G2\A\M\Validation',
		'Model' => '\G2\A\M\Model',
		'ModelRelation' => '\G2\A\M\ModelRelation',
	];
	
	var $conditions = [];
	var $edata = [];
	
	function select(){
		static $forms;
		static $fields;
		
		if(!isset($forms[$this->module][$this->name])){
			/*$this->Model('Form')->hasMany($this->Model('FormField'), 'FormField', 'form_id', ['belongsTo' => [[$this->Model('Field'), 'Field', 'field_id']]], true)
			->where('FormField.enabled', 1)
			->order(['FormField.ordering' => 'asc'])
			->settings(['json' => ['Field.params', 'FormField.params']]);*/
			
			$forms[$this->module][$this->name] = $this->Model('Form')
			->where('module', $this->module)
			->where('name', $this->name)
			->where('enabled', 1)
			->order(['core' => 'asc'])
			->order(['ordering' => 'desc'])
			->select('first', ['json' => ['params']]);
			
			if(!empty($forms[$this->module][$this->name])){
				/*$this->Model('FormField'), 'FormField', 'form_id', ['belongsTo' => [[$this->Model('Field'), 'Field', 'field_id']]], true)
				->where('FormField.enabled', 1)
				->order(['FormField.ordering' => 'asc'])
				->settings(['json' => ['Field.params', 'FormField.params']]);*/
				
				$this->Model('FieldValidation')
				->belongsTo($this->Model('Validation'), 'Validation', 'validation_id');
				
				$this->Model('Field')
				->hasMany($this->Model('FieldValidation'), 'FieldValidation', 'field_id', [], true)
				->settings(['json' => ['FieldValidation.params', 'Validation.params']]);
				
				$_fields = $this->Model('Field')
				->hasOne($this->Model('FormField'), 'FormField', 'field_id')
				//->hasMany($this->Model('FieldValidation'), 'FieldValidation', 'field_id')
				->where('FormField.form_id', $forms[$this->module][$this->name]['Form']['id'])
				->where('FormField.enabled', 1)
				->order(['FormField.ordering' => 'asc'])
				->select('all', ['json' => ['Field.params', 'FormField.params']]);
				
				if(empty($_fields)){
					$_fields = [];
				}
				
				$fields[$this->module][$this->name] = $_fields;
				
				foreach($fields[$this->module][$this->name] as $k => $field){
					$fields[$this->module][$this->name][$k]['Field']['form_name'] = $forms[$this->module][$this->name]['Form']['name'];
					$fields[$this->module][$this->name][$k]['Field']['module'] = $forms[$this->module][$this->name]['Form']['module'];
					
					if(!empty($fields[$this->module][$this->name][$k]['FormField']['params'])){
						$fields[$this->module][$this->name][$k]['Field']['params'] = $fields[$this->module][$this->name][$k]['FormField']['params'];
					}
					
					if(!empty($fields[$this->module][$this->name][$k]['Validation'])){
						foreach($fields[$this->module][$this->name][$k]['Validation'] as $vi => $rule){
							$rule['params'] = array_replace_recursive($rule['params'], $fields[$this->module][$this->name][$k]['FieldValidation'][$vi]['params']);
							$fields[$this->module][$this->name][$k]['Field']['validations']['rules'][$vi] = [
								'type' => $rule['type'],
								'prompt' => $rule['params']['prompt'],
							];
						}
						//$fields[$this->module][$this->name][$k]['Field']['validations'] = $fields[$this->module][$this->name][$k]['Validation'];
					}
				}
				//pr($fields[$this->module][$this->name]);die();
				$fields[$this->module][$this->name] = \G2\L\Arr::getVal($fields[$this->module][$this->name], ['[n]', 'Field'], []);
			}
		}
		/*
		if(empty($forms[$this->module][$this->name]['Field'])){
			$forms[$this->module][$this->name]['Field'] = [];
		}
		
		foreach($forms[$this->module][$this->name]['Field'] as $k => $field){
			$forms[$this->module][$this->name]['Field'][$k]['form_name'] = $forms[$this->module][$this->name]['Form']['name'];
			$forms[$this->module][$this->name]['Field'][$k]['module'] = $forms[$this->module][$this->name]['Form']['module'];
			
			if(!empty($forms[$this->module][$this->name]['FormField'][$k]['params'])){
				$forms[$this->module][$this->name]['Field'][$k]['params'] = $forms[$this->module][$this->name]['FormField'][$k]['params'];
			}
		}
		*/
		$this->set('forms.'.$this->module.'.'.$this->name.'.fields', $fields[$this->module][$this->name]);
		$this->set('form.fields', $fields[$this->module][$this->name]);
		//pr($fields[$this->module][$this->name]);
		
		return $fields[$this->module][$this->name];
	}
	
	function fdata(){
		$fields = $this->select();
		
		$list = [];
		foreach($fields as $field){
			if(!empty($field['model'])){
				$list[$field['model']][$field['name']] = $this->data($field['model'].'.'.$field['name']);
			}
		}
		
		if(!empty($this->edata)){
			$list = array_merge_recursive($this->edata, $list);
		}
		
		return $list;
	}
	
	function edata($edata){
		$this->edata = $edata;
	}
	
	function conditions($conditions){
		$this->conditions = $conditions;
	}
	
	function models($models){
		$this->Model('Model')
		->hasMany($this->Model('ModelRelation'), 'ModelRelation', 'pmodel', [], true)
		->settings(['json' => ['ModelRelation.params']]);
		
		$models = $this->Model('Model')->where('enabled', 1)
		->where('name', $models, 'in')
		->select('all', ['json' => ['params']]);
		
		return $models;
	}
	
	function save($pmodel){
		$this->select();
		
		$fdata = $this->fdata();
		/*
		if(!empty($edata)){
			$fdata = array_merge_recursive($edata, $fdata);
		}
		*/
		$models = array_keys($fdata);
		//pr($fdata);
		if(!empty($models)){
			$models = $this->models($models);
			
			if(!empty($this->conditions[$pmodel])){
				foreach($this->conditions[$pmodel] as $f => $v){
					$this->controller->Model($pmodel)->where($f, $v);
				}
				
				$result = $this->controller->Model($pmodel)->update($fdata[$pmodel]);
			}else{
				$result = $this->controller->Model($pmodel)->insert($fdata[$pmodel]);
				$fdata[$pmodel] = array_merge($fdata[$pmodel], $this->controller->Model($pmodel)->data);
			}
			
			if(empty($result)){
				return false;
			}
			
			$fdata[$pmodel] = array_merge($fdata[$pmodel], $this->conditions[$pmodel]);
			//pr($fdata);die();
			
			foreach($models as $model){
				$model_name = $model['Model']['name'];
				
				if(!isset($this->controller->models[$model_name])){
					$this->controller->models[$model_name] = $model['Model']['params'];
				}
				
				if(!empty($model['ModelRelation'])){
					foreach($model['ModelRelation'] as $rinfo){
						$rdata = [];
						foreach($rinfo['params']['keys'] as $fkey => $pkey){
							$rdata[$fkey] = $fdata[$rinfo['smodel']][$pkey];
						}
						
						$multi = !empty($rinfo['params']['multiple']);
						if($multi){
							foreach($fdata[$model_name] as $mk => $vfdata){
								$fdata[$model_name][$mk] = array_merge($fdata[$model_name][$mk], $rdata);
							}
						}else{
							$fdata[$model_name] = array_merge($fdata[$model_name], $rdata);
						}
						
						if(!empty($rdata)){
							foreach($rdata as $f => $v){
								$this->controller->Model($model_name)->where($f, $v);
							}
							
							$exists = $this->controller->Model($model_name)->select('first');
							
							if(!empty($exists)){
								foreach($rdata as $f => $v){
									$this->controller->Model($model_name)->where($f, $v);
								}
								
								if($multi){
									$this->controller->Model($model_name)->delete();
									foreach($fdata[$model_name] as $mk => $vfdata){
										$result = $this->controller->Model($model_name)->insert($fdata[$model_name][$mk]);
									}
								}else{
									$result = $this->controller->Model($model_name)->update($fdata[$model_name]);
								}
								
							}else{
								if($multi){
									foreach($fdata[$model_name] as $mk => $vfdata){
										$result = $this->controller->Model($model_name)->insert($fdata[$model_name][$mk]);
									}
								}else{
									$result = $this->controller->Model($model_name)->insert($fdata[$model_name]);
								}
							}
							
							if(empty($result)){
								return false;
							}
						}
					}
				}
			}
			
			return $fdata;
		}
	}
	
	function read($pmodel){
		$this->select();
		
		$fdata = $this->fdata();
		$models = array_keys($fdata);
		
		//if(!empty(array_filter($fdata[$pmodel])) AND !empty($models)){
		if(!empty($this->conditions) AND !empty($models)){
			$models = $this->models($models);
			/*
			$models_is = \G2\L\Arr::getVal($models, ['[n]', 'Model', 'name'], []);
			
			$index = array_search($pmodel, $models_is);
			if($index !== false){
				$pmodel_r = $models[$index];
				unset($models[$index]);
				array_unshift($models, $pmodel_r);
			}
			*/
			foreach($models as $model){
				$model_name = $model['Model']['name'];
				
				if(!isset($this->controller->models[$model_name])){
					$this->controller->models[$model_name] = $model['Model']['params'];
				}
				
				if(!empty($model['ModelRelation'])){
					foreach($model['ModelRelation'] as $rinfo){
						$relation = [];
						foreach($rinfo['params']['keys'] as $fkey => $pkey){
							$relation[] = [$pmodel.'.'.$pkey, $model_name.'.'.$fkey, '=', 'field'];
						}
						
						$this->controller->Model($pmodel)->hasOne($this->controller->Model($model_name), $model_name, $relation);
					}
				}
			}
			
			$fields_list = [];
			
			foreach($fdata as $mname => $mfields){
				$mfields = array_keys($mfields);
				foreach($mfields as $mfield){
					$fields_list[] = $mname.'.'.$mfield;
				}
			}
			/*
			if(!empty($pdata)){
				foreach($pdata as $f => $v){
					$this->controller->Model($pmodel)->where($f, $v);
				}
			}
			*/
			foreach($this->conditions as $model => $mdata){
				foreach($mdata as $f => $v){
					$this->controller->Model($model)->where($f, $v);
				}
			}
			/*
			foreach($fdata[$pmodel] as $f => $v){
				if(!empty($v)){
					$this->controller->Model($pmodel)->where($f, $v);
				}
			}
			*/
			$rdata = $this->controller->Model($pmodel)->fields($fields_list)->select('first');
			
			if(!empty($rdata)){
				$this->data = array_merge_recursive($rdata, $this->data);
			}else{
				return false;
			}
		}
		
	}
	
	function validate(){
		$fields = $this->select();
		
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
		
		$this->set('forms.'.$this->module.'.'.$this->name.'.errors', $errors);
		
		return $errors;
	}
}
?>