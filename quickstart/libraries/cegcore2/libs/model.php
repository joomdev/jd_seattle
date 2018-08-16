<?php
/**
* ChronoCMS version 1.0
* Copyright (c) 2012 ChronoCMS.com, All rights reserved.
* Author: (ChronoCMS.com Team)
* license: Please read LICENSE.txt
* Visit http://www.ChronoCMS.com for regular updates and information.
**/
namespace G2\L;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class Model {
	var $dbo;
	var $tablename;
	var $name;
	var $alias;
	var $pkey;
	var $tablefields;
	var $fieldsMap = array();
	var $qparams = array();
	var $errors = array();
	var $data = array();
	var $insertID = null;
	var $id = null;
	var $Cache = null;
	var $cached = true;
	
	function __construct($settings = array()){
		if(empty($settings['dbo'])){
			$dbo = Database::getInstance();
		}else{
			$dbo = $settings['dbo'];
		}
		$this->dbo = &$dbo;
		
		$this->name = empty($settings['name']) ? Base::getClassName(get_class($this)) : $settings['name'];
		$this->alias = empty($settings['alias']) ? $this->name : $settings['alias'];
		$this->cached = isset($settings['cached']) ? $settings['cached'] : $this->cached;
		
		$table = !empty($settings['table']) ? $settings['table'] : '';
		$this->table($table);
		
		if(!empty($this->tablename)){
			$this->_setCache();
			$this->pkey();
			$this->tablefields();
		}
		
		return $this;
	}
	
	public function table($tablename = ''){
		if(!empty($tablename)){
			$this->tablename = $tablename;
		}
		//$this->tablename = $this->dbo->_prefixTable($this->tablename, isset($this->apptable));
		$this->tablename = $this->dbo->_prefixTable($this->tablename);
		return $this;
	}
	
	function _setCache(){
		if(Config::get('cache.enabled') >= 1 AND Config::get('cache.dbinfo.enabled') >= 1 AND $this->cached){
			$this->Cache = Cache::getInstance($this->dbo->db_name.'.'.'db_tables_info', array('expiration' => Config::get('cache.dbinfo.lifetime', 43200)), 'file');
			return;
		}
		$this->cached = false;
	}
	
	function pkey($fresh = false){
		$cached = false;
		if($this->cached AND !$fresh){
			$this->pkey = $this->Cache->get($this->tablename.'.pkey');
			if($this->pkey !== false){
				$cached = true;
			}
		}
		if(!$cached){
			$this->pkey = $this->dbo->getTablePrimary($this->tablename);
		}
		if(!$cached AND $this->cached){
			$this->Cache->set($this->tablename.'.pkey', $this->pkey);
		}
	}
	
	function tablefields($fresh = false){
		$cached = false;
		if($this->cached AND !$fresh){
			$this->tablefields = $this->Cache->get($this->tablename.'.columns');
			if(!empty($this->tablefields)){
				$cached = true;
			}
		}
		if(empty($this->tablefields) OR $fresh){
			$this->tablefields = $this->dbo->getTableColumns($this->tablename);
		}
		if(!$cached AND $this->cached){
			$this->Cache->set($this->tablename.'.columns', $this->tablefields);
		}
	}
	
	public function settings($settings){
		if(empty($this->qparams['settings'])){
			$this->qparams['settings'] = [];
		}
		$this->qparams['settings'] = array_merge($this->qparams['settings'], $settings);
	}
	
	private function _settings($settings){
		return empty($this->qparams['settings']) ? $settings : array_merge($this->qparams['settings'], $settings);
	}
	
	public function quote($string, $type = 'field', $addAlias = true){
		if($type == 'field'){
			if($string == '*'){
				return $string;
			}
			//check if this is a statement
			if(strpos($string, '(') === 0){
				return $this->_cleanString($string);
			}
			//check if a function name is in the string
			if(strpos($string, '(') !== false){
				preg_match('/[(](.*)[)]/', $string, $field_name);
				if(!empty($field_name[1])){
					$field_name = $field_name[1];
					$pieces = explode(' ', $field_name);
					if(count($pieces) > 1){
						$field_name = array_shift($pieces);
					}
					return str_replace($field_name, $this->quote($field_name, 'field'), $string);
				}
			}
			if(strpos($string, '.') !== false){
				//split the field name to alias + name, even if it has more than 1 alias.
				$strings = explode('.', $string, 2);
				$strings[0] = $this->dbo->quoteName($strings[0]);
				$strings[1] = ($strings[1] == '*') ? $strings[1] : $this->dbo->quoteName($strings[1]);
				return implode('.', $strings);
			}else{
				if($addAlias AND !empty($this->alias)){
					return $this->quote($this->_addAlias($string));
				}else{
					return $this->dbo->quoteName($string);
				}
			}
		}else if($type == 'alias'){
			if(!empty($this->alias) AND strpos($string, '.') === false){
				return $this->quote($this->_addAlias($string), 'alias');
			}
			//check if this is Model.field or SubQuery.Model.field
			$strings = explode('.', $string);
			if(count($strings) > 2){
				return $this->dbo->quoteName(array_shift($strings)).'.'.$this->dbo->quoteName(implode('.', $strings));
			}else{
				return $this->dbo->quoteName($string);
			}
		}else if($type == 'table'){
			return $this->dbo->quoteName($string);
		}else if($type == 'value'){
			return $this->dbo->quote($string);
		}
	}
	
