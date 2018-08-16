<?php
/**
* COMPONENT FILE HEADER
**/
namespace G2\A\C\T;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
trait Language {
	
	function Language($ext_name){
		if($this->data('lang')){
			\G2\L\Lang::read(\G2\Globals::ext_path($ext_name, 'admin'), $this->data('lang'));
		}
		
		if(isset($this->data['build'])){
			if($this->data('lang')){
				$this->build();
			}
		}
		
		if((isset($this->data['save']) OR isset($this->data['update'])) AND $this->data('lang')){
			if(isset($this->data['save'])){
				$path = \G2\Globals::ext_path($ext_name, 'admin').'locales'.DS.$this->data('lang').'.custom.ini';
			}else{
				$path = \G2\Globals::ext_path($ext_name, 'admin').'locales'.DS.$this->data('lang').'.ini';
			}
			//pr($path);die();
			$result = \G2\L\File::write($path, $this->data('language_strings'));
			
			if($result === true){
				return ['success' => rl('The language file has been saved successfully'), 'redirect' => r2('index.php?ext='.$ext_name.'&cont=languages&build=1&lang='.$this->data('lang'))];
			}else{
				return ['error' => rl('Error saving the language file.'), 'redirect' => r2('index.php?ext='.$ext_name.'&cont=languages&build=1&lang='.$this->data('lang'))];
			}
		}
		
		$this->set('ext_name', $ext_name);
		$this->view = 'views.common.languages.index';
	}
	
	function buildLanguage($ext_name, $all = false){
		$path = \G2\Globals::ext_path($ext_name, 'admin');
		$files = \G2\L\Folder::getFiles($path, true);
		$path = \G2\Globals::ext_path($ext_name, 'front');
		$files = array_merge($files, \G2\L\Folder::getFiles($path, true));
		
		if($all){
			$path = \G2\Globals::ext_path('chronofc', 'admin');
			$files = array_merge($files, \G2\L\Folder::getFiles($path, true));
		}
		
		$strings = $this->_find_strings($files);
		
		$strings = implode("\n", $strings);
		
		$this->data['language_strings'] = $strings;
		$this->set('strings', $strings);
	}
	
	function _prepare_string($str, $val){
		$found = \G2\L\Lang::find($str, $this->data('lang'));
		$val = ($found === false) ? $val : $found;
		
		return ''.trim($str).' = "'.$val.'"';//htmlspecialchars($val, ENT_COMPAT).'"';
	}
	
	function _find_strings($files){
		$strings = array();
		
		foreach($files as $file){
			if(substr($file, -4, 4) == '.php'){
				$file_code = file_get_contents($file);
				preg_match_all('/(rl|el)\(("|\')([^(\))]*?)\2[,)]/i', $file_code, $langs);
				if(!empty($langs[3])){
					//$strings[] = '; '.$file;
					foreach($langs[3] as $match){
						$strings[] = $this->_prepare_string(\G2\L\Lang::build($match), $match);
					}
				}
			}
		}
		
		$strings = array_unique($strings);
		
		return $strings;
	}
}
?>