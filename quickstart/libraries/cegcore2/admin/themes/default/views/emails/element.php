<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<?php
	$bgcolor = !empty($element['params']['bgcolor']) ? $element['params']['bgcolor'] : '';
	$bgcolor_attr = !empty($bgcolor) ? 'bgcolor="'.$bgcolor.'"' : '';
	$color = !empty($element['params']['color']) ? $element['params']['color'] : '#000000';
	$padding = !empty($element['params']['padding']) ? $element['params']['padding'].'px' : '0';
	$fontsize = !empty($element['params']['font-size']) ? $element['params']['font-size'].'px' : '';
	$lineheight = !empty($element['params']['line-height']) ? $element['params']['line-height'].'px' : '';
	$table_align = ($element['type'] == 'section') ? 'align="center"' : 'align="'.(!empty($element['params']['align']) ? $element['params']['align'] : 'center').'"';
	$td_align = ($element['type'] == 'section') ? '' : 'align="'.(!empty($element['params']['align']) ? $element['params']['align'] : 'center').'"';
	//table css
	$table_css = [
		'border-width' => '0',
		'border-collapse' => 'collapse',
		'border-spacing' => '0',
		'font-family' => "'Arial',Helvetica Neue,Helvetica,sans-serif",
		'font-size' => '12px',
		'font-weight' => '400',
		'line-height' => 'normal',
		'color' => $color,
	];
	//tr css
	$tr_css = [
		'border-width' => '0',
	];
	
	if(!empty($bgcolor)){
		$tr_css = array_merge($tr_css, [
			'background-color' => $bgcolor,
			'background-image' => 'none',
			'background-repeat' => 'repeat',
			'background-position' => 'top left'
		]);
	}
	//td css
	$td_css = [
		'border-width' => '0',
		'padding' => $padding,
	];
	
	if($color != '#000000'){
		$td_css['color'] = $color;
	}
	
	if($fontsize){
		$td_css['font-size'] = $fontsize;
	}
	
	if($lineheight){
		$td_css['line-height'] = $lineheight;
	}
?>
<?php $this->view('views.emails.elements.'.$element['type'], [
	'element' => $element, 
	'elements' => $elements, 
	'bgcolor' => $bgcolor, 
	'bgcolor_attr' => $bgcolor_attr, 
	'color' => $color, 
	'padding' => $padding, 
	'table_align' => $table_align, 
	'td_align' => $td_align, 
	'table_css' => $table_css, 
	'tr_css' => $tr_css, 
	'td_css' => $td_css, 
]); ?>