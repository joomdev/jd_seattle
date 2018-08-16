<?php
namespace G2\L\T;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
trait Models{
	public function Models(){
		$obj = ModelsObject::getInstance($this, true);
		return $obj;
	}
	
}

class ModelsObject extends \G2\L\Component{
	use \G2\L\T\Model;
	
	var $models = [
		'Model' => '\G2\A\M\Model',
		'ModelRelation' => '\G2\A\M\ModelRelation',
	];
	
	function select($module){
		$list = [];
		
		$models = $this->Model('Model')->where('enabled', 1)->select('all', ['json' => ['params']]);
		
		foreach($models as $k => $model){
			$list[$model['Model']['name']] = $model['Model']['params'];
		}
		
		$models = $this->Model('ModelRelation')->select('all', ['json' => ['params']]);
		foreach($models as $k => $model){
			$list[$model['ModelRelation']['pmodel']]['relations'] = $model['ModelRelation']['params'];
		}
		
		return $list;
	}
	
	function fields($module){
		$fields = $this->controller->Fields()->select($module);
		$list = [];
		
		foreach($fields as $field){
			if(!empty($field['model'])){
				$list[] = $field['model'].'.'.$field['name'];
			}
		}
		
		return $list;
	}
	
	function save($module, $data){
		$models = $this->select($module);
		$this->controller->models = array_merge($this->controller->models, $models);
		
		foreach($data as $model => $modelData){
			if(!empty($this->controller->models[$model]['relations']) AND !empty($data[$model])){
				foreach($this->controller->models[$model]['relations'] as $rmodel => $rinfo){
					foreach($rinfo['keys'] as $rkey => $fkey){
						$data[$model][$fkey] = $data[$rmodel][$rkey];
					}
				}
				
				$this->controller->Model($model)->insert($data[$model], ['duplicate_update' => true]);
			}
		}
	}
	
	function load($module){
		$models = $this->select($module);
		$this->controller->models = array_merge($this->controller->models, $models);
		
		foreach($this->controller->models as $model => $modelInfo){
			if(!empty($this->controller->models[$model]['relations'])){
				foreach($this->controller->models[$model]['relations'] as $rmodel => $rinfo){
					$relation = [];
					foreach($rinfo['keys'] as $rkey => $fkey){
						$relation[] = [$rmodel.'.'.$rkey, $model.'.'.$fkey, '=', 'field'];
					}
					
					$this->controller->Model($rmodel)->hasOne($this->controller->Model($model), $model, $relation);
				}
				
			}
		}
	}
	
}
?>