	private function _cleanString($string){
		$string = str_ireplace(['delete', 'update', 'insert'], ['delet', 'updat', 'inser'], $string);
		return $string;
	}
	
	private function _mapping(){
		$return = [];
		
		if(!empty($this->fieldsMap)){
			foreach($this->fieldsMap as $field => $alternative){
				$return[$this->_addAlias($field)] = $this->_addAlias($alternative);
			}
		}
		
		return $return;
	}
	
	private function _map($field){
		if(!empty($this->qparams['mapping'][$field])){
			return $this->qparams['mapping'][$field];
		}
		
		return $field;
	}
	
	private function _unmap($field){
		$found = array_search($field, $this->qparams['mapping']);
		
		if($found !== false){
			return $found;
		}
		
		return $field;
	}
	
	private function _addAlias($field, $alias = ''){
		if(empty($alias)){
			$alias = $this->alias;
		}
		if(!empty($alias) AND strpos($field, '.') === false){
			return $alias.'.'.$field;
		}else{
			return $field;
		}
	}
	
	public function fields($fields = array()){
		$this->qparams['fields'] = !empty($this->qparams['fields']) ? array_merge($this->qparams['fields'], $fields) : $fields;
		return $this;
	}
	
	private function _fields($fields, $default_fields = [], $addAlias = true){
		$fs = array();
		foreach($fields as $k => $field){
			if(is_numeric($k)){
				//check for Alias.*
				if(strpos($field, '.*') !== false){
					$full = preg_match('/^([A-Za-z0-9_\-]+\.)\*/i', $field, $list);
					if(!empty($list[1]) AND !empty($default_fields)){
						foreach($default_fields as $di => $default_field){
							if(strpos($default_field, $list[1]) === 0){
								$fs[] = $this->quote($default_field, 'field').($addAlias ? ' AS '.$this->quote($this->_map($default_field), 'alias') : '');
							}
						}
						continue;
					}
				}
				//check that Alias.field and FUNC(Alias.field) actually exists in the table fields list
				if(strpos($field, '.') !== false){
					$field = $this->_unmap($field);
					
					$full = preg_match('/([A-Za-z0-9_\-]+)\.([A-Za-z0-9_\-]+)/i', $field, $list);
					if(!empty($list) AND !in_array($list[0], $default_fields)){
						continue;
					}
				}
				//no alias
				$fs[] = $this->quote($field, 'field').($addAlias ? ' AS '.$this->quote($this->_map($field), 'alias') : '');
			}else{
				if(strpos($k, '.') !== false){
					if(strpos($k, '(') !== false AND strpos($k, '(') !== 0){
						//check that [FUNC(Alias.field) => alias] actually exists in the table fields list
						$full = preg_match('/([A-Za-z0-9_\-]+)\.([A-Za-z0-9_\-]+)/i', $k, $list);
						if(!empty($list) AND !in_array($list[0], $default_fields)){
							continue;
						}
					}else{
						//if this is a statement or a subquery field (sub.model.field) then we will check the alias instead
						$k = $this->_cleanString($k);
						$alias_model = explode('.', $field)[0].'.';
						$found = false;
						foreach($default_fields as $default_field){
							if(strpos($default_field, $alias_model) === 0){
								$found = true;
								break;
							}
						}
						if(!$found){
							continue;
						}
					}
				}
				//alias
				if(strpos($k, '=') === 0){
					//this is a fixed value, e.g: 'some thing' as "field"
					$fs[] = $this->quote(str_replace('=', '', $k), 'value').($addAlias ? ' AS '.$this->quote($field, 'alias') : '');
				}else{
					$fs[] = $this->quote($k, 'field').($addAlias ? ' AS '.$this->quote($field, 'alias') : '');
				}
				/*if(strpos($k, '(') !== 0){
					//normal field
					$fs[] = $this->quote($k, 'field').($addAlias ? ' AS '.$this->quote($field, 'alias') : '');
				}else{
					//statement case
					$fs[] = $k.($addAlias ? ' AS '.$this->quote($field, 'alias') : '');
				}*/
			}
		}
		return $fs;
	}
	
	public function search($field, $keywords, $relevance_field = '', $max = true){
		$max_match = $match = $this->returnStatement('(MATCH({field1}) AGAINST ({value1} IN BOOLEAN MODE))', [$field], [$keywords]);
		//$max_match = $this->returnStatement('(MAX(MATCH({field1}) AGAINST ({value1} IN BOOLEAN MODE)))', [$field], [$keywords]);
		if($max){
			$max_match = '(MAX'.$max_match.')';
		}
		$this->qparams['search']['fields'][] = [$max_match => $relevance_field];
		$this->where($match, true);
		return $this;
	}
	
	public function having($field, $value = null, $param = '=', $valueType = 'value', $fieldType = 'alias'){
		$this->qparams['having'][] = $this->_conditions($field, $value, $param, $valueType, $fieldType);
		return $this;
	}
	
	public function where($field, $value = null, $param = '=', $valueType = 'value', $fieldType = 'field'){
		if(isset($this->qparams['lastWhereItem']) AND $this->qparams['lastWhereItem'] === false AND !in_array(strtoupper($field), array(')', 'AND', 'OR'))){
			$this->where('AND');
		}
		if(in_array(strtoupper($field), array('(', 'AND', 'OR'))){
			$this->qparams['lastWhereItem'] = true;
		}else{
			$this->qparams['lastWhereItem'] = false;
		}
		
		$this->qparams['where'][] = $this->_conditions($field, $value, $param, $valueType, $fieldType);
		return $this;
	}
	
