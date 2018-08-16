<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="ui segment tab views-tab active" data-tab="view-<?php echo $n; ?>">

	<div class="ui top attached tabular menu small G2-tabs">
		<a class="item active" data-tab="view-<?php echo $n; ?>-general"><?php el('General'); ?></a>
		<a class="item" data-tab="view-<?php echo $n; ?>-permissions"><?php el('Permissions'); ?></a>
	</div>
	
	<div class="ui bottom attached tab segment active" data-tab="view-<?php echo $n; ?>-general">
		<input type="hidden" value="chart" name="Connection[views][<?php echo $n; ?>][type]">
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Name'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][name]">
			</div>
			<div class="field">
				<label><?php el('Category'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][category]">
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Data provider'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][data_provider]">
				<small><?php el('The data set used to generate the chart.'); ?></small>
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('X Field name'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][x_field]">
				<small><?php el('The name of the field to generate the statistic on the X axis.'); ?></small>
			</div>
			<div class="field">
				<label><?php el('Y Field name'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][y_field]">
				<small><?php el('The name of the field to generate the statistic on the Y axis.'); ?></small>
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('x axis label'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][x_field_title]">
				<small><?php el('The label on the X axis.'); ?></small>
			</div>
			<div class="field">
				<label><?php el('y axis label'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][y_field_title]">
				<small><?php el('The label on the Y axis.'); ?></small>
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Chart width'); ?></label>
				<input type="text" value="550" name="Connection[views][<?php echo $n; ?>][width]">
				<small><?php el('The chart width.'); ?></small>
			</div>
			<div class="field">
				<label><?php el('Chart height'); ?></label>
				<input type="text" value="600" name="Connection[views][<?php echo $n; ?>][height]">
				<small><?php el('The chart height.'); ?></small>
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Bottom spacing'); ?></label>
				<input type="text" value="100" name="Connection[views][<?php echo $n; ?>][bottom_indent]">
				<small><?php el('The amount of space left under the x axis for labels.'); ?></small>
			</div>
			<div class="field">
				<label><?php el('Left spacing'); ?></label>
				<input type="text" value="50" name="Connection[views][<?php echo $n; ?>][left_indent]">
				<small><?php el('The amount of space to the left of the y axis left for labels.'); ?></small>
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Top spacing'); ?></label>
				<input type="text" value="50" name="Connection[views][<?php echo $n; ?>][top_indent]">
				<small><?php el('The amount of space left above the chart area.'); ?></small>
			</div>
			<div class="field">
				<label><?php el('Right spacing'); ?></label>
				<input type="text" value="30" name="Connection[views][<?php echo $n; ?>][right_indent]">
				<small><?php el('The amount of space to the right of the chart.'); ?></small>
			</div>
		</div>
		
		<div class="field">
			<div class="ui checkbox">
				<input type="hidden" name="Connection[views][<?php echo $n; ?>][xaxis_labels_rotate]" data-ghost="1" value="">
				<input type="checkbox" class="hidden" name="Connection[views][<?php echo $n; ?>][xaxis_labels_rotate]" value="1">
				<label><?php el('Rotate x axis labels'); ?></label>
				<small><?php el('Rotate the x axis labels to give them more space.'); ?></small>
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Axis label color'); ?></label>
				<input type="text" value="black" name="Connection[views][<?php echo $n; ?>][axis_label_color]">
				<small><?php el('The color of the labels on the axis.'); ?></small>
			</div>
			<div class="field">
				<label><?php el('Field label color'); ?></label>
				<input type="text" value="blue" name="Connection[views][<?php echo $n; ?>][field_label_color]">
				<small><?php el('The color of the fields labels.'); ?></small>
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Bar label color'); ?></label>
				<input type="text" value="red" name="Connection[views][<?php echo $n; ?>][bar_label_color]">
				<small><?php el('The color of the values labels on the bars.'); ?></small>
			</div>
			<div class="field">
				<label><?php el('Bar color'); ?></label>
				<input type="text" value="green" name="Connection[views][<?php echo $n; ?>][bar_color]">
				<small><?php el('The bar color.'); ?></small>
			</div>
		</div>
	
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="view-<?php echo $n; ?>-permissions">
		<div class="two fields">
			<div class="field">
				<label><?php el('Owner id value'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][owner_id]">
			</div>
		</div>
		
		<?php $this->view('views.permissions_manager', ['model' => 'Connection[views]['.$n.']', 'perms' => ['access' => rl('Access')], 'groups' => $this->get('groups')]); ?>
	</div>
	
</div>