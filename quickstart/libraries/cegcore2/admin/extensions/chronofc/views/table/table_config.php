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
		<input type="hidden" value="table" name="Connection[views][<?php echo $n; ?>][type]">
		
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
				<small><?php el('The data set used to populat the table list.'); ?></small>
			</div>
			
			<div class="field">
				<label><?php el('Class'); ?></label>
				<input type="text" value="ui selectable table" name="Connection[views][<?php echo $n; ?>][class]">
				<small><?php el('The class attribute, changing this will affect how your table looks.'); ?></small>
			</div>
		</div>
		
		<div class="field">
			<div class="ui checkbox toggle">
				<input type="hidden" name="Connection[views][<?php echo $n; ?>][form]" data-ghost="1" value="">
				<input type="checkbox" checked class="hidden" name="Connection[views][<?php echo $n; ?>][form]" value="1">
				<label><?php el('Include form ?'); ?></label>
				<small><?php el('Should the content be placed inside form tags ? this is required for listing features like selectors and toolbar buttons.'); ?></small>
			</div>
		</div>
		
		<div class="field">
			<label><?php el('Columns list'); ?></label>
			<textarea name="Connection[views][<?php echo $n; ?>][columns]" rows="10"></textarea>
			<small><?php el('A list of the table columns fields names and headers, example: Model.field_name:Header'); ?></small>
		</div>
		
		<div class="field">
			<div class="ui checkbox">
				<input type="hidden" name="Connection[views][<?php echo $n; ?>][auto_fields]" data-ghost="1" value="">
				<input type="checkbox" class="hidden" name="Connection[views][<?php echo $n; ?>][auto_fields]" value="1">
				<label><?php el('Include all the provider fields'); ?></label>
				<small><?php el('If this option is enabled then all the provider record fields will be listed in the table and the columns list will be ignored.'); ?></small>
			</div>
		</div>
		
		<div class="field">
			<label><?php el('Columns views'); ?></label>
			<textarea name="Connection[views][<?php echo $n; ?>][views]" rows="10"></textarea>
			<small><?php el('A list of the table columns fields names and the column view, example: Model.field_name:{view:view_name}'); ?></small>
		</div>
		
		<div class="field">
			<label><?php el('Columns classes'); ?></label>
			<textarea name="Connection[views][<?php echo $n; ?>][classes]" rows="5"></textarea>
			<small><?php el('A list of the table columns fields names and the column class, example: Model.field_name:class_name'); ?></small>
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