	public function whereGroup($params = array()){
		foreach($params as $param){
			if(is_array($param)){
				$this->where($param[0], $param[1], (!empty($param[2]) ? $param[2] : '='), (!empty($param[3]) ? $param[3] : 'value'), (!empty($param[4]) ? $param[4] : 'field'));
			}else{
				$this->where($param);
			}
		}
		return $this;
	}
	
	private function _conditions($field, $value, $param, $valueType, $fieldType){
		if(in_array(strtoupper($field), array('(', ')', 'AND', 'OR'))){
			$return = strtoupper($field);
		}else{
			$return = array($field, $value, $param, $valueType, $fieldType);
		}
		return $return;
	}
	
	private function _where($conditions, $addAlias = true){
		$where = array();
		foreach($conditions as $k => $condition){
			if(is_array($condition)){
				$field = $condition[0];
				$sign = strtoupper($condition[2]);
				$value = $condition[1];
				$valueType = !empty($condition[3]) ? $condition[3] : 'value';
				$fieldType = !empty($condition[4]) ? $condition[4] : 'field';
				$valueArray = false;
				
				if(is_array($value) AND in_array($sign, ['IN', 'NOT IN'])){
					foreach($value as $value_k => $value_v){
						$value[$value_k] = $this->quote($value_v, 'value');
					}
					$value = '('.implode(', ', $value).')';
					$valueArray = true;
				}
				
				$where[] = $this->quote($field, $fieldType, $addAlias);
				
				if(is_null($value)){
					if($sign == 'IS'){
						$where[] = 'IS NULL';
						continue;
					}
					
					if($sign == 'IS NOT'){
						$where[] = 'IS NOT NULL';
						continue;
					}
				}
				
				if($value === true){
					continue;
				}
				
				$where[] = $sign;
				if(!empty($valueArray)){
					$where[] = $value;
				}else{
					if($valueType == 'value'){
						$where[] = $this->quote($value, 'value');
					}else if($valueType == 'alias'){
						$where[] = $this->quote($value, 'alias', $addAlias);
					}else{
						$where[] = $this->quote($value, 'field', $addAlias);
					}
				}
			}else{
				$where[] = $condition;
			}
		}
		return $where;
	}
	
	public function order($fields = array()){
		$this->qparams['order'] = !empty($this->qparams['order']) ? array_merge($this->qparams['order'], $fields) : $fields;
		return $this;
	}
	
	private function _order($fields){
		$orders = array();
		foreach($fields as $field => $dir){
			if(is_array($dir)){
				if(array_pop($dir) === true){
					$field = array_shift($dir);
					$dir = '= '.$this->quote(array_shift($dir), 'value').' '.array_shift($dir);
				}else{
					$dir = '';
				}
			}else{
				if(!empty($dir)){
					$dir = strtoupper($dir);
					if(!in_array($dir, ['ASC', 'DESC'])){
						$dir = '';
					}
				}
			}
			$orders[] = $this->quote($field, 'alias').' '.$dir;
			//$orders[] = $this->quote($field, 'field').' '.strtoupper($dir);
		}
		return $orders;
	}
	
	public function group($fields = array()){
		$this->qparams['group'] = !empty($this->qparams['group']) ? array_merge($this->qparams['group'], $fields) : $fields;
		return $this;
	}
	
	private function _group($fields){
		$groups = array();
		foreach($fields as $field){
			$groups[] = $this->quote($field, 'alias');
		}
		return $groups;
	}
	
	public function limit($value = array()){
		$this->qparams['limit'] = $value;
		return $this;
	}
	
	public function offset($value = array()){
		$this->qparams['offset'] = $value;
		return $this;
	}
	
	public function join($tablename, $alias, $on = array(), $type = 'left'){
		$this->qparams['join'][] = array($tablename, $alias, $on, $type);
		$this->qparams['joinCounter'][$alias] = 1;
		return $this;
	}
	
	private function _join($data){
		$joins = array();
		foreach($data as $k => $join){
			if(is_array($join)){
				$tablename = $join[0];
				$alias = $join[1];
				$on = $join[2];
				$type = $join[3];
				
				$joins[$k][] = $alias;
				$joins[$k][] = strtoupper($type).' JOIN';
				if(stripos($tablename, 'SELECT ') === 0){
					//sub query
					$joins[$k][] = '('.$this->_prepareSubQuery($tablename).') AS '.$this->quote($alias, 'table');
				}else{
					$joins[$k][] = $this->quote($tablename, 'table').' AS '.$this->quote($alias, 'table');
				}
				$joins[$k][] = 'ON';
				
				$joins[$k][] = implode(' ', $this->_where($on));
			}
		}
		return $joins;
	}
	
	public function from($statement){
		$this->qparams['from'] = $statement;
		return $this;
	}
	
	private function _prepareSubQuery($statement){
		return rtrim($statement, ';');
	}
	
	public function returnStatement($statement, $fields = [], $values = []){
		foreach($fields as $k => $f){
			$statement = str_replace('{field'.($k + 1).'}', $this->quote($f, 'field'), $statement);
		}
		foreach($values as $k => $v){
			$statement = str_replace('{value'.($k + 1).'}', $this->quote($v, 'value'), $statement);
		}
		return $statement;
	}
	
