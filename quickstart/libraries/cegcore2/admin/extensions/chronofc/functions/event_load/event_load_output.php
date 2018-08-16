<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	if(!empty($function['event_name'])){
		$this->Parser->parse('{event:'.$function['event_name'].'}');
	}
	
	if(!empty($function['stop'])){
		$this->Parser->parse('{stop:}');
	}