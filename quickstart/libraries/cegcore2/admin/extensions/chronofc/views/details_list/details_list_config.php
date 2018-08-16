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
		<input type="hidden" value="details_list" name="Connection[views][<?php echo $n; ?>][type]">
		
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
				<small><?php el('The data set used for data display.'); ?></small>
			</div>
		</div>
		
		<div class="field">
			<div class="ui checkbox">
				<input type="hidden" name="Connection[views][<?php echo $n; ?>][auto_fields]" data-ghost="1" value="">
				<input type="checkbox" checked="checked" class="hidden" name="Connection[views][<?php echo $n; ?>][auto_fields]" value="1">
				<label><?php el('Include all the provider fields'); ?></label>
				<small><?php el('Automatically list all the fields in the data set provided, no need to list the view fields below.'); ?></small>
			</div>
		</div>
		
		<div class="field">
			<label><?php el('Fields list'); ?></label>
			<textarea placeholder="<?php el('Multi line list of list fields and labels'); ?>" name="Connection[views][<?php echo $n; ?>][fields]" rows="10"></textarea>
			<small><?php el('Write each field name or path in the data set and its label, example: Model.field_name:Label'); ?></small>
		</div>
		
		<div class="field">
			<label><?php el('Fields views'); ?></label>
			<textarea placeholder="<?php el('Multi line list of list fields and views'); ?>" name="Connection[views][<?php echo $n; ?>][views]" rows="10"></textarea>
			<small><?php el('Write each custom view field and the view name used to display that field value, example: field_name:{view:my_view}'); ?></small>
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