	public function returnQuery($type = 'select'){
		$sql = $this->buildQuery($type);
		$sql = $this->_prepareSubQuery($sql);
		$this->resetParams();
		return $sql;
	}
	
	public function hasOne($class, $alias, $fk, $settings = array()){
		$this->qparams['hasOne'][] = array($class, $alias, $fk, $settings);
		$this->qparams['joinCounter'][$alias] = 1;
		return $this;
	}
	
	public function belongsTo($class, $alias, $fk, $settings = array()){
		$this->qparams['belongsTo'][] = array($class, $alias, $fk, $settings);
		$this->qparams['joinCounter'][$alias] = 1;
		return $this;
	}
	
	public function hasMany($class, $alias, $fk, $settings = array(), $returnNew = false){
		if(!is_object($class)){
			$class = new $class();
		}
		
		$this->qparams['hasMany'][] = array($class, $alias, $fk, $settings, $returnNew);
		
		if($returnNew === true){
			return $class;
		}else{
			return $this;
		}
	}
	
	private function _relations($type, $rels){
		$_fields = array();
		$_joins = array();
		foreach($rels as $rel){
			$list = array();
			if(!empty($this->$rel)){
				$list = array_merge($list, $this->$rel);
			}
			if(!empty($this->qparams[$rel])){
				$list = array_merge($list, $this->qparams[$rel]);
			}
			foreach($list as $k => $relation){
				$className = $relation[0];
				$alias = $relation[1];
				$fkey = $relation[2];
				$settings = !empty($relation[3]) ? $relation[3] : [];
				
				if(is_object($className)){
					$class = $className;
				}else{
					$class = new $className();
				}
				$class->alias = $alias;
				
				//apply any relation settings
				foreach($settings as $sett_k => $sett_v){
					if(in_array($sett_k, ['fields', 'where'])){
						$class->$sett_k($sett_v);
					}else{
						$class->$sett_k = $sett_v;
					}
				}
				
				if(is_array($fkey)){
					$on = $fkey;
				}else{
					if($rel == 'hasOne'){
						$on = [[$this->_addAlias($this->pkey), $this->_addAlias($fkey, $alias), '=', 'field']];
					}else if($rel == 'belongsTo'){
						$on = [[$this->_addAlias($fkey), $this->_addAlias($class->pkey, $alias), '=', 'field']];
					}
				}
				
				if(!empty($class->hasMany) OR !empty($class->qparams['hasMany'])){
					if(empty($this->qparams['hasManyLate'])){
						$this->qparams['hasManyLate'] = array();
					}
					
					$this->qparams['hasManyLate'][] = $class;
					/*
					if(is_string($className)){
						$this->qparams['hasManyLate'][] = $class->hasMany;
					}
					
					if(!empty($class->hasMany)){
						$this->qparams['hasManyLate'][$index] = $class->hasMany;
					}else{
						$this->qparams['hasManyLate'][$index] = $class->qparams['hasMany'];
					}
					*/
				}
				//$this->join($class->tablename, $alias, $on);
				$_joins = array_merge($_joins, $this->_join([[$class->tablename, $alias, $on, 'left']]));
				//$_joins[] = $this->_join([[$class->tablename, $alias, $on, 'left']]);
				//check the class relations
				list($class_fields, $class_joins) = $class->_relations('select', ['hasOne', 'belongsTo']);
				//get the model's table fields
				foreach($class->tablefields as $tablefield){
					$_fields[] = $class->_addAlias($tablefield);
				}
				$this->qparams['mapping'] = array_merge((!empty($this->qparams['mapping']) ? $this->qparams['mapping'] : []), $class->_mapping());
				
				$_fields = array_merge($_fields, $class_fields);
				$_joins = array_merge($_joins, $class_joins);
				//remove this class instance
				unset($class);
			}
		}
		return array($_fields, $_joins);
	}
	
	private function _relationsMany($rels, $data, $multi = true){
		if(empty($data)){
			return $data;
		}
		foreach($rels as $rel){
			$list = array();
			if(!empty($this->$rel)){
				$list = array_merge($list, $this->$rel);
			}
			if(!empty($this->qparams[$rel])){
				$list = array_merge($list, $this->qparams[$rel]);
			}
			foreach($list as $k => $relation){
				$className = $relation[0];
				$alias = $relation[1];
				$fkey = $relation[2];
				$settings = !empty($relation[3]) ? $relation[3] : [];
				
				if(is_object($className)){
					$class = $className;
				}else{
					$class = new $className();
				}
				$class->alias = $alias;
				
				if($multi){
					$ids = Arr::getVal($data, array('[n]', $this->alias, $this->pkey), []);
				}else{
					$ids = Arr::getVal($data, array($this->alias, $this->pkey), []);
				}
				
				if(!is_array($ids)){
					$ids = (array)$ids;
				}
				$ids = array_unique(array_filter($ids));
				//apply any relation settings
				foreach($settings as $sett_k => $sett_v){
					if(in_array($sett_k, ['fields'])){
						$class->$sett_k($sett_v);
					}else{
						$class->$sett_k = $sett_v;
					}
				}
				if(empty($settings['fields']) AND !empty($this->qparams['fields'])){
					//if the relation hasn o fields then use the default fields list
					$class->fields($this->qparams['fields']);
				}
				
				if(empty($class->qparams['where']) AND !empty($class->qparams['group']) AND $class->qparams['group'] == [$class->_addAlias($fkey)]){
					$settings['single'] = true;
				}
				
				if(!empty($ids)){
					$_data = $class->where($fkey, $ids, 'in')->select();
				}else{
					$_data = [];
				}
				
				$_s = array();
				if(!empty($_data)){
					foreach($_data as $i => $_item){
						if(!empty($_item[$alias][$fkey])){
							$_sk = $_item[$alias][$fkey];
							foreach($_item as $_alias => $a_data){
								if(!empty($settings['single'])){
									$_s[$_sk][$_alias] = $a_data;
								}else{
									$_s[$_sk][$_alias][] = $a_data;
								}
							}
						}
					}
				}
				
				if(!empty($_s)){
					if($multi){
						foreach($data as $i => $item){
							if(!empty($_s[$item[$this->alias][$this->pkey]])){
								$_sk = $item[$this->alias][$this->pkey];
								$data[$i] = array_merge_recursive($data[$i], $_s[$_sk]);
							}
						}
					}else{
						if(!empty($_s[$data[$this->alias][$this->pkey]])){
							$_sk = $data[$this->alias][$this->pkey];
							$data = array_merge_recursive($data, $_s[$_sk]);
						}
					}
				}
				//pr($_s);
				$children = Arr::getVal($_data, array('[n]', $this->alias, $this->pkey));
				//remove this class instance
				unset($class);
			}
		}
		return $data;
	}
	
