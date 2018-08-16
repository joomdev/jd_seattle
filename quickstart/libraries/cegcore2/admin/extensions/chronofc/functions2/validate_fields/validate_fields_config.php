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
		<input type="hidden" value="validate_fields" name="Connection[functions][<?php echo $n; ?>][type]">
		
		<div class="two fields advanced_conf">
			<div class="field">
				<label><?php el('Name'); ?></label>
				<input type="text" value="" name="Connection[functions][<?php echo $n; ?>][name]">
			</div>
		</div>
		
		<div class="ui segment active" data-tab="function-<?php echo $n; ?>">
			
			<div class="field">
				<div class="ui checkbox toggle">
					<input type="hidden" name="Connection[functions][<?php echo $n; ?>][enabled]" data-ghost="1" value="">
					<input type="checkbox" checked="checked" class="hidden" name="Connection[functions][<?php echo $n; ?>][enabled]" value="1">
					<label><?php el('Enabled'); ?></label>
				</div>
			</div>
			
			<div class="two fields">
				<div class="field">
					<label><?php el('List errors'); ?></label>
					<select name="Connection[functions][<?php echo $n; ?>][list_errors]" class="ui fluid dropdown">
						<option value="1"><?php el('Yes'); ?></option>
						<option value="0"><?php el('No'); ?></option>
					</select>
				</div>
				<div class="field">
					<label><?php el('Data provider'); ?></label>
					<input type="text" value="{data:}" name="Connection[functions][<?php echo $n; ?>][data_provider]">
					<small><?php el('The data set which has the fields data.'); ?></small>
				</div>
			</div>
			
			<div class="field">
				<label><?php el('Default error message'); ?></label>
				<input type="text" value="<?php el('Please provide all the required info.'); ?>" name="Connection[functions][<?php echo $n; ?>][error_message]">
				<small><?php el('Error message displayed when the fields data is empty.'); ?></small>
			</div>
			
			<div class="field">
				<label><?php el('Fields list selection'); ?></label>
				<select name="Connection[functions][<?php echo $n; ?>][fields_selection]" class="ui fluid dropdown">
					<option value=""><?php el('All fields with validation rules'); ?></option>
					<option value="include"><?php el('Only the list of fields entered below.'); ?></option>
					<option value="exclude"><?php el('All fields with validation rules but excluding those listed below.'); ?></option>
				</select>
				<small><?php el('Select the fields collection to be validated.'); ?></small>
			</div>
			
			<div class="field">
				<label><?php el('Fields list'); ?></label>
				<textarea name="Connection[functions][<?php echo $n; ?>][fields_list]" rows="7"></textarea>
				<small><?php el('Multiline list of fields to be included or excluded based on the setting above.'); ?></small>
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