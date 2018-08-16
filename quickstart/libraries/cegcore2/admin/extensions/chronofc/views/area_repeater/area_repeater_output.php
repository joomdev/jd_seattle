<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$items = $this->Parser->parse($view['data_provider'], true);
	$keys = $this->Parser->parse($view['keys_provider'], true);
	
	if(is_numeric($items)){
		$items = range(0, (int)$items);
	}
	
	if(!is_array($items)){
		$items = [];
	}
	
	if($this->get('_preview') AND empty($items)){
		$items = [0];
	}
	
	if(is_array($items)){
		echo '<div class="'.$view['class'].' repeater" data-count="'.count($items).'" data-limit="'.(!empty($view['max_clones']) ? $view['max_clones'] : '').'">';
			
			if(!empty($view['multiplier'])){
				echo '<div class="ui container fluid source-item" data-name="'.$view['name'].'">';
					$this->set($view['name'].'.key', '-N-');
					$this->set($view['name'].'.key', '#'.$view['name'].'.count');
					echo $this->Parser->section($view['name'].'/body');
					echo '<div class="ui divider"></div>';
				echo '</div>';
			}
			
			foreach($items as $key => $item){
				if(is_array($keys) AND !in_array($key, $keys)){
					continue;
				}
				$this->set($view['name'].'.row', $item);
				$this->set($view['name'].'.key', $key);
				echo '<div class="ui container fluid clone-item">';
					echo $this->Parser->section($view['name'].'/body');
					echo '<div class="ui divider"></div>';
				echo '</div>';
			}
			
			echo $this->Parser->section($view['name'].'/footer');
	
		echo '</div>';
	}