	private function _relationsManyLate($data, $multi){
		if(empty($data)){
			return $data;
		}
		if(!empty($this->qparams['hasManyLate'])){
			foreach($this->qparams['hasManyLate'] as $className){// => $list){
				
				if(is_object($className)){
					$class = $className;
				}else{
					$class = new $className();
				}
				
				$data = $class->_relationsMany(['hasMany'], $data, $multi);
				//remove this class instance
				unset($class);
			}
		}
		return $data;
	}
	
	public function resetParams(){
		$this->qparams = array();
		return $this;
	}
	
	public function getParams(){
		return $this->qparams;
	}
	
	public function setParams($params){
		$this->qparams = array_merge($this->qparams, $params);
		return $this;
	}
	
	public function buildQuery($type = 'select', &$data = [], $settings = []){
		$qs = array();
		
		$this->qparams['mapping'] = $this->_mapping();
		
		if($type == 'select'){
			$qs[] = 'SELECT';
			
			$_fields = array();
			$_joins = array();
			foreach($this->tablefields as $tablefield){
				$_fields[] = $this->_addAlias($tablefield);
			}
			
			list($relation_fields, $relation_joins) = $this->_relations('select', ['hasOne', 'belongsTo']);
			$_fields = array_merge($_fields, $relation_fields);
			
			$fields_to_use = !empty($this->qparams['fields']) ? $this->qparams['fields'] : $_fields;
			
			$fields = $this->_fields($fields_to_use, $_fields);
			$qs[] = implode(', ', $fields);
			/*
			if(!empty($this->qparams['fields'])){
				$fields = $this->_fields($fields_to_use, $_fields);
				$qs[] = implode(', ', $fields);
			}
			*/
			if(!empty($this->qparams['search'])){
				foreach($this->qparams['search']['fields'] as $k => $search_fields){
					$search_fields = $this->_fields($search_fields, $_fields);
					$qs[] = ', '.implode(', ', $search_fields);
				}
			}
			
			$qs[] = 'FROM';
			if(!empty($this->qparams['from'])){
				$qs[] = implode(' ', $this->qparams['from']).(!empty($this->alias) ? ' AS '.$this->quote($this->alias, 'table') : '');
			}else{
				$qs[] = $this->quote($this->tablename, 'table').(!empty($this->alias) ? ' AS '.$this->quote($this->alias, 'table') : '');
			}
			
			$toJoin = array();
			if(!empty($relation_joins)){
				$toJoin = array_merge($toJoin, $relation_joins);
			}
			//$qs[] = implode(' ', $relation_joins);
			if(!empty($this->qparams['join'])){
				//$qs[] = implode(' ', $this->_join($this->qparams['join']));
				$toJoin = array_merge($toJoin, $this->_join($this->qparams['join']));
			}
			if(!empty($toJoin)){
				if(!empty($this->qparams['joinCounter'])){
					foreach($this->qparams['joinCounter'] as $alias => $one){
						foreach($toJoin as $k => $joinData){
							if($joinData[0] == $alias){
								$alias = array_shift($joinData);
								$qs[] = implode(' ', $joinData);
								unset($toJoin[$k]);
							}
						}
					}
				}
				if(!empty($toJoin)){
					foreach($toJoin as $k => $joinData){
						$alias = array_shift($joinData);
						$qs[] = implode(' ', $joinData);
					}
				}
			}
			//$qs[] = implode(' ', $relation_joins);
			
			if(!empty($this->qparams['where'])){
				$qs[] = 'WHERE';
				$qs[] = implode(' ', $this->_where($this->qparams['where']));
			}
			
			if(!empty($this->qparams['having'])){
				$qs[] = 'HAVING';
				$qs[] = implode(' ', $this->_where($this->qparams['having']));
			}
			
			if(!empty($this->qparams['group'])){
				$qs[] = 'GROUP BY';
				$qs[] = implode(', ', $this->_group($this->qparams['group']));
			}
			
			if(!empty($this->qparams['order'])){
				$qs[] = 'ORDER BY';
				$qs[] = implode(', ', $this->_order($this->qparams['order']));
			}
			
			if(!empty($this->qparams['limit'])){
				$qs[] = 'LIMIT '.$this->qparams['limit'];
			}
			
			if(!empty($this->qparams['offset'])){
				$qs[] = 'OFFSET '.$this->qparams['offset'];
			}
			
		}
		
		if($type == 'insert'){
			if(!empty($settings['ignore'])){
				$qs[] = 'INSERT IGNORE INTO';
			}else{
				$qs[] = 'INSERT INTO';
			}
			
			$qs[] = $this->quote($this->tablename, 'table');
			
			$query_fields = array();
			foreach($data as $k => $v){
				if(!in_array($k, $this->tablefields)){
					unset($data[$k]);
					continue;
				}
				$query_fields[] = $k;
			}
			//if there are no eligible fields, exit
			if(empty($query_fields)){
				return false;
			}
			//quote columns names for security
			$query_fields_q = array_map(array($this->dbo, 'quoteName'), $query_fields);
			//build the fields section in an insert query
			$qs[] = '('.implode(', ', $query_fields_q).')';
			$qs[] = ' values ';
			$qs[] = '(:'.implode(', :', $query_fields).')';
			
			if(!empty($settings['duplicate_update'])){
				$qs[] = 'ON DUPLICATE KEY UPDATE';
				$dupdates = [];
				foreach($query_fields as $k => $query_field){
					$dupdates[] = implode(' ', [$query_fields_q[$k], '=', ':'.$query_field]);
				}
				$qs[] = implode(',', $dupdates);
			}
		}
		
		if($type == 'update'){
			$qs[] = 'UPDATE';
			$qs[] = $this->quote($this->tablename, 'table').' AS '.$this->quote($this->alias, 'table');
			
			$query_fields = array();
			foreach($data as $k => $v){
				if(!in_array($k, $this->tablefields)){
					unset($data[$k]);
					continue;
				}
				$query_fields[] = $k;
			}
			//if there are no eligible fields, exit
			if(empty($query_fields)){
				return false;
			}
			//quote columns names for security
			$query_fields_q = array_map(array($this->dbo, 'quoteName'), $query_fields);
			//build the fields section in an insert query
			$qs[] = 'SET';
			$chunks = array();
			foreach($query_fields as $k => $query_field){
				if($query_field != $this->pkey){
					$value = ':'.$query_field;
					if(!empty($settings['ready']) AND in_array($query_field, $settings['ready'])){
						$value = $data[$query_field];
					}
					$chunks[] = $query_fields_q[$k].' = '.$value;
				}else{
					unset($data[$query_field]);//remove the pkey for pdo parameter matching
				}
			}
			$qs[] = implode(', ', $chunks);
			
			if(!empty($this->qparams['where'])){
				$qs[] = 'WHERE';
				$qs[] = implode(' ', $this->_where($this->qparams['where'], false));
			}
		}
		
		if($type == 'delete'){
			$qs[] = 'DELETE';
			
			$tablename = $this->quote($this->tablename, 'table');
			if(!empty($this->qparams['fields'])){
				$fields = $this->_fields($this->qparams['fields'], [], false);
				$qs[] = implode(', ', $fields);
				
				$tablename = $this->quote($this->tablename, 'table').' AS '.$this->quote($this->alias, 'table');
			}
			
			$qs[] = 'FROM';
			//$qs[] = $this->quote($this->tablename, 'table');
			list($relation_fields, $relation_joins) = $this->_relations('select', ['hasOne', 'belongsTo']);
			$qs[] = $tablename;
			
			//$qs[] = implode(' ', $relation_joins);
			$toJoin = $relation_joins;
			foreach($toJoin as $k => $joinData){
				$alias = array_shift($joinData);
				$qs[] = implode(' ', $joinData);
			}
			
			if(!empty($this->qparams['where'])){
				$qs[] = 'WHERE';
				$qs[] = implode(' ', $this->_where($this->qparams['where'], false));
			}
		}
		
		//pr(implode(' ', $qs));
		$sql = implode(' ', $qs);
		$sql = $this->dbo->_close($sql);
		//pr($sql);
		return $sql;
	}
	
