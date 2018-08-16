<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	echo '<'.$view['tag'].' class="'.$view['class'].'">';
	
	echo $this->Parser->parse($view['text'], true);
	
	if(!empty($view['subtext'])){
		echo '<div class="sub header">'.$this->Parser->parse($view['subtext'], true).'</div>';
	}
	
	echo '</'.$view['tag'].'>';