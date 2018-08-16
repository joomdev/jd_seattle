<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$Html = new \G2\H\Html();
	
	list($images) = $this->Parser->multiline($view['options']);
	shuffle($images);
	shuffle($images);
	shuffle($images);
	
	$list = array_slice($images, 0, (int)$view['number']);
	$theone = rand(0, ((int)$view['number']) - 1);
	
	$view['label'] = sprintf($view['label'], $list[$theone]['value']);
	
	$options = [];
	
	foreach($list as $k => $item){
		$val = uniqid();
		if($k == $theone){
			\GApp::session()->set('secicon/'.$view['params']['name'], $val);
		}
		$options[] = $val.'=<i class="icon '.$item['name'].' large"></i>';
	}
	
	$view['options'] = implode("\n", $options);
	$field_class = $this->Field->setup('radios', $view, $this->Parser, $Html);
	
	echo $Html->input('radio', 'radio')->fields([], $field_class);