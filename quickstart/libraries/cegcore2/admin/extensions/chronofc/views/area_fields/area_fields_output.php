<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	echo '<div class="'.(!empty($view['inline']) ? 'inline ' : '').''.$view['number'].' fields" id="'.$view['id'].'">';
	echo $this->Parser->section($view['name'].'/body');
	echo '</div>';