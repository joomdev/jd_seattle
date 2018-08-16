<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$result = $this->Parser->parse($function['content'], true, true);
	
	if(empty($function['return'])){
		echo $result;
	}
	
	$this->set($function['name'], $result);