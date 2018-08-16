<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$data = $this->Parser->parse($function['data_provider'], true);
	
	$session_id = $function['session_id'];
	
	$session = \GApp::session();
	
	$old = $session->get($session_id, []);
	
	$new = array_replace_recursive($old, $data);
	
	$session->set($session_id, $new);
	
	if(!empty($function['data_merge'])){
		$this->data = array_replace_recursive($new, $this->data);
	}
	
	if(!empty($function['clear'])){
		$session->clear($session_id);
	}