	public function beforeSelect($type = 'all', $settings = []){
		if($type == 'count'){
			//if(empty($this->qparams['fields'])){
			$field = !empty($this->pkey) ? 'COUNT('.$this->pkey.')' : 'COUNT(*)';
			$this->qparams['fields'] = [$field => $this->alias.'.count'];
			$this->qparams['order'] = [];
			//}
		}
	}
	
	public function select($type = 'all', $settings = []){
		$settings = $this->_settings($settings);
		$this->beforeSelect($type, $settings);
		
		$sql = $this->buildQuery('select');
		//pr($sql);
		$this->dbo->_log($sql);
		
		if(in_array($type, array('first', 'count'))){
			$settings['multi'] = $multi = false;
			$data = $this->dbo->loadAssoc($sql);
		}else{
			$settings['multi'] = $multi = true;
			$data = $this->dbo->loadAssocList($sql);
		}
		if(!empty($data)){
			$data = $this->_groupData($data, $multi);
		}
		
		if($type != 'count'){
			$data = $this->_relationsMany(['hasMany'], $data, $multi);
			$data = $this->_relationsManyLate($data, $multi);
		}
		
		$data = $this->afterSelect($data, $type, $settings);
		//reset the query settings
		$this->resetParams();
		//pr($data);
		return $data;
	}
	
