<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$data = $this->Parser->parse($function['data_provider'], true);
	
	$session = \GApp::session();
	
	$old = $session->get($function['name']);
	
	$session->set($function['name'], $data);
	
	/*return;
	
	if(empty($old) OR !is_array($old)){
		$session->set($function['name'], $data);
	}else{
		$session->set($function['name'], array_replace_recursive($old, $data));
	}*/