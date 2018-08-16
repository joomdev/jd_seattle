<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	echo $this->Sorter->link($this->Parser->parse($view['content'], true), trim(str_replace('.', '_', $view['field'])));