	public function afterSelect($data, $type, $settings){
		if(!empty($data)){
			if($type == 'list'){
				$new = array();
				foreach($data as $k => $assoc){
					$assoc = array_values($assoc[$this->alias]);
					$count = count($assoc);
					if($count == 1){
						$new[$assoc[0]] = $assoc[0];
					}elseif($count > 1){
						$new[$assoc[0]] = $assoc[1];
					}
				}
				$data = $new;
			}
			
			if($type == 'flat'){
				$this->qparams['parent_id'] = !empty($settings['parent_field']) ? $settings['parent_field'] : 'parent_id';
				$parent_id = !empty($settings['parent_id']) ? $settings['parent_id'] : 0;
				$data = $this->build_flat_list($data, $parent_id);
			}
			if($type == 'threaded'){
				$this->qparams['parent_id'] = !empty($settings['parent_field']) ? $settings['parent_field'] : 'parent_id';
				$parent_id = !empty($settings['parent_id']) ? $settings['parent_id'] : 0;
				$data = $this->build_threaded_list($data, $parent_id);
			}
			if($type == 'count'){
				if(!empty($data)){
					$data = $data[$this->alias]['count'];
				}
			}
			
			if(!empty($settings['json'])){
				if($settings['multi']){
					foreach($data as $k => $v){
						foreach($settings['json'] as $field){
							$field = $this->_addAlias($field);
							$value = Arr::getVal($v, explode('.', $field), null);
							if(!is_null($value)){
								if(strlen($value) == 0){
									$value = '[]';
								}
								$data = Arr::setVal($data, array_merge([$k], explode('.', $field)), json_decode($value, true));
							}
						}
					}
				}else{
					foreach($settings['json'] as $field){
						$field = $this->_addAlias($field);
						$value = Arr::getVal($data, explode('.', $field), null);
						if(!is_null($value)){
							if(strlen($value) == 0){
								$value = '[]';
							}
							$data = Arr::setVal($data, explode('.', $field), json_decode($value, true));
						}
					}
				}
			}
			
			if(!empty($settings['parameter'])){
				if($settings['multi']){
					foreach($data as $k => $v){
						foreach($settings['parameter'] as $field){
							$field = $this->_addAlias($field);
							$value = Arr::getVal($v, explode('.', $field), '');
							$data = Arr::setVal($data, array_merge([$k], explode('.', $field)), (new Parameter(json_decode($value, true))));
						}
					}
				}else{
					foreach($settings['parameter'] as $field){
						$field = $this->_addAlias($field);
						$value = Arr::getVal($data, explode('.', $field), '');
						$data = Arr::setVal($data, explode('.', $field), (new Parameter(json_decode($value, true))));
					}
				}
			}
			
			if($type == 'indexed'){
				$new = array();
				if(!empty($settings['index'])){
					$index = $settings['index'];
				}else{
					$index = [$this->pkey];
				}
				
				foreach($data as $k => $assoc){
					$invalue = '';
					foreach($index as $ind){
						$invalue .= $assoc[$this->alias][$ind];
					}
					
					if($settings['multi']){
						$new[$invalue][] = $assoc;
					}else{
						$new[$invalue] = $assoc;
					}
				}
				$data = $new;
			}
		}
		return $data;
	}
	
	private function _groupData($data, $multi = true){
		$new = array();
		if($multi){
			foreach($data as $i => $d){
				foreach($d as $k => $v){
					$ks = explode('.', $k);
					$new = Arr::setVal($new, array_merge([$i], $ks), $v);
				}
			}
		}else{
			foreach($data as $k => $v){
				$ks = explode('.', $k);
				$new = Arr::setVal($new, $ks, $v);
			}
		}
		return $new;
	}
	
	function build_threaded_list(array &$elements, $parentId = 0, $_depth = 0){
		$branch = array();
		foreach($elements as $k => $element){
			if($element[$this->alias][$this->qparams['parent_id']] == $parentId){
				$element[$this->alias]['_depth'] = $_depth;
				$children = $this->build_threaded_list($elements, $element[$this->alias][$this->pkey], ($_depth + 1));
				if($children){
					$element[$this->alias]['children'] = $children;
				}
				$branch[$k] = $element;
				unset($elements[$k]);
			}
		}
		return $branch;
	}
	
	function build_flat_list(array &$elements, $parentId = 0, $_depth = 0){
		$branch = array();
		foreach($elements as $k => $element){
			if($element[$this->alias][$this->qparams['parent_id']] == $parentId){
				$element[$this->alias]['_depth'] = $_depth;
				$branch[] = $element;
				$children = $this->build_flat_list($elements, $element[$this->alias][$this->pkey], ($_depth + 1));
				if($children){
					$branch = array_merge($branch, $children);
				}
			}
		}
		return $branch;
	}
	
	public function validate($data = array(), $new = false, $list = []){
		return true;
	}
	
	public function validations($setup, $data = array(), $list = []){
		$result = true;
		if(empty($list)){
			$list = array_keys($setup);
		}
		foreach($setup as $field => $config){
			if(!in_array($field, $list)){
				continue;
			}
			$rule = $config[0];
			$message = $config[1];
			
			if(!array_key_exists($field, $data) OR (bool)Validate::$rule($data[$field]) !== true){
				$result = false;
				$this->errors[$field] = $message;
			}
		}
		return $result;
	}
	
	public function save($data, $settings = []){
		if(!empty($data[$this->pkey])){
			return $result = $this->where($this->pkey, $data[$this->pkey])->update($data, $settings);
		}else{
			return $result = $this->insert($data, $settings);
		}
	}
	
