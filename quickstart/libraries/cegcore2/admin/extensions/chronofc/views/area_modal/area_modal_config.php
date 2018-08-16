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
		<input type="hidden" value="area_modal" name="Connection[views][<?php echo $n; ?>][type]">
		
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
			<div class="six wide field">
				<label><?php el('ID'); ?></label>
				<input type="text" value="area_modal_<?php echo $n; ?>" name="Connection[views][<?php echo $n; ?>][id]">
			</div>
			
			<div class="ten wide field">
				<label><?php el('Class'); ?></label>
				<input type="text" value="ui modal" name="Connection[views][<?php echo $n; ?>][class]">
			</div>
		</div>
		
		<div class="three fields">
			
			<div class="field">
				<label><?php el('Close icon ?'); ?></label>
				<select name="Connection[views][<?php echo $n; ?>][close_icon]" class="ui fluid dropdown">
					<option value=""><?php el('No'); ?></option>
					<option value="1"><?php el('Yes'); ?></option>
				</select>
			</div>
			
			<div class="field">
				<label><?php el('Closable ?'); ?></label>
				<select name="Connection[views][<?php echo $n; ?>][closable]" class="ui fluid dropdown">
					<option value="1"><?php el('Yes'); ?></option>
					<option value=""><?php el('No'); ?></option>
				</select>
			</div>
			
			<div class="field">
				<label><?php el('Launch button selector'); ?></label>
				<input type="text" value=".launch" readonly name="Connection[views][<?php echo $n; ?>][launch_selector]">
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