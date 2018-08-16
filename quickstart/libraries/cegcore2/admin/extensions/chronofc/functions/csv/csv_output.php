<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$action = $function['action'];
	$delimiter = $function['delimiter'];
	$file_name = $this->Parser->parse($function['file_name'], true);
	
	$data = $this->Parser->parse($function['data_provider'], true);
	
	$csv_output = function($data, $delimiter){
		$outstream = fopen('php://output', 'w');
		fprintf($outstream, chr(0xEF).chr(0xBB).chr(0xBF));
		
		array_walk($data, 
		function(&$vals, $key, $filehandler) use ($delimiter){
			fputcsv($filehandler, $vals, $delimiter);
		}, $outstream);
		
		fclose($outstream);
	};
	
	if(!empty($action)){
		
		$first = array_values($data)[0];
		$titles = array_keys($first);
		$titles = array_combine($titles, $titles);
		
		if(!empty($function['titles'])){
			list($new_titles) = $this->Parser->multiline($function['titles']);
			foreach($new_titles as $title){
				$titles[$title['name']] = $this->Parser->parse($title['value'], true);
			}
		}
		
		$lines = [];
		$lines[] = $titles;
		
		if(!empty($function['disable_titles'])){
			$lines = [];
		}

		if(!empty($data)){
			foreach($data as $row){
				$lines[] = $row;
			}
		}
		
		@ob_end_clean();
		
		ob_start();
		$csv_output($lines, $delimiter);
		$file_data = ob_get_clean();
		
		if(in_array($action, ['store', 'store_download'])){
			$file_path = $this->Parser->parse($function['file_path'], true);
			$saved = \G2\L\File::write($file_path, $file_data);
			
			if($saved == true){
				$this->set($function['name'], ['path' => $file_path]);
			}else{
				$this->Parser->messages['error'][$function['name']] = rl('Error saving the csv file.');
			}
		}
		
		if(in_array($action, ['download', 'store_download'])){
			$this->Parser->end();
			
			header('Content-type: text/csv');
			header('Content-Disposition: attachment; filename='.$file_name);
			header('Pragma: no-cache');
			header('Expires: 0');
			
			echo $file_data;
			
			exit;
		}
	}