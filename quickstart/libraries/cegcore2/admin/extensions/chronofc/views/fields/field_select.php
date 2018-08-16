<?php
/* @copyright:ChronoEngine.com @license:GPLv2 */defined('_JEXEC') or die('Restricted access');
defined("GCORE_SITE") or die;
?>
<div class="two fields">
	<div class="twelve wide field">
		<label><?php el('Label'); ?></label>
		<input type="text" value="<?php echo \G2\L\Str::camilize($field_type); ?>" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][label]">
	</div>
	<div class="four wide field">
		<label><?php el('Multi select?'); ?></label>
		<select name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][multiple]" class="ui fluid dropdown">
			<option value="0"><?php el('Single'); ?></option>
			<option value="multiple"><?php el('Multi selection'); ?></option>
		</select>
	</div>
</div>

<div class="two fields">
	<div class="field">
		<label><?php el('Name'); ?></label>
		<input type="text" value="<?php echo $field_type; ?><?php echo $field_number; ?>[]" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][name]">
	</div>
	<div class="field">
		<label><?php el('ID'); ?></label>
		<input type="text" value="<?php echo $field_type; ?><?php echo $field_number; ?>" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][id]">
	</div>
</div>

<div class="two fields">
	<div class="ten wide field">
		<label><?php el('Options'); ?></label>
		<textarea name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][options]" rows="7"><?php echo "y=Yes\nn=No"; ?></textarea>
	</div>
	<div class="six wide field">
		<label><?php el('Selected values'); ?></label>
		<textarea name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][selected]" rows="7"></textarea>
	</div>
</div>

<div class="ui fluid accordion">
	<div class="title ui header small"><i class="dropdown icon"></i><?php el('Advanced settings'); ?></div>
	<div class="content">
	
		<div class="two fields">
			<div class="field">
				<label><?php el('AutoComplete event'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][autocomplete][event]">
			</div>
			<div class="field">
				<label><?php el('Minimum Characters to autocomplete'); ?></label>
				<input type="text" value="0" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][data-mincharacters]">
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
					<option value="="><?php el('selecting'); ?></option>
					<option value="!="><?php el('not selecting'); ?></option>
				</select>
			</div>
			<div class="five wide field">
				<label><?php el('Value'); ?></label>
				<input type="text" value="" name="Connection[views][<?php echo $n; ?>][fields][<?php echo $field_number; ?>][events][<?php echo $ke; ?>][value]">
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
			<div class="three wide field">
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