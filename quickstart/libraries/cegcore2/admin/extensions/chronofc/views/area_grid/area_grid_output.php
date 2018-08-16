<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	echo '<div class="'.$view['class'].'">';
	if(!empty($view['sections'])){
		list($sections) = $this->Parser->multiline($view['sections']);
		
		foreach($sections as $section){
			echo '<div class="column '.$section['name'].(!empty($section['value']) ? ' '.$section['value'] : '').'">';
			echo $this->Parser->section($view['name'].'/'.$section['name']);
			echo '</div>';
		}
	}
	echo '</div>';