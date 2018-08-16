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
		<input type="hidden" value="widget_calculator" name="Connection[views][<?php echo $n; ?>][type]">
		
		<div class="two fields advanced_conf">
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
				<label><?php el('Field name'); ?></label>
				<input type="text" value="total<?php echo $n; ?>" name="Connection[views][<?php echo $n; ?>][params][name]">
				<small><?php el('The name of the hidden field used to store the calculated value.'); ?></small>
			</div>
			
			<div class="field">
				<label><?php el('Field ID'); ?></label>
				<input type="text" value="total<?php echo $n; ?>" name="Connection[views][<?php echo $n; ?>][params][id]">
				<small><?php el('The id of the hidden field used to store the calculated value.'); ?></small>
			</div>
			
		</div>
		
		<div class="two fields">
			<div class="six wide field">
				<label><?php el('Widget ID'); ?></label>
				<input type="text" value="widget_calculator<?php echo $n; ?>" name="Connection[views][<?php echo $n; ?>][id]">
				<small><?php el('an ID for the whole widget pane.'); ?></small>
			</div>
			
			<div class="ten wide field">
				<label><?php el('Class'); ?></label>
				<input type="text" value="ui statistic" name="Connection[views][<?php echo $n; ?>][class]">
				<small><?php el('The styling class.'); ?></small>
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Before value'); ?></label>
				<input type="text" value="$" name="Connection[views][<?php echo $n; ?>][before_value]">
				<small><?php el('A string to display before the calculated total.'); ?></small>
			</div>
			
			<div class="field">
				<label><?php el('Initial value'); ?></label>
				<input type="text" value="0" name="Connection[views][<?php echo $n; ?>][value]">
				<small><?php el('The initial displayed total.'); ?></small>
			</div>
			
			<div class="field">
				<label><?php el('Label'); ?></label>
				<input type="text" value="in total" name="Connection[views][<?php echo $n; ?>][label]">
				<small><?php el('A label for the value.'); ?></small>
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
	
	<button type="button" class="ui button compact red tiny close_config forms_conf"><?php el('Close'); ?></button>
</div>