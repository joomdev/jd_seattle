<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	
	$_result = [];
	$_errors = [];
	$_debug = [];
	
	$upload_save_file = function($file, $extensions, $path) use ($function, &$_errors){
		$pathinfo = pathinfo($file['name']);
		$ext = $pathinfo['extension'];
		$fname = $pathinfo['filename'];
		
		if(!in_array(strtolower($ext), $extensions)){
			$_errors[] = $this->Parser->parse($function['file_extension_error'], true);
			return false;
		}
		
		if($file['size']/1000 > (int)$function['max_size']){
			$_errors[] = $this->Parser->parse($function['max_size_error'], true);
			return false;
		}
		
		if(!empty($function['filename_provider'])){
			$this->set($function['name'].'.file.fullname', $file['name']);
			$this->set($function['name'].'.file.name', $fname);
			$this->set($function['name'].'.file.extension', $ext);
			
			$vfilename = $this->Parser->parse($function['filename_provider'], true);
		}else{
			$fname = \G2\L\Str::slug($fname);
			
			$fname = \G2\L\Dater::datetime('YmdHis').'_'.$fname;
			$vfilename = $fname.'.'.$ext;
		}
		
		$target = $path.$vfilename;
		
		$saved = \G2\L\Upload::save($file['tmp_name'], $target);
		
		if($saved){
			$return = [];
			$return['path'] = $target;
			$return['filename'] = $vfilename;
			$return['name'] = $file['name'];
			$return['size'] = filesize($target);
			
			return $return;
		}
	};
	
	//if(!empty($function['config'])){
		
		//list($configs) = $this->Parser->multiline($function['config']);
		
	if(!empty($function['path'])){
		$path = trim($function['path']);
		$path = $this->Parser->parse($path, true);
		$path = str_replace(array('/', '\\'), DS, $path);
		$path = rtrim($path, DS).DS;
	}else{
		$path = \G2\Globals::ext_path(\GApp::instance()->extension, 'front').'uploads'.DS;
	}
	
	$this->Parser->debug[$function['name']]['path'] = $path;
	
	if(!file_exists($path)){
		$_errors[] = rl('Destination directory not available.');
	}else if(!is_writable($path)){
		$_errors[] = rl('Destination directory not writable.');
	}
	
	if(!empty($_errors)){
		$this->Parser->messages['error'][$function['name']] = $_errors;
		$this->set($function['name'], false);
		$this->Parser->fevents[$function['name']]['fail'] = true;
		return;
	}
	
	$attachments = [];
	if(!empty($function['extensions'])){
		$g_extensions = explode(',', trim($function['extensions']));
	}
	
	if(!empty($function['autofields'])){
		$connection = $this->Parser->_connection();
		
		$stored = \GApp::session()->get($connection['alias'].'.upload', []);
		
		if(!empty($stored)){
			foreach($stored as $field){
				$fname = str_replace('[]', '', $field['name']);
				$function['config'] = $function['config']."\n".$fname.(!empty($field['extensions']) ? ':'.$field['extensions'] : '');
				
				if(!empty(\GApp::session()->get($connection['alias'].'.attach.'.$field['name']))){
					$p = '.path';
					if(strpos($field['name'], '[]') !== false){
						$p = '.[n].path';
					}
					\GApp::session()->set($connection['alias'].'.attach.'.$field['name'].'.path', '{var:'.$function['name'].'.'.$fname.$p.'}');
				}
			}
		}
		
		//\GApp::session()->clear($connection['alias'].'.upload');
	}
	
	$processed = [];
	if(!empty($function['config'])){
		list($configs) = $this->Parser->multiline($function['config']);
		
		foreach($configs as $k => $config){
			$name = $config['name'];
			if(in_array($name, $processed)){
				continue;
			}
			$processed[] = $name;
			
			$extensions = explode(',', $config['value']);
			if(empty($extensions) AND !empty($g_extensions)){
				$extensions = $g_extensions;
			}
			
			$this->Parser->debug[$function['name']][$name]['extensions'] = $extensions;
			
			if(empty($extensions) OR empty($name) OR empty($_FILES[$name]['name'])){
				$this->Parser->debug[$function['name']][$name]['info'] = rl('File is not present.');
				
				continue;
			}
			
			$file = $_FILES[$name];
			
			if(is_array($file['name'])){
				foreach($file['name'] as $k => $v){
					if(empty($v)){
						$this->Parser->debug[$function['name']][$name]['info'] = rl('File is not present.');
						continue;
					}
					$returned = $upload_save_file(['name' => $file['name'][$k], 'size' => $file['size'][$k], 'tmp_name' => $file['tmp_name'][$k]], $extensions, $path);
					
					if(!empty($returned)){
						$_result[$name][$k] = $returned;
						$this->data[$name][$k] = $returned['filename'];
						$this->Parser->debug[$function['name']][$name]['saved'] = 1;
					}else{
						$_result = false;
						$this->Parser->debug[$function['name']][$name]['saved'] = 0;
						break;
					}
				}
			}else{
				$returned = $upload_save_file($file, $extensions, $path);
				
				if(!empty($returned)){
					$_result[$name] = $returned;
					$this->data[$name] = $returned['filename'];
					$this->Parser->debug[$function['name']][$name]['saved'] = 1;
				}else{
					$_result = false;
					$this->Parser->debug[$function['name']][$name]['saved'] = 0;
					break;
				}
			}
		}
		
	}else{
		$_errors[] = rl('Files config is empty');
	}
	
	if(!empty($_errors)){
		$this->Parser->messages['error'][$function['name']] = $_errors;
	}
	
	$this->set($function['name'], $_result);
	
	if($_result === false){
		$this->Parser->fevents[$function['name']]['fail'] = true;
	}else{
		$this->Parser->fevents[$function['name']]['success'] = true;
	}