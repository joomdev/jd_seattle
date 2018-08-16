<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$connection = $this->get('__connection');
	$event = $this->get('__event');
	//check if event does not exist
	if(!isset($connection['events'][$event]) AND isset($connection['params']['event_not_found'])){
		$this->Parser->parse($connection['params']['event_not_found']);
		return;
	}
	
	$this->Parser->parse('{event:'.$event.'}');
	
	if(!empty($this->errors)){
		\GApp::session()->flash('error', $this->errors);
	}
	
	if(!empty($this->Parser->messages)){
		foreach($this->Parser->messages as $type => $messages){
			\GApp::session()->flash($type, $messages);
		}
	}