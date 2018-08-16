<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$Html = new \G2\H\Html();
	
	$field_class = $this->Field->setup('calendar', $view, $this->Parser, $Html);
	
	echo $Html->input('calendar')->field($field_class);