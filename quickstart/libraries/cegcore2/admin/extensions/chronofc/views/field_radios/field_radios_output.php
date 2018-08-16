<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$Html = new \G2\H\Html();
	
	$field_class = $this->Field->setup('radios', $view, $this->Parser, $Html);
	
	echo $Html->input('radio', (isset($view['style']) ? $view['style'] : 'radio'))->fields([], $field_class);