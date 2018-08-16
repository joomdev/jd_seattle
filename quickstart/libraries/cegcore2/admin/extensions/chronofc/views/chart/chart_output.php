<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$items = $this->Parser->parse($view['data_provider'], true);
	$settings = [
		'x_field' => $view['x_field'], 
		'y_field' => $view['y_field'],
		'xaxis_labels_rotate' => (bool)$view['xaxis_labels_rotate'],
	];
	
	if(!empty($view['x_field_title'])){
		$settings['x_field_title'] = $view['x_field_title'];
	}
	
	if(!empty($view['y_field_title'])){
		$settings['y_field_title'] = $view['y_field_title'];
	}
	
	if(!empty($view['width'])){
		$settings['width'] = $view['width'];
	}
	
	if(!empty($view['height'])){
		$settings['height'] = $view['height'];
	}
	
	if(!empty($view['bottom_indent'])){
		$settings['bottom_indent'] = $view['bottom_indent'];
	}
	
	if(!empty($view['left_indent'])){
		$settings['left_indent'] = $view['left_indent'];
	}
	
	if(!empty($view['axis_label_color'])){
		$settings['axis_label_color'] = $view['axis_label_color'];
	}
	
	if(!empty($view['field_label_color'])){
		$settings['field_label_color'] = $view['field_label_color'];
	}
	
	if(!empty($view['bar_label_color'])){
		$settings['bar_label_color'] = $view['bar_label_color'];
	}
	
	if(!empty($view['bar_color'])){
		$settings['bar_color'] = $view['bar_color'];
	}
	
	echo \G2\H\Chart::render($items, $settings);