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
	
	$read_data_associate_models = function(&$active_model, $all) use (&$read_data_associate_models, $dbo){
		foreach($all as $model_n => $model){
			if($model['related_to'] == $active_model->alias){
				$new_model = new \G2\L\Model(['dbo' => $dbo, 'name' => $model['model_name'], 'table' => $model['db_table']]);
				
				$read_data_associate_models($new_model, $all);
				
				$relation = $model['relation'];
				
				$relation_conditions = [];
				if(!empty($model['relation_conditions'])){
					
					list($on_data, $on) = $this->Parser->multiline($model['relation_conditions']);
					
					if(is_array($on)){
						$relation_conditions = $on;
					}else if(is_array($on_data)){
						//$relation_conditions = [];
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
				}
				
				if(empty($model['relation_conditions']) OR $relation == 'hasMany'){
					if(empty($model['foreign_key'])){
						continue;
					}
					
					if($relation == 'hasMany' AND !empty($relation_conditions)){
						foreach($relation_conditions as $rk => $relation_condition){
							$relation_conditions[$rk][3] = 'value';
						}
						$new_model->whereGroup($relation_conditions);
					}
					
					$relation_conditions = trim($model['foreign_key']);
				}
				
				//for multi results models
				if(!empty($model['fields']['list'])){
					if(!empty($model['fields']['extra'])){
						$default_fields = [$model['model_name'].'.*'];
						
						$new_model->fields($default_fields);
					}
					
					list($fields_data, $fields) = $this->Parser->multiline($model['fields']['list']);
					
					if(is_array($fields_data)){
						foreach($fields_data as $fields_line){
							
							if(!empty($fields_line['name'])){
								if(!empty($fields_line['value'])){
									$new_model->fields([$fields_line['name'] => $fields_line['value']]);
								}else{
									$new_model->fields([$fields_line['name']]);
								}
							}
						}
					}
					
					if(is_array($fields)){
						$new_model->fields($fields);
					}
				}
				
				if(!empty($model['order'])){
		
					list($order_data, $order) = $this->Parser->multiline($model['order']);
					
					if(is_array($order_data)){
						foreach($order_data as $order_line){
							$new_model->order([$order_line['name'] => !empty($order_line['namep']) ? $order_line['namep'] : 'asc']);
						}
					}
					
					if(is_array($order)){
						$new_model->order($order);
					}
				}
				
				if(!empty($model['group'])){
					list($group_data, $group) = $this->Parser->multiline($model['group']);
		
					if(is_array($group_data)){
						foreach($group_data as $group_line){
							$new_model->group([$group_line['name']]);
						}
					}
					
					if(is_array($group)){
						$new_model->group($group);
					}
				}
				
				if($relation == 'subqueryJoin'){
					$joinQ = $new_model->returnQuery('select');
					$active_model->join($joinQ, $model['model_name'], $relation_conditions);
				}else{
					$active_model->$relation($new_model, $model['model_name'], $relation_conditions);
				}
			}
		}
	};
	
	$Model = new \G2\L\Model(['dbo' => $dbo, 'name' => $function['model_name'], 'table' => $function['db_table']]);
	$start_dbo_log = $Model->dbo->log;
	//check other models
	if(!empty($function['models'])){
		$read_data_associate_models($Model, $function['models']);
	}
	
	$Composer = new \G2\L\Composer();
	
	//sorting
	if(!empty($function['sort']['fields'])){
		$sort_fields = explode("\n", $function['sort']['fields']);
		$sort_fields = array_map('trim', $sort_fields);
		
		$sorters = [];
		foreach($sort_fields as $sort_field){
			$sort_alias = trim(str_replace('.', '_', $sort_field));
			$sorters[$sort_alias] = trim($sort_field);
		}
		$Composer->sorter($sorters, $Model);
	}
	
	$where_print = [];
	if(!empty($function['where'])){
		
		list($where_data, $where) = $this->Parser->multiline($function['where']);
		
		if(is_array($where_data)){
			foreach($where_data as $where_line){
				
				if((!isset($where_line['value']) OR strlen($where_line['value']) == 0) AND empty($where_line['namep'])){
					$Model->where($where_line['name']);
					continue;
				}
				
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
						$this->Parser->debug[$function['name']]['_error'] = rl('Reading aborted because %s value is missing', [$where_line['name']]);
						return false;
					}
					
					if($check_sign == '-' AND empty($filter_value)){
						continue;
					}
				}
				
				//if((!is_array($filter_value) AND strlen($filter_value)) > 0 OR (is_array($filter_value) AND count($filter_value))){
					$Model->where($where_line['name'], $filter_value, !empty($where_line['namep']) ? $where_line['namep'] : $sign);
				//}
			}
			
			$where_print = $where_print + $where_data;
		}
		
		if(is_array($where)){
			$Model->whereGroup($where);
			
			$where_print = $where_print + $where;
		}
	}
	
	//get search data
	if(!empty($function['search']['fields'])){
		$search_fields = explode("\n", $function['search']['fields']);
		$search_fields = array_map('trim', $search_fields);
		
		if(!empty($function['search']['param_name'])){
			$search_param = trim($function['search']['param_name']);
			if($this->data($search_param)){
				foreach($search_fields as $search_field){
					$Model->where(trim($search_field), '%'.$this->data($search_param).'%', 'LIKE');
				}
			}
		}
	}
	
	if(!empty($function['paging'])){
		//pagination
		$paginate_alias = ['connectivity6', 'manager', $this->data('alias', ''), $function['name'], $function['model_name'], md5(json_encode($where_print))];
		$Composer->page(implode('-', $paginate_alias), $Model, $this->Parser->parse($function['limit'], true));
		$this->Paginator->params['alias'] = implode('-', $paginate_alias);
	}else{
		$Model->limit($this->Parser->parse($function['limit'], true));
		
		if(!empty($function['offset'])){
			$Model->offset($this->Parser->parse($function['offset'], true));
		}
	}
	
	//fields
	if(!empty($function['fields']['list'])){
		if(!empty($function['fields']['extra'])){
			$default_fields = [$function['model_name'].'.*'];
			
			if(!empty($function['models'])){
				foreach($function['models'] as $model){
					$default_fields[] = $model['model_name'].'.*';
				}
			}
			
			$Model->fields($default_fields);
		}
		
		list($fields_data, $fields) = $this->Parser->multiline($function['fields']['list']);
		
		if(is_array($fields_data)){
			foreach($fields_data as $fields_line){
				
				if(!empty($fields_line['name'])){
					if(!empty($fields_line['value'])){
						$Model->fields([$fields_line['name'] => $fields_line['value']]);
					}else{
						$Model->fields([$fields_line['name']]);
					}
				}
			}
		}
		
		if(is_array($fields)){
			$Model->fields($fields);
		}
	}
	
	if(!empty($function['order'])){
		
		list($order_data, $order) = $this->Parser->multiline($function['order']);
		
		if(is_array($order_data)){
			foreach($order_data as $order_line){
				$Model->order([$order_line['name'] => !empty($order_line['namep']) ? $order_line['namep'] : 'asc']);
			}
		}
		
		if(is_array($order)){
			$Model->order($order);
		}
	}
	
	if(!empty($function['group'])){
		
		list($group_data, $group) = $this->Parser->multiline($function['group']);
		
		if(is_array($group_data)){
			foreach($group_data as $group_line){
				$Model->group([$group_line['name']]);
			}
		}
		
		if(is_array($group)){
			$Model->group($group);
		}
	}
	
	$select_settings = [];
	if(!empty($function['fields']['special'])){
		
		list($special_data, $special) = $this->Parser->multiline($function['fields']['special']);
		
		if(is_array($special_data)){
			foreach($special_data as $special_line){
				if(!empty($special_line['name']) AND !empty($special_line['namep'])){
					if($special_line['namep'] == 'json'){
						$select_settings['json'][] = $special_line['name'];
					}
					if($special_line['namep'] == 'index'){
						$select_settings['index'][] = $special_line['name'];
					}
				}
			}
		}
	}
	
	$data = $Model->select($function['select_type'], $select_settings);
	
	$this->set($function['name'], $data);
	if(empty($data)){
		$this->Parser->fevents[$function['name']]['notfound'] = true;
	}else{
		$this->Parser->fevents[$function['name']]['found'] = true;
	}
	$this->Parser->debug[$function['name']]['log'] = array_values(array_diff($Model->dbo->log, $start_dbo_log));
	//pr($Model->dbo->log);