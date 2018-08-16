<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	
	$db_options = \G2\Globals::get('custom_db_options', []);
	if(!empty($function['db']['enabled'])){
		$db_options = $function['db'];
	}
	$dbo = \G2\L\Database::getInstance($db_options);
	
	if(empty($function['db_table'])){
		$this->Parser->debug[$function['name']]['_error'] = rl('Aborting, no database table selected.');
		$this->set($function['name'], false);
		return;
	}
	
	$delete_data_associate_models = function(&$active_model, $all) use (&$delete_data_associate_models, $dbo){
		foreach($all as $model_n => $model){
			if($model['related_to'] == $active_model->alias){
				$new_model = new \G2\L\Model(['dbo' => $dbo, 'name' => $model['model_name'], 'table' => $model['db_table']]);
				
				$delete_data_associate_models($new_model, $all);
				
				if(empty($model['foreign_key']) AND !empty($model['relation_conditions'])){
					
					list($on_data, $on) = $this->Parser->multiline($model['relation_conditions']);
					
					if(is_array($on)){
						$relation_conditions = $on;
					}else if(is_array($on_data)){
						$relation_conditions = [];
						foreach($on_data as $k => $on_line){
							
							if(!empty($on_line['name']) AND !empty($on_line['value'])){
								$relation_conditions[$k] = [
									$on_line['name'], 
									$on_line['value'], 
									!empty($on_line['namep']) ? $on_line['namep'] : '=', 
									!empty($on_line['valuep']) ? $on_line['valuep'] : 'field'
								];
							}
						}
					}
					
					if(empty($relation_conditions)){
						continue;
					}
				}else{
					if(empty($model['foreign_key'])){
						continue;
					}
					$relation_conditions = trim($model['foreign_key']);
				}
				
				$active_model->hasOne($new_model, $model['model_name'], $relation_conditions);
			}
		}
	};
	
	$Model = new \G2\L\Model(['dbo' => $dbo, 'name' => $function['model_name'], 'table' => $function['db_table']]);
	$start_dbo_log = $Model->dbo->log;
	//check other models
	if(!empty($function['models'])){
		$delete_data_associate_models($Model, $function['models']);
	}
	
	$conditions_found = false;
	if(!empty($function['where'])){
		
		list($where_data, $where) = $this->Parser->multiline($function['where']);
		
		if(is_array($where_data)){
			
			foreach($where_data as $where_line){
				$filter_value = $this->Parser->parse($where_line['value'], true);
				
				$sign = is_array($filter_value) ? 'in' : '=';
				if(!empty($where_line['namep'])){
					$sign = $where_line['namep'];
				}
				
				if(!empty($where_line['valuep'])){
					$check_sign = $where_line['valuep'];
					
					if($check_sign == '+' AND empty($filter_value)){
						$this->set($function['name'], false);
						$this->Parser->fevents[$function['name']]['fail'] = true;
						$this->Parser->debug[$function['name']]['_error'] = rl('Deletion aborted because %s value is missing', [$where_line['name']]);
						return false;
					}
					
					if($check_sign == '-' AND empty($filter_value)){
						continue;
					}
				}
				
				//if((!is_array($filter_value) AND strlen($filter_value)) > 0 OR (is_array($filter_value) AND count($filter_value))){
					$conditions_found = true;
					
					$Model->where($where_line['name'], $filter_value, !empty($where_line['namep']) ? $where_line['namep'] : $sign);
				//}
			}
		}
		
		if(is_array($where)){
			$Model->whereGroup($where);
			
			$conditions_found = true;
		}
	}
	
	if(!empty($function['delete_protection']) AND $conditions_found !== true){
		$this->set($function['name'], false);
		$this->Parser->fevents[$function['name']]['fail'] = true;
		$this->Parser->messages['error'][$function['name']] = rl('No delete conditions were provided.');
		$this->Parser->debug[$function['name']]['_error'] = rl('Deletion aborted because no delete conditions are available.');
		
		return false;
	}
	
	//add all models fields
	$default_fields = [$function['model_name'].'.*'];
	if(!empty($function['models'])){
		foreach($function['models'] as $model){
			$default_fields[] = $model['model_name'].'.*';
		}
	}
	$Model->fields($default_fields);
	
	$result = $Model->delete();
	$this->Parser->debug[$function['name']]['_success'] = rl('Deletion was successfull.');
	
	$this->set($function['name'], $result);
	if(empty($result)){
		$this->Parser->fevents[$function['name']]['fail'] = true;
	}else{
		$this->Parser->fevents[$function['name']]['success'] = true;
	}
	$this->Parser->debug[$function['name']]['log'] = array_values(array_diff($Model->dbo->log, $start_dbo_log));