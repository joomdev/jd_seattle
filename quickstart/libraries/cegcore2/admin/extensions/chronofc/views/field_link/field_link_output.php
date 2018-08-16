<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$Html = new \G2\H\Html();
	
	if(!empty($view['actions']['type'])){
		switch($view['actions']['type']){
			case 'dynamic-load':
				$settings = $view['actions']['dynamic-load'];
				
				$attrs = ['class:G2-dynamic', 'data-counter:'.(!empty($settings['counter']) ? $settings['counter'] : 0)];
				if(!empty($settings['event'])){
					$attrs[] = 'data-url:{url:'.$settings['event'].'}&tvout=view';
				}
				if(!empty($settings['result'])){
					$attrs[] = 'data-result:'.$settings['result'];
				}
				
				$view['attrs'] = $view['attrs'].implode("\n", $attrs);
				break;
				
			case 'static-remove':
				$settings = $view['actions']['static-remove'];
				
				$attrs = ['class:G2-static'];
				if(!empty($settings['task'])){
					$attrs[] = 'data-task:remove/'.$settings['task'];
				}
				
				$view['attrs'] = $view['attrs'].implode("\n", $attrs);
				break;
		}
	}
	
	$field_class = $this->Field->setup('link', $view, $this->Parser, $Html);
	
	echo $Html->input('button_link')->field($field_class);