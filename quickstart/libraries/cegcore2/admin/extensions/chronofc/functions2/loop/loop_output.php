<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$items = $this->Parser->parse($function['data_provider'], true);
	$keys = $this->Parser->parse($function['keys_provider'], true);
	
	if(is_numeric($items)){
		$items = range(0, (int)$items);
	}
	
	$return = '';
	
	if(is_array($items)){
		if(!empty($function['max_count']) AND (count($items) > (int)$function['max_count'])){
			return;
		}
		
		foreach($items as $key => $item){
			if(is_array($keys) AND !in_array($key, $keys)){
				continue;
			}
			$this->set($function['name'].'.row', $item);
			$this->set($function['name'].'.key', $key);
			if(!empty($function['return'])){
				$return .= $this->Parser->parse($function['content'], true);
			}else{
				$this->Parser->parse($function['content']);
			}
		}
		
		if(!empty($function['return'])){
			$this->set($function['name'], $return);
		}
	}