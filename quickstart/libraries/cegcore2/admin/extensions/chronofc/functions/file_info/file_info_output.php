<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$result = false;
	if(!empty($function['path'])){
		
		$path = $this->Parser->parse(trim($function['path']), true);
		
		if(is_dir($path)){
			if(!empty($function['selection'])){
				//get last modified file
				$latest_ctime = 0;
				$latest_filename = '';    

				$d = dir($path);
				
				while(false !== ($entry = $d->read())){
					
					$filepath = $path.DS.$entry;
					if(is_file($filepath) AND filectime($filepath) > $latest_ctime){
						$latest_ctime = filectime($filepath);
						$latest_filename = $entry;
					}
				}
				
				if(!empty($latest_filename)){
					$path = $path.DS.$latest_filename;
				}
			}
		}
		
		if(file_exists($path)){
			$result = [];
			$result['extension'] = pathinfo($path, PATHINFO_EXTENSION);
			$result['size'] = filesize($path);
			$result['modified'] = filemtime($path);
			$result['name'] = basename($path);

		}else{
			$this->Parser->messages['error'][$function['name']] = rl('File does not exist.');
		}
		
	}else{
		$this->Parser->messages['error'][$function['name']] = rl('Empty file path.');
	}
	$this->set($function['name'], $result);