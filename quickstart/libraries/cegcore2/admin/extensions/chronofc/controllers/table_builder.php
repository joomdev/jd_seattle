<?php
/**
* COMPONENT FILE HEADER
**/
namespace G2\A\E\Chronofc\C;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
trait TableBuilder {
	function index(){
		$Model = new \G2\L\Model(['name' => 'Table', 'table' => $this->data('name')]);
		
		$this->Paginate($Model);
		
		//$order = $this->Composer->sorter(array_combine($Model->tablefields, $Model->tablefields));
		//$Model->order($order);
		$this->Order($Model, array_combine($Model->tablefields, $Model->tablefields));
		
		$rows = $Model->select('all');
		$this->set('rows', $rows);
		$this->set('fields', $Model->tablefields);
		$this->set('pkey', $Model->pkey);
	}
	
	//data reading
	
	function view(){
		$Model = new \G2\L\Model(['name' => 'Table', 'table' => $this->data('name')]);
		if(!empty($this->data['id'])){
			$row = $Model->where($Model->pkey, $this->data('id', null))->select('first');
			$this->set('row', $row);
			$this->set('fields', $Model->tablefields);
			$this->set('pkey', $Model->pkey);
		}
	}
	
	function refresh(){
		$this->redirect(r2('index.php?ext='.$this->extension.'&cont=tables&act=build'.rp('table_name', $this->data).rp('gcb[]', $this->data('gcb.0'))));
	}
	
	function chart(){
		
	}
	
	function delete(){
		if(is_array($this->data('gcb'))){
			$Model = new \G2\L\Model(['name' => 'Table', 'table' => $this->data('table_name')]);
			
			$result = $Model->where($Model->pkey, $this->data('gcb'), 'in')->delete();
			
			if($result !== false){
				\GApp::session()->flash('success', rl('Records deleted successfully.'));
			}else{
				\GApp::session()->flash('error', rl('Records delete error.'));
			}
		}
		
		$this->redirect(r2('index.php?ext='.$this->extension.'&cont=tables&name='.$this->data('table_name')));
	}
	
	function fullcsv(){
		$Model = new \G2\L\Model(['name' => 'Table', 'table' => $this->data('table_name')]);
		
		if(is_array($this->data('gcb'))){
			$data = $Model->where($Model->pkey, $this->data('gcb'), 'in')->select('all');
		}else{
			$data = $Model->select('all');
		}
		
		$data = \G2\L\Arr::getVal($data, '[n].Table');
		
		$this->set('data', $data);
		
		$functions_path = \G2\Globals::ext_path('chronofc', 'admin').'functions'.DS.'csv'.DS.'csv'.'_output.php';
		$view = new \G2\L\View($this);
		$result = $view->view($functions_path, ['function' => ['action' => 'download', 'delimiter' => ',', 'data_provider' => '{var:data}', 'file_name' => $this->data('table_name').'.csv']], true);
	}
	
