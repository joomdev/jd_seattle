<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	echo '<fieldset class="'.$view['class'].'" id="'.$view['id'].'">';
	echo '<legend>'.$view['legend'].'</legend>';
	echo $this->Parser->section($view['name'].'/body');
	echo '</fieldset>';