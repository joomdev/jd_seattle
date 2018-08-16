<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	//echo '<div class="'.$view['class'].'" id="'.$view['id'].'">';
	echo '<div class="ui container fluid modaled" id="'.$view['id'].'">';
	
		echo '<div class="'.$view['class'].' hidden" data-closable="'.$view['closable'].'">';
		
			echo !empty($view['close_icon']) ? '<i class="close icon"></i>' : '';
			
			echo '<div class="header">';
				echo $this->Parser->section($view['name'].'/header');
			echo '</div>';
			
			echo '<div class="content">';
				echo $this->Parser->section($view['name'].'/body');
			echo '</div>';
			
			echo '<div class="actions">';
				echo $this->Parser->section($view['name'].'/actions');
			echo '</div>';
		
		echo '</div>';
	
	echo $this->Parser->section($view['name'].'/launcher');
	
	echo '</div>';