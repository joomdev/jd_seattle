<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$action = $function['action'];
	
	if(empty($function['db_table'])){
		$this->Parser->debug[$function['name']]['_error'] = rl('Aborting, no database table selected.');
		$this->set($function['name'], false);
		return;
	}
	
	if(!empty($function['data_provider'])){
		$data = $this->Parser->parse($function['data_provider'], true);
	}else{
		$data = [];
	}
	
	$db_options = \G2\Globals::get('custom_db_options', []);
	if(!empty($function['db']['enabled'])){
		$db_options = $function['db'];
	}
	$dbo = \G2\L\Database::getInstance($db_options);
	
	$Model = new \G2\L\Model(['dbo' => $dbo, 'name' => $function['model_name'], 'table' => $function['db_table']]);
	$start_dbo_log = $Model->dbo->log;
	
	if($action == 'save' AND !empty($Model->pkey) AND !empty($data[$Model->pkey])){
		$action = 'update';
		$Model->where($Model->pkey, $data[$Model->pkey]);
	}
	
	$condition_found = false;
	
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
						$this->Parser->debug[$function['name']]['_error'] = rl('Data save aborted because %s value is missing', [$where_line['name']]);
						return false;
					}
					
					if($check_sign == '-' AND empty($filter_value)){
						continue;
					}
				}
				
				$condition_found = true;
				
				//if((!is_array($filter_value) AND strlen($filter_value)) > 0 OR (is_array($filter_value) AND count($filter_value))){
					$Model->where($where_line['name'], $filter_value, !empty($where_line['namep']) ? $where_line['namep'] : $sign);
				//}
			}
		}
		
		if(is_array($where)){
			$Model->whereGroup($where);
			
			$condition_found = true;
		}
	}
	
	if($action == 'save' AND $condition_found){
		$action = 'update';
	}
	
	$save_settings = [];
	if(!empty($function['fields']['special'])){
		
		list($special_data, $special) = $this->Parser->multiline($function['fields']['special']);
		
		if(is_array($special_data)){
			foreach($special_data as $special_line){
				if(!empty($special_line['name']) AND !empty($special_line['namep'])){
					if($special_line['namep'] == 'json'){
						$save_settings['json'][] = $special_line['name'];
					}else{
						if(!empty($special_line['value'])){
							$save_settings[$special_line['namep']][$special_line['name']] = $special_line['value'];
						}
					}
				}
			}
		}
	}
	
	if($action == 'save'){
		$action = 'insert';
	}
	
	if(strpos($action, 'insert:') !== false){
		if($action == 'insert:update'){
			$save_settings['duplicate_update'] = true;
		}
		if($action == 'insert:ignore'){
			$save_settings['ignore'] = true;
		}
		$action = 'insert';
	}
	
	if(!empty($function['autofields'])){
		$connection = $this->Parser->_connection();
		
		$stored = \GApp::session()->get($connection['alias'].'.save', []);
		
		if(!empty($stored)){
			foreach($stored as $field){
				if(isset($field['name'])){
					$fname = rtrim(str_replace(['[]', '[', ']', '(N)'], ['(N)', '.', '', '.[n]'], $field['name']), '.');
					$fname_tag = '{data'.(strpos($fname, '[n]') !== false ? '/jsonen' : '').':'.$fname.'/""}';
					$lname = explode('.', str_replace('.[n]', '', $fname));
					$function['insert_data_override'] = $function['insert_data_override']."\n".array_pop($lname).':'.$fname_tag;
				}
			}
		}
		
		//\GApp::session()->clear($connection['alias'].'.save');
	}
	
	if($action == 'insert'){
		if(!empty($function['insert_data_override'])){
			list($new_data) = $this->Parser->multiline($function['insert_data_override']);
			
			if(is_array($new_data)){
				foreach($new_data as $new_data_line){
					
					if(!empty($new_data_line['valuep'])){
						$check_sign = $new_data_line['valuep'];
						$field_value = $this->Parser->parse($new_data_line['value'], true);
						
						if($check_sign == '+' AND empty($field_value)){
							$this->set($function['name'], false);
							$this->Parser->fevents[$function['name']]['fail'] = true;
							$this->Parser->debug[$function['name']]['_error'] = rl('Data save aborted because %s value is missing', [$new_data_line['name']]);
							return false;
						}
						
						if($check_sign == '-' AND empty($field_value)){
							if(isset($userData[$new_data_line['name']])){
								unset($userData[$new_data_line['name']]);
							}
							
							$this->Parser->debug[$function['name']]['info'] = rl('The field %s value has been skipped', [$new_data_line['name']]);
							continue;
						}
					}
					
					$new_data_value = $this->Parser->parse($new_data_line['value'], true);
					$data[$new_data_line['name']] = $new_data_value;
				}
			}
		}
	}
	
	if($action == 'update'){
		if(!empty($function['update_data_override'])){
			list($new_data) = $this->Parser->multiline($function['update_data_override']);
			
			if(is_array($new_data)){
				foreach($new_data as $new_data_line){
					
					if(!empty($new_data_line['valuep'])){
						$check_sign = $new_data_line['valuep'];
						$field_value = $this->Parser->parse($new_data_line['value'], true);
						
						if($check_sign == '+' AND empty($field_value)){
							$this->set($function['name'], false);
							$this->Parser->fevents[$function['name']]['fail'] = true;
							$this->Parser->debug[$function['name']]['_error'] = rl('Data save aborted because %s value is missing', [$new_data_line['name']]);
							return false;
						}
						
						if($check_sign == '-' AND empty($field_value)){
							if(isset($userData[$new_data_line['name']])){
								unset($userData[$new_data_line['name']]);
							}
							
							$this->Parser->debug[$function['name']]['info'] = rl('The field %s value has been skipped', [$new_data_line['name']]);
							continue;
						}
					}
					
					$new_data_value = $this->Parser->parse($new_data_line['value'], true);
					$data[$new_data_line['name']] = $new_data_value;
				}
			}
		}
	}
	
	$this->Parser->debug[$function['name']]['data'] = $data;
	
	$result = $Model->$action($data, $save_settings);
	
	if($result !== false){
		
		if(!empty($Model->pkey)){
			$data[$Model->pkey] = $Model->id;
		}
		
		$this->set($function['name'], $Model->data);
		$this->Parser->fevents[$function['name']]['success'] = true;
		$this->Parser->debug[$function['name']]['_success'] = rl('Data saved successfully');
		
	}else{
		$this->Parser->debug[$function['name']]['_error'] = rl('Error saving the data.');
		$this->set($function['name'], false);
		$this->Parser->fevents[$function['name']]['fail'] = true;
	}
	$this->Parser->debug[$function['name']]['log'] = array_values(array_diff($Model->dbo->log, $start_dbo_log));