	function build(){
		$fields = [];
		
		if($this->data('table_name')){
			$dbo = \G2\L\Database::getInstance([]);
			$db_tables = $dbo->getTablesList();
			
			if(in_array($this->data('table_name'), $db_tables)){
				$Model = new \G2\L\Model(['name' => 'Table', 'table' => $this->data('table_name')]);
				
				$table_fields = $Model->dbo->getTableInfo($this->data('table_name'));
				//pr($table_fields);
				if(!empty($table_fields)){
					foreach($table_fields as $k => $field){
						$fields[$k]['title'] = $field['Field'];
						$fields[$k]['default'] = $field['Default'];
						$fields[$k]['extra'] = $field['Extra'];
						$fields[$k]['null'] = (int)($field['Null'] == 'YES');
						
						if($field['Key'] == 'PRI'){
							$fields[$k]['index'] = 'PRIMARY';
						}
						
						$fields[$k]['type'] = strtoupper($field['Type']);
						/*if(strpos($field['Type'], '(') !== false){
							$type = explode('(', $field['Type']);
							$fields[$k]['type'] = strtoupper($type[0]);
							$fields[$k]['length'] = rtrim($type[1], ')');
						}*/
					}
				}
			}
		}
		
		//save the table
		if($this->data('table_name') AND $this->data('save')){
			//existing table, update it
			if(!empty($fields)){
				$newlist = array_intersect_key($this->data['title'], $this->data['fld']);
				$fields_names = \G2\L\Arr::getVal($fields, '[n].title');
				
				//$drops = array_diff($fields_names, $newlist);
				$dropKeys = array_diff(array_keys($this->data['tbl']), array_keys($this->data['fld']));
				//pr($dropKeys);
				foreach($dropKeys as $key){
					$dropName = $this->data['title'][$key];
					if(in_array($dropName, $fields_names)){
						$Model->dropField($dropName);
					}
				}
				
				$addKeys = array_diff(array_keys($this->data['fld']), array_keys($this->data['tbl']));
				//pr($addKeys);
				foreach($addKeys as $key){
					$addName = $this->data['title'][$key];
					$addParams = [
						'default' => $this->data['default'][$key],
						'length' => $this->data['length'][$key],
						'null' => !empty($this->data['null'][$key]) ? 1 : 0,
						'type' => $this->data['type'][$key],
					];
					$Model->addField($addName, $addParams);
				}
				
				foreach($this->data['fld'] as $key => $column){
					if(!in_array($key, $dropKeys) AND !in_array($key, $addKeys) AND !in_array($this->data['title'][$key], $fields_names)){
						$alterName = $this->data['title'][$key];
						$alterParams = [
							'default' => $this->data['default'][$key],
							'length' => $this->data['length'][$key],
							'null' => !empty($this->data['null'][$key]) ? 1 : 0,
							'type' => $this->data['type'][$key],
						];
						$Model->alterField($fields[$key]['title'], $alterName, $alterParams);
					}
				}
				
				\GApp::session()->flash('success', rl('The table has been updated successfully.'));
				$this->redirect(r2('index.php?ext='.$this->extension.'&cont=connections&act=clear_cache'));
			}else{
				//new table, create it
				$rows = array();
				$pkey = '';
				foreach($this->data['fld'] as $k => $column){
					$pcs = array();
					if(!empty($this->data['title'][$k]) AND !empty($this->data['type'][$k])){
						$pcs[] = '`'.$this->data['title'][$k].'`';
						$pcs[] = $this->data['type'][$k].(!empty($this->data['length'][$k]) ? '('.$this->data['length'][$k].')' : '');
						$pcs[] = !empty($this->data['null'][$k]) ? 'DEFAULT NULL' : 'NOT NULL';
						if(!empty($this->data['extra'][$k])){
							$pcs[] = $this->data['extra'][$k];
						}
						if(!empty($this->data['default'][$k]) AND empty($this->data['null'][$k])){
							$pcs[] = "DEFAULT '".$this->data['default'][$k]."'";
						}
						$rows[] = implode(' ', $pcs);
						if(!empty($this->data['index'][$k])){
							$pkey = $this->data['title'][$k];
						}
					}
				}
				if(!empty($pkey)){
					$rows[] = 'PRIMARY KEY (`'.$pkey.'`)';
				}
				$rows = array('CREATE TABLE IF NOT EXISTS `'.$this->data['table_name'].'` (', implode(",\n", $rows));
				$rows[] = ') DEFAULT CHARSET=utf8;';
				$sql = implode("\n", $rows);
				//pr($sql);
				$dbo = \G2\L\Database::getInstance();
				if($dbo->exec($dbo->_prefixTable($sql)) !== false){
					\GApp::session()->flash('success', rl('The table has been created successfully.'));
				}else{
					\GApp::session()->flash('error', rl('Table creation failed.'));
				}
				
				$this->redirect(r2('index.php?ext='.$this->extension.'&cont=connections'));
			}
		}
		
		if(empty(trim($this->data('table_name'))) AND $this->data('save')){
			\GApp::session()->flash('error', rl('Table name can not be empty.'));
		}
		
		
		$basics = [
			['title' => 'aid', 'type' => 'INT(11) unsigned'/*, 'length' => 11*/, 'index' => 'PRIMARY', 'extra' => 'auto_increment'],
			['title' => 'user_id', 'type' => 'INT(11) unsigned', 'default' => 0, /*'length' => 11*/],
			['title' => 'created', 'type' => 'DATETIME', 'default' => '0000-00-00 00:00:00'],
			['title' => 'modified', 'type' => 'DATETIME', 'null' => 1],
		];
		
		$views = [];
		if(is_array($this->data('gcb')) AND !empty($this->Connection)){
			$connection = $this->Connection->where('id', $this->data['gcb'][0])->select('first', ['json' => ['views']]);
			if(!empty($connection['Connection']['views'])){
				foreach($connection['Connection']['views'] as $view){
					if(!empty($view['params']['name'])){
						$name = $view['params']['name'];
						if(strpos($name, '[') !== false){
							$_name = explode('[', $name);
							$_name = array_map(function($item){return trim($item, ']');}, $_name);
							$_name = array_filter($_name);
							$name = array_pop($_name);
						}
						$views[] = ['title' => $name, 'type' => 'VARCHAR(255)'/*, 'length' => 255*/];
					}
				}
			}
		}
		
		if(!empty($fields)){
			$fields_names = \G2\L\Arr::getVal($fields, '[n].title');
			
			foreach($basics as $k => $basic){
				if(in_array($basic['title'], $fields_names)){
					unset($basics[$k]);
				}
			}
			
			foreach($views as $k => $view){
				if(in_array($view['title'], $fields_names)){
					unset($views[$k]);
				}
			}
		}
		
		$this->set('basics', $basics);
		
		$this->set('views', $views);
		
		$this->set('fields', $fields);
	}
}
?>