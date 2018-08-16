<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	echo '<div class="'.$view['class'].' partitioned">';
	if(!empty($view['sections'])){
		list($sections) = $this->Parser->multiline($view['sections'], true, false);
		
		$disabled_class = !empty($view['sequential']) ? 'disabled' : '';
		
		if($view['style'] == 'steps'){
			echo '<div class="ui top attached steps G2-tabs">';
			foreach($sections as $k => $section){
				echo '<a class="step '.($k == 0 ? 'active' : $disabled_class).'" data-tab="tabs-'.$view['name'].'-'.$section['name'].'">'.(!empty($section['value']) ? $section['value'] : $section['name']).'</a>';
			}
			echo '</div>';
		}else if($view['style'] == 'tabs'){
			echo '<div class="ui top attached tabular menu small G2-tabs">';
			foreach($sections as $k => $section){
				echo '<a class="item '.($k == 0 ? 'active' : $disabled_class).'" data-tab="tabs-'.$view['name'].'-'.$section['name'].'">'.(!empty($section['value']) ? $section['value'] : $section['name']).'</a>';
			}
			echo '</div>';
		}else if($view['style'] == 'sequence'){
			
		}
		
		foreach($sections as $k => $section){
			//echo '<div class="column '.$section['name'].(!empty($section['value']) ? ' '.$section['value'] : '').'">';
			echo '<div class="ui tab segment '.($view['style'] != 'sequence' ? 'bottom attached' : '').' '.($k == 0 ? 'active' : '').'" data-tab="tabs-'.$view['name'].'-'.$section['name'].'">';
			echo $this->Parser->section($view['name'].'/'.$section['name']);
			echo '</div>';
		}
		
		echo $this->Parser->section($view['name'].'/footer');
		
		echo '<div class="ui divider"></div>';
	}
	echo '</div>';