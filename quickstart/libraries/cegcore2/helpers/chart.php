<?php
/**
* ChronoCMS version 1.0
* Copyright (c) 2012 ChronoCMS.com, All rights reserved.
* Author: (ChronoCMS.com Team)
* license: Please read LICENSE.txt
* Visit http://www.ChronoCMS.com for regular updates and information.
**/
namespace G2\H;
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
class Chart extends \G2\L\Helper{
	
	public static function render($data, $settings = array()){
		if(empty($settings['x_field']) OR empty($settings['y_field'])){
			return rl('No data fields defined.');
		}
		$chart['width'] = 550;
		$chart['height'] = 600;
		
		$chart['left_indent'] = 50;
		$chart['right_indent'] = 30;
		$chart['top_indent'] = 50;
		$chart['bottom_indent'] = 100;
		$chart['bars_indent'] = 20;
		
		$chart['axis_label_color'] = 'black';
		$chart['bar_label_color'] = 'red';
		$chart['field_label_color'] = 'blue';
		$chart['bar_color'] = '#04476c';
		
		$chart['xaxis_labels_rotate'] = false;
		
		$chart = array_replace($chart, $settings);
		
		$usable_width = $chart['width'] - $chart['left_indent'] - $chart['right_indent'] - $chart['bars_indent'];
		//$yaxis_top = $y_indent;
		$usable_height = $chart['height'] - $chart['top_indent'] - $chart['bottom_indent'];
		//pr($data);
		$bar_width = $usable_width/count($data) * 0.8;
		$bar_spacing = $usable_width/count($data) * 0.2;
		
		$chart['x_field_title'] = isset($chart['x_field_title']) ? $chart['x_field_title'] : $chart['x_field'];
		$chart['y_field_title'] = isset($chart['y_field_title']) ? $chart['y_field_title'] : $chart['y_field'];
		
		//get max y
		$y_values = \G2\L\Arr::getVal($data, ['[n]', $chart['y_field']], []);
		rsort($y_values);
		$max_y = array_shift($y_values);
		if(!empty($chart['max_y'])){
			$max_y = $chart['max_y'];
		}
		
		$y_factor = $usable_height/$max_y;
		
		//axis
		$xaxis_length = $chart['width'] - $chart['left_indent'] - $chart['right_indent'];
		$xaxis_x = $chart['left_indent'];
		$xaxis_y = $usable_height + $chart['top_indent'];
		
		$yaxis_length = $chart['height'] - $chart['top_indent'] - $chart['bottom_indent'];
		$yaxis_x = $chart['left_indent'];
		$yaxis_y = $chart['top_indent'];
		
		$bars = [];
		foreach($data as $item){
			$bars[] = ['x' => 0];
		}
		ob_start();
		?>
		<svg width="<?php echo $chart['width']; ?>" height="<?php echo $chart['height']; ?>">
			<g class="g2 charts yaxis-labels">
				<?php for($i = 0; $i <= $max_y; $i = $i + $max_y/count($data)): ?>
					<?php
						$ny = ceil($i);
						$yaxis_label_y = (($max_y - $ny) * $y_factor) + $chart['top_indent'];
					?>
					<?php if($i != 0): ?>
					<text x="<?php echo $chart['left_indent'] - 2; ?>" y="<?php echo $yaxis_label_y + 5; ?>" fill="<?php echo $chart['axis_label_color']; ?>" text-anchor="end"><?php echo $ny; ?></text>
					<?php endif; ?>
					<path d="M<?php echo $chart['left_indent']; ?>,<?php echo $yaxis_label_y; ?> L<?php echo $chart['left_indent'] + $xaxis_length; ?>,<?php echo $yaxis_label_y; ?>" stroke-linecap="butt" stroke-dasharray="2,2" stroke="#999999" stroke-opacity="1" stroke-width="1" fill="none"></path>
				<?php endfor; ?>
			</g>
			<g class="g2 charts bars">
				<?php foreach($data as $k => $item): ?>
				<?php
					$bar_x = $chart['left_indent'] + $chart['bars_indent'] + (($bar_width + $bar_spacing) * $k);
					$bar_y = $usable_height + $chart['top_indent'] - ($item[$chart['y_field']] * $y_factor);
					$height = ($item[$chart['y_field']] * $y_factor);
				?>
				<rect x="<?php echo $bar_x; ?>" y="<?php echo $bar_y; ?>" width="<?php echo $bar_width; ?>" height="<?php echo $height; ?>" rx="0" ry="0" fill-opacity="1" fill="<?php echo $chart['bar_color']; ?>" stroke="#333333" stroke-width="0" stroke-dasharray="none" stroke-linejoin="miter" stroke-opacity="1" data-hint="<?php echo $item[$chart['x_field']].': '.$item[$chart['y_field']]; ?>"></rect>
				<?php endforeach; ?>
			</g>
			<g class="g2 charts barlabels">
				<?php foreach($data as $k => $item): ?>
				<?php
					$bar_label_x = $chart['left_indent'] + $chart['bars_indent'] + (($bar_width + $bar_spacing) * $k) + $bar_width/2;// + 5;
					$bar_label_y = $usable_height + $chart['top_indent'] - ($item[$chart['y_field']] * $y_factor) - 2;// + strlen($item[$chart['y_field']]) * 10 + 1;
				?>
				<text x="<?php echo $bar_label_x; ?>" y="<?php echo $bar_label_y; ?>" fill="<?php echo $chart['bar_label_color']; ?>" text-anchor="middle"><?php echo $item[$chart['y_field']]; ?></text>
				<?php endforeach; ?>
			</g>
			<g class="g2 charts xaxis">
				<path d="M<?php echo $xaxis_x; ?>,<?php echo $xaxis_y; ?> L<?php echo $xaxis_x + $xaxis_length; ?>,<?php echo $xaxis_y; ?>" stroke-linecap="butt" stroke="#999999" stroke-opacity="1" stroke-width="1" fill="none"></path>
				<text x="<?php echo $chart['left_indent'] + $usable_width/2; ?>" y="<?php echo $chart['top_indent'] + $yaxis_length + ($chart['bottom_indent'] * 0.8); ?>" fill="<?php echo $chart['field_label_color']; ?>" text-anchor="middle" font-weight="bold" font-size="14"><?php echo $chart['x_field_title']; ?></text>
			</g>
			<g class="g2 charts xaxis-labels">
				<?php foreach($data as $k => $item): ?>
					<?php
						$xaxis_label_x = $chart['left_indent'] + $chart['bars_indent'] + (($bar_width + $bar_spacing) * $k) + $bar_width/2 + 5;
						$xaxis_label_y = $usable_height + $chart['top_indent'] + 3;// + strlen($item[$chart['x_field']]) * 10 + 1;
					?>
					<?php if(!empty($chart['xaxis_labels_rotate'])): ?>
					<text x="<?php echo $xaxis_label_x; ?>" y="<?php echo $xaxis_label_y; ?>" fill="<?php echo $chart['axis_label_color']; ?>" text-anchor="end" transform="rotate(-90 <?php echo $xaxis_label_x; ?> <?php echo $xaxis_label_y; ?>)"><?php echo $item[$chart['x_field']]; ?></text>
					<?php else: ?>
					<text x="<?php echo $xaxis_label_x; ?>" y="<?php echo $xaxis_label_y + 10; ?>" fill="<?php echo $chart['axis_label_color']; ?>" text-anchor="middle"><?php echo $item[$chart['x_field']]; ?></text>
					<?php endif; ?>
				<?php endforeach; ?>
			</g>
			<g class="g2 charts yaxis">
				<!--
				<path d="M<?php echo $yaxis_x; ?>,<?php echo $yaxis_y; ?> L<?php echo $yaxis_x; ?>,<?php echo $yaxis_y + $yaxis_length; ?>" stroke-linecap="butt" stroke="#999999" stroke-opacity="1" stroke-width="1" fill="none" ></path>
				-->
				<text x="<?php echo $chart['left_indent']/5; ?>" y="<?php echo $chart['top_indent'] + $yaxis_length/2; ?>" fill="<?php echo $chart['field_label_color']; ?>" text-anchor="middle" font-weight="bold" font-size="14" transform="rotate(-90 <?php echo $chart['left_indent']/5; ?> <?php echo $chart['top_indent'] + $yaxis_length/2; ?>)"><?php echo $chart['y_field_title']; ?></text>
			</g>
		</svg>
		<?php
		return ob_get_clean();
	}
}