<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	if(!empty($function['autotable'])){
		if(!empty($function['db_table'])){
			$tablename = $function['db_table'];
		}else{
			$tablename = '#__chronoforms_data_';
			if(!empty($this->data('Connection.alias'))){
				$tablename = $tablename.$this->data('Connection.alias');
			}else{
				$tablename = $tablename.\G2\L\Str::slug($this->data('Connection.title'));
			}
			
			$db_options = \G2\Globals::get('custom_db_options', []);
			if(!empty($function['db']['enabled'])){
				$db_options = $function['db'];
			}
			$dbo = \G2\L\Database::getInstance($db_options);
			$db_tables = $dbo->getTablesList();
			
			$this->data['Connection']['functions'][$n]['db_table'] = $tablename = $dbo->_prefixTable($tablename);
			
			if(!in_array($tablename, $db_tables)){
				$rows = [];
				$rows[] = '`aid` int(11) NOT NULL AUTO_INCREMENT';
				$rows[] = '`user_id` int(11) NOT NULL';
				$rows[] = "`created` datetime NOT NULL DEFAULT '0000-00-00 00:00:00'";
				$rows[] = '`modified` datetime DEFAULT NULL';
				
				$rows[] = 'PRIMARY KEY (`aid`)';
				$rows = array('CREATE TABLE IF NOT EXISTS `'.$tablename.'` (', implode(",\n", $rows));
				$rows[] = ') DEFAULT CHARSET=utf8;';
				$sql = implode("\n", $rows);
				$dbo->exec($sql);
			}
		}
		
		$Table = new \G2\L\Model(['name' => 'Table', 'table' => $tablename]);
		//refresh the table fields
		$Table->tablefields(true);
		
		$viewfields = $function['viewfields'];
		if(!empty($viewfields)){
			$viewfields = json_decode($viewfields, true);
			if(!is_array($viewfields)){
				$viewfields = [];
			}
			foreach($viewfields as $viewname => $viewfield){
				if(!in_array($viewfield, $Table->tablefields)){
					unset($viewfields[$viewname]);
				}
			}
			//pr($viewfields);
		}
		
		$fields = [];
		$longs = [];
		if(!empty($this->data('Connection.views'))){
			
			foreach($this->data('Connection.views') as $view){
				if(!empty($view['params']['name']) AND !empty($view['dynamics']['save']['enabled']) AND $view['type'] != 'field_button'){
					$fname = rtrim(str_replace(['[]', '[', ']', '(N)'], ['(N)', '.', '', '.[n]'], $view['params']['name']), '.');
					$lname = explode('.', str_replace('.[n]', '', $fname));
					$fields[$view['name']] = array_pop($lname);
					
					if($view['type'] == 'field_textarea' OR strpos($fname, '.[n]') !== false){
						$longs[] = $fields[$view['name']];
					}
				}
			}
			//pr($fields);
		}
		
		//drop fields
		//pr(array_diff(array_keys($viewfields), array_keys($fields)));
		$drop = array_diff(array_keys($viewfields), array_keys($fields));
		//new fields
		//pr(array_diff(array_keys($fields), array_keys($viewfields)));
		$add = array_diff(array_keys($fields), array_keys($viewfields));
		//updated fields
		//pr(array_diff(array_keys(array_diff($fields, $viewfields)), $add));
		$update = array_diff(array_keys(array_diff($fields, $viewfields)), $add);
		//die();
		foreach($drop as $viewname){
			$fieldname = $viewfields[$viewname];
			$Table->dropField($fieldname);
		}
		
		foreach($add as $viewname){
			$fieldname = $fields[$viewname];
			if(!in_array($fieldname, $Table->tablefields)){
				if(in_array($fieldname, $longs)){
					$Table->addField($fieldname, ['type' => 'text']);
				}else{
					$Table->addField($fieldname, ['type' => 'varchar', 'length' => 255]);
				}
			}
		}
		
		foreach($update as $viewname){
			$Table->alterField($viewfields[$viewname], $fields[$viewname]);
		}
		//refresh the table fields
		//$Table->tablefields(true);
	}