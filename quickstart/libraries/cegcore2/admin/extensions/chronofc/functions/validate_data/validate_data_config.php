<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="ui segment tab functions-tab active" data-tab="function-<?php echo $n; ?>">

	<div class="ui top attached tabular menu small G2-tabs">
		<a class="item active" data-tab="function-<?php echo $n; ?>-general"><?php el('General'); ?></a>
		<a class="item" data-tab="function-<?php echo $n; ?>-permissions"><?php el('Permissions'); ?></a>
	</div>
	
	<div class="ui bottom attached tab segment active" data-tab="function-<?php echo $n; ?>-general">
		<input type="hidden" value="validate_data" name="Connection[functions][<?php echo $n; ?>][type]">
		
		<div class="two fields advanced_conf">
			<div class="field">
				<label><?php el('Name'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][name]">
			</div>
		</div>
		
		<div class="ui segment active" data-tab="function-<?php echo $n; ?>">
			
			<div class="two fields">
				<div class="field">
					<label><?php el('Data provider'); ?></label>
					<input type="text" value="{data:}" name="Connection[functions][<?php echo $n; ?>][data_provider]">
				</div>
				<div class="field">
					<label><?php el('List errors'); ?></label>
					<select name="Connection[functions][<?php echo $n; ?>][list_errors]" class="ui fluid dropdown">
						<option value="1"><?php el('Yes'); ?></option>
						<option value="0"><?php el('No'); ?></option>
					</select>
				</div>
			</div>
			
			<div class="field">
				<label><?php el('Default error message'); ?></label>
				<input type="text" value="<?php el('Please provide all the required info.'); ?>" name="Connection[functions][<?php echo $n; ?>][error_message]">
			</div>
			
			<div class="field required">
				<label><?php el('Fields setup'); ?></label>
				<textarea placeholder="<?php el('Multiline list, e.g: Field/required:Field xyz is required'); ?>" name="Connection[functions][<?php echo $n; ?>][fields]" rows="10"></textarea>
			</div>
			
		</div>
		
	</div>
	
	<div class="ui bottom attached tab segment" data-tab="function-<?php echo $n; ?>-permissions">
		<div class="two fields">
			<div class="field">
				<label><?php el('Owner id value'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][owner_id]">
			</div>
		</div>
		
		<?php $this->view('views.permissions_manager', ['model' => 'Connection[functions]['.$n.']', 'perms' => ['access' => rl('Access')], 'groups' => $this->get('groups')]); ?>
	</div>
	
</div>