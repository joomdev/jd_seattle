<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$returned = eval($function['code']);
	
	$this->Parser->debug[$function['name']]['returned'] = $returned;
	
	$this->set($function['name'], $returned);