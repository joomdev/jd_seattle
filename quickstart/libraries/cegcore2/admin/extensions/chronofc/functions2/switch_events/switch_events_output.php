<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$data = $this->Parser->parse($function['data_provider'], true);
	if($data === true){
		$data = 'true';
	}else if($data === false){
		$data = 'false';
	}
	$this->Parser->fevents[$function['name']][$data] = true;
	$this->set($function['name'], $data);