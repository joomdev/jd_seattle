<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$Html = new \G2\H\Html();
	
	//$view['attrs'] = implode("\n", ['']);
	$view['container']['class'] = 'field hidden';
	$field_class = $this->Field->setup('text', $view, $this->Parser, $Html);
	
	echo $Html->input('text')->field($field_class);