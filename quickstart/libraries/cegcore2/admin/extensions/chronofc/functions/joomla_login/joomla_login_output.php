<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$mainframe = \JFactory::getApplication();
	
	$credentials = array();
	$credentials['username'] = $this->Parser->parse(trim($function['username_provider']), true);
	$credentials['password'] = $this->Parser->parse(trim($function['password_provider']), true);
	
	if($mainframe->login($credentials) === true){
		$this->set($function['name'], true);
		$this->Parser->fevents[$function['name']]['success'] = true;
		$this->Parser->debug[$function['name']]['_success'] = rl('User logged in successfully.');
		return;
	}else{
		$this->Parser->debug[$function['name']]['_error'] = rl('User login failed.');
		$this->set($function['name'], false);
		$this->Parser->fevents[$function['name']]['fail'] = true;
		return;
	}