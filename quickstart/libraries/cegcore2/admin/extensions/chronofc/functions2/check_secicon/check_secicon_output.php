<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	
	if(!empty($function['field_name'])){
		
		$sent = $this->data($function['field_name']);
		$stored = \GApp::session()->get('secicon/'.$function['field_name']);
		
		if(!empty($stored) AND ($sent == $stored)){
			$this->Parser->debug[$function['name']]['_success'] = rl('The security image verification was successfull.');
			$this->set($function['name'], true);
			$this->Parser->fevents[$function['name']]['success'] = true;
			\GApp::session()->clear('secicon/'.$function['field_name']);
			return;
		}else{
			$this->Parser->messages['error'][$function['name']][] = $this->Parser->parse($function['failed_error'], true);
			
			$this->Parser->debug[$function['name']]['_error'] = rl('The security image verification has failed.');
			$this->set($function['name'], false);
			$this->Parser->fevents[$function['name']]['fail'] = true;
			\GApp::session()->clear('secicon/'.$function['field_name']);
			return;
		}
		
	}else{
		$this->Parser->messages['error'][$function['name']][] = $this->Parser->parse($function['failed_error'], true);
		
		$this->Parser->debug[$function['name']]['_error'] = rl('The field name has not been provided.');
		$this->set($function['name'], false);
		$this->Parser->fevents[$function['name']]['fail'] = true;
	}