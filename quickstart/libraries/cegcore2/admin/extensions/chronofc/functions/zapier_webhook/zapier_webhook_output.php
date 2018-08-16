<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$fn_path = \G2\Globals::ext_path('chronofc', 'admin').'functions'.DS.'curl'.DS.'curl_output.php';
	$fn_data = $function;
	$this->view($fn_path, ['function' => $fn_data]);