	public function increment($field, $value = 1){
		$this->update([], ['increment' => [$field => $value]]);
	}
	
	public function decrement($field, $value = 1){
		$this->update([], ['decrement' => [$field => $value]]);
	}
	
	public function beforeInsert($data, $settings){
		if(!empty($settings['json'])){
			foreach($settings['json'] as $field){
				$value = Arr::getVal($data, [$field], array());
				$data = Arr::setVal($data, [$field], json_encode($value, JSON_UNESCAPED_UNICODE));
			}
		}
		if(!empty($settings['alias'])){
			foreach($settings['alias'] as $source => $field){
				if(empty($data[$field]) AND !empty($data[$source])){
					$data[$field] = Str::slug($data[$source], '-', true);
				}
			}
		}
		return $data;
	}
	
	public function insert($data, $settings = []){
		if(!empty($settings['validate'])){
			if($this->validate($data, true) === false){
				return false;
			}
		}
		
		if(!isset($settings['before']) OR $settings['before'] === true){
			$data = $this->beforeInsert($data, $settings);
		}
		
		$build = [];
		$build['ignore'] = !empty($settings['ignore']) ? true : false;
		$build['duplicate_update'] = !empty($settings['duplicate_update']) ? true : false;
		
		$sql = $this->buildQuery('insert', $data, $build);
		$this->dbo->_log($sql, $data);
		
		if(!$this->dbo->execute_query($sql, $data)){
			return false;
		}
		
		if(!empty($this->pkey)){
			if(empty($data[$this->pkey])){
				$last_insert = $this->dbo->lastInsertId();
				$this->id = !empty($last_insert) ? $last_insert : null;
				
				$data[$this->pkey] = $this->id;
			}else{
				$this->id = $data[$this->pkey];
			}
		}
		
		$this->afterInsert($data, $settings);
		$this->data = $data;
		
		return true;
	}
	
	public function afterInsert($data, $settings){
		
	}
	
	public function beforeUpdate($data, $settings){
		if(!empty($settings['json'])){
			foreach($settings['json'] as $field){
				$value = Arr::getVal($data, [$field], array());
				$data = Arr::setVal($data, [$field], json_encode($value, JSON_UNESCAPED_UNICODE));
			}
		}
		if(!empty($settings['alias'])){
			foreach($settings['alias'] as $source => $field){
				if(array_key_exists($field, $data) AND empty($data[$field]) AND !empty($data[$source])){
					$data[$field] = Str::slug($data[$source], '-', true);
				}
			}
		}
		if(!empty($settings['increment'])){
			foreach($settings['increment'] as $field => $value){
				$data[$field] = $this->quote($field, 'field', false).'+ '.$value;
			}
		}
		if(!empty($settings['decrement'])){
			foreach($settings['decrement'] as $field => $value){
				$data[$field] = $this->quote($field, 'field', false).'- '.$value;
			}
		}
		return $data;
	}
	
	public function update($data = [], $settings = []){
		if(!empty($settings['validate'])){
			if($this->validate($data) === false){
				return false;
			}
		}
		
		if(!isset($settings['before']) OR $settings['before'] === true){
			$data = $this->beforeUpdate($data, $settings);
		}
		
		$build = [];
		$build['ready'] = [];
		if(!empty($settings['increment'])){
			$build['ready'] = array_merge($build['ready'], array_keys($settings['increment']));
		}
		if(!empty($settings['decrement'])){
			$build['ready'] = array_merge($build['ready'], array_keys($settings['decrement']));
		}
		
		$data2 = $data;//take a copy of the data before its modified by the buildQuery
		
		$sql = $this->buildQuery('update', $data, $build);
		$this->dbo->_log($sql, $data);
		//reset the query settings
		$this->resetParams();
		
		if(!$this->dbo->execute_query($sql, $data)){
			return false;
		}
		$this->afterUpdate($data, $settings);
		
		if(!empty($data2[$this->pkey])){
			$this->id = $data2[$this->pkey];
		}
		$this->data = $data;
		
		return true;
	}
	
	public function afterUpdate($data, $settings){
		
	}
	
	public function beforeDelete(){
		
	}
	
	public function delete(){
		$proceed = $this->beforeDelete();
		if($proceed === false){
			return false;
		}
		$sql = $this->buildQuery('delete');
		
		$this->dbo->_log($sql);
		//reset the query settings
		$this->resetParams();
		//pr($sql);
		//return;
		
		$result = $this->dbo->exec($sql);
		
		if($result === false){
			return false;
		}
		return $result;
	}
	
	public function dropField($name){
		$return = $this->dbo->dropTableField($this->tablename, $name);
		
		if($this->cached){
			$this->Cache->clear($this->tablename.'.columns');
			$this->Cache->clear($this->tablename.'.pkey');
		}
		
		return $return;
	}
	
	public function addField($name, $params){
		$return = $this->dbo->addTableField($this->tablename, $name, $params);
		
		if($this->cached){
			$this->Cache->clear($this->tablename.'.columns');
			$this->Cache->clear($this->tablename.'.pkey');
		}
		
		return $return;
	}
	
	public function alterField($name, $newName, $params = []){
		$return = $this->dbo->alterTableField($this->tablename, $name, $newName, $params);
		
		if($this->cached){
			$this->Cache->clear($this->tablename.'.columns');
			$this->Cache->clear($this->tablename.'.pkey');
		}
		
		return $return;
	}
}