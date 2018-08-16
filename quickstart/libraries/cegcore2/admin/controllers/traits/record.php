<?php
/**
* COMPONENT FILE HEADER
**/
namespace G2\A\C\T;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
trait Record {
	
	function toggleRecord($Model, $msgs = [], $url = ''){
		$result = $Model->where($Model->pkey, $this->data('gcb'))->update([$this->data('fld') => $this->data('val')]);
		
		$success = !empty($msgs[0]) ? $msgs[0] : rl('Updated successfully.');
		$error = !empty($msgs[1]) ? $msgs[1] : rl('Update error.');
		
		$results = [];
		if($result !== false){
			$results['success'] = $success;
			
			$results['state'] = $this->data('val');
		}else{
			$results['error'] = $error;
		}
		
		$results['redirect'] = r2('index.php?ext='.\GApp::instance()->extension.'&cont='.\GApp::instance()->controller);
		
		return $results;
	}
	
	function deleteRecord($Model, $msgs = [], $url = ''){
		if(is_array($this->data('gcb'))){
			
			$result = $Model->where('id', $this->data('gcb'), 'in')->delete();
			
			$success = !empty($msgs[0]) ? $msgs[0] : rl('Deleted successfully.');
			$error = !empty($msgs[1]) ? $msgs[1] : rl('Delete error.');
			
			$results = [];
			if($result !== false){
				$results['success'] = $success;
			}else{
				$results['error'] = $error;
			}
		}
		
		if(empty($url)){
			$url = r2('index.php?ext='.\GApp::instance()->extension.'&cont='.\GApp::instance()->controller);
		}
		
		$results['redirect'] = $url;
		
		return $results;
	}
	
	function saveRecord($Model, $msgs = [], $url = ''){
		$result = false;
		
		if(!empty($this->data[$Model->alias])){
			$result = $Model->save($this->data[$Model->alias], ['validate' => true]);
		}
		
		if($result === true){
			if(isset($this->data['apply'])){
				$redirect = r2('index.php?ext='.\GApp::instance()->extension.'&cont='.\GApp::instance()->controller.'&act=edit&id='.$Model->id);
			}else{
				$redirect = r2('index.php?ext='.\GApp::instance()->extension.'&cont='.\GApp::instance()->controller);
			}
			
			return ['success' => !empty($msgs[0]) ? $msgs[0] : rl('Saved successfully.'), 'redirect' => $redirect];
		}else{
			$this->errors[$Model->alias] = $Model->errors;
			
			unset($this->data['save']);
			unset($this->data['apply']);
			
			return ['error' => $Model->errors, 'reload' => true];
		}
	}
}
?>