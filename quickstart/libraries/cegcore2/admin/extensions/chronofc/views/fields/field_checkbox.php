<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="field">
	<label><?php el('Label'); ?></label>
	<input type="text" value="<?php echo \G2\L\Str::camilize($field_type); ?>" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][label]">
</div>

<div class="two fields">
	<div class="field">
		<label><?php el('Name'); ?></label>
		<input type="text" value="<?php echo $field_type; ?><?php echo $field_number; ?>" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][name]">
	</div>
	<div class="field">
		<label><?php el('ID'); ?></label>
		<input type="text" value="<?php echo $field_type; ?><?php echo $field_number; ?>" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][id]">
	</div>
</div>

<div class="two fields">
	<div class="field">
		<label><?php el('Value'); ?></label>
		<input type="text" value="1" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][value]">
	</div>
</div>

<div class="field">
	<div class="ui checkbox">
		<input type="hidden" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][checked]" data-ghost="1" value="">
		<input type="checkbox" class="hidden" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][checked]" value="checked">
		<label><?php el('Checked'); ?></label>
	</div>
</div>

<div class="ui fluid accordion">
	<div class="title ui header small"><i class="dropdown icon"></i><?php el('Advanced settings'); ?></div>
	<div class="content">
		
		<div class="fields inline">
			<div class="field">
				<div class="ui checkbox">
					<input type="hidden" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][ghost][enabled]" data-ghost="1" value="0">
					<input type="checkbox" checked="checked" class="hidden" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][ghost][enabled]" value="1">
					<label><?php el('Enable ghost'); ?></label>
				</div>
			</div>
			<div class="field">
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][ghost][value]" placeholder="<?php el('Ghost value'); ?>">
			</div>
		</div>

		<div class="two fields">
			<div class="field">
				<label><?php el('Extra attributes'); ?></label>
				<textarea name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][attrs]" rows="3"></textarea>
			</div>
			<div class="field">
				<label><?php el('Validation rules'); ?></label>
				<textarea name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][validation][rules]" rows="3"></textarea>
			</div>
		</div>

		<div class="two fields">
			<div class="field">
				<label><?php el('Container class'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][container][class]">
			</div>
		</div>
		
	</div>
	
	<div class="title ui header small"><i class="dropdown icon"></i><?php el('Field events'); ?></div>
	<div class="content fields_events_list">
		<input type="hidden" class="fields_events_counter" value="<?php echo !empty($field_data['events']) ? max(array_keys($field_data['events'])) : 0; ?>">
		
		<?php foreach($field_data['events'] as $ke => $field_event): ?>
		<div class="two fields">
			<div class="three wide field">
				<label><?php el('On'); ?></label>
				<select name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][events][<?php echo $ke; ?>][sign]" class="ui fluid dropdown">
					<option value="="><?php el('Checked'); ?></option>
					<option value="!="><?php el('UnChecked'); ?></option>
				</select>
			</div>
			<div class="three wide field">
				<label><?php el('Action'); ?></label>
				<select name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][events][<?php echo $ke; ?>][action]" class="ui fluid dropdown">
					<option value="enable"><?php el('Enable'); ?></option>
					<option value="disable"><?php el('Disable'); ?></option>
					<option value="show"><?php el('Show'); ?></option>
					<option value="hide"><?php el('Hide'); ?></option>
					<option value="disable_validation"><?php el('Disable validation'); ?></option>
					<option value="enable_validation"><?php el('Enable validation'); ?></option>
				</select>
			</div>
			<div class="six wide field">
				<label><?php el('Element identifier'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][events][<?php echo $ke; ?>][identifier]">
			</div>
			<div class="two wide field">
				<label>&nbsp;</label>
				<button type="button" class="ui button icon compact green tiny" onclick="Fields_add_field_event(this);"><i class="plus icon"></i></button>
				<button type="button" class="ui button icon compact red tiny <?php if($ke == 0): ?>hidden<?php endif; ?> delete_button" onclick="Fields_delete_field_event(this);"><i class="cancel icon"></i></button>
			</div>
		</div>
		<?php endforeach; ?>
		
	</div>
	
</div>