<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	
?>
<?php $this->view('views.pages.blocks.'.$block['type'], [
	'block' => $block, 
	'blocks' => $blocks, 
]); ?>