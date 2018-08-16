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
		<input type="hidden" value="area_partitions" name="Connection[views][<?php echo $n; ?>][type]">
		
		<div class="two fields advanced_conf">
			<div class="field">
				<label><?php el('Name'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][name]" class="view_name">
			</div>
			<div class="field">
				<label><?php el('Category'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][category]">
			</div>
		</div>
		
		<div class="field">
			<label><?php el('Designer Label'); ?></label>
			<input type="text" value="" name="Connection[views][<?php echo $n; ?>][label]">
		</div>
		
		<div class="three fields">
			<div class="field">
				<label><?php el('Class'); ?></label>
				<input type="text" value="ui container fluid" name="Connection[views][<?php echo $n; ?>][class]">
			</div>
			
			<div class="field">
				<label><?php el('Style'); ?></label>
				<select name="Connection[views][<?php echo $n; ?>][style]" class="ui fluid dropdown">
					<option value="tabs"><?php el('Tabs'); ?></option>
					<option value="steps"><?php el('Steps'); ?></option>
					<option value="sequence"><?php el('Sequence'); ?></option>
				</select>
			</div>
			
			<div class="field">
				<label><?php el('Sequential'); ?></label>
				<select name="Connection[views][<?php echo $n; ?>][sequential]" class="ui fluid dropdown">
					<option value="0"><?php el('No'); ?></option>
					<option value="1"><?php el('Yes'); ?></option>
				</select>
				<small class="field-desc"><?php el('If enabled, partitions will be disabled until the previous ones have been completed.', [], 'sequential partition desc'); ?></small>
			</div>
		</div>
		
		<div class="two fields">
			<div class="field">
				<label><?php el('Forward button selector'); ?></label>
				<input type="text" value=".forward" readonly name="Connection[views][<?php echo $n; ?>][forward_selector]">
			</div>
			
			<div class="field">
				<label><?php el('Backward button selector'); ?></label>
				<input type="text" value=".backward" readonly name="Connection[views][<?php echo $n; ?>][backward_selector]">
			</div>
		</div>
		
		<div class="field">
			<label><?php el('Partitions list'); ?></label>
			<textarea name="Connection[views][<?php echo $n; ?>][sections]" class="sections_list"><?php echo "part1:Part #1\npart2:Part #2"; ?></textarea>
		</div>
		
		<button type="button" class="ui button small" onclick="area_partitions_add_columns(this, <?php echo $n; ?>);"><?php el('Update partitions'); ?></button>
		<button type="button" class="ui button small refresh_dragged" data-block="view" data-url="<?php echo r2('index.php?ext=chronoforms&cont=connections&act=refresh_element&tvout=view'); ?>"><?php el('Update partitions'); ?></button>
		
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="view-<?php echo $n; ?>-permissions">
		<div class="two fields">
			<div class="field">
				<label><?php el('Owner id value'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][owner_id]">
				<small><?php el('The value of the owner id with which the owner permission will be checked.'); ?></small>
			</div>
			
			<div class="field">
				<label><?php el('Toggle switch'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][toggler]">
				<small><?php el('If provided and is an empty value then the view will not be rendered.'); ?></small>
			</div>
		</div>
		
		<?php $this->view('views.permissions_manager', ['model' => 'Connection[views]['.$n.']', 'perms' => ['access' => rl('Access')], 'groups' => $this->get('groups')]); ?>
	</div>
	
	<button type="button" class="ui button compact red tiny close_config forms_conf"><?php el('Close'); ?></button>
</div>