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
		<input type="hidden" value="selector" name="Connection[views][<?php echo $n; ?>][type]">
		
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
				<label><?php el('Checkbox field name'); ?></label>
				<input type="text" value="gcb[]" name="Connection[views][<?php echo $n; ?>][input_name]">
				<small><?php el('The name of the selector field, this will be used when the selections are being processed in any function.'); ?></small>
			</div>
			
			<div class="field">
				<label><?php el('Value'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][value]">
				<small><?php el('The value of the selector, it is usually the row id field value or 1.'); ?></small>
			</div>
		</div>
		
		<div class="field">
			<div class="ui checkbox">
				<input type="hidden" name="Connection[views][<?php echo $n; ?>][all]" data-ghost="1" value="">
				<input type="checkbox" class="hidden" name="Connection[views][<?php echo $n; ?>][all]" value="1">
				<label><?php el('This is a "Select all" checkbox?'); ?></label>
				<small><?php el('Should this field be a select all trigger for the rest of selectors inside the table view ?'); ?></small>
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